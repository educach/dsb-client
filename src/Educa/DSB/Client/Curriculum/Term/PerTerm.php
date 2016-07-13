<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Curriculum\Term\PerTerm.
 */

namespace Educa\DSB\Client\Curriculum\Term;

use Educa\DSB\Client\Curriculum\Term\BaseTerm;
use Educa\DSB\Client\Curriculum\Term\Traits\HasCode;
use Educa\DSB\Client\Curriculum\Term\Traits\HasUrl;
use Educa\DSB\Client\Curriculum\Term\Traits\HasSchoolYear;

class PerTerm extends BaseTerm
{
    use HasCode, HasUrl, HasSchoolYear;

    public function __construct($type, $id, $name = null, $code = null, $url = null, $schoolYears = null)
    {
        $this
            ->setDescription($type, $id, $name)
            ->setCode($code)
            ->setUrl($url)
            ->setSchoolYears($schoolYears);
        $this->children = array();
    }

}
