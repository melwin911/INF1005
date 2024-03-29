<!-- Hamburger menu for members-->
<div class="site-navbar js-site-navbar">
              <nav role="navigation">
                <div class="container">
                  <div class="row full-height align-items-center">
                    <div class="col-md-6 mx-auto">
                      <ul class="list-unstyled menu">
                        <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'member_page.php' ? 'active' : ''); ?>"><a href="index.php">Home</a></li>
                        <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'rooms.php' ? 'active' : ''); ?>"><a href="rooms.php">Rooms</a></li>
                        <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''); ?>"><a href="about.php">About</a></li>
                        <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'user_profile.php' ? 'active' : ''); ?>"><a href="user_profile.php">User Profile</a></li>
                        <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'logout.php' ? 'active' : ''); ?>"><a href="logout.php">Logout</a></li>
                      </ul>
                    </div>
                  </div>
                </div>
              </nav>
            </div>