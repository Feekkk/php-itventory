<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RCMP-itventory</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/home.css">
    <script src="js/home.js" defer></script>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo">RCMP-itventory</div>
            <div class="nav-links">
                <a href="#">Browse Equipment</a>
                <a href="#services">Services Provided</a>
                <a href="#">Help</a>
                <a class="sign-in" href="#">Sign In</a>
            </div>
        </nav>
    </header>

    <main>
        <section class="hero">
            <div class="hero-content">
                <h1>Find the tech you need.<span>Instantly.</span></h1>
                <p>Reserve devices, check availability, and manage all IT assets in one place.</p>
            </div>
        </section>

        <section class="categories">
            <div class="categories-grid">
                <article class="category-card">
                    <div class="category-icon">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <rect x="3" y="5" width="18" height="11" rx="1.5" stroke="#1fa372" stroke-width="1.5"></rect>
                            <path d="M4 17h16l1 2H3l1-2z" fill="#1fa372" opacity="0.15"></path>
                        </svg>
                    </div>
                    <div class="category-label">Laptops</div>
                </article>
                <article class="category-card">
                    <div class="category-icon">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle cx="7" cy="8" r="3" stroke="#1fa372" stroke-width="1.5"></circle>
                            <circle cx="15" cy="8" r="3" stroke="#1fa372" stroke-width="1.5"></circle>
                            <rect x="4" y="11" width="16" height="7" rx="1.5" stroke="#1fa372" stroke-width="1.5"></rect>
                            <path d="M17.5 12.5l3.5 2.5-3.5 2.5v-5z" fill="#1fa372" opacity="0.3"></path>
                        </svg>
                    </div>
                    <div class="category-label">Projectors</div>
                </article>
                <article class="category-card">
                    <div class="category-icon">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M4 13v3.5a2 2 0 002 2h1.5v-7H6a2 2 0 00-2 2z" stroke="#1fa372" stroke-width="1.5" fill="#1fa372" opacity="0.2"></path>
                            <path d="M20 13v3.5a2 2 0 01-2 2h-1.5v-7H18a2 2 0 012 2z" stroke="#1fa372" stroke-width="1.5" fill="#1fa372" opacity="0.2"></path>
                            <path d="M6 12a6 6 0 0112 0" stroke="#1fa372" stroke-width="1.5" stroke-linecap="round"></path>
                        </svg>
                    </div>
                    <div class="category-label">Audio/Visual</div>
                </article>
                <article class="category-card">
                    <div class="category-icon">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M9 4v6" stroke="#1fa372" stroke-width="1.5" stroke-linecap="round"></path>
                            <path d="M15 4v6" stroke="#1fa372" stroke-width="1.5" stroke-linecap="round"></path>
                            <path d="M7 10h10v3a5 5 0 01-10 0v-3z" fill="#1fa372" opacity="0.2" stroke="#1fa372" stroke-width="1.5"></path>
                            <path d="M12 17v3" stroke="#1fa372" stroke-width="1.5" stroke-linecap="round"></path>
                        </svg>
                    </div>
                    <div class="category-label">Cables &amp; Adapters</div>
                </article>
                <article class="category-card">
                    <div class="category-icon">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle cx="12" cy="12" r="8" stroke="#1fa372" stroke-width="1.5"></circle>
                            <path d="M12 4c2 2 3 5 3 8s-1 6-3 8c-2-2-3-5-3-8s1-6 3-8z" stroke="#1fa372" stroke-width="1.5"></path>
                            <path d="M5 9.5h14M5 14.5h14" stroke="#1fa372" stroke-width="1.5" stroke-linecap="round"></path>
                        </svg>
                    </div>
                    <div class="category-label">Networking</div>
                </article>
                <article class="category-card">
                    <div class="category-icon">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M6 8h12a2 2 0 012 2v7H4v-7a2 2 0 012-2z" fill="#1fa372" opacity="0.18" stroke="#1fa372" stroke-width="1.5"></path>
                            <path d="M9 8V6a1 1 0 011-1h4a1 1 0 011 1v2" stroke="#1fa372" stroke-width="1.5" stroke-linecap="round"></path>
                            <path d="M4 11h16" stroke="#1fa372" stroke-width="1.5" stroke-linecap="round"></path>
                        </svg>
                    </div>
                    <div class="category-label">Accessories</div>
                </article>
            </div>
        </section>

        <section id="services" class="services">
            <div class="services-header">
                <h2>Services We Provide</h2>
                <p>End-to-end support that keeps your teams productive and connected.</p>
            </div>
            <div class="services-marquee" aria-live="polite">
                <div class="services-track">
                    <article class="service-card">
                        <div class="service-icon">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M5 6h14v12H5z" stroke="#1fa372" stroke-width="1.5" fill="#1fa372" opacity="0.16"></path>
                                <path d="M9 9h6M9 12h6M9 15h3" stroke="#1fa372" stroke-width="1.5" stroke-linecap="round"></path>
                            </svg>
                        </div>
                        <h3>Asset Tracking</h3>
                        <p>Real-time monitoring for every device in your inventory.</p>
                    </article>
                    <article class="service-card">
                        <div class="service-icon">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M4 7h16v11H4z" stroke="#1fa372" stroke-width="1.5" fill="#1fa372" opacity="0.2"></path>
                                <path d="M8 7V5a1 1 0 011-1h6a1 1 0 011 1v2" stroke="#1fa372" stroke-width="1.5"></path>
                                <path d="M7 11h10" stroke="#1fa372" stroke-width="1.5" stroke-linecap="round"></path>
                                <path d="M10 14h4" stroke="#1fa372" stroke-width="1.5" stroke-linecap="round"></path>
                            </svg>
                        </div>
                        <h3>Reservation Workflow</h3>
                        <p>Streamlined requests and approvals for hardware checkout.</p>
                    </article>
                    <article class="service-card">
                        <div class="service-icon">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle cx="8" cy="8" r="3" stroke="#1fa372" stroke-width="1.5"></circle>
                                <circle cx="16" cy="16" r="3" stroke="#1fa372" stroke-width="1.5"></circle>
                                <path d="M13.5 6.5l4 4M6.5 13.5l4 4" stroke="#1fa372" stroke-width="1.5" stroke-linecap="round"></path>
                            </svg>
                        </div>
                        <h3>Lifecycle Planning</h3>
                        <p>Prevent downtime with timely refresh cycles and retirements.</p>
                    </article>
                    <article class="service-card">
                        <div class="service-icon">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <rect x="3" y="6" width="18" height="12" rx="2" stroke="#1fa372" stroke-width="1.5"></rect>
                                <path d="M7 10h10M7 14h5" stroke="#1fa372" stroke-width="1.5" stroke-linecap="round"></path>
                            </svg>
                        </div>
                        <h3>Compliance Reports</h3>
                        <p>Generate audits, certifications, and usage reports instantly.</p>
                    </article>
                    <article class="service-card">
                        <div class="service-icon">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M6 5h12l2 4v10H4V9z" stroke="#1fa372" stroke-width="1.5" fill="#1fa372" opacity="0.18"></path>
                                <path d="M9 15c0-1.657 1.343-3 3-3s3 1.343 3 3-1.343 3-3 3-3-1.343-3-3z" stroke="#1fa372" stroke-width="1.5"></path>
                                <path d="M9 9h6" stroke="#1fa372" stroke-width="1.5" stroke-linecap="round"></path>
                            </svg>
                        </div>
                        <h3>Secure Storage</h3>
                        <p>Controlled access to sensitive equipment and accessories.</p>
                    </article>
                    <article class="service-card">
                        <div class="service-icon">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M4 8l8-4 8 4-8 4-8-4z" stroke="#1fa372" stroke-width="1.5" fill="#1fa372" opacity="0.14"></path>
                                <path d="M4 14l8-4 8 4" stroke="#1fa372" stroke-width="1.5"></path>
                                <path d="M4 18l8-4 8 4" stroke="#1fa372" stroke-width="1.5"></path>
                            </svg>
                        </div>
                        <h3>Knowledge Base</h3>
                        <p>Self-service guides to troubleshoot and stay productive.</p>
                    </article>
                    <!-- Duplicate cards to create seamless loop -->
                    <article class="service-card">
                        <div class="service-icon">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M5 6h14v12H5z" stroke="#1fa372" stroke-width="1.5" fill="#1fa372" opacity="0.16"></path>
                                <path d="M9 9h6M9 12h6M9 15h3" stroke="#1fa372" stroke-width="1.5" stroke-linecap="round"></path>
                            </svg>
                        </div>
                        <h3>Asset Tracking</h3>
                        <p>Real-time monitoring for every device in your inventory.</p>
                    </article>
                    <article class="service-card">
                        <div class="service-icon">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M4 7h16v11H4z" stroke="#1fa372" stroke-width="1.5" fill="#1fa372" opacity="0.2"></path>
                                <path d="M8 7V5a1 1 0 011-1h6a1 1 0 011 1v2" stroke="#1fa372" stroke-width="1.5"></path>
                                <path d="M7 11h10" stroke="#1fa372" stroke-width="1.5" stroke-linecap="round"></path>
                                <path d="M10 14h4" stroke="#1fa372" stroke-width="1.5" stroke-linecap="round"></path>
                            </svg>
                        </div>
                        <h3>Reservation Workflow</h3>
                        <p>Streamlined requests and approvals for hardware checkout.</p>
                    </article>
                    <article class="service-card">
                        <div class="service-icon">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle cx="8" cy="8" r="3" stroke="#1fa372" stroke-width="1.5"></circle>
                                <circle cx="16" cy="16" r="3" stroke="#1fa372" stroke-width="1.5"></circle>
                                <path d="M13.5 6.5l4 4M6.5 13.5l4 4" stroke="#1fa372" stroke-width="1.5" stroke-linecap="round"></path>
                            </svg>
                        </div>
                        <h3>Lifecycle Planning</h3>
                        <p>Prevent downtime with timely refresh cycles and retirements.</p>
                    </article>
                    <article class="service-card">
                        <div class="service-icon">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <rect x="3" y="6" width="18" height="12" rx="2" stroke="#1fa372" stroke-width="1.5"></rect>
                                <path d="M7 10h10M7 14h5" stroke="#1fa372" stroke-width="1.5" stroke-linecap="round"></path>
                            </svg>
                        </div>
                        <h3>Compliance Reports</h3>
                        <p>Generate audits, certifications, and usage reports instantly.</p>
                    </article>
                    <article class="service-card">
                        <div class="service-icon">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M6 5h12l2 4v10H4V9z" stroke="#1fa372" stroke-width="1.5" fill="#1fa372" opacity="0.18"></path>
                                <path d="M9 15c0-1.657 1.343-3 3-3s3 1.343 3 3-1.343 3-3 3-3-1.343-3-3z" stroke="#1fa372" stroke-width="1.5"></path>
                                <path d="M9 9h6" stroke="#1fa372" stroke-width="1.5" stroke-linecap="round"></path>
                            </svg>
                        </div>
                        <h3>Secure Storage</h3>
                        <p>Controlled access to sensitive equipment and accessories.</p>
                    </article>
                    <article class="service-card">
                        <div class="service-icon">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M4 8l8-4 8 4-8 4-8-4z" stroke="#1fa372" stroke-width="1.5" fill="#1fa372" opacity="0.14"></path>
                                <path d="M4 14l8-4 8 4" stroke="#1fa372" stroke-width="1.5"></path>
                                <path d="M4 18l8-4 8 4" stroke="#1fa372" stroke-width="1.5"></path>
                            </svg>
                        </div>
                        <h3>Knowledge Base</h3>
                        <p>Self-service guides to troubleshoot and stay productive.</p>
                    </article>
                </div>
            </div>
        </section>
    </main>
    <?php require __DIR__ . '/component/footer.php'; ?>
</body>
</html>