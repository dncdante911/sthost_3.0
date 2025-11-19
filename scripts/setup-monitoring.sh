#!/bin/bash

###############################################################################
# Скрипт быстрой настройки мониторинга для StormHosting UA
# Автор: StormHosting IT Team
# Версия: 1.0.0
###############################################################################

set -e

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Путь к проекту
PROJECT_ROOT="/home/user/sthost_3.0"
CONFIG_FILE="$PROJECT_ROOT/config/monitoring.config.php"
TEMPLATE_FILE="$PROJECT_ROOT/config/monitoring.config.sthost.php"

# Функция вывода
print_header() {
    echo -e "${BLUE}╔════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${BLUE}║                                                            ║${NC}"
    echo -e "${BLUE}║     StormHosting UA - Setup Monitoring System             ║${NC}"
    echo -e "${BLUE}║                                                            ║${NC}"
    echo -e "${BLUE}╚════════════════════════════════════════════════════════════╝${NC}"
    echo ""
}

print_step() {
    echo -e "${GREEN}[✓]${NC} $1"
}

print_error() {
    echo -e "${RED}[✗]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[!]${NC} $1"
}

print_info() {
    echo -e "${BLUE}[i]${NC} $1"
}

# Проверка прав root
check_root() {
    if [[ $EUID -ne 0 ]]; then
        print_error "Этот скрипт должен запускаться с правами root"
        echo "Используйте: sudo $0"
        exit 1
    fi
}

# Проверка PHP
check_php() {
    print_info "Проверка PHP..."
    if ! command -v php &> /dev/null; then
        print_error "PHP не установлен!"
        exit 1
    fi

    PHP_VERSION=$(php -v | head -n 1 | cut -d " " -f 2 | cut -d "." -f 1,2)
    print_step "PHP $PHP_VERSION найден"
}

# Проверка расширений PHP
check_php_extensions() {
    print_info "Проверка расширений PHP..."

    REQUIRED_EXTENSIONS=("curl" "json" "simplexml")
    MISSING_EXTENSIONS=()

    for ext in "${REQUIRED_EXTENSIONS[@]}"; do
        if ! php -m | grep -q "^$ext$"; then
            MISSING_EXTENSIONS+=("php-$ext")
        else
            print_step "Расширение $ext установлено"
        fi
    done

    # SNMP опционально
    if ! php -m | grep -q "^snmp$"; then
        print_warning "SNMP расширение не установлено (нужно для мониторинга сети)"
        MISSING_EXTENSIONS+=("php-snmp")
    else
        print_step "Расширение snmp установлено"
    fi

    if [ ${#MISSING_EXTENSIONS[@]} -ne 0 ]; then
        print_warning "Отсутствуют расширения: ${MISSING_EXTENSIONS[*]}"
        read -p "Установить недостающие расширения? (y/n): " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            apt-get update
            apt-get install -y "${MISSING_EXTENSIONS[@]}"
            systemctl restart apache2 || systemctl restart php-fpm || true
            print_step "Расширения установлены"
        fi
    fi
}

# Создание конфигурации
setup_config() {
    print_info "Настройка конфигурации..."

    if [ -f "$CONFIG_FILE" ]; then
        print_warning "Конфигурация уже существует: $CONFIG_FILE"
        read -p "Перезаписать? (y/n): " -n 1 -r
        echo
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            print_info "Пропускаем создание конфига"
            return
        fi
    fi

    if [ ! -f "$TEMPLATE_FILE" ]; then
        print_error "Шаблон конфигурации не найден: $TEMPLATE_FILE"
        exit 1
    fi

    cp "$TEMPLATE_FILE" "$CONFIG_FILE"
    print_step "Конфигурация создана: $CONFIG_FILE"

    print_warning "ВАЖНО: Отредактируйте конфиг и заполните пароли!"
    echo -e "       ${YELLOW}nano $CONFIG_FILE${NC}"
}

# Тестирование подключений
test_connections() {
    print_info "Тестирование подключений к серверам..."

    # ISPmanager
    print_info "Проверка ISPmanager (192.168.0.250)..."
    if ping -c 1 -W 2 192.168.0.250 &> /dev/null; then
        print_step "ISPmanager доступен (ping)"

        if nc -zv -w 2 192.168.0.250 1500 &> /dev/null; then
            print_step "ISPmanager порт 1500 открыт"
        else
            print_warning "ISPmanager порт 1500 недоступен"
        fi
    else
        print_warning "ISPmanager недоступен по сети"
    fi

    # Proxmox
    print_info "Проверка Proxmox (192.168.0.4)..."
    if ping -c 1 -W 2 192.168.0.4 &> /dev/null; then
        print_step "Proxmox доступен (ping)"

        if nc -zv -w 2 192.168.0.4 8006 &> /dev/null; then
            print_step "Proxmox порт 8006 открыт"
        else
            print_warning "Proxmox порт 8006 недоступен"
        fi
    else
        print_warning "Proxmox недоступен по сети"
    fi

    # HAProxy
    print_info "Проверка HAProxy (192.168.0.10)..."
    if ping -c 1 -W 2 192.168.0.10 &> /dev/null; then
        print_step "HAProxy доступен (ping)"

        if nc -zv -w 2 192.168.0.10 8080 &> /dev/null; then
            print_step "HAProxy stats порт 8080 открыт"
        else
            print_warning "HAProxy stats порт 8080 недоступен (нужно настроить)"
        fi
    else
        print_warning "HAProxy недоступен по сети"
    fi
}

# Тест API
test_api() {
    print_info "Тестирование API мониторинга..."

    API_URL="http://localhost/api/monitoring/status.php"

    if curl -s "$API_URL?action=all" | jq . &> /dev/null; then
        print_step "API работает корректно!"
    else
        print_warning "API вернул ошибку (возможно, не заполнены пароли в конфиге)"
        print_info "Проверьте: curl $API_URL?action=all"
    fi
}

# Установка зависимостей для SNMP
install_snmp() {
    print_info "Установка SNMP..."

    read -p "Установить SNMP для мониторинга сети? (y/n): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        print_info "Пропускаем установку SNMP"
        return
    fi

    apt-get update
    apt-get install -y snmp snmpd php-snmp

    print_step "SNMP установлен"

    # Настройка snmpd на HAProxy
    print_info "Для мониторинга сети нужно настроить SNMP на HAProxy (192.168.0.10)"
    print_info "Инструкция в документации: docs/MONITORING_SETUP_STHOST.md"
}

# Показать следующие шаги
show_next_steps() {
    echo ""
    echo -e "${GREEN}╔════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${GREEN}║                    Установка завершена!                    ║${NC}"
    echo -e "${GREEN}╚════════════════════════════════════════════════════════════╝${NC}"
    echo ""
    echo -e "${YELLOW}Следующие шаги:${NC}"
    echo ""
    echo -e "1. ${BLUE}Заполните пароли в конфигурации:${NC}"
    echo -e "   nano $CONFIG_FILE"
    echo ""
    echo -e "2. ${BLUE}Настройте серверы (см. документацию):${NC}"
    echo -e "   - ISPmanager: создайте API пользователя"
    echo -e "   - Proxmox: создайте токен или используйте root"
    echo -e "   - HAProxy: включите stats в haproxy.cfg"
    echo -e "   - SNMP: настройте snmpd на HAProxy"
    echo ""
    echo -e "3. ${BLUE}Прочитайте полную документацию:${NC}"
    echo -e "   cat $PROJECT_ROOT/docs/MONITORING_SETUP_STHOST.md"
    echo ""
    echo -e "4. ${BLUE}Откройте страницу мониторинга:${NC}"
    echo -e "   http://sthost.pro/server-status"
    echo ""
    echo -e "5. ${BLUE}Тестирование API:${NC}"
    echo -e "   curl http://localhost/api/monitoring/status.php?action=all | jq"
    echo ""
    echo -e "${GREEN}Удачи! 🚀${NC}"
    echo ""
}

###############################################################################
# MAIN
###############################################################################

main() {
    print_header

    check_root
    check_php
    check_php_extensions
    setup_config
    test_connections
    install_snmp

    echo ""
    read -p "Протестировать API? (y/n): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        test_api
    fi

    show_next_steps
}

# Запуск
main
