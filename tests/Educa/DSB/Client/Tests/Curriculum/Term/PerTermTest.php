<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Tests\Curriculum\Term\PerTermTest.
 */

namespace Educa\DSB\Client\Tests\Curriculum\Term;

use Educa\DSB\Client\Curriculum\Term\PerTerm;
use Educa\DSB\Client\Curriculum\Term\TermHasNoParentException;
use Educa\DSB\Client\Curriculum\Term\TermHasNoChildrenException;
use Educa\DSB\Client\Curriculum\Term\TermHasNoPrevSiblingException;
use Educa\DSB\Client\Curriculum\Term\TermHasNoNextSiblingException;

class PerTermTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test getters and setters.
     */
    public function testGetSet()
    {
        $term = new PerTerm('type', 'uuid');
        $term
            ->setUrl('url')
            ->setCode('code')
            ->setSchoolYears('1-2');

        $this->assertEquals('url', $term->getUrl(), "The getter/setter works for URLs.");
        $this->assertEquals('code', $term->getCode(), "The getter/setter works for codes.");
        $this->assertEquals(['1-2'], $term->getSchoolYears(), "The getter/setter works for school years.");
    }

    /**
     * Test searching for a child term.
     */
    public function testSearchChildTerm()
    {
        $terms = array();
        $root = new PerTerm('type', 'uuid0');
        for ($i = 5; $i > 0; $i--) {
            $term = new PerTerm('type', "uuid{$i}", "Child {$i}");
            $root->addChild($term);
            $terms["uuid{$i}"] = $term;

            for ($j = 5; $j > 0; $j--) {
                $childTerm = new PerTerm('type', "uuid{$i}.{$j}", "Child {$i}.{$j}");
                $term->addChild($childTerm);
                $terms["uuid{$i}.{$j}"] = $childTerm;
            }
        }
        $this->assertEquals(
            $terms['uuid3'],
            $root->findChildByName("Child 3"),
            "Searching by name works one level."
        );
        $this->assertEquals(
            null,
            $root->findChildByName("Child 4.2"),
            "Searching by name one level for a child that's located deeper returns null."
        );
        $this->assertEquals(
            $terms['uuid4.2'],
            $root->findChildByNameRecursive("Child 4.2"),
            "Recursively searching by name works."
        );
        $this->assertEquals(
            $terms['uuid3'],
            $root->findChildByIdentifier('uuid3'),
            "Searching by ID works one level."
        );
        $this->assertEquals(
            null,
            $root->findChildByIdentifier('uuid4.2'),
            "Searching by ID one level for a child that's located deeper returns null."
        );
        $this->assertEquals(
            $terms['uuid4.2'],
            $root->findChildByIdentifierRecursive('uuid4.2'),
            "Recursively searching by ID works."
        );
    }

}
