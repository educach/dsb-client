<?php

/**
 * @file
 * Contains \Educa\DSB\Client\Curriculum\CurriculumInterface.
 */

namespace Educa\DSB\Client\Curriculum;

interface CurriculumInterface
{

    /**
     * Get information about the curriculum.
     *
     * A curriculum should be usable inside the LOM "Classification" (9) field.
     * To this end, it exposes its structural information, both for machines and
     * humans. The return value is a tree of items, or "terms". Term types are
     * completely arbitrary, and are usually different per curriculum
     * implementation. It's up to the calling application to know how to treat
     * different terms of different types.
     *
     * Example:
     * @code
     * array(
     *     (object) array(
     *         'type' => 'context',
     *         'child_types' => array(
     *             (object) array(
     *                 'type' => 'school level',
     *                 'required' => false,
     *             ),
     *             (object) array(
     *                 'type' => 'discipline',
     *                 'required' => false,
     *             ),
     *         ),
     *     ),
     *     (object) array(
     *         'type' => 'school level',
     *         'child_types' => array(
     *             (object) array(
     *                 'type' => 'discipline',
     *                 'required' => true,
     *             ),
     *         ),
     *     ),
     *     (object) array(
     *         'type' => 'discipline',
     *         'child_types' => array(
     *             (object) array(
     *                 'type' => 'discipline',
     *                 'required' => false,
     *             ),
     *         ),
     *     ),
     * );
     * @endcode
     *
     * @return array
     *    A list of terms, each having the following properties:
     *    - type: The term's type. More information about types can be fetched
     *      using
     *      \Educa\DSB\Client\Curriculum\CurriculumInterface::describeTermTypes().
     *    - child_types: An array of possible child types. Each child item has
     *      the following properties:
     *      - type: The type of the child term (this can denote recursion).
     *      - required: A boolean indicating whether this term is optional or
     *        required, when in context of the parent term type.
     */
    public function describeDataStructure();

    /**
     * Get information about the curriculum types.
     *
     * Curricula can be vastly different, and it is not possible to catalog them
     * all or easily find a common playground. Curricula are represented as
     * trees, but each term at each level can have a different "type". This
     * method is for describing term types in a human-readable way.
     *
     * Example:
     * @code
     * array(
     *     (object) array(
     *         'type' => 'context',
     *         'name' => (object) array(
     *             'en' => "Context"
     *         ),
     *         'description' => (object) array(
     *             'en' => "Context explanation"
     *         ),
     *     ),
     *     (object) array(
     *         'type' => 'school level',
     *         'name' => (object) array(
     *             'en' => "School level"
     *         ),
     *         'description' => (object) array(
     *             'en' => "School level explanation"
     *         ),
     *     ),
     *     (object) array(
     *         'type' => 'discipline',
     *         'name' => (object) array(
     *             'en' => "Discipline"
     *         ),
     *         'description' => (object) array(
     *             'en' => "Discipline explanation"
     *         ),
     *     ),
     * );
     * @endcode
     *
     * @return array
     *    A list of term types, each entry having the following properties:
     *    - type: The machine-readable name of the type.
     *    - name: A LangString containing the human-readable name for this type.
     *      A LangString is a hash of data, keyed by language key. Language keys
     *      must follow the ISO_639-1 (two characters) standard and be lower
     *      case.
     *    - description: (optional) A LangString containing more detailed
     *      information of what this type is for.
     */
    public function describeTermTypes();

    /**
     * Get the curriculum tree.
     *
     * Fetch the tree representation of the current curriculum data. This will
     * return a list of \Educa\DSB\Client\Curriculum\Term\TermInterface
     * elements, which will themselves contain child data (if available),
     * representing the whole data tree.
     *
     * @return \Educa\DSB\Client\Curriculum\Term\TermInterface
     *    The root \Educa\DSB\Client\Curriculum\Term\TermInterface element,
     *    which contains child items, defining the curriculum tree.
     */
    public function getTree();

    /**
     * Return an ASCII representation of the tree.
     *
     * Useful for debugging, this method converts the tree to a string
     * representation and returns it as a string.
     *
     * Example:
     * @code
     * --- a
     *     +-- d
     *     +-- e
     * --- b
     * --- c
     *     +-- f
     *          +-- g
     *          +-- h
     * @endcode
     *
     * Where each item has the format "type:id", where "type" is the term's
     * type (see
     * \Educa\DSB\Client\Curriculum\CurriculumInterface::describeTermTypes())
     * and "id" is the item identifier.
     *
     * @return string
     *    An ASCII representation of the curriculum tree.
     *
     * @see \Educa\DSB\Client\Curriculum\Term\TermInterface::asciiDump()
     */
    public function asciiDump();

    /**
     * Create a new \Educa\DSB\Client\Curriculum\CurriculumInterface element.
     *
     * Passing a data object, returns a new
     * \Educa\DSB\Client\Curriculum\CurriculumInterface element. The data can
     * be representation of a full curriculum tree, or simply a portion of it.
     *
     * @param mixed $data
     *    An arbitrary representation of the curriculum data. This can vary
     *    greatly between implementations, and it is up to the calling
     *    application to know what to pass to the method.
     * @param mixed $context
     *    (optional) A context to help the class determine how to treat the
     *    data. Defaults to null.
     *
     * @return \Educa\DSB\Client\Curriculum\CurriculumInterface
     *    A new \Educa\DSB\Client\Curriculum\CurriculumInterface element.
     *
     * @throws \Educa\DSB\Client\Curriculum\CurriculumInvalidDataStructureException
     * @throws \Educa\DSB\Client\Curriculum\CurriculumInvalidContextException
     */
    public static function createFromData($data, $context = null);

    /**
     * Fetch the term's type.
     *
     * The stored curriculum data inside the LOM object contains no information
     * about what the term is. We compare the term's identifier to the
     * curriculum standard and determine its type.
     *
     * @param string $identifier
     *    The identifier of the term.
     *
     * @return string
     *    The term's type.
     */
    public function getTermType($identifier);

    /**
     * Fetch the term's name.
     *
     * The stored curriculum data inside the LOM object contains information
     * about the term's name, but this information may not be up to date. We
     * compare the term's identifier to the curriculum standard and determine
     * its name.
     *
     * @param string $identifier
     *    The identifier of the term.
     *
     * @return string
     *    The term's name.
     */
    public function getTermName($identifier);

    /**
     * Create a new classification tree based on a taxonomy path.
     *
     * The LOM-CH standard defines the "classification" field (9), which stores
     * curriculum classification as "taxonomy paths", flat tree structural
     * representation of curriculum classification. By passing such a
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
     */
    public function setTreeBasedOnTaxonPath($paths, $purpose = 'discipline');

    /**
     * Create a new classification tree based on a taxonomy tree.
     *
     * The LOM-CH standard defines the "curriculum" field (10), which stores
     * curriculum classification as "taxonomy trees". By passing such a
     * structure to this method, a new tree will be created representing this
     * structure, and the curriculum class instance will be updated with the
     * correct information.
     *
     * @param array $trees
     *    A list of trees, as described in the LOM-CH standard.
     */
    public function setTreeBasedOnTaxonTree($trees);

}
