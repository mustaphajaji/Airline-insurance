<?php
function csrf_token(){
    if(empty($_SESSION['csrf_token'])) $_SESSION['csrf_token']=bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
function csrf_field(){
    return '<input type="hidden" name="csrf_token" value="'.htmlspecialchars(csrf_token(),ENT_QUOTES,'UTF-8').'">';
}
function csrf_verify(){
    $t=isset($_POST['csrf_token'])?$_POST['csrf_token']:'';
    if(empty($_SESSION['csrf_token'])||!is_string($t)||!hash_equals($_SESSION['csrf_token'],$t)){
        http_response_code(403);
        die('Session expired. Please go back and try again.');
    }
    return true;
}
