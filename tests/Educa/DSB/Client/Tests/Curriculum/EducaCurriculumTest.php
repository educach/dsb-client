<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Tests\Curriculum\EducaCurriculumTest.
 */

namespace Educa\DSB\Client\Tests\Curriculum;

use Educa\DSB\Client\Curriculum\EducaCurriculum;

class EducaCurriculumTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test the parsing of the curriculum JSON.
     */
    public function testCurriculumParsing()
    {
        $json = file_get_contents(FIXTURES_DIR . '/curriculum-data/educa_curriculum.json');

        // Parse the data.
        $data = EducaCurriculum::parseCurriculumJson($json);

        // Load the expected ASCII tree.
        $expectedAsciiTree = file_get_contents(FIXTURES_DIR . '/curriculum-data/educa_curriculum.ascii');

        $this->assertEquals(trim($expectedAsciiTree), $data->curriculum->asciiDump(), "The ASCII representation of the curriculum tree is as expected.");

        // Pick a few arbitrary entries in the dictionary, to make sure we have
        // our entries.
        $this->assertTrue(
            empty($data->dictionary['complexity_and_dependencies']),
            "The item 'complexity_and_dependencies' was deprecated, and should not be part of the tree."
        );
        $this->assertEquals(
            (object) array(
                'type' => 'discipline',
                'name' => (object) array(
                    'de' => "Kaufmännisches Rechnen",
                    'fr' => "Calcul commercial",
                    'it' => "Calco commerciali",
                    'rm' => "Roh_Commercial accounting",
                    'en' => "Commercial accounting"
                ),
                'context' => 'LOM-CHv1.0',
            ),
            $data->dictionary['commercial accounting'],
            "Found the correct data in the dictionary for item 'commercial accounting'."
        );
        $this->assertEquals(
            (object) array(
                'type' => 'school_level',
                'name' => (object) array(
                    'de' => "1. Zyklus (bis 4. Schuljahr)",
                    'fr' => "Cycle 1 (1ère à 4ème année scolaire) ",
                    'it' => "1° ciclo",
                    'rm' => "Emprim ciclus",
                    'en' => "1st cycle (up to 4th school year)"
                ),
                'context' => 'LOM-CHv1.0',
            ),
            $data->dictionary['cycle_1'],
            "Found the correct data in the dictionary for item 'cycle_1'."
        );
    }

    /**
     * Test treating a taxonomy path.
     */
    public function testTaxonomyPathHandling()
    {
        $json = file_get_contents(FIXTURES_DIR . '/curriculum-data/educa_curriculum.json');

        $paths = json_decode(
            file_get_contents(FIXTURES_DIR . '/curriculum-data/educa_taxonomy_path.json'),
            true
        );

        // Create a new curriculum element.
        $curriculum = EducaCurriculum::createFromData($json, EducaCurriculum::CURRICULUM_JSON);

        $curriculum->setTreeBasedOnTaxonPath($paths, ['discipline', 'educational level']);

        // Load the expected ASCII tree.
        $expectedAsciiTree = file_get_contents(FIXTURES_DIR . '/curriculum-data/educa_taxonomy_path.ascii');

        $this->assertEquals(trim($expectedAsciiTree), $curriculum->asciiDump(), "The ASCII representation of the curriculum tree, based on the taxonomy path, is as expected.");
    }
}
