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
        $this->assertEquals(
            (object) array(
                'type' => 'discipline',
                'name' => (object) array(
                    'de' => "Frühkindliche Bildung und Erziehung",
                    'fr' => "Education de la petite enfance",
                    'it' => "Formazione della prima infanzia",
                    'rm' => "",
                    'en' => "Early childhood education"
                ),
            ),
            $data->dictionary['early_childhood_education'],
            "Found the correct data in the dictionary for item 'early_childhood_education'."
        );
        $this->assertEquals(
            (object) array(
                'type' => 'context',
                'name' => (object) array(
                    'de' => "Vorschule",
                    'fr' => "Préobligatoire",
                    'it' => "Livello prescolare",
                    'rm' => "Prescola",
                    'en' => "Pre-compulsory"
                ),
            ),
            $data->dictionary['pre-school'],
            "Found the correct data in the dictionary for item 'pre-school'."
        );
        $this->assertEquals(
            (object) array(
                'type' => 'school level',
                'name' => (object) array(
                    'de' => "1. Zyklus (bis 4. Schuljahr)",
                    'fr' => "Cycle 1 (1ère à 4ème année scolaire) ",
                    'it' => "1° ciclo",
                    'rm' => "Emprim ciclus",
                    'en' => "1st cycle (up to 4th school year)"
                ),
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

        $curriculum->setTreeBasedOnTaxonPath($paths);

        // Load the expected ASCII tree.
        $expectedAsciiTree = file_get_contents(FIXTURES_DIR . '/curriculum-data/educa_taxonomy_path.ascii');

        $this->assertEquals(trim($expectedAsciiTree), $curriculum->asciiDump(), "The ASCII representation of the curriculum tree, based on the taxonomy path, is as expected.");
    }
}
