<!-- Hamburger menu for members-->
<div class="site-menu-toggle js-site-menu-toggle" data-aos="fade">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>

<!-- Hamburger menu for members-->
<div class="site-navbar js-site-navbar">
              <nav role="navigation">
                <div class="container">
                  <div class="row full-height align-items-center">
                    <div class="col-md-6 mx-auto">
                      <ul class="list-unstyled menu">
                        <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'member_page.php' ? 'active' : ''); ?>"><a href="member_page.php">Home</a></li>
                        <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'rooms.php' ? 'active' : ''); ?>"><a href="rooms.php">Rooms</a></li>
                        <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''); ?>"><a href="about.php">About</a></li>
                        <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'view_cart.php' ? 'active' : ''); ?>"><a href="view_cart.php">Booking Cart</a></li>
                        <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'view_my_bookings.php' ? 'active' : ''); ?>"><a href="view_my_bookings.php">View My Bookings</a></li>
                        <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'user_profile.php' ? 'active' : ''); ?>"><a href="user_profile.php">User Profile</a></li>
                        <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'logout.php' ? 'active' : ''); ?>"><a href="logout.php">Logout</a></li>
                      </ul>
                    </div>
                  </div>
                </div>
              </nav>
            </div>