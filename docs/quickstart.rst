==========
Quickstart
==========

A note on API versions
======================

At the time of writing, the RESTful API is at version 2. It is highly likely that this API will undergo backward incompatible changes in the future, as the LOM-CH standard evolves. In order to plan for this in advance, the dsb Client library has an abstract, base class for client implementations, and version specific implementations that inherit from it.

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

Search results are not *LOM-CH* objects, but do contain some of their information. The ``Educa\DSB\Client\Lom\LomDescriptionSearchResult`` class can take a JSON decoded data structure and facilitate it's usage.

.. code-block:: php

    use Educa\DSB\Client\ApiClient\ClientV2;
    use Educa\DSB\Client\ApiClient\ClientAuthenticationException;
    use Educa\DSB\Client\ApiClient\ClientRequestException;
    use Educa\DSB\Client\Lom\LomDescriptionSearchResult;

    $client = new ClientV2('https://dsb-api.educa.ch/v2', 'user@site.com', '/path/to/privatekey.pem', 'passphrase');

    try {
        $client->authenticate();
        $searchResult = $client->search('Cookies');
    } catch(ClientRequestException $e) {
        // The request failed.
    } catch(ClientAuthenticationException $e) {
        // The authentication failed.
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

It is also possible to load a full LOM-CH description. This will contain a lot more information than the search results.

.. code-block:: php

    use Educa\DSB\Client\ApiClient\ClientV2;
    use Educa\DSB\Client\ApiClient\ClientAuthenticationException;
    use Educa\DSB\Client\ApiClient\ClientRequestException;
    use Educa\DSB\Client\Lom\LomDescription;

    $client = new ClientV2('https://dsb-api.educa.ch/v2', 'user@site.com', '/path/to/privatekey.pem', 'passphrase');

    try {
        $client->authenticate();
        $lomDescription = $client->loadDescription('asd789asd9hasd-asd7asdas-asd897asd978');
    } catch(ClientRequestException $e) {
        // The request failed.
    } catch(ClientAuthenticationException $e) {
        // The authentication failed.
    }

It is possible to fetch LOM-CH field data using special methods:

.. code-block:: php

    echo $lomDescription->getLomId();
    echo $lomDescription->getPreviewImage();

Fields that contain data in multiple languages can be instructed to return the information in one language only by specifying a language fallback array. The first language that matches will be returned. If no match is found, the field will be returned in "raw" format (meaning, multilingual fields will be returned as an associative array, with field values keyed by language).

.. code-block:: php

    // This will first look for a German title, then fallback to French and
    // finally Italian.
    echo $lomDescription->getTitle(['de', 'fr', 'it']);

    // This will look for French first and fallback to English.
    echo $lomDescription->getDescription(['fr', 'en']);

Not all fields have shortcut methods. For fields that the ``Educa\DSB\Client\Lom\LomDescriptionInterface`` interface does not define shortcuts for, you can use the ``getField()`` method. For nested fields, use a *dot* (``.``) notation:

.. code-block:: php

    echo $lomDescription->getField('lomId');

    // Use a dot (.) notation to fetch nested fields.
    echo $lomDescription->getField('lifeCycle.version');

    // Fields that are arrays can use numeric field names to get specific items.
    echo $lomDescription->getField('technical.keyword.0');

    // Fields that are multilingual can use a language fallback array as the
    // second parameter.
    echo $lomDescription->getField('general.title', ['de', 'fr']);
