===================
LOM-CH Descriptions
===================

A *description* is a piece of data following the *LOM-CH* standard. The *LOM-CH* standard is a superset of the international `LOM <https://en.wikipedia.org/wiki/Learning_object_metadata>`_ standard. *LOM* stands for *Learning Object Metadata*. It is a standard for representing learning resources, be it books, videos, websites, games, etc. The dsb (*Digitale Schulbibliothek*) groups many of these descriptions in a large catalog, with an API for searching for descriptions, loading a full description, or even adding new ones.

All communication with the API regarding descriptions requires prior authentication. See :doc:`Authentication<authentication>` for more information.

Searching descriptions
======================

Full text search
----------------

It is possible to search for description in a variety of ways. The most obvious is doing a full text search:

.. code-block:: php

    use Educa\DSB\Client\ApiClient\ClientV2;
    use Educa\DSB\Client\ApiClient\ClientAuthenticationException;
    use Educa\DSB\Client\ApiClient\ClientRequestException;

    $client = new ClientV2('https://dsb-api.educa.ch/v2', 'user@site.com', '/path/to/privatekey.pem', 'passphrase');

    try {
        $searchResult = $client->authenticate()->search('Cookies');
    } catch(ClientRequestException $e) {
        // The request failed.
    } catch(ClientAuthenticationException $e) {
        // The authentication failed.
    }

This will do a full text search on the word *Cookies*. All descriptions that fit the bill are returned by the API in JSON. The client will convert this JSON to an associative array before returning it. The returned value has 3 keys:

* `numFound`: The number total number of results the API found. This could be more than the number of actual results returned (defaults to 50).
* `result`: An array of descriptions, the actual search results.
* `facets`: A tree of facet data. More on this later. By default, this is empty.

You can get this data like so:

.. code-block:: php

    // Fetch the descriptions.
    $descriptions = $searchResult['result'];

By default, each description has the following properties:

* `lomId`: The description identifier.
* `title`: The title of the description.
* `teaser`: A shorter version of the description body text.
* `previewImage`: An image representing the learning resource.

Other fields can be added if required. More below.

Adding facets
-------------

The API supports something called *Faceted Search*. Simply put, this allows a search engine to dynamically tell what kind of filtering would be possible for the returned results. This information is then usually used to display some sort of list of options (like checkboxes) which can be displayed on a search form to allow dynamic filtering of the results.

By default, no facets are active, but you can ask the API to compute facets for the current query:

.. code-block:: php

    use Educa\DSB\Client\ApiClient\ClientV2;
    use Educa\DSB\Client\ApiClient\ClientAuthenticationException;
    use Educa\DSB\Client\ApiClient\ClientRequestException;

    $client = new ClientV2('https://dsb-api.educa.ch/v2', 'user@site.com', '/path/to/privatekey.pem', 'passphrase');

    try {
        $searchResult = $client->authenticate()->search('Cookies', ['learningResourceType']);
    } catch(ClientRequestException $e) {
        // The request failed.
    } catch(ClientAuthenticationException $e) {
        // The authentication failed.
    }

This will compute a list of *resource types* (*text*, *image*, *website*, etc) that are available for all found results. You may use this information to build a search form, and display these facets as checkboxes. The values of these checkboxes can then be used as *filters* (more below).

It is possible to pass more than one facet to the Client:

.. code-block:: php

    use Educa\DSB\Client\ApiClient\ClientV2;
    use Educa\DSB\Client\ApiClient\ClientAuthenticationException;
    use Educa\DSB\Client\ApiClient\ClientRequestException;

    $client = new ClientV2('https://dsb-api.educa.ch/v2', 'user@site.com', '/path/to/privatekey.pem', 'passphrase');

    try {
        $searchResult = $client->authenticate()->search('Cookies', ['learningResourceType', 'educaSchoolLevels']);
    } catch(ClientRequestException $e) {
        // The request failed.
    } catch(ClientAuthenticationException $e) {
        // The authentication failed.
    }

A full list of available facets can be found `here <https://dsb-api.educa.ch/latest/doc/#api-Search-GetSearch>`_. A live-example of how these facets can be used can be found `here <http://portal.dsb.educa.ch>`_.

Filtering results
-----------------

It is possible to add filters to narrow the search down. This is often closely related to *facets* (see above). A *filter* is an object, where each property name is a filter name, and its value is an array of possible values. For example, imagine we only want descriptions in German:

.. code-block:: php

    use Educa\DSB\Client\ApiClient\ClientV2;
    use Educa\DSB\Client\ApiClient\ClientAuthenticationException;
    use Educa\DSB\Client\ApiClient\ClientRequestException;

    $client = new ClientV2('https://dsb-api.educa.ch/v2', 'user@site.com', '/path/to/privatekey.pem', 'passphrase');

    try {
        $searchResult = $client->authenticate()->search('Cookies', [], ['language' => ['de']]);
    } catch(ClientRequestException $e) {
        // The request failed.
    } catch(ClientAuthenticationException $e) {
        // The authentication failed.
    }

This will filter all results and only show ones in German. Multi-value filters are possible as well. Multiple values are treated as *OR*, not *AND*:

.. code-block:: php

    use Educa\DSB\Client\ApiClient\ClientV2;
    use Educa\DSB\Client\ApiClient\ClientAuthenticationException;
    use Educa\DSB\Client\ApiClient\ClientRequestException;

    $client = new ClientV2('https://dsb-api.educa.ch/v2', 'user@site.com', '/path/to/privatekey.pem', 'passphrase');

    try {
        $searchResult = $client->authenticate()->search('Cookies', [], ['learningResourceType' => ['text', 'image']]);
    } catch(ClientRequestException $e) {
        // The request failed.
    } catch(ClientAuthenticationException $e) {
        // The authentication failed.
    }

This will filter by descriptions that are either text-based or image-based (or both).

Additional fields
-----------------

It is possible to add more fields to the search results. The 4th parameters passed to the client class when searching allows you to specify what more fields should be returned for each search result. For example, the following would add the ``language`` and ``version`` properties to the result:

.. code-block:: php

    use Educa\DSB\Client\ApiClient\ClientV2;
    use Educa\DSB\Client\ApiClient\ClientAuthenticationException;
    use Educa\DSB\Client\ApiClient\ClientRequestException;

    $client = new ClientV2('https://dsb-api.educa.ch/v2', 'user@site.com', '/path/to/privatekey.pem', 'passphrase');

    try {
        $searchResult = $client->authenticate()->search('Cookies', [], [], ['language', 'version']);
    } catch(ClientRequestException $e) {
        // The request failed.
    } catch(ClientAuthenticationException $e) {
        // The authentication failed.
    }

Read the `API documentation <https://dsb-api.educa.ch/latest/doc/#api-Search-GetSearch>`_ for more information.

Pagination and limiting the number of results
---------------------------------------------

It is possible to limit the number of results by passing a number as the 5th parameter. The following will only show 20 results (instead of 50, the default):

.. code-block:: php

    use Educa\DSB\Client\ApiClient\ClientV2;
    use Educa\DSB\Client\ApiClient\ClientAuthenticationException;
    use Educa\DSB\Client\ApiClient\ClientRequestException;

    $client = new ClientV2('https://dsb-api.educa.ch/v2', 'user@site.com', '/path/to/privatekey.pem', 'passphrase');

    try {
        $searchResult = $client->authenticate()->search('Cookies', [], [], [], 20);
    } catch(ClientRequestException $e) {
        // The request failed.
    } catch(ClientAuthenticationException $e) {
        // The authentication failed.
    }

It is also possible to offset the result, effectively giving applications a way to support *pagination*. The offset is the 6th parameter, and represents by how many items the results should be offset (usually a multiple of the *limit*):

.. code-block:: php

    use Educa\DSB\Client\ApiClient\ClientV2;
    use Educa\DSB\Client\ApiClient\ClientAuthenticationException;
    use Educa\DSB\Client\ApiClient\ClientRequestException;

    $client = new ClientV2('https://dsb-api.educa.ch/v2', 'user@site.com', '/path/to/privatekey.pem', 'passphrase');

    try {
        $searchResult = $client->authenticate()->search('Cookies', [], [], [], 20, 40);
    } catch(ClientRequestException $e) {
        // The request failed.
    } catch(ClientAuthenticationException $e) {
        // The authentication failed.
    }

Manipulating results
--------------------

Manipulating this search data might prove cumbersome. This is why there is a special class, called ``LomDescriptionSearchResult``, which can greatly simplify displaying search results. Simply pass the JSON-decoded value to the constructor:

.. code-block:: php

    use Educa\DSB\Client\ApiClient\ClientV2;
    use Educa\DSB\Client\ApiClient\ClientAuthenticationException;
    use Educa\DSB\Client\ApiClient\ClientRequestException;
    use Educa\DSB\Client\Lom\LomDescriptionSearchResult;

    $client = new ClientV2('https://dsb-api.educa.ch/v2', 'user@site.com', '/path/to/privatekey.pem', 'passphrase');

    try {
        $searchResult = $client->authenticate()->search('Cookies', [], [], ['language', 'version']);
    } catch(ClientRequestException $e) {
        // The request failed.
    } catch(ClientAuthenticationException $e) {
        // The authentication failed.
    }

    foreach($searchResult['result'] as $lomData) {
        $lomDescription = new LomDescriptionSearchResult($lomData);

        echo $lomDescription->getTitle();
        echo $lomDescription->getTeaser();
        echo $lomDescription->getLomId();
        echo $lomDescription->getPreviewImage();
    }

For additional fields, like ``language`` and ``version`` in our example, you may use the method ``getField()``. This method takes a field name as a parameter:

.. code-block:: php

    foreach($searchResult['result'] as $lomData) {
        $lomDescription = new LomDescriptionSearchResult($lomData);

        echo $lomDescription->getField('language');
        echo $lomDescription->getField('version');
    }

Of course, this also works for the default fields:

.. code-block:: php

    foreach($searchResult['result'] as $lomData) {
        $lomDescription = new LomDescriptionSearchResult($lomData);

        echo $lomDescription->getField('title');
        echo $lomDescription->getField('teaser');
        echo $lomDescription->getField('lomId');
        echo $lomDescription->getField('previewImage');
    }


Loading a description
=====================

It is possible to load the full data for a resource. This will contain all meta-data, as well as data from the `Ontology server <http://ontology.biblio.educa.ch/>`_.

Ontology data
-------------

Ontology data provides human-readable strings for *vocabulary* entries. For example, a description can have several *contributors*. Each of these contributors has a *role*, like *author*, *editor*, etc. These are *machine-readable* names, and are always the same, regardless of which language the description is in. In order to keep the human-readable values, as well as translations, of these vocabulary entries centralized, one can query the *Ontology Server*. This can be done directly through the API. See :doc:`ontology` for more information. However, the API "injects" most if this data directly into the loaded descriptions, which saves us the hassle.

Multilingual descriptions
-------------------------

Because this is communicating with the *Swiss* national catalog (which has 4 official languages), many descriptions are multilingual. When loading a description, many fields, like *title*, *keyword*, etc, can have different values per language.

Loading a description
---------------------

Loading a description requires knowing its *LOM identifier*. This is a UUID, or a MD5 hash prefixed with *archibald###* for older versions.

.. code-block:: php

    use Educa\DSB\Client\ApiClient\ClientV2;
    use Educa\DSB\Client\ApiClient\ClientAuthenticationException;
    use Educa\DSB\Client\ApiClient\ClientRequestException;

    $client = new ClientV2('https://dsb-api.educa.ch/v2', 'user@site.com', '/path/to/privatekey.pem', 'passphrase');

    try {
        $descriptionData = $client->authenticate()->loadDescription('asd89iowqe-sadjqw98-asd87a9doiiuowqe');
    } catch(ClientRequestException $e) {
        // The request failed.
    } catch(ClientAuthenticationException $e) {
        // The authentication failed.
    }

This loads the description data as an associative array into ``$descriptionData``. Look at `the API documentation <https://dsb-api.educa.ch/latest/doc/#api-Description-GetDescription>`_ for more information on this data structure.

Manipulating a description
--------------------------

This object can be pretty hard to manipulate. That is where ``LomDescription`` comes in. The ``LomDescription`` class can take a JSON-decoded LOM-CH data object and expose its properties in a much more convenient way:

.. code-block:: php

    use Educa\DSB\Client\ApiClient\ClientV2;
    use Educa\DSB\Client\ApiClient\ClientAuthenticationException;
    use Educa\DSB\Client\ApiClient\ClientRequestException;
    use Educa\DSB\Client\Lom\LomDescription;

    $client = new ClientV2('https://dsb-api.educa.ch/v2', 'user@site.com', '/path/to/privatekey.pem', 'passphrase');

    $client = new ClientV2('https://dsb-api.educa.ch/v2', 'user@site.com', '/path/to/privatekey.pem', 'passphrase');

    try {
        $descriptionData = $client->authenticate()->loadDescription('asd89iowqe-sadjqw98-asd87a9doiiuowqe');
    } catch(ClientRequestException $e) {
        // The request failed.
    } catch(ClientAuthenticationException $e) {
        // The authentication failed.
    }

    $lomDescription = new LomDescription($descriptionData);

    echo $lomDescription->getTitle();
    echo $lomDescription->getDescription();
    echo $lomDescription->getLomId();
    echo $lomDescription->getPreviewImage();

Fields that contain data in multiple languages can be instructed to return the information in one language only by specifying a language fallback array. The first language that matches will be returned. If no match is found, the field will be returned in "raw" format (meaning, multilingual fields will be returned as an associative array, with field values keyed by language).

.. code-block:: php

    // This will first look for a German title, then fallback to French and
    // finally Italian.
    echo $lomDescription->getTitle(['de', 'fr', 'it']);

    // This will look for French first and fallback to English.
    echo $lomDescription->getDescription(['fr', 'en']);

Not all fields have shortcut methods. For fields that the ``LomDescriptionInterface`` interface does not define shortcuts for, you can use the ``getField()`` method. For nested fields, use a *dot* (``.``) notation:

.. code-block:: php

    echo $lomDescription->getField('lomId');

    // Use a dot (.) notation to fetch nested fields.
    echo $lomDescription->getField('lifeCycle.version');

    // Fields that are arrays can use numeric field names to get specific items.
    echo $lomDescription->getField('technical.keyword.0');

    // Fields that are multilingual can use a language fallback array as the
    // second parameter.
    echo $lomDescription->getField('general.title', ['de', 'fr']);


Validating a description
========================

It is possible to validate a description to check if no mandatory fields are missing, that they are well formed and respect the LOM-CH standard. Look at `the API documentation <https://dsb-api.educa.ch/latest/doc/#api-Description-PostDescription>`_ for more information on this data structure.

Simple validation script:

.. code-block:: php

    $client = new ClientV2('https://dsb-api.educa.ch/v2', 'user@site.com', '/path/to/privatekey.pem', 'passphrase');

    // Load a json file containing the LOM object
    $f = 'lom_object.json';
    try {
        $json = file_get_contents($f);
        $client->authenticate()->validateDescription($json);
        echo "Using file $f\n";
        if( isset($response['valid']) && $response['valid'] ) {
            echo "\n> Description is valid.";
        } else {
            echo "\n> Description is invalid.\n";
            echo "\nServer response:\n";
            echo "==============================\n";
            print_r($response);
            echo "==============================\n";
        }
    } catch(ClientRequestException $e) {
        // The request failed.
        print_r("The post request failed. (" . $e->getMessage() . ')');
    } catch(ClientAuthenticationException $e) {
        // The authentication failed.
        print_r("The authentification failed. (" . $e->getMessage() . ')');
    }
    echo "\n";

Response syntax
---------------

The response will always contain a ``valid`` key, that is a boolean.

If the submitted LOM object is invalid, the ``errors`` key will be populated with a list of issues.

In case of an invalid LOM object, the API will return:

.. code-block:: json

    {"valid":false,"message":"Description is not complete or not compliant.","errors":{"general.identifier":"missing","general.description":"missing","general.language":"missing"}}

The client returns a JSON decoded array:

.. code-block:: php

    Array
    (
        [valid] =>
        [message] => Description is not complete or not compliant.
        [errors] => Array
            (
                [general.identifier] => missing
                [general.description] => missing
                [general.language] => missing
            )

    )


