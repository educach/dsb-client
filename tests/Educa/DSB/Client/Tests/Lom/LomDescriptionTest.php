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
    public function testFieldHierarchyParsing()
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

    /**
     * Test the parsing of a LOM object.
     *
     * Pass a decoded JSON description and make sure the class fetches the
     * correct values.
     */
    public function testLomDataParsing()
    {
        $data = json_decode(
            file_get_contents(FIXTURES_DIR . '/lom-data/full_valid.json'),
            true
        );
        $lomDescription = new LomDescription($data);

        foreach ([
            'lomId' => '8aaa3afb37a14bee858aaa3afb37a14bee85',
            'general.identifier.0.title' => 'Example',
            'general.language' => ['fr'],
            'general.keyword.0' => "Expériences",
            'general.keyword.2' => "Some word",
            'general.coverage.1' => "Fauna",
            'general.aggregationLevel' => [
                'source' => 'LOMv1.0',
                'value' => 2,
            ],
            'lifeCycle.version' => "Version 1",
            'lifeCycle.contribute.0.entity' => ['BEGIN:VCARD...END:VCARD'],
            'lifeCycle.contribute.1.role.value' => "author",
            'metaMetadata.identifier.0.catalog' => "archibald",
            'metaMetadata.contribute.1.role.value' => "creator",
            'metaMetadata.metaDataSchema' => "LOM-CHv1.1",
            'technical.format' => ["application/pdf"],
            'technical.otherPlatformRequirements' => "Textverarbeitungsprogramm",
            'technical.previewImage.image' => "http://example.com/files/421/Example.jpg",
            'education.learningResourceType.documentary.1.value' => "text",
            'education.learningResourceType.pedagogical.0.value' => "experiment",
            'education.intendedEndUserRole.1.value' => "teacher",
            'education.context' => [
                [
                    'source' => "LREv3.0",
                    'value' => "compulsory education",
                ],
                [
                    'source' => "LOM-CHv1.0",
                    'value' => "post-compulsory education",
                ],
            ],
            'relation.0.kind.value' => "is_based_on",
        ] as $field => $expected) {
            $this->assertEquals(
                $expected,
                $lomDescription->getField($field),
                "Getting the '$field' field works."
            );
        }
    }

    /**
     * Test the shortcut methods.
     *
     * In addition to the LomDescription::getField() method, there are several
     * methods that provide "shortcuts" for fetching specific data. Test these
     * as well.
     */
    public function testLomFieldShortcuts()
    {
        $data = json_decode(
            file_get_contents(FIXTURES_DIR . '/lom-data/full_valid.json'),
            true
        );
        $lomDescription = new LomDescription($data);

        $this->assertEquals(
            '8aaa3afb37a14bee858aaa3afb37a14bee85',
            $lomDescription->getLomId(),
            "The lomId() method works."
        );
        $this->assertEquals(
            "Example : title, with special characters, like ’ and é",
            $lomDescription->getTitle(),
            "The getTitle() method works."
        );
        $this->assertEquals(
            "Example : description, with special characters, like ’ and é. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc accumsan augue et elit elementum viverra. Nulla eget vulputate justo, et mollis urna. Nullam sed eros scelerisque, scelerisque augue a, pretium lectus. Aenean commodo dui in mi euismod vulputate. Suspendisse ultrices orci id ullamcorper lacinia. Mauris facilisis lacus congue lorem gravida posuere. Quisque aliquam, ligula at eleifend sollicitudin, turpis neque semper dolor, eu imperdiet tellus velit a neque. Donec non tristique sapien.",
            $lomDescription->getDescription(),
            "The getDescription() method works."
        );
        $this->assertEquals(
            "http://example.com/files/421/Example.jpg",
            $lomDescription->getPreviewImage(),
            "The getPreviewImage() method works."
        );
        $this->assertEquals(
            false,
            $lomDescription->getOwnerUsername(),
            "The getOwnerUsername() method works."
        );
        $this->assertEquals(
            [
                "archibald_file/24/logo.gif",
                "archibald_file/24/logo.gif",
                "http://file-api-dsb.educa.ch/files/300/logo_3.gif",
            ],
            $lomDescription->getContributorLogos(),
            "The getContributorLogos() method works."
        );
    }
}
