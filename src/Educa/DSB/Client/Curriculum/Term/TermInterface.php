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

}
