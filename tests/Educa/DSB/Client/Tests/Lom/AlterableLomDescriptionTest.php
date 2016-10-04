<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Tests\Lom\AlterableLomDescriptionTest.
 */

namespace Educa\DSB\Client\Tests\Lom;

use Educa\DSB\Client\Lom\AlterableLomDescription;

class AlterableLomDescriptionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test the parsing of hierarchy field names.
     *
     * AlterableLomDescription::setField() accepts simple field names, like
     * 'lomId', but also accepts more complex field names, like
     * 'general.identifier.catalog'. This tests the parsing logic functions
     * correctly for setting field values.
     */
    public function testFieldHierarchyParsing()
    {
        $lomDescription = new AlterableLomDescription([
            'general' => ['title' => 'value'],
        ]);

        $this->assertEquals(
            'value',
            $lomDescription->getField('general.title'),
            "Getting the general.title field works."
        );
        $this->assertEquals(
            $lomDescription,
            $lomDescription->setField('general.title', 'new title'),
            "Implements a fluent interface"
        );
        $this->assertEquals(
            'new title',
            $lomDescription->getField('general.title'),
            "Setting the general.title field works."
        );

        $lomDescription->setField('general.description', ['fr' => 'description']);
        $this->assertEquals(
            'description',
            $lomDescription->getField('general.description.fr'),
            "Setting the general.description field works."
        );

        $lomDescription->setLomId('some-id')->setOwnerUsername('john@site.com');
        $this->assertEquals(
            'some-id',
            $lomDescription->getLomId()
        );
        $this->assertEquals(
            'john@site.com',
            $lomDescription->getOwnerUsername()
        );

        $this->assertEquals(
            [
                'general' => [
                    'title' => 'new title',
                    'description' => ['fr' => 'description'],
                ],
            ],
            $lomDescription->getRawData()
        );
    }

}
