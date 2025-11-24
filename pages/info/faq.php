<?php
// Захист від прямого доступу
define('SECURE_ACCESS', true);

// Конфігурація сторінки
$page = 'faq';
$page_title = 'База знань - StormHosting UA | FAQ та інструкції з хостингу';
$meta_description = 'База знань StormHosting UA: відповіді на популярні питання, інструкції з хостингу, VPS, доменів, ISPmanager. Повна Wiki з налаштування послуг.';
$meta_keywords = 'база знань, faq, інструкції хостинг, налаштування vps, ispmanager, домени dns, ssl сертифікати';

// Додаткові CSS та JS файли для цієї сторінки
$additional_css = [
    '/assets/css/pages/info-faq.css'
];

$additional_js = [
    '/assets/js/info-faq.js'
];

// Підключення конфігурації та БД
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';

// Підключення header
include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';

// Категорії FAQ/Wiki
$knowledge_categories = [
    'hosting' => [
        'title' => 'Веб-хостинг',
        'icon' => 'bi-server',
        'color' => 'primary',
        'description' => 'Інформація про тарифи, налаштування та керування хостингом',
        'articles_count' => 24,
        'popular_tags' => ['cPanel', 'PHP', 'MySQL', 'Email', 'FTP'],
        'articles' => [
            [
                'id' => 'hosting-setup',
                'title' => 'Як налаштувати хостинг після замовлення?',
                'difficulty' => 'beginner',
                'views' => 2847,
                'likes' => 156,
                'updated' => '2024-01-15',
                'content' => 'Детальна інструкція з налаштування хостингу...'
            ],
            [
                'id' => 'php-versions',
                'title' => 'Як змінити версію PHP на хостингу?',
                'difficulty' => 'intermediate',
                'views' => 1924,
                'likes' => 89,
                'updated' => '2024-01-12',
                'content' => 'Інструкція зі зміни версії PHP...'
            ],
            [
                'id' => 'email-setup',
                'title' => 'Налаштування поштових скриньок',
                'difficulty' => 'beginner',
                'views' => 3156,
                'likes' => 201,
                'updated' => '2024-01-10',
                'content' => 'Як створити та налаштувати email...'
            ]
        ]
    ],
    'vps' => [
        'title' => 'VPS / VDS',
        'icon' => 'bi-hdd-stack',
        'color' => 'success',
        'description' => 'Налаштування, адміністрування та безпека VPS серверів',
        'articles_count' => 18,
        'popular_tags' => ['Linux', 'Ubuntu', 'CentOS', 'SSH', 'Firewall'],
        'articles' => [
            [
                'id' => 'vps-first-setup',
                'title' => 'Перше налаштування VPS сервера',
                'difficulty' => 'advanced',
                'views' => 1847,
                'likes' => 124,
                'updated' => '2024-01-14',
                'content' => 'Початкове налаштування VPS...'
            ],
            [
                'id' => 'ssh-security',
                'title' => 'Налаштування SSH та безпека',
                'difficulty' => 'advanced',
                'views' => 1234,
                'likes' => 87,
                'updated' => '2024-01-11',
                'content' => 'Як захистити SSH доступ...'
            ]
        ]
    ],
    'domains' => [
        'title' => 'Домени',
        'icon' => 'bi-globe',
        'color' => 'warning',
        'description' => 'Реєстрація, перенесення та налаштування доменних імен',
        'articles_count' => 15,
        'popular_tags' => ['DNS', 'Nameservers', 'Transfer', 'WHOIS', 'Subdomain'],
        'articles' => [
            [
                'id' => 'domain-dns',
                'title' => 'Налаштування DNS записів домену',
                'difficulty' => 'intermediate',
                'views' => 2456,
                'likes' => 143,
                'updated' => '2024-01-13',
                'content' => 'Як налаштувати DNS записи...'
            ],
            [
                'id' => 'domain-transfer',
                'title' => 'Перенесення домену до StormHosting',
                'difficulty' => 'intermediate',
                'views' => 1678,
                'likes' => 98,
                'updated' => '2024-01-09',
                'content' => 'Покрокова інструкція трансферу...'
            ]
        ]
    ],
    'ssl' => [
        'title' => 'SSL сертифікати',
        'icon' => 'bi-shield-lock',
        'color' => 'info',
        'description' => 'Встановлення, налаштування та оновлення SSL',
        'articles_count' => 12,
        'popular_tags' => ['Let\'s Encrypt', 'Wildcard', 'Installation', 'HTTPS', 'Security'],
        'articles' => [
            [
                'id' => 'ssl-install',
                'title' => 'Встановлення SSL сертифіката',
                'difficulty' => 'beginner',
                'views' => 3247,
                'likes' => 189,
                'updated' => '2024-01-16',
                'content' => 'Як встановити SSL сертифікат...'
            ]
        ]
    ],
    'billing' => [
        'title' => 'Особистий кабінет та послуги',
        'icon' => 'bi-person-circle',
        'color' => 'danger',
        'description' => 'Керування акаунтом, тарифами та оплатами',
        'articles_count' => 21,
        'popular_tags' => ['Account', 'Payment', 'Invoices', 'Upgrade', 'Support'],
        'articles' => [
            [
                'id' => 'account-setup',
                'title' => 'Реєстрація та налаштування акаунта',
                'difficulty' => 'beginner',
                'views' => 4156,
                'likes' => 234,
                'updated' => '2024-01-17',
                'content' => 'Як створити акаунт...'
            ]
        ]
    ],
    'dns' => [
        'title' => 'Доменні імена та DNS',
        'icon' => 'bi-diagram-3',
        'color' => 'secondary',
        'description' => 'Керування зонами DNS та налаштування записів',
        'articles_count' => 16,
        'popular_tags' => ['A Record', 'CNAME', 'MX', 'NS', 'TXT'],
        'articles' => []
    ],
    'ispmanager' => [
        'title' => 'Панель ISPmanager 6.2',
        'icon' => 'bi-gear-wide-connected',
        'color' => 'dark',
        'description' => 'Інструкції по роботі з панеллю керування ISPmanager',
        'articles_count' => 28,
        'popular_tags' => ['Control Panel', 'Websites', 'Databases', 'Backup', 'Monitoring'],
        'articles' => []
    ],
    'apps' => [
        'title' => 'Технології та сторонні додатки',
        'icon' => 'bi-puzzle',
        'color' => 'info',
        'description' => 'Робота з CMS, модулями та іншими додатками',
        'articles_count' => 31,
        'popular_tags' => ['WordPress', 'Joomla', 'OpenCart', 'Laravel', 'Node.js'],
        'articles' => []
    ]
];

// Популярні питання для головної
$popular_questions = [
    [
        'question' => 'Як швидко активується хостинг після оплати?',
        'answer' => 'Хостинг активується автоматично протягом 1-5 хвилин після підтвердження оплати.',
        'category' => 'hosting',
        'views' => 8945
    ],
    [
        'question' => 'Чи можу я змінити тарифний план?',
        'answer' => 'Так, ви можете в будь-який час змінити тарифний план через особистий кабінет.',
        'category' => 'billing',
        'views' => 6234
    ],
    [
        'question' => 'Як налаштувати email на мобільному?',
        'answer' => 'Використовуйте IMAP/SMTP налаштування з нашої інструкції для налаштування пошти.',
        'category' => 'hosting',
        'views' => 5678
    ],
    [
        'question' => 'Чи надаєте ви безкоштовний SSL?',
        'answer' => 'Так, ми надаємо безкоштовні SSL сертифікати Let\'s Encrypt для всіх доменів.',
        'category' => 'ssl',
        'views' => 4892
    ]
];
?>

<!-- Додаткові стилі для цієї сторінки -->
<?php if (isset($additional_css)): ?>
    <?php foreach ($additional_css as $css_file): ?>
        <link rel="stylesheet" href="<?php echo $css_file; ?>">
    <?php endforeach; ?>
<?php endif; ?>

<!-- FAQ Hero Section -->
<section class="faq-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-content">
                    <div class="faq-badge mb-3">
                        <i class="bi bi-book"></i>
                        <span>Повна база знань</span>
                    </div>
                    <h1 class="display-4 fw-bold text-white mb-4">
                        База знань StormHosting UA
                    </h1>
                    <p class="lead text-white-50 mb-4">
                        Відповіді на найпопулярніші запитання та детальні інструкції 
                        по роботі з нашими послугами. Все що потрібно знати про хостинг!
                    </p>
                    
                    <!-- Поиск -->
                    <div class="search-container">
                        <div class="search-box">
                            <i class="bi bi-search search-icon"></i>
                            <input type="text" id="knowledgeSearch" placeholder="Пошук в базі знань..." autocomplete="off">
                            <div class="search-suggestions" id="searchSuggestions"></div>
                        </div>
                        <button class="search-btn" onclick="performSearch()">
                            <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                    
                    <!-- Популярные поисковые запросы -->
                    <div class="popular-searches">
                        <span class="popular-label">Популярні запити:</span>
                        <button class="popular-tag" onclick="searchFor('SSL налаштування')">SSL налаштування</button>
                        <button class="popular-tag" onclick="searchFor('DNS записи')">DNS записи</button>
                        <button class="popular-tag" onclick="searchFor('Email пошта')">Email пошта</button>
                        <button class="popular-tag" onclick="searchFor('WordPress')">WordPress</button>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="hero-visual">
                    <div class="knowledge-stats">
                        <div class="stats-header">
                            <h6>Статистика бази знань</h6>
                            <div class="update-indicator">
                                <span class="update-dot"></span>
                                Оновлено сьогодні
                            </div>
                        </div>
                        
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-number">165</div>
                                <div class="stat-label">Статей</div>
                                <div class="stat-icon">
                                    <i class="bi bi-file-text"></i>
                                </div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-number">8</div>
                                <div class="stat-label">Категорій</div>
                                <div class="stat-icon">
                                    <i class="bi bi-collection"></i>
                                </div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-number">24k+</div>
                                <div class="stat-label">Переглядів</div>
                                <div class="stat-icon">
                                    <i class="bi bi-eye"></i>
                                </div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-number">96%</div>
                                <div class="stat-label">Корисність</div>
                                <div class="stat-icon">
                                    <i class="bi bi-hand-thumbs-up"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="quick-help">
                            <div class="help-title">Швидка допомога</div>
                            <div class="help-buttons">
                                <button class="help-btn" onclick="openLiveChat()">
                                    <i class="bi bi-chat-dots"></i>
                                    <span>Чат з підтримкою</span>
                                </button>
                                <button class="help-btn" onclick="requestCallback()">
                                    <i class="bi bi-telephone"></i>
                                    <span>Зворотний дзвінок</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Popular Questions Section -->
<section class="popular-questions py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">🔥 Популярні питання</h2>
            <p class="lead text-muted">Найчастіше запитувані питання від наших користувачів</p>
        </div>
        
        <div class="questions-grid">
            <?php foreach ($popular_questions as $index => $q): ?>
                <div class="question-card" data-category="<?php echo $q['category']; ?>">
                    <div class="question-header">
                        <h5 class="question-title"><?php echo $q['question']; ?></h5>
                        <div class="question-meta">
                            <span class="views-count">
                                <i class="bi bi-eye"></i>
                                <?php echo number_format($q['views']); ?>
                            </span>
                            <button class="expand-btn" onclick="toggleQuestion(this)">
                                <i class="bi bi-chevron-down"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="question-answer">
                        <p><?php echo $q['answer']; ?></p>
                        <div class="answer-actions">
                            <button class="action-btn helpful" onclick="markHelpful(this)">
                                <i class="bi bi-hand-thumbs-up"></i>
                                Корисно
                            </button>
                            <button class="action-btn share" onclick="shareQuestion(<?php echo $index; ?>)">
                                <i class="bi bi-share"></i>
                                Поділитися
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-4">
            <button class="btn btn-outline-primary btn-lg" onclick="showAllQuestions()">
                <i class="bi bi-plus-circle me-2"></i>
                Показати всі питання
            </button>
        </div>
    </div>
</section>

<!-- Knowledge Categories Section -->
<section class="categories-section py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">📚 Категорії знань</h2>
            <p class="lead text-muted">Оберіть категорію для детального вивчення</p>
        </div>
        
        <div class="categories-grid">
            <?php foreach ($knowledge_categories as $key => $category): ?>
                <div class="category-card" data-category="<?php echo $key; ?>" onclick="openCategory('<?php echo $key; ?>')">
                    <div class="category-header">
                        <div class="category-icon text-<?php echo $category['color']; ?>">
                            <i class="<?php echo $category['icon']; ?>"></i>
                        </div>
                        <div class="article-count">
                            <?php echo $category['articles_count']; ?> статей
                        </div>
                    </div>
                    
                    <div class="category-content">
                        <h4 class="category-title"><?php echo $category['title']; ?></h4>
                        <p class="category-description"><?php echo $category['description']; ?></p>
                        
                        <div class="popular-tags">
                            <?php foreach (array_slice($category['popular_tags'], 0, 3) as $tag): ?>
                                <span class="tag"><?php echo $tag; ?></span>
                            <?php endforeach; ?>
                            <?php if (count($category['popular_tags']) > 3): ?>
                                <span class="tag more">+<?php echo count($category['popular_tags']) - 3; ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="category-footer">
                        <div class="category-stats">
                            <span class="stat">
                                <i class="bi bi-eye"></i>
                                <?php echo rand(1000, 9999); ?>
                            </span>
                            <span class="stat">
                                <i class="bi bi-heart"></i>
                                <?php echo rand(100, 999); ?>
                            </span>
                        </div>
                        <div class="category-arrow">
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Articles Section -->
<section class="featured-articles py-5">
    <div class="container">
        <div class="section-header">
            <div class="section-title">
                <h2 class="display-5 fw-bold">⭐ Рекомендовані статті</h2>
                <p class="lead text-muted">Найкорисніші матеріали від наших експертів</p>
            </div>
            <div class="section-actions">
                <div class="view-toggle">
                    <button class="toggle-btn active" data-view="grid" onclick="toggleView('grid')">
                        <i class="bi bi-grid-3x3-gap"></i>
                    </button>
                    <button class="toggle-btn" data-view="list" onclick="toggleView('list')">
                        <i class="bi bi-list"></i>
                    </button>
                </div>
                <div class="sort-dropdown">
                    <select class="form-select" onchange="sortArticles(this.value)">
                        <option value="popular">Популярні</option>
                        <option value="recent">Нові</option>
                        <option value="helpful">Корисні</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="articles-container" id="articlesContainer">
            <?php 
            $featured_articles = [];
            foreach ($knowledge_categories as $cat_key => $category) {
                foreach ($category['articles'] as $article) {
                    $article['category'] = $cat_key;
                    $article['category_info'] = $category;
                    $featured_articles[] = $article;
                }
            }
            
            foreach ($featured_articles as $article): 
            ?>
                <div class="article-card" data-difficulty="<?php echo $article['difficulty']; ?>" data-category="<?php echo $article['category']; ?>">
                    <div class="article-header">
                        <div class="article-meta">
                            <span class="category-badge bg-<?php echo $article['category_info']['color']; ?>">
                                <i class="<?php echo $article['category_info']['icon']; ?>"></i>
                                <?php echo $article['category_info']['title']; ?>
                            </span>
                            <span class="difficulty difficulty-<?php echo $article['difficulty']; ?>">
                                <?php 
                                $difficulty_labels = [
                                    'beginner' => 'Початківець',
                                    'intermediate' => 'Середній',
                                    'advanced' => 'Експерт'
                                ];
                                echo $difficulty_labels[$article['difficulty']];
                                ?>
                            </span>
                        </div>
                        <div class="article-actions">
                            <button class="action-btn bookmark" onclick="toggleBookmark(this)" title="Додати в закладки">
                                <i class="bi bi-bookmark"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="article-content">
                        <h5 class="article-title"><?php echo $article['title']; ?></h5>
                        <p class="article-excerpt"><?php echo substr($article['content'], 0, 120) . '...'; ?></p>
                        
                        <div class="article-stats">
                            <div class="stat-item">
                                <i class="bi bi-eye"></i>
                                <span><?php echo number_format($article['views']); ?></span>
                            </div>
                            <div class="stat-item">
                                <i class="bi bi-heart"></i>
                                <span><?php echo $article['likes']; ?></span>
                            </div>
                            <div class="stat-item">
                                <i class="bi bi-calendar"></i>
                                <span><?php echo date('d.m.Y', strtotime($article['updated'])); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="article-footer">
                        <button class="btn btn-primary btn-sm" onclick="openArticle('<?php echo $article['id']; ?>')">
                            <i class="bi bi-arrow-right"></i>
                            Читати статтю
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Help Center Section -->
<section class="help-center py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h3 class="mb-3">Не знайшли відповідь на своє питання?</h3>
                <p class="mb-4">
                    Наша служба підтримки працює 24/7 і готова допомогти з будь-якими питаннями. 
                    Також ви можете запропонувати нову тему для бази знань.
                </p>
                
                <div class="help-options">
                    <div class="help-option">
                        <i class="bi bi-chat-dots-fill"></i>
                        <div>
                            <strong>Онлайн чат</strong>
                            <small>Миттєва відповідь</small>
                        </div>
                    </div>
                    <div class="help-option">
                        <i class="bi bi-envelope-fill"></i>
                        <div>
                            <strong>Email підтримка</strong>
                            <small>Протягом 4 годин</small>
                        </div>
                    </div>
                    <div class="help-option">
                        <i class="bi bi-telephone-fill"></i>
                        <div>
                            <strong>Телефон</strong>
                            <small>+380 (67) 123-45-67</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="/pages/contacts.php" class="btn btn-light btn-lg me-2">
                    <i class="bi bi-headset me-2"></i>
                    Зв'язатися з підтримкою
                </a>
                <button class="btn btn-outline-light btn-lg" onclick="suggestTopic()">
                    <i class="bi bi-lightbulb me-2"></i>
                    Запропонувати тему
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Article Modal -->
<div class="modal fade" id="articleModal" tabindex="-1" aria-labelledby="articleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <div class="article-modal-header">
                    <div class="modal-breadcrumb">
                        <span class="breadcrumb-item" id="modalCategory"></span>
                        <i class="bi bi-chevron-right"></i>
                        <span class="breadcrumb-item active" id="modalTitle"></span>
                    </div>
                    <div class="modal-tools">
                        <button class="tool-btn" onclick="printArticle()" title="Друк">
                            <i class="bi bi-printer"></i>
                        </button>
                        <button class="tool-btn" onclick="shareArticle()" title="Поділитися">
                            <i class="bi bi-share"></i>
                        </button>
                        <button class="tool-btn" onclick="toggleBookmark(this)" title="Закладка">
                            <i class="bi bi-bookmark"></i>
                        </button>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="article-content-container">
                    <div class="article-sidebar">
                        <div class="table-of-contents">
                            <h6>Зміст статті</h6>
                            <ul id="articleTOC">
                                <!-- Динамически генерируется JS -->
                            </ul>
                        </div>
                        
                        <div class="article-info">
                            <div class="info-item">
                                <span class="info-label">Складність:</span>
                                <span class="info-value" id="articleDifficulty"></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Час читання:</span>
                                <span class="info-value" id="readingTime"></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Оновлено:</span>
                                <span class="info-value" id="lastUpdated"></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="article-main">
                        <div class="article-body" id="articleBody">
                            <!-- Содержимое статьи загружается динамически -->
                        </div>
                        
                        <div class="article-feedback">
                            <h6>Чи була ця стаття корисною?</h6>
                            <div class="feedback-buttons">
                                <button class="feedback-btn positive" onclick="submitFeedback('positive')">
                                    <i class="bi bi-hand-thumbs-up"></i>
                                    Так, корисно
                                </button>
                                <button class="feedback-btn negative" onclick="submitFeedback('negative')">
                                    <i class="bi bi-hand-thumbs-down"></i>
                                    Потрібно покращити
                                </button>
                            </div>
                            <div class="feedback-form" id="feedbackForm" style="display: none;">
                                <textarea class="form-control" placeholder="Розкажіть, що можна покращити..."></textarea>
                                <button class="btn btn-primary btn-sm mt-2" onclick="sendDetailedFeedback()">
                                    Відправити відгук
                                </button>
                            </div>
                        </div>
                        
                        <div class="related-articles">
                            <h6>Схожі статті</h6>
                            <div class="related-list" id="relatedArticles">
                                <!-- Заповнюється JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Suggest Topic Modal -->
<div class="modal fade" id="suggestModal" tabindex="-1" aria-labelledby="suggestModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="suggestModalLabel">
                    <i class="bi bi-lightbulb me-2"></i>
                    Запропонувати нову тему
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="suggestForm">
                    <div class="mb-3">
                        <label for="topicTitle" class="form-label">Назва теми</label>
                        <input type="text" class="form-control" id="topicTitle" placeholder="Наприклад: Як налаштувати CRON завдання" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="topicCategory" class="form-label">Категорія</label>
                        <select class="form-select" id="topicCategory" required>
                            <option value="">Оберіть категорію</option>
                            <?php foreach ($knowledge_categories as $key => $category): ?>
                                <option value="<?php echo $key; ?>"><?php echo $category['title']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="topicDescription" class="form-label">Опис проблеми</label>
                        <textarea class="form-control" id="topicDescription" rows="4" 
                                  placeholder="Детально опишіть, що саме ви хочете дізнатися..." required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="topicPriority" class="form-label">Пріоритет</label>
                        <select class="form-select" id="topicPriority">
                            <option value="low">Низький</option>
                            <option value="medium" selected>Середній</option>
                            <option value="high">Високий</option>
                            <option value="urgent">Терміновий</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="contactEmail" class="form-label">Email для зв'язку</label>
                        <input type="email" class="form-control" id="contactEmail" 
                               placeholder="your@email.com" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Скасувати</button>
                <button type="button" class="btn btn-primary" onclick="submitSuggestion()">
                    <i class="bi bi-send"></i>
                    Відправити пропозицію
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Floating Action Button -->
<div class="floating-actions">
    <div class="fab-menu" id="fabMenu">
        <button class="fab-item" onclick="scrollToTop()" title="Вгору">
            <i class="bi bi-arrow-up"></i>
        </button>
        <button class="fab-item" onclick="toggleDarkMode()" title="Темна тема">
            <i class="bi bi-moon"></i>
        </button>
        <button class="fab-item" onclick="increaseFontSize()" title="Збільшити шрифт">
            <i class="bi bi-fonts"></i>
        </button>
    </div>
    <button class="fab-main" onclick="toggleFabMenu()">
        <i class="bi bi-gear"></i>
    </button>
</div>

<!-- Додаткові скрипти для цієї сторінки -->
<?php if (isset($additional_js)): ?>
    <?php foreach ($additional_js as $js_file): ?>
        <script src="<?php echo $js_file; ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
