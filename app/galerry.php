<?php 
require 'config.php';
require 'header.php'; 
?>

<?php
// =====================================
// Daftar wilayah + deskripsi foto berurutan
// =====================================
$wilayahList = [
  "Lembang" => [
    "desc_foto" => [
      "Tangkuban Perahu – Ikon wisata kota Bandung dengan pemandangan kawah megah.",
      "Orchid Forest – Hutan anggrek paling besar di Indonesia, sejuk dan instagramable.",
      "Situ Lembang – Danau alami dengan suasana pegunungan yang tenang dan indah.",
      "Jembatan Gantung – Spot foto unik di tengah alam perbukitan yang menawan."
    ]
  ],
  "Ciwidey" => [
    "desc_foto" => [
      "Rancabolang – Kebun teh hijau luas dengan pemandangan pegunungan yang menyejukkan.",
      "Kawah Putih – Destinasi terkenal dengan danau belerang warna toska yang eksotis.",
      "Situ Patenggang – Danau cantik dengan view alam romantis di dataran tinggi.",
      "Cibuni – Perkebunan dengan pemandangan lembah hijau yang memanjakan mata."
    ]
  ],
  "Pangalengan" => [
    "desc_foto" => [
      "Sunrise Cukul – Panorama matahari terbit terbaik di Bandung selatan.",
      "Cibolang – Area pegunungan dan kolam air panas alami.",
      "Rahong – Kawasan hutan rimbun yang cocok untuk adventure & camping.",
      "Sayang Heulang – Padang hijau luas dengan view lembah dan bukit indah."
    ]
  ]
];

// =====================================
// SCAN semua gambar di folder /images
// =====================================
$folder = "images/";
$files = glob($folder . "*.{jpg,jpeg,png}", GLOB_BRACE);

// kosongkan list gambar dulu
foreach ($wilayahList as $k => $v) {
    $wilayahList[$k]['img'] = [];
}

foreach ($files as $img) {
    $filename = strtolower(basename($img));

    foreach ($wilayahList as $wilayah => $data) {

        $key = strtolower($wilayah);
        $keyClean = str_replace(" ", "", $key);
        $fileClean = str_replace(" ", "", $filename);

        if (strpos($fileClean, $keyClean) !== false) {
            $wilayahList[$wilayah]['img'][] = $img;
        }
    }
}
?>

<div class="container mt-5">

  <!-- HERO -->
  <div class="hero mb-5">
    <div class="row align-items-center">
      <div class="col-md-7" data-animate>
        <h1 class="fw-bold">Jelajahi Keindahan Bandung</h1>
        <p class="lead text-muted">
          Galeri visual dari Lembang, Ciwidey, dan Pangalengan.
        </p>
        <a href="paket.php" class="btn btn-cta btn-lg">Lihat Paket Populer</a>
      </div>
      <div class="col-md-5 text-end" data-animate>
        <img src="images/lembang.jpg" 
             style="width:220px;border-radius:12px;box-shadow:0 12px 30px rgba(16,24,40,0.08)">
      </div>
    </div>
  </div>

  <!-- FOTO PER WILAYAH -->
  <?php foreach ($wilayahList as $wilayah => $data) { ?>
    <div class="mt-5" data-animate>

      <!-- JUDUL WILAYAH -->
      <h3 class="fw-bold mb-4"><?= $wilayah ?></h3>

      <div class="row">
      <?php
      if (empty($data['img'])) {
        echo '<div class="col-12"><p class="text-muted">Belum ada gambar.</p></div>';
      } else {

        $fotoDeskripsi = isset($data['desc_foto']) ? $data['desc_foto'] : [];

        foreach ($data['img'] as $i => $img) {

          $deskripsi = isset($fotoDeskripsi[$i]) ? $fotoDeskripsi[$i] : "";
      ?>
          <div class="col-sm-6 col-md-3 mb-4" data-animate>
            <div class="card-modern" style="height:100%;display:flex;flex-direction:column">
              
              <div class="img-hover-zoom">
                <img src="<?= $img ?>" alt="<?= basename($img) ?>" class="img-fluid">
              </div>

              <?php if ($deskripsi != "") { ?>
              <div class="p-3">
                <p class="small text-muted mb-0"><?= $deskripsi ?></p>
              </div>
              <?php } ?>

            </div>
          </div>
      <?php 
        } 
      } 
      ?>
      </div>

    </div>
  <?php } ?>

</div>

<?php include 'footer.php'; ?>
