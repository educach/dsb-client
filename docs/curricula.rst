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
    // array(
    //     stdClass::__set_state(array(
    //         'type' => 'root',
    //         'child_types' => array(
    //             stdClass::__set_state(array(
    //                 'type' => 'context',
    //                 'required' => true,
    //             )),
    //         ),
    //     )),
    //     stdClass::__set_state(array(
    //         'type' => 'context',
    //         'child_types' => array(
    //             stdClass::__set_state(array(
    //                 'type' => 'school_level',
    //                 'required' => false,
    //             )),
    //             stdClass::__set_state(array(
    //                 'type' => 'discipline',
    //                 'required' => false,
    //             )),
    //         ),
    //     )),
    //     stdClass::__set_state(array(
    //         'type' => 'school_level',
    //         'child_types' => array(
    //             stdClass::__set_state(array(
    //                 'type' => 'discipline',
    //                 'required' => true,
    //             )),
    //         ),
    //     )),
    //     stdClass::__set_state(array(
    //         'type' => 'discipline',
    //         'child_types' => array(
    //             stdClass::__set_state(array(
    //                 'type' => 'discipline',
    //                 'required' => false,
    //             )),
    //         ),
    //     )),
    // );

``describeTermTypes()`` provides even more information on what the term *types* actually stand for.

.. code-block:: php

    use Educa\DSB\Client\Curriculum\EducaCurriculum;

    $curriculum = new EducaCurriculum();

    var_export($curriculum->describeTermTypes());
    // Results in:
    // array(
    //     stdClass::__set_state(array(
    //         'type' => 'root',
    //         'name' => stdClass::__set_state(array(
    //             'en' => "Root",
    //         )),
    //         'description' => stdClass::__set_state(array(
    //             'en' => "Not technically part of the curriculum. The educa curriculum can have multiple contexts, which are, according to the standard, the root elements. As the we must return a single element, this root type defines the top most parent of the curriculum tree.",
    //         )),
    //     )),
    //     stdClass::__set_state(array(
    //         'type' => 'context',
    //         'name' => stdClass::__set_state(array(
    //             'en' => "Context",
    //         )),
    //     )),
    //     stdClass::__set_state(array(
    //         'type' => 'school level',
    //         'name' => stdClass::__set_state(array(
    //             'en' => "School level",
    //         )),
    //     )),
    //     stdClass::__set_state(array(
    //         'type' => 'discipline',
    //         'name' => stdClass::__set_state(array(
    //             'en' => "Discipline",
    //         )),
    //     )),
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

Furthermore, it has one more method, ``describe()``, which allows applications to understand what kind of term they're dealing with.

Thanks to these methods, applications can navigate the entire tree structure and treat it as a flat structure.

There is a basic implementation for terms, ``BaseTerm``. It also implements the ``EditableTermInterface`` interface, and is usually recommended for use withing any curriculum implementation.

"Standard" (educa) curriculum
=============================

The "standard" (or *educa*) curriculum is a non-official curriculum that aims to provide some basic curriculum that all Swiss cantons can more or less relate to. Its definition can be found `here <http://ontology.biblio.educa.ch/>`_.

The definition file is a JSON file that can be downloaded from the site. The ``EducaCurriculum`` class can parse this information for re-use. The reason this data does not *have* to be passed to ``EducaCurriculum`` every time is that application might want to cache the parsing result, and pass the cached data in future calls. This can save time, as the parsing can be quite time-consuming and memory intensive.

.. code-block:: php

    use Educa\DSB\Client\Curriculum\EducaCurriculum;

    // $json contains the official curriculum data in JSON format.
    $json = file_get_contents('/path/to/curriculum.json');
    $curriculum = EducaCurriculum::createFromData($json);

    // We can also simply parse it, and cache $data for future use.
    $data = EducaCurriculum::parseCurriculumJson($json);

    // Demonstration of re-use of cached data.
    $curriculum = new EducaCurriculum();
    $curriculum->setCurriculumDefinition($data->curriculum);
    $curriculum->setCurriculumDictionary($data->dictionary);

The curriculum class supports the handling of LOM *classification* field data (field no 9). This is represented as a series of *taxonomy paths*. Please refer to the `REST API documentation <https://dsb-api.educa.ch/latest/doc/>`_ for more information. By default, it only considers *discipline* taxonomy paths. If you wish to parse a taxonomy path with another *purpose* key, pass it as the second parameter to ``setTreeBasedOnTaxonPath()``.

.. code-block:: php

    use Educa\DSB\Client\Curriculum\EducaCurriculum;

    // Re-use cached data for the dictionary and curriculum definition.
    $curriculum = new EducaCurriculum();
    $curriculum->setCurriculumDefinition($data->curriculum);
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

PER curriculum
==============

todo
