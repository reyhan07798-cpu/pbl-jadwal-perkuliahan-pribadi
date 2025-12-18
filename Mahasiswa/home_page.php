<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda - Website Kami</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        /* Header Navigation */
        header {
            background: linear-gradient(135deg, #0008b8 0%, #0006a0 100%);
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        nav {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
        }

        .logo {
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-size: 1rem;
            transition: opacity 0.3s;
        }

        .nav-links a:hover {
            opacity: 0.8;
        }

        .btn-login {
            background: white;
            color: #0008b8;
            padding: 0.6rem 1.5rem;
            border-radius: 25px;
            border: none;
            cursor: pointer;
            font-weight: bold;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 255, 255, 0.3);
        }

        /* Hero Section */
        .hero {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 0 2rem;
        }

        .hero-image {
            width: 100%;
            height: 400px;
            background: linear-gradient(135deg, #0008b8 0%, #4a5aff 100%);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 8, 184, 0.3);
            position: relative;
            overflow: hidden;
        }

        .hero-image::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: url('#') center/cover;
            opacity: 0.2;
        }

        .hero-text {
            position: relative;
            z-index: 1;
            color: white;
            text-align: center;
            padding: 2rem;
        }

        .hero-text h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .hero-text p {
            font-size: 1.2rem;
            opacity: 0.95;
        }

        /* Contact Developer Section */
        .developer-section {
            max-width: 1200px;
            margin: 4rem auto;
            padding: 0 2rem;
        }

        .section-title {
            text-align: center;
            color: #0008b8;
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .section-subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 3rem;
        }

        .developer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .developer-card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 8, 184, 0.1);
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .developer-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 8, 184, 0.2);
        }

        .developer-icon {
            width: 60px;
            height: 60px;
            background: #0008b8;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }

        .developer-icon svg {
            width: 30px;
            height: 30px;
            fill: white;
        }

        .developer-name {
            font-weight: bold;
            color: #0008b8;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }

        .developer-email {
            color: #666;
            font-size: 0.9rem;
            word-break: break-all;
        }

        /* Contact Form Section */
        .contact-section {
            max-width: 1200px;
            margin: 4rem auto;
            padding: 0 2rem;
            background: #f8f9fa;
            padding: 3rem 2rem;
            border-radius: 15px;
        }

        .contact-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: start;
        }

        .contact-info {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 8, 184, 0.1);
        }

        .contact-info h3 {
            color: #0008b8;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        .contact-info p {
            color: #666;
            margin-bottom: 1.5rem;
            line-height: 1.8;
        }

        .contact-email {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: #f0f2ff;
            padding: 1rem;
            border-radius: 8px;
        }

        .email-icon {
            width: 40px;
            height: 40px;
            background: #0008b8;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .email-icon svg {
            width: 20px;
            height: 20px;
            fill: white;
        }

        .contact-form {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 8, 184, 0.1);
        }

        .contact-form h3 {
            color: #0008b8;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #0008b8;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .btn-submit {
            background: #0008b8;
            color: white;
            padding: 0.8rem 2rem;
            border: none;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: background 0.3s, transform 0.3s;
        }

        .btn-submit:hover {
            background: #0006a0;
            transform: translateY(-2px);
        }

        .btn-submit svg {
            width: 20px;
            height: 20px;
            fill: white;
        }

        /* Disclaimer */
        .disclaimer {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 1.5rem 2rem;
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            border-radius: 8px;
        }

        .disclaimer strong {
            color: #856404;
        }

        .disclaimer p {
            color: #856404;
            margin-top: 0.5rem;
        }

        /* Footer */
        footer {
            background: #0008b8;
            color: white;
            text-align: center;
            padding: 2rem;
            margin-top: 4rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav-links {
                gap: 1rem;
            }

            .hero-text h1 {
                font-size: 2rem;
            }

            .contact-container {
                grid-template-columns: 1fr;
            }

            .developer-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php
    // PHP variables
    $siteName = "Website Kami";
    $developers = [
        ["name" => "Pengembang 1", "email" => "developer1@email.com"],
        ["name" => "Pengembang 2", "email" => "developer2@email.com"],
        ["name" => "Pengembang 3", "email" => "developer3@email.com"],
        ["name" => "Pengembang 4", "email" => "developer4@email.com"]
    ];
    $contactEmail = "info@websitekami.com";
    ?>

    <!-- Header -->
    <header>
        <nav>
            <div class="logo"><?php echo $siteName; ?></div>
            <div class="nav-links">
                <a href="#">Beranda</a>
                <a href="#kontak">Kontak Kami</a>
                <a href="#"><button class="btn-login">Login</button></a>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <a href="#" style="text-decoration: none;">
            <div class="hero-image">
                <div class="hero-text">
                    <h1>Selamat Datang di Website Kami</h1>
                    <p>Solusi terpercaya untuk kebutuhan digital Anda</p>
                </div>
            </div>
        </a>
    </section>

    <!-- Developer Contact Section -->
    <section class="developer-section">
        <h2 class="section-title">Kontak Pengembang</h2>
        <p class="section-subtitle">Tim pengembang kami siap membantu Anda</p>
        
        <div class="developer-grid">
            <?php foreach($developers as $dev): ?>
            <div class="developer-card">
                <div class="developer-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                    </svg>
                </div>
                <div class="developer-name"><?php echo $dev['name']; ?></div>
                <div class="developer-email"><?php echo $dev['email']; ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Contact Form Section -->
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
                        <?php echo $contactEmail; ?>
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

    <!-- Disclaimer -->
    <div class="disclaimer">
        <strong>Disclaimer:</strong>
        <p>Website ini dikembangkan untuk tujuan pembelajaran dan pengembangan keterampilan. Informasi yang disajikan dapat berubah sewaktu-waktu. Untuk informasi lebih lanjut, silakan hubungi tim pengembang kami.</p>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; <?php echo date('Y'); ?> <?php echo $siteName; ?>. Dikembangkan oleh Tim Mahasiswa.</p>
    </footer>

    <script>
        // Form submission handling
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const message = document.getElementById('message').value;
            
            // Simulasi pengiriman pesan
            alert('Terima kasih ' + name + '!\n\nPesan Anda telah berhasil dikirim. Tim kami akan segera menghubungi Anda melalui email: ' + email);
            
            // Reset form
            this.reset();
        });

        // Smooth scrolling untuk anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href !== '#' && href !== '#kontak') return;
                
                if (href === '#kontak') {
                    e.preventDefault();
                    document.querySelector(href).scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
</body>
</html>