<?php
require_once __DIR__.'/../includes/bootstrap.php';
if(is_logged_in()){
    if(current_role()==='admin') redirect('/admin/dashboard.php');
    else redirect('/customer/dashboard.php');
}
$error=''; $timeout=isset($_GET['timeout']);
if($_SERVER['REQUEST_METHOD']==='POST'){
    csrf_verify();
    $email=trim($_POST['email']??'');
    $password=$_POST['password']??'';
    $remember=!empty($_POST['remember']);
    $res=attempt_login($email,$password,$remember);
    if($res['ok']){
        clear_old();
        if($res['user']['role']==='admin') redirect('/admin/dashboard.php');
        else redirect('/customer/dashboard.php');
    } else {
        $error=$res['msg'];
        set_old(array('email'=>$email));
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Log In — Airline Insurance</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?=BASE_URL?>/assets/css/style.css">
<link rel="stylesheet" href="<?=BASE_URL?>/assets/css/auth.css">
</head>
<body>
<div class="auth-shell">
  <!-- Aside -->
  <div class="auth-aside">
    <div>
      <div class="brand"><?=icon('shield')?> Airline Insurance</div>
      <div class="route-line"><div class="dot"></div><div class="path"><span class="pm"><?=icon('plane','',16)?></span></div><div class="dot"></div></div>
      <div class="route-cities"><span>New York (JFK)</span><span>London (LHR)</span></div>
      <h1>Your journey, fully protected.</h1>
      <p class="lead">Log in to manage your policies, submit claims and track your coverage in real time.</p>
      <ul class="feat-list">
        <li><?=icon('shield')?><span>Coverage for delays, cancellations, lost baggage and more</span></li>
        <li><?=icon('clock')?><span>24-hour claim processing by our specialist team</span></li>
        <li><?=icon('lock')?><span>Bank-grade security with session management</span></li>
      </ul>
    </div>
    <div class="aside-quote">&ldquo;We filed a claim after a 5-hour delay and it was approved the same day.&rdquo; &mdash; James T., frequent flyer</div>
  </div>
  <!-- Form -->
  <div class="auth-main">
    <div class="auth-form-wrap">
      <a href="<?=BASE_URL?>/" class="brand light"><?=icon('shield')?> Airline Insurance</a>
      <h2>Welcome back</h2>
      <p class="sub">Log in to your account to access your dashboard.</p>
      <?php if($timeout): ?>
      <div class="alert alert-warning"><?=icon('clock')?> Your session expired due to inactivity. Please log in again.</div>
      <?php endif; ?>
      <?php if($error): ?>
      <div class="alert alert-error"><?=icon('alert-triangle')?> <?=e($error)?></div>
      <?php endif; ?>
      <form method="post" action="" novalidate>
        <?=csrf_field()?>
        <div class="field">
          <label for="email">Email address</label>
          <div class="input-wrap"><?=icon('mail')?>
            <input type="email" id="email" name="email" value="<?=old('email')?>" placeholder="you@example.com" required autocomplete="email">
          </div>
        </div>
        <div class="field">
          <label for="password">Password</label>
          <div class="input-wrap"><?=icon('lock')?>
            <input type="password" id="password" name="password" placeholder="Your password" required autocomplete="current-password">
            <button type="button" class="pw-toggle" aria-label="Toggle password visibility">
              <span class="eye-on"><?=icon('eye')?></span>
              <span class="eye-off" style="display:none"><?=icon('eye-off')?></span>
            </button>
          </div>
        </div>
        <div class="auth-opts">
          <label class="cb-row"><input type="checkbox" name="remember" value="1"> Remember me for 30 days</label>
          <a href="<?=BASE_URL?>/auth/forgot.php">Forgot password?</a>
        </div>
        <button type="submit" class="btn btn-primary btn-block btn-lg"><?=icon('arrow-right')?> Log In</button>
      </form>
      <div class="form-footer">Don't have an account? <a href="<?=BASE_URL?>/auth/register.php">Create one</a></div>
      <div class="demo-box">
        <strong>Demo credentials</strong><br>
        Admin: <code>admin@airlineinsurance.com</code> / <code>Admin@12345</code><br>
        Customer: <code>customer@airlineinsurance.com</code> / <code>Customer@123</code><br>
        <span style="font-size:11.5px;opacity:.8">Run setup.php first if this is a fresh install.</span>
      </div>
    </div>
  </div>
</div>
<script src="<?=BASE_URL?>/assets/js/app.js"></script>
</body>
</html>
