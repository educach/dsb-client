<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Curriculum\Term\LP21Term.
 */

namespace Educa\DSB\Client\Curriculum\Term;

use Educa\DSB\Client\Curriculum\Term\BaseTerm;
use Educa\DSB\Client\Curriculum\Term\Traits\HasCode;
use Educa\DSB\Client\Curriculum\Term\Traits\HasVersion;
use Educa\DSB\Client\Curriculum\Term\Traits\HasCycle;
use Educa\DSB\Client\Curriculum\Term\Traits\HasCanton;
use Educa\DSB\Client\Curriculum\Term\Traits\HasUrl;

class LP21Term extends BaseTerm
{
    use HasCode, HasVersion, HasCycle, HasUrl, HasCanton;

    public function __construct($type, $id, $name = null, $code = null, $version = null, $url = null, $cycles = null, $cantons = null)
    {
        $this
            ->setDescription($type, $id, $name)
            ->setCode($code)
            ->setVersion($version)
            ->setUrl($url)
            ->setCycles($cycles)
            ->setCantons($cantons);
        $this->children = array();
    }

}
