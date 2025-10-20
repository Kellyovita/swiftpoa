<?php
// index.php - Visitors Page

include 'includes/db.php'; 
$conn = db();

// Fetch all blogs (posted by admin)
$stmt = $conn->prepare("SELECT id, title, content, image, created_at FROM blogs ORDER BY created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SwiftPOA - Welcome</title>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: Arial, sans-serif;
      line-height: 1.6;
      background: #f9f9f9;
      color: #333;
    }

    /* ===== HEADER ===== */
    header {
      background: #222;
      color: #fff;
      padding: 15px 50px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: sticky;
      top: 0;
      z-index: 1000;
    }

    header img {
      height: 45px;
    }

    nav ul {
      list-style: none;
      display: flex;
      gap: 20px;
    }

    nav ul li a {
      color: #fff;
      text-decoration: none;
      font-weight: bold;
      transition: color 0.3s;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    nav ul li a:hover {
      color: #ffd902ff;
    }

    /* ===== HERO SECTION ===== */
    .hero {
      background: linear-gradient(to right, #110404ff, #ffd902ff);
      height: 350px;
      display: flex;
      justify-content: center;
      align-items: center;
      text-align: center;
      color: #222;
      flex-direction: column;
      padding: 20px;
    }

    .hero h1 {
      font-size: 2.8rem;
      margin-bottom: 10px;
    }

    .hero p {
      font-size: 1.2rem;
      margin-bottom: 20px;
    }

    .btn {
      display: inline-block;
      background: #222;
      color: #fff;
      padding: 12px 25px;
      border-radius: 5px;
      text-decoration: none;
      font-weight: bold;
      transition: all 0.3s ease;
    }

    .btn:hover {
      background: #ffd902ff;
      color: #222;
    }

    /* ===== SECTIONS ===== */
    section {
      padding: 50px 20px;
      max-width: 1100px;
      margin: auto;
    }

    section h2 {
      text-align: center;
      margin-bottom: 20px;
      font-size: 2rem;
      color: #222;
    }

    .card {
      background: #fff;
      margin-bottom: 30px;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }

    /* ===== BLOG SECTION ===== */
    .blog-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 25px;
      margin-top: 20px;
    }

    .blog-post {
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      overflow: hidden;
      transition: transform 0.3s ease;
    }

    .blog-post:hover {
      transform: translateY(-5px);
    }

    .blog-post img {
      width: 100%;
      height: 220px; /* Uniform image height */
      object-fit: cover;
      display: block;
    }

    .blog-content {
      padding: 20px;
    }

    .blog-content h3 {
      margin-bottom: 10px;
      color: #222;
      font-size: 1.2rem;
    }

    .blog-content p {
      color: #555;
      margin-bottom: 15px;
    }

    .blog-meta {
      font-size: 0.9rem;
      color: #777;
      margin-top: 5px;
    }

    /* ===== FOOTER ===== */
    footer {
      background: #222;
      color: #fff;
      padding: 20px;
      text-align: center;
      font-size: 0.9rem;
      margin-top: 30px;
    }

    footer a {
      color: #ffd902ff;
      text-decoration: none;
    }

    /* ===== CONTACT BUTTONS ===== */
    .call-btn {
      display: inline-block;
      background: #25d366;
      color: #fff;
      padding: 10px 20px;
      border-radius: 5px;
      text-decoration: none;
      font-weight: bold;
      margin-top: 10px;
      transition: background 0.3s;
    }

    .call-btn:hover {
      background: #1ebd5a;
    }

    /* ===== WHATSAPP FLOAT BUTTON ===== */
    .whatsapp-chat {
      position: fixed;
      bottom: 20px;
      right: 20px;
      background: #25D366;
      color: #fff;
      border-radius: 50%;
      width: 60px;
      height: 60px;
      display: flex;
      justify-content: center;
      align-items: center;
      box-shadow: 0 3px 6px rgba(0,0,0,0.2);
      cursor: pointer;
      transition: transform 0.3s;
      z-index: 1000;
    }

    .whatsapp-chat:hover {
      transform: scale(1.1);
    }

    @media(max-width:600px){
      header {
        flex-direction: column;
        padding: 10px;
        text-align: center;
      }

      nav ul {
        flex-wrap: wrap;
        justify-content: center;
      }

      .hero h1 { font-size: 2rem; }
      .hero p { font-size: 1rem; }
    }
  </style>
</head>
<body>

  <header>
    <img src="logo.jpg" alt="SwiftPOA Logo">
    <nav>
      <ul>
        <li><a href="#home"><i class="fas fa-home"></i> Home</a></li>
        <li><a href="#about"><i class="fas fa-info-circle"></i> About</a></li>
        <li><a href="#contact"><i class="fas fa-phone"></i> Contact</a></li>
        <li><a href="#blog"><i class="fas fa-blog"></i> Blog</a></li>
        <li><a href="#terms"><i class="fas fa-file-contract"></i> Terms</a></li>
        <li><a href="login.php" class="btn"><i class="fas fa-box"></i> Place Order</a></li>
      </ul>
    </nav>
  </header>

  <div class="hero" id="home">
    <h1>Welcome to SwiftPOA</h1>
    <p>Your trusted delivery partner at KTDA Plaza</p>
    <a href="login.php" class="btn"><i class="fas fa-paper-plane"></i> Get Started</a>
  </div>

  <section id="about">
    <div class="card">
      <h2>About Us</h2>
      <p>SwiftPOA is a reliable delivery platform connecting vendors, riders, and customers. We ensure fast and safe deliveries across Nairobi.</p>
      <ul>
        <li>Located at <strong>KTDA Plaza</strong> opposite Kenya Cinema, we are committed to excellence and customer satisfaction.</li>
        <li>Usijistress — tutakusort! Just make an order and we’ll deliver in minutes 🚴‍♂️</li>
      </ul>
    </div>
  </section>

  <section id="contact">
    <div class="card">
      <h2>Contact Us</h2>
      <p><strong>Phone:</strong></p>
      <a href="tel:+254700688470" class="call-btn"><i class="fas fa-phone"></i> +254 700 688470</a>
      <p style="margin-top:15px;"><strong>Email:</strong> 
        <a href="mailto:Shimsheldon24@gmail.com"><i class="fas fa-envelope"></i> Shimsheldon24@gmail.com</a>
      </p>
      <p><strong>Location:</strong> <i class="fas fa-map-marker-alt"></i> KTDA Plaza</p>
    </div>
  </section>

  <section id="blog">
    <div class="card">
      <h2>Latest Blogs</h2>
      <?php if ($result->num_rows > 0): ?>
        <div class="blog-container">
          <?php while ($row = $result->fetch_assoc()): ?>
            <div class="blog-post">
              <?php if (!empty($row['image'])): ?>
                <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="Blog Image">
              <?php endif; ?>
              <div class="blog-content">
                <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                <p><?php echo substr(strip_tags($row['content']), 0, 180); ?>...</p>
                <a href="view_blog.php?id=<?php echo $row['id']; ?>" class="btn">Read More</a>
                <p class="blog-meta"><i class="fas fa-calendar"></i> 
                  <?php echo date("F j, Y", strtotime($row['created_at'])); ?>
                </p>
              </div>
            </div>
          <?php endwhile; ?>
        </div>
      <?php else: ?>
        <p><i class="fas fa-newspaper"></i> No blog posts yet. Stay tuned!</p>
      <?php endif; ?>
    </div>
  </section>

  <footer>
    <p>&copy; <?php echo date("Y"); ?> SwiftPOA. All Rights Reserved.</p>
    <p><i class="fas fa-map-marker-alt"></i> KTDA Plaza | 
       <a href="tel:+254700688470"><i class="fas fa-phone"></i> +254 700 688470</a> | 
       <a href="mailto:Shimsheldon24@gmail.com"><i class="fas fa-envelope"></i> Shimsheldon24@gmail.com</a></p>
    <p><a href="https://visionhub.co.ke" target="_blank">By VisionHub</a></p>
  </footer>

  <a href="https://wa.me/254700688470?text=Hello%20SwiftPOA%2C%20I%20would%20like%20to%20make%20an%20inquiry."
     class="whatsapp-chat" target="_blank">
    <i class="fab fa-whatsapp fa-2x"></i>
  </a>

</body>
</html>
