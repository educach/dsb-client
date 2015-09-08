<?php

/**
 * @file
 * Contains \Educa\DSB\Client\ApiClient\AbstractClient.
 */

namespace Educa\DSB\Client\ApiClient;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;

abstract class AbstractClient implements ClientInterface
{

    protected $apiUrl;
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
        // For clarity, code uses URLs with a prefixing slash. If the URL has a
        // trailing slash, remove it.
        // @codeCoverageIgnoreStart
        if (preg_match('/\/$/', $apiUrl)) {
            $apiUrl = substr($apiUrl, 0, strlen($apiUrl)-1);
        }
        // @codeCoverageIgnoreEnd

        $this->apiUrl = $apiUrl;
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
            $response = $this->client->post($this->apiUrl . $path, $options);
        } catch (RequestException $e) {
            // Catch all 4XX errors
            $response =  $e->getResponse();
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
            $response = $this->client->get($this->apiUrl . $path, $options);
        } catch (RequestException $e) {
            // Catch all 4XX errors
            $response =  $e->getResponse();
        }
        return $response;
    }

}
