<?php
define('DB_HOST','localhost');
define('DB_NAME','airline_insurance');
define('DB_USER','root');
define('DB_PASS','');
define('DB_CHARSET','utf8mb4');
function db(){
    static $pdo=null;
    if($pdo instanceof PDO) return $pdo;
    $dsn='mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset='.DB_CHARSET;
    try{
        $pdo=new PDO($dsn,DB_USER,DB_PASS,array(
            PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES=>false));
    }catch(PDOException $e){
        error_log('DB connect failed: '.$e->getMessage());
        http_response_code(500);
        die('<!DOCTYPE html><html><head><title>DB Error</title><style>body{font-family:Arial,sans-serif;background:#f4f7fc;display:flex;align-items:center;justify-content:center;height:100vh;margin:0}.box{text-align:center;padding:40px;background:#fff;border-radius:12px;max-width:460px;box-shadow:0 4px 20px rgba(0,0,0,.08)}h2{color:#0a1f44}p{color:#62718c}</style></head><body><div class="box"><h2>Database Unavailable</h2><p>Please ensure MySQL is running in XAMPP and that you have imported <code>database/schema.sql</code> into phpMyAdmin.</p></div></body></html>');
    }
    return $pdo;
}
