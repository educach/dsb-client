<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Tests\Lom\LomDescriptionSearchResultTest.
 */

namespace Educa\DSB\Client\Tests\Lom;

use Educa\DSB\Client\Lom\LomDescriptionSearchResult;

class LomDescriptionSearchResultTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test the parsing of a LOM object.
     *
     * Pass a decoded JSON description and make sure the class fetches the
     * correct values.
     */
    public function testLomDataParsing()
    {
        $data = json_decode(
            file_get_contents(FIXTURES_DIR . '/lom-data/search_result.json'),
            true
        );
        $lomDescription = new LomDescriptionSearchResult($data);

        foreach ([
            'lomId' => '5ad12b78b09e52cb6bcef2134bb4ab9e',
            'title' => "Example : title, with special characters, like ’ and é",
            'teaser' => "\"Example!\" example teaser with accented words: zugehörig übernehmen Expériences",
            'language' => "de",
            'previewImage' => "http://example.ch/files/image.jpg",
            'metaContributorLogos' => [
                "http://example.com/files/logo.jpg",
                "http://example.com/files/logo.png",
            ],
            'ownerUsername' => "email@site.com",
            'ownerDisplayName' => "Partner name / suffix",
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
            file_get_contents(FIXTURES_DIR . '/lom-data/search_result.json'),
            true
        );
        $lomDescription = new LomDescriptionSearchResult($data);

        $this->assertEquals(
            "Example : title, with special characters, like ’ and é",
            $lomDescription->getTitle(),
            "The getTitle() method works."
        );
        $this->assertEquals(
            false,
            $lomDescription->getDescription(),
            "The getDescription() method works."
        );
        $this->assertEquals(
            "http://example.ch/files/image.jpg",
            $lomDescription->getPreviewImage(),
            "The getPreviewImage() method works."
        );
        $this->assertEquals(
            'email@site.com',
            $lomDescription->getOwnerUsername(),
            "The getOwnerUsername() method works."
        );
        $this->assertEquals(
            [
                "http://example.com/files/logo.jpg",
                "http://example.com/files/logo.png",
            ],
            $lomDescription->getContributorLogos(),
            "The getContributorLogos() method works."
        );
        $this->assertEquals(
            "\"Example!\" example teaser with accented words: zugehörig übernehmen Expériences",
            $lomDescription->getTeaser(),
            "The getTeaser() method works."
        );
        $this->assertEquals(
            "Partner name / suffix",
            $lomDescription->getOwnerDisplayName(),
            "The getOwnerDisplayName() method works."
        );
    }
}
