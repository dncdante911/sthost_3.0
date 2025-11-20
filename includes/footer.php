</main>

<style>
/* ===== FUTURISTIC FOOTER ===== */
.futuristic-footer {
    position: relative;
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);
    color: #e2e8f0;
    overflow: hidden;
}

/* Animated Background */
.footer-bg-animation {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    overflow: hidden;
    pointer-events: none;
}

.footer-bg-animation::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(102, 126, 234, 0.1) 0%, transparent 50%);
    animation: float 20s ease-in-out infinite;
}

.footer-bg-animation::after {
    content: '';
    position: absolute;
    top: 50%;
    right: -30%;
    width: 100%;
    height: 100%;
    background: radial-gradient(circle, rgba(118, 75, 162, 0.1) 0%, transparent 50%);
    animation: float 25s ease-in-out infinite reverse;
}

@keyframes float {
    0%, 100% { transform: translate(0, 0) rotate(0deg); }
    25% { transform: translate(5%, 5%) rotate(5deg); }
    50% { transform: translate(0, 10%) rotate(0deg); }
    75% { transform: translate(-5%, 5%) rotate(-5deg); }
}

/* Glowing Top Border */
.footer-glow {
    height: 4px;
    background: linear-gradient(90deg,
        transparent,
        #667eea,
        #764ba2,
        #667eea,
        transparent
    );
    background-size: 200% auto;
    animation: glowMove 3s linear infinite;
}

@keyframes glowMove {
    0% { background-position: 200% center; }
    100% { background-position: -200% center; }
}

/* Main Content */
.footer-content {
    position: relative;
    z-index: 1;
    padding: 60px 0 30px;
}

.footer-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr 1.5fr;
    gap: 40px;
}

/* Brand Section */
.footer-brand {
    padding-right: 30px;
}

.footer-logo {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
    text-decoration: none;
}

.footer-logo-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    box-shadow: 0 0 30px rgba(102, 126, 234, 0.3);
}

.footer-logo-text {
    color: white;
    font-size: 1.4rem;
    font-weight: 700;
    line-height: 1.2;
}

.footer-logo-text small {
    display: block;
    font-size: 0.6rem;
    font-weight: 400;
    opacity: 0.7;
    letter-spacing: 2px;
    text-transform: uppercase;
}

.footer-desc {
    color: #94a3b8;
    font-size: 0.9rem;
    line-height: 1.7;
    margin-bottom: 25px;
}

/* Social Links */
.footer-social {
    display: flex;
    gap: 10px;
}

.social-link {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
    text-decoration: none;
    font-size: 1.1rem;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.social-link::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, #667eea, #764ba2);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.social-link:hover {
    color: white;
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
    border-color: transparent;
}

.social-link:hover::before {
    opacity: 1;
}

.social-link i {
    position: relative;
    z-index: 1;
}

/* Footer Columns */
.footer-column h4 {
    color: white;
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 20px;
    position: relative;
    padding-left: 15px;
}

.footer-column h4::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 4px;
    height: 20px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 2px;
}

.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links li {
    margin-bottom: 8px;
}

.footer-links a {
    color: #94a3b8;
    text-decoration: none;
    font-size: 0.85rem;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 4px 0;
    transition: all 0.3s ease;
    position: relative;
}

.footer-links a::before {
    content: '→';
    opacity: 0;
    transform: translateX(-10px);
    transition: all 0.3s ease;
    color: #667eea;
}

.footer-links a:hover {
    color: white;
    transform: translateX(5px);
}

.footer-links a:hover::before {
    opacity: 1;
    transform: translateX(0);
}

/* Contact Section */
.footer-contact-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 16px;
    padding: 10px;
    border-radius: 8px;
    transition: background 0.3s ease;
}

.footer-contact-item:hover {
    background: rgba(255, 255, 255, 0.05);
}

.contact-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: rgba(102, 126, 234, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #667eea;
    font-size: 0.9rem;
    flex-shrink: 0;
}

.contact-info {
    flex: 1;
}

.contact-label {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #64748b;
    margin-bottom: 4px;
}

.contact-value {
    color: #e2e8f0;
    font-size: 0.85rem;
}

.contact-value a {
    color: inherit;
    text-decoration: none;
    transition: color 0.3s ease;
}

.contact-value a:hover {
    color: #667eea;
}

/* Stats Section */
.footer-stats {
    padding: 30px 0;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    margin-top: 40px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 30px;
    text-align: center;
}

.stat-item {
    position: relative;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    background: linear-gradient(135deg, #667eea, #a78bfa);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    line-height: 1.2;
}

.stat-label {
    font-size: 0.75rem;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-top: 5px;
}

/* Bottom Section */
.footer-bottom {
    padding: 20px 0;
    position: relative;
    z-index: 1;
}

.footer-bottom-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}

.footer-copyright {
    color: #64748b;
    font-size: 0.8rem;
}

.footer-copyright a {
    color: #667eea;
    text-decoration: none;
}

.footer-legal {
    display: flex;
    gap: 25px;
}

.footer-legal a {
    color: #64748b;
    text-decoration: none;
    font-size: 0.8rem;
    transition: color 0.3s ease;
}

.footer-legal a:hover {
    color: #667eea;
}

/* Responsive */
@media (max-width: 1200px) {
    .footer-grid {
        grid-template-columns: 1fr 1fr 1fr;
    }

    .footer-brand {
        grid-column: 1 / -1;
        padding-right: 0;
        text-align: center;
    }

    .footer-social {
        justify-content: center;
    }
}

@media (max-width: 768px) {
    .footer-grid {
        grid-template-columns: 1fr 1fr;
        gap: 30px;
    }

    .stats-grid {
        grid-template-columns: repeat(3, 1fr);
    }

    .footer-bottom-content {
        flex-direction: column;
        text-align: center;
    }
}

@media (max-width: 480px) {
    .footer-grid {
        grid-template-columns: 1fr;
    }

    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .footer-legal {
        flex-wrap: wrap;
        justify-content: center;
    }
}
</style>

<!-- Futuristic Footer -->
<footer class="futuristic-footer">
    <div class="footer-bg-animation"></div>
    <div class="footer-glow"></div>

    <div class="container">
        <div class="footer-content">
            <div class="footer-grid">
                <!-- Brand -->
                <div class="footer-brand">
                    <a href="/" class="footer-logo">
                        <div class="footer-logo-icon">
                            <i class="bi bi-lightning-charge-fill"></i>
                        </div>
                        <div class="footer-logo-text">
                            StormHosting
                            <small>Хостинг Провайдер</small>
                        </div>
                    </a>
                    <p class="footer-desc">
                        Надійний український хостинг з 2018 року. Сервери в Україні та Європі.
                        Технічна підтримка 24/7. Гарантія uptime 99.9%.
                    </p>
                    <div class="footer-social">
                        <a href="https://t.me/stormhosting_ua" class="social-link" title="Telegram">
                            <i class="bi bi-telegram"></i>
                        </a>
                        <a href="viber://chat?number=380996239637" class="social-link" title="Viber">
                            <i class="bi bi-chat-dots-fill"></i>
                        </a>
                        <a href="https://facebook.com/stormhosting.ua" class="social-link" title="Facebook">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="https://instagram.com/stormhosting_ua" class="social-link" title="Instagram">
                            <i class="bi bi-instagram"></i>
                        </a>
                    </div>
                </div>

                <!-- Services -->
                <div class="footer-column">
                    <h4>Послуги</h4>
                    <ul class="footer-links">
                        <li><a href="/pages/hosting/shared.php">Хостинг</a></li>
                        <li><a href="/pages/hosting/reseller.php">Реселер</a></li>
                        <li><a href="/pages/vds/virtual.php">VDS/VPS</a></li>
                        <li><a href="/pages/vds/dedicated.php">Виділені сервери</a></li>
                        <li><a href="/pages/domains/register.php">Домени</a></li>
                        <li><a href="/pages/info/ssl.php">SSL сертифікати</a></li>
                    </ul>
                </div>

                <!-- Support -->
                <div class="footer-column">
                    <h4>Підтримка</h4>
                    <ul class="footer-links">
                        <li><a href="/pages/info/faq.php">FAQ</a></li>
                        <li><a href="/pages/info/about.php">Про компанію</a></li>
                        <li><a href="/pages/info/quality.php">Гарантія якості</a></li>
                        <li><a href="/pages/info/rules.php">Правила</a></li>
                        <li><a href="/pages/info/legal.php">Юридична інфо</a></li>
                        <li><a href="/pages/contacts.php">Контакти</a></li>
                    </ul>
                </div>

                <!-- Tools -->
                <div class="footer-column">
                    <h4>Інструменти</h4>
                    <ul class="footer-links">
                        <li><a href="/pages/domains/whois.php">WHOIS</a></li>
                        <li><a href="/pages/domains/dns.php">DNS перевірка</a></li>
                        <li><a href="/pages/tools/site-check.php">Перевірка сайту</a></li>
                        <li><a href="/pages/tools/ip-check.php">Перевірка IP</a></li>
                        <li><a href="/pages/vds/vds-calc.php">Калькулятор VDS</a></li>
                        <li><a href="/pages/domains/transfer.php">Трансфер домену</a></li>
                    </ul>
                </div>

                <!-- Contact -->
                <div class="footer-column">
                    <h4>Контакти</h4>
                    <div class="footer-contact-item">
                        <div class="contact-icon">
                            <i class="bi bi-telephone"></i>
                        </div>
                        <div class="contact-info">
                            <div class="contact-label">Телефони</div>
                            <div class="contact-value">
                                <a href="tel:+380996239637">+380 99 623 96 37</a><br>
                                <a href="tel:+380930253941">+380 93 025 39 41</a>
                            </div>
                        </div>
                    </div>
                    <div class="footer-contact-item">
                        <div class="contact-icon">
                            <i class="bi bi-envelope"></i>
                        </div>
                        <div class="contact-info">
                            <div class="contact-label">Email</div>
                            <div class="contact-value">
                                <a href="mailto:info@sthost.pro">info@sthost.pro</a>
                            </div>
                        </div>
                    </div>
                    <div class="footer-contact-item">
                        <div class="contact-icon">
                            <i class="bi bi-clock"></i>
                        </div>
                        <div class="contact-info">
                            <div class="contact-label">Підтримка</div>
                            <div class="contact-value">24/7 Online</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="footer-stats">
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-value">99.9%</div>
                        <div class="stat-label">Uptime</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">24/7</div>
                        <div class="stat-label">Підтримка</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">6+</div>
                        <div class="stat-label">Років досвіду</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">1000+</div>
                        <div class="stat-label">Клієнтів</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">5000+</div>
                        <div class="stat-label">Сайтів</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom -->
        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <div class="footer-copyright">
                    &copy; <?php echo date('Y'); ?> <a href="/">StormHosting UA</a>. Всі права захищені.
                </div>
                <div class="footer-legal">
                    <a href="/pages/info/legal.php">Конфіденційність</a>
                    <a href="/pages/info/rules.php">Умови</a>
                    <a href="/pages/info/about.php">Про нас</a>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Back to Top -->
<button id="back-to-top" onclick="window.scrollTo({top:0,behavior:'smooth'})" style="
    position: fixed;
    bottom: 90px;
    right: 20px;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border: none;
    color: white;
    font-size: 18px;
    cursor: pointer;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    z-index: 9998;
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
">
    <i class="bi bi-arrow-up"></i>
</button>

<!-- Chat Button -->
<a href="https://t.me/stormhosting_ua" target="_blank" class="chat-float-btn" style="
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
    text-decoration: none;
    z-index: 9999;
    box-shadow: 0 4px 25px rgba(102, 126, 234, 0.5);
    transition: all 0.3s ease;
">
    <i class="bi bi-chat-dots-fill"></i>
</a>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/main.js?v=<?php echo @filemtime($_SERVER['DOCUMENT_ROOT'] . '/assets/js/main.js') ?: time(); ?>"></script>

<?php if (isset($page_js) && !empty($page_js)): ?>
    <script src="/assets/js/pages/<?php echo $page_js; ?>.js?v=<?php echo @filemtime($_SERVER['DOCUMENT_ROOT'] . "/assets/js/pages/{$page_js}.js") ?: time(); ?>"></script>
<?php endif; ?>

<script>
// Back to top
window.addEventListener('scroll', function() {
    const btn = document.getElementById('back-to-top');
    if (window.pageYOffset > 300) {
        btn.style.opacity = '1';
        btn.style.visibility = 'visible';
    } else {
        btn.style.opacity = '0';
        btn.style.visibility = 'hidden';
    }
});

// Chat button hover
const chatBtn = document.querySelector('.chat-float-btn');
if (chatBtn) {
    chatBtn.addEventListener('mouseenter', function() {
        this.style.transform = 'scale(1.1)';
    });
    chatBtn.addEventListener('mouseleave', function() {
        this.style.transform = 'scale(1)';
    });
}

// Site config
window.siteConfig = {
    lang: '<?php echo $current_lang ?? 'uk'; ?>',
    baseUrl: '<?php echo defined('SITE_URL') ? SITE_URL : ''; ?>'
};
</script>

</body>
</html>
