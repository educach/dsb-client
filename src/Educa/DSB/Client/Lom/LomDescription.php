<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Lom\LomDescription.
 */

namespace Educa\DSB\Client\Lom;

use Educa\DSB\Client\Lom\LomDescriptionInterface;
use Educa\DSB\Client\Utils;

class LomDescription implements LomDescriptionInterface
{

    protected $rawData;

    public function __construct($data)
    {
        $this->rawData = $data;
    }

    /**
     * @{inheritdoc}
     */
    public function getField($fieldName, $languageFallback = array('de', 'fr', 'it', 'rm', 'en'))
    {
        // We accept fields using a dot-notation to signify a hierarchy. For
        // example, technical.previewImage.image is a valid parameter. Explode the
        // field name on '.', and recursively get the desired key.
        $hierarchy = explode('.', $fieldName);
        $fieldValue = $this->rawData;

        while ($fieldValue && count($hierarchy)) {
            $part = array_shift($hierarchy);

            $fieldValue = isset($fieldValue[$part]) ? $fieldValue[$part] : FALSE;
        }

        // If the value is not an array, return now.
        if (!is_array($fieldValue)) {
            return $fieldValue;
        } else {
            // It could be a LangString. In that case, we use the language
            // fallback. If we didn't find a language key, we simply return the
            // data as-is.
            return Utils::getLSValue($fieldValue, $languageFallback);
        }
    }

    /**
     * @{inheritdoc}
     */
    public function getLomId()
    {
        return $this->getField('lomId');
    }

    /**
     * @{inheritdoc}
     */
    public function getTitle($languageFallback = array('de', 'fr', 'it', 'rm', 'en'))
    {
        return $this->getField('general.title', $languageFallback);
    }

    /**
     * @{inheritdoc}
     */
    public function getDescription($languageFallback = array('de', 'fr', 'it', 'rm', 'en'))
    {
        return $this->getField('general.description', $languageFallback);
    }

    /**
     * @{inheritdoc}
     */
    public function getPreviewImage()
    {
        return $this->getField('technical.previewImage.image');
    }

    /**
     * @{inheritdoc}
     */
    public function getOwnerUsername()
    {
        return $this->getField('ownerUsername');
    }

    /**
     * @{inheritdoc}
     */
    public function getContributorLogos()
    {
        $logos = array();
        $contributors = $this->getField('metaMetadata.contribute');

        if (!empty($contributors)) {
            foreach ($contributors as $contributor) {
                // Does it have a VCARD?
                if (!empty($contributor['entity'])) {
                    foreach ($contributor['entity'] as $vcard) {
                        // We don't want to parse the VCARD; overkill. Just try to
                        // extract the logo.
                        $match = array();
                        if (preg_match('/^LOGO;VALUE:uri:(.+)$/m', $vcard, $match)) {
                            $logos[] = trim($match[1]);
                        }
                    }
                }
            }
        }

        return $logos;
    }

}

