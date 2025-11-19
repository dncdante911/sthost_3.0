/**
 * DNS Lookup JavaScript
 * Handles DNS queries and result display
 */

(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('dnsForm');
        const domainInput = document.getElementById('domainInput');
        const recordTypeSelect = document.getElementById('recordType');
        const searchBtn = document.getElementById('searchBtn');
        const resultsContainer = document.getElementById('dnsResults');
        const quickBtns = document.querySelectorAll('.quick-btn');
        const testBtns = document.querySelectorAll('.test-btn');

        if (!form || !domainInput) return;

        const domainPattern = /^([a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,}$/i;

        // Real-time domain validation
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

        domainInput.addEventListener('blur', function() {
            this.value = this.value.toLowerCase().trim();
        });

        // Quick type buttons
        quickBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                quickBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                recordTypeSelect.value = this.dataset.type;
            });
        });

        // Test buttons
        testBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const type = this.dataset.type;
                recordTypeSelect.value = type;
                if (domainInput.value.trim()) {
                    form.dispatchEvent(new Event('submit'));
                } else {
                    domainInput.focus();
                }
            });
        });

        // Form submission
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const domain = domainInput.value.toLowerCase().trim();
            const recordType = recordTypeSelect.value;

            if (!domainPattern.test(domain)) {
                showError('Невірний формат домену. Введіть повне ім\'я домену (example.com)');
                domainInput.focus();
                return;
            }

            resultsContainer.innerHTML = '';
            setLoadingState(true);

            try {
                const formData = new FormData();
                formData.append('domain', domain);
                formData.append('record_type', recordType);

                const response = await fetch('/api/domains/dns.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const text = await response.text();
                let data;

                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error('Invalid JSON response:', text);
                    throw new Error('Сервер повернув некоректну відповідь');
                }

                if (data.error) {
                    showError(data.error);
                } else if (data.success) {
                    displayResults(data);
                }

            } catch (error) {
                console.error('DNS error:', error);
                showError('Помилка виконання запиту: ' + error.message);
            } finally {
                setLoadingState(false);
            }
        });

        function setLoadingState(loading) {
            if (loading) {
                searchBtn.disabled = true;
                searchBtn.classList.add('loading');
            } else {
                searchBtn.disabled = false;
                searchBtn.classList.remove('loading');
            }
        }

        function showError(message) {
            resultsContainer.innerHTML = `
                <div class="dns-result-card">
                    <div class="result-header" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                        <i class="bi bi-exclamation-triangle" style="font-size: 48px;"></i>
                        <h2>Помилка</h2>
                        <p>${escapeHtml(message)}</p>
                    </div>
                </div>
            `;
            resultsContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function displayResults(data) {
            if (!data.results || data.results.length === 0) {
                resultsContainer.innerHTML = `
                    <div class="dns-result-card">
                        <div class="result-header" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                            <i class="bi bi-exclamation-circle" style="font-size: 48px;"></i>
                            <h2>Записи не знайдено</h2>
                            <p>Для домену ${escapeHtml(data.domain)} не знайдено записів типу ${escapeHtml(data.record_type)}</p>
                        </div>
                    </div>
                `;
            } else {
                const recordsHtml = data.results.map(record => generateRecordRow(record)).join('');

                resultsContainer.innerHTML = `
                    <div class="dns-result-card">
                        <div class="result-header">
                            <i class="bi bi-check-circle" style="font-size: 48px;"></i>
                            <h2>${escapeHtml(data.domain)}</h2>
                            <p>Знайдено ${data.results.length} ${data.record_type} ${plural(data.results.length, 'запис', 'записи', 'записів')}</p>
                        </div>
                        <div class="result-body">
                            <table class="dns-records-table">
                                <thead>
                                    <tr>
                                        <th>Тип</th>
                                        <th>Ім'я</th>
                                        <th>Значення</th>
                                        <th>TTL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${recordsHtml}
                                </tbody>
                            </table>
                        </div>
                    </div>
                `;
            }

            resultsContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function generateRecordRow(record) {
            let value = '';

            switch (record.type) {
                case 'A':
                    value = record.ip || '';
                    break;
                case 'AAAA':
                    value = record.ipv6 || '';
                    break;
                case 'MX':
                    value = `${record.target || ''} <span style="color: #64748b;">(пріоритет: ${record.pri || 0})</span>`;
                    break;
                case 'CNAME':
                    value = record.target || '';
                    break;
                case 'TXT':
                    value = record.txt || '';
                    break;
                case 'NS':
                    value = record.target || '';
                    break;
                case 'SOA':
                    value = `${record.mname || ''}<br><small>Email: ${record.rname || ''}</small>`;
                    break;
                case 'SRV':
                    value = `${record.target || ''} <span style="color: #64748b;">(порт: ${record.port || 0}, вага: ${record.weight || 0})</span>`;
                    break;
                default:
                    value = JSON.stringify(record);
            }

            return `
                <tr>
                    <td><span class="record-type-badge">${escapeHtml(record.type)}</span></td>
                    <td>${escapeHtml(record.host || '-')}</td>
                    <td>${value}</td>
                    <td>${record.ttl || '-'}</td>
                </tr>
            `;
        }

        function plural(n, one, few, many) {
            if (n % 10 === 1 && n % 100 !== 11) return one;
            if (n % 10 >= 2 && n % 10 <= 4 && (n % 100 < 10 || n % 100 >= 20)) return few;
            return many;
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    });
})();
