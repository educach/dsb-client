<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Curriculum\PerCurriculum.
 */

namespace Educa\DSB\Client\Curriculum;

use Educa\DSB\Client\Utils;
use Educa\DSB\Client\Curriculum\Term\TermInterface;
use Educa\DSB\Client\Curriculum\Term\PerTerm;
use Educa\DSB\Client\Curriculum\CurriculumInvalidContextException;
use Educa\DSB\Client\Curriculum\CurriculumInvalidDataStructureException;
use Educa\DSB\Client\Curriculum\PerCurriculumEndpointNotAvailableException;

/**
 * @codeCoverageIgnore
 */
class PerCurriculum extends BaseCurriculum
{

    const CURRICULUM_API = 'curriculum api';

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
    protected $taxonPathSources = array('per');

    /**
     * {@inheritdoc}
     *
     * @param string $context
     *    A context, explaining what kind of data this is. Possible contexts:
     *    - PerCurriculum::CURRICULUM_API: This means the $data param will be
     *      treated as a URL to the official BDPER API endpoint.
     */
    public static function createFromData($data, $context = self::CURRICULUM_API)
    {
        switch ($context) {
            case self::CURRICULUM_API:
                $data = self::fetchCurriculumData($data);
                $curriculum = new PerCurriculum($data->curriculum);
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
                'type' => 'cycles',
                'childTypes' => array('domaines'),
                'entry' => (object) array(
                    'fr' => "Cycle",
                ),
            ),
            (object) array(
                'type' => 'domaines',
                'childTypes' => array('disciplines'),
                'entry' => (object) array(
                    'fr' => "Domaine",
                ),
            ),
            (object) array(
                'type' => 'disciplines',
                'childTypes' => array('objectifs'),
                'entry' => (object) array(
                    'fr' => "Discipline",
                ),
            ),
            (object) array(
                'type' => 'objectifs',
                'childTypes' => array('progressions'),
                'entry' => (object) array(
                    'fr' => "Objectif",
                ),
            ),
            (object) array(
                'type' => 'progressions',
                'childTypes' => array(),
                'entry' => (object) array(
                    'fr' => "Progressions d'apprentissage",
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
                'type' => 'cycles',
                'purpose' => array(
                    'LOM-CHv1.2' => 'educational level',
                ),
            ),
            (object) array(
                'type' => 'domaines',
                'purpose' => array(
                    'LOM-CHv1.2' => 'discipline',
                ),
            ),
            (object) array(
                'type' => 'disciplines',
                'purpose' => array(
                    'LOM-CHv1.2' => 'discipline',
                ),
            ),
            (object) array(
                'type' => 'objectifs',
                'purpose' => array(
                    'LOM-CHv1.2' => 'objective',
                ),
            ),
            (object) array(
                'type' => 'progressions',
                'purpose' => array(
                    'LOM-CHv1.2' => 'objective',
                ),
            ),
        );
    }

    /**
     * {@inheritdoc}
     *
     * Because PER doesn't use unique IDs for each term, the identifier must
     * be prefixed with the term type. For example, simply passing "25" for an
     * objective is not enough, as there is also a domain with the "25" ID. To
     * circumvent this problem, the internal dictionary prefixes each item with
     * the type (usually plural; determined by the URI used by the official PER
     * API; for example, it uses /objectifs/25 and /domaines/25, which
     * translates to the following identifiers: "objectifs:25" and
     * "domaines:25", respectively).
     *
     * This means that this method, in the context of PER, is not really
     * useful...
     */
    public function getTermType($identifier)
    {
        return isset($this->curriculumDictionary[$identifier]) ? $this->curriculumDictionary[$identifier]->type : 'root';
    }

    /**
     * {@inheritdoc}
     *
     * Because PER doesn't use unique IDs for each term, the identifier must
     * be prefixed with the term type. For example, simply passing "25" for an
     * objective is not enough, as there is also a domain with the "25" ID. To
     * circumvent this problem, the internal dictionary prefixes each item with
     * the type (usually plural; determined by the URI used by the official PER
     * API; for example, it uses /objectifs/25 and /domaines/25, which
     * translates to the following identifiers: "objectifs:25" and
     * "domaines:25", respectively).
     */
    public function getTermName($identifier)
    {
        return isset($this->curriculumDictionary[$identifier]->name) ? $this->curriculumDictionary[$identifier]->name : 'n/a';
    }

    /**
     * Load the curriculum definition from the BDPER API.
     *
     * By passing the path to the official definition REST API, this method
     * will parse it and return a curriculum definition it can understand and
     * treat. It mainly needs a "dictionary" of term types.
     *
     * @param string $url
     *    The curriculum definition endpoint URL.
     *
     * @return object
     *    An object with 2 properties:
     *    - curriculum: A parsed and prepared curriculum tree. It uses
     *      Educa\DSB\Client\Curriculum\Term\PerTerm elements to define
     *      the curriculum tree.
     *    - dictionary: A dictionary of term identifiers, with name and type
     *      information for each one of them.
     *
     * @see \Educa\DSB\Client\Curriculum\PerCurriculum::setCurriculumDictionary()
     * @see \Educa\DSB\Client\Curriculum\PerCurriculum::prepareFetch()
     * @see \Educa\DSB\Client\Curriculum\PerCurriculum::fetchDomainsAndDisciplines()
     * @see \Educa\DSB\Client\Curriculum\PerCurriculum::fetchObjectivesAndProgressions()
     */
    public static function fetchCurriculumData($url)
    {
        // Step 1.
        list(
            $root,
            $dictionary
        ) = self::prepareFetch();

        // Step 2.
        list(
            $root,
            $dictionary,
            $objectiveIds
        ) = self::fetchDomainsAndDisciplines($url, $root, $dictionary);

        // Step 3.
        list(
            $root,
            $dictionary
        ) = self::fetchObjectivesAndProgressions(
            $url,
            $root,
            $dictionary,
            $objectiveIds
        );

        // Return the parsed data.
        return (object) array(
            'curriculum' => $root,
            'dictionary' => $dictionary,
        );
    }

    /**
     * Prepare the data structures necessary for the fetch.
     *
     * @return array
     *    An array with 2 values:
     *    - A parsed and prepared curriculum tree. It uses
     *      Educa\DSB\Client\Curriculum\Term\PerTerm elements to define
     *      the curriculum tree.
     *    - A dictionary of term identifiers, with name and type
     *      information for each one of them.
     *
     * @see \Educa\DSB\Client\Curriculum\PerCurriculum::fetchCurriculumData()
     */
    public static function prepareFetch()
    {
        $dictionary = array();

        // First, we want to prepare our 3 cycle base terms. In the Per logic,
        // the cycles are at the root of the tree. That is why we
        // prepare them here, and we will add relevant trees underneath when
        // needed. There are, technically, no cycle elements provided by the
        // PER API. But, to remain consistent, we create them anyway, and make
        // their type plural, as with all the other elements.
        $cycle1 = new PerTerm('cycles', 1, array(
            'fr' => "Cycle 1",
        ));
        $cycle2 = new PerTerm('cycles', 2, array(
            'fr' => "Cycle 2",
        ));
        $cycle3 = new PerTerm('cycles', 3, array(
            'fr' => "Cycle 3",
        ));

        // Prepare our root element, and add our cycles to it.
        $root = new PerTerm('root', 'root');
        $root
            ->addChild($cycle1)
            ->addChild($cycle2)
            ->addChild($cycle3);

        foreach ($root->getChildren() as $child) {
            $description = $child->describe();
            $id = $description->id;
            unset($description->id);
            $dictionary["cycles:{$id}"] = $description;
        }

        // Return the data.
        return array($root, $dictionary);
    }

    /**
     * Fetch domains and disciplines.
     *
     * This is the second step in fetching curriculum information. It must be
     * performed after prepareFetch().
     *
     * @param string $url
     *    The curriculum definition endpoint URL.
     * @param Educa\DSB\Client\Curriculum\Term\PerTerm $root
     *    The curriculum tree to add new elements to.
     * @param array $dictionary
     *    The dictionary of term identifiers.
     *
     * @return array
     *    An array with 3 values:
     *    - A parsed and prepared curriculum tree. It uses
     *      Educa\DSB\Client\Curriculum\Term\PerTerm elements to define
     *      the curriculum tree.
     *    - A dictionary of term identifiers, with name and type
     *      information for each one of them.
     *    - A list of found objective identifiers, as returned by the BDPER
     *      REST API.
     *
     * @see \Educa\DSB\Client\Curriculum\PerCurriculum::prepareFetch()
     * @see \Educa\DSB\Client\Curriculum\PerCurriculum::fetchCurriculumData()
     */
    public static function fetchDomainsAndDisciplines($url, $root, $dictionary)
    {
        if (preg_match('/\/$/', $url)) {
            $url = rtrim($url, '/');
        }

        // We need to fetch the objectives, because the relationship between
        // objectives, domains and disciplines is stored there.
        // We cannot use cURL to fetch local data, so if in context of a unit
        // test, we use file_get_contents(), and fetch a local file. We could
        // use file_get_contents() for loading JSON data over HTTP, but this
        // doesn't work if the application is sitting behind a proxy.
        if (defined('RUNNING_PHPUNIT') && RUNNING_PHPUNIT) {
            $json = file_get_contents("$url/objectifs_all");
        } else {
            $ch = curl_init("$url/objectifs");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $json = curl_exec($ch);
            curl_close($ch);
        }

        if (empty($json)) {
            throw new PerCurriculumEndpointNotAvailableException(
                "Could not load data from the BDPER endpoint!"
            );
        }

        $objectives = json_decode($json, true);
        $objectiveIds = array();
        $domains = array();
        $disciplines = array();

        // Fetch the cycles.
        $cycle1 = $root->findChildByIdentifier(1);
        $cycle2 = $root->findChildByIdentifier(2);
        $cycle3 = $root->findChildByIdentifier(3);

        if (!empty($objectives)) {
            foreach ($objectives as $objectiveData) {
                $objectiveIds[] = $objectiveData['id'];
                $cycleNum = (string) $objectiveData['cycle'];

                // To which cycle does it apply?
                switch ($cycleNum) {
                    case '1':
                        $cycle = $cycle1;
                        break;
                    case '2':
                        $cycle = $cycle2;
                        break;
                    case '3':
                        $cycle = $cycle3;
                        break;

                    default:
                        throw new CurriculumInvalidDataStructureException(
                            sprintf("Couldn't find valid cycle information for the objectif with ID %d", $objectiveData['id'])
                        );
                        break;
                }


                // What is the domain ID?
                $domainId = $objectiveData['domaine']['id'];

                // Contrary to objectives and progressions, domains are shared.
                // Check if we already created this domain. If we did, re-use
                // the old one.
                if (isset($domains[$cycleNum][$domainId])) {
                    $domain = $domains[$cycleNum][$domainId];
                } else {
                    $domain = new PerTerm('domaines', $domainId, array(
                        'fr' => $objectiveData['domaine']['nom'],
                    ));
                    $cycle->addChild($domain);

                    // Store it.
                    $domains[$cycleNum][$domainId] = $domain;

                    // Store the description in the dictionary.
                    $description = $domain->describe();
                    unset($description->id);
                    $dictionary["domaines:{$domainId}"] = $description;
                }

                // Treat all disciplines.
                foreach ($objectiveData['disciplines'] as $disciplineData) {
                    $disciplineId = $disciplineData['id'];

                    // Contrary to objectives and progressions, disciplines are
                    // shared. Check if we already created this discipline. If
                    // we did, re-use the old one.
                    if (isset($disciplines[$cycleNum][$domainId][$disciplineId])) {
                        $discipline = $disciplines[$cycleNum][$domainId][$disciplineId];
                    } else {
                        $discipline = new PerTerm('disciplines', $disciplineId, array(
                            'fr' => $disciplineData['nom'],
                        ));
                        $domain->addChild($discipline);

                        // Store it.
                        $disciplines[$cycleNum][$domainId][$disciplineId] = $discipline;

                        // Store the description in the dictionary.
                        $description = $discipline->describe();
                        unset($description->id);
                        $dictionary["disciplines:{$disciplineId}"] = $description;
                    }
                }
            }
        }

        // Return the data.
        return array($root, $dictionary, $objectiveIds);
    }

    /**
     * Fetch objectives and progressions.
     *
     * This is the third and final step in fetching curriculum information. It
     * must be performed after fetchDomainsAndDisciplines().
     *
     * @param string $url
     *    The curriculum definition endpoint URL.
     * @param Educa\DSB\Client\Curriculum\Term\PerTerm $root
     *    The curriculum tree to add new elements to.
     * @param array $dictionary
     *    The dictionary of term identifiers.
     * @param array $objectiveIds
     *    A list of found objective identifiers to treat. This doesn't have to
     *    be the complete list.
     *
     * @return array
     *    An array with 3 values:
     *    - A parsed and prepared curriculum tree. It uses
     *      Educa\DSB\Client\Curriculum\Term\PerTerm elements to define
     *      the curriculum tree.
     *    - A dictionary of term identifiers, with name and type
     *      information for each one of them.
     *
     * @see \Educa\DSB\Client\Curriculum\PerCurriculum::fetchDomainsAndDisciplines()
     * @see \Educa\DSB\Client\Curriculum\PerCurriculum::fetchCurriculumData()
     */
    public static function fetchObjectivesAndProgressions($url, $root, $dictionary, $objectiveIds)
    {
        if (preg_match('/\/$/', $url)) {
            $url = rtrim($url, '/');
        }

        // Prepare a little function for reducing school year lists.
        $reduceSchoolYears = function($schoolYears) {
            return array_values(array_filter(
                array_map(function($item) {
                switch ($item) {
                    case 1:
                        return '1-2';
                    case 3:
                        return '3-4';
                    case 5:
                        return '5-6';
                    case 7:
                        return '7-8';
                    case 9:
                    case 10:
                    case 11:
                        return (string) $item;
                }
                return null;
            }, array_unique($schoolYears))));
        };

        foreach ($objectiveIds as $objectiveId) {
            $names = array();

            // Load the objective data. This call contains more information
            // then the one used in self::fetchDomainsAndDisciplines().
            // We cannot use cURL to fetch local data, so if in context of a
            // unit test, we use file_get_contents(), and fetch a local file. We
            // could use file_get_contents() for loading JSON data over HTTP,
            // but this doesn't work if the application is sitting behind a
            // proxy.
            if (defined('RUNNING_PHPUNIT') && RUNNING_PHPUNIT) {
                $json = file_get_contents("$url/objectifs/$objectiveId");
            } else {
                $ch = curl_init("$url/objectifs/$objectiveId");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $json = curl_exec($ch);
                curl_close($ch);
            }

            if (empty($json)) {
                throw new PerCurriculumEndpointNotAvailableException(
                    "Could not load data from the BDPER endpoint!"
                );
            }

            $objectiveData = json_decode($json, true);

            // To fetch the disciplines this objective belongs to, we need the
            // domain. And for the domain, we need the cycle.
            $cycle = $root->findChildByIdentifier($objectiveData['cycle']);
            $domain = $cycle->findChildByIdentifier($objectiveData['domaine']['id']);
            if (!$domain) {
                throw new CurriculumInvalidDataStructureException(sprintf(
                    "Couldn't find domain with ID %d for the objective with ID %d",
                    $objectiveData['domaine']['id'],
                    $objectiveId
                ));
            }

            // Prepare all theme names.
            foreach ($objectiveData['thematiques'] as $themeData) {
                $names[] = sprintf(
                    '%s (%s)',
                    $themeData['nom'],
                    $objectiveData['code']
                );
            }

            // Objectives are not unique. They can be "shared" by
            // disciplines, meaning we actually have to create one per
            // discipline.
            foreach ($objectiveData['disciplines'] as $disciplineData) {
                // Fetch the discipline.
                $discipline = $domain->findChildByIdentifier($disciplineData['id']);

                if (!$discipline) {
                    throw new CurriculumInvalidDataStructureException(sprintf(
                        "Couldn't find discipline with ID %d for the objective with ID %d",
                        $disciplineData['id'],
                        $objectiveId
                    ));
                }

                $objective = new PerTerm('objectifs', $objectiveId, array(
                    'fr' => implode("\n", $names),
                ));
                $discipline->addChild($objective);

                // Prepare a list for the objective's school years.
                $objectiveSchoolYears = array();

                // Fetch the progressions.
                foreach ($objectiveData['progressions'] as $progressionGroup) {
                    $objectiveSchoolYears = array_merge(
                        $objectiveSchoolYears,
                        $progressionGroup['annees']
                    );

                    // Fetch the "progressions".
                    foreach ($progressionGroup['items'] as $item) {
                        if (!empty($item['contenus'])) {
                            foreach ($item['contenus'] as $content) {
                                $progression = new PerTerm('progressions', $content['id'], array(
                                    'fr' => $content['texte'],
                                ));
                                $progression->setSchoolYears($reduceSchoolYears($progressionGroup['annees']));
                                $objective->addChild($progression);
                                $description = $progression->describe();
                                $description->schoolYears = $progression->getSchoolYears();
                                unset($description->id);
                                $dictionary["progressions:{$content['id']}"] = $description;
                            }
                        }
                    }
                }

                $objective->setSchoolYears($reduceSchoolYears($objectiveSchoolYears));
                $objective->setCode($objectiveData['code']);

                $description = $objective->describe();
                $description->code = $objectiveData['code'];
                $description->schoolYears = $objective->getSchoolYears();
                unset($description->id);
                $dictionary["objectifs:{$objectiveId}"] = $description;
            }
        }

        // Return the data.
        return array($root, $dictionary);
    }

    /**
     * Set the curriculum dictionary.
     *
     * @param array $dictionary
     *
     * @return this
     *
     * @see \Educa\DSB\Client\Curriculum\PerCurriculum::parseCurriculumXml().
     */
    public function setCurriculumDictionary($dictionary)
    {
        $this->curriculumDictionary = $dictionary;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function termFactory($type, $taxonId, $name = null)
    {
        $code = $url = $schoolYears = null;
        if (isset($this->curriculumDictionary["{$type}:{$taxonId}"])) {
            $definition = $this->curriculumDictionary["{$type}:{$taxonId}"];

            if (isset($definition->url)) {
                $url = $definition->url;
            }

            if (isset($definition->code)) {
                $code = $definition->code;
            }

            if (isset($definition->schoolYears)) {
                $schoolYears = $definition->schoolYears;
            }

            // Always fetch the name from the local data. The data passed may be
            // stale, as it usually comes from the dsb API. Normally, local data
            // is refreshed on regular bases, so should be more up-to-date.
            if (isset($definition->name)) {
                $name = $definition->name;
            }
        }

        return new PerTerm($type, $taxonId, $name, $code, $url, $schoolYears);
    }

    /**
     * {@inheritdoc}
     */
    protected function taxonIsDiscipline($taxon)
    {
        return in_array($this->getTermType($taxon['id']), array(
            'disciplines',
        ));
    }

}
