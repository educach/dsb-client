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
     * The term's root parent, if any.
     *
     * @var \Educa\DSB\Client\Curriculum\Term\EditableTermInterface
     */
    protected $root;

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

    public function __construct($type, $id)
    {
        $this->type = $type;
        $this->id = $id;
        $this->children = array();
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function describe()
    {
        return (object) array(
            'type' => $this->type,
            'id' => $this->id,
        );
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
        return !isset($this->root);
    }

    /**
     * {@inheritdoc}
     */
    public function getRoot()
    {
        if (!$this->isRoot()) {
            return $this->root;
        } else {
            throw new TermIsRootException("Term {$this->type}:{$this->id} is root.");
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
    public function addChild(TermInterface $term)
    {
        // Fetch the root element. If the current term is the root element, use
        // it as the root element.
        if ($this->isRoot()) {
            $term->setRoot($this);
        } else {
            $term->setRoot($this->getRoot());
        }

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
    public function setRoot(TermInterface $term)
    {
        $this->root = $term;

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

}
