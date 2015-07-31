<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Curriculum\BaseCurriculum.
 */

namespace Educa\DSB\Client\Curriculum;

use Educa\DSB\Client\Curriculum\Term\TermInterface;

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
}
