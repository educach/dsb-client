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

    protected $username;
    protected $privateKeyPath;
    protected $privateKeyPassphrase;
    protected $tokenKey;
    protected $client;

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
        $this->username = $username;
        $this->privateKeyPath = $privateKeyPath;
        $this->privateKeyPassphrase = $privateKeyPassphrase;
        $this->client = new GuzzleClient([
            'base_uri' => $apiUrl,
        ]);
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
     * Make an HTTP POST request.
     *
     * This method simply proxies to the GuzzleHttp\Client::post() method, but
     * simplifies the call a little bit. If we have an identification token, we
     * will pass it automatically using a X-TOKEN-KEY header; there's no need to
     * pass it using $options.
     *
     * @param string $path
     *    The path to make the request to. This should not include the API URL.
     * @param array $options
     *    (optional) An array of options. See GuzzleHttp\Client::post() for more
     *    information.
     *
     * @return GuzzleHttp\Message\Response
     *    The response object.
     */
    public function post($path, array $options = array())
    {
        if (!empty($this->tokenKey)) {
            $options['headers']['X-TOKEN-KEY'] = $this->tokenKey;
        }
        try {
            $response = $this->client->post($path, $options);
        } catch (RequestException $e) {
            // Catch all 4XX errors
            $response =  $e->getResponse();
        }

        // If the response is null, the service is unavailable. Create a new
        // response object, which will mimick a 503 response.
        if (!$response) {
            $response = new Response(503);
        }

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
     *    The path to make the request to. This should not include the API URL.
     * @param array $options
     *    (optional) An array of options. See GuzzleHttp\Client::put() for more
     *    information.
     *
     * @return GuzzleHttp\Message\Response
     *    The response object.
     */
    public function put($path, array $options = array())
    {
        if (!empty($this->tokenKey)) {
            $options['headers']['X-TOKEN-KEY'] = $this->tokenKey;
        }
        try {
            $response = $this->client->put($path, $options);
        } catch (RequestException $e) {
            // Catch all 4XX errors
            $response =  $e->getResponse();
        }

        // If the response is null, the service is unavailable. Create a new
        // response object, which will mimick a 503 response.
        if (!$response) {
            $response = new Response(503);
        }

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
     *    The path to make the request to. This should not include the API URL.
     * @param array $options
     *    (optional) An array of options. See GuzzleHttp\Client::get() for more
     *    information.
     *
     * @return GuzzleHttp\Message\Response
     *    The response object.
     */
    public function get($path, array $options = array())
    {
        if (!empty($this->tokenKey)) {
            $options['headers']['X-TOKEN-KEY'] = $this->tokenKey;
        }
        try {
            $response = $this->client->get($path, $options);
        } catch (RequestException $e) {
            // Catch all 4XX errors
            $response =  $e->getResponse();
        }

        // If the response is null, the service is unavailable. Create a new
        // response object, which will mimick a 503 response.
        if (!$response) {
            $response = new Response(503);
        }

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
     *    The path to make the request to. This should not include the API URL.
     * @param array $options
     *    (optional) An array of options. See GuzzleHttp\Client::delete() for
     *    more information.
     *
     * @return GuzzleHttp\Message\Response
     *    The response object.
     */
    public function delete($path, array $options = array())
    {
        if (!empty($this->tokenKey)) {
            $options['headers']['X-TOKEN-KEY'] = $this->tokenKey;
        }
        try {
            $response = $this->client->delete($path, $options);
        } catch (RequestException $e) {
            // Catch all 4XX errors
            $response =  $e->getResponse();
        }

        // If the response is null, the service is unavailable. Create a new
        // response object, which will mimick a 503 response.
        if (!$response) {
            $response = new Response(503);
        }

        return $response;
    }

}
