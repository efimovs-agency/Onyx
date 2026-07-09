<?php

/* ==========================================================
   ROUTER COMPONENT
   Lightweight routing engine responsible for registering HTTP 
   methods and mapping URIs to designated controller actions.
========================================================== */
class Router {
    
    /**
     * @var array Registry of defined routes grouped by HTTP method.
     */
    private $routes = [];

    /* ==========================================================
       URI NORMALIZATION
       Strips base paths, trailing slashes, and query parameters
       to ensure consistent and predictable route matching regardless
       of the application's deployment subdirectory.
    ========================================================== */
    private function normalizeUri($uri) {
        $uri = parse_url($uri, PHP_URL_PATH) ?: '/';
        $base = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        
        if ($base !== '/' && $base !== '' && strpos($uri, $base) === 0) {
            $uri = substr($uri, strlen($base));
        }
        
        $uri = strtolower(rtrim($uri, '/'));
        return $uri === '' ? '/' : $uri;
    }

    /* ==========================================================
       ROUTE REGISTRATION
       Methods to bind specific URIs to executable actions.
    ========================================================== */
    public function get($uri, $action) {
        $this->routes['GET'][$this->normalizeUri($uri)] = $action;
    }

    public function post($uri, $action) {
        $this->routes['POST'][$this->normalizeUri($uri)] = $action;
    }

    /* ==========================================================
       DISPATCHER
       Evaluates the current incoming HTTP request against the 
       registered route table and triggers the associated action.
       Includes a self-contained 404 fallback mechanism.
    ========================================================== */
    public function run() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $this->normalizeUri($_SERVER['REQUEST_URI']);

        if (isset($this->routes[$method][$uri])) {
            $action = $this->routes[$method][$uri];
            
            if (is_callable($action)) {
                call_user_func($action);
                return;
            }
        } 

        /* ==========================================================
           404 FALLBACK INTERFACE
           Renders a standalone error page if no route matches.
           Inline styles are preserved strictly within this block to 
           guarantee rendering stability without external asset dependencies.
        ========================================================== */
        http_response_code(404);
        echo '<!DOCTYPE html>
        <html lang="ru">
        <head>
            <meta charset="UTF-8">
            <title>Onyx | 404</title>
            <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700&display=swap" rel="stylesheet">
            <style>
                body { 
                    font-family: "Montserrat", sans-serif; 
                    background-color: #030303; 
                    background-image: radial-gradient(circle at 50% 0%, rgba(40, 40, 40, 0.4) 0%, transparent 50%);
                    color: #f5f5f7; 
                    display: flex; 
                    flex-direction: column; 
                    justify-content: center; 
                    align-items: center; 
                    height: 100vh; 
                    margin: 0; 
                }
                h1 { 
                    font-size: 72px; 
                    font-weight: 700; 
                    margin: 0 0 16px 0; 
                    color: #fff; 
                    letter-spacing: -0.04em; 
                }
                p { 
                    font-size: 16px; 
                    color: #86868b; 
                    margin: 0 0 40px 0; 
                    font-weight: 500; 
                }
                a { 
                    background: #fff; 
                    color: #000; 
                    padding: 16px 32px; 
                    text-decoration: none; 
                    border-radius: 14px; 
                    font-weight: 600; 
                    font-size: 15px; 
                    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); 
                }
                a:hover { 
                    background: #e5c158; 
                    transform: translateY(-2px); 
                    box-shadow: 0 10px 20px rgba(229, 193, 88, 0.15); 
                }
            </style>
        </head>
        <body>
            <h1>404</h1>
            <p>Сигнал утерян. Маршрут не существует или был удален.</p>
            <a href="/">Вернуться в дашборд</a>
        </body>
        </html>';
    }
}