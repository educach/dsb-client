<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Tests\ApiClient\ClientV2Test.
 */

namespace Educa\DSB\Client\Tests\ApiClient;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Subscriber\Mock;
use GuzzleHttp\Subscriber\History;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use Educa\DSB\Client\ApiClient\ClientV2;
use Educa\DSB\Client\ApiClient\ClientAuthenticationException;

class ClientV2Test extends \PHPUnit_Framework_TestCase
{

    /**
     * Test authentication.
     *
     * Test authenticating a user.
     */
    public function testAuthentication()
    {
        // Test authentication.
        // Giving an non-existent private key throws an error.
        $client = new ClientV2('http://localhost', 'user@site.com', 'not-exist.pem');
        try {
            $client->authenticate();
            $this->fail("Using a non-existent private key should throw an exception.");
        } catch(ClientAuthenticationException $e) {
            $this->assertTrue(true, "Using a non-existent private key throws an exception.");
        }

        // Giving a wrong password for the private key throws an error.
        $client = new ClientV2(
            'http://localhost',
            'user@site.com',
            FIXTURES_DIR . '/user/privatekey_passphrase.pem',
            'incorrect passphrase'
        );
        try {
            $client->authenticate();
            $this->fail("Passing an incorrect passphrase for the private key should throw an exception.");
        } catch(ClientAuthenticationException $e) {
            $this->assertTrue(true, "Passing an incorrect passphrase for the private key throws an exception.");
        }

        // Prepare some mocked responses.
        $guzzle = $this->getGuzzleTestClient([
            new Response(200, [], Stream::factory('{"not":"what you expect"}')),
            new Response(400),
            new Response(204),
            new Response(200, [], Stream::factory('{"token":"asjhasd987asdhasd87"}')),
        ]);

        // Prepare a client with a correct passphrase.
        $client = new ClientV2(
            'http://localhost',
            'user@site.com',
            FIXTURES_DIR . '/user/privatekey_passphrase.pem',
            'passphrase'
        );
        $client->setClient($guzzle);

        // A successful authentication, but that doesn't return a token throws
        // an error.
        try {
            $client->authenticate();
            $this->fail("Receiving a successful authentication response, but with no token, throws an exception.");
        } catch(ClientAuthenticationException $e) {
            $this->assertTrue(true, "Receiving a successful authentication response, but with no token, should throw an exception.");
        }

        // An unsuccessful authentication throws an error.
        try {
            $client->authenticate();
            $this->fail("An unsuccessful authentication throws an exception.");
        } catch(ClientAuthenticationException $e) {
            $this->assertTrue(true, "An unsuccessful authentication should throw an exception.");
        }

        // A status different from 200 throws an exception.
        try {
            $client->authenticate();
            $this->fail("A status different from 200 throws an exception.");
        } catch(ClientAuthenticationException $e) {
            $this->assertTrue(true, "A status different from 200 should throw an exception.");
        }

        // A successful authentication does not throw an exception.
        try {
            $client->authenticate();
            $this->assertTrue(true, "A successful authentication does not throw an exception.");
        } catch(ClientAuthenticationException $e) {
            $this->fail("A successful authentication should not throw an exception.");
        }

        // Check that the authentication request sent the correct data. Prepare
        // a new client, but with a history subscriber.
        $guzzle = $this->getGuzzleTestClient([
            new Response(200, [], Stream::factory('{"token":"asjhasd987asdhasd87"}')),
            new Response(200, [], Stream::factory('{"token":"asjhasd987asdhasd87"}')),
        ]);
        $history = new History();
        $guzzle->getEmitter()->attach($history);

        // Prepare a client with the correct passphrase.
        $client = new ClientV2(
            'http://localhost',
            'user@site.com',
            FIXTURES_DIR . '/user/privatekey_passphrase.pem',
            'passphrase'
        );
        $client->setClient($guzzle);

        // Authenticate.
        $client->authenticate();

        // Get the request.
        $requestData = array();
        parse_str($history->getLastRequest()->getBody(), $requestData);

        // Check the data was correctly encrypted.
        $signature = base64_decode($requestData['signature']);
        $vector = $requestData['vector'];
        $publicKey = openssl_get_publickey(
            file_get_contents(FIXTURES_DIR . '/user/publickey_passphrase.pem')
        );
        $verify = openssl_verify($vector, $signature, $publicKey);
        openssl_free_key($publicKey);
        $this->assertTrue(!!$verify, "Data signed with private key using a passphrase was correctly verified.");

        // Prepare a client, no passphrase this time.
        $client = new ClientV2(
            'http://localhost',
            'user@site.com',
            FIXTURES_DIR . '/user/privatekey_nopassphrase.pem',
            'passphrase'
        );
        $client->setClient($guzzle);

        // Authenticate.
        $client->authenticate();

        // Get the request.
        $requestData = array();
        parse_str($history->getLastRequest()->getBody(), $requestData);

        // Check the data was correctly encrypted.
        $signature = base64_decode($requestData['signature']);
        $vector = $requestData['vector'];
        $publicKey = openssl_get_publickey(
            file_get_contents(FIXTURES_DIR . '/user/publickey_nopassphrase.pem')
        );
        $verify = openssl_verify($vector, $signature, $publicKey);
        openssl_free_key($publicKey);
        $this->assertTrue(!!$verify, "Data signed with private key without a passphrase was correctly verified.");
    }

    /**
     * Get a test client.
     *
     * This returns a GuzzleHttp\Client instance, with a mocked HTTP responses.
     *
     * @param array $responses
     *    A list of GuzzleHttp\Message response objects, to be used by the
     *    mocked test client. Responses will be returned in the order specified
     *
     * @return Educa\DSB\Client\ApiClient\ClientV2
     */
    protected function getGuzzleTestClient(array $responses)
    {
        $mock = new Mock($responses);
        $guzzle = new GuzzleClient();
        $guzzle->getEmitter()->attach($mock);

        return $guzzle;
    }
}
