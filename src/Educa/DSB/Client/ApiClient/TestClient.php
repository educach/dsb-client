<?php

/**
 * @file
 * Contains \Educa\DSB\Client\ApiClient\TestClient.
 *
 * This test client can be used to unit test application that rely on the
 * client library. It will return mocked results and data. Use this class in
 * your own unit tests.
 */

namespace Educa\DSB\Client\ApiClient;

use Educa\DSB\Client\ApiClient\AbstractClient;
use Educa\DSB\Client\ApiClient\ClientAuthenticationException;
use Educa\DSB\Client\Utils;

/**
 * @codeCoverageIgnore
 */
class TestClient extends AbstractClient
{

    public function __construct()
    {
      // Override the AbstractClient constructor.
    }

    /**
     * @{inheritdoc}
     */
    public function authenticate()
    {
        // Support chaining.
        return $this;
    }

    /**
     * @{inheritdoc}
     */
    public function search(
        $query = '',
        array $useFacets = array(),
        array $filters = array(),
        array $additionalFields = array(),
        $offset = 0,
        $limit = 50,
        $sortBy = 'random'
    )
    {
        // Helper closure to generate a random string.
        $randomString = function($length) {
            $str = '';
            for ($i = 0; $i < $length; $i++) {
                $str .= chr(mt_rand(32, 126));
            }
            return $str;
        };

        return array(
            'numFound' => rand(2, 20),
            'result' => array_map(function() use($randomString) {
                $languages = array('de', 'fr');
                $ownerUsernames = array('user1@site.com', 'user2@site.com');
                $ownerDisplayNames = array('John Doe', 'Jane Doe');
                $ownerRand = array_rand($ownerUsernames);
                return array(
                    'lomId' => uniqid(),
                    'teaser' => $randomString(120),
                    'language' => $languages[array_rand($languages)],
                    'title' => $randomString(50),
                    'previewImage' => 'http://biblio.educa.ch/sites/all/themes/subthemes/biblio/img/logo_portal.gif',
                    'metaContributorLogos' => array(
                        'http://biblio.educa.ch/sites/all/themes/subthemes/biblio/img/logo_portal.gif',
                    ),
                    'ownerUsername' => $ownerUsernames[$ownerRand],
                    'ownerDisplayName' => $ownerDisplayNames[$ownerRand],
                );
            }, array_fill(1, 10, null)),
            'facets' => array(
                'language' => array(
                    'ontologyId' => null,
                    'name' => 'language',
                    'childTerms' => array(
                        array(
                            'ontologyId' => null,
                            'name' => 'de',
                            'childTerms' => array(),
                            'resultCount' => rand(4,40),
                        ),
                        array(
                            'ontologyId' => null,
                            'name' => 'fr',
                            'childTerms' => array(),
                            'resultCount' => rand(4,40),
                        ),
                    ),
                    'resultCount' => 0,
                ),
                'educaSchoolLevels' => array(
                    'ontologyId' => 'educa_school_levels',
                    'name' => array(
                        'en' => 'School Levels',
                        'rm' => 'stgalims da furmaziun',
                        'fr' => 'Contexte',
                        'de' => 'Kontext',
                        'it' => 'Contesto',
                    ),
                    'childTerms' => array(
                        array(
                            'ontologyId' => 'pre-school',
                            'name' => array(
                                'en' => 'Pre-compulsory',
                                'rm' => 'Prescola',
                                'it' => 'Livello prescolare',
                                'fr' => 'Préobligatoire',
                                'de' => 'Vorschule',
                            ),
                            'childTerms' => array(),
                            'resultCount' => 10,
                        ),
                        array(
                            'ontologyId' => 'compulsory education',
                            'name' => array(
                                'rm' => 'Scola obligatorica',
                                'it' => 'Scuola dell\'obbligo',
                                'fr' => 'Scolarité obligatoire',
                                'de' => 'Obligatorische Schule',
                                'en' => 'Compulsory education',
                            ),
                            'childTerms' => array(
                                array(
                                    'ontologyId' => 'cycle_1',
                                    'name' => array(
                                        'en' => '1st cycle (up to 4th school year)',
                                        'rm' => 'Emprim ciclus',
                                        'fr' => 'Cycle 1 (1ère à 4ème année scolaire) ',
                                        'de' => '1. Zyklus (bis 4. Schuljahr)',
                                        'it' => '1ê ciclo',
                                    ),
                                    'childTerms' => array(
                                        array(
                                            'ontologyId' => '1st_and_2nd_year',
                                            'name' => array(
                                                'rm' => 'emprim e segund onn da scola (ccolina)',
                                                'it' => '1ê e 2ê anno scolastico (scuola dell\'infanzia)',
                                                'de' => '1. und 2. Schuljahr (Kindergarten)',
                                                'en' => '1st and 2nd year (kindergarten)',
                                                'fr' => '1e et 2e année (école enfantine)',
                                            ),
                                            'childTerms' => array(),
                                            'resultCount' => 2,
                                        ),
                                        array(
                                            'ontologyId' => '3rd_and_4th_year',
                                            'name' => array(
                                                'en' => '3rd and 4th year',
                                                'rm' => '3. e 4. classa',
                                                'fr' => '3e et 4e année',
                                                'de' => '3. und 4. Schuljahr',
                                                'it' => '3ê e 4ê anno scolastico',
                                            ),
                                            'childTerms' => array(),
                                            'resultCount' => 22,
                                        ),
                                    ),
                                    'resultCount' => 25,
                                ),
                            ),
                            'resultCount' => 196,
                        ),
                    ),
                    'resultCount' => 196,
                ),
            ),
        );
    }

    /**
     * @{inheritdoc}
     * @todo
     */
    public function loadDescription($lomId)
    {
        return array(
            'lomId' => $lomId,
        );
    }

    /**
     * @{inheritdoc}
     */
    public function validateDescription($json)
    {
        $object = json_decode($json);

        // If the object title has the "VALID" keyword, we treat it as valid.
        // Else, we return some random mumbo jumbo.
        if (isset($object->general->title) && preg_match('/VALID/', Utils::getLSValue($object->general->title))) {
            return array(
                'valid' => true,
            );
        } else {
            return array(
                'valid' => false,
                'message' => "Description is not complete or not compliant.",
                'errors' => array(
                    'general.title' => 'missing',
                    'general.description' => 'wrong type',
                    'general.identifier.entry' => 'malformed',
                    'education.typicalLearningTime' => 'malformed',
                    'education.description' => 'missing',
                ),
            );
        }
    }

    /**
     * @{inheritdoc}
     * @todo
     */
    public function getSuggestions($query = '', array $filters = array())
    {
        return null;
    }

    /**
     * @{inheritdoc}
     * @todo
     */
    public function loadOntologyData($type = 'list', array $vocabularyIds = null)
    {
        return null;
    }

    /**
     * @{inheritdoc}
     * @todo
     */
    public function loadPartners()
    {
        return null;
    }

    /**
     * @{inheritdoc}
     * @todo
     */
    public function loadPartner($partner)
    {
        return null;
    }

    /**
     * @{inheritdoc}
     * @todo
     */
    public function putPartner($partner, $json)
    {
        return null;
    }

    /**
     * @{inheritdoc}
     * @todo
     */
    public function postDescription($json, $catalogs = array(), $previewImage = false)
    {
        return array('lomId' => uniqid());
    }

    /**
     * @{inheritdoc}
     * @todo
     */
    public function putDescription($id, $json, $catalogs = array())
    {
        return array('lomId' => $id);
    }

    /**
     * @{inheritdoc}
     * @todo
     */
    public function deleteDescription($id)
    {
        return array('lomId' => $id);
    }

    /**
     * @{inheritdoc}
     */
    public function loadPartnerStatistics($partnerId, $from, $to, $aggregationMethod = 'day')
    {
        $fromTime = strtotime($from);
        $toTime = strtotime($to);

        if ($fromTime >= $toTime) {
            throw new \InvalidArgumentException("The 'to' date must be greater than the 'from' date.");
        }

        $fromYear = date('Y', $fromTime);
        $toYear = date('Y', $toTime);

        return array_map(function() use($fromYear, $toYear, $aggregationMethod) {
            $result = [
                'lomId' => uniqid(),
                'views' => rand(1, 300),
                // At least put some realistic values for the years.
                'year' => $fromYear == $toYear ? $fromYear : rand($fromYear, $toYear),
            ];

            if (in_array($aggregationMethod, ['day', 'month'])) {
                $result['month'] = rand(1, 12);

                if ($result['month'] < 10) {
                    $result['month'] = '0' . $result['month'];
                }

                if ($aggregationMethod == 'day') {
                    $result['day'] = rand(1, 31);

                    if ($result['day'] < 10) {
                        $result['day'] = '0' . $result['day'];
                    }
                }
            }

            return $result;
        }, array_fill(1, rand(2, 20), null));
    }

    /**
     * @{inheritdoc}
     */
    public function uploadFile($filePath)
    {
        $extension = @end(explode('.', $filePath));
        return array(
            'fileUrl' => 'http://dev-dsb-api.educa.ch/v2/file/default/' . md5($filePath) . ".$extension",
        );
    }
}
