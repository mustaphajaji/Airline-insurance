<?php
require_once __DIR__.'/../includes/bootstrap.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
    csrf_verify();
    do_logout();
    flash('success','You have been logged out.');
    redirect('/auth/login.php');
} else {
    redirect('/');
}
