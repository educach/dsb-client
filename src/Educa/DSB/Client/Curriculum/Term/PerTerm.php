<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Curriculum\Term\PerTerm.
 */

namespace Educa\DSB\Client\Curriculum\Term;

use Educa\DSB\Client\Curriculum\Term\BaseTerm;
use Educa\DSB\Client\Curriculum\Term\Traits\HasCode;

class PerTerm extends BaseTerm
{
    use HasCode;

    /**
     * The term's URL property, if any.
     *
     * @var string
     */
    protected $url;

    /**
     * The term's school years property, if any.
     *
     * @var string
     */
    protected $schoolYears;

    public function __construct($type, $id, $name = null, $code = null, $url = null, $schoolYears = null)
    {
        $this
            ->setDescription($type, $id, $name)
            ->setCode($code)
            ->setUrl($url)
            ->setSchoolYears($schoolYears);
        $this->children = array();
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
     * Get the term table.
     *
     * @return string|null
     *    The table.
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set the term's "school years".
     *
     * @param string|array $schoolYears
     *    The school years this term applies to. Format is "1-2", "3-4", etc.
     *    Can either be a "single" school year, given as a string, or multiple
     *    values.
     *
     * @return this
     */
    public function setSchoolYears($schoolYears)
    {
        $this->schoolYears = is_string($schoolYears) ? [$schoolYears] : $schoolYears;
        return $this;
    }

    /**
     * Get the term's "school years".
     *
     * @return array|null
     *    The school years this term applies to, or null if not set. Format is
     *    "1-2", "3-4", etc.
     */
    public function getSchoolYears()
    {
        return $this->schoolYears;
    }

}
