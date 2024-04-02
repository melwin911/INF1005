<!-- Hamburger menu for non-members-->
<div class="site-menu-toggle js-site-menu-toggle" data-aos="fade">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>

<div class="site-navbar js-site-navbar">
  <nav role="navigation">
    <div class="container">
      <div class="row full-height align-items-center">
        <div class="col-md-6 mx-auto">
          <ul class="list-unstyled menu">
            <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''); ?>"><a href="index.php">Home</a></li>
            <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'rooms.php' ? 'active' : ''); ?>"><a href="rooms.php">Rooms</a></li>
            <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''); ?>"><a href="about.php">About</a></li>
            <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'registration.php' ? 'active' : ''); ?>"><a href="registration.php">Registration</a></li>
            <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'login.php' ? 'active' : ''); ?>"><a href="login.php">Login</a></li>
          </ul>
        </div>
      </div>
    </div>
  </nav>
</div>