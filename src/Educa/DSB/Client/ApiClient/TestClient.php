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
}
