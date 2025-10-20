<?php
include __DIR__ . '/db.php';
$conn = db();

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $stmt = $conn->prepare("DELETE FROM staff WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: staffs.php?msg=Staff+deleted+successfully");
        exit();
    } else {
        echo "Error deleting staff: " . $stmt->error;
    }
} else {
    header("Location: staffs.php?msg=Invalid+staff+ID");
    exit();
}
