<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Tests\UtilsTest.
 */

namespace Educa\DSB\Client\Tests;

use Educa\DSB\Client\Utils;

class UtilsTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test the parsing of LangStrings (LS).
     */
    public function testLSParsing()
    {
        $this->assertEquals(
            'value fr',
            Utils::getLSValue([
                'fr' => 'value fr',
                'de' => 'value de',
            ], ['fr', 'de']),
            "Fetching the LangString data in the correct order (fr first)."
        );
        $this->assertEquals(
            'value de',
            Utils::getLSValue([
                'fr' => 'value fr',
                'de' => 'value de',
            ], ['de', 'fr']),
            "Fetching the LangString data in the correct order (de first)."
        );
        $this->assertEquals(
            'value de',
            Utils::getLSValue([
                'fr' => 'value fr',
                'de' => 'value de',
            ], ['en', 'de', 'fr']),
            "Fetching the LangString data in the correct order (en first, falling back to de)."
        );
        $this->assertEquals([
                'fr' => 'value fr',
                'de' => 'value de',
            ],
            Utils::getLSValue([
                'fr' => 'value fr',
                'de' => 'value de',
            ], []),
            "Returning the raw LangString data when no match (no language fallback)."
        );
        $this->assertEquals([
                'fr' => 'value fr',
                'de' => 'value de',
            ],
            Utils::getLSValue([
                'fr' => 'value fr',
                'de' => 'value de',
            ], ['en', 'it']),
            "Returning the raw LangString data when no match (fallback doesn't contain LS languages)."
        );
    }

    /**
     * Test the parsing of Vocabulary entries (VC).
     */
    public function testVCParsing()
    {
        $this->assertEquals(
            'value fr',
            Utils::getVCName([
                'name' => 'raw name',
                'ontologyName' => [
                    'fr' => 'value fr',
                    'de' => 'value de',
                ],
            ], ['fr', 'de']),
            "Fetching the Vocabulary name in the correct order (fr first)."
        );
        $this->assertEquals(
            'value de',
            Utils::getVCName([
                'name' => 'raw name',
                'ontologyName' =>  [
                    'fr' => 'value fr',
                    'de' => 'value de',
                ],
            ], ['de', 'fr']),
            "Fetching the Vocabulary name in the correct order (de first)."
        );
        $this->assertEquals(
            'value de',
            Utils::getVCName([
                'name' => 'raw name',
                'ontologyName' => [
                    'fr' => 'value fr',
                    'de' => 'value de',
                ],
            ], ['en', 'de', 'fr']),
            "Fetching the Vocabulary name in the correct order (en first, falling back to de)."
        );
        $this->assertEquals(
            'raw name',
            Utils::getVCName([
                'name' => 'raw name',
                'ontologyName' => [
                    'fr' => 'value fr',
                    'de' => 'value de',
                ],
            ], []),
            "Returning the raw Vocabulary name when no match (no language fallback)."
        );
        $this->assertEquals(
            'raw name',
            Utils::getVCName([
                'name' => 'raw name',
                'ontologyName' => [
                    'fr' => 'value fr',
                    'de' => 'value de',
                ],
            ], ['en', 'it']),
            "Returning the raw Vocabulary name when no match (fallback doesn't contain LS languages)."
        );
        $this->assertEquals(
            'raw name',
            Utils::getVCName([
                'name' => 'raw name',
            ], ['en', 'it']),
            "Returning the raw Vocabulary name when no Ontology data is present."
        );
    }
}
