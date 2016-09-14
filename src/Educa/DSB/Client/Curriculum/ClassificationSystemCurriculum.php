<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Curriculum\ClassificationSystemCurriculum.
 */

namespace Educa\DSB\Client\Curriculum;

use Educa\DSB\Client\Utils;
use Educa\DSB\Client\Curriculum\Term\TermInterface;
use Educa\DSB\Client\Curriculum\Term\BaseTerm;
use Educa\DSB\Client\Curriculum\CurriculumInvalidContextException;

class ClassificationSystemCurriculum extends EducaCurriculum implements MappableCurriculumInterface
{

    /**
     * The sources of taxonomy paths that can be treated by this class.
     *
     * This class can also treat "educa" taxonPaths, because it is meant as a
     * replacement. This needs some mappings, though, as described in
     * getDeprecationMap() and getMappedIdentifier().
     *
     * @var array
     */
    protected $taxonPathSources = array('educa', 'classification system');

    /**
     * {@inheritdoc}
     *
     * @param string $context
     *    A context, explaining what kind of data this is. Possible contexts:
     *    - ClassificationSystemCurriculum::CURRICULUM_JSON: Representation
     *      of the curriculum structure, in JSON. This information can be
     *      found on the bsn Ontology server.
     */
    public static function createFromData($data, $context = self::CURRICULUM_JSON)
    {
        switch ($context) {
            case self::CURRICULUM_JSON:
                $data = self::parseCurriculumJson($data);
                $curriculum = new ClassificationSystemCurriculum($data->curriculum);
                $curriculum->setCurriculumDictionary($data->dictionary);
                return $curriculum;
        }

        // @codeCoverageIgnoreStart
        throw new CurriculumInvalidContextException();
        // @codeCoverageIgnoreEnd
    }

    /**
     * {@inheritdoc}
     */
    protected static function parseCurriculumJsonGetType($vocabulary, $term) {
        if ($vocabulary->identifier == 'school context') {
            return !empty($term->parents) ? 'school_level' : 'context';
        } else {
            return 'discipline';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function mapTerm($source, $target, TermInterface $term)
    {
        if ($source == 'educa' && $target == 'classification system') {
            $description = $term->describe();

            $map = [
                // "educa" keys => "classification system" keys.
                'computer_science_programming' => 'computer science',
                'ethics and religions' => 'ethics religions communities',
                'accounting' => 'accounting finance',
                'creative activities' => 'art craft design',
                'sport' => 'motion health',
                'general_education' => 'interdisciplinary topics skills',
                'collective_projects' => 'projects',
                'indipendent_of_levels' => 'independent of levels',
                'indipendent_of_levels_others' => 'independent of levels others',
                'pre-school' => 'compulsory education',
                'applied mathematics' => 'mathematics',
                'geometry' => 'mathematics',
                'home economics' => 'domestic science',
                'commercial accounting' => 'accounting',
                'office_and_typing' => 'media and ict',
                'prevention_and_health' => 'motion health',
                'environment_and_dependencies' => 'development',
                'personal_projects' => 'projects',
            ];

            $description->id = isset($map[$description->id]) ?
                $map[$description->id] :
                // We have many keys in common, actually. But of those in
                // common, "educa" uses underscores, whereas we use spaces. If
                // it is neither of those, it's probably one of our own keys
                // anyway; let it pass through.
                str_replace('_', ' ', $description->id);

            return new BaseTerm(
                $description->type,
                $description->id,
                isset($description->name) ? $description->name : null
            );
        }

        return null;
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
                'type' => 'school context',
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
                'type' => 'school subjects',
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
     */
    public function getTermType($identifier)
    {
        return parent::getTermType($this->mapIdentifier($identifier));
    }

    /**
     * {@inheritdoc}
     */
    public function getTermName($identifier)
    {
        return parent::getTermName($this->mapIdentifier($identifier));
    }

    /**
     * {@inheritdoc}
     */
    protected function termFactory($type, $taxonId, $name = null)
    {
        $term = new BaseTerm($type, $taxonId, $name);
        return $this->mapTerm('educa', 'classification system', $term);
    }

    /**
     * Helper method to map an identifier directly.
     *
     * @param string $identifier
     *
     * @return string
     */
    protected function mapIdentifier($identifier)
    {
        // We might get data from an "educa" source. Map it to our own data,
        // which requires an actual term. Create a dummy one, and use the
        // mapTerm() method to fetch a new identifier, if needed.
        $term = new BaseTerm('temp', $identifier);
        $term = $this->mapTerm('educa', 'classification system', $term);
        return $term->describe()->id;
    }
}
