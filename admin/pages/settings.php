<?php
/**
 * StormHosting UA - Налаштування
 * Файл: /admin/pages/settings.php
 */
?>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-gear me-2"></i>Загальні налаштування</h5>
            </div>
            <div class="card-body">
                <form>
                    <div class="mb-3">
                        <label class="form-label">Назва сайту</label>
                        <input type="text" class="form-control" value="StormHosting UA">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email для контактів</label>
                        <input type="email" class="form-control" value="info@stormhosting.ua">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Телефон підтримки</label>
                        <input type="text" class="form-control" value="+380 (44) 123-45-67">
                    </div>

                    <button type="submit" class="btn btn-primary">Зберегти</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-shield me-2"></i>Безпека</h5>
            </div>
            <div class="card-body">
                <form>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="twoFactor" checked>
                        <label class="form-check-label" for="twoFactor">
                            Двофакторна автентифікація
                        </label>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="emailVerification" checked>
                        <label class="form-check-label" for="emailVerification">
                            Підтвердження email при реєстрації
                        </label>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="loginNotifications">
                        <label class="form-check-label" for="loginNotifications">
                            Сповіщення про вхід в систему
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary">Зберегти</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="alert alert-info">
    <i class="bi bi-info-circle me-2"></i>
    <strong>Налаштування системи:</strong> Тут ви можете керувати основними параметрами та безпекою сайту.
</div>
