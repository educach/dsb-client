<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Curriculum\Term\EditableTermInterface.
 */

namespace Educa\DSB\Client\Curriculum\Term;

interface EditableTermInterface extends TermInterface
{

    /**
     * Add a child term.
     *
     * The order in which a child term is added is important. If other child
     * terms are present, their relationship as siblings will be affected.
     * Meaning, if term A is added first, and then term B, A will be the
     * "previous" sibling of B.
     *
     * @param \Educa\DSB\Client\Curriculum\Term\TermInterface $term
     *
     * @return this
     *    Support method chaining by return the current class.
     */
    public function addChild(EditableTermInterface $term);

    /**
     * Add a parent term.
     *
     * @param \Educa\DSB\Client\Curriculum\Term\TermInterface $term
     *
     * @return this
     *    Support method chaining by return the current class.
     */
    public function setParent(TermInterface $term);

    /**
     * Add a root term.
     *
     * @param \Educa\DSB\Client\Curriculum\Term\TermInterface $term
     *
     * @return this
     *    Support method chaining by return the current class.
     */
    public function setRoot(TermInterface $term);

    /**
     * Add a "previous" sibling term.
     *
     * @param \Educa\DSB\Client\Curriculum\Term\TermInterface $term
     *
     * @return this
     *    Support method chaining by return the current class.
     */
    public function setPrevSibling(TermInterface $term);

    /**
     * Add a "next" sibling term.
     *
     * @param \Educa\DSB\Client\Curriculum\Term\TermInterface $term
     *
     * @return this
     *    Support method chaining by return the current class.
     */
    public function setNextSibling(TermInterface $term);

}
