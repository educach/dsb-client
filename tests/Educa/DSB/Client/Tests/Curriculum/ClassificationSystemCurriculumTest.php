<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Tests\Curriculum\ClassificationSystemCurriculumTest.
 */

namespace Educa\DSB\Client\Tests\Curriculum;

use Educa\DSB\Client\Curriculum\ClassificationSystemCurriculum;
use Educa\DSB\Client\Curriculum\Term\EducaTerm;

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
                'context' => 'LOM-CHv1.2',
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
                'context' => 'LOM-CHv1.2',
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

    /**
     * Test reverse mapping taxonomy terms.
     */
    public function testReverseMapping()
    {
        $json = file_get_contents(FIXTURES_DIR . '/curriculum-data/classification_system_curriculum.json');

        // Create a new curriculum element.
        $curriculum = ClassificationSystemCurriculum::createFromData($json, ClassificationSystemCurriculum::CURRICULUM_JSON);

        $term = new EducaTerm('school_level', 'independent of levels others', [], 'LOM-CHv1.2');
        $this->assertEquals(
            'indipendent_of_levels_others',
            $curriculum->mapTerm(
                'classification system',
                'educa',
                $term
            )->describe()->id,
            "Found the correct mapped term for item 'independent of levels others'."
        );

        $term = new EducaTerm('school_level', '1st and 2nd year', [], 'LOM-CHv1.2');
        $this->assertEquals(
            '1st_and_2nd_year',
            $curriculum->mapTerm(
                'classification system',
                'educa',
                $term
            )->describe()->id,
            "Found the correct mapped term for item '1st and 2nd year'."
        );

        $term = new EducaTerm('school_level', 'visual arts', [], 'LOM-CHv1.0');
        $this->assertEquals(
            'visual arts',
            $curriculum->mapTerm(
                'classification system',
                'educa',
                $term
            )->describe()->id,
            "Found the correct mapped term for item 'visual arts'."
        );
    }
}
