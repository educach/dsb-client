<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Curriculum\Term\Traits\HasCanton.
 */

namespace Educa\DSB\Client\Curriculum\Term\Traits;

trait HasCanton
{

    /**
     * The term's canton information, if any.
     *
     * @var array
     */
    protected $cantons;

    /**
     * Set the canton(s) this term applies to.
     *
     * @param array $canton
     *    The canton(s), like 'BE' or 'ZU'.
     *
     * @return this
     */
    public function setCantons($cantons)
    {
        $this->cantons = $cantons;
        return $this;
    }

    /**
     * Get the canton(s) this term applies to.
     *
     * @return array|null
     *    The canton(s) this term applies to, or null if none is set.
     */
    public function getCantons()
    {
        return $this->cantons;
    }

}
