</main>

<!-- FOOTER - Modern Design -->
<footer class="footer mt-5">
  <div class="container-lg">
    <div class="row gy-4 pb-4">
      
      <!-- Brand Section -->
      <div class="col-lg-3 col-md-6">
        <div class="d-flex align-items-center gap-2 mb-3">
          <div style="width:48px; height:48px; border-radius:12px; background: linear-gradient(135deg, #667eea, #764ba2); display:flex; align-items:center; justify-content:center; color:#fff; font-weight:700; font-size:1.3rem;">
            <i class="fas fa-map-location-dot"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white">Explore Bandung</h6>
            <small style="color: #9ca3af;">Wisata & Petualangan</small>
          </div>
        </div>
        <p style="color: #d1d5db;" class="mb-3">
          Platform wisata terpercaya untuk menjelajahi keindahan Bandung dengan paket pilihan dan guide profesional.
        </p>
        <div class="d-flex gap-3">
          <a href="https://www.instagram.com/mhamadakbr_?igsh=Y3JwOWU4OThyYnhj" target="_blank" class="footer-social-link">
            <i class="fab fa-instagram fa-lg"></i>
          </a>
          <a href="https://www.youtube.com/@muhamadakbar8578" target="_blank" class="footer-social-link">
            <i class="fab fa-youtube fa-lg"></i>
          </a>
          <a href="https://wa.me/6289508891566?text=Halo%20admin,%20saya%20mau%20tanya%20paket%20wisata" target="_blank" class="footer-social-link">
            <i class="fab fa-whatsapp fa-lg"></i>
          </a>
          <a href="mailto:info@explorebandung.com" class="footer-social-link">
            <i class="fas fa-envelope fa-lg"></i>
          </a>
        </div>
      </div>

      <!-- Menu Links -->
      <div class="col-lg-2 col-md-6">
        <h6 class="fw-bold mb-3 text-white">Menu Utama</h6>
        <ul class="list-unstyled">
          <li class="mb-2"><a href="index.php" class="footer-link"><i class="fas fa-angle-right me-2"></i>Beranda</a></li>
          <li class="mb-2"><a href="paket.php" class="footer-link"><i class="fas fa-angle-right me-2"></i>Paket Wisata</a></li>
          <li class="mb-2"><a href="guide.php" class="footer-link"><i class="fas fa-angle-right me-2"></i>Guide</a></li>
          <li class="mb-2"><a href="galerry.php" class="footer-link"><i class="fas fa-angle-right me-2"></i>Galeri</a></li>
        </ul>
      </div>

      <!-- Account Links -->
      <div class="col-lg-2 col-md-6">
        <h6 class="fw-bold mb-3 text-white">Akun</h6>
        <ul class="list-unstyled">
          <?php if(isset($_SESSION['user_id'])): ?>
            <li class="mb-2"><a href="dashboard.php" class="footer-link"><i class="fas fa-angle-right me-2"></i>Dashboard</a></li>
            <li class="mb-2"><a href="riwayat.php" class="footer-link"><i class="fas fa-angle-right me-2"></i>Pemesanan Saya</a></li>
            <li class="mb-2"><a href="logout.php" class="footer-link"><i class="fas fa-angle-right me-2"></i>Logout</a></li>
          <?php else: ?>
            <li class="mb-2"><a href="login.php" class="footer-link"><i class="fas fa-angle-right me-2"></i>Login</a></li>
            <li class="mb-2"><a href="register.php" class="footer-link"><i class="fas fa-angle-right me-2"></i>Daftar</a></li>
          <?php endif; ?>
        </ul>
      </div>

      <!-- Contact Info -->
      <div class="col-lg-3 col-md-6">
        <h6 class="fw-bold mb-3 text-white">Hubungi Kami</h6>
        <div class="mb-3">
          <div class="mb-2" style="color: #d1d5db;"><i class="fas fa-map-marker-alt me-2" style="color: #667eea;"></i>Jl. Contoh No.1 Bandung</div>
          <div class="mb-2" style="color: #d1d5db;"><i class="fas fa-envelope me-2" style="color: #667eea;"></i>info@explorebdg.id</div>
          <div style="color: #d1d5db;"><i class="fas fa-phone me-2" style="color: #667eea;"></i>+62 895 0889 1566</div>
        </div>
      </div>
    </div>

    <!-- Divider -->
    <hr class="my-4" style="border-color: rgba(255,255,255,0.1);">

    <!-- Copyright -->
    <div class="row align-items-center">
      <div class="col-12 text-center">
        <p class="small mb-2" style="color: #9ca3af;">
          &copy; <?= date('Y') ?> Explore Bandung. All rights reserved.
        </p>
        <p class="small mb-0" style="color: #d1d5db;">
          <i class="fas fa-code me-1"></i> Developed by <strong style="color: #667eea;">Muhamad Akbar Ergiansyah</strong> • 23552011411 • TIF 23-CNS B
        </p>
      </div>
    </div>
  </div>
</footer>

<style>
/* Footer link styles */
.footer-link {
  color: #9ca3af !important;
  text-decoration: none;
  transition: all 0.3s ease;
  display: inline-block;
}
.footer-link:hover {
  color: #fff !important;
  transform: translateX(5px);
}
.footer-social-link {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: rgba(255,255,255,0.1);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #9ca3af !important;
  text-decoration: none;
  transition: all 0.3s ease;
}
.footer-social-link:hover {
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: #fff !important;
  transform: translateY(-3px);
}
</style>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>

<!-- Custom JS -->
<script>
  // Intersection Observer untuk animasi
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('in-view');
      }
    });
  }, { threshold: 0.1 });

  document.querySelectorAll('[data-animate]').forEach(el => {
    observer.observe(el);
  });

  // Initialize AOS
  AOS.init({
    duration: 1000,
    once: true,
    offset: 100
  });

  // Session validation - check if user session is still valid
  <?php if(isset($_SESSION['user_id'])): ?>
    const sessionCheckInterval = setInterval(function() {
      fetch('api/check_session.php')
        .then(response => response.json())
        .then(data => {
          if (!data.valid) {
            alert('Sesi Anda telah berakhir. Silakan login kembali.');
            window.location.href = 'logout.php';
          }
        })
        .catch(err => console.error('Session check error:', err));
    }, 5 * 60 * 1000); // Check every 5 minutes
  <?php endif; ?>

  // Smooth scrolling
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });
</script>

</body>
</html>
