<?php
// create_messages_table.php
// Make sure db.php exists and db() returns a mysqli connection.
include __DIR__ . '/db.php';

$conn = null;
try {
    $conn = db();
} catch (Exception $e) {
    die("DB connection failed: " . $e->getMessage());
}

$sql = <<<SQL
CREATE DATABASE IF NOT EXISTS `swiftpoa` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `swiftpoa`;
DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sender_name` VARCHAR(100) NOT NULL,
  `sender_email` VARCHAR(255),
  `message` TEXT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

if ($conn->multi_query($sql)) {
    // consume results
    do {
        if ($res = $conn->store_result()) {
            $res->free();
        }
    } while ($conn->more_results() && $conn->next_result());
    echo "✅ messages table created (or already existed).<br>";
} else {
    echo "<strong>Error creating messages table:</strong><br>" . htmlspecialchars($conn->error);
}
?>
