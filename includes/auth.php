<?php
require_once __DIR__.'/../config/database.php';
const MAX_ATTEMPTS=5;
const LOCK_MINS=15;
function attempt_login($email,$password,$remember=false){
    $email=trim(strtolower($email));
    $pdo=db();
    $s=$pdo->prepare('SELECT * FROM users WHERE email=? LIMIT 1');
    $s->execute(array($email));
    $user=$s->fetch();
    if(!$user) return array('ok'=>false,'msg'=>'Incorrect email or password.');
    if(!empty($user['locked_until'])&&strtotime($user['locked_until'])>time()){
        $m=ceil((strtotime($user['locked_until'])-time())/60);
        return array('ok'=>false,'msg'=>"Account locked. Try again in $m minute(s).");
    }
    if($user['status']!=='active') return array('ok'=>false,'msg'=>'This account has been suspended.');
    if(!password_verify($password,$user['password_hash'])){
        $att=(int)$user['failed_attempts']+1;
        if($att>=MAX_ATTEMPTS){
            $lu=date('Y-m-d H:i:s',time()+LOCK_MINS*60);
            $pdo->prepare('UPDATE users SET failed_attempts=0,locked_until=? WHERE id=?')->execute(array($lu,$user['id']));
            return array('ok'=>false,'msg'=>'Too many failed attempts. Account locked for '.LOCK_MINS.' minutes.');
        }
        $pdo->prepare('UPDATE users SET failed_attempts=? WHERE id=?')->execute(array($att,$user['id']));
        return array('ok'=>false,'msg'=>'Incorrect email or password.');
    }
    $pdo->prepare('UPDATE users SET failed_attempts=0,locked_until=NULL,last_login=NOW() WHERE id=?')->execute(array($user['id']));
    session_regenerate_id(true);
    $_SESSION['uid']=(int)$user['id'];
    $_SESSION['role']=$user['role'];
    $_SESSION['name']=$user['full_name'];
    $_SESSION['email']=$user['email'];
    $_SESSION['last_act']=time();
    if($remember) _set_remember($user['id']);
    return array('ok'=>true,'msg'=>'Login successful.','user'=>$user);
}
function _set_remember($uid){
    $pdo=db(); $tok=bin2hex(random_bytes(32)); $h=hash('sha256',$tok);
    $exp=date('Y-m-d H:i:s',time()+30*86400);
    $pdo->prepare('INSERT INTO remember_tokens(user_id,token_hash,expires_at) VALUES(?,?,?)')->execute(array($uid,$h,$exp));
    setcookie('air_rem',$uid.':'.$tok,time()+30*86400,BASE_URL!==''?BASE_URL:'/',false,true);
}
function try_remember(){
    if(is_logged_in()||empty($_COOKIE['air_rem'])) return;
    $parts=explode(':',$_COOKIE['air_rem'],2);
    if(count($parts)!==2) return;
    list($uid,$tok)=$parts; $h=hash('sha256',$tok);
    $s=db()->prepare('SELECT * FROM remember_tokens WHERE user_id=? AND token_hash=? AND expires_at>NOW() LIMIT 1');
    $s->execute(array($uid,$h));
    if(!$s->fetch()) return;
    $s2=db()->prepare('SELECT * FROM users WHERE id=? AND status="active" LIMIT 1');
    $s2->execute(array($uid));
    $u=$s2->fetch();
    if(!$u) return;
    session_regenerate_id(true);
    $_SESSION['uid']=(int)$u['id']; $_SESSION['role']=$u['role'];
    $_SESSION['name']=$u['full_name']; $_SESSION['email']=$u['email'];
    $_SESSION['last_act']=time();
}
function is_logged_in(){ return !empty($_SESSION['uid']); }
function current_uid(){ return isset($_SESSION['uid'])?(int)$_SESSION['uid']:null; }
function current_role(){ return isset($_SESSION['role'])?$_SESSION['role']:null; }
function current_user(){
    static $u=null;
    if($u!==null) return $u;
    if(!is_logged_in()) return null;
    $s=db()->prepare('SELECT id,full_name,email,phone,role,status,last_login,created_at FROM users WHERE id=? LIMIT 1');
    $s->execute(array(current_uid())); $u=$s->fetch(); return $u;
}
function redirect($p){ header('Location:'.BASE_URL.$p); exit; }
function require_login($role=null){
    try_remember();
    if(!is_logged_in()) redirect('/auth/login.php');
    if(isset($_SESSION['last_act'])&&(time()-$_SESSION['last_act']>3600)){
        do_logout(); redirect('/auth/login.php?timeout=1');
    }
    $_SESSION['last_act']=time();
    if($role!==null&&current_role()!==$role){
        if(current_role()==='admin') redirect('/admin/dashboard.php');
        else redirect('/customer/dashboard.php');
    }
}
function do_logout(){
    $_SESSION=array();
    if(ini_get('session.use_cookies')){
        $p=session_get_cookie_params();
        setcookie(session_name(),'',time()-42000,$p['path'],$p['domain'],$p['secure'],$p['httponly']);
    }
    session_destroy();
    if(!empty($_COOKIE['air_rem'])) setcookie('air_rem','',time()-42000,BASE_URL!==''?BASE_URL:'/');
}
function flash($type,$msg){ $_SESSION['flash']=array('type'=>$type,'msg'=>$msg); }
function get_flash(){
    if(empty($_SESSION['flash'])) return null;
    $f=$_SESSION['flash']; unset($_SESSION['flash']); return $f;
}
