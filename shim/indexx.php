<?php
// index.php - Visitors Page
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
    /* Reset */
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: Arial, sans-serif;
      line-height: 1.6;
      background: #f9f9f9;
      color: #333;
    }

    /* Header */
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
      height: 40px;
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
      color: #faa403ff;
    }

    /* Hero Section */
    .hero {
      background: linear-gradient(to right, #faa403ff, #faa403ff);
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
      transition: background 0.3s;
    }

    .btn:hover {
      background: #faa403ff;
      color: #222;
    }

    /* Sections */
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

    /* Footer */
    footer {
      background: #222;
      color: #fff;
      padding: 20px;
      text-align: center;
      font-size: 0.9rem;
      margin-top: 30px;
    }

    footer a {
      color: #faa403ff;
      text-decoration: none;
    }

    /* Call button */
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

    /* WhatsApp Chat Button */
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

    .whatsapp-chat img {
      width: 35px;
      height: 35px;
    }
  </style>
</head>
<body>

  <!-- Header -->
  <header>
    <img src="logo.jpg" alt="SwiftPOA Logo">
    <nav>
      <ul>
        <li><a href="#home"><i class="fas fa-home"></i> Home</a></li>
        <li><a href="#about"><i class="fas fa-info-circle"></i> About</a></li>
        <li><a href="#contact"><i class="fas fa-phone"></i> Contact</a></li>
        <li><a href="#blog"><i class="fas fa-blog"></i> Blog</a></li>
        <li><a href="login.php" class="btn"><i class="fas fa-box"></i> Place Order</a></li>
      </ul>
    </nav>
  </header>

  <!-- Hero Section -->
  <div class="hero" id="home">
    <h1>Welcome to SwiftPOA</h1>
    <p>Your trusted delivery partner at KTDA Plaza</p>
    <a href="login.php" class="btn"><i class="fas fa-paper-plane"></i> Get Started</a>
  </div>

  <!-- About Section -->
  <section id="about">
    <div class="card">
      <h2>About Us</h2>
      <p>
        SwiftPOA is a reliable delivery platform connecting vendors, riders, and customers. 
        We ensure fast and safe deliveries across the region. 
        Located at <strong>KTDA Plaza</strong>, we are committed to excellence and customer satisfaction.
      </p>
    </div>
  </section>

  <!-- Contact Section -->
  <section id="contact">
    <div class="card">
      <h2>Contact Us</h2>
      <p><strong>Phone:</strong></p>
      <a href="tel:+254706516771" class="call-btn"><i class="fas fa-phone"></i> 0706 516 771</a>
      <p style="margin-top:15px;"><strong>Email:</strong> <a href="mailto:kellyovvy@gmail.com"><i class="fas fa-envelope"></i> kellyovvy@gmail.com</a></p>
      <p><strong>Location:</strong> <i class="fas fa-map-marker-alt"></i> KTDA Plaza</p>
    </div>
  </section>

  <!-- Blog Section -->
  <section id="blog">
    <div class="card">
      <h2>Latest Blog</h2>
      <p><i class="fas fa-newspaper"></i> Coming soon! Stay tuned for updates, delivery tips, and exciting news from SwiftPOA.</p>
    </div>
  </section>

  <!-- Footer -->
  <footer>
    <p>&copy; <?php echo date("Y"); ?> SwiftPOA. All Rights Reserved.</p>
    <p><i class="fas fa-map-marker-alt"></i> KTDA Plaza | 
       <a href="tel:+254706516771"><i class="fas fa-phone"></i> 0706 516 771</a> | 
       <a href="mailto:kellyovvy@gmail.com"><i class="fas fa-envelope"></i> kellyovvy@gmail.com</a></p>
  </footer>

  <!-- WhatsApp Chat -->
  <a href="https://wa.me/254706516771?text=Hello%20SwiftPOA%2C%20I%20would%20like%20to%20make%20an%20inquiry." 
     class="whatsapp-chat" target="_blank">
    <i class="fab fa-whatsapp fa-2x"></i>
  </a>

</body>
</html>
