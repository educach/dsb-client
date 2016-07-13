<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Curriculum\Term\Traits\HasCode.
 */

namespace Educa\DSB\Client\Curriculum\Term\Traits;

trait HasCode
{

    /**
     * The term's code, if any.
     *
     * @var string
     */
    protected $code;

    /**
     * Set the term code for this term.
     *
     * @param string $code
     *    The term code, like 'FG 31'.
     *
     * @return this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * Get the term code for this term.
     *
     * @return string|null
     *    The term code, or null if none is set.
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Find a child term based on its code.
     *
     * @param string $code
     *    The code to search a child for.
     *
     * @return \Educa\DSB\Client\Curriculum\Term\TermInterface|null
     *    The child, or null if not found.
     *
     * @throws \Educa\DSB\Client\Curriculum\Term\TermHasNoChildrenException
     */
    public function findChildByCode($code)
    {
        foreach ($this->getChildren() as $child) {
            if ($child->getCode() == $code) {
                return $child;
            }
        }
    }

    /**
     * Find a child term based on its code, recursively.
     *
     * @param string $code
     *    The code to search a child for.
     *
     * @return \Educa\DSB\Client\Curriculum\Term\TermInterface|null
     *    The child, or null if not found.
     *
     * @throws \Educa\DSB\Client\Curriculum\Term\TermHasNoChildrenException
     */
    public function findChildByCodeRecursive($code)
    {
        foreach ($this->getChildren() as $child) {
            if ($child->getCode() == $code) {
                return $child;
            } elseif ($child->hasChildren()) {
                if ($found = $child->findChildByCodeRecursive($code)) {
                    return $found;
                }
            }
        }
    }

}
