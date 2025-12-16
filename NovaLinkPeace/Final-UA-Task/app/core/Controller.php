<?php

/**
 * Base controller: loads models and renders views.
 */
abstract class Controller
{
    protected $config;
    
    public function __construct()
    {
        try {
            $configFile = __DIR__ . '/../../config/config.php';
            if (!file_exists($configFile)) {
                throw new RuntimeException('Configuration file not found: ' . $configFile);
            }
            
            // Load the configuration
            $this->config = require $configFile;
            
            // Ensure config is an array
            if (!is_array($this->config)) {
                $this->config = [];
            }
            
            // Ensure app config exists and is an array
            if (!isset($this->config['app']) || !is_array($this->config['app'])) {
                $this->config['app'] = [];
            }
            
            // Set default base URL if not configured
            if (empty($this->config['app']['base_url'])) {
                $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $scriptName = dirname($_SERVER['SCRIPT_NAME'] ?? '');
                $this->config['app']['base_url'] = rtrim($protocol . '://' . $host . $scriptName, '/');
            }
        } catch (Exception $e) {
            error_log('Error in Controller constructor: ' . $e->getMessage());
            // Set safe defaults if there's an error
            $this->config = [
                'app' => [
                    'base_url' => '/NovaLinkPeace-Jasser-Ouni-task/public',
                    'debug' => true
                ]
            ];
        }
    }
    
    protected function baseUrl($path = '')
    {
        try {
            // Ensure we have a valid base URL
            $base = '';
            if (isset($this->config['app']['base_url']) && is_string($this->config['app']['base_url'])) {
                $base = rtrim($this->config['app']['base_url'], '/');
            } else {
                // Fallback to a default base URL if not set
                $base = '/NovaLinkPeace-Jasser-Ouni-task/public';
            }
            
            // Clean up the path
            $path = ltrim($path ?? '', '/');
            
            // Combine base and path, ensuring no double slashes
            $url = $base . ($path ? '/' . ltrim($path, '/') : '');
            
            // Clean up any remaining double slashes
            return str_replace('//', '/', $url);
            
        } catch (Exception $e) {
            error_log('Error in baseUrl: ' . $e->getMessage());
            return '/NovaLinkPeace-Jasser-Ouni-task/public' . ($path ? '/' . ltrim($path, '/') : '');
        }
    }
    
    protected function asset($path)
    {
        return $this->baseUrl('/assets/' . ltrim($path, '/'));
    }
    protected function model(string $model)
    {
        $modelClass = ucfirst($model);
        $path = __DIR__ . '/../models/' . $modelClass . '.php';

        if (!file_exists($path)) {
            throw new RuntimeException("Model {$modelClass} non trouvé.");
        }

        require_once $path;
        return new $modelClass();
    }

    protected function view(string $view, array $data = [], string $layout = 'front')
    {
        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            throw new RuntimeException("Vue {$view} introuvable.");
        }

        extract($data);

        // Map legacy 'back' layout to a dedicated admin layout file
        $layoutName = ($layout === 'back') ? 'admin_back' : $layout;
        $layoutFile = __DIR__ . '/../views/layouts/' . $layoutName . '.php';
        if (!file_exists($layoutFile)) {
            throw new RuntimeException("Layout {$layout} introuvable.");
        }

        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        include $layoutFile;
    }

    protected function currentUser(): ?array
    {
        // Anonymous user model; there is no real authentication.
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $isAdminContext = strpos($script, 'admin.php') !== false;

        return [
            'id_utilisateur' => 1,
            'is_admin' => $isAdminContext,
            'email' => $isAdminContext ? 'admin@example.test' : 'anonymous@example.test',
        ];
    }

    protected function requireLogin(): array
    {
        // Login is no longer enforced; always return the anonymous user.
        return $this->currentUser() ?? [];
    }

    protected function requireAdmin(): array
    {
        // Anyone reaching the admin entry point is treated as admin.
        return $this->currentUser() ?? [];
    }
    
    protected function isAdmin(): bool
    {
        $user = $this->currentUser();
        return !empty($user['is_admin']);
    }

    protected function isRegularUser(): bool
    {
        return !$this->isAdmin();
    }

    protected function requireUser(): array
    {
        // Frontend always operates as an anonymous regular user.
        return $this->currentUser() ?? [];
    }

    protected function preventAdminOnFrontend(): void
    {
        // No-op in anonymous mode; kept for backwards compatibility.
    }
    
    /**
     * CSRF utilities: tokens are disabled in anonymous mode but methods
     * are kept for compatibility.
     */
    protected function generateCsrfToken(): string
    {
        return 'anonymous_csrf_token';
    }

    protected function validateCsrfToken(string $token): bool
    {
        // CSRF protection is disabled in this assignment context.
        return true;
    }
    
    /**
     * Login checks are disabled; all actions are accessible.
     */
    protected function checkLoginExcept(array $exceptActions = []): void
    {
        // Intentionally left blank.
    }

    protected function redirect(string $path)
    {
        $config = require __DIR__ . '/../../config/config.php';
        $base = rtrim($config['app']['base_url'], '/');
        header('Location: ' . $base . '/' . ltrim($path, '/'));
        exit;
    }
}

