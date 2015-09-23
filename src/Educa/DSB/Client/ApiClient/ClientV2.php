<?php

/**
 * @file
 * Contains \Educa\DSB\Client\ApiClient\ClientV2.
 *
 * This client is compatible with the version 2.x of the REST API.
 */

namespace Educa\DSB\Client\ApiClient;

use GuzzleHttp\Exception\ClientException as GuzzleClientException;
use Educa\DSB\Client\ApiClient\AbstractClient;
use Educa\DSB\Client\ApiClient\ClientAuthenticationException;
use Educa\DSB\Client\ApiClient\ClientRequestException;

class ClientV2 extends AbstractClient
{

    /**
     * @{inheritdoc}
     */
    public function authenticate()
    {
        if (!file_exists($this->privateKeyPath)) {
            throw new ClientAuthenticationException("Private key could not be loaded. Is the path correct ?");
        }

        $privateKeyRaw = file_get_contents($this->privateKeyPath);
        if (empty($this->privateKeyPassphrase)) {
            $privateKey = openssl_pkey_get_private($privateKeyRaw);
        } else {
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

        try {
            $response = $this->post('/auth', $options);
            if ($response->getStatusCode() == 200) {
                $data = json_decode($response->getBody(), true);
                if (!empty($data['token'])) {
                    $this->tokenKey = $data['token'];
                }
                else {
                    throw new ClientAuthenticationException(sprintf("Authentication failed. Status was correct, but couldn't find a token in the body. Body: %s", $response->getBody()));
                }
            } else {
                throw new ClientAuthenticationException(sprintf("Authentication failed. Status: %s. Error message: %s", $response->getStatusCode(), $response->getBody()));
            }
        } catch (GuzzleClientException $e) {
            throw new ClientAuthenticationException(sprintf("Authentication failed. Status: %s. Error message: %s", $e->getCode(), $e->getMessage()));
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
    )
    {
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

        try {
            $response = $this->get('/search', $options);
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody(), true);
            } else {
                throw new ClientRequestException(sprintf("Request to /search failed. Status: %s. Error message: %s", $response->getStatusCode(), $response->getBody()));
            }
        } catch(GuzzleClientException $e) {
            throw new ClientRequestException(sprintf("Request to /search failed. Status: %s. Error message: %s", $e->getCode(), $e->getMessage()));
        }
    }

    /**
     * @{inheritdoc}
     */
    public function getSuggestions($query = '', array $filters = array())
    {
        if (empty($this->tokenKey)) {
            throw new ClientAuthenticationException(sprintf("No token found. Cannot fetch suggestions without a token."));
        }

        $options = array(
            'query' => array(
                'query' => $query,
                'filters' => empty($filters) ? '{}' : json_encode($filters),
            ),
        );

        try {
            $response = $this->get('/suggest', $options);
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody(), true);
            } else {
                throw new ClientRequestException(sprintf("Request to /suggest failed. Status: %s. Error message: %s", $response->getStatusCode(), $response->getBody()));
            }
        } catch(GuzzleClientException $e) {
            throw new ClientRequestException(sprintf("Request to /suggest failed. Status: %s. Error message: %s", $e->getCode(), $e->getMessage()));
        }
    }

    /**
     * @{inheritdoc}
     */
    public function loadDescription($lomId)
    {
        if (empty($this->tokenKey)) {
            throw new ClientAuthenticationException(sprintf("No token found. Cannot load a LOM description without a token."));
        }

        try {
            $response = $this->get('/description/' . urlencode($lomId));
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody(), true);
            } else {
                throw new ClientRequestException(sprintf("Request to /description/%s failed. Status: %s. Error message: %s", $lomId, $response->getStatusCode(), $response->getBody()));
            }
        } catch(GuzzleClientException $e) {
            throw new ClientRequestException(sprintf("Request to /description/%s failed. Status: %s. Error message: %s", $lomId, $e->getCode(), $e->getMessage()));
        }
    }

    /**
     * @{inheritdoc}
     */
    public function loadOntologyData($type = 'list', array $vocabularyIds = null)
    {
        if (empty($this->tokenKey)) {
            throw new ClientAuthenticationException(sprintf("No token found. Cannot load a LOM description without a token."));
        }

        try {
            $response = $this->get(
                "/ontology/{$type}" . (!empty($vocabularyIds) ? '/' . implode(',', $vocabularyIds) : '')
            );

            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody(), true);
            } else {
                throw new ClientRequestException(sprintf("Request to /ontology/%s/%s failed. Status: %s. Error message: %s", $type, implode(',', $vocabularyIds), $response->getStatusCode(), $response->getBody()));
            }
        } catch(GuzzleClientException $e) {
            throw new ClientRequestException(sprintf("Request to /ontology/%s/%s failed. Status: %s. Error message: %s", $type, implode(',', $vocabularyIds), $e->getCode(), $e->getMessage()));
        }
    }

    /**
     * @{inheritdoc}
     */
    public function loadPartners()
    {
        if (empty($this->tokenKey)) {
            throw new ClientAuthenticationException(sprintf("No token found. Cannot load a LOM description without a token."));
        }

        try {
            $response = $this->get('/partner');

            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody(), true);
            } else {
                throw new ClientRequestException(sprintf("Request to /partner failed. Status: %s. Error message: %s", $response->getStatusCode(), $response->getBody()));
            }
        } catch(GuzzleClientException $e) {
            throw new ClientRequestException(sprintf("Request to /partner failed. Status: %s. Error message: %s", $e->getCode(), $e->getMessage()));
        }
    }

    /**
     * @{inheritdoc}
     */
    public function validateDescription($json)
    {
        if (empty($this->tokenKey)) {
            throw new ClientAuthenticationException(sprintf("No token found. Cannot validate a LOM description without a token."));
        }

        $params = array(
            'body' => array(
                'description' => $json
            ),
        );

        try {
            $response = $this->post('/validate', $params);
            return json_decode($response->getBody(), true);
        } catch(GuzzleClientException $e) {
            throw new ClientRequestException(sprintf("Request to /validate failed. Status: %s. Error message: %s", $e->getCode(), $e->getMessage()));
        }
    }

}

