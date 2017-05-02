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
     * @param array $useFacets = array()
     * @param array $filters = array()
     * @param array $additionalFields = array()
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
     * @param array $filters = array()
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
     * @deprecated Use ::getDescription() instead.
     *
     * @param string $lomId
     *
     * @return object
     *    The result object.
     */
    public function loadDescription($lomId);

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
    public function getDescription($lomId);

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
     * @param array $catalogs
     *    (optional) A list of catalogs to publish to.
     * @param string|false $previewImage
     *    (optional) A path to the preview image to upload in the same payload.
     *    Defaults to false.
     *
     * @return array
     *    The creation result, as returned by the REST API.
     */
    public function postDescription($json, $catalogs = array(), $previewImage = false);

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
     * @param array $catalogs
     *    (optional) A list of catalogs to publish to.
     *
     * @return array
     *    The update result, as returned by the REST API.
     */
    public function putDescription($id, $json, $catalogs = array());

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
     * @deprecated Use ::getOntologyData() instead.
     *
     * @return array
     *    The Ontology data.
     */
    public function loadOntologyData($type = 'list', array $vocabularyIds = null);

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
    public function getOntologyData($type = 'list', array $vocabularyIds = null);

    /**
     * Load content partner data.
     *
     * @throws \Educa\DSB\Client\ApiClient\ClientAuthenticationException
     *    If not authenticated, will throw an exception. If not authorized, will
     *    also throw an exception.
     * @throws \Educa\DSB\Client\ApiClient\ClientRequestException
     *    If the request fails, will throw an exception.
     *
     * @deprecated Use ::getPartner() instead.
     *
     * @return array
     *    The list of content partners.
     */
    public function loadPartners();

    /**
     * Load a single content partner data.
     *
     * @throws \Educa\DSB\Client\ApiClient\ClientAuthenticationException
     *    If not authenticated, will throw an exception. If not authorized, will
     *    also throw an exception.
     * @throws \Educa\DSB\Client\ApiClient\ClientRequestException
     *    If the request fails, will throw an exception.
     *
     * @deprecated Use ::getPartner() instead.
     *
     * @param string $partner
     *    The username of the partner, usually an email address.
     *
     * @return array
     *    The data of the content partner.
     */
    public function loadPartner($partner);

    /**
     * Load a single, or all, content partner data.
     *
     * @throws \Educa\DSB\Client\ApiClient\ClientAuthenticationException
     *    If not authenticated, will throw an exception. If not authorized, will
     *    also throw an exception.
     * @throws \Educa\DSB\Client\ApiClient\ClientRequestException
     *    If the request fails, will throw an exception.
     *
     * @param string $partner
     *    (optional) If only a single partner must be loaded, pass its username,
     *    usually an email address. If no username is given, all partners will
     *    be loaded.
     *
     * @return array
     *    The data of the content partner, keyed by partner username. If only
     *    a single partner was requested, the data will not be keyed by its name
     *    but will be returned directly.
     */
    public function getPartner($partner = null);

    /**
     * Update a single content partner data.
     *
     * @throws \Educa\DSB\Client\ApiClient\ClientAuthenticationException
     *    If not authenticated, will throw an exception. If not authorized, will
     *    also throw an exception.
     * @throws \Educa\DSB\Client\ApiClient\ClientRequestException
     *    If the request fails, will throw an exception.
     *
     * @param string $partner
     *    The username of the partner, usually an email address.$
     * @param string $json
     *    The data to store, in JSON format. Refer to the official documentation
     *    for more information on how to format this array.
     *
     * @return array
     *    The updated data of the content partner.
     */
    public function putPartner($partner, $json);

    /**
     * Load partner statistics.
     *
     * @throws \Educa\DSB\Client\ApiClient\ClientAuthenticationException
     *    If not authenticated, will throw an exception. If not authorized, will
     *    also throw an exception.
     * @throws \Educa\DSB\Client\ApiClient\ClientRequestException
     *    If the request fails, will throw an exception.
     *
     * @deprecated Use ::getPartnerStatistics() instead.
     *
     * @param string $partnerId
     *    The partner identifier, usually an email address.
     * @param string $from
     *    The start date for the statistics. Must use the "Y-m-d" format.
     * @param string $to
     *    The end date for the statistics. Must use the "Y-m-d" format.
     * @param string $aggregationMethod
     *    (optional) The aggregation method for the views. Can be either "day",
     *    "month" or "year". Defaults to "day".
     * @param string $lomId
     *    (optional) The LOM ID to filter the results by. Defaults to null,
     *    which means all description statistics are returned.
     * @param int $limit
     *    (optional) The limit of statistics to return. Defaults to null.
     * @param int $offset
     *    (optional) The offset of the statistics. Used for pagination. Defaults
     *    to null.
     *
     * @return array
     *    The list of content partners.
     */
    public function loadPartnerStatistics($partnerId, $from, $to, $aggregationMethod = 'day', $lomId = null, $limit = null, $offset = null);

    /**
     * Load partner statistics.
     *
     * @throws \Educa\DSB\Client\ApiClient\ClientAuthenticationException
     *    If not authenticated, will throw an exception. If not authorized, will
     *    also throw an exception.
     * @throws \Educa\DSB\Client\ApiClient\ClientRequestException
     *    If the request fails, will throw an exception.
     *
     * @param string $partnerId
     *    The partner identifier, usually an email address.
     * @param string $from
     *    The start date for the statistics. Must use the "Y-m-d" format.
     * @param string $to
     *    The end date for the statistics. Must use the "Y-m-d" format.
     * @param string $aggregationMethod
     *    (optional) The aggregation method for the views. Can be either "day",
     *    "month" or "year". Defaults to "day".
     * @param string $lomId
     *    (optional) The LOM ID to filter the results by. Defaults to null,
     *    which means all description statistics are returned.
     * @param int $limit
     *    (optional) The limit of statistics to return. Defaults to null.
     * @param int $offset
     *    (optional) The offset of the statistics. Used for pagination. Defaults
     *    to null.
     *
     * @return array
     *    The list of content partners.
     */
    public function getPartnerStatistics($partnerId, $from, $to, $aggregationMethod = 'day', $lomId = null, $limit = null, $offset = null);

    /**
     * Upload a file.
     *
     * Upload a file. Check the official REST API documentation for the response
     * format.
     *
     * @throws \Educa\DSB\Client\ApiClient\ClientAuthenticationException
     *    If not authenticated, will throw an exception. If not authorized, will
     *    also throw an exception.
     * @throws \Educa\DSB\Client\ApiClient\ClientRequestException
     *    If the request fails, will throw an exception.
     * @throws \RuntimeException
     *    If file is not readable, or doesn't exist, will throw an exception.
     *
     * @deprecated Use ::postFile() instead.
     *
     * @param string $filePath
     *    The path of the file to upload.
     *
     * @return array
     *    The creation result, as returned by the REST API.
     */
    public function uploadFile($filePath);

    /**
     * Upload a file.
     *
     * Upload a file. Check the official REST API documentation for the response
     * format.
     *
     * @throws \Educa\DSB\Client\ApiClient\ClientAuthenticationException
     *    If not authenticated, will throw an exception. If not authorized, will
     *    also throw an exception.
     * @throws \Educa\DSB\Client\ApiClient\ClientRequestException
     *    If the request fails, will throw an exception.
     * @throws \RuntimeException
     *    If file is not readable, or doesn't exist, will throw an exception.
     *
     * @param string $filePath
     *    The path of the file to upload.
     *
     * @return array
     *    The creation result, as returned by the REST API.
     */
    public function postFile($filePath);

    /**
     * Get suggestions for mapping a curriculum to another.
     *
     * The REST API provides suggestions for mapping a term from one curriculum
     * to another.
     *
     * @throws \Educa\DSB\Client\ApiClient\ClientAuthenticationException
     *    If not authenticated, will throw an exception. If not authorized, will
     *    also throw an exception.
     * @throws \Educa\DSB\Client\ApiClient\ClientRequestException
     *    If the request fails, will throw an exception.
     *
     * @param string $from
     *    The curriculum the mapped term belongs to.
     * @param string $to
     *    The curriculum the term should be mapped to.
     * @param string $termId
     *    The term identifier. Check the REST API documentation for more
     *    information, as some curricula have specific formats.
     *
     * @return array
     *    A list of suggestions, keyed by term ID.
     */
    public function getCurriculaMappingSuggestions($from, $to, $termId);

}
