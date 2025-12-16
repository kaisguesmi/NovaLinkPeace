<?php
/**
 * PeaceLink admin front controller.
 * Example route: /NovaLinkPeace-Jasser-Ouni-task/public/admin.php?action=moderation
 */

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));

require ROOT_PATH . '/app/core/Database.php';
require ROOT_PATH . '/app/core/Model.php';
require ROOT_PATH . '/app/core/Controller.php';

spl_autoload_register(function ($class) {
    $paths = [
        ROOT_PATH . '/app/models/' . $class . '.php',
        ROOT_PATH . '/app/controllers/' . $class . '.php',
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Force AdminController; allow different actions via query string
$controllerParam = 'admin';
$action = $_GET['action'] ?? 'index';

$controllerName = ucfirst($controllerParam) . 'Controller';
$controllerFile = ROOT_PATH . '/app/controllers/' . $controllerName . '.php';

if (!file_exists($controllerFile)) {
    http_response_code(404);
    exit('Controller admin not found.');
}

require_once $controllerFile;
$controller = new $controllerName();

if (!method_exists($controller, $action)) {
    http_response_code(404);
    exit("Action {$action} not defined in {$controllerName}.");
}

// Support HTTP method override from forms (?method=DELETE or hidden field)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method'])) {
    $_SERVER['REQUEST_METHOD'] = strtoupper($_POST['_method']);
}

$controller->{$action}();

