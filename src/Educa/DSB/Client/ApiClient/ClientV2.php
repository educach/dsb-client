<?php

/**
 * @file
 * Contains \Educa\DSB\Client\ApiClient\ClientV2.
 *
 * This client is compatible with the version 2.x of the REST API.
 */

namespace Educa\DSB\Client\ApiClient;

use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
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

        $options = [
            'form_params' => [
                'user' => $this->username,
                'signature' => base64_encode($signature),
                'vector' => $vector,
            ],
        ];

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
        } catch (GuzzleRequestException $e) {
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
        array $useFacets = [],
        array $filters = [],
        array $additionalFields = [],
        $offset = 0,
        $limit = 50,
        $sortBy = 'random'
    )
    {
        if (empty($this->tokenKey)) {
            throw new ClientAuthenticationException("No token found. Cannot make a search request without a token.");
        }

        $options = [
            'query' => [
              'query' => $query,
              'facets' => empty($useFacets) ? '[]' : json_encode($useFacets),
              'filters' => empty($filters) ? '{}' : json_encode($filters),
              'additionalFields' => empty($additionalFields) ? '[]' : json_encode($additionalFields),
              'offset' => $offset,
              'limit' => $limit,
              'sortBy' => $sortBy,
            ],
        ];

        try {
            $response = $this->get('/search', $options);
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody(), true);
            } else {
                throw new ClientRequestException(sprintf("Request to /search failed. Status: %s. Error message: %s", $response->getStatusCode(), $response->getBody()));
            }
        } catch(GuzzleRequestException $e) {
            throw new ClientRequestException(sprintf("Request to /search failed. Status: %s. Error message: %s", $e->getCode(), $e->getMessage()));
        }
    }

    /**
     * @{inheritdoc}
     */
    public function getSuggestions($query = '', array $filters = [])
    {
        if (empty($this->tokenKey)) {
            throw new ClientAuthenticationException("No token found. Cannot fetch suggestions without a token.");
        }

        $options = [
            'query' => [
                'query' => $query,
                'filters' => empty($filters) ? '{}' : json_encode($filters),
            ],
        ];

        try {
            $response = $this->get('/suggest', $options);
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody(), true);
            } else {
                throw new ClientRequestException(sprintf("Request to /suggest failed. Status: %s. Error message: %s", $response->getStatusCode(), $response->getBody()));
            }
        } catch(GuzzleRequestException $e) {
            throw new ClientRequestException(sprintf("Request to /suggest failed. Status: %s. Error message: %s", $e->getCode(), $e->getMessage()));
        }
    }

    /**
     * @{inheritdoc}
     */
    public function loadDescription($lomId)
    {
        if (empty($this->tokenKey)) {
            throw new ClientAuthenticationException("No token found. Cannot load a LOM description without a token.");
        }

        try {
            $response = $this->get('/description/' . urlencode($lomId));
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody(), true);
            } else {
                throw new ClientRequestException(sprintf("Request to /description/%s failed. Status: %s. Error message: %s", $lomId, $response->getStatusCode(), $response->getBody()));
            }
        } catch(GuzzleRequestException $e) {
            throw new ClientRequestException(sprintf("Request to /description/%s failed. Status: %s. Error message: %s", $lomId, $e->getCode(), $e->getMessage()));
        }
    }

    /**
     * @{inheritdoc}
     */
    public function loadOntologyData($type = 'list', array $vocabularyIds = null)
    {
        if (empty($this->tokenKey)) {
            throw new ClientAuthenticationException("No token found. Cannot load Ontology data without a token.");
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
        } catch(GuzzleRequestException $e) {
            throw new ClientRequestException(sprintf("Request to /ontology/%s/%s failed. Status: %s. Error message: %s", $type, implode(',', $vocabularyIds), $e->getCode(), $e->getMessage()));
        }
    }

    /**
     * @{inheritdoc}
     */
    public function loadPartners()
    {
        if (empty($this->tokenKey)) {
            throw new ClientAuthenticationException("No token found. Cannot load a partner without a token.");
        }

        try {
            $response = $this->get('/partner');

            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody(), true);
            } else {
                throw new ClientRequestException(sprintf("Request to /partner failed. Status: %s. Error message: %s", $response->getStatusCode(), $response->getBody()));
            }
        } catch(GuzzleRequestException $e) {
            throw new ClientRequestException(sprintf("Request to /partner failed. Status: %s. Error message: %s", $e->getCode(), $e->getMessage()));
        }
    }

    /**
     * @{inheritdoc}
     */
    public function validateDescription($json)
    {
        if (empty($this->tokenKey)) {
            throw new ClientAuthenticationException("No token found. Cannot validate a LOM description without a token.");
        }

        $params = [
            'form_params' => [
                'description' => $json
            ],
        ];

        try {
            $response = $this->post('/validate', $params);
            return json_decode($response->getBody(), true);
        } catch(GuzzleRequestException $e) {
            throw new ClientRequestException(sprintf("Request to /validate failed. Status: %s. Error message: %s", $e->getCode(), $e->getMessage()));
        }
    }

    /**
     * @{inheritdoc}
     */
    public function postDescription($json, $previewImage = false)
    {
        if (empty($this->tokenKey)) {
            throw new ClientAuthenticationException("No token found. Cannot create a LOM description without a token.");
        }

        // @todo DRYer, merge with putDescription() logic.
        $params = [
            'multipart' => [
                [
                    'name'     => 'description',
                    'contents' => $json,
                ],
            ],
        ];

        if ($previewImage) {
            if (file_exists($previewImage) && is_readable($previewImage)) {
                $params['multipart'][] = [
                    'name'     => 'previewImage',
                    'contents' => fopen($previewImage, 'r'),
                ];
            } else {
                throw new \RuntimeException(sprintf("File %s does not exist, or is not readable.", $previewImage));
            }
        }

        try {
            $response = $this->post('/description', $params);
            return json_decode($response->getBody(), true);
        } catch(GuzzleRequestException $e) {
            throw new ClientRequestException(sprintf("Post request to /description failed. Status: %s. Error message: %s", $e->getCode(), $e->getMessage()));
        }
    }

    /**
     * @{inheritdoc}
     */
    public function putDescription($id, $json, $previewImage = false)
    {
        if (empty($this->tokenKey)) {
            throw new ClientAuthenticationException("No token found. Cannot update a LOM description without a token.");
        }

        // @todo DRYer, merge with postDescription() logic.
        $params = [
            'multipart' => [
                [
                    'name'     => 'description',
                    'contents' => $json,
                ],
            ],
        ];

        if ($previewImage) {
            if (file_exists($previewImage) && is_readable($previewImage)) {
                $params['multipart'][] = [
                    'name'     => 'previewImage',
                    'contents' => fopen($previewImage, 'r'),
                ];
            } else {
                throw new \RuntimeException(sprintf("File %s does not exist, or is not readable.", $previewImage));
            }
        }

        try {
            $response = $this->put("/description/" . urlencode($id), $params);
            return json_decode($response->getBody(), true);
        } catch(GuzzleRequestException $e) {
            throw new ClientRequestException(sprintf("Put request to /description/$id failed. Status: %s. Error message: %s", $e->getCode(), $e->getMessage()));
        }
    }

    /**
     * @{inheritdoc}
     */
    public function deleteDescription($id)
    {
        if (empty($this->tokenKey)) {
            throw new ClientAuthenticationException("No token found. Cannot delete a LOM description without a token.");
        }

        try {
            $response = $this->delete("/description/" . urlencode($id));
            return json_decode($response->getBody(), true);
        } catch(GuzzleRequestException $e) {
            throw new ClientRequestException(sprintf("Delete request to /description/$id failed. Status: %s. Error message: %s", $e->getCode(), $e->getMessage()));
        }
    }

}

