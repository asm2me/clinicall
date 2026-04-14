<?php
/**
 * ClinicAll — PDO Database Wrapper (singleton)
 */
class Database
{
    private static ?PDO $pdo = null;
    private static string $driver = 'pgsql';

    public static function connect(array $cfg): void
    {
        self::$driver = $cfg['driver'] ?? 'pgsql';

        if (self::$driver === 'mysql') {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $cfg['host'], $cfg['port'], $cfg['name'], $cfg['charset'] ?? 'utf8mb4'
            );
        } else {
            $dsn = sprintf(
                'pgsql:host=%s;port=%s;dbname=%s',
                $cfg['host'], $cfg['port'], $cfg['name']
            );
        }

        self::$pdo = new PDO($dsn, $cfg['user'], $cfg['password'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }

    public static function get(): PDO
    {
        if (!self::$pdo) {
            throw new RuntimeException('Database not connected. Call Database::connect() first.');
        }
        return self::$pdo;
    }

    public static function driver(): string { return self::$driver; }

    /** Run a query and return the statement */
    public static function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::get()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /** Fetch a single row */
    public static function row(string $sql, array $params = []): ?array
    {
        $row = self::query($sql, $params)->fetch();
        return $row ?: null;
    }

    /** Fetch all rows */
    public static function all(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    /** Fetch a single column value */
    public static function val(string $sql, array $params = []): mixed
    {
        return self::query($sql, $params)->fetchColumn();
    }

    /** Execute INSERT / UPDATE / DELETE; returns rows affected */
    public static function exec(string $sql, array $params = []): int
    {
        return self::query($sql, $params)->rowCount();
    }

    /** INSERT one row from associative array; returns new UUID (pass it in) */
    public static function insert(string $table, array $data): int
    {
        $cols   = implode(', ', array_keys($data));
        $places = implode(', ', array_map(fn($k) => ":$k", array_keys($data)));
        return self::exec("INSERT INTO $table ($cols) VALUES ($places)", $data);
    }

    /** UPDATE rows matching $where (key=>val array); returns rows affected */
    public static function update(string $table, array $data, array $where): int
    {
        $set  = implode(', ', array_map(fn($k) => "$k = :set_$k",   array_keys($data)));
        $whr  = implode(' AND ', array_map(fn($k) => "$k = :whr_$k", array_keys($where)));
        $params = [];
        foreach ($data  as $k => $v) { $params["set_$k"] = $v; }
        foreach ($where as $k => $v) { $params["whr_$k"] = $v; }
        return self::exec("UPDATE $table SET $set WHERE $whr", $params);
    }

    /** ILIKE-safe LIKE: PostgreSQL uses ILIKE, MySQL uses LIKE (case-insensitive by collation) */
    public static function likeOp(): string
    {
        return self::$driver === 'pgsql' ? 'ILIKE' : 'LIKE';
    }

    /** Boolean literal for current driver */
    public static function bool(bool $v): string
    {
        return self::$driver === 'pgsql' ? ($v ? 'true' : 'false') : ($v ? '1' : '0');
    }

    /** Test a connection with given config (used by install wizard) */
    public static function test(array $cfg): bool
    {
        try {
            self::connect($cfg);
            self::get()->query('SELECT 1');
            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
