<?php

/**
 * @file
 * Contains \Educa\DSB\Client\ApiClient\ClientInterface.
 *
 * Important note: all methods, except those that must return a value, should
 * return the class itself, so as to provide method chaining.
 */

namespace Educa\DSB\Client\ApiClient;

interface ClientInterface
{

    /**
     * Authenticate.
     *
     * Authenticate with the REST API. This will request an identification token,
     * which will be used for subsequent requests.
     *
     * @throws \Educa\DSB\Client\ApiClient\ClientAuthenticationException
     *    On authentication errors, will throw an exception.
     */
    public function authenticate();

    /**
     * Search.
     *
     * Perform a search request to the REST API.
     *
     * @throws \Educa\DSB\Client\ApiClient\ClientAuthenticationException
     *    If not authenticated, will throw an exception. If not authorized, will
     *    also throw an exception.
     * @throws \Educa\DSB\Client\ApiClient\ClientRequestException
     *    If the request fails, will throw an exception
     *
     * @param string $query = ''
     * @param string $useFacets = array()
     * @param string $filters = array()
     * @param string $additionalFields = array()
     * @param int $offset = 0
     * @param int $limit = 50
     * @param string $sortBy = 'random'
     *
     * @return object
     *    The result object. The result object has the following properties:
     *    - num_found: The number of results.
     *    - results: A list of result objects. Returned fields may vary depending
     *      on the passed parameters.
     *    - facets: A tree of facets.
     */
    public function search(
        $query = '',
        array $useFacets = array(),
        array $filters = array(),
        array $additionalFields = array(),
        $offset = 0,
        $limit = 50,
        $sortBy = 'random'
    );

    /**
     * Get suggestions.
     *
     * Fetch a list of suggestions for a given keyword.
     *
     * @throws \Educa\DSB\Client\ApiClient\ClientAuthenticationException
     *    If not authenticated, will throw an exception. If not authorized, will
     *    also throw an exception.
     * @throws \Educa\DSB\Client\ApiClient\ClientRequestException
     *    If the request fails, will throw an exception
     *
     * @param string $query = ''
     * @param string $filters = array()
     *
     * @return array
     *    A list of suggestions. Each suggestion is an object with the following
     *    properties:
     *    - suggestion: The actual suggestion
     *    - context: The context from which the suggestion is taken, usually a
     *      whole sentence, or even paragraph.
     */
    public function getSuggestions($query = '', array $filters = array());

    /**
     * Load a description.
     *
     * @throws \Educa\DSB\Client\ApiClient\ClientAuthenticationException
     *    If not authenticated, will throw an exception. If not authorized, will
     *    also throw an exception.
     * @throws \Educa\DSB\Client\ApiClient\ClientRequestException
     *    If the request fails, will throw an exception
     *
     * @param string $lomId
     *
     * @return object
     *    The result object.
     */
    public function loadDescription($lomId);

    /**
     * Validate a description.
     *
     * Validate a description JSON representation. Check the official REST API
     * documentation for the response format.
     *
     * @throws \Educa\DSB\Client\ApiClient\ClientAuthenticationException
     *    If not authenticated, will throw an exception. If not authorized, will
     *    also throw an exception.
     * @throws \Educa\DSB\Client\ApiClient\ClientRequestException
     *    If the request fails, will throw an exception
     *
     * @param string $json
     *    The JSON representation of the description to validate.
     *
     * @return array
     *    The validation result, as returned by the REST API.
     */
    public function validateDescription($json);

    /**
     * Post a description.
     *
     * Create a new description. Check the official REST API
     * documentation for the response format.
     *
     * @throws \Educa\DSB\Client\ApiClient\ClientAuthenticationException
     *    If not authenticated, will throw an exception. If not authorized, will
     *    also throw an exception.
     * @throws \Educa\DSB\Client\ApiClient\ClientRequestException
     *    If the request fails, will throw an exception
     *
     * @param string $json
     *    The JSON representation of the description to create.
     *
     * @return array
     *    The creation result, as returned by the REST API.
     */
    public function postDescription($json, $previewImage = false);

    /**
     * Put a description.
     *
     * Update a description. Check the official REST API
     * documentation for the response format.
     *
     * @throws \Educa\DSB\Client\ApiClient\ClientAuthenticationException
     *    If not authenticated, will throw an exception. If not authorized, will
     *    also throw an exception.
     * @throws \Educa\DSB\Client\ApiClient\ClientRequestException
     *    If the request fails, will throw an exception
     *
     * @param string $id
     *    The ID of the description.
     * @param string $json
     *    The JSON representation of the description to create.
     *
     * @return array
     *    The update result, as returned by the REST API.
     */
    public function putDescription($id, $json, $previewImage = false);

    /**
     * Delete a description.
     *
     * Remove a description. Check the official REST API documentation
     * for the response format.
     *
     * @throws \Educa\DSB\Client\ApiClient\ClientAuthenticationException
     *    If not authenticated, will throw an exception. If not authorized, will
     *    also throw an exception.
     * @throws \Educa\DSB\Client\ApiClient\ClientRequestException
     *    If the request fails, will throw an exception
     *
     * @param string $id
     *    The ID of the description.
     *
     * @return array
     *    The deletion result, as returned by the REST API.
     */
    public function deleteDescription($id);

    /**
     * Load Ontology data.
     *
     * @throws \Educa\DSB\Client\ApiClient\ClientAuthenticationException
     *    If not authenticated, will throw an exception. If not authorized, will
     *    also throw an exception.
     * @throws \Educa\DSB\Client\ApiClient\ClientRequestException
     *    If the request fails, will throw an exception
     *
     * @param string $type
     *    (optional) The format the data must be returned in. Check the official
     *    REST API documentation for the available types. Defaults to 'list'.
     * @param array $vocabularyIds
     *    (optional) The vocabularies to load. Check the official REST API
     *    documentation for the available vocabularies.
     *
     * @return array
     *    The Ontology data.
     */
    public function loadOntologyData($type = 'list', array $vocabularyIds = null);

    /**
     * Load content partner data.
     *
     * @throws \Educa\DSB\Client\ApiClient\ClientAuthenticationException
     *    If not authenticated, will throw an exception. If not authorized, will
     *    also throw an exception.
     * @throws \Educa\DSB\Client\ApiClient\ClientRequestException
     *    If the request fails, will throw an exception
     *
     * @return array
     *    The list of content partners.
     */
    public function loadPartners();

}
