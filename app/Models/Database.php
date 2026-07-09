<?php

/* ==========================================================
   DATABASE CONNECTION MANAGER
   Optimized for Serverless (Vercel) + Supabase PgBouncer
========================================================== */

class Database
{
    private static $instance = null;
    private $conn;

    private $host = 'aws-0-eu-central-1.pooler.supabase.com';
    private $port = '6543';
    private $db   = 'postgres';
    private $user = 'postgres.fpudeughixppplvvpqrb';
    private $pass;

    private function __construct()
    {
        $this->pass = getenv('DB_PASSWORD');

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
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    // КРИТИЧНО ДЛЯ VERCEL: Держим соединение открытым между запросами
                    PDO::ATTR_PERSISTENT => true,
                    // КРИТИЧНО ДЛЯ SUPABASE (Порт 6543): Эмуляция подготовки предотвращает зависание PgBouncer
                    PDO::ATTR_EMULATE_PREPARES => true 
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

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }

        return self::$instance;
    }

    public function getConnection()
    {
        return $this->conn;
    }
}