<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Lom\AlterableLomDescription.
 */

namespace Educa\DSB\Client\Lom;

use Educa\DSB\Client\Lom\LomDescription;
use Educa\DSB\Client\Lom\AlterableLomDescriptionInterface;

class AlterableLomDescription extends LomDescription implements AlterableLomDescriptionInterface
{

    /**
     * @{inheritdoc}
     */
    public function getRawData()
    {
        return $this->rawData;
    }

    /**
     * @{inheritdoc}
     */
    public function setField($fieldName, $value)
    {
        // We accept fields using a dot-notation to signify a hierarchy. For
        // example, technical.previewImage.image is a valid parameter. Use
        // eval() to set the correct element.
        $hierarchy = explode('.', $fieldName);
        $data = $this->rawData;
        eval(sprintf('$data["%s"] = $value;', implode('"]["', $hierarchy)));
        $this->rawData = $data;

        return $this;
    }

    /**
     * @{inheritdoc}
     */
    public function setLomId($lomId)
    {
        $this->lomId = $lomId;
        return $this;
    }

    /**
     * @{inheritdoc}
     */
    public function setOwnerUsername($username)
    {
        $this->ownerUsername = $username;
        return $this;
    }

}

