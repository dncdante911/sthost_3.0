/**
 * StormHosting UA - Domain Registration Page JavaScript
 * Functionality for domain search, validation, and user interactions
 */

class DomainRegistration {
    constructor() {
        this.searchForm = null;
        this.searchResults = null;
        this.bulkSearchActive = false;
        this.searchCache = new Map();
        this.searchTimeout = null;
        
        this.init();
    }

    /**
     * Initialize all functionality
     */
    init() {
        this.cacheElements();
        this.bindEvents();
        this.initializeAnimations();
        this.setupValidation();
    }

    /**
     * Cache DOM elements for better performance
     */
    cacheElements() {
        this.searchForm = document.getElementById('domainSearchForm');
        this.domainInput = document.getElementById('domainName');
        this.zoneSelect = document.getElementById('domainZone');
        this.searchResults = document.getElementById('searchResults');
        this.bulkToggle = document.getElementById('toggleBulkSearch');
        this.csrfToken = document.getElementById('csrf_token');
        this.quickSearchButtons = document.querySelectorAll('.btn-check-domain');
        this.domainCards = document.querySelectorAll('.domain-card');
    }

    /**
     * Bind all event listeners
     */
    bindEvents() {
        // Main search form
        if (this.searchForm) {
            this.searchForm.addEventListener('submit', this.handleSearch.bind(this));
        }

        // Real-time domain validation
        if (this.domainInput) {
            this.domainInput.addEventListener('input', this.handleDomainInput.bind(this));
            this.domainInput.addEventListener('keydown', this.handleKeyDown.bind(this));
        }

        // Zone selector change
        if (this.zoneSelect) {
            this.zoneSelect.addEventListener('change', this.handleZoneChange.bind(this));
        }

        // Bulk search toggle
        if (this.bulkToggle) {
            this.bulkToggle.addEventListener('click', this.toggleBulkSearch.bind(this));
        }

        // Quick search buttons
        this.quickSearchButtons.forEach(button => {
            button.addEventListener('click', this.handleQuickSearch.bind(this));
        });

        // Domain card interactions
        this.domainCards.forEach(card => {
            card.addEventListener('mouseenter', this.handleCardHover.bind(this));
            card.addEventListener('mouseleave', this.handleCardLeave.bind(this));
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', this.handleGlobalKeyDown.bind(this));
    }

    /**
     * Handle domain input with real-time validation
     */
    handleDomainInput(event) {
        const value = event.target.value;
        
        // Clear previous timeout
        if (this.searchTimeout) {
            clearTimeout(this.searchTimeout);
        }

        // Real-time validation
        this.validateDomainInput(value);

        // Auto-search after user stops typing
        if (value.length >= 2) {
            this.searchTimeout = setTimeout(() => {
                if (this.domainInput.value === value) {
                    this.performQuickCheck(value);
                }
            }, 1000);
        }
    }

    /**
     * Validate domain input in real-time
     */
    validateDomainInput(value) {
        const input = this.domainInput;
        const isValid = this.isValidDomainName(value);
        
        // Update input styling
        input.classList.toggle('is-invalid', value.length > 0 && !isValid);
        input.classList.toggle('is-valid', value.length > 0 && isValid);

        // Show validation message
        this.showValidationFeedback(value, isValid);
    }

    /**
     * Check if domain name is valid
     */
    isValidDomainName(domain) {
        if (!domain || domain.length < 2 || domain.length > 63) return false;
        if (domain.startsWith('-') || domain.endsWith('-')) return false;
        if (domain.includes('--')) return false;
        
        const validPattern = /^[a-zA-Z0-9-]+$/;
        return validPattern.test(domain);
    }

    /**
     * Show validation feedback to user
     */
    showValidationFeedback(value, isValid) {
        // Remove existing feedback
        const existingFeedback = document.querySelector('.domain-validation-feedback');
        if (existingFeedback) {
            existingFeedback.remove();
        }

        if (value.length > 0 && !isValid) {
            const feedback = document.createElement('div');
            feedback.className = 'domain-validation-feedback';
            feedback.innerHTML = `
                <div class="validation-message error">
                    <i class="bi bi-exclamation-triangle"></i>
                    <span>Недопустимі символи або формат доменного імені</span>
                </div>
            `;
            
            this.domainInput.parentNode.appendChild(feedback);
        }
    }

    /**
     * Handle keyboard navigation
     */
    handleKeyDown(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            this.handleSearch(event);
        }
    }

    /**
     * Handle global keyboard shortcuts
     */
    handleGlobalKeyDown(event) {
        // Ctrl/Cmd + / to focus search
        if ((event.ctrlKey || event.metaKey) && event.key === '/') {
            event.preventDefault();
            this.domainInput?.focus();
        }

        // Escape to clear search
        if (event.key === 'Escape') {
            this.clearSearch();
        }
    }

    /**
     * Handle zone selector change
     */
    handleZoneChange(event) {
        const selectedOption = event.target.selectedOptions[0];
        const price = selectedOption.dataset.price;
        const renewal = selectedOption.dataset.renewal;

        // Update price display if needed
        this.updatePriceDisplay(price, renewal);

        // Re-search if domain is entered
        if (this.domainInput.value.trim()) {
            this.performQuickCheck(this.domainInput.value.trim());
        }
    }

    /**
     * Handle main search form submission
     */
    async handleSearch(event) {
        event.preventDefault();

        const domain = this.domainInput.value.trim();
        const zone = this.zoneSelect.value;

        if (!domain) {
            this.showError('Введіть ім\'я домену');
            this.domainInput.focus();
            return;
        }

        if (!this.isValidDomainName(domain)) {
            this.showError('Недопустимі символи в імені домену');
            this.domainInput.focus();
            return;
        }

        // Show loading state
        this.setSearchLoading(true);

        try {
            if (this.bulkSearchActive) {
                await this.performBulkSearch(domain);
            } else {
                await this.performSingleSearch(domain, zone);
            }
        } catch (error) {
            this.showError('Помилка при перевірці домену. Спробуйте ще раз.');
            console.error('Search error:', error);
        } finally {
            this.setSearchLoading(false);
        }
    }

    /**
     * Perform single domain search
     */
    async performSingleSearch(domain, zone) {
        const cacheKey = `${domain}${zone}`;
        
        // Check cache first
        if (this.searchCache.has(cacheKey)) {
            this.displaySingleResult(this.searchCache.get(cacheKey));
            return;
        }

        const formData = new FormData();
        formData.append('action', 'check_domain');
        formData.append('domain', domain);
        formData.append('zone', zone);
        formData.append('csrf_token', this.csrfToken.value);

        const response = await fetch(window.domainConfig.searchUrl, {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.error) {
            this.showError(result.error);
            return;
        }

        // Cache result
        this.searchCache.set(cacheKey, result);

        this.displaySingleResult(result);
    }

    /**
     * Perform bulk search across multiple zones
     */
    async performBulkSearch(domain) {
        const popularZones = ['.ua', '.com.ua', '.pp.ua', '.kiev.ua', '.net.ua', '.org.ua', '.com', '.net'];
        
        const formData = new FormData();
        formData.append('action', 'bulk_check');
        formData.append('domain', domain);
        formData.append('csrf_token', this.csrfToken.value);
        
        popularZones.forEach(zone => {
            formData.append('zones[]', zone);
        });

        const response = await fetch(window.domainConfig.searchUrl, {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.error) {
            this.showError(result.error);
            return;
        }

        this.displayBulkResults(result.results);
    }

    /**
     * Perform quick check without full form submission
     */
    async performQuickCheck(domain) {
        if (!this.isValidDomainName(domain)) return;

        const zone = this.zoneSelect.value;
        const cacheKey = `${domain}${zone}`;

        if (this.searchCache.has(cacheKey)) {
            this.showQuickResult(this.searchCache.get(cacheKey));
            return;
        }

        try {
            const formData = new FormData();
            formData.append('action', 'check_domain');
            formData.append('domain', domain);
            formData.append('zone', zone);
            formData.append('csrf_token', this.csrfToken.value);

            const response = await fetch(window.domainConfig.searchUrl, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (!result.error) {
                this.searchCache.set(cacheKey, result);
                this.showQuickResult(result);
            }
        } catch (error) {
            console.error('Quick check error:', error);
        }
    }

    /**
     * Handle quick search button clicks
     */
    handleQuickSearch(event) {
        const zone = event.target.dataset.zone;
        
        if (zone) {
            this.zoneSelect.value = zone;
            
            const domain = this.domainInput.value.trim();
            if (domain) {
                this.performSingleSearch(domain, zone);
            } else {
                this.domainInput.focus();
                this.domainInput.placeholder = `назва-домену${zone}`;
            }
        }
    }

    /**
     * Toggle bulk search mode
     */
    toggleBulkSearch() {
        this.bulkSearchActive = !this.bulkSearchActive;
        
        const toggleText = this.bulkSearchActive ? 
            'Повернутися до звичайного пошуку' : 
            'Перевірити у всіх популярних зонах';
            
        this.bulkToggle.innerHTML = `
            <i class="bi bi-${this.bulkSearchActive ? 'arrow-left' : 'list-check'}"></i>
            ${toggleText}
        `;

        // Update form appearance
        this.zoneSelect.style.display = this.bulkSearchActive ? 'none' : 'block';
        
        // Clear previous results
        this.clearSearchResults();
    }

    /**
     * Display single search result
     */
    displaySingleResult(result) {
        const statusClass = result.available ? 'result-available' : 'result-unavailable';
        const statusIcon = result.available ? 'check-circle' : 'x-circle';
        const statusText = result.available ? 'Доступний' : 'Зайнятий';

        const card = document.createElement('div');
        card.className = `search-result-card ${statusClass}`;

        // Header
        const header = document.createElement('div');
        header.className = 'result-header';

        const domainDiv = document.createElement('div');
        domainDiv.className = 'result-domain';
        domainDiv.textContent = result.domain;

        const statusDiv = document.createElement('div');
        statusDiv.className = `result-status ${result.available ? 'available' : 'unavailable'}`;

        const statusIcon_el = document.createElement('i');
        statusIcon_el.className = `bi bi-${statusIcon}`;

        const statusSpan = document.createElement('span');
        statusSpan.textContent = statusText;

        statusDiv.appendChild(statusIcon_el);
        statusDiv.appendChild(statusSpan);

        header.appendChild(domainDiv);
        header.appendChild(statusDiv);
        card.appendChild(header);

        // Body
        if (result.available) {
            // Details
            const details = document.createElement('div');
            details.className = 'result-details';

            const priceDiv = document.createElement('div');
            priceDiv.className = 'result-price';
            priceDiv.textContent = this.formatPrice(result.price) + ' / рік';

            const renewalDiv = document.createElement('div');
            renewalDiv.className = 'result-renewal';
            renewalDiv.textContent = 'Продовження: ' + this.formatPrice(result.renewal_price) + ' / рік';

            details.appendChild(priceDiv);
            details.appendChild(renewalDiv);
            card.appendChild(details);

            // Actions
            const actions = document.createElement('div');
            actions.className = 'result-actions';

            const registerBtn = document.createElement('button');
            registerBtn.className = 'btn-register';
            registerBtn.innerHTML = '<i class="bi bi-cart-plus"></i> Додати до кошика';
            registerBtn.addEventListener('click', () => {
                window.location.href = '/cart/add-domain?domain=' + encodeURIComponent(result.domain);
            });

            const detailsBtn = document.createElement('button');
            detailsBtn.className = 'btn btn-outline-primary';
            detailsBtn.innerHTML = '<i class="bi bi-info-circle"></i> Детальніше';

            const extraInfo = document.createElement('div');
            extraInfo.className = 'result-extra-info';
            extraInfo.style.display = 'none';
            extraInfo.innerHTML = `<h5>Що включено:</h5>
                        <ul>
                            <li>Безкоштовне керування DNS</li>
                            <li>Захист конфіденційності WHOIS</li>
                            <li>Автопродовження (опціонально)</li>
                            <li>Підтримка 24/7</li>
                        </ul>`;

            detailsBtn.addEventListener('click', () => {
                extraInfo.style.display = extraInfo.style.display === 'none' ? 'block' : 'none';
            });

            actions.appendChild(registerBtn);
            actions.appendChild(detailsBtn);
            card.appendChild(actions);
            card.appendChild(extraInfo);
        } else {
            // Unavailable message
            const message = document.createElement('div');
            message.className = 'result-message';
            const p = document.createElement('p');
            p.textContent = 'Цей домен уже зареєстрований. Спробуйте інше ім\'я або іншу доменну зону.';
            message.appendChild(p);
            card.appendChild(message);

            // Actions
            const actions = document.createElement('div');
            actions.className = 'result-actions';

            const whoisBtn = document.createElement('button');
            whoisBtn.className = 'btn btn-outline-primary';
            whoisBtn.innerHTML = '<i class="bi bi-search"></i> WHOIS інформація';
            whoisBtn.addEventListener('click', () => {
                window.open('/pages/domains/whois.php?domain=' + encodeURIComponent(result.domain), '_blank');
            });

            const altBtn = document.createElement('button');
            altBtn.className = 'btn btn-outline-secondary';
            altBtn.innerHTML = '<i class="bi bi-lightbulb"></i> Альтернативи';
            altBtn.addEventListener('click', () => {
                this.suggestAlternatives(result.domain);
            });

            actions.appendChild(whoisBtn);
            actions.appendChild(altBtn);
            card.appendChild(actions);
        }

        this.searchResults.innerHTML = '';
        this.searchResults.appendChild(card);
        this.searchResults.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    /**
     * Display bulk search results
     */
    displayBulkResults(results) {
        const bulkContainer = document.createElement('div');
        bulkContainer.className = 'bulk-results';

        results.forEach(result => {
            const item = document.createElement('div');
            item.className = `bulk-result-item ${result.available ? 'available' : 'unavailable'}`;

            // Header
            const header = document.createElement('div');
            header.className = 'bulk-result-header';

            const domainDiv = document.createElement('div');
            domainDiv.className = 'bulk-domain';
            domainDiv.textContent = result.domain;

            const statusDiv = document.createElement('div');
            statusDiv.className = `bulk-status ${result.available ? 'text-success' : 'text-danger'}`;

            const statusIcon = document.createElement('i');
            statusIcon.className = `bi bi-${result.available ? 'check-circle' : 'x-circle'}`;

            const statusText = document.createElement('span');
            statusText.textContent = result.available ? 'Доступний' : 'Зайнятий';

            statusDiv.appendChild(statusIcon);
            statusDiv.appendChild(statusText);

            header.appendChild(domainDiv);
            header.appendChild(statusDiv);
            item.appendChild(header);

            if (result.available) {
                const priceDiv = document.createElement('div');
                priceDiv.className = 'bulk-price';
                priceDiv.textContent = this.formatPrice(result.price) + ' / рік';
                item.appendChild(priceDiv);

                const btn = document.createElement('button');
                btn.className = 'btn btn-sm btn-primary w-100 mt-2';
                btn.innerHTML = '<i class="bi bi-cart-plus"></i> Додати до кошика';
                btn.addEventListener('click', () => {
                    window.location.href = '/cart/add-domain?domain=' + encodeURIComponent(result.domain);
                });
                item.appendChild(btn);
            } else {
                const unavailDiv = document.createElement('div');
                unavailDiv.className = 'bulk-unavailable';
                const small = document.createElement('small');
                small.className = 'text-muted';
                small.textContent = 'Недоступний для реєстрації';
                unavailDiv.appendChild(small);
                item.appendChild(unavailDiv);
            }

            bulkContainer.appendChild(item);
        });

        this.searchResults.innerHTML = '';
        this.searchResults.appendChild(bulkContainer);
        this.searchResults.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    /**
     * Show quick result hint
     */
    showQuickResult(result) {
        // Remove existing quick result
        const existing = document.querySelector('.quick-result-hint');
        if (existing) existing.remove();

        const hint = document.createElement('div');
        hint.className = `quick-result-hint ${result.available ? 'available' : 'unavailable'}`;
        hint.innerHTML = `
            <i class="bi bi-${result.available ? 'check-circle' : 'x-circle'}"></i>
            <span>${result.available ? 'Доступний' : 'Зайнятий'}</span>
            ${result.available ? `<span class="price">${this.formatPrice(result.price)}</span>` : ''}
        `;

        this.domainInput.parentNode.appendChild(hint);

        // Auto-remove after 3 seconds
        setTimeout(() => {
            if (hint.parentNode) {
                hint.remove();
            }
        }, 3000);
    }

    /**
     * Suggest domain alternatives
     */
    suggestAlternatives(domain) {
        const baseName = domain.split('.')[0];
        const suggestions = [
            `${baseName}-ua`,
            `${baseName}2024`,
            `${baseName}-pro`,
            `my-${baseName}`,
            `${baseName}-site`,
            `get-${baseName}`
        ];

        const suggestionsDiv = document.createElement('div');
        suggestionsDiv.className = 'domain-suggestions';

        const title = document.createElement('h5');
        title.textContent = 'Альтернативні варіанти:';
        suggestionsDiv.appendChild(title);

        const grid = document.createElement('div');
        grid.className = 'suggestions-grid';

        suggestions.forEach(suggestion => {
            const btn = document.createElement('button');
            btn.className = 'suggestion-item';
            btn.textContent = suggestion + '.ua';
            btn.addEventListener('click', () => {
                this.domainInput.value = suggestion;
                this.handleSearch(new Event('submit'));
            });
            grid.appendChild(btn);
        });

        suggestionsDiv.appendChild(grid);

        const existingSuggestions = document.querySelector('.domain-suggestions');
        if (existingSuggestions) {
            existingSuggestions.replaceWith(suggestionsDiv);
        } else {
            this.searchResults.appendChild(suggestionsDiv);
        }
    }

    /**
     * Handle domain card hover effects
     */
    handleCardHover(event) {
        const card = event.currentTarget;
        card.style.transform = 'translateY(-8px) scale(1.02)';
    }

    /**
     * Handle domain card leave effects
     */
    handleCardLeave(event) {
        const card = event.currentTarget;
        card.style.transform = '';
    }

    /**
     * Set search loading state
     */
    setSearchLoading(loading) {
        const searchBtn = this.searchForm.querySelector('.search-btn');
        const icon = searchBtn.querySelector('i');
        const text = searchBtn.querySelector('span');

        if (loading) {
            searchBtn.disabled = true;
            searchBtn.classList.add('loading');
            icon.className = 'bi bi-hourglass-split';
            text.textContent = 'Перевіряємо...';
        } else {
            searchBtn.disabled = false;
            searchBtn.classList.remove('loading');
            icon.className = 'bi bi-search';
            text.textContent = 'Перевірити';
        }
    }

    /**
     * Update price display
     */
    updatePriceDisplay(price, renewal) {
        const priceElements = document.querySelectorAll('.dynamic-price');
        priceElements.forEach(element => {
            element.textContent = this.formatPrice(price);
        });
    }

    /**
     * Format price with currency
     */
    formatPrice(price) {
        return new Intl.NumberFormat('uk-UA', {
            style: 'currency',
            currency: 'UAH',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(price).replace('₴', 'грн');
    }

    /**
     * Show error message
     */
    showError(message) {
        // Create or update error element
        let errorElement = document.querySelector('.search-error');

        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.className = 'search-error';
            this.searchResults.parentNode.insertBefore(errorElement, this.searchResults);
        } else {
            errorElement.innerHTML = '';
        }

        const alert = document.createElement('div');
        alert.className = 'alert alert-danger d-flex align-items-center';
        alert.setAttribute('role', 'alert');

        const icon = document.createElement('i');
        icon.className = 'bi bi-exclamation-triangle me-2';

        const span = document.createElement('span');
        span.textContent = message;

        const closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.className = 'btn-close ms-auto';
        closeBtn.addEventListener('click', () => {
            if (errorElement.parentNode) {
                errorElement.remove();
            }
        });

        alert.appendChild(icon);
        alert.appendChild(span);
        alert.appendChild(closeBtn);
        errorElement.appendChild(alert);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (errorElement.parentNode) {
                errorElement.remove();
            }
        }, 5000);
    }

    /**
     * Clear search results and cache
     */
    clearSearch() {
        this.searchResults.innerHTML = '';
        this.domainInput.value = '';
        this.domainInput.classList.remove('is-valid', 'is-invalid');
        
        // Clear validation feedback
        const feedback = document.querySelector('.domain-validation-feedback');
        if (feedback) feedback.remove();
        
        // Clear quick result hints
        const hints = document.querySelectorAll('.quick-result-hint');
        hints.forEach(hint => hint.remove());
        
        this.domainInput.focus();
    }

    /**
     * Clear search results only
     */
    clearSearchResults() {
        this.searchResults.innerHTML = '';
    }

    /**
     * Initialize animations and visual effects
     */
    initializeAnimations() {
        // Initialize AOS if available
        if (typeof AOS !== 'undefined') {
            AOS.init({
                duration: 600,
                easing: 'ease-out-cubic',
                once: true,
                offset: 100
            });
        }

        // Add scroll animations for stats
        this.initializeCounterAnimations();

        // Add floating animations to hero elements
        this.initializeFloatingAnimations();
    }

    /**
     * Initialize counter animations for statistics
     */
    initializeCounterAnimations() {
        const counters = document.querySelectorAll('.stat-number');
        
        const animateCounter = (counter) => {
            const target = parseInt(counter.textContent.replace(/\D/g, ''));
            const duration = 2000;
            const step = target / (duration / 16);
            let current = 0;

            const timer = setInterval(() => {
                current += step;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                
                const formattedValue = counter.textContent.includes('+') ? 
                    Math.floor(current) + '+' : 
                    counter.textContent.includes('від') ?
                    'від ' + Math.floor(current) + ' грн' :
                    counter.textContent.includes('/') ?
                    '24/7' :
                    Math.floor(current);
                    
                counter.textContent = formattedValue;
            }, 16);
        };

        // Intersection Observer for counters
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        });

        counters.forEach(counter => observer.observe(counter));
    }

    /**
     * Initialize floating animations
     */
    initializeFloatingAnimations() {
        const floatingElements = document.querySelectorAll('.floating-element');
        
        floatingElements.forEach((element, index) => {
            element.style.animationDelay = `${index * 2}s`;
            element.style.animationDuration = `${6 + index}s`;
        });
    }

    /**
     * Setup form validation
     */
    setupValidation() {
        // Real-time validation for domain input
        if (this.domainInput) {
            this.domainInput.addEventListener('blur', (e) => {
                const value = e.target.value.trim();
                if (value && !this.isValidDomainName(value)) {
                    this.showError('Будь ласка, введіть правильне ім\'я домену');
                }
            });
        }

        // Prevent form submission with invalid data
        if (this.searchForm) {
            this.searchForm.addEventListener('submit', (e) => {
                const domain = this.domainInput.value.trim();
                if (!domain || !this.isValidDomainName(domain)) {
                    e.preventDefault();
                    this.domainInput.focus();
                    return false;
                }
            });
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.domainRegistration = new DomainRegistration();
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = DomainRegistration;
}