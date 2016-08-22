<?php

/**
 * @file
 * Contains \Educa\DSB\Client\ApiClient\AbstractClient.
 */

namespace Educa\DSB\Client\ApiClient;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\Psr7\Response;

abstract class AbstractClient implements ClientInterface
{

    protected $apiUrl;
    protected $username;
    protected $privateKeyPath;
    protected $privateKeyPassphrase;
    protected $tokenKey;
    protected $client;
    protected $requestHeaders = array();
    protected $lastResponseHeaders = array();

    /**
     * Constructor.
     *
     * @param string $apiUrl
     *    The URL to query for the national catalog.
     * @param string $username
     *    The username for authentication.
     * @param string $privateKeyPath
     *    The path to the private key file for authentication.
     * @param string $privateKeyPassphrase
     *    (optional) A passphrase to use for the private key.
     */
    public function __construct($apiUrl, $username, $privateKeyPath, $privateKeyPassphrase = null)
    {
        $this->apiUrl = rtrim($apiUrl, '/');
        $this->username = $username;
        $this->privateKeyPath = $privateKeyPath;
        $this->privateKeyPassphrase = $privateKeyPassphrase;
        $this->client = new GuzzleClient();
    }

    /**
     * Set the HTTP client.
     *
     * By default, a simple GuzzleHttp\Client is used. But it is possible to
     * override this property and use another client class.
     *
     * @param GuzzleHttp\ClientInterface $client
     */
    public function setClient(GuzzleClientInterface $client)
    {
        $this->client = $client;
        return $this;
    }

    /**
     * Get the HTTP client.
     *
     * @return GuzzleHttp\ClientInterface $client
     *
     * @codeCoverageIgnore
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Add a request header.
     *
     * @param string $name
     * @param string $value
     */
    public function addRequestHeader($name, $value)
    {
        $this->requestHeaders[$name] = $value;
        return $this;
    }

    /**
     * Set the headers to send with the request.
     *
     * @param array $headers
     *    An array of header values, keyed by header name. For example:
     *    - X-DSB-TRACK-ID: 2387dsfjhsdf87234
     */
    public function setRequestHeaders($headers)
    {
        $this->requestHeaders = $headers;
        return $this;
    }

    /**
     * Get the headers to send with the request.
     *
     * @return array
     */
    public function getRequestHeaders()
    {
        return $this->requestHeaders;
    }

    /**
     * Set the headers received with the last response.
     *
     * @param GuzzleHttp\Message\Response $response
     */
    public function setLastResponseHeaders($response)
    {
        $this->lastResponseHeaders = $response->getHeaders();
        return $this;
    }

    /**
     * Get the headers received with the last response.
     *
     * @return array
     */
    public function getLastResponseHeaders()
    {
        return $this->lastResponseHeaders;
    }

    /**
     * Make an HTTP POST request.
     *
     * This method simply proxies to the GuzzleHttp\Client::post() method, but
     * simplifies the call a little bit. If we have an identification token, we
     * will pass it automatically using a X-TOKEN-KEY header; there's no need to
     * pass it using $options.
     *
     * @param string $path
     *    The path to make the request to. This should not include the API URL,
     *    and should start with a slash.
     * @param array $options
     *    (optional) An array of options. See GuzzleHttp\Client::post() for more
     *    information.
     *
     * @return GuzzleHttp\Message\Response
     *    The response object.
     */
    public function post($path, array $options = array())
    {
        $options = $this->preProcessHeaders($options);
        try {
            $response = $this->client->post($this->apiUrl . $path, $options);
        } catch (RequestException $e) {
            // Catch all 4XX errors
            $response =  $e->getResponse();
        }

        // If the response is null, the service is unavailable. Create a new
        // response object, which will mimick a 503 response.
        if (!$response) {
            $response = new Response(503);
        }

        $this->setLastResponseHeaders($response);
        return $response;
    }

    /**
     * Make an HTTP PUT request.
     *
     * This method simply proxies to the GuzzleHttp\Client::put() method, but
     * simplifies the call a little bit. If we have an identification token, we
     * will pass it automatically using a X-TOKEN-KEY header; there's no need to
     * pass it using $options.
     *
     * @param string $path
     *    The path to make the request to. This should not include the API URL,
     *    and should start with a slash.
     * @param array $options
     *    (optional) An array of options. See GuzzleHttp\Client::put() for more
     *    information.
     *
     * @return GuzzleHttp\Message\Response
     *    The response object.
     */
    public function put($path, array $options = array())
    {
        $options = $this->preProcessHeaders($options);
        try {
            $response = $this->client->put($this->apiUrl . $path, $options);
        } catch (RequestException $e) {
            // Catch all 4XX errors
            $response =  $e->getResponse();
        }

        // If the response is null, the service is unavailable. Create a new
        // response object, which will mimick a 503 response.
        if (!$response) {
            $response = new Response(503);
        }

        $this->setLastResponseHeaders($response);
        return $response;
    }
    /**
     * Make an HTTP GET request.
     *
     * This method simply proxies to the GuzzleHttp\Client::get() method, but
     * simplifies the call a little bit. If we have an identification token, we
     * will pass it automatically using a X-TOKEN-KEY header; there's no need to
     * pass it using $options.
     *
     * @param string $path
     *    The path to make the request to. This should not include the API URL,
     *    and should start with a slash.
     * @param array $options
     *    (optional) An array of options. See GuzzleHttp\Client::get() for more
     *    information.
     *
     * @return GuzzleHttp\Message\Response
     *    The response object.
     */
    public function get($path, array $options = array())
    {
        $options = $this->preProcessHeaders($options);
        try {
            $response = $this->client->get($this->apiUrl . $path, $options);
        } catch (RequestException $e) {
            // Catch all 4XX errors
            $response =  $e->getResponse();
        }

        // If the response is null, the service is unavailable. Create a new
        // response object, which will mimick a 503 response.
        if (!$response) {
            $response = new Response(503);
        }

        $this->setLastResponseHeaders($response);
        return $response;
    }

    /**
     * Make an HTTP DELETE request.
     *
     * This method simply proxies to the GuzzleHttp\Client::delete() method, but
     * simplifies the call a little bit. If we have an identification token, we
     * will pass it automatically using a X-TOKEN-KEY header; there's no need to
     * pass it using $options.
     *
     * @param string $path
     *    The path to make the request to. This should not include the API URL,
     *    and should start with a slash.
     * @param array $options
     *    (optional) An array of options. See GuzzleHttp\Client::delete() for
     *    more information.
     *
     * @return GuzzleHttp\Message\Response
     *    The response object.
     */
    public function delete($path, array $options = array())
    {
        $options = $this->preProcessHeaders($options);
        try {
            $response = $this->client->delete($this->apiUrl . $path, $options);
        } catch (RequestException $e) {
            // Catch all 4XX errors
            $response =  $e->getResponse();
        }

        // If the response is null, the service is unavailable. Create a new
        // response object, which will mimick a 503 response.
        if (!$response) {
            $response = new Response(503);
        }

        $this->setLastResponseHeaders($response);
        return $response;
    }

    /**
     * Pre-process the request headers.
     *
     * This allows the class to add information like the access token header.
     *
     * @param array $options
     *
     * @return array
     */
    protected function preProcessHeaders($options)
    {
        if (!empty($this->tokenKey)) {
            $options['headers']['X-TOKEN-KEY'] = $this->tokenKey;
        }
        if (!empty($this->requestHeaders)) {
            foreach ($this->requestHeaders as $key => $value) {
                $options['headers'][$key] = $value;
            }
        }
        return $options;
    }

}
