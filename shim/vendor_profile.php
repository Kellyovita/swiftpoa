<?php
session_start();

// Load DB connection
if (file_exists(__DIR__ . '/includes/db.php')) {
    require_once __DIR__ . '/includes/db.php';
    $conn = function_exists('db') ? db() : new mysqli('localhost', 'root', '', 'swiftpoa');
} else {
    $conn = new mysqli('localhost', 'root', '', 'swiftpoa');
}

if ($conn->connect_error) {
    die("DB connection failed: " . htmlspecialchars($conn->connect_error));
}

// Ensure vendor logged in
if (!isset($_SESSION['vendor_id'])) {
    header('Location: login.php');
    exit;
}

$vendor_id = (int) $_SESSION['vendor_id'];

// Fetch vendor data
$stmt = $conn->prepare("SELECT * FROM vendors WHERE id = ?");
if (!$stmt) {
    die("Prepare failed: " . htmlspecialchars($conn->error));
}
$stmt->bind_param("i", $vendor_id);
$stmt->execute();
$result = $stmt->get_result();
$vendor = $result->fetch_assoc();
$stmt->close();

if (!$vendor) {
    die("Vendor not found.");
}

$message = "";

// Handle POST (profile update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone'] ?? $vendor['phone'] ?? '');
    $email = trim($_POST['email'] ?? $vendor['email'] ?? '');
    $location = trim($_POST['location'] ?? $vendor['location'] ?? '');
    $password = $_POST['password'] ?? '';

    $profile_image = $vendor['profile_image'] ?? '';

    // Handle upload if any image provided
    if (!empty($_FILES['profile_image']['name']) && is_uploaded_file($_FILES['profile_image']['tmp_name'])) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo_type = mime_content_type($_FILES['profile_image']['tmp_name']);
        if (!in_array($finfo_type, $allowed_types, true)) {
            $message = "Only JPG/PNG/GIF/WEBP images are allowed.";
        } else {
            $uploadsDir = __DIR__ . '/uploads/vendors';
            if (!is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0777, true);
            }
            $orig = basename($_FILES['profile_image']['name']);
            $orig = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $orig);
            $fileName = time() . '_' . $orig;
            $targetFile = $uploadsDir . '/' . $fileName;

            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetFile)) {
                $profile_image = 'uploads/vendors/' . $fileName;
            } else {
                $message = "Failed to upload profile image.";
            }
        }
    }

    if ($message === '') {
        if ($password !== '') {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $upd = $conn->prepare("UPDATE vendors SET phone=?, email=?, location=?, profile_image=?, password=? WHERE id=?");
            $upd->bind_param("sssssi", $phone, $email, $location, $profile_image, $hashed, $vendor_id);
        } else {
            $upd = $conn->prepare("UPDATE vendors SET phone=?, email=?, location=?, profile_image=? WHERE id=?");
            $upd->bind_param("ssssi", $phone, $email, $location, $profile_image, $vendor_id);
        }

        if ($upd->execute()) {
            $message = "Profile updated successfully.";
        } else {
            $message = "Update failed: " . htmlspecialchars($upd->error);
        }
        $upd->close();

        // Reload updated info
        $stmt = $conn->prepare("SELECT * FROM vendors WHERE id = ?");
        $stmt->bind_param("i", $vendor_id);
        $stmt->execute();
        $vendor = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}

// Helper to safely output
function e($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Vendor Profile - SwiftPOA</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
body { font-family: Arial, sans-serif; background:#f4f4f4; margin:0; padding:20px; }
.container { max-width:700px; margin:30px auto; background:#fff; padding:24px; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.08); }
.profile-pic img { width:120px; height:120px; border-radius:50%; object-fit:cover; border:3px solid #ffb400; }
label { display:block; margin-top:12px; font-weight:600; }
input[type="text"], input[type="password"], input[type="email"] { width:100%; padding:10px; border-radius:6px; border:1px solid #ccc; margin-top:6px; }
input[type="file"] { margin-top:8px; }
button { margin-top:18px; padding:12px 18px; background:#ffb400; border:none; border-radius:8px; font-weight:700; cursor:pointer; }
.message { margin-top:12px; font-weight:600; color:green; }
.error { margin-top:12px; color:red; font-weight:600; }
.back { display:inline-block; margin-top:14px; text-decoration:none; color:#333; }
</style>
</head>
<body>
<div class="container">
    <h2>Vendor Profile</h2>

    <?php if ($message): ?>
        <div class="<?= strpos($message, 'failed') !== false || strpos($message, 'error') !== false ? 'error' : 'message' ?>">
            <?= e($message) ?>
        </div>
    <?php endif; ?>

    <div style="display:flex; gap:18px; align-items:center; margin-top:14px;">
        <div class="profile-pic">
            <img src="<?= e($vendor['profile_image'] ?: 'default-avatar.png') ?>" alt="Profile">
        </div>
        <div>
            <div><strong><?= e($vendor['name'] ?? '') ?></strong></div>
            <div><?= e($vendor['email'] ?? '') ?></div>
            <div><?= e($vendor['location'] ?? '') ?></div>
        </div>
    </div>

    <form method="post" enctype="multipart/form-data" style="margin-top:18px;">
        <label for="phone">Phone</label>
        <input id="phone" name="phone" type="text" value="<?= e($vendor['phone']) ?>" required>

        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="<?= e($vendor['email']) ?>" required>

        <label for="location">Location</label>
        <input id="location" name="location" type="text" value="<?= e($vendor['location']) ?>" required>

        <label for="password">New Password (leave blank to keep current)</label>
        <input id="password" name="password" type="password" placeholder="••••••">

        <label for="profile_image">Profile Picture (optional)</label>
        <input id="profile_image" name="profile_image" type="file" accept="image/*">

        <button type="submit">Save Changes</button>
    </form>

    <a class="back" href="vendor.php">← Back to Dashboard</a>
</div>
</body>
</html>
