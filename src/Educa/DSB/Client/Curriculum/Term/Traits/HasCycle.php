<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Curriculum\Term\Traits\HasCycle.
 */

namespace Educa\DSB\Client\Curriculum\Term\Traits;

trait HasCycle
{

    /**
     * The term's cycle information, if any.
     *
     * @var array
     */
    protected $cycles;

    /**
     * Set the cycle(s) this term applies to.
     *
     * @param array $cycle
     *    The cycle(s), like 1 or 2.
     *
     * @return this
     */
    public function setCycles($cycles)
    {
        $this->cycles = $cycles;
        return $this;
    }

    /**
     * Get the cycle(s) this term applies to.
     *
     * @return array|null
     *    The cycle(s) this term applies to, or null if none is set.
     */
    public function getCycles()
    {
        return $this->cycles;
    }

}
