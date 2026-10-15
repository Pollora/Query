<?php

declare(strict_types=1);

namespace Pollora\Query\Traits\User;

use Pollora\Query\QueryException;

trait Fields
{
    /**
     * @var string|array<string>
     */
    protected string|array $fields = 'all';

    /**
     * Fields WP_User_Query can return. Several fields (an array) give back objects with only those fields.
     *
     * @param  string|array<string>  $fields  'all', or among ID, display_name, user_login, user_nicename,
     *                                        user_email, user_url, user_registered, user_pass,
     *                                        user_activation_key, user_status, spam, deleted.
     *
     * @throws QueryException If a field is not supported by WP_User_Query.
     */
    public function fields(string|array $fields): self
    {
        if ($fields === '*') {
            $fields = 'all';
        }

        $allowed = [
            'all', 'ID', 'display_name', 'user_login', 'user_nicename', 'user_email', 'user_url',
            'user_registered', 'user_pass', 'user_activation_key', 'user_status', 'spam', 'deleted',
        ];

        $normalize = function (string $field) use ($allowed): string {
            $match = array_values(array_filter($allowed, fn (string $candidate): bool => strcasecmp($candidate, $field) === 0));

            return $match[0] ?? throw new QueryException('Invalid fields value supplied: '.$field);
        };

        if (is_array($fields)) {
            $fields = array_values(array_unique(array_map($normalize, $fields)));

            if ($fields === [] || in_array('all', $fields, true)) {
                throw new QueryException("'all' cannot be combined with other fields, and the list cannot be empty.");
            }
        } else {
            $fields = $normalize($fields);
        }

        $this->fields = $fields;

        return $this;
    }
}
