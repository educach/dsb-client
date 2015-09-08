<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Curriculum\BaseCurriculum.
 */

namespace Educa\DSB\Client\Curriculum;

use Educa\DSB\Client\Curriculum\Term\TermInterface;
use Educa\DSB\Client\Curriculum\Term\BaseTerm;

abstract class BaseCurriculum implements CurriculumInterface
{

    /**
     * The root element of the curriculum tree.
     *
     * @var \Educa\DSB\Client\Curriculum\Term\TermInterface
     */
    protected $root;

    public function __construct(TermInterface $root = null)
    {
        $this->root = $root;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function createFromData($data, $context = null)
    {
        throw new \RuntimeException("BaseCurriculum::createFromData() must be overwritten.");
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    abstract public function describeDataStructure();

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    abstract public function describeTermTypes();

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    abstract public function getTermType($identifier);

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    abstract public function getTermName($identifier);

    /**
     * {@inheritdoc}
     */
    public function getTree()
    {
        return $this->root;
    }

    /**
     * {@inheritdoc}
     */
    public function asciiDump()
    {
        if (empty($this->root)) {
            return '';
        } else {
            return $this->root->asciiDump();
        }
    }

    /**
     * Create a new curriculum tree based on a taxonomy path.
     *
     * The LOM-CH standard defines the "curricula" field (10), which stores
     * curriculum classification as "taxonomy paths", flat tree structural
     * representation of curriculum classification. It uses a very similar
     * structure to the LOM "classification" field (9). By passing such a
     * structure to this method, a new tree will be created representing this
     * structure, and the curriculum class instance will be updated with the
     * correct information.
     *
     * @param array $paths
     *    A list of paths, as described in the LOM-CH standard.
     * @param string $purpose
     *    (optional) The curriculum paths comes in 4 flavors, "discipline"
     *    "objective", "competency* and "educational level" paths. Only one can
     *    be treated at a time. Defaults to "discipline".
     *
     * @return this
     */
    public function setTreeBasedOnTaxonPath($paths, $purpose = 'discipline')
    {
        // Prepare a new root item.
        $this->root = new BaseTerm('root', 'root');

        // Prepare a "catalog" of entries, based on their identifiers. This
        // will allow us to easily convert the linear tree representation
        // (LOM describes branches only, with a single path; if a node has
        // multiple sub-branches, their will be multiple paths, and we can
        // link nodes together via their ID).
        $terms = array(
            'root' => $this->root,
        );

        foreach ($paths as $path) {
            // Cast to an array, just in case.
            $path = (array) $path;
            $pathPurpose = $path['purpose']['value'];

            if ($pathPurpose == $purpose) {
                foreach ($path['taxonPath'] as $i => $taxonPath) {
                    // Prepare the parent. For the first item, it is always the
                    // root element.
                    $parent = $terms['root'];
                    $parentId = 'root';
                    foreach ($taxonPath['taxon'] as $taxon) {
                        // Cast to an array, just in case.
                        $taxon = (array) $taxon;
                        $taxonId = $taxon['id'];

                        // Do we already have this term prepared?
                        if (isset($terms["$parentId:{$taxonId}"])) {
                            $term = $terms["$parentId:{$taxonId}"];
                        } else {
                            // Prepare a new term object. First, look for the
                            // term's type. This is defined in the official
                            // curriculum JSON definition.
                            $type = $this->getTermType($taxonId);

                            // Get the term's name.
                            $name = $this->getTermName($taxonId);

                            // Create the new term.
                            $term = new BaseTerm($type, $taxonId, $name);

                            // If this is a discipline, we treat it differently.
                            // Contexts, school levels and even school years can
                            // be merged, but disciplines follow unique paths.
                            // Disciplines are always represented as a "branch"
                            // with no sub-branches. Because of this, we add
                            // a unique integer to the ID, which will prevent it
                            // from being re-used for a different discipline
                            // path, and thus it won't get merged.
                            // For example, the following 3 paths:
                            // -- compulsory education
                            //    +- cycle_1
                            //       +- languages
                            // -- compulsory education
                            //    +- cycle_1
                            //       +- languages
                            //          +- french
                            // -- compulsory education
                            //    +- cycle_1
                            //       +- languages
                            //          +- german
                            // Should NOT be merged like this:
                            // -- compulsory education
                            //    +- cycle_1
                            //       +- languages
                            //          +- french
                            //          +- german
                            // But like this:
                            // -- compulsory education
                            //    +- cycle_1
                            //       +- languages
                            //       +- languages
                            //          +- french
                            //       +- languages
                            //          +- german
                            // The first "language" entry is a discipline on its
                            // own, and because there's a path ending with it,
                            // it was meant to be treated on its own as well.
                            if ($type === 'discipline') {
                                $taxonId = "{$i}-{$taxonId}";
                            }

                            // Store it.
                            $terms["$parentId:{$taxonId}"] = $term;
                        }

                        // Did we already add this term to the parent?
                        if (!$parent->hasChildren() || !in_array($term, $parent->getChildren())) {
                            // Add our term to the tree.
                            $parent->addChild($term);
                        }

                        // Our term is now the parent, in preparation for the
                        // next item.
                        $parent = $term;
                        $parentId .= ":{$taxonId}";
                    }
                }
            }
        }

        return $this;
    }
}
