<?php

/* ==========================================================
   ENVIRONMENT CONFIGURATION
   Enables error reporting to ensure exceptions are caught
   and logged during execution for debugging purposes.
========================================================== */
ini_set('display_errors', 1);
error_reporting(E_ALL);

/* ==========================================================
   SESSION MANAGEMENT
   Initializes the session safely to prevent headers already
   sent errors and to maintain user state across requests.
========================================================== */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ==========================================================
   LOCALIZATION (i18n) CORE
   Handles dynamic language switching via POST requests,
   persists language preferences in cookies, and loads the
   corresponding JSON translation dictionary.
========================================================== */
$availableLangs = ['ru', 'en', 'uk'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'set_language') {
    $newLang = $_POST['lang'] ?? 'en';
    
    if (in_array($newLang, $availableLangs)) {
        $_SESSION['lang'] = $newLang;
        setcookie('lang', $newLang, time() + 31536000, '/');
    }
    
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success']);
    exit;
}

if (isset($_COOKIE['lang']) && in_array($_COOKIE['lang'], $availableLangs)) {
    $_SESSION['lang'] = $_COOKIE['lang'];
} elseif (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en';
}

$langFile = __DIR__ . '/../lang/' . $_SESSION['lang'] . '.json';
$translations = [];

if (file_exists($langFile)) {
    $decoded = json_decode(file_get_contents($langFile), true);
    if (is_array($decoded)) {
        $translations = $decoded;
    }
}

/* ==========================================================
   TRANSLATION HELPER
   Global helper function to retrieve translated strings.
   Returns null on missing keys to allow template fallback.
========================================================== */
function __($key) {
    global $translations;
    return $translations[$key] ?? null; 
}

/* ==========================================================
   APPLICATION ROUTING SETUP
   Bootstraps the router and primary controller responsible
   for handling core application views and API endpoints.
========================================================== */
// Подключение хелпера Vite
require_once __DIR__ . '/../app/Core/Vite.php';

require_once __DIR__ . '/../app/Router.php';
require_once __DIR__ . '/../app/Controllers/PageController.php';

$router = new Router();
$pageController = new PageController();

/* ==========================================================
   SYSTEM ROUTES REGISTRATION
   Defines the static application endpoints for UI views,
   authentication logic, analytics, and data export.
========================================================== */
$router->get('/', [$pageController, 'index']);
$router->post('/', [$pageController, 'index']); 

$router->get('/links', [$pageController, 'links']);
$router->post('/links', [$pageController, 'links']);

$router->get('/login', [$pageController, 'login']);
$router->post('/login', [$pageController, 'login']); 
$router->get('/logout', [$pageController, 'logout']); 

$router->get('/analytics', [$pageController, 'analytics']);

$router->get('/contact', [$pageController, 'contact']);
$router->post('/api/contact', [$pageController, 'sendMessage']);

$router->get('/api/export', [$pageController, 'exportCsv']);

/* ==========================================================
   SHORT-LINK INTERCEPTOR
   Dynamically catches unreserved URIs to process short link
   redirections before passing them to the page controller.
========================================================== */
function normalizeUriForCheck($uri) {
    $uri = parse_url($uri, PHP_URL_PATH) ?: '/';
    $base = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    
    if ($base !== '/' && $base !== '' && strpos($uri, $base) === 0) {
        $uri = substr($uri, strlen($base));
    }
    
    $uri = strtolower(rtrim($uri, '/'));
    return $uri === '' ? '/' : $uri;
}

$cleanUri = normalizeUriForCheck($_SERVER['REQUEST_URI']);
$reservedRoutes = [
    '/', '/login', '/logout', '/analytics', '/links', 
    '/contact', '/api/contact', '/api/export'
];

if (!in_array($cleanUri, $reservedRoutes)) {
    $shortCode = ltrim($cleanUri, '/'); 
    
    $router->get($cleanUri, function() use ($pageController, $shortCode) {
        $pageController->redirect($shortCode);
    });
}

/* ==========================================================
   ROUTER EXECUTION
   Dispatches the matched route and invokes the corresponding
   controller method or closure.
========================================================== */
$router->run();