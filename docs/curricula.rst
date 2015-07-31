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

``describeTermTypes()`` provides even more information on what the term *types* actually stand for.

``asciiDump()`` provides a way to dump a tree representation to a ASCII string, helping in debugging.

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

todo

PER curriculum
==============

todo
