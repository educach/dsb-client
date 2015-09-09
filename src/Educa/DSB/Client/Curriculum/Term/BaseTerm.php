<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Curriculum\Term\BaseTerm.
 */

namespace Educa\DSB\Client\Curriculum\Term;

use Educa\DSB\Client\Utils;

class BaseTerm implements EditableTermInterface
{

    /**
     * The term's parent, if any.
     *
     * @var \Educa\DSB\Client\Curriculum\Term\EditableTermInterface
     */
    protected $parent;

    /**
     * The term's "previous" sibling, if any.
     *
     * @var \Educa\DSB\Client\Curriculum\Term\EditableTermInterface
     */
    protected $prevSibling;

    /**
     * The term's "next" sibling, if any.
     *
     * @var \Educa\DSB\Client\Curriculum\Term\EditableTermInterface
     */
    protected $nextSibling;

    /**
     * The term's children, if any.
     *
     * @var array
     */
    protected $children;

    /**
     * The term's type.
     *
     * @var string
     */
    protected $type;

    /**
     * The term's identifier.
     *
     * @var string
     */
    protected $id;

    /**
     * The term's name, if any.
     *
     * @var string
     */
    protected $name;

    public function __construct($type, $id, $name = null)
    {
        $this->setDescription($type, $id, $name);
        $this->children = array();
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function describe()
    {
        $description = (object) array(
            'type' => $this->type,
            'id' => $this->id,
        );

        if (!empty($this->name)) {
            $description->name = $this->name;
        }

        return $description;
    }

    /**
     * Set the description information.
     *
     * Update the description information set in the constructor.
     *
     * @param string $type
     *    The term type.
     * @param string $id
     *    The term identifier.
     * @param array|object $name
     *    (optional) The term name, in LangString format.
     *
     * @return this
     */
    public function setDescription($type, $id, $name = null)
    {
        $this->type = $type;
        $this->id = $id;
        $this->name = $name;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasChildren()
    {
        return !empty($this->children);
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren()
    {
        if ($this->hasChildren()) {
            return $this->children;
        } else {
            throw new TermHasNoChildrenException("Term {$this->type}:{$this->id} has no children.");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasParent()
    {
        return isset($this->parent);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        if ($this->hasParent()) {
            return $this->parent;
        } else {
            throw new TermHasNoParentException("Term {$this->type}:{$this->id} has no parent.");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isRoot()
    {
        return !$this->hasParent();
    }

    /**
     * {@inheritdoc}
     */
    public function getRoot()
    {
        if (!$this->isRoot()) {
            return $this->getParent()->getRoot();
        } else {
            return $this;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasPrevSibling()
    {
        return isset($this->prevSibling);
    }

    /**
     * {@inheritdoc}
     */
    public function getPrevSibling()
    {
        if ($this->hasPrevSibling()) {
            return $this->prevSibling;
        } else {
            throw new TermHasNoPrevSiblingException("Term {$this->type}:{$this->id} has no previous sibling.");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasNextSibling()
    {
        return isset($this->nextSibling);
    }

    /**
     * {@inheritdoc}
     */
    public function getNextSibling()
    {
        if ($this->hasNextSibling()) {
            return $this->nextSibling;
        } else {
            throw new TermHasNoNextSiblingException("Term {$this->type}:{$this->id} has no next sibling.");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function asciiDump()
    {
        $recursiveStringify = function($items, $depth = 0) use(&$recursiveStringify) {
            $string = '';
            foreach ($items as $item) {
                // Prepare the indentation, using whitespace. We use 4 spaces
                // for each level of depth.
                if ($depth) {
                    $string .= implode('', array_fill(0, $depth * 4, ' '));
                }
                $string .= ($depth ? '+' : '-') . "-- {$item}\n";

                if ($item->hasChildren()) {
                    $string .= $recursiveStringify($item->getChildren(), $depth+1);
                }
            }
            return $string;
        };

        return trim($recursiveStringify(array($this)));
    }

    /**
     * {@inheritdoc}
     */
    public function addChild(EditableTermInterface $term)
    {
        // Add the current term as the child's parent.
        $term->setParent($this);

        // Do we already have children? If so, we need to add the last one as
        // the new one's "previous" sibling, and add the new one as the last
        // one's "next" sibling.
        if (!empty($this->children)) {
            // Fetch the last one.
            $lastTerm = $this->children[count($this->children)-1];

            // Add the "next" sibling to it.
            $lastTerm->setNextSibling($term);

            // And add it as the "previous" sibling for the new one.
            $term->setPrevSibling($lastTerm);
        }

        // Add it to our children list.
        $this->children[] = $term;

        // Support method chaining.
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setParent(TermInterface $term)
    {
        $this->parent = $term;

        // Support method chaining.
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPrevSibling(TermInterface $term)
    {
        $this->prevSibling = $term;

        // Support method chaining.
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setNextSibling(TermInterface $term)
    {
        $this->nextSibling = $term;

        // Support method chaining.
        return $this;
    }

    public function __toString()
    {
        return "{$this->type}:{$this->id}";
    }

}
