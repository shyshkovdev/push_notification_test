<?php

namespace App\Services;

use PDO;

abstract class DatabaseService
{
    public static function findById(string $table, int $id, array $options = []): ?array
    {
        $connection = getConnection();

        $query = $connection->prepare(
            'SELECT ' . self::getSelectedFields($options) . 'FROM ' . $table . ' WHERE id=:id'
        );
        $query->execute([':id' => $id]);
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public static function find(string $table, array $values, array $options = []): ?array
    {
        $connection = getConnection();

        $query = $connection->prepare(
            'SELECT ' . self::getSelectedFields($options) .
            ' FROM ' . $table . ' ' . self::getConditions($values) .
            '' . self::getGroupBy($options)
        );
        $query->execute($values);

        $result = $query->fetch(PDO::FETCH_ASSOC);

        return $result ?: [];
    }

    public static function findBySqlQuery(string $sql): ?array
    {
        $connection = getConnection();
        $query = $connection->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findAll(string $table, array $values, array $options = []): ?array
    {
        $connection = getConnection();

        $query = $connection->prepare(
            'SELECT ' . self::getSelectedFields($options) .
            ' FROM ' . $table . ' ' . self::getConditions($values) .
            '' . self::getGroupBy($options)
        );
        $query->execute($values);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create(string $table, array $values): int
    {
        $connection = getConnection();

        $query = $connection->prepare(
            'INSERT INTO ' . $table .
            ' (' . join(', ', array_map(fn($i) => substr($i, 1), array_keys($values))) . ') VALUES ' .
            '(' . join(', ', array_keys($values)) . ')'
        );

        $query->execute($values);
        return (int)$connection->lastInsertId();

    }

    public static function update(string $table, array $values, array $conditions): ?bool
    {
        $connection = getConnection();

        $query = $connection->prepare(
            'UPDATE ' . $table .
            ' SET ' . join(", ", array_map(fn($i) => substr($i, 1) . '=' . $i, array_keys($values))) .
            ' ' . self::getConditions($conditions)
        );
        return $query->execute(array_merge($values, $conditions));
    }

    public static function updateBySqlQuery(string $sql, array $values): ?bool
    {
        $connection = getConnection();
        $query = $connection->prepare($sql);
        return $query->execute($values);
    }


    private static function getSelectedFields(array $options): string
    {
        return empty($options['fields']) ? '*' : join(", ", $options['fields']);
    }
    private static function getConditions(array &$conditions): string
    {
        return empty($conditions) ? '' : 'WHERE ' .join(" AND ", array_map(function ($item) use (&$conditions) {
                if (is_array($conditions[$item])) {
                    $condition = substr($item, 1) . ' ' . $conditions[$item][0] . '  ' . $conditions[$item][1];
                    unset($conditions[$item]);
                    return $condition;
                }

                return substr($item, 1) . '=' . $item;
            }, array_keys($conditions))) . ' ';
    }
    private static function getGroupBy(array $options): string
    {
        return empty($options['groupBy']) ? '' : ' GROUP BY ' . $options['groupBy'];
    }
}