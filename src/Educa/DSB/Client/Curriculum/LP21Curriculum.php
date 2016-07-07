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
                'type' => 'root',
                'child_types' => array(
                    (object) array(
                        'type' => 'zyklus',
                        'required' => true,
                    ),
                ),
            ),
            (object) array(
                'type' => 'zyklus',
                'child_types' => array(
                    (object) array(
                        'type' => 'fachbereich',
                        'required' => true,
                    ),
                ),
            ),
            (object) array(
                'type' => 'fachbereich',
                'child_types' => array(
                    (object) array(
                        'type' => 'fach',
                        'required' => false,
                    ),
                    (object) array(
                        'type' => 'kompetenzbereich',
                        'required' => false,
                    ),
                ),
            ),
            (object) array(
                'type' => 'fach',
                'child_types' => array(
                    (object) array(
                        'type' => 'kompetenzbereich',
                        'required' => true,
                    ),
                ),
            ),
            (object) array(
                'type' => 'kompetenzbereich',
                'child_types' => array(
                    (object) array(
                        'type' => 'handlungs_themenaspekt',
                        'required' => false,
                    ),
                    (object) array(
                        'type' => 'kompetenz',
                        'required' => false,
                    ),
                ),
            ),
            (object) array(
                'type' => 'handlungs_themenaspekt',
                'child_types' => array(
                    (object) array(
                        'type' => 'kompetenz',
                        'required' => true,
                    ),
                ),
            ),
            (object) array(
                'type' => 'kompetenz',
                'child_types' => array(
                    (object) array(
                        'type' => 'kompetenzstufe',
                        'required' => true,
                    ),
                ),
            ),
            (object) array(
                'type' => 'kompetenzstufe',
                'child_types' => array(),
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
                'type' => 'root',
                'name' => (object) array(
                    'en' => "Root",
                    'de' => "Stamm",
                ),
            ),
            (object) array(
                'type' => 'zyklus',
                'name' => (object) array(
                    'en' => "Cycle",
                    'de' => "Zyklus",
                ),
            ),
            (object) array(
                'type' => 'fachbereich',
                'name' => (object) array(
                    'en' => "Field",
                    'de' => "Fachbereich",
                ),
            ),
            (object) array(
                'type' => 'fach',
                'name' => (object) array(
                    'en' => "Subject",
                    'de' => "Fach",
                ),
            ),
            (object) array(
                'type' => 'kompetenzbereich',
                'name' => (object) array(
                    'en' => "Area of competence",
                    'de' => "Kompetenzbereich",
                ),
            ),
            (object) array(
                'type' => 'handlungs_themenaspekt',
                'name' => (object) array(
                    'en' => "Action-/Topic aspect",
                    'de' => "Handlungs-/Themenaspekt",
                ),
            ),
            (object) array(
                'type' => 'kompetenz',
                'name' => (object) array(
                    'en' => "Competency",
                    'de' => "Kompetenz",
                ),
            ),
            (object) array(
                'type' => 'kompetenzstufe',
                'name' => (object) array(
                    'en' => "Competency level",
                    'de' => "Kompetenzstufe",
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
    public static function parseCurriculumXml($curriculumXml)
    {
        $reader = new Reader();

        // Prepare a custom handler for reading an XML node. See the Sabre\Xml
        // documentation for more information.
        $baseHandler = function($reader) {
            $node = new \stdClass();

            // Fetch the attributes. We want the UUID attribute.
            $attributes = $reader->parseAttributes();
            $node->uuid = trim($attributes['uuid']);

            // We derive the type from the node name.
            $node->type = strtolower(
                str_replace(array('{}', '-'), array('', '_'), trim($reader->getClark()))
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
                        '{}Fach',
                        '{}Kompetenzbereich',
                        '{}Handlungs-Themenaspekt',
                        '{}Kompetenz',
                        '{}Kompetenzstufe',
                    ))) {
                        $node->children[] = $child;
                    } elseif ($child['name'] == '{}Bezeichnung') {
                        $node->description = (object) array(
                            'de' => trim($child['value'])
                        );
                    } elseif ($child['name'] == '{}Zyklus') {
                        $node->cycle = trim($child['value']);
                    } elseif ($child['name'] == '{}URL') {
                        $node->url = trim($child['value']);
                    } elseif ($child['name'] == '{}Code') {
                        $node->code = trim($child['value']);
                    } elseif ($child['name'] == '{}Lehrplanversion') {
                        $node->version = trim($child['value']);
                    }
                }
            }

            return $node;
        };

        // Register our handler for the following node types. All others will be
        // treated with the default one provided by Sabre\Xml, but we don't
        // really care.
        $reader->elementMap = [
            '{}Fachbereich' => $baseHandler,
            '{}Fach' => $baseHandler,
            '{}Kompetenzbereich' => $baseHandler,
            '{}Handlungs-Themenaspekt' => $baseHandler,
            '{}Kompetenz' => $baseHandler,
            '{}Kompetenzstufe' => $baseHandler,
        ];

        // Parse the data.
        $reader->xml($curriculumXml);
        $data = $reader->parse();

        // Prepare the dictionary and list of taxonomy paths.
        $dictionary = array();
        $list = array();

        // We now have our tree, but we want a tree of LP21Terms. First, we want
        // to prepare our 3 cycle base terms. In the LP21 XML, the cycles are at
        // the leaves of the tree, not the root. This makes sense in the LP21
        // document, but we want to start with the cycles. That is why we
        // prepare them here, and we will add relevant trees underneath when
        // needed.
        $cycle1 = new LP21Term('zyklus', 1, (object) array(
            'de' => "1. Zyklus",
        ));
        $cycle2 = new LP21Term('zyklus', 2, (object) array(
            'de' => "2. Zyklus",
        ));
        $cycle3 = new LP21Term('zyklus', 3, (object) array(
            'de' => "3. Zyklus",
        ));

        // Prepare our root element, and add our cycles to it.
        $root = new LP21Term('root', 'root');
        $root
            ->addChild($cycle1)
            ->addChild($cycle2)
            ->addChild($cycle3);

        // Store the term definitions in the dictionary while we are at it.
        foreach (array(1, 2, 3) as $value) {
            $dictionary[$value] = (object) array(
                'name' => (object) array(
                    'de' => "{$value}. Zyklus",
                ),
                'type' => 'zyklus'
            );
        }

        // Now, recursively parse the tree. The way we proceed is to find the
        // "taxonomy" paths, from the root element up to the cycle
        // information. This will allow us to reconstruct the tree with cycle
        // information at the root.
        $recurse = function($tree, $parentPath = '') use (&$recurse, &$list, &$dictionary) {
            foreach ($tree as $item) {
                // Fetch our node.
                $node = $item['value'];

                // Double check the format. Is this one of our nodes?
                if (isset($node->uuid) && isset($node->type) && isset($node->description)) {
                    // Add it to our dictionary.
                    $dictionary[$node->uuid] = (object) array(
                        'name' => $node->description,
                        'type' => $node->type
                    );

                    // Do we have an objective code?
                    if (!empty($node->code)) {
                        $dictionary[$node->uuid]->code = $node->code;
                    }

                    // Do we have curriculum version information?
                    if (!empty($node->version)) {
                        $dictionary[$node->uuid]->version = $node->version;
                    }

                    // Do we have URL information?
                    if (!empty($node->url)) {
                        $dictionary[$node->uuid]->url = $node->url;
                    }

                    // Do we have cycle information? If so, we are at the end
                    // of our path.
                    if (!empty($node->cycle)) {
                        // Cycle information is stored as a list of numbers,
                        // with no separations. Split to get the cycles, and
                        // iterate over them to add our tree to the relevant
                        // cycle terms.
                        foreach (str_split($node->cycle) as $cycle) {
                            $list[] = preg_replace('/^:/', '', "$parentPath:{$node->uuid}:$cycle");
                        }
                    } elseif (!empty($node->children)) {
                        $recurse($node->children, "$parentPath:{$node->uuid}");
                    }
                }
            }
        };
        $recurse($data['value']);

        // Now we have our list of paths. We can construct our tree of
        // LP21Terms.
        foreach ($list as $path) {
            $parts = explode(':', $path);
            $cycle = (int) array_pop($parts);

            switch ($cycle) {
                case 1:
                    $parentTerm = $cycle1;
                    break;

                case 2:
                    $parentTerm = $cycle2;
                    break;

                case 3:
                    $parentTerm = $cycle3;
                    break;
            }

            // We now have our first parent term. Iterate over the parts, and
            // construct the tree.
            while ($uuid = array_shift($parts)) {
                // Do we already have this term?
                if ($parentTerm->hasChildren()) {
                    $term = $parentTerm->findChildByIdentifier($uuid);
                } else {
                    $term = null;
                }

                // If not, create it now and add it to the tree.
                if (!isset($term)) {
                    $definition = $dictionary[$uuid];
                    $term = new LP21Term(
                        $definition->type,
                        $uuid,
                        $definition->name
                    );

                    if (isset($definition->url)) {
                        $term->setUrl($definition->url);
                    }

                    if (isset($definition->code)) {
                        $term->setCode($definition->code);
                    }

                    if (isset($definition->version)) {
                        $term->setCurriculumVersion($definition->version);
                    }
                    $parentTerm->addChild($term);
                }

                // This term becomes the new parent term.
                $parentTerm = $term;
            }
        }

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
        $code = $version = $url = null;
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
        }

        return new LP21Term($type, $taxonId, $name, $code, $version, $url);
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
