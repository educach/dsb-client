<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Curriculum\EducaCurriculum.
 */

namespace Educa\DSB\Client\Curriculum;

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
     * The official curriculum definition.
     *
     * @var array
     */
    protected $curriculumDefinition;

    /**
     * {@inheritdoc}
     *
     * @param string $context
     *    A context, explaining what kind of data this is. Possible contexts:
     *    - EducaCurriculum::CURRICULUM_JSON: Representation of the curriculum
     *      structure, in JSON. This information can be found on the bsn
     *      Ontology server.
     */
    public static function createFromData($data, $context = null)
    {
        switch ($context) {
            case self::TAXON_PATH:
                $curriculum = new EducaCurriculum();
                $curriculum->setCurriculumDefinition($data);
                return $curriculum;
                break;

            default:
                throw new CurriculumInvalidContextException();
                break;
        }
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
                'type' => 'root',
                'child_types' => array(
                    (object) array(
                        'type' => 'context',
                        'required' => true,
                    ),
                ),
            ),
            (object) array(
                'type' => 'context',
                'child_types' => array(
                    (object) array(
                        'type' => 'school_level',
                        'required' => false,
                    ),
                    (object) array(
                        'type' => 'discipline',
                        'required' => false,
                    ),
                ),
            ),
            (object) array(
                'type' => 'school_level',
                'child_types' => array(
                    (object) array(
                        'type' => 'discipline',
                        'required' => true,
                    ),
                ),
            ),
            (object) array(
                'type' => 'discipline',
                'child_types' => array(
                    (object) array(
                        'type' => 'discipline',
                        'required' => false,
                    ),
                ),
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
                'type' => 'root',
                'name' => (object) array(
                    'en' => "Root",
                    'fr' => "Racine",
                    'it' => "Radice",
                ),
                'description' => (object) array(
                    'en' => "Not technically part of the curriculum. The educa curriculum can have multiple contexts, which are, according to the standard, the root elements. As the we must return a single element, this root type defines the top most parent of the curriculum tree.",
                ),
            ),
            (object) array(
                'type' => 'context',
                'name' => (object) array(
                    'en' => "Context",
                    'fr' => "Contexte",
                    'it' => "Contesto",
                ),
            ),
            (object) array(
                'type' => 'school level',
                'name' => (object) array(
                    'en' => "School level",
                    'fr' => "Niveau scolaire",
                    'it' => "Livelli scolastici",
                ),
            ),
            (object) array(
                'type' => 'discipline',
                'name' => (object) array(
                    'en' => "Discipline",
                    'fr' => "Discipline",
                    'it' => "Disciplina",
                ),
            ),
        );
    }

    /**
     * Parse the curriculum definition file.
     *
     * By passing the official curriculum definition file (JSON), this method
     * will parse it and return a curriculum definition it can understand and
     * treat. It mainly needs a "dictionary" of term types. The educa curriculum
     * has the specificity that all disciplines apply to all school levels, as
     * well as some contexts. See
     * \Educa\DSB\Client\Curriculum\EducaCurriculum::setCurriculumDefinition()
     * and
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
     * @see \Educa\DSB\Client\Curriculum\EducaCurriculum::setCurriculumDefinition()
     * @see \Educa\DSB\Client\Curriculum\EducaCurriculum::setCurriculumDictionary()
     */
    public static function parseCurriculumJson($curriculumJson)
    {
        $data = json_decode($curriculumJson);

        // Prepare the dictionary.
        $dictionary = array();

        // Prepare a list of items. This will make the creation of our
        // curriculum tree easier to manage.
        $list = array(
            'educa_school_levels' => array(
                'root' => new BaseTerm('root', 'root'),
            ),
            'educa_school_subjects' => array(
                'root' => new BaseTerm('root', 'root'),
            ),
        );

        // We are interested in the vocabularies. First, we need to treat the
        // school levels. Once they are treated, we can add the discipline
        // information as well to each school level leaf.
        foreach ($data->vocabularies as $vocabulary) {
            foreach ($vocabulary->terms as $term) {
                if ($vocabulary->identifier == 'educa_school_levels') {
                    $type = !empty($term->parents) ? 'school level' : 'context';
                } else {
                    $type = 'discipline';
                }

                // Store the term definition in the dictionary.
                $dictionary[$term->identifier] = (object) array(
                    'name' => $term->name,
                    'type' => $type,
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
        foreach ($list['educa_school_levels'] as $key => $item) {
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
        }

        return (object) array(
            'curriculum' => $list['educa_school_levels']['root'],
            'dictionary' => $dictionary,
        );
    }

    /**
     * Set the curriculum definition.
     *
     * @param array $definition
     *
     * @return this
     *
     * @see \Educa\DSB\Client\Curriculum\EducaCurriculum::parseCurriculumJson().
     */
    public function setCurriculumDefinition($definition)
    {
        $this->curriculumDefinition = $definition;
        return $this;
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
     * Create a new curriculum tree based on a taxonomy paths.
     *
     * The LOM standard defines the "classification" field (9), which stores
     * curriculum classification as "taxonomy paths", flat tree structural
     * representation of curriculum classification. By passing such a structure
     * to this static method, a new tree will be created representing this
     * structure, and a new EducaCurriculum class instance will be returned,
     * with the correct information.
     *
     * @param array $paths
     *    A list of paths, as described in the LOM standard.
     * @param string $purpose
     *    (optional) The educa curriculum paths comes in 2 flavors, "discipline"
     *    and "educational level" paths. Only one can be treated at a time.
     *    Defaults to "discipline".
     *
     * @return this
     */
    public function createTreeFromTaxonPath($paths, $purpose = 'discipline')
    {
        // Prepare a new root item.
        $this->root = new BaseTerm('root', 'root');

        // Prepare a "catalog" of entries, based on their identifiers. This
        // will allow us to easily convert the linear tree representation
        // (LOM describes branches only, with a single path; if a node has
        // multiple sub-branches, their will be multiple paths, and we can
        // link nodes together via their ID).
        $terms = array(
            'root' => $this->root,
        );

        foreach ($paths as $path) {
            // Cast to an array, just in case.
            $path = (array) $path;
            $pathPurpose = Utils::getVCName($path['purpose']);

            if ($pathPurpose == $purpose) {
                foreach ($path['taxonPath'] as $taxonPath) {
                    // Prepare the parent. For the first item, it is always the
                    // root element.
                    $parent = $terms['root'];
                    foreach ($taxonPath['taxon'] as $taxon) {
                        // Cast to an array, just in case.
                        $taxon = (array) $taxon;

                        // Do we already have this term prepared?
                        if (isset($terms[$taxon['id']])) {
                            $term = $terms[$taxon['id']];
                        } else {
                            // Prepare a new term object. First, look for the
                            // term's type. This is defined in the official
                            // curriculum JSON definition.
                            $type = $this->getTermType($taxon['id']);

                            // Get the term's name.
                            $name = $this->getTermName($taxon['id']);

                            // Create the new term.
                            $term = new BaseTerm($type, $taxon['id'], $name);
                        }

                        // Add our term to the tree.
                        $parent->addChild($term);

                        // Our term is now the parent, in preparation for the
                        // next item.
                        $parent = $term;
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Fetch the term's type.
     *
     * The stored curriculum data inside the LOM object contains no information
     * about what the term is. We compare the term's identifier to the
     * curriculum standard and determine its type.
     *
     * @param string $identifier
     *    The identifier of the term.
     *
     * @return string
     *    The term's type.
     */
    protected function getTermType($identifier)
    {
        return isset($this->$curriculumDictionary[$identifier]) ? $this->$curriculumDictionary[$identifier]->type : 'root';
    }

    /**
     * Fetch the term's name.
     *
     * The stored curriculum data inside the LOM object contains information
     * about the term's name, but this information may not be up to date. We
     * compare the term's identifier to the curriculum standard and determine
     * its name.
     *
     * @param string $identifier
     *    The identifier of the term.
     *
     * @return string
     *    The term's name.
     */
    protected function getTermName($identifier)
    {
        return isset($this->$curriculumDictionary[$identifier]) ? $this->$curriculumDictionary[$identifier]->name : 'n/a';
    }
}
