<?php
require_once __DIR__.'/../includes/bootstrap.php';
if(is_logged_in()) redirect('/customer/dashboard.php');
$error=''; $sent=false;
if($_SERVER['REQUEST_METHOD']==='POST'){
    csrf_verify();
    $email=trim(strtolower($_POST['email']??''));
    if(!filter_var($email,FILTER_VALIDATE_EMAIL)){
        $error='Please enter a valid email address.';
    } else {
        $pdo=db();
        $s=$pdo->prepare('SELECT id FROM users WHERE email=? LIMIT 1');
        $s->execute(array($email));
        $user=$s->fetch();
        if($user){
            $token=bin2hex(random_bytes(32));
            $exp=date('Y-m-d H:i:s',time()+3600);
            $pdo->prepare('INSERT INTO password_resets(user_id,token,expires_at) VALUES(?,?,?)')->execute(array($user['id'],$token,$exp));
            // In production, email the reset link. For the demo, we display it.
            $_SESSION['demo_reset_link']=BASE_URL.'/auth/reset.php?token='.$token;
        }
        $sent=true; // Always show success to prevent email enumeration
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Reset Password — Airline Insurance</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?=BASE_URL?>/assets/css/style.css">
<link rel="stylesheet" href="<?=BASE_URL?>/assets/css/auth.css">
</head>
<body>
<div class="auth-shell">
  <div class="auth-aside">
    <div>
      <div class="brand"><?=icon('shield')?> Airline Insurance</div>
      <h1 style="margin-top:40px">Account recovery</h1>
      <p class="lead">Enter the email address linked to your account and we will send you a password reset link.</p>
      <ul class="feat-list">
        <li><?=icon('lock')?><span>Secure one-time reset token</span></li>
        <li><?=icon('clock')?><span>Link expires after 1 hour</span></li>
      </ul>
    </div>
    <div class="aside-quote">Need help? Contact support@airlineinsurance.com</div>
  </div>
  <div class="auth-main">
    <div class="auth-form-wrap">
      <a href="<?=BASE_URL?>/" class="brand light"><?=icon('shield')?> Airline Insurance</a>
      <h2>Reset your password</h2>
      <p class="sub">We'll send a secure link to your inbox.</p>
      <?php if($error): ?><div class="alert alert-error"><?=icon('alert-triangle')?> <?=e($error)?></div><?php endif; ?>
      <?php if($sent): ?>
        <div class="alert alert-success"><?=icon('check-circle')?> If that email is registered, a reset link has been sent.</div>
        <?php if(!empty($_SESSION['demo_reset_link'])): ?>
        <div class="demo-box"><strong>Demo only:</strong> <a href="<?=e($_SESSION['demo_reset_link'])?>"><?=e($_SESSION['demo_reset_link'])?></a></div>
        <?php unset($_SESSION['demo_reset_link']); endif; ?>
      <?php else: ?>
      <form method="post" action="" novalidate>
        <?=csrf_field()?>
        <div class="field">
          <label for="email">Email address</label>
          <div class="input-wrap"><?=icon('mail')?>
            <input type="email" id="email" name="email" placeholder="you@example.com" required autocomplete="email">
          </div>
        </div>
        <button type="submit" class="btn btn-primary btn-block btn-lg"><?=icon('send')?> Send Reset Link</button>
      </form>
      <?php endif; ?>
      <div class="form-footer"><a href="<?=BASE_URL?>/auth/login.php"><?=icon('chevron-left')?> Back to login</a></div>
    </div>
  </div>
</div>
<script src="<?=BASE_URL?>/assets/js/app.js"></script>
</body>
</html>
