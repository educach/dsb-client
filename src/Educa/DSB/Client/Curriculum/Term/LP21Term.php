<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Curriculum\Term\LP21Term.
 */

namespace Educa\DSB\Client\Curriculum\Term;

use Educa\DSB\Client\Curriculum\Term\BaseTerm;

class LP21Term extends BaseTerm
{

    /**
     * The term's code, if any.
     *
     * @var string
     */
    protected $code;

    /**
     * The term's curriculum version, if any.
     *
     * @var string
     */
    protected $curriculumVersion;

    /**
     * The term's URL property, if any.
     *
     * @var string
     */
    protected $url;

    public function __construct($type, $id, $name = null, $code = null, $curriculumVersion = null, $url = null)
    {
        $this
            ->setDescription($type, $id, $name)
            ->setCode($code)
            ->setCurriculumVersion($curriculumVersion)
            ->setUrl($url);
        $this->children = array();
    }

    /**
     * Set the objective code for this term.
     *
     * @param string $code
     *    The objective code, like 'D.1.A.1.a'.
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
     * Set the curriculum version that applies to this term.
     *
     * @param string $curriculumVersion
     *    The curriculum version, like '1.0'.
     *
     * @return this
     */
    public function setCurriculumVersion($curriculumVersion)
    {
        $this->curriculumVersion = $curriculumVersion;
        return $this;
    }

    /**
     * Get the curriculum version that applies to this term.
     *
     * @return string|null
     *    The curriculum version, or null if none is set.
     */
    public function getCurriculumVersion()
    {
        return $this->curriculumVersion;
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
     * Get the term URL describing this term.
     *
     * @return string|null
     *    The URL describing this term, or null if not set.
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Find a child term based on its identifier.
     *
     * @param string $identifier
     *    The identifier to search a child for.
     *
     * @return \Educa\DSB\Client\Curriculum\Term\TermInterface|null
     *    The child, or null if not found.
     *
     * @throws \Educa\DSB\Client\Curriculum\Term\TermHasNoChildrenException
     */
    public function findChildByIdentifier($identifier)
    {
        foreach ($this->getChildren() as $child) {
            if ($child->describe()->id == $identifier) {
                return $child;
            }
        }
    }

}
