<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Tests\Curriculum\BaseCurriculumTest.
 */

namespace Educa\DSB\Client\Tests\Curriculum;

use Educa\DSB\Client\Curriculum\Term\BaseTerm;

class BaseCurriculumTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test dumping a tree to a string.
     *
     * A tree of terms can be dumped to an ASCII tree for debugging purposes.
     * We will construct a tree of BaseTerms, and expect the resulting ASCII
     * representation to be accurate.
     */
    public function testAsciiTreeDump()
    {
        // Prepare the tree.
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

        // Prepare the expected ASCII tree.
        $expectedAsciiTree = <<<'EOF'
--- a:a
    +-- b:b
    +-- c:c
        +-- d:d
        +-- e:e
            +-- f:f
                +-- g:g
    +-- h:h
    +-- i:i
EOF;

        // Prepare a mocked class, because BaseCurriculum is an abstract class.
        // Pass the root element of the tree to the constructor.
        $stub = $this->getMockForAbstractClass('Educa\DSB\Client\Curriculum\BaseCurriculum', [$a]);

        $this->assertEquals($a, $stub->getTree(), "Fetching the tree returns the root element.");

        $this->assertEquals(trim($expectedAsciiTree), $stub->asciiDump(), "The ASCII representation of the tree is correct.");

        // If there is no root, we return an empty string.
        $stub = $this->getMockForAbstractClass('Educa\DSB\Client\Curriculum\BaseCurriculum');
        $this->assertEquals('', $stub->asciiDump(), "The ASCII representation of an empty tree is an empty string.");
    }
}
