<?php
function renderNavbar($currentPage) {
    $pages = [
        'Home' => 'index.php',
        'Rooms' => 'rooms.php',
        'About' => 'about.php',
        'User Profile' => 'user_profile.php',
        'Logout' => 'logout.php',
    ];
?>

<!-- start of head section -->
<section class="site-hero inner-page overlay" style="background-image: url(images/slider-6.jpg)">
  <div class="container">
    <div class="row site-hero-inner justify-content-center align-items-center">
      <div class="col-md-10 text-center" data-aos="fade">
        <h1 class="heading mb-3"><?= htmlspecialchars($currentPage) ?></h1>
        <ul class="custom-breadcrumbs mb-4">
          <?php foreach ($pages as $pageName => $pageLink): ?>
            <li>
              <?php if ($pageName == $currentPage): ?>
                <span><?= htmlspecialchars($pageName) ?></span>
              <?php else: ?>
                <a href="<?= htmlspecialchars($pageLink) ?>"><?= htmlspecialchars($pageName) ?></a>
              <?php endif; ?>
            </li>
            <li>&bullet;</li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
</section>

<?php
}
?>
