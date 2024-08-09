<?php include 'navbar.php'; ?>
<!DOCTYPE html>
<lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Kape Kita Coffee Shop</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <!-- bootstrap links -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <!-- bootstrap links -->
    <!-- fonts links -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
    <!-- fonts links -->

    <style>
        .cart-icon {
            position: fixed;
            bottom: 100px;
            right: 50px;
            background-color: #ffffff;
            border: 2px solid #000000;
            border-radius: 50%;
            padding: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            height: 50px;
            z-index: 1000; 
        }

        .cart-icon i {
            font-size: 24px;
        }

        .checkout-form {
            max-width: 400px;
            margin: auto;
        }
    </style>
</head>

<body>
  <div class="all-content">
<!-- home section -->
   <section id="home">
    <div class="content">
      <h3>Start Your Day With a <br> Fresh Coffee</h3>
      <p>Experience the rich flavors of freshly brewed coffee that will awaken your senses.
      </p>
      <button id="btn">Shop Now</button>
    </div>
   </section>
<!-- home section -->

<!-- about section -->
<div class="about" id="about">
  <div class="container">
  <div class="heading">About <span>Us</span></div>
    <div class="row">
      <div class="col-md-6">
        <div class="card">
          <img src="./image/about.png" alt="">
        </div>
      </div>
      <div class="col-md-6">
        <h3>What Makes Our Coffee Special?</h3>
        <p>Discover what makes our coffee stand out from the rest. We meticulously select the finest beans and roast them to perfection to bring out their unique flavors.
          <br><br>At Kape Kita, we are passionate about providing you with an exceptional coffee experience. Our commitment to quality and craftsmanship ensures every cup is a delight.
          <br><br>Join us on a journey of flavor and aroma that will elevate your coffee moments. Whether you prefer a robust espresso or a smooth latte, our coffee will exceed your expectations.
        </p>
         <button id="about-btn">Learn More.</button>
      </div>
    </div>
  </div>
</div>
<!-- about section -->

<!-- top cards -->
<section class="top-cards">
  <div class="heading2">Top <span>Categories</span></div>
  <div class="container">
    <div class="row">
      <div class="col-md-4 py-3 py-md-0">
        <div class="card">
          <img src="./image/c1.png" alt="">
          
        </div>
      </div>
      <div class="col-md-4 py-3 py-md-0">
        <div class="card">
          <img src="./image/c2.png" alt="">
          
        </div>
      </div>
      <div class="col-md-4 py-3 py-md-0">
        <div class="card">
          <img src="./image/c3.png" alt="">
          
        </div>
      </div>
    </div>
  </div>
</section>
<!-- top cards -->

<!-- product -->
<section id="product" class="product">
  <div class="container">
  <div class="heading4">Products</div>
</div>
  <div class="container" id="container3">

    <div class="row">
      <div class="col-md-3 py-3 py-md-0">
        <div class="card">
          <img src="./image/arabica coffee.png" alt="">
          <div class="card-body">
            <h3>Arabic Coffee</h3>
            <p>₱120 <strike>₱150</strike> <span class="add-to-cart" data-name="Arabic Coffee" data-price="120"><i class="fa-solid fa-cart-shopping"></i></span></p>
          </div>
        </div>
      </div>
      <div class="col-md-3 py-3 py-md-0">
        <div class="card">
          <img src="./image/Cappuccino coffee.png" alt="">
          <div class="card-body">
            <h3>Cappuccino Coffee</h3>
            <p>₱250 <strike>₱350</strike> <span class="add-to-cart" data-name="Cappuccino Coffee" data-price="250"><i class="fa-solid fa-cart-shopping"></i></span></p>

          </div>
        </div>
      </div>
      <div class="col-md-3 py-3 py-md-0">
        <div class="card">
          <img src="./image/Black Coffee.png" alt="">
          <div class="card-body">
            <h3>Black Coffee</h3>
            <p>₱130 <strike>₱150</strike> <span class="add-to-cart" data-name="Black Coffee" data-price="130"><i class="fa-solid fa-cart-shopping"></i></span></p>
          </div>
        </div>
      </div>
      <div class="col-md-3 py-3 py-md-0">
        <div class="card">
          <img src="./image/Decaf caffee.png" alt="">
          <div class="card-body">
            <h3>Decaf Coffee</h3>
            <p>₱120 <strike>₱150</strike> <span class="add-to-cart" data-name="Decaf Coffee" data-price="120"><i class="fa-solid fa-cart-shopping"></i></span></p>

          </div>
        </div>
      </div>
    </div>

  </div>
</section>
<!-- product -->

<!-- our gallary -->
<div class="container" id="gallary">
  <h1>Our <span>Gallary</span></h1>
  <div class="row" style="margin-top: 30px;">
    <div class="col-md-4 py-3 py-md-0">
      <div class="card">
        <img src="./image/image1.png" alt="">
      </div>
    </div>
    <div class="col-md-4 py-3 py-md-0">
      <div class="card">
        <img src="./image/image2.png" alt="">
      </div>
    </div>
    <div class="col-md-4 py-3 py-md-0">
      <div class="card">
        <img src="./image/image3.png" alt="">
      </div>
    </div>
  </div>
  <div class="row" style="margin-top: 30px;">
    <div class="col-md-4 py-3 py-md-0">
      <div class="card">
        <img src="./image/image4.png" alt="">
      </div>
    </div>
    <div class="col-md-4 py-3 py-md-0">
      <div class="card">
        <img src="./image/image5.png" alt="">
      </div>
    </div>
    <div class="col-md-4 py-3 py-md-0">
      <div class="card">
        <img src="./image/image6.png" alt="">
      </div>
    </div>
  </div>
</div>
<!-- our gallary -->

<!-- contact -->
<section class="contact" id="contact">
  <div class="container">
    <div class="row">
      <div class="col-md-7">
        <div class="heading6">Contact <span>Us</span></div>
        <p>Have questions or feedback? Feel free to reach out to us!</p>

        <input class="form-control" type="text" placeholder="Name" aria-label="Name"><br>
        <input class="form-control" type="email" placeholder="Email" aria-label="Email"><br>
        <input class="form-control" type="tel" placeholder="Phone Number" aria-label="Phone Number"><br>
        <button id="contact-btn">Send Message</button>
      </div>
      <div class="col-md-5" id="col">
        <h1>Contact Information</h1>
        <p><i class="fa-regular fa-envelope"></i> info@kapekita.com.ph</p>
        <p><i class="fa-solid fa-phone"></i> +63 912 345 6789</p>
        <p><i class="fa-solid fa-building-columns"></i> Manila, Philippines</p>
        <p>Visit us to experience the finest coffee in the heart of Manila.</p>
      </div>
    </div>
  </div>
</section>
<!-- contact -->

   <!-- footer -->
   <footer id="footer">
    <div class="footer-logo text-center"><img src="./image/logo.png" alt=""></div>
    <div class="socail-links text-center">
      <i class="fa-brands fa-twitter"></i>
      <i class="fa-brands fa-facebook-f"></i>
      <i class="fa-brands fa-instagram"></i>
      <i class="fa-brands fa-youtube"></i>
      <i class="fa-brands fa-pinterest-p"></i>
    </div>
    <div class="copyright text-center">
      &copy; Copyright <strong><span>Kape Kita Coffee Shop</span></strong>. All Rights Reserved
  </div>
</footer>
   <!-- footer -->

   <a href="#" class="arrow"><i><img src="./image/up-arrow.png" alt="" width="50px"></i></a>


  </div>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>