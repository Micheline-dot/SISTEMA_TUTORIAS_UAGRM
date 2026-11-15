<?php

namespace App\DataAccess\PostgreSQL\Users\Repositories;

use InvalidArgumentException;

final class PostgreSqlIntegerId
{
    private const MAX_INTEGER = 2147483647;

    public static function toInteger(string $id): int
    {
        if (! ctype_digit($id) || (int) $id < 1 || (int) $id > self::MAX_INTEGER) {
            throw new InvalidArgumentException('The identifier is not a valid PostgreSQL INTEGER key.');
        }

        return (int) $id;
    }

    public static function toString(int|string $id): string
    {
        if ((! is_int($id) && ! ctype_digit($id)) || (int) $id < 1 || (int) $id > self::MAX_INTEGER) {
            throw new InvalidArgumentException('The generated identifier is not a valid PostgreSQL INTEGER key.');
        }

        return (string) $id;
    }
}
