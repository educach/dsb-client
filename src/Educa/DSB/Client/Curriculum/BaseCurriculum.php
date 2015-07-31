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
     */
    abstract public static function createFromData($data);

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
        }

        $recursiveStringify = function($items, $depth = 0) use(&$recursiveStringify) {
            $string = '';
            foreach ($items as $item) {
                // Fetch the term description.
                $data = $item->describe();

                // Prepare the indentation, using whitespace. We use 4 spaces
                // for each level of depth.
                if ($depth) {
                    $string .= implode('', array_fill(0, $depth * 4, ' '));
                }
                $string .= ($depth ? '+' : '-') . "-- {$data->type}:{$data->id}\n";

                if ($item->hasChildren()) {
                    $string .= $recursiveStringify($item->getChildren(), $depth+1);
                }
            }
            return $string;
        };

        return trim($recursiveStringify(array($this->root)));
    }
}
