<?php
include __DIR__ . '/db.php';
$conn = db();

if (!isset($_GET['id'])) {
    die("Vendor ID not provided.");
}

$id = intval($_GET['id']);

// Delete vendor
$stmt = $conn->prepare("DELETE FROM vendors WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: vendors.php?success=deleted");
    exit();
} else {
    echo "Error deleting vendor.";
}
