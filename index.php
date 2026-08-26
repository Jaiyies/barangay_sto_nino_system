<?php
// index.php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Barangay Sto. Niño | Online Services Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; min-height: 100vh; display: flex; flex-direction: column; }

        .top-nav {
            background: white; padding: 0 48px; height: 80px;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08); position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            border-bottom: 1px solid #e2e9f0;
        }
        .logo { display: flex; align-items: center; gap: 14px; }
        .logo img { width: 55px; height: 55px; border-radius: 50%; object-fit: cover; border: 2px solid #8B0000; padding: 3px; background: white; }
        .logo h2 { font-size: 1.3rem; font-weight: 700; color: #0b4127; }
        .logo h2 span { color: #8B0000; }
        .nav-links { display: flex; gap: 32px; align-items: center; }
        .nav-link { text-decoration: none; font-weight: 500; font-size: 0.95rem; color: #1a3b2f; transition: all 0.2s; cursor: pointer; display: flex; align-items: center; gap: 8px; padding: 8px 4px; border-bottom: 2px solid transparent; }
        .nav-link i { font-size: 1rem; color: #2c7a4b; }
        .nav-link:hover { color: #0f5c3a; border-bottom-color: #0f5c3a; }

        .main-container { display: flex; min-height: 100vh; margin-top: 80px; flex: 1; }
        .hero-panel {
            flex: 1.2; background: linear-gradient(135deg, #0d3b26, #1a5d3a);
            display: flex; align-items: center; justify-content: center; padding: 40px;
        }
        .hero-content { max-width: 520px; color: white; animation: fadeInUp 0.6s ease-out; text-shadow: 0 2px 8px rgba(0,0,0,0.2); }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        .hero-content h1 { font-size: 3.2rem; font-weight: 800; line-height: 1.2; margin-bottom: 20px; letter-spacing: -0.02em; }
        .hero-content p { font-size: 1.1rem; line-height: 1.5; opacity: 0.95; margin-bottom: 28px; }
        .feature-badge { display: flex; gap: 16px; margin-top: 28px; flex-wrap: wrap; }
        .feature-badge span { background: rgba(255,255,255,0.15); padding: 8px 20px; border-radius: 60px; font-size: 0.8rem; font-weight: 600; border: 1px solid rgba(255,255,255,0.2); transition: transform 0.2s; }
        .feature-badge span:hover { transform: translateY(-2px); background: rgba(255,255,255,0.25); }

        .cta-panel {
            flex: 0.9; background: rgba(255,255,255,0.95); backdrop-filter: blur(10px);
            display: flex; align-items: center; justify-content: center; padding: 48px 32px;
        }
        .cta-card {
            background: white; border-radius: 48px; padding: 50px 40px; width: 100%; max-width: 460px;
            box-shadow: 0 30px 55px -15px rgba(0,0,0,0.2); text-align: center; border: 1px solid #eef3f0;
        }
        .cta-card .logo-img { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; margin: 0 auto 15px; box-shadow: 0 8px 18px rgba(21,128,61,0.15); border: 3px solid white; display: block; }
        .cta-card h2 { font-size: 1.6rem; font-weight: 700; color: #0b4127; margin-bottom: 8px; }
        .cta-card p { color: #6b7c6f; font-size: 0.95rem; margin-bottom: 30px; }
        .cta-btn {
            display: inline-block; padding: 16px 40px; background: linear-gradient(105deg, #0f5c3a, #1f8a5c);
            color: white; border: none; border-radius: 50px; font-weight: 700; font-size: 1rem;
            text-decoration: none; transition: all 0.3s ease;
        }
        .cta-btn:hover { transform: translateY(-3px); box-shadow: 0 15px 28px -12px #166534; background: linear-gradient(105deg, #0a4a2e, #166534); }
        .cta-card .register-note { margin-top: 22px; font-size: 0.85rem; color: #5b6e64; }
        .cta-card .register-note a { color: #166534; text-decoration: none; font-weight: 700; }
        .cta-card .register-note a:hover { color: #0a4a2e; text-decoration: underline; }

        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 1000; align-items: center; justify-content: center; }
        .modal-card { background: white; max-width: 620px; width: 90%; border-radius: 44px; overflow: hidden; animation: modalPop 0.25s cubic-bezier(0.2,0.9,0.4,1.1); box-shadow: 0 40px 70px rgba(0,0,0,0.3); }
        @keyframes modalPop { from { opacity: 0; transform: scale(0.94); } to { opacity: 1; transform: scale(1); } }
        .modal-header { display: flex; justify-content: space-between; align-items: center; padding: 26px 36px; background: #fafdfb; border-bottom: 1px solid #eef2f0; }
        .modal-header h3 { font-size: 1.6rem; font-weight: 700; color: #1d3d30; }
        .close-modal { font-size: 36px; cursor: pointer; line-height: 1; color: #9bb7ab; transition: color 0.2s; }
        .close-modal:hover { color: #dc2626; }
        .modal-body { padding: 32px 36px 40px; }
        .dev-grid { display: grid; grid-template-columns: repeat(2,1fr); gap: 14px; margin: 20px 0 12px; }
        .dev-item { background: #f1f6f3; padding: 10px 18px; border-radius: 60px; font-weight: 500; font-size: 0.85rem; }
        .contact-line { display: flex; align-items: center; gap: 18px; padding: 14px 0; border-bottom: 1px solid #edf2f0; }

        @media (max-width: 960px) {
            .main-container { flex-direction: column; }
            .hero-panel { min-height: 280px; padding: 30px 20px; text-align: center; }
            .hero-content { max-width: 100%; }
            .hero-content h1 { font-size: 2.5rem; }
            .feature-badge { justify-content: center; }
            .cta-panel { padding: 30px 16px; }
            .cta-card { max-width: 100%; }
            .top-nav { padding: 0 24px; }
        }
        @media (max-width: 768px) {
            .hero-content h1 { font-size: 2rem; }
            .logo img { width: 45px; height: 45px; }
            .logo h2 { font-size: 1rem; }
        }
        @media (max-width: 480px) {
            .top-nav { padding: 0 16px; height: 64px; flex-wrap: wrap; }
            .logo img { width: 36px; height: 36px; }
            .logo h2 { font-size: 0.85rem; }
            .hero-content h1 { font-size: 1.8rem; }
            .hero-content p { font-size: 0.9rem; }
            .feature-badge span { font-size: 0.65rem; padding: 4px 14px; }
            .cta-card { padding: 30px 20px; border-radius: 32px; }
            .cta-card h2 { font-size: 1.3rem; }
            .dev-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <nav class="top-nav">
        <div class="logo">
            <img src="images/logo.jpg" alt="Barangay Sto. Niño Logo">
            <h2><span>Sto.</span> Niño</h2>
        </div>
        <div class="nav-links">
            <a class="nav-link" id="aboutNavBtn"><i class="fas fa-info-circle"></i> About</a>
            <a class="nav-link" id="contactNavBtn"><i class="fas fa-headset"></i> Contact</a>
        </div>
    </nav>

    <div class="main-container">
        <div class="hero-panel">
            <div class="hero-content">
                <h1>Online Services<br>Simplified</h1>
                <p>Digital, transparent, and hassle-free document processing and event permit management for the residents of Barangay Sto. Niño, Parañaque City.</p>
                <div class="feature-badge">
                    <span><i class="fas fa-check-circle"></i> Real-time tracking</span>
                    <span><i class="fas fa-shield-alt"></i> Secure processing</span>
                    <span><i class="fas fa-envelope"></i> Email notifications</span>
                </div>
            </div>
        </div>

        <div class="cta-panel">
            <div class="cta-card">
                <img src="images/logo.jpg" alt="Logo" class="logo-img">
                <h2>Welcome to Barangay Sto. Niño</h2>
                <p>Sign in to access online services or create an account to get started.</p>
                <a href="auth/login.php" class="cta-btn">
                    <i class="fas fa-sign-in-alt"></i> Go to Login
                </a>
                <div class="register-note">
                    New to the barangay? <a href="auth/register.php">Create an account →</a>
                </div>
            </div>
        </div>
    </div>

    <div id="aboutModal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3><i class="fas fa-leaf" style="color: #166534;"></i> About the System</h3>
                <span class="close-modal" id="closeAboutBtn">&times;</span>
            </div>
            <div class="modal-body">
                <p style="margin-bottom: 18px;"><strong>Barangay Sto. Niño Online Services</strong> is a digital platform designed to streamline the end-to-end process of requesting documents, reviewing applications, and managing event permits in Barangay Sto. Niño, Parañaque City.</p>
                <p>Our mission is to empower residents with an efficient, paperless, and transparent way to secure barangay documents and permits while helping barangay administrators track and respond to requests in real time.</p>
                <div style="margin: 28px 0 12px;">
                    <p><strong><i class="fas fa-graduation-cap"></i> Capstone Project Team (BSIT)</strong></p>
                    <div class="dev-grid">
                        <div class="dev-item">Alcantara, Angelica Ann T.</div>
                        <div class="dev-item">Goyon, Jireh B.</div>
                        <div class="dev-item">Oliva, Kristine Joy I.</div>
                        <div class="dev-item">Sanchez, Jenica C.</div>
                    </div>
                    <p style="font-size: 0.8rem; color: #506e60; margin-top: 16px;">Polytechnic University of the Philippines - Parañaque Campus</p>
                </div>
            </div>
        </div>
    </div>

    <div id="contactModal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3><i class="fas fa-address-card"></i> Contact & Support</h3>
                <span class="close-modal" id="closeContactBtn">&times;</span>
            </div>
            <div class="modal-body">
                <p style="margin-bottom: 18px;">Reach out to the Barangay Sto. Niño Hall for any inquiries regarding document requests, permit applications, technical support, or general assistance.</p>
                <div class="contact-line"><i class="fas fa-map-pin" style="width: 30px; color:#166534;"></i> <span>Barangay Sto. Niño Barangay Hall, Parañaque City, Metro Manila</span></div>
                <div class="contact-line"><i class="fas fa-phone-alt" style="width: 30px; color:#166534;"></i> <span>(02) 8532 1470</span></div>
                <div class="contact-line"><i class="fas fa-mobile-alt" style="width: 30px; color:#166534;"></i> <span>0968 542 1132 (Globe)</span></div>
                <div class="contact-line"><i class="fas fa-envelope" style="width: 30px; color:#166534;"></i> <span>barangay.stonino.paranaque@gmail.com</span></div>
                <div class="contact-line"><i class="fas fa-clock" style="width: 30px; color:#166534;"></i> <span>Mon - Fri: 8:00 AM – 5:00 PM</span></div>
                <div style="margin-top: 28px; background:#F4F9F6; padding: 14px; border-radius: 34px; text-align:center; font-size:0.8rem;">
                    <i class="fas fa-life-ring"></i> For system issues, email: <strong>barangay.stonino.paranaque@gmail.com</strong>
                </div>
            </div>
        </div>
    </div>

    <script>
        const aboutBtn = document.getElementById('aboutNavBtn');
        const contactBtn = document.getElementById('contactNavBtn');
        const aboutModal = document.getElementById('aboutModal');
        const contactModal = document.getElementById('contactModal');
        const closeAbout = document.getElementById('closeAboutBtn');
        const closeContact = document.getElementById('closeContactBtn');

        function openModal(modal) { if (modal) modal.style.display = 'flex'; document.body.style.overflow = 'hidden'; }
        function closeModal(modal) { if (modal) modal.style.display = 'none'; document.body.style.overflow = ''; }

        window.addEventListener('click', (e) => {
            if (e.target === aboutModal) closeModal(aboutModal);
            if (e.target === contactModal) closeModal(contactModal);
        });

        aboutBtn?.addEventListener('click', () => openModal(aboutModal));
        contactBtn?.addEventListener('click', () => openModal(contactModal));
        closeAbout?.addEventListener('click', () => closeModal(aboutModal));
        closeContact?.addEventListener('click', () => closeModal(contactModal));
    </script>
</body>
</html>