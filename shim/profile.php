<?php
session_start();
require_once "db.php"; // adjust path if db.php is inside includes/

// Redirect if not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$conn = db();
$admin_id = $_SESSION['admin_id'];

// Fetch admin details
$stmt = $conn->prepare("SELECT id, name, email, phone, address, profile_pic FROM admins WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = $_POST['name'];
    $phone   = $_POST['phone'];
    $address = $_POST['address'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    // Handle file upload
    $profile_pic = $admin['profile_pic'];
    if (!empty($_FILES['profile_pic']['name'])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        $filename = uniqid() . "_" . basename($_FILES["profile_pic"]["name"]);
        $target_file = $target_dir . $filename;

        if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
            $profile_pic = $target_file;
        }
    }

    if ($password) {
        $sql = "UPDATE admins SET name=?, phone=?, address=?, password=?, profile_pic=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $name, $phone, $address, $password, $profile_pic, $admin_id);
    } else {
        $sql = "UPDATE admins SET name=?, phone=?, address=?, profile_pic=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $name, $phone, $address, $profile_pic, $admin_id);
    }

    if ($stmt->execute()) {
        $_SESSION['message'] = "Profile updated successfully.";
        header("Location: profile.php");
        exit;
    } else {
        $error = "Error updating profile: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Profile</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        form { max-width: 500px; margin: auto; }
        input, button { width: 100%; padding: 10px; margin: 5px 0; }
        img { max-width: 150px; display: block; margin-bottom: 10px; }
        .message { color: green; }
        .error { color: red; }
    </style>
</head>
<body>

<h2>My Profile</h2>

<?php if (isset($_SESSION['message'])): ?>
    <p class="message"><?= $_SESSION['message']; unset($_SESSION['message']); ?></p>
<?php endif; ?>

<?php if (isset($error)): ?>
    <p class="error"><?= $error; ?></p>
<?php endif; ?>

<form action="" method="POST" enctype="multipart/form-data">
    <label>Name</label>
    <input type="text" name="name" value="<?= htmlspecialchars($admin['name']) ?>" required>

    <label>Email</label>
    <input type="text" value="<?= htmlspecialchars($admin['email']) ?>" disabled>

    <label>Phone</label>
    <input type="text" name="phone" value="<?= htmlspecialchars($admin['phone']) ?>">

    <label>Address</label>
    <input type="text" name="address" value="<?= htmlspecialchars($admin['address']) ?>">

    <label>New Password (leave blank if not changing)</label>
    <input type="password" name="password">

    <label>Profile Picture</label>
    <?php if ($admin['profile_pic']): ?>
        <img src="<?= htmlspecialchars($admin['profile_pic']) ?>" alt="Profile Picture">
    <?php endif; ?>
    <input type="file" name="profile_pic">

    <button type="submit">Update Profile</button>
</form>

</body>
</html>
