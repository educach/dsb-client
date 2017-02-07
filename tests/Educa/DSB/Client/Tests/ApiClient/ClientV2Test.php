<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Tests\ApiClient\ClientV2Test.
 */

namespace Educa\DSB\Client\Tests\ApiClient;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
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
            new Response(200, [], Psr7\stream_for('{"not":"what you expect"}')),
            new Response(400),
            new Response(204),
            new Response(200, [], Psr7\stream_for('{"token":"asjhasd987asdhasd87"}')),
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
            $this->assertEquals(
                400,
                $e->getCode(),
                "The HTTP status code is passed as the Exception code."
            );
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
        $transactions = array();
        $guzzle = $this->getGuzzleTestClient([
            new Response(200, [], Psr7\stream_for('{"token":"asjhasd987asdhasd87"}')),
            new Response(200, [], Psr7\stream_for('{"token":"asjhasd987asdhasd87"}')),
        ], $transactions);

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
        $transaction = end($transactions);
        parse_str((string) $transaction['request']->getBody(), $requestData);

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
        $transaction = end($transactions);
        parse_str((string) $transaction['request']->getBody(), $requestData);

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
        $transactions = array();
        $guzzle = $this->getGuzzleTestClient([
            new Response(200, [], Psr7\stream_for('{"token":"asjhasd987asdhasd87"}')),
            new Response(200),
            new Response(400),
            new Response(204),
        ], $transactions);

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
        $transaction = end($transactions);
        $requestData = array();
        parse_str($transaction['request']->getUri()->getQuery(), $requestData);

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
            new Response(200, [], Psr7\stream_for('{"token":"asjhasd987asdhasd87"}')),
            new Response(200, ['X-DSB-LOMID' => 'asdjkl89qwe798asdkj']),
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
            $this->assertEquals(
                ['X-DSB-LOMID' => ['asdjkl89qwe798asdkj']],
                $client->getLastResponseHeaders(),
                "The client contains the headers from the last response"
            );
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
     * Test fetching suggestions.
     */
    public function testGetSuggestions()
    {
        // Prepare a new client.
        $guzzle = $this->getGuzzleTestClient([
            new Response(200, [], Psr7\stream_for('{"token":"asjhasd987asdhasd87"}')),
            new Response(200, [], Psr7\stream_for('[{"suggestion":"Suggestion A","context":"The context of Suggestion A is this."},{"suggestion":"Suggestion B","context":"The context of Suggestion B is this."}]')),
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

        // Fetching suggestions without being authenticated throws an error.
        try {
            $client->getSuggestions('keyword');
            $this->fail("Fetching suggestions without being authenticated should throw an exception.");
        } catch(ClientAuthenticationException $e) {
            $this->assertTrue(true, "Fetching suggestions without being authenticated throws an exception.");
        }

        // Fetching suggestions while authenticated doesn't throw an error.
        try {
            $client->authenticate();
            $client->getSuggestions('keyword');
            $this->assertTrue(true, "Fetching suggestions while being authenticated does not throw an exception.");
        } catch(ClientAuthenticationException $e) {
            $this->fail("Fetching suggestions while being authenticated should not throw an exception.");
        }

        // Failing to fetch suggestions throws an exception.
        try {
            $client->getSuggestions('keyword');
            $this->fail("Failing to fetch suggestions should throw an exception.");
        } catch(ClientRequestException $e) {
            $this->assertTrue(true, "Failing to fetch suggestions throws an exception.");
        }

        // A status different from 200 while fetching suggestions throws an
        // exception.
        try {
            $client->getSuggestions('keyword');
            $this->fail("A status different from 200 while fetching suggestions should throw an exception.");
        } catch(ClientRequestException $e) {
            $this->assertTrue(true, "A status different from 200 while fetching suggestions throws an exception.");
        }
    }

    /**
     * Test loading Ontology data.
     */
    public function testGetOntology()
    {
        // Prepare a new client.
        $guzzle = $this->getGuzzleTestClient([
            new Response(200, [], Psr7\stream_for('{"token":"asjhasd987asdhasd87"}')),
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
            $client->loadOntologyData('list', ['id']);
            $this->fail("Loading Ontology data without being authenticated should throw an exception.");
        } catch(ClientAuthenticationException $e) {
            $this->assertTrue(true, "Loading Ontology data without being authenticated throws an exception.");
        }

        // Loading Ontology data while authenticated doesn't throw an error.
        try {
            $client->authenticate();
            $client->loadOntologyData('list', ['id']);
            $this->assertTrue(true, "Loading Ontology data while being authenticated does not throw an exception.");
        } catch(ClientAuthenticationException $e) {
            $this->fail("Loading Ontology data while being authenticated should not throw an exception.");
        }

        // Failing to load Ontology data throws an exception.
        try {
            $client->loadOntologyData('list', ['id']);
            $this->fail("Failing to load Ontology data should throw an exception.");
        } catch(ClientRequestException $e) {
            $this->assertTrue(true, "Failing to load Ontology data throws an exception.");
        }

        // A status different from 200 while loading Ontology data throws an
        // exception.
        try {
            $client->loadOntologyData('list', ['id']);
            $this->fail("A status different from 200 while loading Ontology data should throw an exception.");
        } catch(ClientRequestException $e) {
            $this->assertTrue(true, "A status different from 200 while loading Ontology data throws an exception.");
        }
    }

    /**
     * Test loading stats data.
     */
    public function testGetStats()
    {
        // Prepare a new client.
        $guzzle = $this->getGuzzleTestClient([
            new Response(200, [], Psr7\stream_for('{"token":"asjhasd987asdhasd87"}')),
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

        // Loading stats data without being authenticated throws an error.
        try {
            $client->loadPartnerStatistics('user@site.com', '2015', '2016');
            $this->fail("Loading stats data without being authenticated should throw an exception.");
        } catch(ClientAuthenticationException $e) {
            $this->assertTrue(true, "Loading stats data without being authenticated throws an exception.");
        }

        // Loading stats data while authenticated doesn't throw an error.
        try {
            $client->authenticate();
            $client->loadPartnerStatistics('user@site.com', '2015', '2016');
            $this->assertTrue(true, "Loading stats data while being authenticated does not throw an exception.");
        } catch(ClientAuthenticationException $e) {
            $this->fail("Loading stats data while being authenticated should not throw an exception.");
        }

        // Passing an incorrect aggregation method throws an exception.
        try {
            $client->loadPartnerStatistics('john@doe.com', '2015', '2016', 'dummy');
            $this->fail("Passing an incorrect aggregation method should throw an exception.");
        } catch(\InvalidArgumentException $e) {
            $this->assertTrue(true, "Passing an incorrect aggregation method throws an exception.");
        }

        // Passing an to date that is smaller than the from date throws an
        // exception.
        try {
            $client->loadPartnerStatistics('john@doe.com', '2016', '2015', 'dummy');
            $this->fail("Passing an to date that is smaller than the from date should throw an exception.");
        } catch(\InvalidArgumentException $e) {
            $this->assertTrue(true, "Passing an to date that is smaller than the from date throws an exception.");
        }
    }

    /**
     * Test loading mapping suggestions.
     */
    public function testGetMappingSuggestions()
    {
        // Prepare a new client.
        $guzzle = $this->getGuzzleTestClient([
            new Response(200, [], Psr7\stream_for('{"token":"asjhasd987asdhasd87"}')),
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

        // Fetching mapping suggestions without being authenticated throws an
        // error.
        try {
            $client->getCurriculaMappingSuggestions('per', 'lp21', 'term-1');
            $this->fail("Fetching mapping suggestions without being authenticated should throw an exception.");
        } catch(ClientAuthenticationException $e) {
            $this->assertTrue(true, "Fetching mapping suggestions without being authenticated throws an exception.");
        }

        // Fetching mapping suggestions while authenticated doesn't throw an
        // error.
        try {
            $client->authenticate();
            $client->getCurriculaMappingSuggestions('per', 'lp21', 'term-1');
            $this->assertTrue(true, "Fetching mapping suggestions while being authenticated does not throw an exception.");
        } catch(ClientAuthenticationException $e) {
            $this->fail("Fetching mapping suggestions while being authenticated should not throw an exception.");
        }
    }

    /**
     * Test creating a new description.
     */
    public function testPostDescription()
    {
        // Prepare a new client.
        $guzzle = $this->getGuzzleTestClient([
            new Response(200, [], Psr7\stream_for('{"token":"asjhasd987asdhasd87"}')),
            new Response(200),
            new Response(200),
            new Response(200),
            new Response(200),
            new Response(200),
        ]);

        // Prepare a client.
        $client = new ClientV2(
            'http://localhost',
            'user@site.com',
            FIXTURES_DIR . '/user/privatekey_nopassphrase.pem'
        );
        $client->setClient($guzzle);

        // Creating a description without being authenticated throws an error.
        try {
            $client->postDescription('{"json":"data"}');
            $this->fail("Creating a description without being authenticated should throw an exception.");
        } catch(ClientAuthenticationException $e) {
            $this->assertTrue(true, "Creating a description without being authenticated throws an exception.");
        }

        // Creating a description while authenticated doesn't throw an error.
        try {
            $client->authenticate();
            $client->postDescription('{"json":"data"}');
            $this->assertTrue(true, "Creating a description while being authenticated does not throw an exception.");
        } catch(ClientAuthenticationException $e) {
            $this->fail("Creating a description while being authenticated should not throw an exception.");
        }

        // Setting invalid catalogs throws an error.
        try {
            $client->postDescription('{"json":"data"}', 'catalog');
            $this->fail("Setting invalid catalogs should throw an error.");
        } catch(\RuntimeException $e) {
            $this->assertTrue(true, "Setting invalid catalogs throws an error.");
        }

        // Setting correct catalogs doesn't throw an error, and updates the
        // headers.
        try {
            $client->postDescription('{"json":"data"}', ['catalog1', 'catalog2']);
            $this->assertTrue(true, "Setting correct catalogs doesn't throw an error.");
        } catch(\RuntimeException $e) {
            $this->fail("Setting correct catalogs shouldn't throw an error.");
        }
        $this->assertEquals(
            ['X-DSB-CATALOGS' => 'catalog1,catalog2'],
            $client->getRequestHeaders(),
            "Setting catalogs updates the request headers."
        );

        // Trying to upload a file that doesn't exist throws an error.
        try {
            $client->postDescription('{"json":"data"}', [], '/where/is/this/file.jpg');
            $this->fail("Trying to upload a file that doesn't exist should throw an error.");
        } catch(\RuntimeException $e) {
            $this->assertTrue(true, "Trying to upload a file that doesn't exist throws an error.");
        }

        // Trying to upload a file that does exist does not throw an error.
        try {
            $client->postDescription('{"json":"data"}', [], FIXTURES_DIR . '/lom-data/image.png');
            $this->assertTrue(true, "Trying to upload a file that does exist does not throw an error.");
        } catch(\Exception $e) {
            $this->fail("Trying to upload a file that does exist should not throw an error.");
        }
    }

    /**
     * Test updating a description.
     */
    public function testPutDescription()
    {
        // Prepare a new client.
        $guzzle = $this->getGuzzleTestClient([
            new Response(200, [], Psr7\stream_for('{"token":"asjhasd987asdhasd87"}')),
            new Response(200),
            new Response(200),
        ]);

        // Prepare a client.
        $client = new ClientV2(
            'http://localhost',
            'user@site.com',
            FIXTURES_DIR . '/user/privatekey_nopassphrase.pem'
        );
        $client->setClient($guzzle);

        // Updating a description without being authenticated throws an error.
        try {
            $client->putDescription('some-id', '{"json":"data"}');
            $this->fail("Updating a description without being authenticated should throw an exception.");
        } catch(ClientAuthenticationException $e) {
            $this->assertTrue(true, "Updating a description without being authenticated throws an exception.");
        }

        // Updating a description while authenticated doesn't throw an error.
        try {
            $client->authenticate();
            $client->putDescription('some-id', '{"json":"data"}');
            $this->assertTrue(true, "Updating a description while being authenticated does not throw an exception.");
        } catch(ClientAuthenticationException $e) {
            $this->fail("Updating a description while being authenticated should not throw an exception.");
        }

        // Setting invalid catalogs throws an error.
        try {
            $client->putDescription('some-id', '{"json":"data"}', 'catalog');
            $this->fail("Setting invalid catalogs should throw an error.");
        } catch(\RuntimeException $e) {
            $this->assertTrue(true, "Setting invalid catalogs throws an error.");
        }

        // Setting correct catalogs doesn't throw an error, and updates the
        // headers.
        try {
            $client->putDescription('some-id', '{"json":"data"}', ['catalog1', 'catalog2']);
            $this->assertTrue(true, "Setting correct catalogs doesn't throw an error.");
        } catch(\RuntimeException $e) {
            $this->fail("Setting correct catalogs shouldn't throw an error.");
        }
        $this->assertEquals(
            ['X-DSB-CATALOGS' => 'catalog1,catalog2'],
            $client->getRequestHeaders(),
            "Setting catalogs updates the request headers."
        );
    }

    /**
     * Test deleting a description.
     */
    public function testDeleteDescription()
    {
        // Prepare a new client.
        $guzzle = $this->getGuzzleTestClient([
            new Response(200, [], Psr7\stream_for('{"token":"asjhasd987asdhasd87"}')),
            new Response(200),
        ]);

        // Prepare a client.
        $client = new ClientV2(
            'http://localhost',
            'user@site.com',
            FIXTURES_DIR . '/user/privatekey_nopassphrase.pem'
        );
        $client->setClient($guzzle);

        // Deleting a description without being authenticated throws an error.
        try {
            $client->deleteDescription('some-id');
            $this->fail("Deleting a description without being authenticated should throw an exception.");
        } catch(ClientAuthenticationException $e) {
            $this->assertTrue(true, "Deleting a description without being authenticated throws an exception.");
        }

        // Deleting a description while authenticated doesn't throw an error.
        try {
            $client->authenticate();
            $client->deleteDescription('some-id');
            $this->assertTrue(true, "Deleting a description while being authenticated does not throw an exception.");
        } catch(ClientAuthenticationException $e) {
            $this->fail("Deleting a description while being authenticated should not throw an exception.");
        }
    }

    /**
     * Test loading content partner data.
     */
    public function testGetPartners()
    {
        // Prepare a new client.
        $guzzle = $this->getGuzzleTestClient([
            new Response(200, [], Psr7\stream_for('{"token":"asjhasd987asdhasd87"}')),
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

        // Loading content partners without being authenticated throws an error.
        try {
            $client->loadPartners();
            $this->fail("Loading content partners without being authenticated should throw an exception.");
        } catch(ClientAuthenticationException $e) {
            $this->assertTrue(true, "Loading content partners without being authenticated throws an exception.");
        }

        // Loading content partners while authenticated doesn't throw an error.
        try {
            $client->authenticate();
            $partners = $client->loadPartners();
            $this->assertTrue(true, "Loading content partners while being authenticated does not throw an exception.");
        } catch(ClientAuthenticationException $e) {
            $this->fail("Loading content partners while being authenticated should not throw an exception.");
        }

        // Failing to load content partner data throws an exception.
        try {
            $client->loadPartners();
            $this->fail("Failing to load content partner data should throw an exception.");
        } catch(ClientRequestException $e) {
            $this->assertTrue(true, "Failing to load content partner data throws an exception.");
        }

        // A status different from 200 while loading content partner data throws
        // an exception.
        try {
            $client->loadPartners();
            $this->fail("A status different from 200 while loading content partner data should throw an exception.");
        } catch(ClientRequestException $e) {
            $this->assertTrue(true, "A status different from 200 while loading content partner data throws an exception.");
        }
    }

    /**
     * Test loading a single content partner data.
     */
    public function testGetPartner()
    {
        // Prepare a new client.
        $guzzle = $this->getGuzzleTestClient([
            new Response(200, [], Psr7\stream_for('{"token":"asjhasd987asdhasd87"}')),
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

        // Loading content partners without being authenticated throws an error.
        try {
            $client->loadPartner('user@site.com');
            $this->fail("Loading a content partner without being authenticated should throw an exception.");
        } catch(ClientAuthenticationException $e) {
            $this->assertTrue(true, "Loading a content partner without being authenticated throws an exception.");
        }

        // Loading a content partner while authenticated doesn't throw an error.
        try {
            $client->authenticate();
            $client->loadPartner('user@site.com');
            $this->assertTrue(true, "Loading a content partner while being authenticated does not throw an exception.");
        } catch(ClientAuthenticationException $e) {
            $this->fail("Loading a content partner while being authenticated should not throw an exception.");
        }

        // Failing to load a content partner data throws an exception.
        try {
            $client->loadPartner('user@site.com');
            $this->fail("Failing to load a content partner data should throw an exception.");
        } catch(ClientRequestException $e) {
            $this->assertTrue(true, "Failing to load a content partner data throws an exception.");
        }

        // A status different from 200 while loading a content partner data
        // throws an exception.
        try {
            $client->loadPartner('user@site.com');
            $this->fail("A status different from 200 while loading a content partner data should throw an exception.");
        } catch(ClientRequestException $e) {
            $this->assertTrue(true, "A status different from 200 while loading a content partner data throws an exception.");
        }
    }

    /**
     * Test updating a single content partner data.
     */
    public function testPutPartner()
    {
        // Prepare a new client.
        $guzzle = $this->getGuzzleTestClient([
            new Response(200, [], Psr7\stream_for('{"token":"asjhasd987asdhasd87"}')),
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

        // Updating content partners without being authenticated throws an
        // error.
        try {
            $client->putPartner('user@site.com', '{"company": "hello"}');
            $this->fail("Updating a content partner without being authenticated should throw an exception.");
        } catch(ClientAuthenticationException $e) {
            $this->assertTrue(true, "Updating a content partner without being authenticated throws an exception.");
        }

        // Updating a content partner while authenticated doesn't throw an
        // error.
        try {
            $client->authenticate();
            $client->putPartner('user@site.com', '{"company": "hello"}');
            $this->assertTrue(true, "Updating a content partner while being authenticated does not throw an exception.");
        } catch(ClientAuthenticationException $e) {
            $this->fail("Updating a content partner while being authenticated should not throw an exception.");
        }

        // Failing to update a content partner data throws an exception.
        try {
            $client->putPartner('user@site.com', '{"company": "hello"}');
            $this->fail("Failing to update a content partner data should throw an exception.");
        } catch(ClientRequestException $e) {
            $this->assertTrue(true, "Failing to update a content partner data throws an exception.");
        }

        // A status different from 200 while updating a content partner data
        // throws an exception.
        try {
            $client->putPartner('user@site.com', '{"company": "hello"}');
            $this->fail("A status different from 200 while updating a content partner data should throw an exception.");
        } catch(ClientRequestException $e) {
            $this->assertTrue(true, "A status different from 200 while updating a content partner data throws an exception.");
        }
    }

    /**
     * Test unavailable endpoint.
     */
    public function testUnavailableEndpoint()
    {
        // Prepare a new client.
        $guzzle = $this->getGuzzleTestClient([
            new RequestException("Error Communicating with Server", new Request('GET', 'test')),
        ]);

        // Prepare a client.
        $client = new ClientV2(
            'http://localhost',
            'user@site.com',
            FIXTURES_DIR . '/user/privatekey_nopassphrase.pem'
        );
        $client->setClient($guzzle);

        try {
            $client->authenticate();
            $this->fail("If there's no response, we create a 503 response and throw an exception.");
        } catch(ClientAuthenticationException $e) {
            $this->assertTrue(true, "If there's no response, we create a 503 response and throw an exception.");
        }
    }

    /**
     * Test uploading a file.
     */
    public function testUploadFile()
    {
        $filePath = FIXTURES_DIR . '/image-data/image.gif';

        // Prepare a new client.
        $guzzle = $this->getGuzzleTestClient([
            new Response(200, [], Psr7\stream_for('{"token":"asjhasd987asdhasd87"}')),
            new Response(200),
            new Response(200),
        ]);

        // Prepare a client.
        $client = new ClientV2(
            'http://localhost',
            'user@site.com',
            FIXTURES_DIR . '/user/privatekey_nopassphrase.pem'
        );
        $client->setClient($guzzle);

        // Uploading a file without being authenticated throws an error.
        try {
            $client->uploadFile($filePath);
            $this->fail("Uploading a file without being authenticated should throw an exception.");
        } catch(ClientAuthenticationException $e) {
            $this->assertTrue(true, "Uploading a file without being authenticated throws an exception.");
        }

        // Uploading a file while authenticated doesn't throw an error.
        try {
            $client->authenticate();
            $client->uploadFile($filePath);
            $this->assertTrue(true, "Uploading a file while being authenticated does not throw an exception.");
        } catch(ClientAuthenticationException $e) {
            $this->fail("Uploading a file while being authenticated should not throw an exception.");
        }

        // Trying to upload a file that doesn't exist throws an error.
        try {
            $client->uploadFile('/does/not/exist.png');
            $this->fail("Trying to upload a file that doesn't exist should throw an error.");
        } catch(\RuntimeException $e) {
            $this->assertTrue(true, "Trying to upload a file that doesn't exist throws an error.");
        }

        // Trying to upload a file that does exist does not throw an error.
        try {
            $client->uploadFile($filePath);
            $this->assertTrue(true, "Trying to upload a file that does exist does not throw an error.");
        } catch(\Exception $e) {
            $this->fail("Trying to upload a file that does exist should not throw an error.");
        }
    }

    /**
     * Test headers.
     */
    public function testHeaders()
    {
        $client = new ClientV2(
            'http://localhost',
            'user@site.com',
            FIXTURES_DIR . '/user/privatekey_nopassphrase.pem'
        );
        $client->addRequestHeader('toto', 'value');
        $client->addRequestHeader('toto2', 'value2');
        $this->assertEquals(
            [
                'toto' => 'value',
                'toto2' => 'value2',
            ],
            $client->getRequestHeaders(),
            "Add a single header works."
        );
        $client->setRequestHeaders(['hi' => 'there']);
        $this->assertEquals(
            ['hi' => 'there'],
            $client->getRequestHeaders(),
            "Replace all headers works."
        );

        $class = new \ReflectionClass('Educa\DSB\Client\ApiClient\ClientV2');
        $method = $class->getMethod('preProcessHeaders');
        $method->setAccessible(true);
        $this->assertEquals(
            ['headers' => ['hi' => 'there']],
            $method->invokeArgs($client, [[]]),
            "Preprocessing headers works."
        );
    }

    /**
     * Get a test client.
     *
     * This returns a GuzzleHttp\Client instance, with a mocked HTTP responses.
     *
     * @param array $responses
     *    A list of GuzzleHttp\Message response objects, to be used by the
     *    mocked test client. Responses will be returned in the order specified.
     * @param array &$transactions
     *    (optional) If passed, this array will be populated with the different
     *    transactions, resulting from the requests made.
     *
     * @return GuzzleHttp\Client
     */
    protected function getGuzzleTestClient(array $responses, &$transactions = NULL)
    {
        $mock = new MockHandler($responses);
        $handler = HandlerStack::create($mock);

        if (isset($transactions)) {
            $history = Middleware::history($transactions);
            // Add the history middleware to the handler stack.
            $handler->push($history);
        }

        $guzzle = new GuzzleClient(['handler' => $handler]);

        return $guzzle;
    }
}
