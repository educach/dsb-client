<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Tests\Curriculum\Term\BaseTermTest.
 */

namespace Educa\DSB\Client\Tests\Curriculum\Term;

use Educa\DSB\Client\Curriculum\Term\BaseTerm;
use Educa\DSB\Client\Curriculum\Term\TermHasNoParentException;
use Educa\DSB\Client\Curriculum\Term\TermHasNoChildrenException;
use Educa\DSB\Client\Curriculum\Term\TermHasNoPrevSiblingException;
use Educa\DSB\Client\Curriculum\Term\TermHasNoNextSiblingException;

class BaseTermTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test navigating a tree of terms.
     *
     * \Educa\DSB\Client\Curriculum\Term\TermInterface elements provide methods
     * for navigating their tree structure. Construct a tree and test the
     * navigation methods.
     *
     * We construct a tree that looks as follows:
     *
     * - a
     *   +- b
     *   +- c
     *      +- d
     *      +- e
     *         +- f
     *            +- g
     *   +- h
     *   +- i
     */
    public function testTreeNavigation()
    {
        $a = new BaseTerm('a', 'a');
        $b = new BaseTerm('b', 'b');
        $c = new BaseTerm('c', 'c');
        $d = new BaseTerm('d', 'd');
        $e = new BaseTerm('e', 'e');
        $f = new BaseTerm('f', 'f');
        $g = new BaseTerm('g', 'g');
        $h = new BaseTerm('h', 'h');
        $i = new BaseTerm('i', 'i');

        $a->addChild($b)->addChild($c)->addChild($h)->addChild($i);
        $c->addChild($d)->addChild($e);
        $e->addChild($f);
        $f->addChild($g);

        // Checking if root is root must return true.
        $this->assertTrue($a->isRoot(), "Checking if root is root returns true.");

        // Checking if a non-root term is root must return false.
        $this->assertFalse($b->isRoot(), "Checking if a non-root term is root returns false.");

        // Fetching the root of any item in the tree must return $a.
        $this->assertEquals($a, $d->getRoot(), "Fetching the root of d return a.");
        $this->assertEquals($a, $g->getRoot(), "Fetching the root of g return a.");

        // Trying to fetch the parent element of $a (the root) should throw
        // an exception.
        try {
            $a->getParent();
            $this->fail("Fetching the parent of the root element must throw an exception.");
        } catch (TermHasNoParentException $exception) {
            $this->assertTrue(true, "Fetching the parent of the root element must throw an exception.");
        }

        // Trying to fetch the parent element of $h should throw an exception.
        try {
            $g->getParent();
            $this->assertTrue(true, "Fetching the parent of a non-root element does not throw an exception.");
        } catch (TermHasNoParentException $exception) {
            $this->fail("Fetching the parent of a non-root element does not throw an exception.");
        }

        // Checking if a root element has a parent returns false.
        $this->assertFalse($a->hasParent(), "Checking if a root element has a parent returns false.");

        // Checking if a non-root element has a parent returns true.
        $this->assertTrue($b->hasParent(), "Checking if a non-root element has a parent returns true.");

        // Fetching the parent of an element returns the correct element.
        $this->assertEquals($a, $b->getParent(), "Fetching the parent of b returns a.");
        $this->assertEquals($c, $e->getParent(), "Fetching the parent of e returns c.");

        // Trying to fetch the child elements of $a should not throw an
        // exception.
        try {
            $a->getChildren();
            $this->assertTrue(true, "Fetching the child elements of a parent must not throw an exception.");
        } catch (TermHasNoChildrenException $exception) {
            $this->fail("Fetching the child elements of a parent must not throw an exception.");
        }

        // Trying to fetch the child elements of $g should not throw an
        // exception.
        try {
            $g->getChildren();
            $this->fail("Fetching the child elements of a leaf element must throw an exception.");
        } catch (TermHasNoChildrenException $exception) {
            $this->assertTrue(true, "Fetching the child elements of a leaf element must throw an exception.");
        }

        // Checking if a parent element has children returns true.
        $this->assertTrue($c->hasChildren(), "Checking if a parent element has children returns true.");

        // Checking if a leaf element has children returns false.
        $this->assertFalse($h->hasChildren(), "Checking if a leaf element has children returns false.");

        // Fetching the children of an element returns the correct elements.
        $this->assertEquals([$d, $e], $c->getChildren(), "Fetching the children of c returns d and e.");
        $this->assertEquals([$b, $c, $h, $i], $a->getChildren(), "Fetching the children of a returns b, c, h and i.");

        // Trying to fetch the "previous" sibling of $d should throw an
        // exception.
        try {
            $d->getPrevSibling();
            $this->fail("Fetching the previous sibling of an element that has none must throw an exception.");
        } catch (TermHasNoPrevSiblingException $exception) {
            $this->assertTrue(true, "Fetching the previous sibling of an element that has none must throw an exception.");
        }

        // Trying to fetch the "previous" sibling of $e should not throw an
        // exception.
        try {
            $e->getPrevSibling();
            $this->assertTrue(true, "Fetching the previous sibling of an element that has one must not throw an exception.");
        } catch (TermHasNoPrevSiblingException $exception) {
            $this->fail("Fetching the previous sibling of an element that has one must not throw an exception.");
        }

        // Checking if $d has a previous sibling returns false.
        $this->assertFalse($d->hasPrevSibling(), "Checking if d has a previous sibling returns false.");

        // Checking if $e has a previous sibling returns true.
        $this->assertTrue($e->hasPrevSibling(), "Checking if e has a previous sibling returns true.");

        // Fetching the previous sibling of $e returns $d.
        $this->assertEquals($d, $e->getPrevSibling(), "Fetching the previous sibling of e returns d.");

        // Trying to fetch the "next" sibling of $e should throw an
        // exception.
        try {
            $e->getNextSibling();
            $this->fail("Fetching the next sibling of an element that has none must throw an exception.");
        } catch (TermHasNoNextSiblingException $exception) {
            $this->assertTrue(true, "Fetching the next sibling of an element that has none must throw an exception.");
        }

        // Trying to fetch the "next" sibling of $d should not throw an
        // exception.
        try {
            $d->getNextSibling();
            $this->assertTrue(true, "Fetching the next sibling of an element that has one must not throw an exception.");
        } catch (TermHasNoNextSiblingException $exception) {
            $this->fail("Fetching the next sibling of an element that has one must not throw an exception.");
        }

        // Checking if $e has a next sibling returns false.
        $this->assertFalse($e->hasNextSibling(), "Checking if e has a next sibling returns false.");

        // Checking if $d has a next sibling returns true.
        $this->assertTrue($d->hasNextSibling(), "Checking if d has a next sibling returns true.");

        // Fetching the next sibling of $d returns $e.
        $this->assertEquals($e, $d->getNextSibling(), "Fetching the next sibling of d returns e.");
    }

    /**
     * Test searching for a child term.
     */
    public function testSearchChildTerm()
    {
        $terms = array();
        $root = new BaseTerm('type', 'uuid0');
        for ($i = 5; $i > 0; $i--) {
            $term = new BaseTerm('type', "uuid{$i}", "Child {$i}");
            $root->addChild($term);
            $terms["uuid{$i}"] = $term;

            for ($j = 5; $j > 0; $j--) {
                $childTerm = new BaseTerm('type', "uuid{$i}.{$j}", "Child {$i}.{$j}", "{$i}.{$j}");
                $term->addChild($childTerm);
                $terms["uuid{$i}.{$j}"] = $childTerm;
            }
        }
        $this->assertEquals(
            [$terms['uuid3']],
            $root->findChildrenByName("Child 3"),
            "Searching by name works one level."
        );
        $this->assertEquals(
            null,
            $root->findChildrenByName("Child 4.2"),
            "Searching by name one level for a child that's located deeper returns null."
        );
        $this->assertEquals(
            [$terms['uuid4.2']],
            $root->findChildrenByNameRecursive("Child 4.2"),
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
