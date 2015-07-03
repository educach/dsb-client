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
use Educa\DSB\Client\ApiClient\ClientRequestException;

class ClientV2Test extends \PHPUnit_Framework_TestCase
{

    /**
     * Test authentication.
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
            FIXTURES_DIR . '/user/privatekey_nopassphrase.pem'
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
     * Test searching.
     */
    public function testSearch()
    {
        // Prepare a new client, with a history subscriber.
        $guzzle = $this->getGuzzleTestClient([
            new Response(200, [], Stream::factory('{"token":"asjhasd987asdhasd87"}')),
            new Response(200),
            new Response(400),
            new Response(204),
        ]);
        $history = new History();
        $guzzle->getEmitter()->attach($history);

        // Prepare a client.
        $client = new ClientV2(
            'http://localhost',
            'user@site.com',
            FIXTURES_DIR . '/user/privatekey_nopassphrase.pem'
        );
        $client->setClient($guzzle);

        // Performing a search without being authenticated throws an error.
        try {
            $client->search();
            $this->fail("Performing a search without being authenticated should throw an exception.");
        } catch(ClientAuthenticationException $e) {
            $this->assertTrue(true, "Performing a search without being authenticated throws an exception.");
        }

        // Performing a search while authenticated doesn't throw an error.
        try {
            $client->authenticate();
            $client->search(
                'query',
                ['facet'],
                ['filter' => ['value']],
                ['additional field'],
                20,
                30,
                'sort'
            );
            $this->assertTrue(true, "Performing a search while being authenticated does not throw an exception.");
        } catch(ClientAuthenticationException $e) {
            $this->fail("Performing a search while being authenticated should not throw an exception.");
        }

        // Check the sent parameters.
        $requestData = $history->getLastRequest()->getQuery();
        $this->assertEquals(
            'query',
            $requestData['query'],
            "The query was correctly set."
        );
        $this->assertEquals(
            '["facet"]',
            $requestData['facets'],
            "The facets were correctly set."
        );
        $this->assertEquals(
            '{"filter":["value"]}',
            $requestData['filters'],
            "The filters were correctly set."
        );
        $this->assertEquals(
            '["additional field"]',
            $requestData['additionalFields'],
            "The additional fields were correctly set."
        );
        $this->assertEquals(
            20,
            $requestData['offset'],
            "The offset was correctly set."
        );
        $this->assertEquals(
            30,
            $requestData['limit'],
            "The limit was correctly set."
        );
        $this->assertEquals(
            'sort',
            $requestData['sortBy'],
            "The sortBy parameter was correctly set."
        );

        // A search that fails throws an exception.
        try {
            $client->search();
            $this->fail("A search that fails should throw an exception.");
        } catch(ClientRequestException $e) {
            $this->assertTrue(true, "A search that fails throws an exception.");
        }

        // A response status different from 200 while performing a search throws
        // an exception.
        try {
            $client->search();
            $this->fail("A response status different from 200 while performing a search should throw an exception.");
        } catch(ClientRequestException $e) {
            $this->assertTrue(true, "A response status different from 200 while performing a search throws an exception.");
        }
    }

    /**
     * Test loading a description.
     */
    public function testGetDescription()
    {
        // Prepare a new client.
        $guzzle = $this->getGuzzleTestClient([
            new Response(200, [], Stream::factory('{"token":"asjhasd987asdhasd87"}')),
            new Response(200),
            new Response(400),
            new Response(304),
        ]);

        // Prepare a client.
        $client = new ClientV2(
            'http://localhost',
            'user@site.com',
            FIXTURES_DIR . '/user/privatekey_nopassphrase.pem'
        );
        $client->setClient($guzzle);

        // Loading a description without being authenticated throws an error.
        try {
            $client->loadDescription('id');
            $this->fail("Loading a description without being authenticated should throw an exception.");
        } catch(ClientAuthenticationException $e) {
            $this->assertTrue(true, "Loading a description without being authenticated throws an exception.");
        }

        // Loading a description while authenticated doesn't throw an error.
        try {
            $client->authenticate();
            $client->loadDescription('id');
            $this->assertTrue(true, "Loading a description while being authenticated does not throw an exception.");
        } catch(ClientAuthenticationException $e) {
            $this->fail("Loading a description while being authenticated should not throw an exception.");
        }

        // Failing to load a description throws an exception.
        try {
            $client->loadDescription('id');
            $this->fail("Failing to load a description should throw an exception.");
        } catch(ClientRequestException $e) {
            $this->assertTrue(true, "Failing to load a description throws an exception.");
        }

        // A status different from 200 while loading a description throws an
        // exception.
        try {
            $client->loadDescription('id');
            $this->fail("A status different from 200 while loading a description should throw an exception.");
        } catch(ClientRequestException $e) {
            $this->assertTrue(true, "A status different from 200 while loading a description throws an exception.");
        }
    }

    /**
     * Test loading Ontology data.
     */
    public function testGetOntology()
    {
        // Prepare a new client.
        $guzzle = $this->getGuzzleTestClient([
            new Response(200, [], Stream::factory('{"token":"asjhasd987asdhasd87"}')),
            new Response(200),
            new Response(400),
            new Response(304),
        ]);

        // Prepare a client.
        $client = new ClientV2(
            'http://localhost',
            'user@site.com',
            FIXTURES_DIR . '/user/privatekey_nopassphrase.pem'
        );
        $client->setClient($guzzle);

        // Loading Ontology data without being authenticated throws an error.
        try {
            $client->loadOntologyData(['id']);
            $this->fail("Loading Ontology data without being authenticated should throw an exception.");
        } catch(ClientAuthenticationException $e) {
            $this->assertTrue(true, "Loading Ontology data without being authenticated throws an exception.");
        }

        // Loading Ontology data while authenticated doesn't throw an error.
        try {
            $client->authenticate();
            $client->loadOntologyData(['id']);
            $this->assertTrue(true, "Loading Ontology data while being authenticated does not throw an exception.");
        } catch(ClientAuthenticationException $e) {
            $this->fail("Loading Ontology data while being authenticated should not throw an exception.");
        }

        // Failing to load Ontology data throws an exception.
        try {
            $client->loadOntologyData(['id']);
            $this->fail("Failing to load Ontology data should throw an exception.");
        } catch(ClientRequestException $e) {
            $this->assertTrue(true, "Failing to load Ontology data throws an exception.");
        }

        // A status different from 200 while loading Ontology data throws an
        // exception.
        try {
            $client->loadOntologyData(['id']);
            $this->fail("A status different from 200 while loading Ontology data should throw an exception.");
        } catch(ClientRequestException $e) {
            $this->assertTrue(true, "A status different from 200 while loading Ontology data throws an exception.");
        }
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
