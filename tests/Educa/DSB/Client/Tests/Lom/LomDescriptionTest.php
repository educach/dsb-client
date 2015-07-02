<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Tests\Lom\LomDescriptionTest.
 */

namespace Educa\DSB\Client\Tests\Lom;

use Educa\DSB\Client\Lom\LomDescription;

class LomDescriptionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test the parsing of hierarchy field names.
     *
     * LomDescription::getField() accepts simple field names, like 'lomId', but
     * also accepts more complex field names, like 'general.identifier.catalog'.
     * This tests the parsing logic functions correctly.
     */
    public function testFieldParsing()
    {
        $data = array(
          'lomId' => 'lom id',
            'general' => array(
              'title' => array(
                  'de' => 'general title de',
                  'fr' => 'general title fr',
                  'en' => 'general title en',
              ),
              'identifier' => array(array(
                  'catalog' => 'general identifier 0 catalog',
                  'entry' => 'general identifier 0 entry',
              )),
          ),
          'technical' => array(
              'format' => 'technical format',
              'previewImage' => array(
                  'image' => 'technical previewImage image',
                  'copyright' => 'technical previewImage copyright',
              ),
          ),
        );

        $lomDescription = new LomDescription($data);

        $this->assertFalse(
            $lomDescription->getField('general.not_exist'),
            "Getting an non-existent field returns false."
        );
        $this->assertEquals(
            'lom id',
            $lomDescription->getField('lomId'),
            "Getting the lomId field works."
        );
        $this->assertEquals(
            $data['general'],
            $lomDescription->getField('general'),
            "Getting the general data works."
        );
        $this->assertEquals(
            'general title de',
            $lomDescription->getField('general.title'),
            "Getting the general.title field works; it detects the LangString format and returns German by default."
        );
        $this->assertEquals(
            'general title fr',
            $lomDescription->getField('general.title', array('fr', 'de')),
            "Getting the general.title field works; it gets the language fallback array and matches the French version first."
        );
        $this->assertEquals(
            'general title en',
            $lomDescription->getField('general.title', array('non-existent', 'en')),
            "Getting the general.title field works; it gets the language fallback array, correctly ignores a non-existent key and matches the English version first."
        );
        $this->assertEquals(
            'general identifier 0 catalog',
            $lomDescription->getField('general.identifier.0.catalog'),
            "Getting the general.identifier.0.catalog field works."
        );
        $this->assertEquals(
            'general identifier 0 entry',
            $lomDescription->getField('general.identifier.0.entry'),
            "Getting the general.identifier.0.entry field works."
        );
        $this->assertEquals(
            'technical format',
            $lomDescription->getField('technical.format'),
            "Getting the technical.format field works."
        );
        $this->assertEquals(
            'technical previewImage image',
            $lomDescription->getField('technical.previewImage.image'),
            "Getting the technical.previewImage.image field works."
        );
        $this->assertEquals(
            $data['technical']['previewImage'],
            $lomDescription->getField('technical.previewImage'),
            "Getting the technical.previewImage field works."
        );
    }
}
