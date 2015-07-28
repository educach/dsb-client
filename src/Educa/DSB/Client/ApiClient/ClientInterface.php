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
     * Load a description.
     *
     * @param string $lomId
     *
     * @return object
     *    The result object.
     */
    public function loadDescription($lomId);

    /**
     * Load Ontology data.
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

}
