<?php

use PHPUnit\Framework\TestCase;

class LogErrorTest extends TestCase
{
    private static mysqli $con;

    public static function setUpBeforeClass(): void
    {
        global $con;
        self::$con = $con;

        // Create tables if they don't exist
        $schema = file_get_contents(__DIR__ . '/schema.sql');
        self::$con->multi_query($schema);
        // Consume all results from multi_query
        while (self::$con->next_result()) {
            ;
        }
    }

    protected function setUp(): void
    {
        // Clean table before each test
        self::$con->query("DELETE FROM error_log");
    }

    public function testLogErrorInsertsIntoDatabase(): void
    {
        $errorMessage = "Test error message";
        $query = "SELECT * FROM non_existent_table";

        logError($errorMessage, $query);

        // Verify error was inserted
        $stmt = self::$con->prepare("SELECT error_message, query FROM error_log");
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        $this->assertNotNull($row, "No row found in error_log table");
        $this->assertSame($errorMessage, $row['error_message']);
        $this->assertSame($query, $row['query']);
    }

    public function testLogErrorHandlesLongStrings(): void
    {
        $longMessage = str_repeat("A long error message. ", 100);
        $longQuery = str_repeat("SELECT * FROM some_table WHERE id = ? AND ", 50);

        logError($longMessage, $longQuery);

        $stmt = self::$con->prepare("SELECT error_message, query FROM error_log");
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        $this->assertNotNull($row, "No row found in error_log table for long strings");
        $this->assertSame($longMessage, $row['error_message']);
        $this->assertSame($longQuery, $row['query']);
    }

    public function testLogErrorWithNullValues(): void
    {
        logError(null, null);

        $stmt = self::$con->prepare("SELECT error_message, query FROM error_log");
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        $this->assertNotNull($row, "No row found in error_log table for null values");
        $this->assertNull($row['error_message']);
        $this->assertNull($row['query']);
    }
}
