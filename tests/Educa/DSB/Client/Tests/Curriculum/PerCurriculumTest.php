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
                'type' => 'cycles',
                'name' => (object) array(
                    'fr' => "Cycle 1",
                ),
            ),
            $data->dictionary['cycles:1'],
            "Found the correct data in the dictionary for item 'cycles:1' (Cycle 1)."
        );
        $this->assertEquals(
            (object) array(
                'type' => 'domaines',
                'name' => (object) array(
                    'fr' => "Arts",
                ),
            ),
            $data->dictionary['domaines:4'],
            "Found the correct data in the dictionary for item 'domaines:4' (Arts)."
        );
        $this->assertEquals(
            (object) array(
                'type' => 'objectifs',
                'name' => (object) array(
                    'fr' => "Expression et représentation (A 11 AV)",
                ),
                'code' => 'A 11 AV',
                'schoolYears' => ['1-2', '3-4']
            ),
            $data->dictionary['objectifs:77'],
            "Found the correct data in the dictionary for item 'objectifs:77' (A 11 AV)."
        );
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
