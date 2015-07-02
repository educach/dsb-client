<?php

/**
 * @file
 * Contains \Educa\DSB\Client\ApiClient\TestClient.
 */

namespace Educa\DSB\Client\ApiClient;

use Educa\DSB\Client\ApiClient\AbstractClient;
use Educa\DSB\Client\ApiClient\ClientAuthenticationException;

class TestClient extends AbstractClient {

  public function __construct() {
    // Override the AbstractClient constructor.
  }

  /**
   * @{inheritdoc}
   */
  public function authenticate() {
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
  ) {
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
        return array(
          'lomId' => uniqid(),
          'teaser' => $randomString(120),
          'language' => $languages[array_rand($languages)],
          'title' => $randomString(50),
          'previewImage' => 'http://biblio.educa.ch/sites/all/themes/subthemes/biblio/img/logo_portal.gif',
          'metaContributorLogos' => array(
            'http://biblio.educa.ch/sites/all/themes/subthemes/biblio/img/logo_portal.gif',
          ),
          'ownerUsername' => 'some@email.com',
          'ownerDisplayName' => $randomString(20),
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
      ),
    );
  }

  /**
   * @{inheritdoc}
   */
  public function loadDescription($lomId) {
    return array(
      'lomId' => $lomId,
    );
  }

  /**
   * @{inheritdoc}
   */
  public function loadOntologyData(array $vocabularyIds = null) {
    return null;
  }
}
