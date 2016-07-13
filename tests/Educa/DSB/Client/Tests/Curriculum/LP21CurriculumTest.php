<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Tests\Curriculum\LP21CurriculumTest.
 */

namespace Educa\DSB\Client\Tests\Curriculum;

use Educa\DSB\Client\Curriculum\LP21Curriculum;

class LP21CurriculumTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test the parsing of the curriculum JSON.
     */
    public function testCurriculumParsing()
    {
        $xml = file_get_contents(FIXTURES_DIR . '/curriculum-data/lp21_curriculum_obfuscated.xml');

        // Parse the data.
        $data = LP21Curriculum::parseCurriculumXml($xml);

        // Load the expected ASCII tree.
        $expectedAsciiTree = file_get_contents(FIXTURES_DIR . '/curriculum-data/lp21_curriculum_obfuscated.ascii');
        $this->assertEquals(trim($expectedAsciiTree), $data->curriculum->asciiDump(), "The ASCII representation of the curriculum tree is as expected.");
    }

    /**
     * Test treating a taxonomy tree.
     */
    public function testTaxonomyTreeHandling()
    {
        $trees = json_decode(
            file_get_contents(FIXTURES_DIR . '/curriculum-data/lp21_taxonomy_tree_obfuscated.json'),
            true
        );

        // Create a new curriculum element.
        $xml = file_get_contents(FIXTURES_DIR . '/curriculum-data/lp21_curriculum_obfuscated.xml');
        $curriculum = LP21Curriculum::createFromData(
            $xml,
            LP21Curriculum::CURRICULUM_XML
        );

        $curriculum->setTreeBasedOnTaxonTree($trees);

        // Load the expected competency ASCII tree.
        $expectedAsciiTree = file_get_contents(FIXTURES_DIR . '/curriculum-data/lp21_taxonomy_tree_obfuscated.ascii');
        $this->assertEquals(trim($expectedAsciiTree), $curriculum->asciiDump(), "The ASCII representation of the competency curriculum tree, based on the taxonomy tree, is as expected.");
    }
}
