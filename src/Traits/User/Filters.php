<?php

declare(strict_types=1);

namespace Pollora\Query\Traits\User;

use Pollora\Query\QueryException;
use Pollora\Query\Traits\Concerns\ValidatesChoices;

trait Filters
{
    use ValidatesChoices;

    protected ?int $blogId = null;

    /**
     * @var string|array<string>|null
     */
    protected string|array|null $role = null;

    /**
     * @var array<string>|null
     */
    protected ?array $role_In = null;

    /**
     * @var array<string>|null
     */
    protected ?array $role_NotIn = null;

    /**
     * @var string|array<string>|null
     */
    protected string|array|null $capability = null;

    /**
     * @var array<string>|null
     */
    protected ?array $capability_In = null;

    /**
     * @var array<string>|null
     */
    protected ?array $capability_NotIn = null;

    /**
     * @var array<int>|null
     */
    protected ?array $include = null;

    /**
     * @var array<int>|null
     */
    protected ?array $exclude = null;

    protected ?string $search = null;

    /**
     * @var array<string>|null
     */
    protected ?array $searchColumns = null;

    protected ?string $login = null;

    /**
     * @var array<string>|null
     */
    protected ?array $login_In = null;

    /**
     * @var array<string>|null
     */
    protected ?array $login_NotIn = null;

    protected ?string $nicename = null;

    /**
     * @var array<string>|null
     */
    protected ?array $nicename_In = null;

    /**
     * @var array<string>|null
     */
    protected ?array $nicename_NotIn = null;

    /**
     * @var bool|array<string>|null
     */
    protected bool|array|null $hasPublishedPosts = null;

    /**
     * The site to query the roles and capabilities of (multisite). Defaults to the current site.
     */
    public function blogId(int $blogId): self
    {
        $this->blogId = $blogId;

        return $this;
    }

    /**
     * Users must have every given role.
     *
     * @param  string|array<string>  $role
     */
    public function role(string|array $role): self
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Users must have at least one of the roles.
     *
     * @param  array<string>  $roles
     */
    public function roleIn(array $roles): self
    {
        $this->role_In = $roles;

        return $this;
    }

    /**
     * @param  array<string>  $roles
     */
    public function roleNotIn(array $roles): self
    {
        $this->role_NotIn = $roles;

        return $this;
    }

    /**
     * Users must have every given capability. Only works for capabilities stored in the database.
     *
     * @param  string|array<string>  $capability
     */
    public function capability(string|array $capability): self
    {
        $this->capability = $capability;

        return $this;
    }

    /**
     * @param  array<string>  $capabilities
     */
    public function capabilityIn(array $capabilities): self
    {
        $this->capability_In = $capabilities;

        return $this;
    }

    /**
     * @param  array<string>  $capabilities
     */
    public function capabilityNotIn(array $capabilities): self
    {
        $this->capability_NotIn = $capabilities;

        return $this;
    }

    /**
     * @param  array<int>  $userIds
     */
    public function whereIn(array $userIds): self
    {
        $this->include = $userIds;

        return $this;
    }

    /**
     * @param  array<int>  $userIds
     */
    public function whereNotIn(array $userIds): self
    {
        $this->exclude = $userIds;

        return $this;
    }

    /**
     * Without searchColumns(), WP picks the columns from the keyword. A leading and/or trailing `*`
     * makes it a wildcard search.
     */
    public function search(string $keyword): self
    {
        $this->search = $keyword;

        return $this;
    }

    /**
     * @param  array<string>  $columns  Among ID, user_login, user_email, user_url, user_nicename, display_name.
     *
     * @throws QueryException
     */
    public function searchColumns(array $columns): self
    {
        $allowed = ['ID', 'user_login', 'user_email', 'user_url', 'user_nicename', 'display_name'];

        foreach ($columns as $column) {
            $this->assertOneOf($column, $allowed, 'search column');
        }

        $this->searchColumns = $columns;

        return $this;
    }

    public function login(string $login): self
    {
        $this->login = $login;

        return $this;
    }

    /**
     * @param  array<string>  $logins
     */
    public function loginIn(array $logins): self
    {
        $this->login_In = $logins;

        return $this;
    }

    /**
     * @param  array<string>  $logins
     */
    public function loginNotIn(array $logins): self
    {
        $this->login_NotIn = $logins;

        return $this;
    }

    public function nicename(string $nicename): self
    {
        $this->nicename = $nicename;

        return $this;
    }

    /**
     * @param  array<string>  $nicenames
     */
    public function nicenameIn(array $nicenames): self
    {
        $this->nicename_In = $nicenames;

        return $this;
    }

    /**
     * @param  array<string>  $nicenames
     */
    public function nicenameNotIn(array $nicenames): self
    {
        $this->nicename_NotIn = $nicenames;

        return $this;
    }

    /**
     * Only users who have published posts: in the given post types, or in every public post type
     * with `true`. WP ignores it for the current site unless a blog ID is resolved.
     *
     * @param  bool|array<string>  $postTypes
     */
    public function hasPublishedPosts(bool|array $postTypes = true): self
    {
        $this->hasPublishedPosts = $postTypes;

        return $this;
    }
}
