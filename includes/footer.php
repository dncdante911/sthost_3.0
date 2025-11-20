</main>

<style>
/* ===== FOOTER STYLES ===== */
.site-footer {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    color: #e2e8f0;
    padding-top: 60px;
    position: relative;
}

.site-footer::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.footer-main {
    padding-bottom: 40px;
}

.footer-brand {
    margin-bottom: 20px;
}

.footer-logo {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
    text-decoration: none;
    color: white;
}

.footer-logo img {
    height: 40px;
    filter: brightness(0) invert(1);
}

.footer-logo span {
    font-size: 1.5rem;
    font-weight: 700;
}

.footer-description {
    color: #94a3b8;
    font-size: 0.95rem;
    line-height: 1.6;
    margin-bottom: 20px;
}

.footer-social {
    display: flex;
    gap: 12px;
}

.footer-social a {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    text-decoration: none;
    transition: all 0.3s ease;
}

.footer-social a:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    transform: translateY(-3px);
}

.footer-title {
    color: white;
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 20px;
    position: relative;
    padding-bottom: 10px;
}

.footer-title::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 30px;
    height: 2px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links li {
    margin-bottom: 10px;
}

.footer-links a {
    color: #94a3b8;
    text-decoration: none;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.footer-links a:hover {
    color: #667eea;
    transform: translateX(5px);
}

.footer-links a i {
    font-size: 0.8rem;
    opacity: 0.7;
}

.footer-contact-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 16px;
    color: #94a3b8;
    font-size: 0.9rem;
}

.footer-contact-item i {
    color: #667eea;
    font-size: 1.1rem;
    margin-top: 2px;
}

.footer-contact-item a {
    color: #94a3b8;
    text-decoration: none;
    transition: color 0.3s ease;
}

.footer-contact-item a:hover {
    color: #667eea;
}

.footer-contact-item strong {
    color: #e2e8f0;
    display: block;
    margin-bottom: 4px;
}

/* Trust Badges */
.footer-trust {
    padding: 30px 0;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.trust-badges {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
    gap: 40px;
}

.trust-badge {
    text-align: center;
    color: #94a3b8;
}

.trust-badge i {
    font-size: 2rem;
    margin-bottom: 8px;
    display: block;
}

.trust-badge span {
    font-size: 0.8rem;
    display: block;
}

.trust-badge.ssl i { color: #22c55e; }
.trust-badge.uptime i { color: #f59e0b; }
.trust-badge.support i { color: #06b6d4; }
.trust-badge.ukraine i { color: #3b82f6; }
.trust-badge.payment i { color: #8b5cf6; }

/* Footer Bottom */
.footer-bottom {
    padding: 20px 0;
}

.footer-bottom-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}

.footer-copyright {
    color: #64748b;
    font-size: 0.85rem;
}

.footer-copyright a {
    color: #667eea;
    text-decoration: none;
}

.footer-legal {
    display: flex;
    gap: 20px;
}

.footer-legal a {
    color: #64748b;
    text-decoration: none;
    font-size: 0.85rem;
    transition: color 0.3s ease;
}

.footer-legal a:hover {
    color: #667eea;
}

/* Responsive */
@media (max-width: 991px) {
    .footer-main .row > div {
        margin-bottom: 30px;
    }
}

@media (max-width: 767px) {
    .trust-badges {
        gap: 20px;
    }

    .footer-bottom-content {
        flex-direction: column;
        text-align: center;
    }

    .footer-legal {
        flex-wrap: wrap;
        justify-content: center;
    }
}
</style>

<!-- Footer -->
<footer class="site-footer">
    <div class="container">
        <!-- Main Footer Content -->
        <div class="footer-main">
            <div class="row">
                <!-- Company Info -->
                <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
                    <div class="footer-brand">
                        <a href="/" class="footer-logo">
                            <img src="/assets/img/logo.svg" alt="StormHosting UA">
                            <span>StormHosting</span>
                        </a>
                        <p class="footer-description">
                            Надійний український хостинг провайдер. Забезпечуємо стабільну роботу ваших сайтів 24/7 з 2018 року. Сервери в Україні та Європі.
                        </p>
                        <div class="footer-social">
                            <a href="https://t.me/stormhosting_ua" target="_blank" rel="noopener" title="Telegram">
                                <i class="bi bi-telegram"></i>
                            </a>
                            <a href="https://facebook.com/stormhosting.ua" target="_blank" rel="noopener" title="Facebook">
                                <i class="bi bi-facebook"></i>
                            </a>
                            <a href="https://instagram.com/stormhosting_ua" target="_blank" rel="noopener" title="Instagram">
                                <i class="bi bi-instagram"></i>
                            </a>
                            <a href="viber://chat?number=380996239637" title="Viber">
                                <i class="bi bi-chat-dots"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Services -->
                <div class="col-lg-2 col-md-6 col-6 mb-4 mb-lg-0">
                    <h5 class="footer-title">Послуги</h5>
                    <ul class="footer-links">
                        <li><a href="/pages/hosting/shared.php"><i class="bi bi-chevron-right"></i>Хостинг</a></li>
                        <li><a href="/pages/hosting/reseller.php"><i class="bi bi-chevron-right"></i>Реселер</a></li>
                        <li><a href="/pages/vds/virtual.php"><i class="bi bi-chevron-right"></i>VDS/VPS</a></li>
                        <li><a href="/pages/vds/dedicated.php"><i class="bi bi-chevron-right"></i>Виділені сервери</a></li>
                        <li><a href="/pages/domains/register.php"><i class="bi bi-chevron-right"></i>Домени</a></li>
                        <li><a href="/pages/info/ssl.php"><i class="bi bi-chevron-right"></i>SSL сертифікати</a></li>
                        <li><a href="/pages/hosting/cloud.php"><i class="bi bi-chevron-right"></i>Cloud сховище</a></li>
                    </ul>
                </div>

                <!-- Support & Info -->
                <div class="col-lg-2 col-md-6 col-6 mb-4 mb-lg-0">
                    <h5 class="footer-title">Підтримка</h5>
                    <ul class="footer-links">
                        <li><a href="/pages/info/faq.php"><i class="bi bi-chevron-right"></i>FAQ</a></li>
                        <li><a href="/pages/info/about.php"><i class="bi bi-chevron-right"></i>Про компанію</a></li>
                        <li><a href="/pages/info/quality.php"><i class="bi bi-chevron-right"></i>Гарантія якості</a></li>
                        <li><a href="/pages/info/rules.php"><i class="bi bi-chevron-right"></i>Правила</a></li>
                        <li><a href="/pages/info/legal.php"><i class="bi bi-chevron-right"></i>Юридична інфо</a></li>
                        <li><a href="/pages/info/complaints.php"><i class="bi bi-chevron-right"></i>Скарги</a></li>
                        <li><a href="/pages/contacts.php"><i class="bi bi-chevron-right"></i>Контакти</a></li>
                    </ul>
                </div>

                <!-- Tools -->
                <div class="col-lg-2 col-md-6 col-6 mb-4 mb-lg-0">
                    <h5 class="footer-title">Інструменти</h5>
                    <ul class="footer-links">
                        <li><a href="/pages/domains/whois.php"><i class="bi bi-chevron-right"></i>WHOIS</a></li>
                        <li><a href="/pages/domains/dns.php"><i class="bi bi-chevron-right"></i>DNS перевірка</a></li>
                        <li><a href="/pages/tools/site-check.php"><i class="bi bi-chevron-right"></i>Перевірка сайту</a></li>
                        <li><a href="/pages/tools/ip-check.php"><i class="bi bi-chevron-right"></i>Перевірка IP</a></li>
                        <li><a href="/pages/vds/vds-calc.php"><i class="bi bi-chevron-right"></i>Калькулятор VDS</a></li>
                        <li><a href="/pages/domains/transfer.php"><i class="bi bi-chevron-right"></i>Трансфер домену</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div class="col-lg-2 col-md-6 col-6">
                    <h5 class="footer-title">Контакти</h5>
                    <div class="footer-contact-item">
                        <i class="bi bi-telephone"></i>
                        <div>
                            <strong>Телефони</strong>
                            <a href="tel:+380996239637">+380 99 623 96 37</a><br>
                            <a href="tel:+380930253941">+380 93 025 39 41</a>
                        </div>
                    </div>
                    <div class="footer-contact-item">
                        <i class="bi bi-envelope"></i>
                        <div>
                            <strong>Email</strong>
                            <a href="mailto:info@sthost.pro">info@sthost.pro</a><br>
                            <a href="mailto:support@sthost.pro">support@sthost.pro</a>
                        </div>
                    </div>
                    <div class="footer-contact-item">
                        <i class="bi bi-geo-alt"></i>
                        <div>
                            <strong>Адреса</strong>
                            м. Дніпро, Україна<br>
                            пл. Ак. Стародубова 1
                        </div>
                    </div>
                    <div class="footer-contact-item">
                        <i class="bi bi-clock"></i>
                        <div>
                            <strong>Графік</strong>
                            Пн-Пт: 09:00-18:00<br>
                            Підтримка: 24/7
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Trust Badges -->
        <div class="footer-trust">
            <div class="trust-badges">
                <div class="trust-badge ssl">
                    <i class="bi bi-shield-check"></i>
                    <span>SSL Захист</span>
                </div>
                <div class="trust-badge uptime">
                    <i class="bi bi-lightning-charge"></i>
                    <span>99.9% Uptime</span>
                </div>
                <div class="trust-badge support">
                    <i class="bi bi-headset"></i>
                    <span>24/7 Підтримка</span>
                </div>
                <div class="trust-badge ukraine">
                    <i class="bi bi-flag"></i>
                    <span>UA Сервери</span>
                </div>
                <div class="trust-badge payment">
                    <i class="bi bi-credit-card"></i>
                    <span>Безпечні платежі</span>
                </div>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <div class="footer-copyright">
                    &copy; <?php echo date('Y'); ?> <a href="/">StormHosting UA</a>. Всі права захищені.
                    <br><small>Розроблено з ❤️ в Україні</small>
                </div>
                <div class="footer-legal">
                    <a href="/pages/info/legal.php">Політика конфіденційності</a>
                    <a href="/pages/info/rules.php">Умови використання</a>
                    <a href="/pages/info/about.php">Про нас</a>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Back to Top Button -->
<button id="back-to-top" class="back-to-top" aria-label="Наверх" style="
    position: fixed;
    bottom: 100px;
    right: 20px;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border: none;
    color: white;
    font-size: 20px;
    cursor: pointer;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    z-index: 9998;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
">
    <i class="bi bi-arrow-up"></i>
</button>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/main.js?v=<?php echo @filemtime($_SERVER['DOCUMENT_ROOT'] . '/assets/js/main.js') ?: time(); ?>"></script>

<?php if (isset($page_js) && !empty($page_js)): ?>
    <script src="/assets/js/pages/<?php echo $page_js; ?>.js?v=<?php echo @filemtime($_SERVER['DOCUMENT_ROOT'] . "/assets/js/pages/{$page_js}.js") ?: time(); ?>"></script>
<?php endif; ?>

<script>
// Back to top button
document.addEventListener('DOMContentLoaded', function() {
    const backToTop = document.getElementById('back-to-top');

    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            backToTop.style.opacity = '1';
            backToTop.style.visibility = 'visible';
        } else {
            backToTop.style.opacity = '0';
            backToTop.style.visibility = 'hidden';
        }
    });

    backToTop.addEventListener('click', function() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
});

// Site config
window.siteConfig = {
    lang: '<?php echo $current_lang ?? 'uk'; ?>',
    baseUrl: '<?php echo defined('SITE_URL') ? SITE_URL : ''; ?>'
};
</script>

<!-- Chat Widget (Simple) -->
<style>
.chat-widget-btn {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 50%;
    border: none;
    color: white;
    cursor: pointer;
    z-index: 9999;
    font-size: 24px;
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
    transition: all 0.3s ease;
}

.chat-widget-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 25px rgba(102, 126, 234, 0.5);
}
</style>

<button class="chat-widget-btn" onclick="window.open('https://t.me/stormhosting_ua', '_blank')" title="Написати в Telegram">
    <i class="bi bi-chat-dots"></i>
</button>

</body>
</html>
