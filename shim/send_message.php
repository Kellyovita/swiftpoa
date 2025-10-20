<?php
include('db.php');
$conn = db();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $msg = $_POST['message'];

    $stmt = $conn->prepare("INSERT INTO messages (sender_name, sender_email, message) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $msg);

    if ($stmt->execute()) {
        echo "Message sent!";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Send Message - SwiftPOA</title>
</head>
<body>
  <h1>Send a Message</h1>
  <form method="POST">
    <input type="text" name="name" placeholder="Your Name" required><br><br>
    <input type="email" name="email" placeholder="Your Email"><br><br>
    <textarea name="message" placeholder="Write your message here..." required></textarea><br><br>
    <button type="submit">Send</button>
  </form>
</body>
</html>
