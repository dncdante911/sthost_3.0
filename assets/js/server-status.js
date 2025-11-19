/**
 * Server Status Page JavaScript
 * Скрипт для отображения статуса серверов в реальном времени
 */

class ServerStatusMonitor {
    constructor() {
        this.apiUrl = '/api/monitoring/status.php';
        this.updateInterval = 30000; // 30 секунд
        this.timer = null;

        this.init();
    }

    init() {
        this.loadStatus();
        this.startAutoUpdate();
    }

    async loadStatus() {
        try {
            this.showLoading();
            this.hideError();

            const response = await fetch(this.apiUrl + '?action=all&format=simple');
            const data = await response.json();

            if (!data.success) {
                throw new Error(data.error || 'Failed to load server status');
            }

            this.renderStatus(data.data);
            this.loadAlerts();

        } catch (error) {
            console.error('Error loading status:', error);
            this.showError(error.message);
        } finally {
            this.hideLoading();
        }
    }

    renderStatus(data) {
        // Обновляем сводку
        this.updateSummary(data.summary, data.timestamp);

        // Отображаем серверы
        this.renderServersByType(data.servers);

        // Показываем секцию серверов
        document.getElementById('serversSection').style.display = 'block';
    }

    updateSummary(summary, timestamp) {
        document.getElementById('totalServers').textContent = summary.total || 0;
        document.getElementById('onlineServers').textContent = summary.online || 0;
        document.getElementById('offlineServers').textContent = summary.offline || 0;

        const lastUpdate = new Date(timestamp * 1000);
        document.getElementById('lastUpdate').textContent = lastUpdate.toLocaleTimeString('uk-UA');
    }

    renderServersByType(servers) {
        const groups = {
            ISPManager: [],
            Proxmox: [],
            HAProxy: [],
            Network: []
        };

        servers.forEach(server => {
            if (groups[server.type]) {
                groups[server.type].push(server);
            }
        });

        // ISPManager
        if (groups.ISPManager.length > 0) {
            this.renderGroup('ispmanager', groups.ISPManager, this.renderISPManagerCard.bind(this));
        }

        // Proxmox
        if (groups.Proxmox.length > 0) {
            this.renderGroup('proxmox', groups.Proxmox, this.renderProxmoxCard.bind(this));
        }

        // HAProxy
        if (groups.HAProxy.length > 0) {
            this.renderGroup('haproxy', groups.HAProxy, this.renderHAProxyCard.bind(this));
        }

        // Network
        if (groups.Network.length > 0) {
            this.renderGroup('network', groups.Network, this.renderNetworkCard.bind(this));
        }
    }

    renderGroup(type, servers, renderFunc) {
        const grid = document.getElementById(type + 'Grid');
        const count = document.getElementById(type + 'Count');

        grid.innerHTML = '';
        count.textContent = servers.length;

        servers.forEach(server => {
            const card = renderFunc(server);
            grid.appendChild(card);
        });

        document.getElementById(type + 'Section').style.display = 'block';
    }

    renderISPManagerCard(server) {
        const card = document.createElement('div');
        card.className = `server-card status-${server.status}`;
        card.innerHTML = `
            <div class="card-header">
                <div class="server-name">${server.name}</div>
                <span class="status-badge ${server.status}">${this.getStatusText(server.status)}</span>
            </div>
            <div class="card-body">
                <div class="metrics">
                    <div class="metric">
                        <div class="metric-label">CPU</div>
                        <div class="metric-bar">
                            <div class="metric-fill" style="width: ${server.cpu}%"></div>
                        </div>
                        <div class="metric-value">${server.cpu}%</div>
                    </div>
                    <div class="metric">
                        <div class="metric-label">Пам'ять</div>
                        <div class="metric-bar">
                            <div class="metric-fill" style="width: ${server.memory}%"></div>
                        </div>
                        <div class="metric-value">${server.memory}%</div>
                    </div>
                    <div class="metric">
                        <div class="metric-label">Uptime</div>
                        <div class="metric-value">${server.uptime}%</div>
                    </div>
                </div>
            </div>
        `;
        return card;
    }

    renderProxmoxCard(server) {
        const card = document.createElement('div');
        card.className = `server-card status-${server.status}`;
        card.innerHTML = `
            <div class="card-header">
                <div class="server-name">${server.name}</div>
                <span class="status-badge ${server.status}">${this.getStatusText(server.status)}</span>
            </div>
            <div class="card-body">
                <div class="metrics">
                    <div class="metric">
                        <div class="metric-label">CPU</div>
                        <div class="metric-bar">
                            <div class="metric-fill" style="width: ${server.cpu}%"></div>
                        </div>
                        <div class="metric-value">${server.cpu}%</div>
                    </div>
                    <div class="metric">
                        <div class="metric-label">Пам'ять</div>
                        <div class="metric-bar">
                            <div class="metric-fill" style="width: ${server.memory}%"></div>
                        </div>
                        <div class="metric-value">${server.memory}%</div>
                    </div>
                    <div class="metric">
                        <div class="metric-label">Віртуальні машини</div>
                        <div class="metric-value">${server.vms_count || 0}</div>
                    </div>
                </div>
            </div>
        `;
        return card;
    }

    renderHAProxyCard(server) {
        const card = document.createElement('div');
        card.className = `server-card status-${server.status}`;
        card.innerHTML = `
            <div class="card-header">
                <div class="server-name">${server.name}</div>
                <span class="status-badge ${server.status}">${this.getStatusText(server.status)}</span>
            </div>
            <div class="card-body">
                <div class="metrics">
                    <div class="metric">
                        <div class="metric-label">Backends UP</div>
                        <div class="metric-value">${server.backends_up}/${server.backends_total}</div>
                    </div>
                    <div class="metric">
                        <div class="metric-label">Sessions</div>
                        <div class="metric-value">${server.sessions}</div>
                    </div>
                </div>
            </div>
        `;
        return card;
    }

    renderNetworkCard(server) {
        const card = document.createElement('div');
        card.className = `server-card status-${server.status}`;
        card.innerHTML = `
            <div class="card-header">
                <div class="server-name">${server.name}</div>
                <span class="status-badge ${server.status}">${this.getStatusText(server.status)}</span>
            </div>
            <div class="card-body">
                <div class="metrics">
                    <div class="metric">
                        <div class="metric-label">Використання</div>
                        <div class="metric-bar">
                            <div class="metric-fill" style="width: ${server.usage}%"></div>
                        </div>
                        <div class="metric-value">${server.usage}%</div>
                    </div>
                    <div class="metric">
                        <div class="metric-label">RX</div>
                        <div class="metric-value">${server.rx_rate}</div>
                    </div>
                    <div class="metric">
                        <div class="metric-label">TX</div>
                        <div class="metric-value">${server.tx_rate}</div>
                    </div>
                </div>
            </div>
        `;
        return card;
    }

    async loadAlerts() {
        try {
            const response = await fetch(this.apiUrl + '?action=alerts');
            const data = await response.json();

            if (data.success && data.data.length > 0) {
                this.renderAlerts(data.data);
            }
        } catch (error) {
            console.error('Error loading alerts:', error);
        }
    }

    renderAlerts(alerts) {
        const container = document.getElementById('alertsList');
        container.innerHTML = '';

        alerts.forEach(alert => {
            const alertEl = document.createElement('div');
            alertEl.className = `alert alert-${alert.severity}`;
            alertEl.innerHTML = `
                <i class="bi bi-exclamation-triangle"></i>
                <div class="alert-content">
                    <div class="alert-message">${alert.message}</div>
                </div>
            `;
            container.appendChild(alertEl);
        });

        document.getElementById('alertsSection').style.display = alerts.length > 0 ? 'block' : 'none';
    }

    getStatusText(status) {
        const texts = {
            'online': 'Онлайн',
            'offline': 'Офлайн',
            'maintenance': 'Обслуговування',
            'error': 'Помилка',
            'unknown': 'Невідомо'
        };
        return texts[status] || status;
    }

    showLoading() {
        document.getElementById('loadingIndicator').style.display = 'block';
    }

    hideLoading() {
        document.getElementById('loadingIndicator').style.display = 'none';
    }

    showError(message) {
        document.getElementById('errorText').textContent = message;
        document.getElementById('errorMessage').style.display = 'block';
    }

    hideError() {
        document.getElementById('errorMessage').style.display = 'none';
    }

    startAutoUpdate() {
        this.timer = setInterval(() => {
            this.loadStatus();
        }, this.updateInterval);
    }

    stopAutoUpdate() {
        if (this.timer) {
            clearInterval(this.timer);
            this.timer = null;
        }
    }
}

// Инициализация при загрузке страницы
window.ServerStatus = null;

document.addEventListener('DOMContentLoaded', () => {
    window.ServerStatus = new ServerStatusMonitor();
});

// Остановка обновлений при уходе со страницы
window.addEventListener('beforeunload', () => {
    if (window.ServerStatus) {
        window.ServerStatus.stopAutoUpdate();
    }
});
