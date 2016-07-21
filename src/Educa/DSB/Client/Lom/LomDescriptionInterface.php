<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Lom\LomDescriptionInterface.
 */

namespace Educa\DSB\Client\Lom;

interface LomDescriptionInterface
{

    /**
     * Get a field value.
     *
     * @param string $fieldName
     *    The field name we wish to return. Can use JavaScript hierarchy notation,
     *    usings fullstops. E.g., general.identifier.catalog.
     * @param array $languageFallback
     *    (optional) An array of language codes to look for. This is necessary
     *    for LangString data, which has a key per language. The order of the
     *    language codes is the order in which the LangString will be searched.
     *    The first match will be returned. This parameter will be ignored for
     *    fields that are not LangStrings.
     *
     * @return mixed|false
     *    The field data, or false if not available.
     */
    public function getField($fieldName, $languageFallback = array('de', 'fr', 'it', 'rm', 'en'));

    /**
     * Get the LOM description ID.
     *
     * @return string|false
     *    The LOM ID, or false if not available.
     */
    public function getLomId();

    /**
     * Get the LOM description title.
     *
     * Alias for LomDescriptionInterface::getField('general.title').
     *
     * @param array $languageFallback
     *    (optional) An array of language codes to look for. This is necessary
     *    for LangString data, which has a key per language. The order of the
     *    language codes is the order in which the LangString will be searched.
     *    The first match will be returned.
     *
     * @return string|false
     *    The description title, or false if not available.
     */
    public function getTitle($languageFallback = array('de', 'fr', 'it', 'rm', 'en'));

    /**
     * Get the LOM description description.
     *
     * Alias for LomDescriptionInterface::getField('general.description').
     *
     * @param array $languageFallback
     *    (optional) An array of language codes to look for. This is necessary
     *    for LangString data, which has a key per language. The order of the
     *    language codes is the order in which the LangString will be searched.
     *    The first match will be returned.
     *
     * @return string|false
     *    The description title, or false if not available.
     */
    public function getDescription($languageFallback = array('de', 'fr', 'it', 'rm', 'en'));

    /**
     * Get the LOM description preview image.
     *
     * Alias for LomDescriptionInterface::getField('technical.previewImage').
     *
     * @return string|false
     *    The URL to the preview image, or false if none is available.
     */
    public function getPreviewImage();

    /**
     * Get the LOM description owner username.
     *
     * @return string|false
     *    The description owner username, or false if not available.
     */
    public function getOwnerUsername();

    /**
     * Get the LOM description contributor logo.
     *
     * This data can be found in multiple locations. There's no "easy" way to
     * get if not using this method.
     *
     * @return array|false
     *    The description contributor logos, or false if not available.
     */
    public function getContributorLogos();

}
