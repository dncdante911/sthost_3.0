/**
 * DOM Helpers - Безопасная работа с DOM для предотвращения XSS
 * SECURITY FIX: Этот файл создан для защиты от XSS атак через innerHTML
 */

/**
 * Безопасная установка текстового контента
 * Использует textContent вместо innerHTML для защиты от XSS
 *
 * @param {HTMLElement|string} element - DOM элемент или селектор
 * @param {string} text - Текст для вставки
 */
function safeSetText(element, text) {
    const el = typeof element === 'string' ? document.querySelector(element) : element;
    if (el) {
        el.textContent = text;
    }
}

/**
 * Безопасная установка HTML контента с использованием DOMPurify
 * Требует подключения DOMPurify через CDN
 *
 * @param {HTMLElement|string} element - DOM элемент или селектор
 * @param {string} html - HTML для вставки
 * @param {Object} config - Конфигурация DOMPurify (опционально)
 */
function safeSetHTML(element, html, config = {}) {
    const el = typeof element === 'string' ? document.querySelector(element) : element;
    if (!el) return;

    // Проверяем наличие DOMPurify
    if (typeof DOMPurify !== 'undefined') {
        const defaultConfig = {
            ALLOWED_TAGS: ['b', 'i', 'em', 'strong', 'a', 'p', 'br', 'ul', 'ol', 'li', 'span', 'div'],
            ALLOWED_ATTR: ['href', 'target', 'class', 'id']
        };
        const finalConfig = { ...defaultConfig, ...config };
        el.innerHTML = DOMPurify.sanitize(html, finalConfig);
    } else {
        console.warn('DOMPurify not loaded. Using textContent as fallback.');
        el.textContent = html; // Fallback к безопасному методу
    }
}

/**
 * Создание элемента с безопасной установкой текста
 *
 * @param {string} tag - Тег элемента (div, span, p и т.д.)
 * @param {string} text - Текстовое содержимое
 * @param {Object} attributes - Атрибуты элемента (className, id и т.д.)
 * @return {HTMLElement} Созданный элемент
 */
function createSafeElement(tag, text = '', attributes = {}) {
    const el = document.createElement(tag);

    // Устанавливаем текст безопасно
    if (text) {
        el.textContent = text;
    }

    // Устанавливаем атрибуты
    for (const [key, value] of Object.entries(attributes)) {
        if (key === 'className') {
            el.className = value;
        } else if (key === 'style' && typeof value === 'object') {
            Object.assign(el.style, value);
        } else {
            el.setAttribute(key, value);
        }
    }

    return el;
}

/**
 * Безопасная вставка списка элементов
 *
 * @param {HTMLElement|string} container - Контейнер
 * @param {Array} items - Массив данных для вставки
 * @param {Function} renderFn - Функция рендеринга для каждого элемента
 */
function safeRenderList(container, items, renderFn) {
    const el = typeof container === 'string' ? document.querySelector(container) : container;
    if (!el) return;

    // Очищаем контейнер
    el.innerHTML = '';

    // Создаем фрагмент для оптимизации
    const fragment = document.createDocumentFragment();

    items.forEach((item, index) => {
        const element = renderFn(item, index);
        if (element) {
            fragment.appendChild(element);
        }
    });

    el.appendChild(fragment);
}

/**
 * Экранирование HTML специальных символов
 * Fallback метод когда DOMPurify недоступен
 *
 * @param {string} str - Строка для экранирования
 * @return {string} Экранированная строка
 */
function escapeHTML(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

/**
 * Безопасная установка атрибута href для ссылок
 * Блокирует javascript: протокол
 *
 * @param {HTMLElement} element - Элемент ссылки
 * @param {string} url - URL
 */
function safeSetHref(element, url) {
    // Блокируем опасные протоколы
    const dangerousProtocols = ['javascript:', 'data:', 'vbscript:'];
    const urlLower = url.toLowerCase().trim();

    for (const protocol of dangerousProtocols) {
        if (urlLower.startsWith(protocol)) {
            console.warn('Blocked dangerous protocol in URL:', url);
            return;
        }
    }

    element.href = url;
}

/**
 * Создание карточки новости с безопасной вставкой контента
 * Пример использования безопасных методов
 *
 * @param {Object} newsItem - Объект новости
 * @return {HTMLElement} DOM элемент карточки
 */
function createNewsCard(newsItem) {
    const card = document.createElement('div');
    card.className = 'news-card';

    // Безопасное создание изображения
    if (newsItem.image) {
        const img = document.createElement('img');
        img.src = newsItem.image; // src безопасен, браузер блокирует javascript:
        img.alt = newsItem.title || 'News image';
        card.appendChild(img);
    }

    // Безопасное создание заголовка
    const title = document.createElement('h3');
    title.textContent = newsItem.title;
    card.appendChild(title);

    // Безопасное создание контента
    const content = document.createElement('p');
    content.textContent = newsItem.content;
    card.appendChild(content);

    // Безопасное создание ссылки
    if (newsItem.url) {
        const link = document.createElement('a');
        link.textContent = 'Читать далее';
        link.className = 'read-more';
        safeSetHref(link, newsItem.url);
        card.appendChild(link);
    }

    return card;
}

// Экспорт для использования в других модулях (если используется ES6 modules)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        safeSetText,
        safeSetHTML,
        createSafeElement,
        safeRenderList,
        escapeHTML,
        safeSetHref,
        createNewsCard
    };
}
