<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Curriculum\BaseCurriculum.
 */

namespace Educa\DSB\Client\Curriculum;

use Educa\DSB\Client\Curriculum\Term\TermInterface;
use Educa\DSB\Client\Curriculum\Term\BaseTerm;
use Educa\DSB\Client\Utils;

abstract class BaseCurriculum implements CurriculumInterface
{

    /**
     * The root element of the curriculum tree.
     *
     * @var \Educa\DSB\Client\Curriculum\Term\TermInterface
     */
    protected $root;

    /**
     * The sources of taxonomy paths that can be treated by this class.
     *
     * @var array
     */
    protected $taxonPathSources = array();

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
     * {@inheritdoc}
     */
    public function setTreeBasedOnTaxonTree($trees)
    {
        // Prepare a new root item.
        $this->root = $this->termFactory('root', 'root');

        // Prepare a callback for recursively adding elements to the tree.
        $recursiveAdd = function($children, $parent) use (&$recursiveAdd) {
            foreach ($children as $child) {
                $taxonId = $child['id'];
                $term = $this->termFactory(
                    // Always fetch the type and name from the local data. The
                    // data in the trees may be stale, as it usually comes from
                    // the API. Normally, local data is refreshed on regular
                    // bases, so should be more up-to-date.
                    $this->getTermType($taxonId),
                    $taxonId,
                    $this->getTermName($taxonId)
                );
                $parent->addChild($term);

                if (!empty($child['childTaxons'])) {
                    $recursiveAdd($child['childTaxons'], $term);
                }
            }
        };

        foreach ($trees as $tree) {
            // Cast to an array, just in case.
            $tree = (array) $tree;

            // Check if we treat this path. It might be a different
            // source.
            if (!in_array($tree['source']['name'], $this->taxonPathSources)) {
                continue;
            }

            $recursiveAdd($tree['taxonTree'], $this->root);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setTreeBasedOnTaxonPath($paths, $purpose = 'discipline')
    {
        // Prepare a new root item.
        $this->root = $this->termFactory('root', 'root');

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
                    // Check if we treat this path. It might be a different
                    // source.
                    if (!in_array(Utils::getLSValue($taxonPath['source']), $this->taxonPathSources)) {
                        continue;
                    }

                    // Prepare the parent. For the first item, it is always the
                    // root element.
                    $parent = $terms['root'];
                    $parentId = 'root';
                    foreach ($taxonPath['taxon'] as $taxon) {
                        // Cast to an array, just in case.
                        $taxon = (array) $taxon;
                        $taxonId = $taxon['id'];

                        // Do we already have this term prepared?
                        if (isset($terms["{$parentId}:{$taxonId}"])) {
                            $term = $terms["{$parentId}:{$taxonId}"];
                        } else {
                            // Create the new term.
                            $term = $this->termFactory(
                                // Always fetch the type and name from the local
                                // data. The data in the trees may be stale, as
                                // it usually comes from the API. Normally,
                                // local data is refreshed on regular bases, so
                                // should be more up-to-date.
                                $this->getTermType($taxonId),
                                $taxonId,
                                $this->getTermName($taxonId)
                            );

                            // If this is a discipline, we treat it differently.
                            // Educational levels, competencies and even
                            // educational objectives can be merged, but
                            // disciplines follow unique paths.
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
                            if ($this->taxonIsDiscipline($taxon) && $pathPurpose == 'discipline') {
                                $taxonId = "{$i}-{$taxonId}";
                            }

                            // Store it.
                            $terms["{$parentId}:{$taxonId}"] = $term;
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

    /**
     * Helper method for creating new terms.
     *
     * This method allows subclasses to change the type of terms used in the
     * term trees.
     *
     * @param string $type
     * @param string $taxonId
     * @param array|object $name
     *
     * @return \Educa\DSB\Client\Curriculum\Term\TermInterface
     */
    protected function termFactory($type, $taxonId, $name = null)
    {
        return new BaseTerm($type, $taxonId, $name);
    }

    /**
     * Helper method for checking if a taxon is a discipline.
     *
     * @param array $taxon
     *    A taxon entry in a taxonomy path.
     *
     * @return bool
     *    True if the taxon is a discipline, false otherwise.
     */
    protected function taxonIsDiscipline($taxon)
    {
        return $this->getTermType($taxon['id']) == 'discipline';
    }
}
