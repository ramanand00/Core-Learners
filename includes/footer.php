    </div>
    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>About Core Learners</h3>
                <p>Empowering learners worldwide with quality education and collaborative learning experiences.</p>
                <div class="social-links">
                    <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>

            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul class="footer-links">
                    <li><a href="/Core-Learners/pages/courses.php">Courses</a></li>
                    <li><a href="/Core-Learners/pages/videos.php">Videos</a></li>
                    <li><a href="/Core-Learners/pages/notes.php">Notes</a></li>
                    <li><a href="/Core-Learners/pages/community.php">Community</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h3>Support</h3>
                <ul class="footer-links">
                    <li><a href="/Core-Learners/pages/faq.php">FAQ</a></li>
                    <li><a href="/Core-Learners/pages/contact.php">Contact Us</a></li>
                    <li><a href="/Core-Learners/pages/privacy.php">Privacy Policy</a></li>
                    <li><a href="/Core-Learners/pages/terms.php">Terms of Service</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h3>Newsletter</h3>
                <p>Subscribe to our newsletter for updates and learning resources.</p>
                <form class="newsletter-form">
                    <div class="input-group">
                        <input type="email" placeholder="Enter your email" required>
                        <button type="submit" class="btn btn-primary">Subscribe</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <p>&copy; <?php echo date('Y'); ?> Core Learners. All rights reserved.</p>
                <div class="footer-bottom-links">
                    <a href="/Core-Learners/pages/cookies.php">Cookie Policy</a>
                    <a href="/Core-Learners/pages/sitemap.php">Sitemap</a>
                </div>
            </div>
        </div>
    </footer>
    <script src="/Core-Learners/assets/js/script.js"></script>
    <style>
    .footer {
        background-color: #2c3e50;
        color: #ecf0f1;
        padding: 4rem 0 0;
        margin-top: 4rem;
    }

    .footer-content {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 2rem;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
    }

    .footer-section h3 {
        color: #3498db;
        margin-bottom: 1.5rem;
        font-size: 1.2rem;
    }

    .footer-section p {
        color: #bdc3c7;
        line-height: 1.6;
        margin-bottom: 1.5rem;
    }

    .social-links {
        display: flex;
        gap: 1rem;
    }

    .social-link {
        color: #ecf0f1;
        font-size: 1.5rem;
        transition: color 0.3s ease;
    }

    .social-link:hover {
        color: #3498db;
    }

    .footer-links {
        list-style: none;
        padding: 0;
    }

    .footer-links li {
        margin-bottom: 0.8rem;
    }

    .footer-links a {
        color: #bdc3c7;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .footer-links a:hover {
        color: #3498db;
    }

    .newsletter-form .input-group {
        display: flex;
        gap: 0.5rem;
    }

    .newsletter-form input {
        flex: 1;
        padding: 0.5rem;
        border: 1px solid #34495e;
        border-radius: 4px;
        background-color: #34495e;
        color: #ecf0f1;
    }

    .newsletter-form button {
        padding: 0.5rem 1rem;
        background-color: #3498db;
        border: none;
        border-radius: 4px;
        color: #ecf0f1;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .newsletter-form button:hover {
        background-color: #2980b9;
    }

    .footer-bottom {
        margin-top: 3rem;
        padding: 1.5rem 0;
        border-top: 1px solid #34495e;
    }

    .footer-bottom-content {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .footer-bottom p {
        color: #bdc3c7;
        margin: 0;
    }

    .footer-bottom-links {
        display: flex;
        gap: 1.5rem;
    }

    .footer-bottom-links a {
        color: #bdc3c7;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .footer-bottom-links a:hover {
        color: #3498db;
    }

    @media (max-width: 768px) {
        .footer-content {
            grid-template-columns: 1fr;
            text-align: center;
        }

        .social-links {
            justify-content: center;
        }

        .footer-bottom-content {
            flex-direction: column;
            text-align: center;
        }

        .footer-bottom-links {
            justify-content: center;
        }
    }
    </style>
</body>
</html> 