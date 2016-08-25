<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Curriculum\EducaCurriculum.
 */

namespace Educa\DSB\Client\Curriculum;

use Educa\DSB\Client\Utils;
use Educa\DSB\Client\Curriculum\Term\TermInterface;
use Educa\DSB\Client\Curriculum\Term\BaseTerm;
use Educa\DSB\Client\Curriculum\CurriculumInvalidContextException;

class EducaCurriculum extends BaseCurriculum
{

    const CURRICULUM_JSON = 'curriculum json';

    /**
     * The list of all terms, with their associated term type.
     *
     * @var array
     */
    protected $curriculumDictionary;

    /**
     * The sources of taxonomy paths that can be treated by this class.
     *
     * @var array
     */
    protected $taxonPathSources = array('educa');

    /**
     * {@inheritdoc}
     *
     * @param string $context
     *    A context, explaining what kind of data this is. Possible contexts:
     *    - EducaCurriculum::CURRICULUM_JSON: Representation of the curriculum
     *      structure, in JSON. This information can be found on the bsn
     *      Ontology server.
     */
    public static function createFromData($data, $context = self::CURRICULUM_JSON)
    {
        switch ($context) {
            case self::CURRICULUM_JSON:
                $data = self::parseCurriculumJson($data);
                $curriculum = new EducaCurriculum($data->curriculum);
                $curriculum->setCurriculumDictionary($data->dictionary);
                return $curriculum;
        }

        // @codeCoverageIgnoreStart
        throw new CurriculumInvalidContextException();
        // @codeCoverageIgnoreEnd
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function describeDataStructure()
    {
        return array(
            (object) array(
                'type' => 'educa_school_levels',
                'childTypes' => array('context'),
            ),
            (object) array(
                'type' => 'context',
                'childTypes' => array('school_level'),
            ),
            (object) array(
                'type' => 'school_level',
                'childTypes' => array('school_level'),
            ),
            (object) array(
                'type' => 'educa_school_subjects',
                'childTypes' => array('discipline'),
            ),
            (object) array(
                'type' => 'discipline',
                'childTypes' => array('discipline'),
            ),
        );
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function describeTermTypes()
    {
        return array(
            (object) array(
                'type' => 'context',
                'purpose' => array(
                    'LOM-CHv1.2' => 'educational level',
                ),
            ),
            (object) array(
                'type' => 'school level',
                'purpose' => array(
                    'LOM-CHv1.2' => 'educational level',
                ),
            ),
            (object) array(
                'type' => 'discipline',
                'purpose' => array(
                    'LOM-CHv1.2' => 'discipline',
                ),
            ),
        );
    }

    /**
     * Parse the curriculum definition file.
     *
     * By passing the official curriculum definition file (JSON), this method
     * will parse it and return a curriculum definition it can understand and
     * treat. It mainly needs a "dictionary" of term types. See
     * \Educa\DSB\Client\Curriculum\EducaCurriculum::setCurriculumDictionary().
     *
     * @param string $curriculumJson
     *    The curriculum definition file, in JSON.
     *
     * @return array
     *    An object with 2 properties:
     *    - curriculum: A parsed and prepared curriculum tree. It uses
     *      Educa\DSB\Client\Curriculum\Term\TermInterface elements to define
     *      the curriculum tree.
     *    - dictionary: A dictionary of term identifiers, with name and type
     *      information for each one of them.
     *
     * @see \Educa\DSB\Client\Curriculum\EducaCurriculum::setCurriculumDictionary()
     */
    public static function parseCurriculumJson($curriculumJson)
    {
        $data = json_decode($curriculumJson);

        // Prepare the dictionary.
        $dictionary = array();

        // Prepare a list of items. This will make the creation of our
        // curriculum tree easier to manage.
        $list = array();

        $root = new BaseTerm('root', 'root');

        foreach ($data->vocabularies as $vocabulary) {
            $dictionary[$vocabulary->identifier] = (object) array(
                'name' => $vocabulary->name,
                'type' => $vocabulary->identifier,
            );

            $list[$vocabulary->identifier]['root'] = new BaseTerm(
                $vocabulary->identifier,
                $vocabulary->identifier,
                $vocabulary->name
            );

            $root->addChild($list[$vocabulary->identifier]['root']);

            foreach ($vocabulary->terms as $term) {
                if (!empty($term->deprecated)) {
                    continue;
                }

                if ($vocabulary->identifier == 'educa_school_levels') {
                    $type = !empty($term->parents) ? 'school level' : 'context';
                } else {
                    $type = 'discipline';
                }

                // Store the term definition in the dictionary.
                $dictionary[$term->identifier] = (object) array(
                    'name' => $term->name,
                    'type' => $type
                );

                // Did we already create this term, on a temporary basis?
                if (isset($list[$vocabulary->identifier][$term->identifier])) {
                    // We need to "enhance" it now with its actual
                    // information.
                    $item = $list[$vocabulary->identifier][$term->identifier];
                    $item->setDescription($type, $term->identifier, $term->name);
                } else {
                    // Prepare the term element.
                    $item = new BaseTerm($type, $term->identifier, $term->name);
                    $list[$vocabulary->identifier][$term->identifier] = $item;
                }

                // Does it have a parent?
                if (!empty($term->parents)) {
                    // Now, we may not have found the parent yet. Check if
                    // we already have the parent item ready. Even though
                    // the parents property is an array, in practice there
                    // is always a single parent, so we can safely treat the
                    // first key.
                    if (isset($list[$vocabulary->identifier][$term->parents[0]])) {
                        // Found the parent.
                        $parent = $list[$vocabulary->identifier][$term->parents[0]];
                    } else {
                        // There is no parent item ready yet. We need to
                        // create a temporary one, which will be enhanced as
                        // soon as we reach the actual parent term.
                        $parent = new BaseTerm('temp', 'temp');

                        // Store it already; later, we will update its
                        // description data.
                        $list[$vocabulary->identifier][$term->parents[0]] = $parent;
                    }
                } else {
                    // If not, we add it to the root.
                    $parent = $list[$vocabulary->identifier]['root'];
                }

                $parent->addChild($item);
            }
        }

        // Now, treat all items of the school levels, and add the discipline
        // tree to it.
        /*foreach ($list['educa_school_levels'] as $key => $item) {
            // If the item has no children, it is a leaf and can contain
            // discipline information.
            if (!$item->hasChildren()) {
                // We use a trick here. We cannot actually add the same item
                // hierarchy to multiple parents. But, if we clone the top item,
                // it will keep its references to the child items. We can thus
                // simulate multiple trees, where in fact they are all the same
                // tree.
                foreach ($list['educa_school_subjects']['root']->getChildren() as $discipline) {
                    $item->addChild(clone $discipline);
                }
            }
        }*/

        return (object) array(
            'curriculum' => $root,
            'dictionary' => $dictionary,
        );
    }

    /**
     * Set the curriculum dictionary.
     *
     * @param array $dictionary
     *
     * @return this
     *
     * @see \Educa\DSB\Client\Curriculum\EducaCurriculum::parseCurriculumJson().
     */
    public function setCurriculumDictionary($dictionary)
    {
        $this->curriculumDictionary = $dictionary;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTermType($identifier)
    {
        $map = $this->getDeprecationMap();
        $identifier = isset($map[$identifier]) ? $map[$identifier] : $identifier;
        return isset($this->curriculumDictionary[$identifier]) ? $this->curriculumDictionary[$identifier]->type : 'n/a';
    }

    /**
     * {@inheritdoc}
     */
    public function getTermName($identifier)
    {
        $map = $this->getDeprecationMap();
        $identifier = isset($map[$identifier]) ? $map[$identifier] : $identifier;
        return isset($this->curriculumDictionary[$identifier]->name) ? $this->curriculumDictionary[$identifier]->name : 'n/a';
    }

    /**
     * {@inheritdoc}
     */
    protected function taxonIsDiscipline($taxon)
    {
        $map = $this->getDeprecationMap();
        $taxon['id'] = isset($map[$taxon['id']]) ? $map[$taxon['id']] : $taxon['id'];

        // First check the parent implementation. If it is false, use a legacy
        // method.
        if (parent::taxonIsDiscipline($taxon)) {
            return true;
        } else {
            return $this->getTermType($taxon['id']) === 'discipline';
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function termFactory($type, $taxonId, $name = null)
    {
        $map = $this->getDeprecationMap();
        return new BaseTerm(
            $type,
            isset($map[$taxonId]) ? $map[$taxonId] : $taxonId,
            $name
        );
    }

    /**
     * Get a taxon ID deprecation and replacement map.
     *
     * @return array
     */
    protected function getDeprecationMap()
    {
        // Some IDs have been replaced on the Ontology server. However,
        // it doesn't provide a way of fetching a list of replacements. We
        // hard-code it for now.
        return [
            // Keys that got renamed.
            'computer_science_programming' => 'computer_science',
            'ethics and religions' => 'ethics_religions_communities',
            'accounting' => 'accounting_finance',
            'creative activities' => 'art_craft_design',
            'sport' => 'motion_health',
            'general_education' => 'interdisciplinary_topics_skills',
            'collective_projects' => 'projects',
            // Keys that got deprecated in favor of other ones.
            'applied mathematics' => 'mathematics',
            'geometry' => 'mathematics',
            'home economics' => 'domestic science',
            'commercial accounting' => 'accounting',
            'office_and_typing' => 'media and ict',
            'prevention_and_health' => 'motion_health',
            'environment_and_dependencies' => 'development',
            'personal_projects' => 'projects',
        ];
    }
}
