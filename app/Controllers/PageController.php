<?php
require_once(__DIR__ . '/../Models/Link.php');
require_once(__DIR__ . '/../Models/User.php');

/* ==========================================================
   PAGE CONTROLLER
   Main application controller handling view rendering, form 
   submissions, asynchronous requests, and redirection routing.
========================================================== */
class PageController {
    
    public function index() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'set_theme') {
            $theme = $_POST['theme'] === 'light' ? 'light' : 'dark';
            $_SESSION['theme'] = $theme;
            
            if (isset($_SESSION['user_id'])) {
                $userModel = new User();
                $userModel->updateTheme($_SESSION['user_id'], $theme);
            }
            exit(); 
        }

        $error = null;
        $linkError = null;
        $userLinks = [];
        $totalClicks = 0;
        $activeForm = 'register'; 
        $isSuccess = false; 

        // Вспомогательная функция для чистой отправки JSON
        $sendJson = function($status, $message, $redirect = null) {
            while (ob_get_level()) { ob_end_clean(); }
            header('Content-Type: application/json; charset=utf-8');
            $response = ['status' => $status, 'message' => $message];
            if ($redirect) { $response['redirect'] = $redirect; }
            echo json_encode($response);
            exit();
        };
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
            
            // Проверяем, пришел ли запрос от JS
            $isAjax = !empty($_POST['ajax']) || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
            
            $email = trim(strval($_POST['email'] ?? $_POST['reg_email'] ?? ''));
            $login = trim(strval($_POST['login'] ?? $_POST['reg_login'] ?? ''));
            $password = strval($_POST['password'] ?? $_POST['reg_password'] ?? ''); 

            if ($email === '' || $login === '' || $password === '') {
                $error = __('fill_all_fields') ?? "Пожалуйста, заполните все поля.";
            } 
            elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = __('invalid_email') ?? "Пожалуйста, введите корректный email.";
            } 
            elseif (mb_strlen($password) < 6) {
                $error = __('password_short') ?? "Пароль должен содержать минимум 6 символов.";
            } 
            else {
                try {
                    $userModel = new User();

                    if ($userModel->userExists($email, $login)) {
                        $error = __('user_exists') ?? "Пользователь с таким логином или email уже существует.";
                    } else {
                        if ($userModel->register($email, $login, $password)) {
                            if ($isAjax) {
                                $sendJson('success', __('reg_success') ?? "Регистрация успешна! Теперь вы можете войти.", '/login');
                            }
                            $error = __('reg_success') ?? "Регистрация успешна! Теперь вы можете войти.";
                            $isSuccess = true;
                            $activeForm = 'login'; 
                        } else {
                            $error = __('reg_error') ?? "Ошибка при регистрации в базе данных.";
                        }
                    }
                } catch (\Throwable $e) {
                    $error = "Системная ошибка: " . $e->getMessage();
                }
            }

            if ($error && $isAjax) {
                $sendJson('error', $error);
            }
        }
        
        if (isset($_SESSION['user_id'])) {
            $linkModel = new Link();

            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
                if ($_POST['action'] === 'shorten') {
                    $title = trim($_POST['title'] ?? '');
                    $originalUrl = trim($_POST['original_url'] ?? '');
                    $customCode = trim($_POST['custom_code'] ?? ''); 

                    if (!empty($originalUrl) && !empty($customCode)) {
                        if (!$linkModel->createShortLink($_SESSION['user_id'], $originalUrl, $customCode, $title)) {
                            $linkError = "Такое сокращение уже используется в базе";
                        }
                    }
                }

                if ($_POST['action'] === 'delete') {
                    $linkId = $_POST['link_id'] ?? null;
                    if ($linkId) {
                        $linkModel->deleteLink($linkId, $_SESSION['user_id']);
                    }
                }
            }

            $userLinks = $linkModel->getUserLinks($_SESSION['user_id']);
            $totalClicks = array_sum(array_column($userLinks, 'clicks_count'));
            $clicks24h = $linkModel->getTotalClicksLast24h($_SESSION['user_id']);
            $topLink = $linkModel->getTopLink($_SESSION['user_id']);
            $topSourceData = $linkModel->getTrafficSources($_SESSION['user_id'], null, '1y');
            $topSource = !empty($topSourceData) ? $topSourceData[0]['source_name'] : 'Нет данных';
        }

        require_once(dirname(__DIR__, 2) . '/views/home.php');
    }

    public function links() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /login");
            exit();
        }

        $linkModel = new Link();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            if ($_POST['action'] === 'delete') {
                $linkId = $_POST['link_id'] ?? null;
                if ($linkId) {
                    $linkModel->deleteLink($linkId, $_SESSION['user_id']);
                }
            }

            if ($_POST['action'] === 'edit') {
                $linkId = $_POST['link_id'] ?? null;
                $title = trim($_POST['title'] ?? '');
                $originalUrl = trim($_POST['original_url'] ?? '');
                
                if ($linkId && !empty($originalUrl)) {
                    if ($title === '') {
                        $allLinks = $linkModel->getUserLinks($_SESSION['user_id']);
                        $maxNum = 0;
                        foreach ($allLinks as $l) {
                            $existingTitle = trim($l['title']);
                            if (preg_match('/^Без названия\s*(\d+)$/iu', $existingTitle, $matches)) {
                                $num = (int)$matches[1];
                                if ($num > $maxNum) { $maxNum = $num; }
                            } elseif (mb_strtolower($existingTitle) === 'без названия') {
                                if ($maxNum < 1) { $maxNum = 1; }
                            }
                        }
                        $title = "Без названия " . ($maxNum + 1);
                    }
                    $linkModel->updateLink($linkId, $_SESSION['user_id'], $title, $originalUrl);
                }
            }
        }

        $userLinks = $linkModel->getUserLinks($_SESSION['user_id']);
        require_once '../views/links.php';
    }

    public function analytics() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /login");
            exit();
        }

        try {
            $linkModel = new Link();
            
            $linkId = $_GET['link_id'] ?? null;
            $range = $_GET['range'] ?? '7d'; 
            
            $rangeLabels = [
                '1d'  => 'за 24 часа',
                '7d'  => 'за 7 дней',
                '30d' => 'за 30 дней',
                '1y'  => 'за год'
            ];
            $rangeLabel = $rangeLabels[$range] ?? 'за 7 дней';
            
            $timelineData = $linkModel->getClicksTimeline($_SESSION['user_id'], $linkId, $range);
            $ohlcData = $linkModel->getOhlcData($_SESSION['user_id'], $linkId, $range); 
            $osData = $linkModel->getOsDistribution($_SESSION['user_id'], $linkId, $range);
            $sourceData = $linkModel->getTrafficSources($_SESSION['user_id'], $linkId, $range);
            $globePoints = $linkModel->getGlobePoints($_SESSION['user_id'], $linkId, $range);
            
            $hourlyData = $linkModel->getHourlyActivity($_SESSION['user_id'], $linkId, $range);
            
            $uniqueVisitors = $linkModel->getUniqueVisitorsCount($_SESSION['user_id'], $linkId, $range);
            $totalClicks = $linkModel->getTotalClicksByRange($_SESSION['user_id'], $linkId, $range);
            $mobileShare = $linkModel->getMobileTrafficShare($_SESSION['user_id'], $linkId, $range);
            $topIp = $linkModel->getTopIp($_SESSION['user_id'], $linkId, $range);
            
            $topLocation = 'Нет данных';
            
            if ($topIp && $topIp !== 'unknown') {
                $isLocal = filter_var($topIp, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
                
                if ($isLocal) {
                    $topLocation = 'Одесса (Local Dev)';
                } else {
                    $ctx = stream_context_create(['http' => ['timeout' => 1]]);
                    $geoData = @file_get_contents("http://ip-api.com/json/{$topIp}?lang=ru", false, $ctx);
                    if ($geoData) {
                        $geo = json_decode($geoData, true);
                        if ($geo && $geo['status'] === 'success') {
                            $topLocation = $geo['country'] . ($geo['city'] ? ', ' . $geo['city'] : '');
                        }
                    }
                }
            }
            
            $userLinks = $linkModel->getUserLinks($_SESSION['user_id']);
            $currentLink = null;
            
            if ($linkId) {
                foreach ($userLinks as $link) {
                    if ($link['id'] == $linkId) {
                        $currentLink = $link;
                        break;
                    }
                }
            }

            require_once '../views/analytics.php';
            
        } catch (\Throwable $e) {
            die("<div style='background:#1a1010; color:#ff3b30; padding:30px; font-family:sans-serif; border-radius:12px; margin: 40px auto; max-width: 800px; border: 1px solid rgba(255,59,48,0.3);'>
                    <h2 style='margin-top:0;'>🛡️ Критический сбой базы данных</h2>
                    <p style='color:#a1a1a6;'><b>Причина:</b> " . $e->getMessage() . "</p>
                 </div>");
        }
    }

    public function login() {
        $error = null;
        $activeForm = 'login'; 
        $isSuccess = false;

        $sendJson = function($status, $message, $redirect = null) {
            while (ob_get_level()) { ob_end_clean(); }
            header('Content-Type: application/json; charset=utf-8');
            $response = ['status' => $status, 'message' => $message];
            if ($redirect) { $response['redirect'] = $redirect; }
            echo json_encode($response);
            exit();
        };

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            $isAjax = !empty($_POST['ajax']) || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
            
            $login = trim(strval($_POST['login'] ?? ''));
            $password = strval($_POST['password'] ?? '');

            if (strlen($login) === 0 || strlen($password) === 0) {
                $error = __('fill_all_fields') ?? "Пожалуйста, заполните все поля.";
            } else {
                try {
                    $userModel = new User();
                    $user = $userModel->login($login, $password);

                    if ($user) {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['login'] = $user['login'];
                        $_SESSION['theme'] = $user['theme'] ?? 'dark'; 
                        
                        if ($isAjax) {
                            $sendJson('success', 'Вход выполнен!', '/');
                        }
                        
                        header("Location: /");
                        exit();
                    } else {
                        $error = __('invalid_credentials') ?? "Неверный логин или пароль.";
                    }
                } catch (\Throwable $e) {
                    $error = "Системная ошибка: " . $e->getMessage();
                }
            }
            
            if ($error && $isAjax) {
                $sendJson('error', $error);
            }
        }
        require_once(dirname(__DIR__, 2) . '/views/login.php');
    }

    public function logout() {
        session_destroy();
        header("Location: /login");
        exit();
    }

    public function redirect($shortCode) {
        $linkModel = new Link();
        $linkData = $linkModel->getLinkByCode($shortCode);

        if ($linkData) {
            $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            if (strpos($ipAddress, ',') !== false) {
                $ipAddress = trim(explode(',', $ipAddress)[0]);
            }

            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            $referer = $_SERVER['HTTP_REFERER'] ?? null;

            $linkModel->logClick($linkData['id'], $ipAddress, $userAgent, $referer);
            header("Location: " . $linkData['original_url']);
            exit();
        } else {
            http_response_code(404);
            echo '<!DOCTYPE html>
            <html lang="ru">
            <head>
                <meta charset="UTF-8">
                <title>Onyx | 404</title>
                <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700&display=swap" rel="stylesheet">
                <style>
                    body { font-family: "Montserrat", sans-serif; background-color: #030303; background-image: radial-gradient(circle at 50% 0%, rgba(40, 40, 40, 0.4) 0%, transparent 50%); color: #f5f5f7; display: flex; flex-direction: column; justify-content: center; align-items: center; height: 100vh; margin: 0; }
                    h1 { font-size: 72px; font-weight: 700; margin: 0 0 16px 0; color: #fff; letter-spacing: -0.04em; }
                    p { font-size: 16px; color: #86868b; margin: 0 0 40px 0; font-weight: 500; }
                </style>
            </head>
            <body>
                <h1>404</h1>
                <p>Сигнал утерян. Маршрут не существует или был удален.</p>
            </body>
            </html>';
            exit();
        }
    }

    public function contact() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /login");
            exit();
        }
        require_once(dirname(__DIR__, 2) . '/views/contact.php');
    }

    public function sendMessage() {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Функция отправки временно отключена']);
        exit;
    }

    public function exportCsv() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /login");
            exit();
        }

        $linkModel = new Link();
        $userLinks = $linkModel->getUserLinks($_SESSION['user_id']);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="onyx_export_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF");
        fputcsv($output, ['ID', 'Название', 'Оригинальный URL', 'Короткая ссылка', 'Клики', 'Дата'], ';');
        
        if (!empty($userLinks)) {
            foreach ($userLinks as $link) {
                fputcsv($output, [
                    $link['id'],
                    $link['title'] ?? 'Без названия',
                    $link['original_url'],
                    $_SERVER['HTTP_HOST'] . '/' . $link['short_code'],
                    $link['clicks_count'] ?? 0,
                    $link['created_at'] ?? '—'
                ], ';');
            }
        }
        fclose($output);
        exit();
    }
}