<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Curriculum\Term\Traits\HasSchoolYear.
 */

namespace Educa\DSB\Client\Curriculum\Term\Traits;

trait HasSchoolYear
{

    /**
     * The term's school years, if any.
     *
     * @var array
     */
    protected $schoolYears;


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
