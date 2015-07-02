<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Lom\LomDescriptionSearchResult.
 *
 * The search result does not have the same data structure as an actual LOM
 * description. This class takes this into account.
 */

namespace Educa\DSB\Client\Lom;

use Educa\DSB\Client\Lom\LomDescription;

class LomDescriptionSearchResult extends LomDescription {

  /**
   * @{inheritdoc}
   *
   * Search results don't have LangStrings. They simply return the title in
   * the main language. The language fallback parameter will simply be
   * ignored.
   */
  public function getTitle($languageFallback = array()) {
    return $this->getField('title');
  }

  /**
   * @{inheritdoc}
   */
  public function getPreviewImage() {
    return $this->getField('previewImage');
  }

  /**
   * @{inheritdoc}
   */
  public function getContributorLogos() {
    return $this->getField('metaContributorLogos');
  }

  /**
   * Get the LOM description teaser.
   *
   * Alias for LomDescriptionInterface::getField('teaser').
   *
   * @return string|false
   *    The description teaser, or false if not available.
   */
  public function getTeaser() {
    return $this->getField('teaser');
  }

  /**
   * Get the LOM description contributor display name.
   *
   * Alias for LomDescriptionInterface::getField('ownerDisplayName').
   *
   * @return string|false
   *    The description owner display name, or false if not available.
   */
  public function getOwnerDisplayName() {
    return $this->getField('ownerDisplayName');
  }

}
