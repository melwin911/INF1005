<!DOCTYPE HTML>
<html lang="en">

<?php
    include "head.inc.php";
    ?>

  <body>

  <?php
    include "header.inc.php";
    ?>
     
            
  <!-- Start of Navbar  -->
  <?php
    include "navbar.inc.php";
      ?>
  <!-- End of navbar  -->

    <!-- start of head section -->
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
              <li>Login</li>
            </ul>
          </div>
        </div>
      </div>
    </section>
    <!-- END head section -->


    <div class="container section">

      <main class="container" id="registration"> 
        <h1>Member Login</h1> 
        <p> 
            Existing members log in here. For new members, please sign up at 
            <a href="registration.php">Registration page</a>. 
        </p> 
        <form action="process_login.php" method="post"> 
            <div class="mb-3"> 
                <label for="email" class="form-label">Email:</label> 
                <input required type="email" id="email" name="email" class="form-control" placeholder="Enter email"> 
            </div> 
            <div class="mb-3"> 
                <label for="pwd" class="form-label">Password:</label> 
                <input required type="password" id="pwd" name="pwd" class="form-control" placeholder="Enter password"> 
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
    
  </body>
</html>