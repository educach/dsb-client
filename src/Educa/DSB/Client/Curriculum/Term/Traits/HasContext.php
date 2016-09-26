<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Curriculum\Term\Traits\HasContext.
 */

namespace Educa\DSB\Client\Curriculum\Term\Traits;

trait HasContext
{

    /**
     * The term's context, if any.
     *
     * @var string
     */
    protected $context;

    /**
     * Set the term context for this term.
     *
     * @param string $context
     *    The term context, like 'LREv3.0'.
     *
     * @return this
     */
    public function setContext($context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Get the term context for this term.
     *
     * @return string|null
     *    The term context, or null if none is set.
     */
    public function getContext()
    {
        return $this->context;
    }

}
