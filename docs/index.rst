=============================================================
Documentation for the Swiss digital school library API Client
=============================================================

Introduction
============

The DSB Client library is a suite of PHP components that facilitate building new applications that communicate with the national catalog of the Swiss digital school library.

This national catalog exposes a RESTful API (more information `here <https://dsb-api.educa.ch/latest/doc/>`_), which allows *content partners* (organizations that have the right to access the catalog) to write to the catalog, as well as read from it and search for specific *descriptions*. A *description* is a piece of data following the *LOM-CH* standard. The *LOM-CH* standard is a superset of the international `LOM <https://en.wikipedia.org/wiki/Learning_object_metadata>`_ standard. *LOM-CH* is fully compatible with *LOM*, but the inverse is not true (*LOM-CH* has more fields).

User Guide
==========

.. toctree::
    :maxdepth: 3

    install
    quickstart
    authentication
    descriptions
    testing
