==========
Quickstart
==========

A note on API versions
======================

At the time of writing, the RESTful API is at version 2. It is highly likely that this API will undergo backward incompatible changes in the future, as the LOM-CH standard evolves. In order to plan for this in advance, the DSB Client library has an abstract, base class for client implementations, and version specific implementations that inherit from it.

Make sure to choose the correct client version, depending on which version of the RESTful API your connecting to. For instance, for communicating with the version 2 of the RESTful API, you should use ``ClientV2``.

Authentication
==============

All communication with the API requires prior authentication. Upon registering with `educa.ch <http://biblio.educa.ch/de/partner-1>`_ to become a *content partner*, you received a private RSA key, a username (usually an email address) along with a passphrase. You need to pass this information to the client class.

.. code-block:: php

    use Educa\DSB\Client\ApiClient\ClientV2;
    use Educa\DSB\Client\ApiClient\ClientAuthenticationException;

    $client = new ClientV2('https://dsb-api.educa.ch/v2', 'user@site.com', '/path/to/privatekey.pem', 'passphrase');

    try {
        $client->authenticate();
    } catch(ClientAuthenticationException $e) {
        // The authentication failed.
    }

Search
======

It is possible to search the catalog for specific descriptions. The search is very powerful and flexible, and beyond the scope of this documentation. Refer to the `RESTful API documentation <https://dsb-api.educa.ch/latest/doc/#api-Search>`_ for more information.

Search results are not *LOM-CH* objects, but do contain some of their information. The Educa\DSB\Client\Lom\LomDescriptionSearchResult class can take a JSON decoded data structure and facilitate it's usage.

.. code-block:: php

    use Educa\DSB\Client\ApiClient\ClientV2;
    use Educa\DSB\Client\ApiClient\ClientAuthenticationException;
    use Educa\DSB\Client\ApiClient\ClientRequestException;
    use Educa\DSB\Client\Lom\LomDescriptionSearchResult;

    $client = new ClientV2('https://dsb-api.educa.ch/v2', 'user@site.com', '/path/to/privatekey.pem', 'passphrase');

    try {
        $client->authenticate();
    } catch(ClientAuthenticationException $e) {
        // The authentication failed.
    }

    try {
        $searchResult = $client->search('Cookies');
    } catch(ClientRequestException $e) {
        // The request failed.
    }

    $results = array();
    foreach($searchResult['result'] as $lomData) {
        $results[] = new LomDescriptionSearchResult($lomData);
    }

    // Get the first LomDescriptionSearchResult.
    $lomDescription = $results[0];

    // Fetch the field data.
    echo $lomDescription->getTitle();
    echo $lomDescription->getTeaser();

Loading a description
=====================

.. code-block:: php

    use Educa\DSB\Client\ApiClient\ClientV2;
    use Educa\DSB\Client\ApiClient\ClientAuthenticationException;
    use Educa\DSB\Client\ApiClient\ClientRequestException;
    use Educa\DSB\Client\Lom\LomDescription;

    $client = new ClientV2('https://dsb-api.educa.ch/v2', 'user@site.com', '/path/to/privatekey.pem', 'passphrase');

    try {
        $client->authenticate();
    } catch(ClientAuthenticationException $e) {
        // The authentication failed.
    }

    try {
        $lomDescription = $client->loadDescription('asd789asd9hasd-asd7asdas-asd897asd978');
    } catch(ClientRequestException $e) {
        // The request failed.
    }

    // Fetch the field data. LOM-CH descriptions can contain information in
    // multiple languages. Fields that contain data in multiple languages can
    // be told to return the information in one language only by specifying
    // a language fallback array. The first language that matches will be
    // returned.
    // This will first look for a German title, then fallback to French and
    // finally Italian.
    echo $lomDescription->getTitle(['de', 'fr', 'it']);
    // This will look for French first and fallback to English.
    echo $lomDescription->getDescription(['fr', 'en']);
