<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Curriculum\ClassificationSystemCurriculum.
 */

namespace Educa\DSB\Client\Curriculum;

use Educa\DSB\Client\Utils;
use Educa\DSB\Client\Curriculum\Term\TermInterface;
use Educa\DSB\Client\Curriculum\Term\EducaTerm;
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
    protected $taxonPathSources = array('educa', 'classification system', 'classification systems');

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
        if (
            ($source == 'educa' && $target == 'classification system') ||
            ($source == 'classification system' && $target == 'educa')
        ) {
            $description = $term->describe();

            $educa2ClassSysMap = [
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

            $classSys2EducaMap = [
                'computer science' => 'computer_science_programming',
                'ethics religions communities' => 'ethics and religions',
                'accounting finance' => 'accounting',
                'art craft design' => 'creative activities',
                'motion health' => 'sport',
                'interdisciplinary topics skills' => 'general_education',
                'projects' => 'collective_projects',
                'independent of levels' => 'indipendent_of_levels',
                'independent of levels others' => 'indipendent_of_levels_others',
                'home economics' => 'domestic science',
                'media and ict' => 'media and ict',
                'development' => 'environment_and_dependencies',
            ];

            if ($source == 'educa') {
                $taxonId = isset($educa2ClassSysMap[$description->id]) ?
                    $educa2ClassSysMap[$description->id] :
                    // We have many keys in common, actually. But of those in
                    // common, "educa" uses underscores, whereas we use spaces. If
                    // it is neither of those, it's probably one of our own keys
                    // anyway; let it pass through.
                    str_replace('_', ' ', $description->id);

                // Set the context. If the identifier is the same as the
                // original term, use the context of the original term. If it's
                // not, or the original term is not available, use "LOM-CHv1.2".
                $context = $description->id == $taxonId && method_exists($term, 'getContext') ?
                    $term->getContext() :
                    'LOM-CHv1.2';

            } else {
                // Set a default value.
                $context = 'LOM-CHv1.0';

                $taxonId = isset($classSys2EducaMap[$description->id]) ?
                    $classSys2EducaMap[$description->id] :
                    // This is a bit more tricky. We don't know exactly which
                    // keys got translated from underscores to spaces. But all
                    // keys that have a context that is different from LOM-CH,
                    // or have a context that is lower than LOM-CHv1.2 are
                    // probably unaltered. Check the context, if it exists. If
                    // it does, and it is not LOM-CH, or lower than LOM-CHv1.2,
                    // leave the key as-is. Otherwise, try translating spaces to
                    // underscores, and hope for the best,
                    (
                        method_exists($term, 'getContext') &&
                        ($context = $term->getContext()) &&
                        (
                            !preg_match('/^LOM-CH/', $context) ||
                            version_compare($context, 'LOM-CHv1.2', '<')
                        ) ?
                            $description->id :
                            str_replace(' ', '_', $description->id)
                    );
            }

            return new EducaTerm(
                $description->type,
                $taxonId,
                isset($description->name) ? $description->name : null,
                $context
            );
        }

        // @codeCoverageIgnoreStart
        return null;
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
        $term = parent::termFactory($type, $taxonId, $name);
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
        $term = new EducaTerm('temp', $identifier);
        $term = $this->mapTerm('educa', 'classification system', $term);
        return $term->describe()->id;
    }
}
