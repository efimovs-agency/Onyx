<?php

/* ==========================================================
   DATABASE CONNECTION MANAGER
   Singleton implementation for PostgreSQL database access 
   using PDO. Ensures a single active connection instance 
   is maintained and reused across the application lifecycle.
========================================================== */
class Database {
    
    /**
     * @var Database|null Singleton instance of the class.
     */
    private static $instance = null;
    
    /**
     * @var PDO Active database connection.
     */
    private $conn;

    /* ==========================================================
       CONNECTION CONFIGURATION
       Credentials and routing parameters for the PostgreSQL host.
    ========================================================== */
    // БОЕВЫЕ НАСТРОЙКИ SUPABASE (FRANKFURT)
    private $host = 'aws-0-eu-central-1.pooler.supabase.com'; 
    private $port = '6543';
    private $db   = 'postgres';
    private $user = 'postgres.fpudeughixppplvvpqrb';
    private $pass = getenv('DB_PASSWORD');

    /* ==========================================================
       INITIALIZATION
       Private constructor prevents direct instantiation. 
       Establishes the PDO connection, configures strict error 
       reporting, and sets the default fetch mode. Halts execution 
       with a formatted UI fallback upon connection failure.
    ========================================================== */
    private function __construct() {
        try {
            // Добавлен sslmode=require — обязательное требование Supabase для облака
            $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->db};sslmode=require";
            
            $this->conn = new PDO($dsn, $this->user, $this->pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            
        } catch (PDOException $e) {
            die("<div style='background:#1a1010; color:#ff3b30; padding:20px; font-family:sans-serif; border-radius:10px;'>
                    <b>Сбой подключения к БД:</b><br>" . $e->getMessage() . "
                 </div>");
        }
    }

    /* ==========================================================
       INSTANCE RETRIEVAL
       Provides global access point to the Singleton instance, 
       instantiating the connection only on the initial request.
    ========================================================== */
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        
        return self::$instance;
    }

    /* ==========================================================
       CONNECTION ACCESS
       Returns the active PDO resource for executing queries.
    ========================================================== */
    public function getConnection() {
        return $this->conn;
    }
}