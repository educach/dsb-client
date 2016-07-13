<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Curriculum\Term\Traits\HasUrl.
 */

namespace Educa\DSB\Client\Curriculum\Term\Traits;

trait HasUrl
{

    /**
     * The term's url, if any.
     *
     * @var string
     */
    protected $url;

    /**
     * Set the term url for this term.
     *
     * @param string $url
     *    The term url, like 'FG 31'.
     *
     * @return this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Get the term url for this term.
     *
     * @return string|null
     *    The term url, or null if none is set.
     */
    public function getUrl()
    {
        return $this->url;
    }

}
