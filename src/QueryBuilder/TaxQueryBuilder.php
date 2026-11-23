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
    /** Match the terms by their slug. */
    final public const string SEARCH_BY_SLUG = 'slug';

    /** Match the terms by their name. */
    final public const string SEARCH_BY_NAME = 'name';

    /** Match the terms by their term_taxonomy_id. */
    final public const string SEARCH_BY_TERM_TAX_ID = 'term_taxonomy_id';

    /** Match the terms by their term_id (default). */
    final public const string SEARCH_BY_ID = 'term_id';

    /** The field the terms are matched on. */
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
     * The terms the query looks for.
     *
     * @var array<string|int>|null
     */
    private ?array $terms = null;

    public function __construct(
        private readonly string $taxonomy
    ) {}

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
     * @param  string  $field  The field to be used in the query.
     * @return self Returns the current instance of the object.
     *
     * @throws QueryException If an invalid tax field type is supplied.
     */
    public function field(string $field): self
    {

        $allowed = [self::SEARCH_BY_ID, self::SEARCH_BY_NAME, self::SEARCH_BY_TERM_TAX_ID, self::SEARCH_BY_SLUG];

        if (! in_array($field, $allowed, true)) {
            throw new QueryException('Invalid tax field type supplied: '.$field);
        }

        $this->field = $field;

        return $this;
    }

    /**
     * Sets the terms to be used in the query and sets the operator to "IN".
     *
     * @param  array<string|int>  $terms  The terms to be used in the query.
     * @return self Returns the current instance of the object.
     */
    public function contains(array $terms): self
    {
        $this->terms = $terms;
        $this->operator = 'IN';

        return $this;
    }

    /**
     * Sets the terms to be used in the query and sets the operator to "AND": every term must match.
     *
     * @param  array<string|int>  $terms
     */
    public function containsAll(array $terms): self
    {
        $this->terms = $terms;
        $this->operator = 'AND';

        return $this;
    }

    /**
     * Sets the terms to exclude from the search.
     *
     * @param  array<string|int>  $terms  The terms to exclude.
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
     * The WP_Query clause.
     *
     * `containsAll()` on a taxonomy whose children are included is a group of one `IN` clause per term, joined with
     * `AND`: WordPress adds the children of every term to the list of an `AND` clause and then requires a post to have
     * all of them (a term and its child both), which no post matches. A post that has a term or one of its children,
     * for every listed term, is what the builder documents.
     *
     * @return array<int|string, mixed> A clause: taxonomy, field, terms, operator and include_children; or, for
     *                                  `containsAll()` with children, `['relation' => 'AND', clause, clause...]`.
     */
    public function get(): array
    {
        if ($this->operator === 'AND' && $this->includeChildren && is_array($this->terms) && $this->terms !== []) {
            $clauses = array_map(fn (string|int $term): array => $this->clause([$term], 'IN'), array_values($this->terms));

            return count($clauses) === 1 ? $clauses[0] : ['relation' => 'AND', ...$clauses];
        }

        return $this->clause($this->terms, $this->operator);
    }

    /**
     * @param  array<string|int>|null  $terms
     * @return array{taxonomy: string, field: string, terms: array<string|int>|null, operator: string, include_children: bool}
     */
    private function clause(?array $terms, string $operator): array
    {
        return [
            'taxonomy' => $this->taxonomy,
            'field' => $this->field,
            'terms' => $terms,
            'operator' => $operator,
            'include_children' => $this->includeChildren,
        ];
    }
}
