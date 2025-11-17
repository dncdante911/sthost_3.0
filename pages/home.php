<?php
/**
 * StormHosting UA - Обновленная главная страница
 * Файл: /pages/home.php
 * 
 * Главная страница с новостями, бегущей строкой и богатым контентом
 */

// Защита от прямого доступа
if (!defined('SECURE_ACCESS')) {
    http_response_code(403);
    die('Доступ запрещен');
}

// Функции-заглушки если не определены
if (!function_exists('escapeOutput')) {
    function escapeOutput($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('formatPrice')) {
    function formatPrice($price, $currency = 'грн') {
        return number_format($price, 0, '.', ' ') . ' ' . $currency;
    }
}

if (!function_exists('t')) {
    function t($key) {
        $translations = [
            'hero_title' => 'Професійний хостинг з підтримкою 24/7',
            'hero_subtitle' => 'Швидкі SSD сервери, безкоштовний SSL, миттєва активація. Найкращий хостинг для вашого бізнесу в Україні!',
            'stats_sites' => 'Активних сайтів',
            'stats_uptime' => 'Час безвідмовної роботи',
            'stats_support' => 'Підтримка 24/7',
            'news_title' => 'Останні новини',
            'popular_domains_title' => 'Популярні домени',
            'popular_hosting_title' => 'Тарифні плани хостингу'
        ];
        return $translations[$key] ?? $key;
    }
}

// Получение новостей для бегущей строки
$news_ticker = [
    'Нові тарифи хостингу з покращеними характеристиками!',
    'Безкоштовні SSL сертифікати для всіх клієнтів',
    'Підтримка PHP 8.2 та останніх технологій',
    'Акція: -50% на перший місяць VPS серверів!',
    'Новий дата-центр у Києві вже працює',
];

// Текущий язык
$current_lang = $current_lang ?? 'ua';
?>
<link rel="stylesheet" href="/assets/css/pages/index.css">
<!-- Бегущая строка новостей -->
<div class="news-ticker">
    <div class="news-ticker-wrap">
        <div class="news-ticker-content">
            <i class="bi bi-megaphone"></i>
            <span class="ticker-label">Новини:</span>
            <div class="ticker-items">
                <?php foreach($news_ticker as $news_item): ?>
                    <span class="ticker-item"><?php echo escapeOutput($news_item); ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-background">
        <div class="hero-particles"></div>
        <div class="floating-icons">
            <i class="bi bi-server floating-icon" style="top: 20%; left: 10%;"></i>
            <i class="bi bi-shield-check floating-icon" style="top: 60%; left: 15%;"></i>
            <i class="bi bi-lightning floating-icon" style="top: 30%; right: 20%;"></i>
            <i class="bi bi-globe floating-icon" style="top: 70%; right: 10%;"></i>
        </div>
    </div>
    
    <div class="container">
        <div class="row align-items-center min-vh-75">
            <div class="col-lg-7">
                <div class="hero-content">
                    <h1 class="hero-title">
                        <span class="title-highlight">Storm</span>Hosting UA
                        <br>
                        <span class="subtitle-animated"><?php echo t('hero_title'); ?></span>
                    </h1>
                    
                    <p class="hero-subtitle">
                        <?php echo t('hero_subtitle'); ?>
                    </p>
                    
                    <!-- Статистика -->
                    <div class="hero-stats">
                        <div class="stat-item">
                            <div class="stat-number" data-count="15000">0</div>
                            <div class="stat-label"><?php echo t('stats_sites'); ?></div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number" data-count="99.9">0</div>
                            <div class="stat-label"><?php echo t('stats_uptime'); ?>%</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number" data-count="24">0</div>
                            <div class="stat-label"><?php echo t('stats_support'); ?></div>
                        </div>
                    </div>
                    
                    <div class="hero-buttons">
                        <a href="/pages/hosting/shared.php" class="btn-hero-primary">
                            <i class="bi bi-rocket-takeoff"></i>
                            Обрати хостинг
                        </a>
                        <a href="/pages/domains/register.php" class="btn-hero-outline">
                            <i class="bi bi-globe"></i>
                            Зареєструвати домен
                        </a>
                        <a href="/pages/vds/virtual.php" class="btn-hero-secondary">
                            <i class="bi bi-server"></i>
                            VDS сервери
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-5">
                <div class="hero-visual">
                    <div class="server-rack">
                        <div class="server-unit active">
                            <div class="server-lights">
                                <span class="light green pulse"></span>
                                <span class="light green pulse" style="animation-delay: 0.5s;"></span>
                                <span class="light green pulse" style="animation-delay: 1s;"></span>
                            </div>
                            <div class="server-label">Web Server</div>
                        </div>
                        <div class="server-unit active">
                            <div class="server-lights">
                                <span class="light blue pulse"></span>
                                <span class="light blue pulse" style="animation-delay: 0.3s;"></span>
                                <span class="light blue pulse" style="animation-delay: 0.8s;"></span>
                            </div>
                            <div class="server-label">Database</div>
                        </div>
                        <div class="server-unit active">
                            <div class="server-lights">
                                <span class="light orange pulse"></span>
                                <span class="light orange pulse" style="animation-delay: 0.7s;"></span>
                                <span class="light orange pulse" style="animation-delay: 1.2s;"></span>
                            </div>
                            <div class="server-label">Storage</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Услуги -->
<section class="services-section">
    <div class="container">
        <div class="section-header text-center">
            <h2 class="section-title">Наші послуги</h2>
            <p class="section-subtitle">Повний спектр послуг для вашого онлайн бізнесу</p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="service-card" data-href="/pages/hosting/shared.php">
                    <div class="service-icon">
                        <i class="bi bi-hdd-stack"></i>
                    </div>
                    <h3 class="service-title">Веб хостинг</h3>
                    <p class="service-description">Надійний хостинг для сайтів будь-якої складності з підтримкою PHP, MySQL та безкоштовним SSL</p>
                    <div class="service-features">
                        <div class="feature">✓ SSD накопичувачі</div>
                        <div class="feature">✓ Безкоштовний SSL</div>
                        <div class="feature">✓ Щоденні бекапи</div>
                        <div class="feature">✓ cPanel панель</div>
                    </div>
                    <div class="service-price">
                        від <span class="price">99 грн</span>/міс
                    </div>
                    <a href="/pages/hosting/shared.php" class="service-btn">Детальніше</a>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="service-card featured" data-href="/pages/vds/virtual.php">
                    <div class="featured-badge">Популярно</div>
                    <div class="service-icon">
                        <i class="bi bi-cloud"></i>
                    </div>
                    <h3 class="service-title">VPS/VDS сервери</h3>
                    <p class="service-description">Потужні віртуальні сервери з повним контролем та гарантованими ресурсами</p>
                    <div class="service-features">
                        <div class="feature">✓ NVMe SSD диски</div>
                        <div class="feature">✓ Root доступ</div>
                        <div class="feature">✓ Миттєва активація</div>
                        <div class="feature">✓ Масштабування</div>
                    </div>
                    <div class="service-price">
                        від <span class="price">299 грн</span>/міс
                    </div>
                    <a href="/pages/vds/virtual.php" class="service-btn">Детальніше</a>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="service-card" data-href="/pages/domains/register.php">
                    <div class="service-icon">
                        <i class="bi bi-globe2"></i>
                    </div>
                    <h3 class="service-title">Домени</h3>
                    <p class="service-description">Реєстрація та управління доменними іменами у всіх популярних зонах</p>
                    <div class="service-features">
                        <div class="feature">✓ Українські домени</div>
                        <div class="feature">✓ Міжнародні зони</div>
                        <div class="feature">✓ DNS управління</div>
                        <div class="feature">✓ WHOIS захист</div>
                    </div>
                    <div class="service-price">
                        від <span class="price">150 грн</span>/рік
                    </div>
                    <a href="/pages/domains/register.php" class="service-btn">Детальніше</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Популярные домены -->
<section class="popular-domains-section">
    <div class="container">
        <div class="section-header text-center">
            <h2 class="section-title"><?php echo t('popular_domains_title'); ?></h2>
            <p class="section-subtitle">Найпопулярніші доменні зони за вигідними цінами</p>
        </div>
        
        <div class="domains-grid">
            <?php if (isset($popular_domains) && is_array($popular_domains)): ?>
                <?php foreach($popular_domains as $domain): ?>
                    <div class="domain-card">
                        <div class="domain-zone"><?php echo escapeOutput($domain['zone']); ?></div>
                        <div class="domain-price"><?php echo formatPrice($domain['price_registration']); ?>/рік</div>
                        <a href="/pages/domains/register.php" class="domain-btn">Зареєструвати</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Статические данные если БД недоступна -->
                <div class="domain-card" data-href="/pages/domains/register.php">
                    <div class="domain-zone">.com.ua</div>
                    <div class="domain-price">150 грн/рік</div>
                    <a href="/pages/domains/register.php" class="domain-btn">Зареєструвати</a>
                </div>
                <div class="domain-card" data-href="/pages/domains/register.php">
                    <div class="domain-zone">.ua</div>
                    <div class="domain-price">200 грн/рік</div>
                    <a href="/pages/domains/register.php" class="domain-btn">Зареєструвати</a>
                </div>
                <div class="domain-card" data-href="/pages/domains/register.php">
                    <div class="domain-zone">.info</div>
                    <div class="domain-price">300 грн/рік</div>
                    <a href="/pages/domains/register.php" class="domain-btn">Зареєструвати</a>
                </div>
                <div class="domain-card" data-href="/pages/domains/register.php">
                    <div class="domain-zone">.com</div>
                    <div class="domain-price">350 грн/рік</div>
                    <a href="/pages/domains/register.php" class="domain-btn">Зареєструвати</a>
                </div>
                <div class="domain-card" data-href="/pages/domains/register.php">
                    <div class="domain-zone">.org</div>
                    <div class="domain-price">400 грн/рік</div>
                    <a href="/pages/domains/register.php" class="domain-btn">Зареєструвати</a>
                </div>
                <div class="domain-card" data-href="/pages/domains/register.php">
                    <div class="domain-zone">.net</div>
                    <div class="domain-price">450 грн/рік</div>
                    <a href="/pages/domains/register.php" class="domain-btn">Зареєструвати</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Тарифы хостинга -->
<section class="hosting-plans-section">
    <div class="container">
        <div class="section-header text-center">
            <h2 class="section-title"><?php echo t('popular_hosting_title'); ?></h2>
            <p class="section-subtitle">Оберіть оптимальний план для вашого проекту</p>
        </div>
        
        <div class="row g-4">
            <?php if (isset($popular_hosting) && is_array($popular_hosting)): ?>
                <?php foreach($popular_hosting as $plan): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="hosting-plan <?php echo $plan['is_popular'] ? 'popular' : ''; ?>">
                            <?php if ($plan['is_popular']): ?>
                                <div class="plan-badge">Популярний</div>
                            <?php endif; ?>
                            
                            <div class="plan-header">
                                <h3 class="plan-name"><?php echo escapeOutput($plan['name_ua']); ?></h3>
                                <div class="plan-price">
                                    <span class="price"><?php echo formatPrice($plan['price_monthly']); ?></span>
                                    <span class="period">/місяць</span>
                                </div>
                            </div>
                            
                            <div class="plan-features">
                                <div class="feature">
                                    <i class="bi bi-hdd"></i>
                                    <span><?php echo number_format($plan['disk_space']/1024, 0); ?> ГБ SSD</span>
                                </div>
                                <div class="feature">
                                    <i class="bi bi-arrow-up-right"></i>
                                    <span><?php echo $plan['bandwidth']; ?> ГБ трафіку</span>
                                </div>
                                <div class="feature">
                                    <i class="bi bi-database"></i>
                                    <span><?php echo $plan['databases']; ?> БД MySQL</span>
                                </div>
                                <div class="feature">
                                    <i class="bi bi-envelope"></i>
                                    <span><?php echo $plan['email_accounts']; ?> email скриньок</span>
                                </div>
                                <div class="feature">
                                    <i class="bi bi-shield-check"></i>
                                    <span>Безкоштовний SSL</span>
                                </div>
                            </div>
                            
                            <a href="/pages/hosting/shared.php" class="plan-btn <?php echo $plan['is_popular'] ? 'btn-primary' : 'btn-outline'; ?>">
                                Обрати план
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Новости -->
<section class="news-section">
    <div class="container">
        <div class="section-header">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="section-title"><?php echo t('news_title'); ?></h2>
                    <p class="section-subtitle">Будьте в курсі всіх оновлень та новин StormHosting UA</p>
                </div>
                <div class="col-auto">
                    <a href="javascript:void(0);" id="showAllNewsBtn" class="btn btn-outline-primary">Всі новини</a>
                </div>
            </div>
        </div>
        
        <div class="row g-4">
            <?php if (isset($latest_news) && is_array($latest_news)): ?>
                <?php foreach(array_slice($latest_news, 0, 4) as $index => $news): ?>
                    <div class="col-lg-6 col-md-6">
                        <article class="news-card <?php echo $index === 0 ? 'featured' : ''; ?>" data-news-id="<?php echo $news['id']; ?>">
                            <?php if ($news['image']): ?>
                                <div class="news-image">
                                    <img src="<?php echo escapeOutput($news['image']); ?>" alt="<?php echo escapeOutput($news['title']); ?>">
                                </div>
                            <?php else: ?>
                                <div class="news-image-placeholder">
                                    <i class="bi bi-newspaper"></i>
                                </div>
                            <?php endif; ?>

                            <div class="news-content">
                                <div class="news-meta">
                                    <time datetime="<?php echo date('Y-m-d', strtotime($news['created_at'])); ?>">
                                        <?php echo formatDate($news['created_at'], 'd.m.Y'); ?>
                                    </time>
                                </div>

                                <h3 class="news-title">
                                    <a href="javascript:void(0);"><?php echo escapeOutput($news['title']); ?></a>
                                </h3>

                                <p class="news-excerpt">
                                    <?php echo escapeOutput(mb_substr(strip_tags($news['content']), 0, 120)); ?>...
                                </p>

                                <a href="javascript:void(0);" class="news-link">
                                    Читати далі <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Преимущества -->
<section class="advantages-section">
    <div class="container">
        <div class="section-header text-center">
            <h2 class="section-title">Чому обирають StormHosting UA?</h2>
            <p class="section-subtitle">Переваги, які роблять нас кращими</p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="advantage-card">
                    <div class="advantage-icon">
                        <i class="bi bi-lightning-charge"></i>
                    </div>
                    <h3 class="advantage-title">Швидкість</h3>
                    <p class="advantage-description">NVMe SSD накопичувачі та оптимізовані сервери забезпечують максимальну швидкість</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="advantage-card">
                    <div class="advantage-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h3 class="advantage-title">Безпека</h3>
                    <p class="advantage-description">Комплексна система захисту, SSL сертифікати та регулярні бекапи</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="advantage-card">
                    <div class="advantage-icon">
                        <i class="bi bi-headset"></i>
                    </div>
                    <h3 class="advantage-title">Підтримка 24/7</h3>
                    <p class="advantage-description">Наша команда експертів готова допомогти вам у будь-який час доби</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="advantage-card">
                    <div class="advantage-icon">
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <h3 class="advantage-title">Масштабування</h3>
                    <p class="advantage-description">Легко збільшуйте ресурси в міру зростання вашого проекту</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter подписка -->
<section class="newsletter-section">
    <div class="container">
        <div class="newsletter-card">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h3 class="newsletter-title">Підпишіться на розсилку</h3>
                    <p class="newsletter-description">Отримуйте останні новини, акції та корисні поради від StormHosting UA</p>
                </div>
                <div class="col-lg-4">
                    <form class="newsletter-form" id="newsletterForm">
                        <div class="form-group">
                            <input type="email" class="form-control" placeholder="Ваш email" required>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-envelope"></i>
                                Підписатися
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content text-center">
            <h2 class="cta-title">Готові почати?</h2>
            <p class="cta-description">Приєднуйтесь до тисяч задоволених клієнтів StormHosting UA вже сьогодні</p>
            <div class="cta-buttons">
                <a href="/pages/hosting/shared.php" class="btn btn-primary btn-lg">
                    <i class="bi bi-rocket-takeoff"></i>
                    Почати зараз
                </a>
                <a href="/pages/info/contacts.php" class="btn btn-outline-light btn-lg">
                    <i class="bi bi-chat-dots"></i>
                    Зв'язатися з нами
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Модальное окно для новости -->
<div class="modal fade" id="newsModal" tabindex="-1" aria-labelledby="newsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newsModalLabel">
                    <i class="bi bi-newspaper me-2"></i>
                    <span id="newsModalTitle">Завантаження...</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="newsModalBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Завантаження...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для всех новостей -->
<div class="modal fade" id="allNewsModal" tabindex="-1" aria-labelledby="allNewsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="allNewsModalLabel">
                    <i class="bi bi-newspaper me-2"></i>
                    Всі новини StormHosting UA
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="allNewsModalBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Завантаження...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Стили для модального окна новостей */
#newsModal .modal-content,
#allNewsModal .modal-content {
    border-radius: 16px;
    border: none;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

#newsModal .modal-header,
#allNewsModal .modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 16px 16px 0 0;
    border-bottom: none;
    padding: 1.5rem;
}

#newsModal .modal-header .btn-close,
#allNewsModal .modal-header .btn-close {
    filter: brightness(0) invert(1);
}

#newsModalBody img {
    max-width: 100%;
    height: auto;
    border-radius: 12px;
    margin-bottom: 1.5rem;
}

.news-modal-image {
    width: 100%;
    height: 300px;
    object-fit: cover;
    border-radius: 12px;
    margin-bottom: 1.5rem;
}

.news-modal-meta {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e2e8f0;
}

.news-modal-content {
    font-size: 1.05rem;
    line-height: 1.8;
    color: #333;
}

.news-modal-content h1,
.news-modal-content h2,
.news-modal-content h3 {
    margin-top: 1.5rem;
    margin-bottom: 1rem;
}

.news-modal-content p {
    margin-bottom: 1rem;
}

.all-news-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.all-news-item {
    background: #f8fafc;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
    cursor: pointer;
    border: 1px solid #e2e8f0;
}

.all-news-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.15);
    border-color: #667eea;
}

.all-news-item img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.all-news-item-placeholder {
    width: 100%;
    height: 200px;
    background: linear-gradient(45deg, #f1f5f9, #e2e8f0);
    display: flex;
    align-items: center;
    justify-content: center;
}

.all-news-item-placeholder i {
    font-size: 3rem;
    color: #94a3b8;
}

.all-news-item-content {
    padding: 1.5rem;
}

.all-news-item-date {
    color: #666;
    font-size: 0.85rem;
    margin-bottom: 0.75rem;
}

.all-news-item-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1a1a1a;
    margin-bottom: 0.75rem;
}

.all-news-item-excerpt {
    color: #666;
    font-size: 0.9rem;
    line-height: 1.6;
}
</style>

<!-- JavaScript для анимаций и интерактивности -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Анимация счетчиков
    const animateCounters = () => {
        const counters = document.querySelectorAll('.stat-number[data-count]');
        
        counters.forEach(counter => {
            const target = parseInt(counter.getAttribute('data-count'));
            const increment = target / 100;
            let current = 0;
            
            const updateCounter = () => {
                if (current < target) {
                    current += increment;
                    counter.textContent = Math.ceil(current);
                    requestAnimationFrame(updateCounter);
                } else {
                    counter.textContent = target;
                }
            };
            
            updateCounter();
        });
    };
    
    // Запуск анимации счетчиков при скролле
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounters();
                observer.disconnect();
            }
        });
    });
    
    const heroStats = document.querySelector('.hero-stats');
    if (heroStats) {
        observer.observe(heroStats);
    }
    
    // Анимация бегущей строки
    const ticker = document.querySelector('.ticker-items');
    if (ticker) {
        let position = 0;
        const speed = 1;
        
        const animateTicker = () => {
            position -= speed;
            if (position <= -ticker.scrollWidth) {
                position = 0;
            }
            ticker.style.transform = `translateX(${position}px)`;
            requestAnimationFrame(animateTicker);
        };
        
        animateTicker();
    }
    
    // Обработка формы подписки
    const newsletterForm = document.getElementById('newsletterForm');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = this.querySelector('input[type="email"]').value;
            
            // Здесь будет AJAX запрос на сервер
            fetch('/api/newsletter/subscribe.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email: email })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Дякуємо за підписку!');
                    this.reset();
                } else {
                    alert('Помилка при підписці. Спробуйте ще раз.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Помилка при підписці. Спробуйте ще раз.');
            });
        });
    }
    
    // Плавная прокрутка для якорных ссылок
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // ============================================================================
    // КЛИКАБЕЛЬНЫЕ КАРТОЧКИ
    // ============================================================================

    // Делаем карточки услуг кликабельными
    document.querySelectorAll('.service-card[data-href], .domain-card[data-href], .hosting-plan[data-href]').forEach(card => {
        card.addEventListener('click', function(e) {
            // Не переходить если кликнули по ссылке или кнопке внутри карточки
            if (e.target.tagName === 'A' || e.target.tagName === 'BUTTON' || e.target.closest('a') || e.target.closest('button')) {
                return;
            }

            const href = this.getAttribute('data-href');
            if (href) {
                window.location.href = href;
            }
        });
    });

    // ============================================================================
    // МОДАЛЬНЫЕ ОКНА НОВОСТЕЙ
    // ============================================================================

    // Функция для загрузки одной новости
    async function loadNewsModal(newsId) {
        const modal = new bootstrap.Modal(document.getElementById('newsModal'));
        const modalTitle = document.getElementById('newsModalTitle');
        const modalBody = document.getElementById('newsModalBody');

        // Показываем модальное окно с загрузкой
        modalTitle.textContent = 'Завантаження...';
        modalBody.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Завантаження...</span>
                </div>
            </div>
        `;
        modal.show();

        try {
            // Загружаем новость через AJAX
            const response = await fetch(`/api/news/get.php?id=${newsId}`);
            const data = await response.json();

            if (data.success && data.news) {
                const news = data.news;
                modalTitle.textContent = news.title;

                let imageHtml = '';
                if (news.image) {
                    imageHtml = `<img src="${escapeHtml(news.image)}" alt="${escapeHtml(news.title)}" class="news-modal-image">`;
                }

                modalBody.innerHTML = `
                    ${imageHtml}
                    <div class="news-modal-meta">
                        <i class="bi bi-calendar3 me-2"></i>
                        ${formatDate(news.created_at)}
                    </div>
                    <div class="news-modal-content">
                        ${news.content}
                    </div>
                `;
            } else {
                modalBody.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Не вдалося завантажити новину
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error loading news:', error);
            modalBody.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Помилка завантаження новини
                </div>
            `;
        }
    }

    // Функция для загрузки всех новостей
    async function loadAllNewsModal() {
        const modal = new bootstrap.Modal(document.getElementById('allNewsModal'));
        const modalBody = document.getElementById('allNewsModalBody');

        // Показываем модальное окно с загрузкой
        modalBody.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Завантаження...</span>
                </div>
            </div>
        `;
        modal.show();

        try {
            // Загружаем все новости через AJAX
            const response = await fetch('/api/news/list.php');
            const data = await response.json();

            if (data.success && data.news && data.news.length > 0) {
                let newsHtml = '<div class="all-news-grid">';

                data.news.forEach(news => {
                    let imageHtml = '';
                    if (news.image) {
                        imageHtml = `<img src="${escapeHtml(news.image)}" alt="${escapeHtml(news.title)}">`;
                    } else {
                        imageHtml = `
                            <div class="all-news-item-placeholder">
                                <i class="bi bi-newspaper"></i>
                            </div>
                        `;
                    }

                    const excerpt = news.content.replace(/<[^>]*>/g, '').substring(0, 120);

                    newsHtml += `
                        <div class="all-news-item" data-news-id="${news.id}">
                            ${imageHtml}
                            <div class="all-news-item-content">
                                <div class="all-news-item-date">
                                    <i class="bi bi-calendar3 me-1"></i>
                                    ${formatDate(news.created_at)}
                                </div>
                                <div class="all-news-item-title">${escapeHtml(news.title)}</div>
                                <div class="all-news-item-excerpt">${escapeHtml(excerpt)}...</div>
                            </div>
                        </div>
                    `;
                });

                newsHtml += '</div>';
                modalBody.innerHTML = newsHtml;

                // Добавляем обработчики кликов для элементов списка новостей
                document.querySelectorAll('#allNewsModal .all-news-item').forEach(item => {
                    item.addEventListener('click', function() {
                        const newsId = this.getAttribute('data-news-id');
                        bootstrap.Modal.getInstance(document.getElementById('allNewsModal')).hide();
                        setTimeout(() => loadNewsModal(newsId), 300);
                    });
                });
            } else {
                modalBody.innerHTML = `
                    <div class="alert alert-info text-center">
                        <i class="bi bi-info-circle me-2"></i>
                        Немає доступних новин
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error loading news list:', error);
            modalBody.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Помилка завантаження новин
                </div>
            `;
        }
    }

    // Обработчики кликов по карточкам новостей
    document.querySelectorAll('.news-card[data-news-id]').forEach(card => {
        card.addEventListener('click', function(e) {
            // Не открывать если кликнули по ссылке
            if (e.target.tagName === 'A' || e.target.closest('a')) {
                e.preventDefault();
            }

            const newsId = this.getAttribute('data-news-id');
            loadNewsModal(newsId);
        });
    });

    // Обработчик кнопки "Всі новини"
    const showAllNewsBtn = document.getElementById('showAllNewsBtn');
    if (showAllNewsBtn) {
        showAllNewsBtn.addEventListener('click', function(e) {
            e.preventDefault();
            loadAllNewsModal();
        });
    }

    // Вспомогательные функции
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        return `${day}.${month}.${year}`;
    }
});
</script>


<script>
function orderService(serviceType, planId, planName, price) {
    // Показать загрузку
    showLoading('Створення замовлення...');
    
    // Отправить на сервер
    fetch('/api/create_order.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': getCsrfToken()
        },
        body: JSON.stringify({
            service_type: serviceType,
            plan_id: planId,
            plan_name: planName,
            price: price
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            // Перенаправление на FOSSBilling
            window.location.href = data.payment_url;
        } else {
            showError(data.message);
        }
    })
    .catch(error => {
        hideLoading();
        showError('Помилка створення замовлення');
    });
}

// Обновить кнопки доменов
document.querySelectorAll('.domain-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const card = this.closest('.domain-card');
        const zone = card.querySelector('.domain-zone').textContent;
        const price = parseFloat(card.querySelector('.domain-price').textContent);
        
        orderService('domain_registration', 0, 'Домен ' + zone, price);
    });
});

// Обновить кнопки хостинга
document.querySelectorAll('.plan-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const planId = this.dataset.planId;
        const planName = this.dataset.planName;
        const price = this.dataset.price;
        
        orderService('shared_hosting', planId, planName, price);
    });
});
</script>
<script src="/assets/js/security-protection.js"></script>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>