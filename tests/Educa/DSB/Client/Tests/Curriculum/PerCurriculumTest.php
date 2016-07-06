<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Tests\Curriculum\PerCurriculumTest.
 */

namespace Educa\DSB\Client\Tests\Curriculum;

use Educa\DSB\Client\Curriculum\PerCurriculum;

class PerCurriculumTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test the parsing of the curriculum JSON.
     */
    public function testCurriculumParsing()
    {
        // Parse the data.
        $data = PerCurriculum::fetchCurriculumData(FIXTURES_DIR . '/curriculum-data/per-api');

        // Load the expected ASCII tree.
        $expectedAsciiTree = file_get_contents(FIXTURES_DIR . '/curriculum-data/per_curriculum.ascii');

        $this->assertEquals(trim($expectedAsciiTree), $data->curriculum->asciiDump(), "The ASCII representation of the curriculum tree is as expected.");

        // Pick a few arbitrary entries in the dictionary, to make sure we have
        // our entries.
        $this->assertEquals(
            (object) array(
                'type' => 'cycle',
                'name' => (object) array(
                    'fr' => "Cycle 1",
                ),
            ),
            $data->dictionary['cycles-1'],
            "Found the correct data in the dictionary for item 'cycles-1' (Cycle 1)."
        );
        $this->assertEquals(
            (object) array(
                'type' => 'domaine',
                'name' => (object) array(
                    'fr' => "Arts",
                ),
            ),
            $data->dictionary['cycles-1-domaines-4'],
            "Found the correct data in the dictionary for item 'cycles-1-domaines-4' (Arts)."
        );
        $this->assertEquals(
            (object) array(
                'type' => 'objectif',
                'name' => (object) array(
                    'fr' => "Expression et représentation (A 11 AV)",
                ),
                'code' => 'A 11 AV',
                'schoolYears' => ['1-2', '3-4']
            ),
            $data->dictionary['cycles-1-objectives-77'],
            "Found the correct data in the dictionary for item 'cycles-1-objectives-77' (A 11 AV)."
        );
    }

    /**
     * Test treating a taxonomy path.
     */
    public function testTaxonomyPathHandling()
    {
        $paths = json_decode(
            file_get_contents(FIXTURES_DIR . '/curriculum-data/per_taxonomy_path.json'),
            true
        );

        // Create a new curriculum element.
        $curriculum = PerCurriculum::createFromData(
            FIXTURES_DIR . '/curriculum-data/per-api',
            PerCurriculum::CURRICULUM_API
        );

        $curriculum->setTreeBasedOnTaxonPath($paths);

        // Load the expected discipline ASCII tree.
        $expectedAsciiTree = file_get_contents(FIXTURES_DIR . '/curriculum-data/per_taxonomy_path.discipline.ascii');
        $this->assertEquals(trim($expectedAsciiTree), $curriculum->asciiDump(), "The ASCII representation of the discipline curriculum tree, based on the taxonomy path, is as expected.");

        $curriculum->setTreeBasedOnTaxonPath($paths, 'competency');

        // Load the expected competency ASCII tree.
        $expectedAsciiTree = file_get_contents(FIXTURES_DIR . '/curriculum-data/per_taxonomy_path.competency.ascii');
        $this->assertEquals(trim($expectedAsciiTree), $curriculum->asciiDump(), "The ASCII representation of the competency curriculum tree, based on the taxonomy path, is as expected.");
    }

    /**
     * Test treating a taxonomy tree.
     */
    public function testTaxonomyTreeHandling()
    {
        $trees = json_decode(
            file_get_contents(FIXTURES_DIR . '/curriculum-data/per_taxonomy_tree.json'),
            true
        );

        // Create a new curriculum element.
        $curriculum = PerCurriculum::createFromData(
            FIXTURES_DIR . '/curriculum-data/per-api',
            PerCurriculum::CURRICULUM_API
        );

        $curriculum->setTreeBasedOnTaxonTree($trees);

        // Load the expected competency ASCII tree.
        $expectedAsciiTree = file_get_contents(FIXTURES_DIR . '/curriculum-data/per_taxonomy_path.competency.ascii');
        $this->assertEquals(trim($expectedAsciiTree), $curriculum->asciiDump(), "The ASCII representation of the competency curriculum tree, based on the taxonomy tree, is as expected.");
    }
}