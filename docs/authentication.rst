==============
Authentication
==============

Almost all communication with the API requires prior authentication. Authentication happens through the exchange of data, signed with a private key. The API, which has access to the *public* key, verifies the validity of this signed data, and returns an *authentication token*. This token then needs to be passed in the request headers for each request.

Getting a private key
=====================

You must register with `educa.ch <http://biblio.educa.ch/de/partner-1>`_ to become a *content partner*. You will receive a private RSA key, a username (usually an email address) along with a passphrase. These three elements will be used by the client library to request an authentication token from the API.

Authenticating
==============

Authentication is pretty simple:

.. code-block:: php

    use Educa\DSB\Client\ApiClient\ClientV2;
    use Educa\DSB\Client\ApiClient\ClientAuthenticationException;

    $client = new ClientV2('https://dsb-api.educa.ch/v2', 'user@site.com', '/path/to/privatekey.pem', 'passphrase');

    try {
        $client->authenticate();
    } catch(ClientAuthenticationException $e) {
        // The authentication failed.
    }

The Client class will throw an exception in several cases:

* The private key could not be read: check the path, or that the private key is readable.
* The passphrase could not be loaded into memory: make sure the passphrase is correct.
* The request failed: the server did not return a *200* status code. Check the error message.
* The request succeeded, but the response did not contain a token.

If no exception is thrown, the authentication was successful. Once authenticated, the class can communicate with the API.

Chaining methods
================

The client class supports chaining methods. It is thus possible to chain an authentication with another action. For example:

.. code-block:: php

    use Educa\DSB\Client\ApiClient\ClientV2;
    use Educa\DSB\Client\ApiClient\ClientAuthenticationException;
    use Educa\DSB\Client\ApiClient\ClientRequestException;

    $client = new ClientV2('https://dsb-api.educa.ch/v2', 'user@site.com', '/path/to/privatekey.pem', 'passphrase');

    try {
        $client->authenticate()->search('Dog');
    } catch(ClientAuthenticationException $e) {
        // The authentication failed.
    } catch(ClientRequestException $e) {
        // The request failed.
    }
