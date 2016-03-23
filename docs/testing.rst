=================================
Unit testing your own application
=================================

It is possible to write unit tests for your own application by using the ``TestClient`` class instead of one of the real implementations. This class implements the same ``ClientInterface`` interface, and returns mocked results. It's implementation is pretty simple, but can do the trick for many test cases. If you wish to have more complex results, you may simply extend it and implement your own.

It is a good idea to use some kind of *dependency injection* in your application. `Symfony <http://symfony.com/>`_ and `Silex <http://silex.sensiolabs.org/>`_ provide such mechanisms out of the box. Another popular method is using `Pimple <http://pimple.sensiolabs.org/>`_. In the official DSB Client Drupal module, a simple function returns an instance of the client based on the system settings. Anyway, if you make sure the client class is returned by some sort of service container or function, you can make sure it returns a ``TestClient`` in your testing environment, making sure your unit tests run without requiring actual access to the API.

It is recommended to look at the `source code <https://github.com/educach/dsb-client/blob/master/src/Educa/DSB/Client/ApiClient/TestClient.php>`_ of the ``TestClient``, to get a better understanding of how it can be used inside a test environment.
