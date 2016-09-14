<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Curriculum\MappableCurriculumInterface.
 */

namespace Educa\DSB\Client\Curriculum;

use Educa\DSB\Client\Curriculum\Term\TermInterface;

interface MappableCurriculumInterface
{

    /**
     * Map a term identifier from a curriculum to another one.
     *
     * This static method allows systems to map curricula information to another
     * curriculum, like mapping Standard Lehrplan data to PER. It is always
     * based on the passed term, usually on its ID.
     *
     * @param string $source
     *    The source curriculum, usually a string like "per", "lp21", "educa",
     *    etc.
     * @param string $target
     *    The target curriculum, similar to the $source parameter.
     * @param Educa\DSB\Client\Curriculum\Term\TermInterface $term
     *    The term to map.
     *
     * @return Educa\DSB\Client\Curriculum\Term\TermInterface|null
     *    A mapped term, or null if no mapping is available.
     */
    public function mapTerm($source, $target, TermInterface $term);
}
