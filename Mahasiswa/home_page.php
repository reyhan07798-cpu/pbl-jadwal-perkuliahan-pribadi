<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda - JadwalKu</title>
    <style>
        /* --- Reset & Base Styles --- */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f7f6;
            overflow-x: hidden;
        }

        /* --- Header & Navigation --- */
        header {
            background: linear-gradient(135deg, #1a4d80, #163a66);
            padding: 1rem 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            position: sticky;
            top: 0;
            z-index: 1000;
            width: 100%;
        }

        nav {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
            position: relative;
        }

        .logo {
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
            text-decoration: none;
            z-index: 1001;
        }

        /* Desktop Navigation */
        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
            list-style: none;
        }

        .nav-links li a {
            color: white;
            text-decoration: none;
            font-size: 1rem;
            transition: all 0.3s;
            font-weight: 500;
            position: relative;
        }

        .nav-links li a:not(.btn-login):hover {
            opacity: 0.8;
        }
        
       
        .nav-links li a:not(.btn-login)::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -5px;
            left: 0;
            background-color: white;
            transition: width 0.3s;
        }
        .nav-links li a:not(.btn-login):hover::after {
            width: 100%;
        }

        .btn-login {
            background-color: white;
            color: #1a4d80 !important;
            padding: 0.6rem 2rem; 
            border-radius: 50px; 
            font-weight: 700;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2); 
            display: inline-block;
            border: 1px solid rgba(255,255,255,0.2);
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            background-color: #f8f9fa;
            transform: translateY(-3px); 
            box-shadow: 0 6px 15px rgba(0,0,0,0.25);
            opacity: 1 !important;
        }

        .btn-login::after {
            display: none; 
        }

        /* Hamburger Menu  */
        .hamburger {
            display: none;
            cursor: pointer;
            z-index: 1001;
        }

        .bar {
            display: block;
            width: 25px;
            height: 3px;
            margin: 5px auto;
            transition: all 0.3s ease-in-out;
            background-color: white;
        }

        /* --- Hero Section --- */
        .hero {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }

        .hero-image {
            width: 100%;
            min-height: 350px; 
            height: 50vh; 
            background: linear-gradient(135deg, #4682B4, #1a4d80);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 8, 184, 0.2);
            position: relative;
            overflow: hidden;
            text-decoration: none;
        }

        .hero-image::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background-image: radial-gradient(rgba(255,255,255,0.2) 1px, transparent 1px);
            background-size: 20px 20px;
            opacity: 0.3;
        }

        .hero-text {
            position: relative;
            z-index: 1;
            color: white;
            text-align: center;
            padding: 1rem;
            width: 100%;
        }

        .hero-text h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            line-height: 1.2;
        }

        .hero-text p {
            font-size: 1.1rem;
            opacity: 0.95;
        }

        /* --- Contact Section --- */
        .contact-section {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }

        .section-title {
            text-align: center;
            color: #1a4d80;
            font-size: 2rem;
            margin-bottom: 2rem;
        }

        .contact-container {
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            gap: 2rem;
            align-items: start;
        }

        .contact-info, .contact-form {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #eee;
        }

        .contact-info h3, .contact-form h3 {
            color: #1a4d80;
            margin-bottom: 1rem;
            font-size: 1.4rem;
        }

        .contact-info p {
            color: #555;
            margin-bottom: 1.5rem;
            line-height: 1.7;
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
            background: #4682B4;
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

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.9rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
            font-family: inherit;
            background-color: #fcfcfc;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4682B4;
            background-color: #fff;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .btn-submit {
            background: #4682B4;
            color: white;
            padding: 0.9rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: background 0.3s, transform 0.2s, box-shadow 0.3s;
            width: 100%;
        }

        @media(min-width: 768px) {
            .btn-submit {
                width: auto;
            }
        }

        .btn-submit:hover {
            background: #1a4d80;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(26, 77, 128, 0.3);
        }

        .btn-submit svg {
            width: 20px;
            height: 20px;
            fill: white;
        }

        footer {
            background: #1a4d80;
            color: white;
            text-align: center;
            padding: 2rem 1rem;
            margin-top: 4rem;
            font-size: 0.9rem;
        }

        /* --- RESPONSIVE MEDIA QUERIES --- */
        @media (max-width: 900px) {
            .hero-text h1 {
                font-size: 2rem;
            }
            .contact-container {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            /* Hamburger Menu Styles */
            .hamburger {
                display: block;
            }

            .hamburger.active .bar:nth-child(2) {
                opacity: 0;
            }
            .hamburger.active .bar:nth-child(1) {
                transform: translateY(8px) rotate(45deg);
            }
            .hamburger.active .bar:nth-child(3) {
                transform: translateY(-8px) rotate(-45deg);
            }

            .nav-links {
                position: fixed;
                left: -100%;
                top: 70px; 
                gap: 0;
                flex-direction: column;
                background-color: #1a4d80;
                width: 100%;
                height: calc(100vh - 70px);
                text-align: center;
                transition: 0.3s;
                box-shadow: 0 10px 10px rgba(0,0,0,0.1);
                padding-top: 2rem;
            }

            .nav-links.active {
                left: 0;
            }

            .nav-links li {
                margin: 1.5rem 0;
            }

            .nav-links li a {
                font-size: 1.2rem;
                display: block;
                padding: 0.8rem;
            }
            
            /* Login Button di Mobile */
            .nav-links .btn-login {
                width: auto; 
                min-width: 180px; 
                display: inline-block;
                margin-top: 1rem;
            }

            .hero {
                margin-top: 1rem;
                padding: 0 1rem;
            }

            .hero-text h1 {
                font-size: 1.8rem;
            }

            .contact-info, .contact-form {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <nav>
            <!-- Logo -->
            <a href="#" class="logo">JadwalKu</a>

            <!-- Hamburger Menu Icon -->
            <div class="hamburger">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>

            <!-- Navigation Links -->
            <ul class="nav-links">
                <li><a href="#">Beranda</a></li>
                <li><a href="#kontak">Kontak Kami</a></li>
                <li>
                    <a href="login_mahasiswa.php" class="btn-login">Login</a>
                </li>
            </ul>
        </nav>
    </header>

    <!-- Hero Section -->
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

    <!-- Footer -->
    <footer>
        <p>&copy; 2025 JadwalKu. Dikembangkan oleh Technova.</p>
    </footer>

    <!-- Scripts -->
    <script>
        // 1. Mobile Menu Toggle
        const hamburger = document.querySelector(".hamburger");
        const navMenu = document.querySelector(".nav-links");
        const navLinks = document.querySelectorAll(".nav-links li a");

        hamburger.addEventListener("click", () => {
            hamburger.classList.toggle("active");
            navMenu.classList.toggle("active");
        });

        navLinks.forEach(n => n.addEventListener("click", () => {
            hamburger.classList.remove("active");
            navMenu.classList.remove("active");
        }));

        // 2. Form Submission
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault(); 
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const message = document.getElementById('message').value;

            const btn = this.querySelector('button[type="submit"]');
            const originalBtnContent = btn.innerHTML;
            btn.innerHTML = 'Mengirim...';
            btn.disabled = true;
            btn.style.opacity = '0.7';

            fetch('kirim_pesan.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name, email, message })
            })
            .then(response => {
                if (!response.ok) throw new Error("Network response was not ok");
                return response.json();
            })
            .then(data => {
                alert('Terima kasih, ' + name + '!\n\nPesan Anda telah kami terima (Simulasi Sukses).');
                document.getElementById('contactForm').reset(); 
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Mohon maaf, ini adalah halaman demo HTML statis.\n\nData berikut akan dikirim jika backend PHP aktif:\nNama: ' + name + '\nEmail: ' + email + '\nPesan: ' + message);
            })
            .finally(() => {
                btn.innerHTML = originalBtnContent;
                btn.disabled = false;
                btn.style.opacity = '1';
            });
        });

        // 3. Smooth Scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    </script>
</body>
</html>