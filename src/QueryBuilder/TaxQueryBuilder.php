<?php

declare(strict_types=1);

/**
 * Class TaxQueryBuilder
 *
 * This class represents a query builder for taxonomies in WordPress.
 */

namespace Pollora\Query\QueryBuilder;

use Pollora\Query\QueryException;

/**
 * Class TaxQueryBuilder
 *
 * This class is used to build queries for taxonomy searching in WordPress.
 */
class TaxQueryBuilder extends SubQuery
{
    /**
     * Constant representing the search term "term_taxonomy_id".
     *
     * Usage:
     * This constant can be used when performing a search operation
     * to specify that the search should be based on the term's taxonomy ID.
     *
     * Example:
     * $searchBy = SEARCH_BY_TERM_TAX_ID;
     * $termTaxonomyId = 10;
     * $results = performSearch($searchBy, $termTaxonomyId);
     */
    final public const SEARCH_BY_SLUG = 'slug';

    /**
     * Constant representing the search term 'term_taxonomy_id'.
     */
    final public const SEARCH_BY_NAME = 'name';

    /**
     * Constant that represents the search parameter name for searching by term taxonomy ID.
     */
    final public const SEARCH_BY_TERM_TAX_ID = 'term_taxonomy_id';

    /**
     * This constant defines the term taxonomy ID for searching.
     */
    final public const SEARCH_BY_ID = 'term_id';

    /**
     * Defines the constant SEARCH_BY_TERM_TAX_ID and its value 'term_taxonomy_id'.
     */
    private string $field = 'term_id';

    /**
     * Operator for search
     */
    private string $operator = 'IN';

    /**
     * @var bool Whether to include children in the search or not.
     */
    private bool $includeChildren = true;

    /**
     * @var array<string>|null
     * Variable that stores an array of terms used for searching.
     */
    private ?array $terms = null;

    public function __construct(
        private readonly string $taxonomy
    ) {
    }

    /**
     * Sets the operator to "NOT EXISTS".
     *
     * @return self Returns the current instance of the object.
     */
    public function notExists(): self
    {
        $this->operator = 'NOT EXISTS';

        return $this;
    }

    /**
     * Sets the operator to "EXISTS".
     *
     * @return self Returns the current instance of the object.
     */
    public function exists(): self
    {
        $this->operator = 'EXISTS';

        return $this;
    }

    /**
     * Sets the field to be used in the query.
     *
     * @param  string  $field The field to be used in the query.
     * @return self Returns the current instance of the object.
     *
     * @throws QueryException If an invalid tax field type is supplied.
     */
    public function field(string $field): self
    {

        $allowed = [self::SEARCH_BY_ID, self::SEARCH_BY_NAME, self::SEARCH_BY_TERM_TAX_ID, self::SEARCH_BY_SLUG];

        if (! in_array($field, $allowed)) {
            throw new QueryException('Invalid tax field type supplied: '.$field);
        }

        $this->field = $field;

        return $this;
    }

    /**
     * Sets the terms to be used in the query and sets the operator to "IN".
     *
     * @param  array<string|int>  $terms The terms to be used in the query.
     * @return self Returns the current instance of the object.
     */
    public function contains(array $terms): self
    {
        $this->terms = $terms;
        $this->operator = 'IN';

        return $this;
    }

    /**
     * Sets the terms to exclude from the search.
     *
     * @param  array<string|int>  $terms The terms to exclude.
     */
    public function notContains(array $terms): self
    {
        $this->terms = $terms;
        $this->operator = 'NOT IN';

        return $this;
    }

    /**
     * Sets the field to search for term slugs.
     *
     * @throws QueryException
     */
    public function searchByTermSlug(): self
    {
        $this->field(self::SEARCH_BY_SLUG);

        return $this;
    }

    /**
     * Sets the field to search for term names.
     *
     * @throws QueryException
     */
    public function searchByTermName(): self
    {
        $this->field(self::SEARCH_BY_NAME);

        return $this;
    }

    /**
     * Sets the field to search for term taxonomy IDs.
     *
     * @throws QueryException
     */
    public function searchByTermTaxId(): self
    {
        $this->field(self::SEARCH_BY_TERM_TAX_ID);

        return $this;
    }

    /**
     * Sets the field to search for term IDs.
     *
     * @throws QueryException
     */
    public function searchById(): self
    {
        $this->field(self::SEARCH_BY_ID);

        return $this;
    }

    /**
     * Sets the flag to include children in the query.
     *
     * @return self Returns the current instance of the object.
     */
    public function includeChildren(): self
    {
        $this->includeChildren = true;

        return $this;
    }

    /**
     * Sets the includeChildren property to "false", indicating that only parent items should be included in the query result.
     *
     * @return self Returns the current instance of the object.
     */
    public function onlyParent(): self
    {
        $this->includeChildren = false;

        return $this;
    }

    /**
     * Sets the flag to exclude children from the query result.
     *
     * @return self Returns the current instance of the object.
     */
    public function excludeChildren(): self
    {
        $this->includeChildren = false;

        return $this;
    }

    /**
     * @return array{
     *   taxonomy: string,
     *   field: string,
     *   terms: array|null,
     *   operator: string,
     *   include_children: bool
     * }
     */
    public function get(): array
    {
        return [
            'taxonomy' => $this->taxonomy,
            'field' => $this->field,
            'terms' => $this->terms,
            'operator' => $this->operator,
            'include_children' => $this->includeChildren,
        ];
    }
}
