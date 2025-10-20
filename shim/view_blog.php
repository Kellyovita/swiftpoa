<?php
require_once __DIR__ . '/includes/db.php';
$conn = db();

$id = intval($_GET['id'] ?? 0);
$sql = "SELECT * FROM blogs WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$blog = $result->fetch_assoc();

if (!$blog) {
    echo "<p>Blog post not found.</p>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($blog['title']); ?> - SwiftPOA Blog</title>
  <style>
    body {
      font-family: "Poppins", sans-serif;
      background: #f9fafc;
      color: #333;
      margin: 0;
      padding: 0;
      line-height: 1.7;
    }
    .container {
      max-width: 850px;
      margin: 50px auto;
      background: #fff;
      padding: 30px;
      border-radius: 16px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    }
    h1 {
      font-size: 2em;
      color: #1d3557;
      margin-bottom: 20px;
      text-align: center;
    }
    .blog-image {
      max-width: 700px;
      width: 100%;
      height: auto;
      border-radius: 12px;
      display: block;
      margin: 20px auto;
      box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
    p {
      font-size: 1.05em;
      margin: 15px 0;
      text-align: justify;
      color: #444;
    }
    a.back-link {
      display: inline-block;
      margin-top: 25px;
      text-decoration: none;
      color: #fff;
      background: #1d3557;
      padding: 10px 20px;
      border-radius: 8px;
      transition: background 0.3s ease;
    }
    a.back-link:hover {
      background: #457b9d;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1><?php echo htmlspecialchars($blog['title']); ?></h1>
    <?php if (!empty($blog['image'])): ?>
      <img src="<?php echo htmlspecialchars($blog['image']); ?>" alt="Blog Image" class="blog-image">
    <?php endif; ?>
    <p><?php echo nl2br(htmlspecialchars($blog['content'])); ?></p>
    <a href="index.php#blog" class="back-link">← Back to Blogs</a>
  </div>
</body>
</html>
