/**
 * WHOIS Lookup JavaScript
 * Handles WHOIS queries and result display
 */

(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('whoisForm');
        const domainInput = document.getElementById('domainInput');
        const searchBtn = document.getElementById('searchBtn');
        const resultsContainer = document.getElementById('whoisResults');

        if (!form || !domainInput) return;

        // Domain validation pattern
        const domainPattern = /^([a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,}$/i;

        /**
         * Real-time domain validation
         */
        domainInput.addEventListener('input', function() {
            const value = this.value.toLowerCase().trim();

            if (value.length === 0) {
                this.classList.remove('is-valid', 'is-invalid');
                return;
            }

            if (domainPattern.test(value)) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
            }
        });

        /**
         * Auto-lowercase domain on blur
         */
        domainInput.addEventListener('blur', function() {
            this.value = this.value.toLowerCase().trim();
        });

        /**
         * Handle form submission
         */
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const domain = domainInput.value.toLowerCase().trim();

            // Validate domain
            if (!domainPattern.test(domain)) {
                showError('Невірний формат домену. Введіть повне ім\'я домену (example.com)');
                domainInput.focus();
                return;
            }

            // Clear previous results
            resultsContainer.innerHTML = '';

            // Set loading state
            setLoadingState(true);

            try {
                // Call API
                const formData = new FormData();
                formData.append('domain', domain);

                const response = await fetch('/api/domains/whois.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                // Parse response
                const text = await response.text();
                let data;

                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error('Invalid JSON response:', text);
                    throw new Error('Сервер повернув некоректну відповідь');
                }

                // Handle response
                if (data.error) {
                    showError(data.error);
                } else if (data.success) {
                    displayResults(data);
                }

            } catch (error) {
                console.error('WHOIS error:', error);
                showError('Помилка виконання запиту: ' + error.message);
            } finally {
                setLoadingState(false);
            }
        });

        /**
         * Set loading state
         */
        function setLoadingState(loading) {
            if (loading) {
                searchBtn.disabled = true;
                searchBtn.classList.add('loading');
            } else {
                searchBtn.disabled = false;
                searchBtn.classList.remove('loading');
            }
        }

        /**
         * Show error message
         */
        function showError(message) {
            resultsContainer.innerHTML = `
                <div class="whois-result-card">
                    <div class="result-header" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                        <i class="bi bi-exclamation-triangle" style="font-size: 48px;"></i>
                        <h2>Помилка</h2>
                        <p class="domain-status">${escapeHtml(message)}</p>
                    </div>
                </div>
            `;
            resultsContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        /**
         * Display WHOIS results
         */
        function displayResults(data) {
            const whoisData = data.data;
            const isAvailable = whoisData.status === 'available';

            if (isAvailable) {
                resultsContainer.innerHTML = `
                    <div class="whois-result-card">
                        <div class="result-header available">
                            <i class="bi bi-check-circle" style="font-size: 48px;"></i>
                            <h2>${escapeHtml(data.domain)}</h2>
                            <p class="domain-status">Домен доступний для реєстрації!</p>
                        </div>
                        <div class="result-body">
                            <div class="text-center">
                                <p class="mb-4">Цей домен вільний та може бути зареєстрований.</p>
                                <a href="/pages/domains/register.php?domain=${encodeURIComponent(data.domain)}"
                                   class="btn-primary-large">
                                    <i class="bi bi-plus-circle"></i>
                                    Зареєструвати ${escapeHtml(data.domain)}
                                </a>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                resultsContainer.innerHTML = `
                    <div class="whois-result-card">
                        <div class="result-header">
                            <i class="bi bi-info-circle" style="font-size: 48px;"></i>
                            <h2>${escapeHtml(data.domain)}</h2>
                            <p class="domain-status">WHOIS інформація</p>
                        </div>
                        <div class="result-body">
                            <div class="whois-data-grid">
                                ${generateDateSection(whoisData)}
                                ${generateRegistrarSection(whoisData)}
                                ${generateStatusSection(whoisData)}
                                ${generateNameServersSection(whoisData)}
                                ${generateRawDataSection(whoisData)}
                            </div>
                        </div>
                    </div>
                `;
            }

            resultsContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        /**
         * Generate date section
         */
        function generateDateSection(data) {
            if (!data.creation_date && !data.expiration_date && !data.updated_date) {
                return '';
            }

            return `
                <div class="whois-section">
                    <h3><i class="bi bi-calendar-event"></i> Важливі дати</h3>
                    ${data.creation_date ? `
                        <div class="data-row">
                            <span class="data-label">Дата реєстрації</span>
                            <span class="data-value">${escapeHtml(data.creation_date)}</span>
                        </div>
                    ` : ''}
                    ${data.expiration_date ? `
                        <div class="data-row">
                            <span class="data-label">Закінчується</span>
                            <span class="data-value">${escapeHtml(data.expiration_date)}</span>
                        </div>
                    ` : ''}
                    ${data.updated_date ? `
                        <div class="data-row">
                            <span class="data-label">Останнє оновлення</span>
                            <span class="data-value">${escapeHtml(data.updated_date)}</span>
                        </div>
                    ` : ''}
                </div>
            `;
        }

        /**
         * Generate registrar section
         */
        function generateRegistrarSection(data) {
            if (!data.registrar && !data.registrar_url) {
                return '';
            }

            return `
                <div class="whois-section">
                    <h3><i class="bi bi-building"></i> Реєстратор</h3>
                    ${data.registrar ? `
                        <div class="data-row">
                            <span class="data-label">Компанія</span>
                            <span class="data-value">${escapeHtml(data.registrar)}</span>
                        </div>
                    ` : ''}
                    ${data.registrar_url ? `
                        <div class="data-row">
                            <span class="data-label">Веб-сайт</span>
                            <span class="data-value">
                                <a href="${escapeHtml(data.registrar_url)}" target="_blank" rel="noopener">
                                    ${escapeHtml(data.registrar_url)}
                                </a>
                            </span>
                        </div>
                    ` : ''}
                </div>
            `;
        }

        /**
         * Generate status section
         */
        function generateStatusSection(data) {
            if (!data.status_list || data.status_list.length === 0) {
                return '';
            }

            const statusesHtml = data.status_list.map(status => `
                <div class="data-row">
                    <span class="data-label">Статус</span>
                    <span class="data-value">${escapeHtml(status)}</span>
                </div>
            `).join('');

            return `
                <div class="whois-section">
                    <h3><i class="bi bi-shield-lock"></i> Статус домену</h3>
                    ${statusesHtml}
                </div>
            `;
        }

        /**
         * Generate name servers section
         */
        function generateNameServersSection(data) {
            if (!data.name_servers || data.name_servers.length === 0) {
                return '';
            }

            const serversHtml = data.name_servers.map(ns => `
                <div class="name-server-item">
                    <i class="bi bi-hdd-network"></i>
                    ${escapeHtml(ns)}
                </div>
            `).join('');

            return `
                <div class="whois-section">
                    <h3><i class="bi bi-diagram-3"></i> DNS Сервери</h3>
                    <div class="name-servers-list">
                        ${serversHtml}
                    </div>
                </div>
            `;
        }

        /**
         * Generate raw data section
         */
        function generateRawDataSection(data) {
            if (!data.raw_data) {
                return '';
            }

            return `
                <div class="whois-section">
                    <h3><i class="bi bi-file-text"></i> Необроблені дані WHOIS</h3>
                    <div class="raw-whois-data">${escapeHtml(data.raw_data)}</div>
                </div>
            `;
        }

        /**
         * Escape HTML to prevent XSS
         */
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    });
})();
