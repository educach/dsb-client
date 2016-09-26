<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Tests\Curriculum\Term\EducaTermTest.
 */

namespace Educa\DSB\Client\Tests\Curriculum\Term;

use Educa\DSB\Client\Curriculum\Term\EducaTerm;
use Educa\DSB\Client\Curriculum\Term\TermHasNoParentException;
use Educa\DSB\Client\Curriculum\Term\TermHasNoChildrenException;
use Educa\DSB\Client\Curriculum\Term\TermHasNoPrevSiblingException;
use Educa\DSB\Client\Curriculum\Term\TermHasNoNextSiblingException;

class EducaTermTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test getters and setters.
     */
    public function testGetSet()
    {
        $term = new EducaTerm('type', 'uuid');
        $term
            ->setContext('LREv3.0');

        $this->assertEquals('LREv3.0', $term->getContext(), "The getter/setter works for contexts.");
    }

}
