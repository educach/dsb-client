<?php

/**
 * @file
 * Contains \Educa\DSB\Client\ApiClient\ClientV2.
 */

namespace Educa\DSB\Client\ApiClient;

use Educa\DSB\Client\ApiClient\AbstractClient;
use Educa\DSB\Client\ApiClient\ClientAuthenticationException;

class ClientV2 extends AbstractClient {

  /**
   * @{inheritdoc}
   */
  public function authenticate() {
    $privateKeyRaw = file_get_contents($this->privateKeyPath);
    if (empty($this->privateKeyPassphrase)) {
      $privateKey = openssl_pkey_get_private($privateKeyRaw);
    }
    else {
      $privateKey = openssl_pkey_get_private($privateKeyRaw, $this->privateKeyPassphrase);
    }

    if (!$privateKey) {
      throw new ClientAuthenticationException("Private key could not be loaded. Is the passphrase correct ?");
    }

    $vector = md5($this->username . time());
    openssl_sign($vector, $signature, $privateKey);

    $options = array(
      'body' => array(
        'user' => $this->username,
        'signature' => base64_encode($signature),
        'vector' => $vector,
      ),
    );

    $response = $this->post('/auth', $options);

    if ($response->getStatusCode() == 200) {
      $data = json_decode($response->getBody(), true);
      if (!empty($data['token'])) {
        $this->tokenKey = $data['token'];
      }
      else {
        throw new ClientAuthenticationException(sprintf("Authentication failed. Status was correct, but couldn't find a token in the body. Body: %s", $response->getBody()));
      }
    }
    else {
      throw new ClientAuthenticationException(sprintf("Authentication failed. Status: %s. Error message: %s", $response->getStatusCode(), $response->getBody()));
    }

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
    if (empty($this->tokenKey)) {
      throw new ClientAuthenticationException(sprintf("No token found. Cannot make a search request without a token."));
    }

    $options = array(
      'query' => array(
        'query' => $query,
        'facets' => empty($useFacets) ? '[]' : json_encode($useFacets),
        'filters' => empty($filters) ? '{}' : json_encode($filters),
        'additionalFields' => empty($additionalFields) ? '[]' : json_encode($additionalFields),
        'offset' => $offset,
        'limit' => $limit,
        'sortBy' => $sortBy,
      ),
    );

    $response = $this->get('/search', $options);

    if ($response->getStatusCode() == 200) {
      return json_decode($response->getBody(), true);
    }
    else {
      throw new ClientAuthenticationException(sprintf("Request to /search failed. Status: %s. Error message: %s", $response->getStatusCode(), $response->getBody()));
    }
  }

  /**
   * @{inheritdoc}
   */
  public function loadDescription($lomId) {
    if (empty($this->tokenKey)) {
      throw new ClientAuthenticationException(sprintf("No token found. Cannot load a LOM description without a token."));
    }

    $response = $this->get('/description/' . urlencode($lomId));

    if ($response->getStatusCode() == 200) {
      return json_decode($response->getBody(), true);
    }
    else {
      throw new ClientAuthenticationException(sprintf("Request to /description/%s failed. Status: %s. Error message: %s", $lomId, $response->getStatusCode(), $response->getBody()));
    }
  }

  /**
   * @{inheritdoc}
   */
  public function loadOntologyData(array $vocabularyIds = null) {
    if (empty($this->tokenKey)) {
      throw new ClientAuthenticationException(sprintf("No token found. Cannot load a LOM description without a token."));
    }

    $response = $this->get(
      '/ontology/list' . (!empty($vocabularyIds) ? '/' . implode(',', $vocabularyIds) : '')
    );

    if ($response->getStatusCode() == 200) {
      return json_decode($response->getBody(), true);
    }
    else {
      throw new ClientAuthenticationException(sprintf("Request to /ontology/list/%s failed. Status: %s. Error message: %s", implode(',', $vocabularyIds), $response->getStatusCode(), $response->getBody()));
    }
  }
}
