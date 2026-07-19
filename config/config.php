<?php
error_reporting(E_ALL);
ini_set('display_errors','0');
ini_set('log_errors','1');
define('SITE_NAME','Airline Insurance');
define('BASE_PATH', dirname(__DIR__));
if (!isset($_SERVER['HTTP_HOST'])) $_SERVER['HTTP_HOST']='localhost';
$_scriptDir = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\','/',dirname($_SERVER['SCRIPT_NAME'])) : '';
$_root = $_scriptDir;
foreach (array('/auth','/customer','/admin') as $_sub) {
    if (substr($_root,-strlen($_sub))===$_sub) { $_root=substr($_root,0,-strlen($_sub)); break; }
}
if ($_root==='/') $_root='';
define('BASE_URL',$_root);
define('UPLOAD_DIR', BASE_PATH.'/uploads/claims/');
define('UPLOAD_URL', BASE_URL.'/uploads/claims/');
define('MAX_UPLOAD_SIZE', 5*1024*1024);
define('ALLOWED_UPLOAD_TYPES', array('pdf','jpg','jpeg','png'));
if (session_status()===PHP_SESSION_NONE) {
    $cp = $_root!==''?$_root:'/';
    session_set_cookie_params(array('lifetime'=>0,'path'=>$cp,'domain'=>'','secure'=>false,'httponly'=>true,'samesite'=>'Lax'));
    session_name('airline_ins');
    session_start();
}
date_default_timezone_set('UTC');
