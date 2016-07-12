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
}
