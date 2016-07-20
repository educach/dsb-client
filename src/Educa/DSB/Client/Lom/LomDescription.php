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
        if (!$this->isLegacyLOMCHFormat()) {
            trigger_error("getLomId() is deprecated and will be removed in a future version. The new LOM-CH standard doesn't have a lomId field anymore.", E_USER_NOTICE);
        }
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
        $path = $this->isLegacyLOMCHFormat() ? 'general.description' : 'general.description.0';
        return $this->getField($path, $languageFallback);
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

    /**
     * Check whether we are dealing with a legacy LOM-CH format.
     *
     * @return bool
     *    True if this is an older format, false otherwise.
     */
    public function isLegacyLOMCHFormat()
    {
        $metaDataSchema = $this->getField('metaMetadata.metadataSchema');
        if (!empty($metaDataSchema)) {
            // Is the data an array? If not, we are dealing with a legacy
            // format. If it is, we are dealing with a format that at least
            // respects the LOM standard.
            if (is_array($metaDataSchema)) {
                foreach ($metaDataSchema as $schema) {
                    // Make sure we are comparing a LOM-CHv* schema, and that
                    // the version is higher than 1.2.
                    if (
                        preg_match('/^LOM-CHv\d/i', $schema) &&
                        version_compare($schema, 'LOM-CHv1.2') !== -1
                    ) {
                        // Found a match. Don't treat this as a legacy format.
                        return false;
                    }
                }
            }
        }

        // By default, treat it as a legacy format.
        return true;
    }

}

