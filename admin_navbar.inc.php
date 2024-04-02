<!-- Hamburger menu for admin-->
<div class="site-menu-toggle js-site-menu-toggle" data-aos="fade">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>

<!-- Hamburger menu for admin-->
<div class="site-navbar js-site-navbar">
              <nav role="navigation">
                <div class="container">
                  <div class="row full-height align-items-center">
                    <div class="col-md-6 mx-auto">
                      <ul class="list-unstyled menu">
                        <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'view_bookings.php' ? 'active' : ''); ?>"><a href="view_bookings.php">View Bookings</a></li>
                        <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'rooms.php' ? 'active' : ''); ?>"><a href="edit.rooms.php">Rooms</a></li>
                        <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'logout.php' ? 'active' : ''); ?>"><a href="logout.php">Logout</a></li>
                      </ul>
                    </div>
                  </div>
                </div>
              </nav>
            </div>