<?php
include __DIR__ . '/db.php';
$conn = db();

if (!isset($_GET['id'])) die("Rider ID missing.");
$id = intval($_GET['id']);

$stmt = $conn->prepare("DELETE FROM riders WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: riders.php?success=deleted");
exit();
