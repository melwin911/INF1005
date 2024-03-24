<!DOCTYPE html>
<html lang="en">
<head>
    <title>Test Hotel</title>
    <?php
    include "head.inc.php";
    ?>
</head>

<body>
    <?php
    include "header.inc.php";
    include "navbar.inc.php";
    ?>

    <section class="site-hero inner-page overlay" style="background-image: url(images/slider-6.jpg)" >
      <div class="container">
        <div class="row site-hero-inner justify-content-center align-items-center">
          <div class="col-md-10 text-center" data-aos="fade">
            <h1 class="heading mb-3">Login</h1>
            <ul class="custom-breadcrumbs mb-4">
              <li><a href="index.php">Home</a></li>
              <li>&bullet;</li>
              <li><a href="rooms.php">Rooms</a></li>
              <li>&bullet;</li>
              <li><a href="about.php">About</a></li>
              <li>&bullet;</li>
              <li><a href="registration.php">Registration</a></li>
              <li>&bullet;</li>
              <li>Login</li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <main class="container" id="registration"> 
        <h1>Member Login</h1> 
        <p> 
            Existing members log in here. For new members, please sign up at 
            <a href="registration.php">Registration page</a>. 
        </p> 
        <form action="process_login.php" method="post"> 
            <div class="mb-3"> 
                <label for="email" class="form-label">Email:</label> 
                <input required maxlength="45" type="email" id="email" name="email" class="form-control" placeholder="Enter email"> 
            </div> 
            <div class="mb-3"> 
                <label for="pwd" class="form-label">Password:</label> 
                <input required type="password" id="pwd" name="pwd" class="form-control" placeholder="Enter password"> 
            </div> 
            <div class="mb-3">
                <input type="checkbox" id="rememberme" name="rememberme" class="form-check-input">
                <label for="rememberme" class="form-check-label">Remember Me</label>
            </div>
            <div class="mb-3"> 
                <button type="submit" class="btn btn-primary">Submit</button> 
            </div> 
        </form>
    </main>
   <!-- Start of footer -->
    <?php
      include "footer.inc.php";
      ?>
<!-- End of footer -->
<script src="https://cdn.botpress.cloud/webchat/v1/inject.js"></script>
<script src="https://mediafiles.botpress.cloud/5839c45b-a068-4754-9a6c-6e58dee3de97/webchat/config.js" defer></script>
    
</body>
</html>