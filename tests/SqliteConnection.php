<?php

// phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps

/**
 * MySQLi-compatible wrapper around PDO SQLite for testing.
 *
 * Implements just enough of the MySQLi API to support
 * the functions in functions.php and the test suite.
 * Method names use snake_case to match MySQLi's API.
 */

class SqliteResult
{
    private PDOStatement $stmt;

    public function __construct(PDOStatement $stmt)
    {
        $this->stmt = $stmt;
    }

    /**
     * @return ?array<int, mixed>
     */
    public function fetch_row(): ?array
    {
        $row = $this->stmt->fetch(PDO::FETCH_NUM);
        return $row === false ? null : $row;
    }

    /**
     * @return ?array<string, mixed>
     */
    public function fetch_assoc(): ?array
    {
        $row = $this->stmt->fetch(PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }
}

class SqliteStatement
{
    private PDOStatement $stmt;
    /** @var array<int, mixed> */
    private array $params = [];

    public function __construct(PDOStatement $stmt)
    {
        $this->stmt = $stmt;
    }

    public function bind_param(string $types, mixed &...$vars): bool
    {
        $this->params = [];
        foreach ($vars as $var) {
            $this->params[] = $var;
        }
        return true;
    }

    public function execute(): bool
    {
        foreach ($this->params as $i => $val) {
            $this->stmt->bindValue($i + 1, $val);
        }
        return $this->stmt->execute();
    }

    public function get_result(): SqliteResult
    {
        return new SqliteResult($this->stmt);
    }

    public function close(): bool
    {
        $this->stmt->closeCursor();
        return true;
    }
}

class SqliteConnection
{
    private PDO $pdo;
    public ?string $connect_error = null;

    public function __construct(string $dbPath = ':memory:')
    {
        try {
            $this->pdo = new PDO("sqlite:$dbPath");
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            $this->connect_error = $e->getMessage();
        }
    }

    public function prepare(string $sql): SqliteStatement
    {
        $stmt = $this->pdo->prepare($sql);
        return new SqliteStatement($stmt);
    }

    public function query(string $sql): SqliteResult
    {
        $stmt = $this->pdo->query($sql);
        return new SqliteResult($stmt);
    }

    public function multi_query(string $sql): bool
    {
        $statements = explode(';', $sql);
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                $this->pdo->exec($statement);
            }
        }
        return true;
    }

    public function next_result(): bool
    {
        return false;
    }
}
