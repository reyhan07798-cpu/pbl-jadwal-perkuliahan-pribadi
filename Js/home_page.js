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