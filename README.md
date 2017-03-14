Client to the national catalog of the Swiss digital school library
==================================================================

[![Build Status](https://travis-ci.org/educach/dsb-client.svg?branch=master)](https://travis-ci.org/educach/dsb-client) [![Coverage Status](https://coveralls.io/repos/educach/dsb-client/badge.svg?branch=master)](https://coveralls.io/r/educach/dsb-client?branch=master) [![Code Climate](https://codeclimate.com/github/educach/dsb-client/badges/gpa.svg)](https://codeclimate.com/github/educach/dsb-client) [![Documentation Status](https://readthedocs.org/projects/dsb-client/badge/?version=latest)](https://readthedocs.org/projects/dsb-client/?badge=latest)

This is the official PHP library for connecting and communicating with the REST API to the national catalog of the Swiss digital school library. It handles authentication, reading descriptions, searching, etc.

Installation
------------

Install using [Composer](https://getcomposer.org/). Add the following to your `composer.json`:

    {
      "require": {
        "educach/dsb-client": "dev-master"
      }
    }

Documentation
-------------

* Documentation for the client library can be found [here](http://dsb-client.readthedocs.org/en/latest/).
* Documentation for the REST API can be found [here](https://dsb-api.educa.ch/latest/doc/).
* Documentation for the LOM-CH standard (v1.2) can be found here: [German](https://dsb-api.educa.ch/lom-ch/lom-chv1.2_de.pdf), [French](https://dsb-api.educa.ch/lom-ch/lom-chv1.2_fr.pdf), [Italian](https://dsb-api.educa.ch/lom-ch/lom-chv1.2_it.pdf)

Contributing
------------

Contributions are more than welcome. There's still lots of work to be done before we reach [version 1.0.0](https://github.com/educach/dsb-client/milestones).

General guide lines:

* Respect the [PSR-2](http://www.php-fig.org/psr/psr-2/) standard (coding style guide).
* Respect the [PSR-4](http://www.php-fig.org/psr/psr-4/) standard (autoloading).
* Write unit tests. Our aim is to remain at +90% coverage.

Check the existing interfaces and try to remain consistent in regards to method naming, parameters, usage, etc.
