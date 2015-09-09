<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Tests\Curriculum\Term\LP21TermTest.
 */

namespace Educa\DSB\Client\Tests\Curriculum\Term;

use Educa\DSB\Client\Curriculum\Term\LP21Term;
use Educa\DSB\Client\Curriculum\Term\TermHasNoParentException;
use Educa\DSB\Client\Curriculum\Term\TermHasNoChildrenException;
use Educa\DSB\Client\Curriculum\Term\TermHasNoPrevSiblingException;
use Educa\DSB\Client\Curriculum\Term\TermHasNoNextSiblingException;

class LP21TermTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test getters and setters.
     */
    public function testGetSet()
    {
        $term = new LP21Term('type', 'uuid');
        $term
            ->setUrl('url')
            ->setCode('code')
            ->setCurriculumVersion('version');

        $this->assertEquals('url', $term->getUrl(), "The getter/setter works for URLs.");
        $this->assertEquals('code', $term->getCode(), "The getter/setter works for codes.");
        $this->assertEquals('version', $term->getCurriculumVersion(), "The getter/setter works for versions.");
    }

}
