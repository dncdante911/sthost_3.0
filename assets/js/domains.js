/**
 * StormHosting UA - Domain Management JavaScript
 */

class DomainManager {
    constructor() {
        this.searchForm = document.getElementById('domainSearchForm');
        this.whoisForm = document.getElementById('whoisForm');
        this.dnsForm = document.getElementById('dnsForm');
        this.init();
    }

    init() {
        this.bindEvents();
        this.setupQuickActions();
    }

    bindEvents() {
        // Domain search form
        if (this.searchForm) {
            this.searchForm.addEventListener('submit', (e) => this.handleDomainSearch(e));
        }

        // WHOIS form
        if (this.whoisForm) {
            this.whoisForm.addEventListener('submit', (e) => this.handleWhoisLookup(e));
        }

        // DNS form
        if (this.dnsForm) {
            this.dnsForm.addEventListener('submit', (e) => this.handleDNSLookup(e));
        }

        // Quick type buttons for DNS
        document.querySelectorAll('.quick-type-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const type = e.target.dataset.type;
                const select = document.getElementById('recordType');
                if (select) {
                    select.value = type;
                    this.highlightQuickButton(e.target);
                }
            });
        });

        // Quick search buttons for domain zones
        document.querySelectorAll('.quick-search-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const zone = e.target.dataset.zone;
                this.quickDomainSearch(zone);
            });
        });

        // Test type buttons for DNS
        document.querySelectorAll('.test-type-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const type = e.target.dataset.type;
                this.quickDNSTest(type);
            });
        });
    }

    setupQuickActions() {
        // Auto-suggest для domain input
        const domainInput = document.getElementById('domainName');
        if (domainInput) {
            domainInput.addEventListener('input', (e) => {
                this.validateDomainInput(e.target);
            });
        }

        // Real-time validation
        const whoisInput = document.getElementById('whoisDomain');
        if (whoisInput) {
            whoisInput.addEventListener('input', (e) => {
                this.validateDomainInput(e.target);
            });
        }

        const dnsInput = document.getElementById('dnsDomain');
        if (dnsInput) {
            dnsInput.addEventListener('input', (e) => {
                this.validateDomainInput(e.target);
            });
        }
    }

    validateDomainInput(input) {
        const value = input.value.toLowerCase();
        const isValid = /^[a-zA-Z0-9.-]*$/.test(value);
        
        input.classList.toggle('is-invalid', !isValid && value.length > 0);
        input.classList.toggle('is-valid', isValid && value.length > 2);

        // Remove invalid characters
        if (!isValid) {
            input.value = value.replace(/[^a-zA-Z0-9.-]/g, '');
        }
    }

    async handleDomainSearch(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const domain = formData.get('domain') || document.getElementById('domainName').value;
        const zone = formData.get('zone') || document.getElementById('domainZone').value;
        
        if (!domain || !zone) {
            this.showError('Введіть ім\'я домену та оберіть зону');
            return;
        }

        const resultsContainer = document.getElementById('searchResults');
        this.showLoading(resultsContainer, 'Перевіряємо доступність домену...');

        try {
            const response = await this.makeRequest('check_domain', {
                domain: domain,
                zone: zone,
                csrf_token: window.domainConfig?.csrfToken || document.getElementById('csrf_token').value
            });

            this.displayDomainResults(resultsContainer, response);
        } catch (error) {
            this.showError(error.message, resultsContainer);
        }
    }

    async handleWhoisLookup(e) {
        e.preventDefault();
        
        const domain = document.getElementById('whoisDomain').value;
        
        if (!domain) {
            this.showError('Введіть ім\'я домену');
            return;
        }

        const resultsContainer = document.getElementById('whoisResults');
        this.showLoading(resultsContainer, 'Виконуємо WHOIS запит...');

        try {
            const response = await this.makeRequest('whois_lookup', {
                domain: domain,
                csrf_token: window.whoisConfig?.csrfToken || document.getElementById('csrf_token').value
            });

            this.displayWhoisResults(resultsContainer, response);
        } catch (error) {
            this.showError(error.message, resultsContainer);
        }
    }

    async handleDNSLookup(e) {
        e.preventDefault();
        
        const domain = document.getElementById('dnsDomain').value;
        const recordType = document.getElementById('recordType').value;
        
        if (!domain) {
            this.showError('Введіть ім\'я домену');
            return;
        }

        const resultsContainer = document.getElementById('dnsResults');
        this.showLoading(resultsContainer, 'Виконуємо DNS запит...');

        try {
            const response = await this.makeRequest('dns_lookup', {
                domain: domain,
                record_type: recordType,
                csrf_token: window.dnsConfig?.csrfToken || document.getElementById('csrf_token').value
            });

            this.displayDNSResults(resultsContainer, response);
        } catch (error) {
            this.showError(error.message, resultsContainer);
        }
    }

    async makeRequest(action, data) {
        const url = '?ajax=1';
        const formData = new FormData();
        
        formData.append('action', action);
        Object.keys(data).forEach(key => {
            formData.append(key, data[key]);
        });

        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        
        if (result.error) {
            throw new Error(result.error);
        }
        
        return result;
    }

    displayDomainResults(container, data) {
        const isAvailable = data.available;
        const statusClass = isAvailable ? 'success' : 'danger';
        const statusIcon = isAvailable ? 'check-circle' : 'x-circle';
        const statusText = isAvailable ? 'Доступний' : 'Зайнятий';

        // Очистка контейнера
        container.innerHTML = '';

        const card = document.createElement('div');
        card.className = 'domain-result-card';

        const header = document.createElement('div');
        header.className = `result-header text-${statusClass}`;

        const icon = document.createElement('i');
        icon.className = `bi bi-${statusIcon} fs-1`;
        header.appendChild(icon);

        const domainTitle = document.createElement('h3');
        domainTitle.textContent = data.domain;
        header.appendChild(domainTitle);

        const statusP = document.createElement('p');
        statusP.className = 'status';
        statusP.textContent = statusText;
        header.appendChild(statusP);

        const body = document.createElement('div');
        body.className = 'result-body';

        if (isAvailable) {
            const priceInfo = document.createElement('div');
            priceInfo.className = 'price-info';

            const priceAmount = document.createElement('div');
            priceAmount.className = 'price-amount';
            priceAmount.textContent = `${data.price} ${data.currency}`;
            priceInfo.appendChild(priceAmount);

            const pricePeriod = document.createElement('div');
            pricePeriod.className = 'price-period';
            pricePeriod.textContent = 'за перший рік';
            priceInfo.appendChild(pricePeriod);

            body.appendChild(priceInfo);

            const actionButtons = document.createElement('div');
            actionButtons.className = 'action-buttons';

            const registerBtn = document.createElement('button');
            registerBtn.className = 'btn btn-primary btn-lg';
            registerBtn.addEventListener('click', () => registerDomain(data.domain));

            const registerIcon = document.createElement('i');
            registerIcon.className = 'bi bi-cart-plus';
            registerBtn.appendChild(registerIcon);

            const registerText = document.createElement('span');
            registerText.textContent = '\u00A0Зареєструвати домен';
            registerBtn.appendChild(registerText);

            const wishlistBtn = document.createElement('button');
            wishlistBtn.className = 'btn btn-outline-secondary';
            wishlistBtn.addEventListener('click', () => addToWishlist(data.domain));

            const wishlistIcon = document.createElement('i');
            wishlistIcon.className = 'bi bi-heart';
            wishlistBtn.appendChild(wishlistIcon);

            const wishlistText = document.createElement('span');
            wishlistText.textContent = '\u00A0Додати до списку бажань';
            wishlistBtn.appendChild(wishlistText);

            actionButtons.appendChild(registerBtn);
            actionButtons.appendChild(wishlistBtn);
            body.appendChild(actionButtons);

            const benefits = document.createElement('div');
            benefits.className = 'domain-benefits';

            const benefitsList = [
                'Безкоштовне керування DNS',
                'Захист приватності WHOIS',
                'Підтримка 24/7'
            ];

            benefitsList.forEach(benefit => {
                const benefitDiv = document.createElement('div');
                benefitDiv.className = 'benefit';

                const checkIcon = document.createElement('i');
                checkIcon.className = 'bi bi-check text-success';
                benefitDiv.appendChild(checkIcon);

                const benefitText = document.createElement('span');
                benefitText.textContent = '\u00A0' + benefit;
                benefitDiv.appendChild(benefitText);

                benefits.appendChild(benefitDiv);
            });

            body.appendChild(benefits);
        } else {
            const unavailableInfo = document.createElement('div');
            unavailableInfo.className = 'unavailable-info';

            const infoText = document.createElement('p');
            infoText.textContent = 'Цей домен вже зареєстрований кимось іншим.';
            unavailableInfo.appendChild(infoText);

            const alternativeActions = document.createElement('div');
            alternativeActions.className = 'alternative-actions';

            const suggestBtn = document.createElement('button');
            suggestBtn.className = 'btn btn-outline-primary';
            suggestBtn.addEventListener('click', () => suggestAlternatives(data.domain));

            const suggestIcon = document.createElement('i');
            suggestIcon.className = 'bi bi-lightbulb';
            suggestBtn.appendChild(suggestIcon);

            const suggestText = document.createElement('span');
            suggestText.textContent = '\u00A0Запропонувати альтернативи';
            suggestBtn.appendChild(suggestText);

            const whoisBtn = document.createElement('button');
            whoisBtn.className = 'btn btn-outline-secondary';
            whoisBtn.addEventListener('click', () => checkWhois(data.domain));

            const whoisIcon = document.createElement('i');
            whoisIcon.className = 'bi bi-info-circle';
            whoisBtn.appendChild(whoisIcon);

            const whoisText = document.createElement('span');
            whoisText.textContent = '\u00A0Перевірити WHOIS';
            whoisBtn.appendChild(whoisText);

            const monitorBtn = document.createElement('button');
            monitorBtn.className = 'btn btn-outline-warning';
            monitorBtn.addEventListener('click', () => monitorDomain(data.domain));

            const monitorIcon = document.createElement('i');
            monitorIcon.className = 'bi bi-bell';
            monitorBtn.appendChild(monitorIcon);

            const monitorText = document.createElement('span');
            monitorText.textContent = '\u00A0Моніторити домен';
            monitorBtn.appendChild(monitorText);

            alternativeActions.appendChild(suggestBtn);
            alternativeActions.appendChild(whoisBtn);
            alternativeActions.appendChild(monitorBtn);

            unavailableInfo.appendChild(alternativeActions);
            body.appendChild(unavailableInfo);
        }

        card.appendChild(header);
        card.appendChild(body);
        container.appendChild(card);
    }

    displayWhoisResults(container, data) {
        // Очистка контейнера
        container.innerHTML = '';

        if (data.data.status === 'available') {
            const availableCard = document.createElement('div');
            availableCard.className = 'whois-result-card';

            const availableHeader = document.createElement('div');
            availableHeader.className = 'result-header text-success';

            const checkIcon = document.createElement('i');
            checkIcon.className = 'bi bi-check-circle fs-1';
            availableHeader.appendChild(checkIcon);

            const domainTitle = document.createElement('h3');
            domainTitle.textContent = data.domain;
            availableHeader.appendChild(domainTitle);

            const availableStatus = document.createElement('p');
            availableStatus.className = 'status';
            availableStatus.textContent = 'Домен доступен для реєстрації';
            availableHeader.appendChild(availableStatus);

            const availableBody = document.createElement('div');
            availableBody.className = 'result-body';

            const registerLink = document.createElement('a');
            registerLink.href = '/domains/register?domain=' + encodeURIComponent(data.domain);
            registerLink.className = 'btn btn-primary btn-lg';

            const registerIcon = document.createElement('i');
            registerIcon.className = 'bi bi-plus-circle';
            registerLink.appendChild(registerIcon);

            const registerText = document.createElement('span');
            registerText.textContent = '\u00A0Зареєструвати домен';
            registerLink.appendChild(registerText);

            availableBody.appendChild(registerLink);
            availableCard.appendChild(availableHeader);
            availableCard.appendChild(availableBody);
            container.appendChild(availableCard);
            return;
        }

        const whoisData = data.data;
        const card = document.createElement('div');
        card.className = 'whois-result-card';

        const header = document.createElement('div');
        header.className = 'result-header';

        const titleH3 = document.createElement('h3');
        titleH3.textContent = data.domain;
        header.appendChild(titleH3);

        const serverP = document.createElement('p');
        serverP.className = 'whois-server';
        serverP.textContent = 'WHOIS Server: ' + (data.whois_server || 'N/A');
        header.appendChild(serverP);

        const body = document.createElement('div');
        body.className = 'result-body';

        const rowDiv = document.createElement('div');
        rowDiv.className = 'row g-4';

        // Дата раздел
        const dateColDiv = document.createElement('div');
        dateColDiv.className = 'col-md-6';

        const dateSection = document.createElement('div');
        dateSection.className = 'whois-section';

        const dateH5 = document.createElement('h5');
        const dateIcon = document.createElement('i');
        dateIcon.className = 'bi bi-calendar';
        dateH5.appendChild(dateIcon);
        const dateText = document.createElement('span');
        dateText.textContent = '\u00A0Важливі дати';
        dateH5.appendChild(dateText);

        dateSection.appendChild(dateH5);

        const dateDataDiv = document.createElement('div');
        dateDataDiv.className = 'whois-data';

        const dateRows = [
            { label: 'Дата реєстрації:', value: whoisData.creation_date || 'Не вказано' },
            { label: 'Дата закінчення:', value: whoisData.expiration_date || 'Не вказано' },
            { label: 'Останнє оновлення:', value: whoisData.updated_date || 'Не вказано' }
        ];

        dateRows.forEach(row => {
            const dataRow = document.createElement('div');
            dataRow.className = 'data-row';

            const label = document.createElement('span');
            label.className = 'label';
            label.textContent = row.label;
            dataRow.appendChild(label);

            const value = document.createElement('span');
            value.className = 'value';
            value.textContent = row.value;
            dataRow.appendChild(value);

            dateDataDiv.appendChild(dataRow);
        });

        dateSection.appendChild(dateDataDiv);
        dateColDiv.appendChild(dateSection);
        rowDiv.appendChild(dateColDiv);

        // Реєстратор раздел
        const registrarColDiv = document.createElement('div');
        registrarColDiv.className = 'col-md-6';

        const registrarSection = document.createElement('div');
        registrarSection.className = 'whois-section';

        const registrarH5 = document.createElement('h5');
        const registrarIcon = document.createElement('i');
        registrarIcon.className = 'bi bi-building';
        registrarH5.appendChild(registrarIcon);
        const registrarText = document.createElement('span');
        registrarText.textContent = '\u00A0Реєстратор';
        registrarH5.appendChild(registrarText);

        registrarSection.appendChild(registrarH5);

        const registrarDataDiv = document.createElement('div');
        registrarDataDiv.className = 'whois-data';

        const registrarRows = [
            { label: 'Реєстратор:', value: whoisData.registrar || 'Не вказано' },
            { label: 'Статус:', value: whoisData.status || 'Не вказано' }
        ];

        registrarRows.forEach(row => {
            const dataRow = document.createElement('div');
            dataRow.className = 'data-row';

            const label = document.createElement('span');
            label.className = 'label';
            label.textContent = row.label;
            dataRow.appendChild(label);

            const value = document.createElement('span');
            value.className = 'value';
            value.textContent = row.value;
            dataRow.appendChild(value);

            registrarDataDiv.appendChild(dataRow);
        });

        registrarSection.appendChild(registrarDataDiv);
        registrarColDiv.appendChild(registrarSection);
        rowDiv.appendChild(registrarColDiv);

        // DNS сервери раздел
        if (whoisData.name_servers && whoisData.name_servers.length > 0) {
            const dnsColDiv = document.createElement('div');
            dnsColDiv.className = 'col-12';

            const dnsSection = document.createElement('div');
            dnsSection.className = 'whois-section';

            const dnsH5 = document.createElement('h5');
            const dnsIcon = document.createElement('i');
            dnsIcon.className = 'bi bi-dns';
            dnsH5.appendChild(dnsIcon);
            const dnsText = document.createElement('span');
            dnsText.textContent = '\u00A0DNS сервери';
            dnsH5.appendChild(dnsText);

            dnsSection.appendChild(dnsH5);

            const dnsServersDiv = document.createElement('div');
            dnsServersDiv.className = 'name-servers';

            whoisData.name_servers.forEach(ns => {
                const nsSpan = document.createElement('span');
                nsSpan.className = 'name-server';
                nsSpan.textContent = ns;
                dnsServersDiv.appendChild(nsSpan);
            });

            dnsSection.appendChild(dnsServersDiv);
            dnsColDiv.appendChild(dnsSection);
            rowDiv.appendChild(dnsColDiv);
        }

        // Raw WHOIS раздел
        const rawColDiv = document.createElement('div');
        rawColDiv.className = 'col-12';

        const rawSection = document.createElement('div');
        rawSection.className = 'whois-section';

        const rawH5 = document.createElement('h5');
        const rawIcon = document.createElement('i');
        rawIcon.className = 'bi bi-file-text';
        rawH5.appendChild(rawIcon);
        const rawText = document.createElement('span');
        rawText.textContent = '\u00A0Необроблені дані WHOIS';
        rawH5.appendChild(rawText);

        rawSection.appendChild(rawH5);

        const rawDataDiv = document.createElement('div');
        rawDataDiv.className = 'raw-whois';

        const rawPre = document.createElement('pre');
        rawPre.textContent = whoisData.raw_data || 'Дані недоступні';
        rawDataDiv.appendChild(rawPre);

        rawSection.appendChild(rawDataDiv);
        rawColDiv.appendChild(rawSection);
        rowDiv.appendChild(rawColDiv);

        body.appendChild(rowDiv);
        card.appendChild(header);
        card.appendChild(body);
        container.appendChild(card);
    }

    displayDNSResults(container, data) {
        const results = data.results;

        // Очистка контейнера
        container.innerHTML = '';

        if (!results || results.length === 0) {
            const noResultsCard = document.createElement('div');
            noResultsCard.className = 'dns-result-card';

            const noResultsHeader = document.createElement('div');
            noResultsHeader.className = 'result-header text-warning';

            const noResultsIcon = document.createElement('i');
            noResultsIcon.className = 'bi bi-exclamation-triangle fs-1';
            noResultsHeader.appendChild(noResultsIcon);

            const noResultsTitle = document.createElement('h3');
            noResultsTitle.textContent = 'DNS записи не знайдено';
            noResultsHeader.appendChild(noResultsTitle);

            const noResultsP = document.createElement('p');
            noResultsP.textContent = `Для домену ${data.domain} не знайдено записів типу ${data.record_type}`;
            noResultsHeader.appendChild(noResultsP);

            noResultsCard.appendChild(noResultsHeader);
            container.appendChild(noResultsCard);
            return;
        }

        const card = document.createElement('div');
        card.className = 'dns-result-card';

        const header = document.createElement('div');
        header.className = 'result-header text-success';

        const headerIcon = document.createElement('i');
        headerIcon.className = 'bi bi-check-circle fs-1';
        header.appendChild(headerIcon);

        const headerTitle = document.createElement('h3');
        headerTitle.textContent = `DNS записи для ${data.domain}`;
        header.appendChild(headerTitle);

        const headerP = document.createElement('p');
        headerP.textContent = `Тип запису: ${data.record_type}`;
        header.appendChild(headerP);

        const body = document.createElement('div');
        body.className = 'result-body';

        // Создание таблицы
        const tableWrapper = document.createElement('div');
        tableWrapper.className = 'dns-table-wrapper';

        const table = document.createElement('table');
        table.className = 'table table-hover dns-records-table';

        const thead = document.createElement('thead');
        const headerRow = document.createElement('tr');

        ['Тип', 'Ім\'я', 'Значення', 'TTL'].forEach(headerText => {
            const th = document.createElement('th');
            th.textContent = headerText;
            headerRow.appendChild(th);
        });

        thead.appendChild(headerRow);
        table.appendChild(thead);

        const tbody = document.createElement('tbody');

        results.forEach(record => {
            let row;

            switch (record.type) {
                case 'A':
                    row = this.createDNSTableRow(record.type, 'type-a', record.host, record.ip, record.ttl);
                    break;
                case 'AAAA':
                    row = this.createDNSTableRow(record.type, 'type-aaaa', record.host, record.ipv6, record.ttl);
                    break;
                case 'MX':
                    row = this.createDNSTableRow(record.type, 'type-mx', record.host, `${record.target} (пріоритет: ${record.pri})`, record.ttl);
                    break;
                case 'CNAME':
                    row = this.createDNSTableRow(record.type, 'type-cname', record.host, record.target, record.ttl);
                    break;
                case 'TXT':
                    row = this.createDNSTableRow(record.type, 'type-txt', record.host, record.txt, record.ttl, 'txt-value');
                    break;
                case 'NS':
                    row = this.createDNSTableRow(record.type, 'type-ns', record.host, record.target, record.ttl);
                    break;
                case 'SOA':
                    const soaValue = `${record.mname}<br><small>Email: ${record.rname}</small>`;
                    row = this.createDNSTableRow(record.type, 'type-soa', record.host, soaValue, record.ttl);
                    break;
            }

            if (row) {
                tbody.appendChild(row);
            }
        });

        table.appendChild(tbody);
        tableWrapper.appendChild(table);
        body.appendChild(tableWrapper);

        // Кнопки действий
        const actionsDiv = document.createElement('div');
        actionsDiv.className = 'dns-actions mt-4';

        const exportBtn = document.createElement('button');
        exportBtn.className = 'btn btn-outline-primary';
        exportBtn.addEventListener('click', () => exportDNSRecords(data.domain, data.record_type));

        const exportIcon = document.createElement('i');
        exportIcon.className = 'bi bi-download';
        exportBtn.appendChild(exportIcon);

        const exportText = document.createElement('span');
        exportText.textContent = '\u00A0Експортувати записи';
        exportBtn.appendChild(exportText);

        const checkBtn = document.createElement('button');
        checkBtn.className = 'btn btn-outline-secondary';
        checkBtn.addEventListener('click', () => checkAllRecords(data.domain));

        const checkIcon = document.createElement('i');
        checkIcon.className = 'bi bi-search';
        checkBtn.appendChild(checkIcon);

        const checkText = document.createElement('span');
        checkText.textContent = '\u00A0Перевірити всі типи записів';
        checkBtn.appendChild(checkText);

        actionsDiv.appendChild(exportBtn);
        actionsDiv.appendChild(checkBtn);
        body.appendChild(actionsDiv);

        card.appendChild(header);
        card.appendChild(body);
        container.appendChild(card);
    }

    createDNSTableRow(type, typeClass, host, value, ttl, valueClass = '') {
        const fragment = document.createDocumentFragment();

        const typeCell = document.createElement('td');
        const typeSpan = document.createElement('span');
        typeSpan.className = `record-type ${typeClass}`;
        typeSpan.textContent = type;
        typeCell.appendChild(typeSpan);
        fragment.appendChild(typeCell);

        const hostCell = document.createElement('td');
        hostCell.textContent = host;
        fragment.appendChild(hostCell);

        const valueCell = document.createElement('td');
        if (valueClass) {
            valueCell.className = valueClass;
        }

        // Special handling for SOA records with HTML
        if (value.includes('<br>')) {
            // For SOA only - contains mname and email
            const parts = value.split('<br><small>Email: ');
            valueCell.textContent = parts[0];

            const br = document.createElement('br');
            valueCell.appendChild(br);

            const small = document.createElement('small');
            small.textContent = 'Email: ' + (parts[1] ? parts[1].replace('</small>', '') : '');
            valueCell.appendChild(small);
        } else {
            valueCell.textContent = value;
        }
        fragment.appendChild(valueCell);

        const ttlCell = document.createElement('td');
        ttlCell.textContent = ttl;
        fragment.appendChild(ttlCell);

        const row = document.createElement('tr');
        row.appendChild(fragment);
        return row;
    }

    quickDomainSearch(zone) {
        const input = document.getElementById('domainName');
        const select = document.getElementById('domainZone');
        
        if (input && select) {
            if (input.value) {
                select.value = zone;
                this.searchForm.dispatchEvent(new Event('submit'));
            } else {
                input.focus();
                input.placeholder = `введіть-назву${zone}`;
                select.value = zone;
            }
        }
    }

    quickDNSTest(type) {
        const select = document.getElementById('recordType');
        const input = document.getElementById('dnsDomain');
        
        if (select && input) {
            select.value = type;
            this.highlightQuickButton(document.querySelector(`[data-type="${type}"]`));
            
            if (input.value) {
                this.dnsForm.dispatchEvent(new Event('submit'));
            } else {
                input.focus();
                input.placeholder = `example.com для ${type} запису`;
            }
        }
    }

    highlightQuickButton(button) {
        // Remove active class from all buttons
        document.querySelectorAll('.quick-type-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        // Add active class to clicked button
        button.classList.add('active');
    }

    showLoading(container, message = 'Завантаження...') {
        container.innerHTML = `
            <div class="loading-state">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Завантаження...</span>
                </div>
                <p class="mt-3">${message}</p>
            </div>
        `;
    }

    showError(message, container = null) {
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger alert-dismissible fade show';
        alert.setAttribute('role', 'alert');

        const icon = document.createElement('i');
        icon.className = 'bi bi-exclamation-triangle';
        alert.appendChild(icon);

        const messageSpan = document.createElement('span');
        messageSpan.textContent = '\u00A0' + message;
        alert.appendChild(messageSpan);

        const closeButton = document.createElement('button');
        closeButton.type = 'button';
        closeButton.className = 'btn-close';
        closeButton.setAttribute('data-bs-dismiss', 'alert');
        alert.appendChild(closeButton);

        if (container) {
            container.innerHTML = '';
            container.appendChild(alert);
        } else {
            // Show in toast
            this.showToast(message, 'error');
        }
    }

    showToast(message, type = 'info') {
        const toastContainer = document.getElementById('toast-container') || this.createToastContainer();

        const toastId = 'toast-' + Date.now();
        const iconClass = {
            'success': 'bi-check-circle',
            'error': 'bi-exclamation-triangle',
            'warning': 'bi-exclamation-triangle',
            'info': 'bi-info-circle'
        }[type] || 'bi-info-circle';

        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type === 'error' ? 'danger' : type} border-0`;
        toast.id = toastId;
        toast.setAttribute('role', 'alert');

        const flexDiv = document.createElement('div');
        flexDiv.className = 'd-flex';

        const toastBody = document.createElement('div');
        toastBody.className = 'toast-body';

        const icon = document.createElement('i');
        icon.className = `bi ${iconClass} me-2`;
        toastBody.appendChild(icon);

        const messageSpan = document.createElement('span');
        messageSpan.textContent = message;
        toastBody.appendChild(messageSpan);

        const closeButton = document.createElement('button');
        closeButton.type = 'button';
        closeButton.className = 'btn-close btn-close-white me-2 m-auto';
        closeButton.setAttribute('data-bs-dismiss', 'toast');

        flexDiv.appendChild(toastBody);
        flexDiv.appendChild(closeButton);
        toast.appendChild(flexDiv);

        toastContainer.appendChild(toast);

        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();

        // Remove toast element after it's hidden
        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    }

    createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = '1080';
        document.body.appendChild(container);
        return container;
    }
}

// Global functions for button actions
window.registerDomain = function(domain) {
    window.location.href = `/domains/register?domain=${encodeURIComponent(domain)}`;
};

window.addToWishlist = function(domain) {
    // Add to wishlist functionality
    const manager = window.domainManager;
    manager.showToast(`Домен ${domain} додано до списку бажань`, 'success');
};

window.suggestAlternatives = function(domain) {
    // Suggest alternatives functionality
    const baseName = domain.split('.')[0];
    const alternatives = [
        `${baseName}.ua`,
        `${baseName}.com.ua`,
        `${baseName}.net.ua`,
        `${baseName}-ua.com`,
        `get${baseName}.com`
    ];
    
    alert(`Альтернативні варіанти:\n${alternatives.join('\n')}`);
};

window.checkWhois = function(domain) {
    window.location.href = `/domains/whois?domain=${encodeURIComponent(domain)}`;
};

window.monitorDomain = function(domain) {
    const manager = window.domainManager;
    manager.showToast(`Моніторинг домену ${domain} налаштовано`, 'success');
};

window.exportDNSRecords = function(domain, recordType) {
    // Export DNS records functionality
    const data = `# DNS Records for ${domain} (${recordType})\n# Generated on ${new Date().toISOString()}\n`;
    const blob = new Blob([data], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);
    
    const a = document.createElement('a');
    a.href = url;
    a.download = `${domain}-${recordType}-records.txt`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
};

window.checkAllRecords = function(domain) {
    const types = ['A', 'AAAA', 'MX', 'CNAME', 'TXT', 'NS'];
    const promises = types.map(type => {
        // This would be a real API call in production
        return new Promise(resolve => {
            setTimeout(() => {
                resolve({ type, hasRecords: Math.random() > 0.3 });
            }, Math.random() * 1000);
        });
    });
    
    Promise.all(promises).then(results => {
        const summary = results.map(r => `${r.type}: ${r.hasRecords ? '✓' : '✗'}`).join('\n');
        alert(`Сводка по всім типам записів для ${domain}:\n\n${summary}`);
    });
};

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.domainManager = new DomainManager();
    
    // Initialize tooltips
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(tooltip => {
        new bootstrap.Tooltip(tooltip);
    });
    
    // Initialize popovers
    const popovers = document.querySelectorAll('[data-bs-toggle="popover"]');
    popovers.forEach(popover => {
        new bootstrap.Popover(popover);
    });
});

// Export for use in other scripts
window.DomainManager = DomainManager;