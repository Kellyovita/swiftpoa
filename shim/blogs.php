<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admins') {
    header("Location: login.php");
    exit;
}
require_once __DIR__ . '/includes/db.php';
$conn = db();

$message = "";

// Handle blog submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $imagePath = null;

    if (!empty($_FILES['image']['name'])) {
        $targetDir = "uploads/blogs/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        $imagePath = $targetDir . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);
    }

    $stmt = $conn->prepare("INSERT INTO blogs (title, content, image) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $title, $content, $imagePath);
    if ($stmt->execute()) {
        $message = "✅ Blog posted successfully!";
    } else {
        $message = "❌ Failed to post blog.";
    }
}

// Fetch all blogs
$result = $conn->query("SELECT * FROM blogs ORDER BY created_at DESC");
$blogs = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Blogs - SwiftPOA</title>
<style>
body { font-family: Arial, sans-serif; background: #f7f7f7; margin:0; padding:20px; }
h1 { color: #333; }
form { background:#fff; padding:20px; border-radius:10px; box-shadow:0 2px 6px rgba(0,0,0,0.1); max-width:600px; margin-bottom:30px; }
input, textarea { width:100%; padding:10px; margin-bottom:10px; border:1px solid #ccc; border-radius:5px; }
button { background:#000; color:#ffb400; padding:10px 20px; border:none; border-radius:5px; cursor:pointer; }
button:hover { background:#ffb400; color:#000; }
.blog { background:#fff; padding:15px; margin-bottom:20px; border-radius:10px; box-shadow:0 2px 6px rgba(0,0,0,0.1); }
.blog img { max-width:100%; border-radius:10px; margin-bottom:10px; }
.stats { font-size:14px; color:#555; margin-top:10px; }
.comment-section { margin-top:10px; background:#f1f1f1; padding:10px; border-radius:8px; }
</style>
</head>
<body>
<h1>📝 Manage Blogs</h1>
<p style="color:green;"><?= htmlspecialchars($message) ?></p>

<!-- Blog Form -->
<form method="POST" enctype="multipart/form-data">
    <label>Title:</label>
    <input type="text" name="title" required>
    <label>Content:</label>
    <textarea name="content" rows="5" required></textarea>
    <label>Upload Image (optional):</label>
    <input type="file" name="image" accept="image/*">
    <button type="submit">Publish Blog</button>
</form>

<!-- Blog List -->
<?php foreach ($blogs as $blog): ?>
<div class="blog">
    <h2><?= htmlspecialchars($blog['title']) ?></h2>
    <?php if (!empty($blog['image'])): ?>
        <img src="<?= htmlspecialchars($blog['image']) ?>" alt="">
    <?php endif; ?>
    <p><?= nl2br(htmlspecialchars($blog['content'])) ?></p>

    <?php
    // Count likes
    $likes = $conn->query("SELECT COUNT(*) AS total FROM blog_likes WHERE blog_id = " . $blog['id'])->fetch_assoc()['total'];
    // Count comments
    $comments = $conn->query("SELECT COUNT(*) AS total FROM blog_comments WHERE blog_id = " . $blog['id'])->fetch_assoc()['total'];
    ?>
    <div class="stats">
        👍 <?= $likes ?> Likes | 💬 <?= $comments ?> Comments
    </div>

    <!-- View Comments -->
    <div class="comment-section">
        <strong>Comments:</strong><br>
        <?php
        $comRes = $conn->query("SELECT * FROM blog_comments WHERE blog_id = " . $blog['id'] . " ORDER BY created_at DESC");
        if ($comRes->num_rows > 0) {
            while ($c = $comRes->fetch_assoc()) {
                echo "<p><b>" . htmlspecialchars($c['name']) . ":</b> " . htmlspecialchars($c['comment']) . "</p>";
            }
        } else {
            echo "<p>No comments yet.</p>";
        }
        ?>
    </div>
</div>
<?php endforeach; ?>

</body>
</html>
