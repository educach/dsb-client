<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Curriculum\Term\TermInterface.
 */

namespace Educa\DSB\Client\Curriculum\Term;

interface TermInterface
{

    /**
     * Get information about the term.
     *
     * Each term has a specific place and role inside the curriculum tree. To
     * understand what this term "is", this method returns information about its
     * type and "identifier".
     *
     * @return object
     *    An object containing information about the current term. The object
     *    has the following properties:
     *    - type: The type of the term. See
     *      \Educa\DSB\Client\Curriculum\CurriculumInterface::describeTermTypes()
     *      for more information.
     *    - id: The identifier for this term, usually a string.
     *    - name: (optional) A human-readable name for this term. This data is
     *      formatted as a LangString, meaning it is a hash of strings, each
     *      string keyed by its language code (ISO ISO_639-1). See
     *      Educa\DSB\Client\Utils::getLSValue() for more information.
     */
    public function describe();

    /**
     * Check if this term has child terms.
     *
     * @return bool
     */
    public function hasChildren();

    /**
     * Return the child terms.
     *
     * @return array
     *    A list of \Educa\DSB\Client\Curriculum\Term\TermInterface
     *    elements.
     */
    public function getChildren();

    /**
     * Check if this term has a parent.
     *
     * @return bool
     */
    public function hasParent();

    /**
     * Return the parent term.
     *
     * @return \Educa\DSB\Client\Curriculum\Term\TermInterface
     *
     * @throws \Educa\DSB\Client\Curriculum\Term\TermNoParentException
     */
    public function getParent();

    /**
     * Check if this term the root of the tree.
     *
     * @return bool
     */
    public function isRoot();

    /**
     * Return the root element of the tree.
     *
     * @return \Educa\DSB\Client\Curriculum\Term\TermInterface
     *
     * @throws \Educa\DSB\Client\Curriculum\Term\TermIsRootException
     */
    public function getRoot();

    /**
     * Check if the term has a "previous" sibling.
     *
     * @return bool
     */
    public function hasPrevSibling();

    /**
     * Return the "previous" sibling term.
     *
     * @return \Educa\DSB\Client\Curriculum\Term\TermInterface
     *
     * @throws \Educa\DSB\Client\Curriculum\Term\TermHasNoPrevSiblingException
     */
    public function getPrevSibling();

    /**
     * Check if the term has a "next" sibling.
     *
     * @return bool
     */
    public function hasNextSibling();

    /**
     * Return the "next" sibling term.
     *
     * @return \Educa\DSB\Client\Curriculum\Term\TermInterface
     *
     * @throws \Educa\DSB\Client\Curriculum\Term\TermHasNoNextSiblingException
     */
    public function getNextSibling();

    /**
     * Return a ASCII representation of the tree.
     *
     * Useful for debugging, this method converts the term's children tree to a
     * string representation and returns it as a string.
     *
     * Example:
     * @code
     * --- element
     *     +-- child a
     *     +-- child b
     *     +-- child c
     *          +-- child d
     *          +-- child e
     * @endcode
     *
     * Where each item has the format "type:id", where "type" is the term's
     * type (see
     * \Educa\DSB\Client\Curriculum\CurriculumInterface::describeTermTypes())
     * and "id" is the item identifier.
     *
     * @return string
     *    An ASCII representation of the curriculum tree.
     */
    public function asciiDump();

}
