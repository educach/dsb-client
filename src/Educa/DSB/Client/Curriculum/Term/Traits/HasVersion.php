<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Curriculum\Term\Traits\HasVersion.
 */

namespace Educa\DSB\Client\Curriculum\Term\Traits;

trait HasVersion
{

    /**
     * The term's version, if any.
     *
     * @var string
     */
    protected $version;

    /**
     * Set the term version for this term.
     *
     * @param string $version
     *    The term version.
     *
     * @return this
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * Get the term version for this term.
     *
     * @return string|null
     *    The term version, or null if none is set.
     */
    public function getVersion()
    {
        return $this->version;
    }

}
