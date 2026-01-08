<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda - JadwalKu</title>
    <link rel="stylesheet" href="../Css/home_page.css">
</head>
<body>
    <header>
        <nav>
            <a href="#" class="logo">JadwalKu</a>

            <div class="hamburger">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>

            <ul class="nav-links">
                <li><a href="#">Beranda</a></li>
                <li><a href="#kontak">Kontak Kami</a></li>
                <li>
                    <a href="login_mahasiswa.php" class="btn-login">Masuk</a>
                </li>
            </ul>
        </nav>
    </header>

    <section class="hero">
        <a href="#" style="text-decoration: none;">
            <div class="hero-image">
                <div class="hero-text">
                    <h1>Kelola Jadwal Kuliahmu dengan Mudah</h1>
                    <p>Tingkatkan Produktivitas Akademik</p>
                </div>
            </div>
        </a>
    </section>

    <section class="contact-section" id="kontak">
        <h2 class="section-title">Kontak Kami</h2>
        
        <div class="contact-container">
            <div class="contact-info">
                <h3>Hubungi Tim Kami</h3>
                <p>Kami sangat menghargai masukan dan pengalaman Anda dalam menggunakan website kami. Silakan tinggalkan pesan Anda, dan tim kami akan merespons secepat mungkin.</p>
                
                <div class="contact-email">
                    <div class="email-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                        </svg>
                    </div>
                    <div>
                        <strong>Email Kami:</strong><br>
                        infojadwalku@gmail.com
                    </div>
                </div>
            </div>

            <div class="contact-form">
                <h3>Tinggalkan Pesan</h3>
                <form id="contactForm">
                    <div class="form-group">
                        <label for="name">Nama Lengkap</label>
                        <input type="text" id="name" name="name" required placeholder="Masukkan nama lengkap Anda">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required placeholder="Masukkan email Anda">
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Pesan / Pengalaman</label>
                        <textarea id="message" name="message" required placeholder="Ceritakan pengalaman Anda menggunakan website kami..."></textarea>
                    </div>
                    
                    <button type="submit" class="btn-submit">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                        </svg>
                        Kirim Pesan
                    </button>
                </form>
            </div>
        </div>
    </section>

    <footer>
        <p>&copy; 2025 JadwalKu. Dikembangkan oleh Technova.</p>
    </footer>

    <script src="../Js/home_page.js"></script>
</body>
</html>