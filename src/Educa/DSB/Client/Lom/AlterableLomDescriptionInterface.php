<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Lom\AlterableLomDescriptionInterface.
 */

namespace Educa\DSB\Client\Lom;

interface AlterableLomDescriptionInterface extends LomDescriptionInterface
{

    /**
     * Get the raw values.
     *
     * @return array
     */
    public function getRawData();

    /**
     * Set a field value.
     *
     * @param string $fieldName
     *    The field name we wish to return. Can use JavaScript hierarchy notation,
     *    usings fullstops. E.g., general.identifier.catalog.
     * @param mixed $value
     *    The value
     */
    public function setField($fieldName, $value);

    /**
     * Set the LOM description ID.
     *
     * @param string $lomId
     */
    public function setLomId($lomId);

    /**
     * set the LOM description owner username.
     *
     * @param string $username
     */
    public function setOwnerUsername($username);

}
