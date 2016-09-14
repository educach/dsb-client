<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Tests\Curriculum\ClassificationSystemCurriculumTest.
 */

namespace Educa\DSB\Client\Tests\Curriculum;

use Educa\DSB\Client\Curriculum\ClassificationSystemCurriculum;

class ClassificationSystemCurriculumTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test the parsing of the curriculum JSON.
     */
    public function testCurriculumParsing()
    {
        $json = file_get_contents(FIXTURES_DIR . '/curriculum-data/classification_system_curriculum.json');

        // Parse the data.
        $data = ClassificationSystemCurriculum::parseCurriculumJson($json);

        // Load the expected ASCII tree.
        $expectedAsciiTree = file_get_contents(FIXTURES_DIR . '/curriculum-data/classification_system_curriculum.ascii');

        $this->assertEquals(trim($expectedAsciiTree), $data->curriculum->asciiDump(), "The ASCII representation of the curriculum tree is as expected.");

        // Pick a few arbitrary entries in the dictionary, to make sure we have
        // our entries.
        $this->assertEquals(
            (object) array(
                'type' => 'school_level',
                'name' => (object) array(
                    'de' => "Fachhochschulen",
                    'fr' => "Hautes écoles spécialisées",
                    'it' => "Scuole universitarie professionali",
                    'rm' => "Scolas autas professiunalas",
                    'en' => "Universities of Applied sciences "
                ),
            ),
            $data->dictionary['universities of applied sciences'],
            "Found the correct data in the dictionary for item 'universities of applied sciences'."
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
            ),
            $data->dictionary['cycle 1'],
            "Found the correct data in the dictionary for item 'cycle 1'."
        );
    }

    /**
     * Test treating a taxonomy path.
     */
    public function testTaxonomyPathHandling()
    {
        $json = file_get_contents(FIXTURES_DIR . '/curriculum-data/classification_system_curriculum.json');

        $paths = json_decode(
            file_get_contents(FIXTURES_DIR . '/curriculum-data/classification_system_taxonomy_path.json'),
            true
        );

        // Create a new curriculum element.
        $curriculum = ClassificationSystemCurriculum::createFromData($json, ClassificationSystemCurriculum::CURRICULUM_JSON);

        $curriculum->setTreeBasedOnTaxonPath($paths, ['discipline', 'educational level']);

        // Load the expected ASCII tree.
        $expectedAsciiTree = file_get_contents(FIXTURES_DIR . '/curriculum-data/classification_system_taxonomy_path.ascii');

        $this->assertEquals(trim($expectedAsciiTree), $curriculum->asciiDump(), "The ASCII representation of the curriculum tree, based on the taxonomy path, is as expected.");
    }
}
