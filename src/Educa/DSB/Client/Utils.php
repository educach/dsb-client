<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Utils.
 */

namespace Educa\DSB\Client;

class Utils
{

    /**
     * Parse the LangString value and return the value in the correct language.
     *
     * LangStrings (LS) are a hash of language data, keyed by language code
     * (ISO_639-1, two characters). The method expects a language fallback list,
     * which it will iterate over, until it finds a match in the LangString. If
     * no match is found, the data is returned raw.
     *
     * @param array|object $langString
     *    The LangString hash.
     * @param array $languageFallback
     *    (optional) An array of language codes to look for. The order of the
     *    language codes is the order in which the LangString will be searched.
     *    The first match will be returned.
     *
     * @return string|array
     *    The found value, or simply the raw LangString if none was found.
     */
    public static function getLSValue($langString, array $languageFallback = array('de', 'fr', 'it', 'rm', 'en'))
    {
        if (is_scalar($langString)) {
            return $langString;
        } else {
            $langString = (array) $langString;

            foreach ($languageFallback as $language) {
                if (isset($langString[$language])) {
                    return $langString[$language];
                }
            }

            // If we didn't find a language key, we simply return the data as-is.
            return $langString;
        }
    }

    /**
     * Parse the Vocabulary entry and return the Ontology name in the correct
     * language.
     *
     * Vocabulary entries (VC) are a hash of vocabulary data, with the actual
     * human-readable name being a LangString (LS). This human-readable name
     * comes from the Ontology server, and is injected into the LOM object by
     * the REST API at load time.
     *
     * The method expects a language fallback list, which it will iterate over,
     * until it finds a match in the LangString. If no match is found, the data
     * is returned raw.
     *
     * @param array|object $vocabularyEntry
     *    The Vocabulary entry. It should contain an ontologyName key.
     * @param array $languageFallback
     *    (optional) An array of language codes to look for. The order of the
     *    language codes is the order in which the LangString will be searched.
     *    The first match will be returned. If none is given, the default of
     *    Educa\DSB\Client\Utils::getLSValue() will be used.
     *
     * @return string
     *    The found Ontology name, or simply the raw key if none was found.
     */
    public static function getVCName($vocabularyEntry, array $languageFallback = null)
    {
        $vocabularyEntry = (array) $vocabularyEntry;
        if (isset($vocabularyEntry['ontologyName'])) {
            if (isset($languageFallback)) {
                $name = self::getLSValue($vocabularyEntry['ontologyName'], $languageFallback);
            } else {
                $name = self::getLSValue($vocabularyEntry['ontologyName']);
            }

            if (is_array($name)) {
                // We got the raw LangString again, so no match. Return the raw
                // name.
                return $vocabularyEntry['name'];
            } else {
                return $name;
            }
        } else {
            return $vocabularyEntry['name'];
        }
    }
}