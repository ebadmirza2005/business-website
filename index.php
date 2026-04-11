<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Faaz Pro Tech</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/x-icon" href="assets/favicon.png">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
    <header>
        <a href="#home" class="brand" aria-label="Fast Pro Tech Home">
            <img class="logo" src="assets/logo.png" alt="Faaz Pro Tech logo" width="350" height="300" loading="eager" fetchpriority="high" decoding="async">
        </a>

        <button class="menu-toggle" id="menuToggle" aria-label="Toggle navigation" aria-expanded="false"
            aria-controls="primaryNav">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <nav class="nav-menu" id="primaryNav" aria-label="Primary navigation">
            <a href="#home">Home</a>
            <a href="#service">Services</a>
            <a href="#about">About</a>
            <a href="#contact">Contact</a>
            <button class="btn btn-ghost nav-auth-btn" id="openAuthMenu" type="button">Login / Signup</button>
        </nav>
    </header>

    <main>
        <section class="section-home" id="home">
            <canvas id="web"></canvas>
            <div class="profile">
                <img src="assets/brain.png" alt="Profile image of Faaz Pro Tech founder" width="400" height="400" loading="eager"  fetchpriority="high" decoding="async">
                <h2>
                    <span>F</span>aaz
                    <span>P</span>ro
                    <span>T</span>ech
                </h2>
            </div>
            <div class="detail-section">
                <span>
                    <p>
                        We provide expert web and app development, creative design, and result-driven
                         digital marketing solutions. Alongside this, we offer reliable data 
                         processing services including data entry, data management, 
                         and analysis with high accuracy. Our goal is to deliver complete digital and
                          data solutions that help businesses grow efficiently.
                    </p>
                </span>
                <span>
                    <p class="choose">Why Choose Us?</p>
                </span>
                <span>
                    <ul>
                        <li>All-in-one services: development, design, marketing & data processing</li>
                        <li>High accuracy and secure data handling</li>
                        <li>Fast delivery with scalable and modern solutions</li>
                        <li>Dedicated support focused on client success</li>
                    </ul>
                </span>
                <span>
                    <div class="hero-actions">
                        <a href="#contact" class="btn btn-primary">Book Consultation</a>
                        <a href="#service" class="btn btn-ghost">Explore Services</a>
                    </div>
                </span>
            </div>
        </section>

        <section class="section-service-bpo">
            <h2 id="service"><span>Our</span> <span>BPO</span> <span>Services</span></h2>
            <div class="service-card">
                <div class="card">
                    <!-- <div class="lines"></div> -->
                    <div class="imgBx">
                        <!-- <img src="assets/data-collection.png" alt="Data collection service" loading="lazy" decoding="async"> -->
                    </div>
                    <div class="content">
                        <div class="details">
                            <h3>Data Collection</h3>
                            <p>Gather accurate and reliable data from multiple sources for better decision-making.</p>
                        </div>
                    </div>
                </div>
                    <div class="card">
                        <!-- <div class="lines"></div> -->
                        <div class="imgBx">
                            <!-- <img src="assets/list-building.png" alt="Prospect list building service" loading="lazy" decoding="async"> -->
                        </div>
                        <div class="content">
                            <div class="details">
                                <h3>Prospect List Building</h3>
                                <p>Create targeted contact lists or datasets tailored to your business needs.</p>
                            </div>
                        </div>
                
                    </div>

                    <div class="card">
                        <!-- <div class="lines"></div> -->
                        <div class="imgBx">
                            <!-- <img src="assets/data-research.png" alt="Data research service" loading="lazy" decoding="async"> -->
                        </div>
                        <div class="content">
                            <div class="details">
                                <h3>Data Research</h3>
                        <p>Analyze trends, patterns, and insights to support strategic planning.</p>
                            </div>
                        </div>
                        
                    </div>

                    <div class="card">
                        <!-- <div class="lines"></div> -->
                        <div class="imgBx">
                            <!-- <img src="assets/lead-generation.png" alt="Cold lead generation service" loading="lazy" decoding="async"> -->
                        </div>
                        <div class="content">
                            <div class="details">
                                <h3>Cold Lead Generation</h3>
                                <p>Identify and reach potential clients to grow your sales pipeline effectively.</p>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <!-- <div class="lines"></div> -->
                        <div class="imgBx">
                            <!-- <img src="assets/data-entry.png" alt="Advanced data entry service" loading="lazy" decoding="async"> -->
                        </div>
                        <div class="content">
                            <div class="details">
                                <h3>Data Entry (Advanced)</h3>
                                <p>Advanced data entry ensures accurate management of large datasets.</p>
                            </div>
                        </div>
                    </div>

                     <div class="card">
                        <!-- <div class="lines"></div> -->
                        <div class="imgBx">
                            <!-- <img src="assets/pdf-to-excel.png" alt="PDF to Excel conversion service" loading="lazy" decoding="async"> -->
                        </div>
                        <div class="content">
                            <div class="details">
                                <h3>PDF to Excel</h3>
                                <p>Convert PDF data into Excel sheets quickly and accurately for easy analysis.</p>
                            </div>
                        </div>
                    </div>

                    <div class="card hidden">
                        <!-- <div class="lines"></div> -->
                        <div class="imgBx">
                            <!-- <img src="assets/cold-calling.png" alt="Cold calling service" loading="lazy" decoding="async"> -->
                        </div>
                        <div class="content">
                            <div class="details">
                                <h3>Cold Calling</h3>
                                <p>Engage prospects directly to introduce your products and generate leads efficiently.</p>
                            </div>
                        </div>
                    </div>

                    <div class="card hidden">
                        <!-- <div class="lines"></div> -->
                        <div class="imgBx">
                            <!-- <img src="assets/data-scraping.png" alt="Data scraping service" loading="lazy" decoding="async"> -->
                        </div>
                        <div class="content">
                            <div class="details">
                                <h3>Data Scraping</h3>
                                <p>Extract structured data from websites efficiently and ethically.</p>
                            </div>
                        </div>
                    </div>

                    <div class="card hidden">
                        <!-- <div class="lines"></div> -->
                        <div class="imgBx">
                            <!-- <img src="assets/data-visualization.png" alt="Data visualization service" loading="lazy" decoding="async"> -->
                        </div>
                        <div class="content">
                            <div class="details">
                                 <h3>Data Visualization</h3>
                        <p>Transform raw data into clear charts, graphs, and dashboards.</p>
                            </div>
                        </div>
                    </div>

                     <div class="card hidden">
                        <!-- <div class="lines"></div> -->
                        <div class="imgBx">
                            <!-- <img src="assets/crm-tools.png" alt="CRM tools" loading="lazy" decoding="async"> -->
                        </div>
                        <div class="content">
                            <div class="details">
                                <h3>CRM Tools</h3>
                                <p>Manage customer relationships efficiently and streamline workflows.</p>
                            </div>
                        </div>
                    </div>

                    
                    <div class="card hidden">
                        <!-- <div class="lines"></div> -->
                        <div class="imgBx">
                            <!-- <img src="assets/data-extraction.png" alt="Data extraction tools" loading="lazy" decoding="async"> -->
                        </div>
                        <div class="content">
                            <div class="details">
                                <h3>Data Extraction Tools</h3>
                                <p>Quickly gather and organize data from multiple sources for analysis and insights.</p>
                            </div>
                        </div>
                    </div>

                    <div class="card hidden">
                        <!-- <div class="lines"></div>` -->
                        <div class="imgBx">
                            <!-- <img src="assets/high-volume-data-entry.png" alt="High volume data entry service" loading="lazy" decoding="async"> --> 
                        </div>
                        <div class="content">
                            <div class="details">
                                <h3>High Volume Data Entry</h3>
                                <p>Accurately input large amounts of data efficiently while maintaining quality and consistency.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="card hidden">
                        <!-- <div class="lines"></div> -->
                        <div class="imgBx">
                            <!-- <img src="assets/ms-excel-advanced.png" alt="MS Excel (Advanced)" loading="lazy" decoding="async"> -->
                        </div>
                        <div class="content">
                            <div class="details">
                                <h3>MS Excel (Advanced)</h3>
                                <p>Analyze, visualize, and manage data using advanced Excel functions and tools efficiently.</p>
                            </div>
                        </div>
                    </div>

                    <div class="card hidden">
                        <!-- <div class="lines"></div> -->
                        <div class="imgBx">
                            <!-- <img src="assets/google-workspace.png" alt="Google Workspace" loading="lazy" decoding="async"> -->
                        </div>
                        <div class="content">
                            <div class="details">
                                <h3>Google Workspace</h3>
                                <p>Collaborate and manage work efficiently using Google's suite of productivity tools.</p>
                            </div>
                        </div>
                    </div>

                    <div class="card hidden">
                        <!-- <div class="lines"></div> -->
                        <div class="imgBx">
                            <!-- <img src="assets/tools-and-technology.png" alt="Tools and Technologies" loading="lazy" decoding="async"> -->
                        </div>
                        <div class="content">
                            <div class="details">
                                <h3>Tools and Technologies</h3>
                                <p>Utilize modern tools and technologies to streamline data processes.</p>
                            </div>
                        </div>
                    </div>
            </div>

            <button id="seeMoreBtn">See More</button>
        </section>

        <section class="section-service-digital">
                <h2><span>Our</span> <span>Digital</span> <span>Services</span></h2>
                <div class="service-card">
                    <div class="card">
                        <div class="lines"></div>
                                <div class="imgBx">
                                    <label for="">🌐</label>
                                </div>
                                <div class="content">
                                    <div class="details">
                                        <h3>Web Development</h3>
                                        <p>Building responsive and user-friendly websites using modern technologies.</p>
                                    </div>
                                </div>
                            </div>
                    <div class="card">
                        <div class="lines"></div>
                        <div class="imgBx">
                            <label>📱</label>
                        </div>
                        <div class="content">
                            <div class="details">
                                <h3>App Development</h3>
                                <p>Creating powerful mobile applications for Android and iOS platforms.</p>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="lines"></div>
                        <div class="imgBx">
                            <label>🎨</label>
                        </div>
                        <div class="content">
                            <div class="details">
                                <h3>Logo Design</h3>
                                <p>Designing unique and memorable logos that represent a brand's identity.</p>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="lines"></div>
                        <div class="imgBx">
                            <label>🖌️</label>
                        </div>
                        <div class="content">
                            <div class="details">
                                <h3>Graphic Design</h3>
                                <p>Crafting visually appealing designs for digital and print media.</p>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="lines"></div>
                        <div class="imgBx">
                            <label>📈</label>
                        </div>
                        <div class="content">
                            <div class="details">
                                <h3>Marketing</h3>
                                <p>Promoting products and services to reach the right audience and grow business.</p>
                            </div>
                        </div>
                    </div>
                </div>
        </section>

        <section class="section-about" id="about">
            <div class="about-card">
                <p class="about-eyebrow">About Us</p>
                <h2>About Faaz Pro Tech</h2>
                <p class="about-lead">
                    We help startups, agencies, and growing businesses with end-to-end development and high-accuracy data processing,
                    turning ideas and raw data into business-ready digital solutions.
                </p>
                <div class="about-pill-row">
                    <span class="about-pill">Data Precision</span>
                    <span class="about-pill">Fast Turnaround</span>
                    <span class="about-pill">Secure Handling</span>
                </div>
                <div class="stats-grid">
                    <article>
                        <h3>98%</h3>
                        <p>Client Satisfaction</p>
                    </article>
                    <article>
                        <h3>24/7</h3>
                        <p>Support Availability</p>
                    </article>
                </div>
                <a href="#contact" class="btn btn-primary about-cta" style="margin-top: 20px;">Start Your Project</a>
            </div>
            <div class="about-card about-checklist">
                <h3>How We Work</h3>
                <p>From kickoff to delivery, every project follows a clear and transparent process.</p>
                <ul>
                    <li>Requirement understanding and source planning</li>
                    <li>Clean execution with quality checkpoints</li>
                    <li>Transparent reports and export-ready delivery</li>
                    <li>Continuous improvements based on feedback</li>
                </ul>
            </div>
        </section>

        <section class="section-contact" id="contact">
            <div class="contact-box">
                <h2>Let's build your next data workflow</h2>
                <p>Share your requirements and get a quick response from our team.</p>
                <form class="contact-form" id="contactForm" action="#" method="post" novalidate>
                    <label>
                        Full Name
                        <input type="text" name="name" placeholder="Your name" required>
                    </label>
                    <label>
                        Email Address
                        <input type="email" name="email" placeholder="you@example.com" required>
                    </label>
                    <label>
                        Project Details
                        <textarea name="message" rows="5" placeholder="Tell us what you need" required></textarea>
                    </label>
                    <button type="submit" class="btn btn-primary">Send Request</button>
                </form>
                <p class="auth-message" id="contactMessage" aria-live="polite"></p>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="footer-details">
            <div class="footer-top">
                <h3><span>FAAZ PRO</span> TECH</h3>
                <p>Turning data into powerful business insights.</p>
            </div>
            <div class="footer-links">
                <div class="quick-links">
                    <h2>Quick Links</h2>
                    <a href="#home">Home</a>
                    <a href="#service">Services</a>
                    <a href="#about">About</a>
                    <a href="#contact">Contact</a>
                    <a href="portfolio/index.html" target="_blank" rel="noopener">Portfolio</a>
                </div>
                <div class="contact-links">
                    <h2>Contact</h2>
                    <a href="mailto:ebadmirza.2005@gmail.com">ebadmirza.2005@gmail.com</a>
                    <a href="#contact">Request a Quote</a>
                    <p>Fast response for BPO Services and Digital Services projects.</p>
                </div>
                <div class="social-links">
                    <h2>Social Links</h2>
                    <a href="https://www.facebook.com/profile.php?id=61573335866294" target="_blank" rel="noopener">
                        <i class='bx bxl-facebook-circle' aria-hidden="true"></i>
                        <span>Facebook</span>
                    </a>
                    <a href="https://www.linkedin.com/company/faaz-pro-tech/?viewAsMember=true" target="_blank" rel="noopener">
                        <i class='bx bxl-linkedin-square' aria-hidden="true"></i>
                        <span>LinkedIn</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p>Copyright <span id="year"></span> Fast Pro Tech. All rights reserved.</p>
        </div>
    </footer>

    <div class="auth-modal" id="authModal" aria-hidden="true" role="dialog" aria-labelledby="authModalTitle">
        <div class="auth-panel" role="document">
            <button type="button" class="auth-close" id="closeAuth" aria-label="Close authentication">x</button>
            <h2 id="authModalTitle">Welcome Back</h2>
            <p>Join Fast Pro Tech and manage your service requests with ease.</p>

            <div class="auth-tabs" role="tablist" aria-label="Auth tabs">
                <button type="button" class="tab-btn is-active" id="loginTab" data-target="loginForm" role="tab"
                    aria-selected="true">Login</button>
                <button type="button" class="tab-btn" id="signupTab" data-target="signupForm" role="tab"
                    aria-selected="false">Sign Up</button>
            </div>

            <form id="loginForm" class="auth-form is-active" method="POST" novalidate>
                <label>
                    Email Address
                    <input type="email" id="loginEmail" placeholder="you@example.com" required>
                </label>
                <label>
                    Password
                </label>
                <input type="password" id="loginPassword" placeholder="Enter password" minlength="6" required>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>

            <form id="signupForm" class="auth-form" method="POST"  novalidate>
                <label>
                    Full Name
                    <input type="text" id="signupName" placeholder="Your full name" required>
                </label>
                <label>
                    Email Address
                    <input type="email" id="signupEmail" placeholder="you@example.com" required>
                </label>
                <label>
                    Password
                    <input type="password" id="signupPassword" placeholder="Create password" minlength="6" required>
                </label>
                <button type="submit" class="btn btn-primary">Create Account</button>
            </form>

            <p class="auth-message" id="authMessage" aria-live="polite"></p>
        </div>
    </div>

    <script src="script.js"></script>
</body>

</html>