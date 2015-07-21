============
Installation
============

Using Composer
==============

The easiest way, by far, is to use `Composer <https://getcomposer.org/>`_. Add the following line to your ``composer.json`` file's ``"require"`` hash:

.. code-block:: js

    {
        "require": {
            "educach/dsb-client": "dev-master"
        }
    }

Call the following command to download the library:

.. code-block:: bash

    composer install

After installing, you need to require Composer's autoloader:

.. code-block:: php

    require 'vendor/autoload.php';

Manual installation
===================

If you wish to use this library without using Composer, you can download a release `here <https://github.com/educach/dsb-client/releases>`_, or do a checkout using Git at ``https://github.com/educach/dsb-client.git``.

Make sure you have some sort of autoloading mechanism in place. The dsb Client library is `PSR-4 <http://www.php-fig.org/psr/psr-4/>`_ compatible.
