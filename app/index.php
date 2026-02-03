<?php
require 'config.php';
require 'header.php';
?>

<style>
.card-tour {
    transition: .35s ease;
}

.card-tour:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 28px rgba(0,0,0,.14);
}

.card-tour img {
    transition: .4s ease;
}

.card-tour:hover img {
    transform: scale(1.07);
}
</style>

<!-- PROFIL PERUSAHAAN -->
<div class="container mt-5">
    <h2 class="fw-bold text-center mb-4" data-aos="fade-down">Profil Perusahaan</h2>

    <div class="p-4 bg-light rounded shadow-sm" data-aos="fade-up">
        <p style="font-size: 1.05rem; line-height: 1.7;">
            <b>Explore Bandung</b> adalah perusahaan penyedia layanan perjalanan wisata 
            profesional yang berfokus pada pengembangan pariwisata di wilayah Bandung Raya. 
            Perusahaan ini hadir untuk memberikan pengalaman liburan terbaik bagi wisatawan 
            dengan mengutamakan kenyamanan, keamanan, edukasi, serta keindahan alam Bandung 
            yang menawan.
        </p>
        <p style="font-size: 1.05rem; line-height: 1.7;">
            Kami menyediakan berbagai paket wisata yang telah dikurasi secara profesional, 
            mulai dari wisata alam, wisata edukasi, hingga pengalaman lokal seperti kegiatan 
            budaya, camping, eksplorasi pegunungan, hingga pertunjukan dan kuliner nusantara. 
            Tim kami terdiri dari pemandu wisata berpengalaman yang memahami setiap sudut 
            keindahan Lembang, Ciwidey, dan Pangalengan.
        </p>
        <p style="font-size: 1.05rem; line-height: 1.7;">
            Explore Bandung berkomitmen menjadi mitra perjalanan wisata terbaik bagi masyarakat 
            lokal maupun wisatawan nasional, serta berperan aktif dalam membantu mempromosikan 
            potensi wisata Bandung kepada publik luas melalui layanan tur berkualitas, dokumentasi, 
            dan publikasi digital.
        </p>
    </div>
</div>

<!-- VISI MISI -->
<div class="container py-5">
    <div class="row g-4">

        <div class="col-lg-6" data-aos="fade-right">
            <div class="p-4 bg-light rounded shadow-sm">
                <h4 class="fw-bold mb-2">Visi</h4>
                <p>
                    Menjadi penyedia layanan wisata Bandung terbaik yang mengedepankan
                    kenyamanan, keamanan, edukasi, dan pengalaman tak terlupakan bagi wisatawan.
                </p>
            </div>
        </div>

        <div class="col-lg-6" data-aos="fade-left">
            <div class="p-4 bg-light rounded shadow-sm">
                <h4 class="fw-bold mb-2">Misi</h4>
                <ul>
                    <li>Mengutamakan pelayanan terbaik kepada seluruh wisatawan</li>
                    <li>Mempromosikan potensi wisata di seluruh Bandung Raya</li>
                    <li>Menyediakan paket wisata lengkap dan profesional</li>
                </ul>
            </div>
        </div>

    </div>
</div>

<!-- PILIHAN WISATA -->
<div class="container pb-5">
    <h2 class="fw-bold text-center mb-4" data-aos="fade-down">Pilihan Paket Wisata</h2>

    <div class="row g-4">
        
        <!-- LEMBANG -->
        <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
            <div class="card card-tour shadow-sm border-0">
                <img src="images/lembang.jpg" class="img-fluid rounded-top">
                <div class="p-3">
                    <h5 class="fw-bold">Wisata Lembang</h5>
                    <p class="small text-muted">Paket Sunrise & Farm House</p>
                    <a href="detail.php?slug=paket-sunrise-lembang" class="btn btn-primary w-100">
                        Lihat Detail
                    </a>
                </div>
            </div>
        </div>

        <!-- CIWIDEY -->
        <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
            <div class="card card-tour shadow-sm border-0">
                <img src="images/ciwidey.jpeg" class="img-fluid rounded-top">
                <div class="p-3">
                    <h5 class="fw-bold">Wisata Ciwidey</h5>
                    <p class="small text-muted">Kawah Putih & Perkebunan</p>
                    <a href="detail.php?slug=paket-ciwidey" class="btn btn-primary w-100">
                        Lihat Detail
                    </a>
                </div>
            </div>
        </div>

        <!-- PANGALENGAN -->
        <div class="col-lg-4" data-aos="fade-up" data-aos-delay="300">
            <div class="card card-tour shadow-sm border-0">
                <img src="images/pangalengan.jpg" class="img-fluid rounded-top">
                <div class="p-3">
                    <h5 class="fw-bold">Wisata Pangalengan</h5>
                    <p class="small text-muted">Camping & Danau Cileunca</p>
                    <a href="detail.php?slug=paket-pangalengan" class="btn btn-primary w-100">
                        Lihat Detail
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- AOS INIT -->
<link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
AOS.init({
    duration: 900,
    once: true
});
</script>

<?php require 'footer.php'; ?>
