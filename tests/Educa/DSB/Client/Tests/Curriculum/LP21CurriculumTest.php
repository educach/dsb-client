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
        $xml = file_get_contents(FIXTURES_DIR . '/curriculum-data/lp21_curriculum.xml');

        // Parse the data.
        $data = LP21Curriculum::parseCurriculumXml($xml);

        // Load the expected ASCII tree.
        $expectedAsciiTree = file_get_contents(FIXTURES_DIR . '/curriculum-data/lp21_curriculum.ascii');

        $this->assertEquals(trim($expectedAsciiTree), $data->curriculum->asciiDump(), "The ASCII representation of the curriculum tree is as expected.");

        // Pick a few arbitrary entries in the dictionary, to make sure we have
        // our entries.
        $this->assertEquals(
            (object) array(
                'type' => 'zyklus',
                'name' => (object) array(
                    'de' => "1. Zyklus",
                ),
            ),
            $data->dictionary[1],
            "Found the correct data in the dictionary for item '1' (1. Zyklus)."
        );
        $this->assertEquals(
            (object) array(
                'type' => 'fach',
                'name' => (object) array(
                    'de' => "Deutsch",
                ),
                'code' => '1|1',
            ),
            $data->dictionary['010ffPWHRKNUdFDK9LRgFbPDLXwTxa4bw'],
            "Found the correct data in the dictionary for item '010ffPWHRKNUdFDK9LRgFbPDLXwTxa4bw' (Fach, Deutsch)."
        );
        $this->assertEquals(
            (object) array(
                'type' => 'handlungs_themenaspekt',
                'name' => (object) array(
                    'de' => "Grundfertigkeiten",
                ),
                'code' => '1|1|1|1',
            ),
            $data->dictionary['010hak7t6e8U64tTD7MBgVLk6XEc6mrSF'],
            "Found the correct data in the dictionary for item '010hak7t6e8U64tTD7MBgVLk6XEc6mrSF' (Handlungs-/Themenaspekt Grundfertigkeiten)."
        );
    }

    /**
     * Test treating a taxonomy path.
     */
    public function testTaxonomyPathHandling()
    {
        $xml = file_get_contents(FIXTURES_DIR . '/curriculum-data/lp21_curriculum.xml');

        $paths = json_decode(
            file_get_contents(FIXTURES_DIR . '/curriculum-data/lp21_taxonomy_path.json'),
            true
        );

        // Create a new curriculum element.
        $curriculum = LP21Curriculum::createFromData($xml, LP21Curriculum::CURRICULUM_XML);

        $curriculum->setTreeBasedOnTaxonPath($paths);

        // Load the expected discipline ASCII tree.
        $expectedAsciiTree = file_get_contents(FIXTURES_DIR . '/curriculum-data/lp21_taxonomy_path.discipline.ascii');
        $this->assertEquals(trim($expectedAsciiTree), $curriculum->asciiDump(), "The ASCII representation of the discipline curriculum tree, based on the taxonomy path, is as expected.");

        $curriculum->setTreeBasedOnTaxonPath($paths, 'competency');

        // Load the expected competency ASCII tree.
        $expectedAsciiTree = file_get_contents(FIXTURES_DIR . '/curriculum-data/lp21_taxonomy_path.competency.ascii');
        $this->assertEquals(trim($expectedAsciiTree), $curriculum->asciiDump(), "The ASCII representation of the competency curriculum tree, based on the taxonomy path, is as expected.");
    }
}
