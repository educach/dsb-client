=====
Files
=====

Files can be uploaded to the dsb (*Digitale Schulbibliothek*) API server to be hosted there. The dsb API provides methods for images to be resized on the fly. This is a functionality *partners* can leverage to display images of certain sizes to their users, instead of using the original, high-resolution version. Check out the `official dsb API documentation <https://dsb-api.educa.ch/v2/doc/#api-File>`_ for more information.

Uploading files
===============

Files can be uploaded using the ``uploadFile`` method. At the time of writing, the dsb API only allows *image files*. Make sure you upload an image in a supported format.

.. code-block:: php

    use Educa\DSB\Client\ApiClient\ClientV2;
    use Educa\DSB\Client\ApiClient\ClientAuthenticationException;
    use Educa\DSB\Client\ApiClient\ClientRequestException;

    $client = new ClientV2('https://dsb-api.educa.ch/v2', 'user@site.com', '/path/to/privatekey.pem', 'passphrase');

    try {
        $result = $client->authenticate()->fileUpload('/path/to/file.jpg');

        // The result contains the URL at which the file is accessible.
        echo $result['fileUrl'];
    } catch(ClientRequestException $e) {
        // The request failed.
    } catch(ClientAuthenticationException $e) {
        // The authentication failed.
    } catch(\RuntimeException $e) {
        // Either the file is not readable or doesn't exist.
    }
