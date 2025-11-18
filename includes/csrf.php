<?php
/**
 * CSRF Protection Class
 * Provides Cross-Site Request Forgery protection for forms
 */

class CSRF {
    /**
     * Generate a new CSRF token or return existing one
     *
     * @return string The CSRF token
     */
    public static function generateToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }

        // Regenerate token if it's older than 1 hour
        if (isset($_SESSION['csrf_token_time']) && (time() - $_SESSION['csrf_token_time']) > 3600) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Validate CSRF token
     *
     * @param string $token The token to validate
     * @return bool True if valid, false otherwise
     */
    public static function validateToken($token) {
        if (!isset($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }

        // Check if token has expired (1 hour)
        if (isset($_SESSION['csrf_token_time']) && (time() - $_SESSION['csrf_token_time']) > 3600) {
            return false;
        }

        // Use hash_equals to prevent timing attacks
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Generate hidden input field with CSRF token
     *
     * @return string HTML input field
     */
    public static function tokenField() {
        $token = self::generateToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Get CSRF token value (for AJAX requests)
     *
     * @return string The token value
     */
    public static function getToken() {
        return self::generateToken();
    }

    /**
     * Validate token from POST request and die with error if invalid
     *
     * @param string $errorMessage Optional custom error message
     * @return void Dies if validation fails
     */
    public static function validateOrDie($errorMessage = 'CSRF token validation failed') {
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';

        if (!self::validateToken($token)) {
            http_response_code(403);

            // Return JSON for AJAX requests
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                die(json_encode([
                    'success' => false,
                    'message' => $errorMessage
                ]));
            }

            // Regular HTML response
            die($errorMessage);
        }
    }

    /**
     * Generate meta tag for AJAX requests (place in <head>)
     *
     * @return string HTML meta tag
     */
    public static function metaTag() {
        $token = self::generateToken();
        return '<meta name="csrf-token" content="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
}
?>
