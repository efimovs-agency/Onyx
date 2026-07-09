<?php

/* ==========================================================
   DATABASE CONNECTION MANAGER
   Singleton implementation for PostgreSQL database access
   using PDO. Ensures a single active connection instance
   is maintained and reused across the application lifecycle.
========================================================== */

class Database
{
    /**
     * @var Database|null Singleton instance.
     */
    private static $instance = null;

    /**
     * @var PDO Active database connection.
     */
    private $conn;

    /* ==========================================================
       CONNECTION CONFIGURATION
    ========================================================== */

    // Supabase (Frankfurt)
    private $host = 'aws-0-eu-central-1.pooler.supabase.com';
    private $port = '6543';
    private $db   = 'postgres';
    private $user = 'postgres.fpudeughixppplvvpqrb';

    /**
     * @var string Database password.
     */
    private $pass;

    /* ==========================================================
       INITIALIZATION
    ========================================================== */

    private function __construct()
    {
        // Получаем пароль из переменной окружения Vercel
        $this->pass = getenv('DB_PASSWORD');

        // Если переменная не найдена — выводим понятную ошибку
        if (!$this->pass) {
            die("
                <div style='background:#1a1010;color:#ff3b30;padding:20px;
                font-family:sans-serif;border-radius:10px;'>
                    <b>Ошибка:</b><br>
                    Переменная окружения <b>DB_PASSWORD</b> не найдена.
                </div>
            ");
        }

        try {

            $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->db};sslmode=require";

            $this->conn = new PDO(
                $dsn,
                $this->user,
                $this->pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );

        } catch (PDOException $e) {

            die("
                <div style='background:#1a1010;color:#ff3b30;padding:20px;
                font-family:sans-serif;border-radius:10px;'>
                    <b>Сбой подключения к БД:</b><br>
                    {$e->getMessage()}
                </div>
            ");

        }
    }

    /* ==========================================================
       INSTANCE RETRIEVAL
    ========================================================== */

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }

        return self::$instance;
    }

    /* ==========================================================
       CONNECTION ACCESS
    ========================================================== */

    public function getConnection()
    {
        return $this->conn;
    }
}