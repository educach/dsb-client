<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Curriculum\Term\EducaTerm.
 */

namespace Educa\DSB\Client\Curriculum\Term;

use Educa\DSB\Client\Curriculum\Term\BaseTerm;
use Educa\DSB\Client\Curriculum\Term\Traits\HasContext;

class EducaTerm extends BaseTerm
{
    use HasContext;

    public function __construct($type, $id, $name = null, $context = null)
    {
        $this
            ->setDescription($type, $id, $name)
            ->setContext($context);
        $this->children = array();
    }

}
