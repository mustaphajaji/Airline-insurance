<?php
require_once __DIR__.'/../includes/bootstrap.php';
if(is_logged_in()) redirect('/customer/dashboard.php');
$token=trim($_GET['token']??'');
$error=''; $success=false; $user=null; $row=null;
if($token){
    $pdo=db();
    $s=$pdo->prepare('SELECT pr.*,u.email FROM password_resets pr JOIN users u ON u.id=pr.user_id WHERE pr.token=? AND pr.used=0 AND pr.expires_at>NOW() LIMIT 1');
    $s->execute(array($token));
    $row=$s->fetch();
    if(!$row){ $error='This reset link is invalid or has expired. Please request a new one.'; }
}
if($_SERVER['REQUEST_METHOD']==='POST'&&$row){
    csrf_verify();
    $pw=$_POST['password']??'';
    $pw2=$_POST['confirm_password']??'';
    $perr=validate_password($pw);
    if($perr) $error=$perr;
    elseif($pw!==$pw2) $error='Passwords do not match.';
    else {
        $pdo=db();
        $hash=password_hash($pw,PASSWORD_BCRYPT);
        $pdo->prepare('UPDATE users SET password_hash=?,failed_attempts=0,locked_until=NULL WHERE id=?')->execute(array($hash,$row['user_id']));
        $pdo->prepare('UPDATE password_resets SET used=1 WHERE id=?')->execute(array($row['id']));
        flash('success','Password reset. Please log in with your new password.');
        redirect('/auth/login.php');
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>New Password — Airline Insurance</title>
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
      <h1 style="margin-top:40px">Create a new password</h1>
      <p class="lead">Choose a strong password that's at least 8 characters and includes uppercase letters, lowercase letters, and numbers.</p>
    </div>
    <div class="aside-quote">Your account security is our priority.</div>
  </div>
  <div class="auth-main">
    <div class="auth-form-wrap">
      <a href="<?=BASE_URL?>/" class="brand light"><?=icon('shield')?> Airline Insurance</a>
      <h2>Set new password</h2>
      <?php if($error): ?><div class="alert alert-error"><?=icon('alert-triangle')?> <?=e($error)?></div><?php endif; ?>
      <?php if(!$row&&!$success): ?>
        <p class="sub">This link is no longer valid.</p>
        <a href="<?=BASE_URL?>/auth/forgot.php" class="btn btn-primary">Request a new link</a>
      <?php else: ?>
      <form method="post" action="<?=BASE_URL?>/auth/reset.php?token=<?=urlencode($token)?>" novalidate>
        <?=csrf_field()?>
        <div class="field">
          <label for="password">New password <span class="req">*</span></label>
          <div class="input-wrap"><?=icon('lock')?>
            <input type="password" id="password" name="password" placeholder="Min 8 chars, upper, lower, number" required autocomplete="new-password">
            <button type="button" class="pw-toggle">
              <span class="eye-on"><?=icon('eye')?></span><span class="eye-off" style="display:none"><?=icon('eye-off')?></span>
            </button>
          </div>
        </div>
        <div class="field">
          <label for="confirm_password">Confirm new password <span class="req">*</span></label>
          <div class="input-wrap"><?=icon('lock')?>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat new password" required autocomplete="new-password">
          </div>
        </div>
        <button type="submit" class="btn btn-primary btn-block btn-lg"><?=icon('lock')?> Reset Password</button>
      </form>
      <?php endif; ?>
      <div class="form-footer"><a href="<?=BASE_URL?>/auth/login.php"><?=icon('chevron-left')?> Back to login</a></div>
    </div>
  </div>
</div>
<script src="<?=BASE_URL?>/assets/js/app.js"></script>
</body>
</html>
