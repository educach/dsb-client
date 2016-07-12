<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Curriculum\Term\LP21Term.
 */

namespace Educa\DSB\Client\Curriculum\Term;

use Educa\DSB\Client\Curriculum\Term\BaseTerm;
use Educa\DSB\Client\Curriculum\Term\Traits\HasCode;

class LP21Term extends BaseTerm
{
    use HasCode;

    /**
     * The term's curriculum version, if any.
     *
     * @var string
     */
    protected $curriculumVersion;

    /**
     * The term's URL property, if any.
     *
     * @var string
     */
    protected $url;

    public function __construct($type, $id, $name = null, $code = null, $curriculumVersion = null, $url = null)
    {
        $this
            ->setDescription($type, $id, $name)
            ->setCode($code)
            ->setCurriculumVersion($curriculumVersion)
            ->setUrl($url);
        $this->children = array();
    }

    /**
     * Set the curriculum version that applies to this term.
     *
     * @param string $curriculumVersion
     *    The curriculum version, like '1.0'.
     *
     * @return this
     */
    public function setCurriculumVersion($curriculumVersion)
    {
        $this->curriculumVersion = $curriculumVersion;
        return $this;
    }

    /**
     * Get the curriculum version that applies to this term.
     *
     * @return string|null
     *    The curriculum version, or null if none is set.
     */
    public function getCurriculumVersion()
    {
        return $this->curriculumVersion;
    }

    /**
     * Set the term URL describing this term.
     *
     * @param string $url
     *    The URL describing this term.
     *
     * @return this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Get the term URL describing this term.
     *
     * @return string|null
     *    The URL describing this term, or null if not set.
     */
    public function getUrl()
    {
        return $this->url;
    }

}
