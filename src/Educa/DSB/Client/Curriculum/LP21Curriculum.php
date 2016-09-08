<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Curriculum\LP21Curriculum.
 */

namespace Educa\DSB\Client\Curriculum;

use Educa\DSB\Client\Utils;
use Educa\DSB\Client\Curriculum\Term\TermInterface;
use Educa\DSB\Client\Curriculum\Term\LP21Term;
use Educa\DSB\Client\Curriculum\CurriculumInvalidContextException;
use Sabre\Xml\Reader;
use Sabre\Xml\Element\KeyValue;

/**
 * @codeCoverageIgnore
 */
class LP21Curriculum extends BaseCurriculum
{

    const CURRICULUM_XML = 'curriculum xml';

    /**
     * The list of all terms, with their associated term type.
     *
     * @var array
     */
    protected $curriculumDictionary;

    /**
     * The sources of taxonomy paths that can be treated by this class.
     *
     * @var array
     */
    protected $taxonPathSources = array('lp21');

    /**
     * {@inheritdoc}
     *
     * @param string $context
     *    A context, explaining what kind of data this is. Possible contexts:
     *    - EducaCurriculum::CURRICULUM_XML: Representation of the curriculum
     *      structure, in XML. This information can be found on the official
     *      lehrplan.ch website.
     */
    public static function createFromData($data, $context = self::CURRICULUM_XML)
    {
        switch ($context) {
            case self::CURRICULUM_XML:
                $data = self::parseCurriculumXml($data);
                $curriculum = new LP21Curriculum($data->curriculum);
                $curriculum->setCurriculumDictionary($data->dictionary);
                return $curriculum;
        }

        // @codeCoverageIgnoreStart
        throw new CurriculumInvalidContextException();
        // @codeCoverageIgnoreEnd
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function describeDataStructure()
    {
        return array(
            (object) array(
                'type' => 'fachbereich',
                'childTypes' => array('fach'),
                'entry' => (object) array(
                    'de' => "Fachbereich",
                ),
            ),
            (object) array(
                'type' => 'fach',
                'childTypes' => array('kompetenzbereich'),
                'entry' => (object) array(
                    'de' => "Fach",
                ),
            ),
            (object) array(
                'type' => 'kompetenzbereich',
                'childTypes' => array('handlungs_themenaspekt'),
                'entry' => (object) array(
                    'de' => "Kompetenzbereich",
                ),
            ),
            (object) array(
                'type' => 'handlungs_themenaspekt',
                'childTypes' => array('kompetenz'),
                'entry' => (object) array(
                    'de' => "Handlungs/Themenaspekt",
                ),
            ),
            (object) array(
                'type' => 'kompetenz',
                'childTypes' => array('kompetenzstufe'),
                'entry' => (object) array(
                    'de' => "Kompetenz",
                ),
            ),
            (object) array(
                'type' => 'kompetenzstufe',
                'childTypes' => array(),
                'entry' => (object) array(
                    'de' => "Kompetenzstufe",
                ),
            ),
        );
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function describeTermTypes()
    {
        return array(
            (object) array(
                'type' => 'fachbereich',
                'purpose' => array(
                    'LOM-CHv1.2' => 'discipline',
                ),
            ),
            (object) array(
                'type' => 'fach',
                'purpose' => array(
                    'LOM-CHv1.2' => 'discipline',
                ),
            ),
            (object) array(
                'type' => 'kompetenzbereich',
                'purpose' => array(
                    'LOM-CHv1.2' => 'objective',
                ),
            ),
            (object) array(
                'type' => 'handlungs-themenaspekt',
                'purpose' => array(
                    'LOM-CHv1.2' => 'objective',
                ),
            ),
            (object) array(
                'type' => 'kompetenz',
                'purpose' => array(
                    'LOM-CHv1.2' => 'objective',
                ),
            ),
            (object) array(
                'type' => 'kompetenzbstufe',
                'purpose' => array(
                    'LOM-CHv1.2' => 'objective',
                ),
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getTermType($identifier)
    {
        return isset($this->curriculumDictionary[$identifier]) ? $this->curriculumDictionary[$identifier]->type : 'root';
    }

    /**
     * {@inheritdoc}
     */
    public function getTermName($identifier)
    {
        return isset($this->curriculumDictionary[$identifier]->name) ? $this->curriculumDictionary[$identifier]->name : 'n/a';
    }

    /**
     * Parse the curriculum definition file.
     *
     * By passing the official curriculum definition file (XML), this method
     * will parse it and return a curriculum definition it can understand and
     * treat. It mainly needs a "dictionary" of term types.
     *
     * @param string $curriculumXml
     *    The curriculum definition file, in XML.
     * @param string $variant
     *    (optional) The variant of the curriculum to parse. Defaults to 'V_EF'.
     *
     * @return array
     *    An object with 2 properties:
     *    - curriculum: A parsed and prepared curriculum tree. It uses
     *      Educa\DSB\Client\Curriculum\Term\LP21Term elements to define
     *      the curriculum tree.
     *    - dictionary: A dictionary of term identifiers, with name and type
     *      information for each one of them.
     *
     * @see \Educa\DSB\Client\Curriculum\LP21Curriculum::setCurriculumDictionary()
     */
    public static function parseCurriculumXml($curriculumXml, $variant = 'V_EF')
    {
        $reader = new Reader();

        // Prepare custom handlers for reading an XML node. See the Sabre\Xml
        // documentation for more information.
        $baseHandler = function($reader) use ($variant) {
            $node = new \stdClass();

            // Fetch the attributes. We want the UUID attribute.
            $attributes = $reader->parseAttributes();
            $node->uuid = trim($attributes['uuid']);

            // We derive the type from the node name.
            $node->type = strtolower(
                str_replace('{}', '', trim($reader->getClark()))
            );

            // Give a default description.
            $node->description = (object) array(
                'de' => ''
            );

            // Fetch the descendants.
            $children = $reader->parseInnerTree();
            if (!empty($children)) {
                $node->children = array();
                foreach($children as $child) {
                    // Look for child types that are relevant for us. Some must
                    // be parsed as child types of their own, others should be
                    // treated as being part of the current node.
                    if (in_array($child['name'], array(
                        '{}fach',
                        '{}kompetenzbereich',
                        '{}handlungs-themenaspekt',
                        '{}kompetenz',
                        '{}kompetenzstufe',
                    ))) {
                        $node->children[] = $child;
                    } elseif ($child['name'] == '{}bezeichnung') {
                        $node->description = (object) array_reduce(
                            $child['value'],
                            function($carry, $item) {
                                $langcode = strtolower(
                                    str_replace('{}', '', $item['name'])
                                );
                                $carry[$langcode] = $item['value'];
                                return $carry;
                            },
                            array()
                        );
                    } elseif ($child['name'] == '{}kantone') {
                        $node->cantons = array_map('trim', explode(',', $child['value']));
                    }
                }
            }

            if (
                !empty($node->cantons) &&
                !in_array($variant, $node->cantons)
            ) {
                return null;
            }

            return $node;
        };

        $kompetenzstufeHandler = function($reader) use ($variant) {
            $nodes = array();
            $cycle = $url = $version = $code = null;

            // Fetch the descendants.
            $children = $reader->parseInnerTree();
            if (!empty($children)) {
                foreach($children as $child) {
                    if ($child['name'] == '{}absaetze') {
                        $nodes = $child['value'];
                    } elseif ($child['name'] == '{}zyklus') {
                        $cycle = trim($child['value']);
                    } elseif ($child['name'] == '{}lehrplanversion') {
                        $version = trim($child['value']);
                    } elseif (
                        $child['name'] == '{}kanton' &&
                        $child['attributes']['id'] == $variant
                    ) {
                        foreach ($child['value'] as $grandChild) {
                            if ($grandChild['name'] == '{}code') {
                                $code = trim($grandChild['value']);
                            } elseif ($grandChild['name'] == '{}url') {
                                $url = trim($grandChild['value']);
                            }
                        }
                    }
                }
            }

            // Map all the Kompetenzstufe properties to the child Absaetzen.
            return array_map(function($node) use ($cycle, $url, $version, $code) {
                if (isset($cycle)) {
                    $node->cycle = $cycle;
                }
                if (isset($url)) {
                    $node->url = $url;
                }
                if (isset($version)) {
                    $node->version = $version;
                }
                if (isset($code)) {
                    $node->code = $code;
                }
                return $node;
            }, $nodes);
        };

        $absaetzeHandler = function($reader) {
            $nodes = array();

            // Fetch the descendants.
            $children = $reader->parseInnerTree();
            if (!empty($children)) {
                foreach($children as $child) {
                    if ($child['name'] == '{}bezeichnung') {
                        $node = new \stdClass();
                        // We treat it as a "Kompetenzstufe".
                        $node->type = 'kompetenzstufe';
                        $node->description = (object) array_reduce(
                            $child['value'],
                            function($carry, $item) {
                                $langcode = strtolower(
                                    str_replace('{}', '', $item['name'])
                                );
                                $carry[$langcode] = $item['value'];
                                return $carry;
                            },
                            array()
                        );
                        // The UUID is on the child Bezeichnung element, not our
                        // own node.
                        $node->uuid = $child['attributes']['uuid'];
                        $nodes[] = $node;
                    }
                }
            }

            return $nodes;
        };

        // Register our handler for the following node types. All others will be
        // treated with the default one provided by Sabre\Xml, but we don't
        // really care.
        $reader->elementMap = [
            '{}fachbereich' => $baseHandler,
            '{}fach' => $baseHandler,
            '{}kompetenzbereich' => $baseHandler,
            '{}handlungs-themenaspekt' => $baseHandler,
            '{}kompetenz' => $baseHandler,
            '{}kompetenzstufe' => $kompetenzstufeHandler,
            '{}absaetze' => $absaetzeHandler,
        ];

        // Parse the data.
        $reader->xml($curriculumXml);
        $data = $reader->parse();

        // Prepare the dictionary.
        $dictionary = array();

        // Prepare our root element.
        $root = new LP21Term('root', 'root');

        // Now, recursively parse the tree, transforming it into a tree of
        // LP21Term instances.
        $recurse = function($tree, $parent) use (&$recurse, &$dictionary) {
            foreach ($tree as $item) {
                // Fetch our nodes.
                $nodes = $item['value'];
                if (!is_array($nodes)) {
                    $nodes = [$nodes];
                }

                // Double check the format. Is this one of our nodes?
                foreach ($nodes as $node) {
                    if (
                        isset($node->uuid) &&
                        isset($node->type) &&
                        isset($node->description)
                    ) {
                        $term = new LP21Term(
                            $node->type,
                            $node->uuid,
                            $node->description
                        );
                        $parent->addChild($term);

                        // Add it to our dictionary.
                        $dictionary[$node->uuid] = (object) array(
                            'name' => $node->description,
                            'type' => $node->type
                        );

                        // Do we have an objective code?
                        if (!empty($node->code)) {
                            $term->setCode($node->code);
                            $dictionary[$node->uuid]->code = $node->code;
                        }

                        // Do we have any cantons information?
                        if (!empty($node->cantons)) {
                            $term->setCantons($node->cantons);
                            $dictionary[$node->uuid]->cantons = $node->cantons;
                        }

                        // Do we have curriculum version information?
                        if (!empty($node->version)) {
                            $term->setVersion($node->version);
                            $dictionary[$node->uuid]->version = $node->version;
                        }

                        // Do we have URL information?
                        if (!empty($node->url)) {
                            $term->setUrl($node->url);
                            $dictionary[$node->uuid]->url = $node->url;
                        }

                        // Do we have cycle information?
                        if (!empty($node->cycle)) {
                            $cycles = str_split($node->cycle);
                            $term->setCycles($cycles);
                            $dictionary[$node->uuid]->cycles = $cycles;
                        }

                        if (!empty($node->children)) {
                            $recurse($node->children, $term);
                        }
                    }
                }
            }
        };
        $recurse($data['value'], $root);

        // Return the parsed data.
        return (object) array(
            'curriculum' => $root,
            'dictionary' => $dictionary,
        );
    }

    /**
     * Set the curriculum dictionary.
     *
     * @param array $dictionary
     *
     * @return this
     *
     * @see \Educa\DSB\Client\Curriculum\LP21Curriculum::parseCurriculumXml().
     */
    public function setCurriculumDictionary($dictionary)
    {
        $this->curriculumDictionary = $dictionary;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function termFactory($type, $taxonId, $name = null)
    {
        $code = $version = $url = $cycles = $cantons = null;
        if (isset($this->curriculumDictionary[$taxonId])) {
            $definition = $this->curriculumDictionary[$taxonId];

            if (isset($definition->url)) {
                $url = $definition->url;
            }

            if (isset($definition->version)) {
                $version = $definition->version;
            }

            if (isset($definition->code)) {
                $code = $definition->code;
            }

            if (isset($definition->cycles)) {
                $cycles = $definition->cycles;
            }

            if (isset($definition->cantons)) {
                $cantons = $definition->cantons;
            }

            // Always fetch the name from the local data. The data passed may be
            // stale, as it usually comes from the dsb API. Normally, local data
            // is refreshed on regular bases, so should be more up-to-date.
            if (isset($definition->name)) {
                $name = $definition->name;
            }
        }

        return new LP21Term($type, $taxonId, $name, $code, $version, $url, $cycles, $cantons);
    }

    /**
     * {@inheritdoc}
     */
    protected function taxonIsDiscipline($taxon)
    {
        // First check the parent implementation. If it is false, use a legacy
        // method.
        if (parent::taxonIsDiscipline($taxon)) {
            return true;
        } else {
            return in_array($this->getTermType($taxon['id']), array(
                'fachbereich',
                'fach',
                'kompetenzbereich',
                'handlungs_themenaspekt',
            ));
        }
    }

}
