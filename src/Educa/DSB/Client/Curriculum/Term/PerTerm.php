<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Curriculum\Term\PerTerm.
 */

namespace Educa\DSB\Client\Curriculum\Term;

use Educa\DSB\Client\Curriculum\Term\BaseTerm;

class PerTerm extends BaseTerm
{

    /**
     * The term's code, if any.
     *
     * @var string
     */
    protected $code;

    /**
     * The term's URL property, if any.
     *
     * @var string
     */
    protected $url;

    /**
     * The term's school years property, if any.
     *
     * @var string
     */
    protected $schoolYears;

    public function __construct($type, $id, $name = null, $code = null, $url = null, $schoolYears = null)
    {
        $this
            ->setDescription($type, $id, $name)
            ->setCode($code)
            ->setUrl($url)
            ->setSchoolYears($schoolYears);
        $this->children = array();
    }

    /**
     * Set the objective code for this term.
     *
     * @param string $code
     *    The objective code, like 'FG 31'.
     *
     * @return this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * Get the objective code for this term.
     *
     * @return string|null
     *    The objective code, or null if none is set.
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set the term URL describing this term.
     *
     * @param string $url
     *    The URL describing this term.
     *
     * @return this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Get the term table.
     *
     * @return string|null
     *    The table.
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set the term's "school years".
     *
     * @param string|array $schoolYears
     *    The school years this term applies to. Format is "1-2", "3-4", etc.
     *    Can either be a "single" school year, given as a string, or multiple
     *    values.
     *
     * @return this
     */
    public function setSchoolYears($schoolYears)
    {
        $this->schoolYears = is_string($schoolYears) ? [$schoolYears] : $schoolYears;
        return $this;
    }

    /**
     * Get the term's "school years".
     *
     * @return array|null
     *    The school years this term applies to, or null if not set. Format is
     *    "1-2", "3-4", etc.
     */
    public function getSchoolYears()
    {
        return $this->schoolYears;
    }

    /**
     * Find a child term based on its identifier.
     *
     * @param string $id
     *    The identifier to search a child for.
     *
     * @return \Educa\DSB\Client\Curriculum\Term\TermInterface|null
     *    The child, or null if not found.
     *
     * @throws \Educa\DSB\Client\Curriculum\Term\TermHasNoChildrenException
     */
    public function findChildByIdentifier($id)
    {
        foreach ($this->getChildren() as $child) {
            if ($child->describe()->id == $id) {
                return $child;
            }
        }
    }

    /**
     * Find a child term based on its identifier, recursively.
     *
     * @param string $id
     *    The identifier to search a child for.
     *
     * @return \Educa\DSB\Client\Curriculum\Term\TermInterface|null
     *    The child, or null if not found.
     *
     * @throws \Educa\DSB\Client\Curriculum\Term\TermHasNoChildrenException
     */
    public function findChildByIdentifierRecursive($id)
    {
        foreach ($this->getChildren() as $child) {
            if ($child->describe()->id == $id) {
                return $child;
            } elseif ($child->hasChildren()) {
                if ($found = $child->findChildByIdentifierRecursive($id)) {
                    return $found;
                }
            }
        }
    }

    /**
     * Find a child term based on its name.
     *
     * @param string $name
     *    The name to search a child for.
     *
     * @return \Educa\DSB\Client\Curriculum\Term\TermInterface|null
     *    The child, or null if not found.
     *
     * @throws \Educa\DSB\Client\Curriculum\Term\TermHasNoChildrenException
     */
    public function findChildByName($name)
    {
        foreach ($this->getChildren() as $child) {
            if ($child->describe()->name == $name) {
                return $child;
            }
        }
    }

    /**
     * Find a child term based on its name, recursively.
     *
     * @param string $name
     *    The name to search a child for.
     *
     * @return \Educa\DSB\Client\Curriculum\Term\TermInterface|null
     *    The child, or null if not found.
     *
     * @throws \Educa\DSB\Client\Curriculum\Term\TermHasNoChildrenException
     */
    public function findChildByNameRecursive($name)
    {
        foreach ($this->getChildren() as $child) {
            if ($child->describe()->name == $name) {
                return $child;
            } elseif ($child->hasChildren()) {
                if ($found = $child->findChildByNameRecursive($name)) {
                    return $found;
                }
            }
        }
    }

}
