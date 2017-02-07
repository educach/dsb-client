=========
Curricula
=========

A curriculum allows the national catalog to categorize and describe a resource in context of a specific course curriculum. For example, in the French part of Switzerland, many schools try to adhere to the `PER <http://www.plandetudes.ch/>`_ curriculum. LOM descriptions can contain meta-data information about how it can apply to this curriculum.

Curricula can vary greatly, and there's not often a common *ground* or standard that they all can refer to. Instead, it is up to the application to know how to treat the curriculum at hand.

In an effort to provide some level of abstraction and code re-use, the dsb Client Library comes with a set of curricula implementations. These follow a very simple, yet powerful logic of storing curricula *terms* (e.g., *school levels*, *contexts*, *themes*, *objectives*, etc) in a flat tree structure. This allows applications to navigate the tree and get meta-data information about each element.

Abstraction and developing new curriculum implementations
=========================================================

The base classes and interfaces make no assumption about the actual data format of the curricula. For instance, the official document for the PER curriculum data structure is an XML file. The official document for the *standard* (a.k.a. *educa*) curriculum data structure is a JSON file, and so on.

However, all curricula implementations *must* implement the ``CurriculumInterface`` interface. This interface describes methods for fetching curricula meta-data and structural information, allowing applications to better understand how a typical curriculum tree could look.

For example, the ``describeDataStructure()`` method returns a standard format of describing relationships and types of curriculum *terms*.

.. code-block:: php

    use Educa\DSB\Client\Curriculum\EducaCurriculum;

    $curriculum = new EducaCurriculum();

    var_export($curriculum->describeDataStructure());
    // Results in:
    // array (
    //   0 =>
    //   stdClass::__set_state(array(
    //      'type' => 'educa_school_levels',
    //      'childTypes' =>
    //     array (
    //       0 => 'context',
    //     ),
    //   )),
    //   1 =>
    //   stdClass::__set_state(array(
    //      'type' => 'context',
    //      'childTypes' =>
    //     array (
    //       0 => 'school_level',
    //     ),
    //   )),
    //   2 =>
    //   stdClass::__set_state(array(
    //      'type' => 'school_level',
    //      'childTypes' =>
    //     array (
    //       0 => 'school_level',
    //     ),
    //   )),
    //   3 =>
    //   stdClass::__set_state(array(
    //      'type' => 'educa_school_subjects',
    //      'childTypes' =>
    //     array (
    //       0 => 'discipline',
    //     ),
    //   )),
    //   4 =>
    //   stdClass::__set_state(array(
    //      'type' => 'discipline',
    //      'childTypes' =>
    //     array (
    //       0 => 'discipline',
    //     ),
    //   )),
    // )

``describeTermTypes()`` provides even more information on what the term *types* actually stand for.

.. code-block:: php

    use Educa\DSB\Client\Curriculum\EducaCurriculum;

    $curriculum = new EducaCurriculum();

    var_export($curriculum->describeTermTypes());
    // Results in:
    // array (
    //   0 =>
    //   stdClass::__set_state(array(
    //      'type' => 'context',
    //      'purpose' =>
    //     array (
    //       'LOM-CHv1.2' => 'educational level',
    //     ),
    //   )),
    //   1 =>
    //   stdClass::__set_state(array(
    //      'type' => 'school level',
    //      'purpose' =>
    //     array (
    //       'LOM-CHv1.2' => 'educational level',
    //     ),
    //   )),
    //   2 =>
    //   stdClass::__set_state(array(
    //      'type' => 'discipline',
    //      'purpose' =>
    //     array (
    //       'LOM-CHv1.2' => 'discipline',
    //     ),
    //   )),
    // );

``asciiDump()`` provides a way to dump a tree representation to a ASCII string, helping in debugging.

.. code-block:: php

    use Educa\DSB\Client\Curriculum\EducaCurriculum;

    $curriculum = new EducaCurriculum();

    // Do some treatment, constructing the curriculum tree...

    print $curriculum->asciiDump();
    // Results in:
    // --- root:root
    //     +-- context:compulsory education
    //         +-- school level:cycle_3
    //             +-- discipline:languages
    //                 +-- discipline:french school language
    //     +-- context:special_needs_education
    //         +-- discipline:languages
    //             +-- discipline:french school language


The standard, static ``createFromData()`` method provides a standard factory method for creating new curriculum elements, although the format of the actual data passed to the method is completely left to the implementor.

A curriculum tree consists of ``TermInterface`` elements. Each element has the following methods, allowing applications to navigate the tree:

- ``hasChildren()``: Whether the term has child terms.
- ``getChildren()``: Get the child terms.
- ``hasParent()``: Whether the term has a parent term.
- ``getParent()``: Get the parent term.
- ``isRoot()``: Whether the term is the root parent term.
- ``getRoot()``: Get the root parent term.
- ``hasPrevSibling()``: Whether the term has a sibling term "in front" of it.
- ``getPrevSibling()``: Get the sibling term "in front" of it.
- ``hasNextSibling()``: Whether the term has a sibling term "after" it.
- ``getNextSibling()``: Get the sibling term "after" it.
* ``findChildByIdentifier()``: Allows to search direct descendants for a specific term via its identifier.
* ``findChildByIdentifierRecursive()``: Same as above, but recursively descends onto child terms as well
* ``findChildrenByName()``: Allows to search direct descendants for a specific term via its name.
* ``findChildrenByNameRecursive()``: Same as above, but recursively descends onto child terms as well.
* ``findChildrenByType()``: Allows to search direct descendants for a specific term via its type.
* ``findChildrenByTypeRecursive()``: Same as above, but recursively descends onto child terms as well.

Furthermore, it has one more method, ``describe()``, which allows applications to understand what kind of term they're dealing with.

Thanks to these methods, applications can navigate the entire tree structure and treat it as a flat structure.

There is a basic implementation for terms, ``BaseTerm``. It also implements the ``EditableTermInterface`` interface, and is usually recommended for use within any curriculum implementation.

"Standard" (educa) curriculum
=============================

**This curriculum has been deprecated, and replaced by the Classification System**

The "standard" (or *educa*) curriculum is a non-official curriculum that aims to provide some basic curriculum that all Swiss cantons can more or less relate to. Its definition can be found `here <http://ontology.biblio.educa.ch/>`_.

The definition file is a JSON file that can be downloaded from the site (`link <http://ontology.biblio.educa.ch/json/educa_standard_curriculum>`_). The ``EducaCurriculum`` class can parse this information for re-use. The reason this raw definition data does not *have* to be passed to ``EducaCurriculum`` every time is that applications might want to cache the parsing result, and pass the cached data in future calls. This can save time, as the parsing can be quite time-consuming and memory intensive.

.. code-block:: php

    use Educa\DSB\Client\Curriculum\EducaCurriculum;

    // $json contains the official curriculum data in JSON format.
    $json = file_get_contents('/path/to/curriculum.json');
    $curriculum = EducaCurriculum::createFromData($json);

    // We can also simply parse it, and cache $data for future use.
    $data = EducaCurriculum::parseCurriculumJson($json);

    // Demonstration of re-use of cached data.
    $curriculum = new EducaCurriculum($data->curriculum);
    $curriculum->setCurriculumDictionary($data->dictionary);

The curriculum class supports the handling of LOM *classification* field data (field no 9). This is represented as a series of *taxonomy paths*. Please refer to the `REST API documentation <https://dsb-api.educa.ch/latest/doc/>`_ for more information. By default, it only considers *discipline* taxonomy paths. If you wish to parse a taxonomy path with another *purpose* key, pass it as the second parameter to ``setTreeBasedOnTaxonPath()``.

.. code-block:: php

    use Educa\DSB\Client\Curriculum\EducaCurriculum;

    // Re-use cached data for the dictionary and curriculum definition.
    // See previous example.
    $curriculum = new EducaCurriculum($data->curriculum);
    $curriculum->setCurriculumDictionary($data->dictionary);

    // $paths is an array of taxonomy paths. See official REST API documentation
    // for more info.
    $curriculum->setTreeBasedOnTaxonPath($paths);

    print $curriculum->asciiDump();
    // Results in:
    // --- root:root
    //     +-- context:compulsory education
    //         +-- school level:cycle_3
    //             +-- discipline:languages
    //                 +-- discipline:german school language
    //             +-- discipline:social and human sciences
    //                 +-- discipline:citizenship
    //                 +-- discipline:history
    //     +-- context:post compulsory education
    //         +-- discipline:languages
    //             +-- discipline:german school language
    //         +-- discipline:social and human sciences
    //             +-- discipline:history
    //             +-- discipline:psychology
    //             +-- discipline:philosophy
    //         +-- discipline:general_education
    //             +-- discipline:identity

Of course, you can call ``getTree()`` to get the root item of the tree, and navigate it.


Classification System
=====================

Not a true curriculum, this system allows educational resources to be classified independently from cantonal curricula. Its definition can be found `here <http://ontology.biblio.educa.ch/>`_.

The definition file is a JSON file that can be downloaded from the site (`link <http://ontology.biblio.educa.ch/json/classification_system>`_). The ``ClassificationSystemCurriculum`` class can parse this information for re-use. The reason this raw definition data does not *have* to be passed to ``ClassificationSystemCurriculum`` every time is that applications might want to cache the parsing result, and pass the cached data in future calls. This can save time, as the parsing can be quite time-consuming and memory intensive.

.. code-block:: php

    use Educa\DSB\Client\Curriculum\ClassificationSystemCurriculum;

    // $json contains the official curriculum data in JSON format.
    $json = file_get_contents('/path/to/curriculum.json');
    $curriculum = ClassificationSystemCurriculum::createFromData($json);

    // We can also simply parse it, and cache $data for future use.
    $data = ClassificationSystemCurriculum::parseCurriculumJson($json);

    // Demonstration of re-use of cached data.
    $curriculum = new ClassificationSystemCurriculum($data->curriculum);
    $curriculum->setCurriculumDictionary($data->dictionary);

The curriculum class supports the handling of LOM *classification* field data (field no 9). This is represented as a series of *taxonomy paths*. Please refer to the `REST API documentation <https://dsb-api.educa.ch/latest/doc/>`_ for more information. By default, it only considers *discipline* taxonomy paths. If you wish to parse a taxonomy path with another *purpose* key, pass it as the second parameter to ``setTreeBasedOnTaxonPath()``.

.. code-block:: php

    use Educa\DSB\Client\Curriculum\ClassificationSystemCurriculum;

    // Re-use cached data for the dictionary and curriculum definition.
    // See previous example.
    $curriculum = new ClassificationSystemCurriculum($data->curriculum);
    $curriculum->setCurriculumDictionary($data->dictionary);

    // $paths is an array of taxonomy paths. See official REST API documentation
    // for more info.
    $curriculum->setTreeBasedOnTaxonPath($paths);

    print $curriculum->asciiDump();
    // Results in:
    // --- root:root
    //     +-- context:compulsory education
    //         +-- school level:cycle 3
    //             +-- discipline:languages
    //                 +-- discipline:german school language
    //             +-- discipline:social and human sciences
    //                 +-- discipline:history
    //     +-- context:post compulsory education
    //         +-- discipline:languages
    //             +-- discipline:german school language
    //         +-- discipline:social and human sciences
    //             +-- discipline:history
    //             +-- discipline:psychology
    //             +-- discipline:philosophy
    //         +-- discipline:general education
    //             +-- discipline:identity

Of course, you can call ``getTree()`` to get the root item of the tree, and navigate it.

Plan d'études Romand (PER) curriculum
=====================================

The *Plan d'études romand* (or *per*) curriculum is an official curriculum for the French speaking cantons in Switzerland. More information can be found `here <https://www.plandetudes.ch/>`_.

The definition data can be fetched via an API, which is openly accessible `here <http://bdper.plandetudes.ch/api/v1/>`_. The ``PerCurriculum`` class can fetch and parse this information for re-use. The reason this data does not *have* to be loaded by ``PerCurriculum`` every time is that applications might want to cache the parsing result, and pass the cached data in future calls. This can save time, as the parsing can be very time-consuming and memory intensive (it requires hundreds of ``GET`` requests to the REST API).

.. code-block:: php

    use Educa\DSB\Client\Curriculum\PerCurriculum;

    // $url contains the path to the REST API the class must use.
    $url = 'http://bdper.plandetudes.ch/api/v1/';
    $curriculum = PerCurriculum::createFromData($url);

    // We can also simply parse it, and cache $data for future use.
    $data = PerCurriculum::fetchCurriculumData($url);

    // Demonstration of re-use of cached data.
    $curriculum = new PerCurriculum($data->curriculum);
    $curriculum->setCurriculumDictionary($data->dictionary);

The curriculum class supports the handling of LOM-CH *curriculum* field data (field no 10). This is represented as a series of *taxonomy trees*. Please refer to the `REST API documentation <https://dsb-api.educa.ch/latest/doc/>`_, for more information on the structure.

.. code-block:: php

    use Educa\DSB\Client\Curriculum\PerCurriculum;

    // Re-use cached data for the dictionary and curriculum definition.
    // See previous example for more info.
    $curriculum = new PerCurriculum($data->curriculum);
    $curriculum->setCurriculumDictionary($data->dictionary);

    // $trees is an array of taxonomy trees. See official REST API documentation
    // for more info.
    $curriculum->setTreeBasedOnTaxonTree($trees);

    print $curriculum->asciiDump();
    // Results in:
    // --- root:root
    //     +-- cycle:1
    //         +-- domaine:4
    //             +-- discipline:13
    //                 +-- objectif:76
    //             +-- discipline:11
    //                 +-- objectif:77

Of course, you can call ``getTree()`` to get the root item of the tree, and navigate it.

A curriculum tree consists of ``TermInterface`` elements, just as for the other curricula implementations. However, ``PerCurriculum`` uses a custom term implementation, ``PerTerm``. This implements the same interfaces, so can be used in exactly the same ways as the standard terms. The difference is ``PerTerm`` exposes a few more methods:

* ``findChildByCode()``: Allows to search direct descendants for a specific term via its code (mostly applies to *Objectifs*)
* ``findChildByCodeRecursive()``: Same as above, but recursively descends onto child terms as well
* ``getUrl()`` and ``setUrl()``: Get/set the URL property of an item (mostly applies to *Objectifs*)
* ``getCode()`` and ``setCode()``: Get/set the code property of an item (mostly applies to *Objectifs*)
* ``getSchoolYears()`` and ``setSchoolYears()``: Get/set the school years property of an item (mostly applies to *Objectifs* and *Progressions d'apprentissage*)

Lehrplan 21 (lp21) curriculum
=============================

The *Lehrplan 21* (or *lp21*) curriculum is an official curriculum for the German speaking cantons in Switzerland. More information can be found `here <http://lehrplan.ch/>`_.

The definition file is a XML file that can be downloaded from the site. The ``LP21Curriculum`` class can parse this information for re-use. The reason this data does not *have* to be passed to ``LP21Curriculum`` every time is that applications might want to cache the parsing result, and pass the cached data in future calls. This can save time, as the parsing can be very time-consuming and memory intensive (the XML is over 20Mb in size).

.. code-block:: php

    use Educa\DSB\Client\Curriculum\LP21Curriculum;

    // $xml contains the official curriculum data in XML format.
    $xml = file_get_contents('/path/to/curriculum.xml');
    $curriculum = LP21Curriculum::createFromData($xml);

    // We can also simply parse it, and cache $data for future use.
    $data = LP21Curriculum::parseCurriculumXml($xml);

    // Demonstration of re-use of cached data.
    $curriculum = new LP21Curriculum($data->curriculum);
    $curriculum->setCurriculumDictionary($data->dictionary);

The curriculum class supports the handling of LOM-CH *curriculum* field data (field no 10). This is represented as a series of *taxonomy trees*. Please refer to the `REST API documentation <https://dsb-api.educa.ch/latest/doc/>`_, for more information on the structure.

.. code-block:: php

    use Educa\DSB\Client\Curriculum\LP21Curriculum;

    // Re-use cached data for the dictionary and curriculum definition.
    // See previous example for more info.
    $curriculum = new LP21Curriculum($data->curriculum);
    $curriculum->setCurriculumDictionary($data->dictionary);

    // $trees is an array of taxonomy trees. See official REST API documentation
    // for more info.
    $curriculum->setTreeBasedOnTaxonTree($trees);

    print $curriculum->asciiDump();
    // Results in:
    // --- root:root
    //     +-- zyklus:3
    //         +-- fachbereich:010fby8NKE8fCB79TRL69VS8VT4HnuHmN
    //             +-- fach:010ffPWHRKNUdFDK9LRgFbPDLXwTxa4bw
    //         +-- fachbereich:010fbNpVqv9R3TePRnCeZECuB4ucv6rEw
    //             +-- kompetenzbereich:010kbAnkUn9X9c8kz25FN9zFTFaHdAbPb
    //                 +-- handlungs_themenaspekt:010hafG6hGk8FZJWduaNDBGE4zhRWnvXK
    //         +-- fachbereich:010fbNpVqv9R3TePRnCeZECuB4ucv6rEw
    //             +-- kompetenzbereich:010kbAnkUn9X9c8kz25FN9zFTFaHdAbPb
    //         +-- fachbereich:010fbNpVqv9R3TePRnCeZECuB4ucv6rEw
    //             +-- kompetenzbereich:010kbAnkUn9X9c8kz25FN9zFTFaHdAbPb
    //                 +-- handlungs_themenaspekt:010ha4HnxH3GG5f5mqe8bddWtJK8bbVmD

Of course, you can call ``getTree()`` to get the root item of the tree, and navigate it.

A curriculum tree consists of ``TermInterface`` elements, just as for the other curricula implementations. However, ``LP21Curriculum`` uses a custom term implementation, ``LP21Term``. This implements the same interfaces, so can be used in exactly the same ways as the standard terms. The difference is ``LP21Term`` exposes a few more methods:

* ``findChildByCode()``: Allows to search direct descendants for a specific term via its code
* ``findChildByCodeRecursive()``: Same as above, but recursively descends onto child terms as well
* ``getUrl()`` and ``setUrl()``: Get/set the URL property of an item (mostly applies to *Kompetenzstufe*)
* ``getCode()`` and ``setCode()``: Get/set the code property of an item
* ``getVersion()`` and ``setVersion()``: Get the version of the Lehrplan this item is meant for (mostly applies to *Kompetenzstufe*)
* ``getCantons()`` and ``setCantons()``: Get the *Cantons* this item is meant for (mostly applies to *Fachbereiche*)
* ``getCycles()`` and ``setCycles()``: Get the *cycles* this item applies to (mostly applies to *Kompetenzstufe*)
