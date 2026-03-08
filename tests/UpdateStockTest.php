<?php

use PHPUnit\Framework\TestCase;

class UpdateStockTest extends TestCase
{
    private static mysqli $con;

    public static function setUpBeforeClass(): void
    {
        global $con;
        if ($con === null) {
            require __DIR__ . '/../connect.php';
        }
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
        // Clean tables before each test
        self::$con->query("DELETE FROM history");
        self::$con->query("DELETE FROM article_info");

        // Insert a test article with stock = 10
        $stmt = self::$con->prepare(
            "INSERT INTO article_info (article_id, article_name, stock, threshold) VALUES (?, ?, ?, ?)"
        );
        $id = 1;
        $name = 'テスト物品';
        $stock = 10;
        $threshold = 5;
        $stmt->bind_param("isis", $id, $name, $stock, $threshold);
        $stmt->execute();
        $stmt->close();
    }

    public function testRestockIncreasesStock(): void
    {
        $result = updateStock('restock', 1, 5);

        $this->assertEquals(1, $result);

        // Verify stock was increased from 10 to 15
        $stmt = self::$con->prepare("SELECT stock FROM article_info WHERE article_id = ?");
        $id = 1;
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_row();
        $stmt->close();

        $this->assertEquals(15, $row[0]);
    }

    public function testDestockDecreasesStock(): void
    {
        $result = updateStock('destock', 1, 3);

        $this->assertEquals(1, $result);

        // Verify stock was decreased from 10 to 7
        $stmt = self::$con->prepare("SELECT stock FROM article_info WHERE article_id = ?");
        $id = 1;
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_row();
        $stmt->close();

        $this->assertEquals(7, $row[0]);
    }

    public function testRestockCreatesHistoryEntry(): void
    {
        updateStock('restock', 1, 5);

        $stmt = self::$con->prepare(
            "SELECT article_id, type, original_value, updated_value, from_barcode FROM history WHERE article_id = ?"
        );
        $id = 1;
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $this->assertEquals(1, $row['article_id']);
        $this->assertEquals('入庫', $row['type']);
        $this->assertEquals(10, $row['original_value']);
        $this->assertEquals(15, $row['updated_value']);
        $this->assertEquals(0, $row['from_barcode']);
    }

    public function testDestockCreatesHistoryEntry(): void
    {
        updateStock('destock', 1, 3);

        $stmt = self::$con->prepare(
            "SELECT type, original_value, updated_value FROM history WHERE article_id = ?"
        );
        $id = 1;
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $this->assertEquals('出庫', $row['type']);
        $this->assertEquals(10, $row['original_value']);
        $this->assertEquals(7, $row['updated_value']);
    }

    public function testFromBarcodeFlag(): void
    {
        updateStock('destock', 1, 2, 1);

        $stmt = self::$con->prepare(
            "SELECT from_barcode FROM history WHERE article_id = ?"
        );
        $id = 1;
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $this->assertEquals(1, $row['from_barcode']);
    }

    public function testMultipleOperationsAccumulate(): void
    {
        updateStock('restock', 1, 5);  // 10 -> 15
        updateStock('restock', 1, 3);  // 15 -> 18
        updateStock('destock', 1, 8);  // 18 -> 10

        $stmt = self::$con->prepare("SELECT stock FROM article_info WHERE article_id = ?");
        $id = 1;
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_row();
        $stmt->close();

        $this->assertEquals(10, $row[0]);

        // Verify 3 history entries were created
        $result = self::$con->query("SELECT COUNT(*) FROM history WHERE article_id = 1");
        $count = $result->fetch_row()[0];
        $this->assertEquals(3, $count);
    }

    public function testHandleStockWithArray(): void
    {
        // Insert a second test article
        $stmt = self::$con->prepare(
            "INSERT INTO article_info (article_id, article_name, stock, threshold) VALUES (?, ?, ?, ?)"
        );
        $id = 2;
        $name = 'テスト物品2';
        $stock = 20;
        $threshold = 5;
        $stmt->bind_param("isis", $id, $name, $stock, $threshold);
        $stmt->execute();
        $stmt->close();

        // handleStock with array of changes (play_audio=false for tests)
        $changes = ['1' => '5', '2' => '10'];
        handleStock('restock', $changes, null, null, 0, false);

        // Verify both articles updated
        $stmt = self::$con->prepare("SELECT stock FROM article_info WHERE article_id = ?");

        $id = 1;
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $this->assertEquals(15, $stmt->get_result()->fetch_row()[0]);

        $id = 2;
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $this->assertEquals(30, $stmt->get_result()->fetch_row()[0]);

        $stmt->close();
    }
}
