<?php
require 'config.php';
$pageTitle = "Guide Profesional";
require 'header.php';

// Data guide dengan foto dari folder images
$guides = [
  [
    'name' => 'Muhamad Akbar Ergiansyah',
    'nickname' => 'Akbar',
    'lang' => 'Indonesia, English',
    'exp' => '20 Tahun',
    'photo' => 'images/aku.jpg',
    'specialty' => 'Wisata Alam & Tracking',
    'bio' => 'Guide profesional spesialis wisata alam, survival, dan tracking di kawasan Bandung Raya. Berpengalaman memandu ribuan wisatawan lokal dan mancanegara.',
    'rating' => 4.9,
    'tours_count' => 850,
    'tours' => [
      [
        'title' => 'Sunrise Adventure Lembang',
        'duration' => '7 Jam',
        'rundown' => [
          '04.30 - Penjemputan dan briefing singkat',
          '05.15 - Tiba di spot sunrise Gunung Putri',
          '06.00 - Dokumentasi foto & video',
          '07.00 - Sarapan khas Sunda',
          '09.00 - Tracking ringan ke spot tebing',
          '11.00 - Kembali & penutupan'
        ]
      ]
    ]
  ],
  [
    'name' => 'Abi Rahman',
    'nickname' => 'Abi',
    'lang' => 'Indonesia',
    'exp' => '7 Tahun',
    'photo' => 'images/abi.jpg',
    'specialty' => 'Tour Fun & Adventure',
    'bio' => 'Guide berkepribadian fun & humoris, ahli dalam menciptakan suasana tour yang menyenangkan. Sangat cocok untuk perjalanan bersama teman atau komunitas.',
    'rating' => 4.8,
    'tours_count' => 420,
    'tours' => [
      [
        'title' => 'Ciwidey Eksplore 1 Hari',
        'duration' => '9 Jam',
        'rundown' => [
          '06.00 - Penjemputan Peserta',
          '07.30 - Tiba di Kawah Putih, sesi foto & cerita sejarah',
          '09.00 - Kunjungan perkebunan teh',
          '11.00 - Panen strawberry bebas pilih',
          '12.30 - Makan siang khas Ciwidey',
          '14.30 - Kembali & penutupan'
        ]
      ]
    ]
  ],
  [
    'name' => 'Dewi Kusumastuti',
    'nickname' => 'Ahay',
    'lang' => 'Indonesia, English, Japanese',
    'exp' => '12 Tahun',
    'photo' => 'images/ahay.jpg',
    'specialty' => 'Family & International Tour',
    'bio' => 'Guide perempuan profesional yang fasih berbahasa asing. Sangat berpengalaman melayani tamu keluarga dan wisatawan mancanegara dari berbagai negara.',
    'rating' => 4.9,
    'tours_count' => 680,
    'tours' => [
      [
        'title' => 'Pangalengan Lakeside Journey',
        'duration' => '9 Jam',
        'rundown' => [
          '05.00 - Berangkat menuju Pangalengan',
          '06.15 - Foto sunrise di Danau Cileunca',
          '07.00 - Perahu & eksplor spot foto rahasia',
          '09.00 - Sarapan di pinggir danau',
          '11.00 - Mini tracking ke hutan pinus',
          '14.00 - Kembali ke titik jemput'
        ]
      ]
    ]
  ]
];
?>

<style>
/* Hero Section */
.guide-hero {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 60px 0;
  color: white;
  text-align: center;
  margin-bottom: 40px;
}

/* Guide Card */
.guide-card {
  background: #fff;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 10px 40px rgba(0,0,0,0.1);
  transition: all 0.3s ease;
  margin-bottom: 30px;
}

.guide-card:hover {
  transform: translateY(-10px);
  box-shadow: 0 20px 50px rgba(0,0,0,0.15);
}

/* Profile Section */
.guide-profile {
  padding: 30px;
  text-align: center;
  background: linear-gradient(180deg, #f8f9ff 0%, #fff 100%);
}

.guide-photo {
  width: 150px;
  height: 150px;
  border-radius: 50%;
  object-fit: cover;
  border: 5px solid #fff;
  box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
  margin-bottom: 20px;
}

.guide-name {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 5px;
}

.guide-specialty {
  color: #667eea;
  font-weight: 600;
  margin-bottom: 15px;
}

.guide-bio {
  color: #6b7280;
  font-size: 0.95rem;
  line-height: 1.6;
}

/* Stats */
.guide-stats {
  display: flex;
  justify-content: center;
  gap: 30px;
  margin: 20px 0;
  padding: 20px;
  background: #fff;
  border-radius: 15px;
}

.stat-item {
  text-align: center;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #667eea;
}

.stat-label {
  font-size: 0.8rem;
  color: #9ca3af;
  text-transform: uppercase;
}

/* Info Badges */
.guide-badges {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  justify-content: center;
  margin-top: 15px;
}

.badge-custom {
  padding: 8px 16px;
  border-radius: 50px;
  font-size: 0.85rem;
  font-weight: 500;
}

.badge-lang {
  background: #e0e7ff;
  color: #4338ca;
}

.badge-exp {
  background: #d1fae5;
  color: #059669;
}

/* Tour Section */
.tour-section {
  padding: 25px;
  background: #fff;
}

.tour-title {
  font-size: 1.2rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 5px;
}

.tour-duration {
  color: #667eea;
  font-size: 0.9rem;
  margin-bottom: 20px;
}

/* Timeline */
.timeline {
  position: relative;
  padding-left: 25px;
}

.timeline::before {
  content: '';
  position: absolute;
  left: 8px;
  top: 5px;
  bottom: 5px;
  width: 2px;
  background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
}

.timeline-item {
  position: relative;
  padding: 10px 0;
  padding-left: 20px;
  color: #4b5563;
  font-size: 0.95rem;
}

.timeline-item::before {
  content: '';
  position: absolute;
  left: -21px;
  top: 50%;
  transform: translateY(-50%);
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: #667eea;
  border: 3px solid #fff;
  box-shadow: 0 0 0 2px #667eea;
}

/* Animation */
[data-aos] {
  opacity: 0;
  transition: all 0.6s ease;
}
[data-aos].aos-animate {
  opacity: 1;
}
</style>

<!-- Hero Section -->
<div class="guide-hero">
  <div class="container">
    <h1 class="display-5 fw-bold mb-3">
      <i class="fas fa-user-tie me-3"></i>Guide Profesional Kami
    </h1>
    <p class="lead mb-0 opacity-75">
      Tim guide berpengalaman siap menemani perjalanan wisata Anda di Bandung
    </p>
  </div>
</div>

<div class="container pb-5">
  <div class="row justify-content-center">
    
    <?php foreach($guides as $index => $g): ?>
    <div class="col-lg-10 mb-4" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
      <div class="guide-card">
        <div class="row g-0">
          <!-- Profile Side -->
          <div class="col-md-4">
            <div class="guide-profile h-100">
              <img src="<?= $g['photo'] ?>" alt="<?= $g['name'] ?>" class="guide-photo">
              <h3 class="guide-name"><?= $g['name'] ?></h3>
              <div class="guide-specialty">
                <i class="fas fa-certificate me-1"></i><?= $g['specialty'] ?>
              </div>
              <p class="guide-bio"><?= $g['bio'] ?></p>
              
              <!-- Stats -->
              <div class="guide-stats">
                <div class="stat-item">
                  <div class="stat-value">
                    <i class="fas fa-star text-warning"></i> <?= $g['rating'] ?>
                  </div>
                  <div class="stat-label">Rating</div>
                </div>
                <div class="stat-item">
                  <div class="stat-value"><?= $g['tours_count'] ?>+</div>
                  <div class="stat-label">Tour</div>
                </div>
              </div>
              
              <!-- Badges -->
              <div class="guide-badges">
                <span class="badge-custom badge-lang">
                  <i class="fas fa-language me-1"></i><?= $g['lang'] ?>
                </span>
                <span class="badge-custom badge-exp">
                  <i class="fas fa-award me-1"></i><?= $g['exp'] ?>
                </span>
              </div>
            </div>
          </div>
          
          <!-- Tour Side -->
          <div class="col-md-8">
            <div class="tour-section h-100">
              <h4 class="mb-4 fw-bold" style="color: #667eea;">
                <i class="fas fa-route me-2"></i>Contoh Itinerary Tour
              </h4>
              
              <?php foreach($g['tours'] as $t): ?>
              <div class="mb-4">
                <h5 class="tour-title">
                  <i class="fas fa-map-marked-alt me-2"></i><?= $t['title'] ?>
                </h5>
                <div class="tour-duration">
                  <i class="fas fa-clock me-1"></i>Durasi: <?= $t['duration'] ?>
                </div>
                
                <div class="timeline">
                  <?php foreach($t['rundown'] as $r): ?>
                  <div class="timeline-item"><?= $r ?></div>
                  <?php endforeach; ?>
                </div>
              </div>
              <?php endforeach; ?>
              
              <!-- CTA Button -->
              <div class="mt-4 pt-3 border-top">
                <a href="https://wa.me/6289508891566?text=Halo, saya ingin booking guide <?= urlencode($g['nickname']) ?> untuk tour." 
                   class="btn btn-primary rounded-pill px-4">
                  <i class="fab fa-whatsapp me-2"></i>Booking Guide <?= $g['nickname'] ?>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
    
  </div>
  
  <!-- Call to Action -->
  <div class="text-center mt-5 py-5" style="background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%); border-radius: 20px;">
    <h3 class="fw-bold mb-3">Butuh Guide Khusus?</h3>
    <p class="text-muted mb-4">Hubungi kami untuk request guide sesuai kebutuhan wisata Anda</p>
    <a href="https://wa.me/6289508891566" class="btn btn-lg btn-success rounded-pill px-5">
      <i class="fab fa-whatsapp me-2"></i>Hubungi Kami
    </a>
  </div>
</div>

<script>
// Simple scroll animation
document.addEventListener('DOMContentLoaded', function() {
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('aos-animate');
      }
    });
  }, { threshold: 0.1 });
  
  document.querySelectorAll('[data-aos]').forEach(el => observer.observe(el));
});
</script>

<?php require 'footer.php'; ?>
