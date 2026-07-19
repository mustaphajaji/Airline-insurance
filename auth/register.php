<?php
require_once __DIR__.'/../includes/bootstrap.php';
if(is_logged_in()) redirect(current_role()==='admin'?'/admin/dashboard.php':'/customer/dashboard.php');
$errors=array(); $success=false;
if($_SERVER['REQUEST_METHOD']==='POST'){
    csrf_verify();
    $name=trim($_POST['full_name']??'');
    $email=trim(strtolower($_POST['email']??''));
    $phone=trim($_POST['phone']??'');
    $pw=$_POST['password']??'';
    $pw2=$_POST['confirm_password']??'';
    if(strlen($name)<2) $errors[]='Full name must be at least 2 characters.';
    if(!filter_var($email,FILTER_VALIDATE_EMAIL)) $errors[]='Please enter a valid email address.';
    $perr=validate_password($pw);
    if($perr) $errors[]=$perr;
    if($pw!==$pw2) $errors[]='Passwords do not match.';
    if(empty($errors)){
        $pdo=db();
        $chk=$pdo->prepare('SELECT id FROM users WHERE email=? LIMIT 1');
        $chk->execute(array($email));
        if($chk->fetch()) $errors[]='That email address is already registered.';
    }
    if(empty($errors)){
        $pdo=db();
        $hash=password_hash($pw,PASSWORD_BCRYPT);
        $ins=$pdo->prepare('INSERT INTO users(full_name,email,phone,password_hash,role,status) VALUES(?,?,?,?,"customer","active")');
        $ins->execute(array($name,$email,$phone,$hash));
        $uid=$pdo->lastInsertId();
        notify_user($uid,'Welcome to Airline Insurance','Your account has been created. Browse our plans to get covered for your next flight.');
        flash('success','Account created! You can now log in.');
        redirect('/auth/login.php');
    } else {
        set_old(array('full_name'=>$name,'email'=>$email,'phone'=>$phone));
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Create Account — Airline Insurance</title>
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
      <div class="route-line"><div class="dot"></div><div class="path"><span class="pm"><?=icon('plane')?></span></div><div class="dot"></div></div>
      <div class="route-cities"><span>Dubai (DXB)</span><span>Sydney (SYD)</span></div>
      <h1>Join thousands of protected travellers.</h1>
      <p class="lead">Create your account and choose a plan. Your coverage begins the moment payment is confirmed.</p>
      <ul class="feat-list">
        <li><?=icon('check-circle')?><span>Six coverage categories to choose from</span></li>
        <li><?=icon('check-circle')?><span>Instant policy issuance after payment</span></li>
        <li><?=icon('check-circle')?><span>Online claims — no paperwork required</span></li>
      </ul>
    </div>
    <div class="aside-quote">&ldquo;The claims process was seamless. Our lost baggage was reimbursed within 48 hours.&rdquo;</div>
  </div>
  <div class="auth-main">
    <div class="auth-form-wrap">
      <a href="<?=BASE_URL?>/" class="brand light"><?=icon('shield')?> Airline Insurance</a>
      <h2>Create your account</h2>
      <p class="sub">Fill in the details below to get started.</p>
      <?php if(!empty($errors)): ?>
      <div class="alert alert-error"><?=icon('alert-triangle')?>
        <div><?php foreach($errors as $e): ?><div><?=htmlspecialchars($e,ENT_QUOTES,'UTF-8')?></div><?php endforeach; ?></div>
      </div>
      <?php endif; ?>
      <form method="post" action="" novalidate>
        <?=csrf_field()?>
        <div class="field">
          <label for="full_name">Full name <span class="req">*</span></label>
          <div class="input-wrap"><?=icon('user')?>
            <input type="text" id="full_name" name="full_name" value="<?=old('full_name')?>" placeholder="Sarah Mitchell" required autocomplete="name">
          </div>
        </div>
        <div class="form-grid">
          <div class="field">
            <label for="email">Email address <span class="req">*</span></label>
            <div class="input-wrap"><?=icon('mail')?>
              <input type="email" id="email" name="email" value="<?=old('email')?>" placeholder="you@example.com" required autocomplete="email">
            </div>
          </div>
          <div class="field">
            <label for="phone">Phone number</label>
            <div class="input-wrap"><?=icon('phone')?>
              <input type="tel" id="phone" name="phone" value="<?=old('phone')?>" placeholder="+1 555 000 0000" autocomplete="tel">
            </div>
          </div>
        </div>
        <div class="field">
          <label for="password">Password <span class="req">*</span></label>
          <div class="input-wrap"><?=icon('lock')?>
            <input type="password" id="password" name="password" placeholder="Min 8 chars, upper, lower, number" required autocomplete="new-password">
            <button type="button" class="pw-toggle" aria-label="Toggle visibility">
              <span class="eye-on"><?=icon('eye')?></span>
              <span class="eye-off" style="display:none"><?=icon('eye-off')?></span>
            </button>
          </div>
        </div>
        <div class="field">
          <label for="confirm_password">Confirm password <span class="req">*</span></label>
          <div class="input-wrap"><?=icon('lock')?>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat your password" required autocomplete="new-password">
          </div>
        </div>
        <div class="field">
          <label class="cb-row"><input type="checkbox" required> I agree to the <a href="#">Terms & Conditions</a> and <a href="#">Privacy Policy</a></label>
        </div>
        <button type="submit" class="btn btn-primary btn-block btn-lg"><?=icon('user')?> Create Account</button>
      </form>
      <div class="form-footer">Already have an account? <a href="<?=BASE_URL?>/auth/login.php">Log in</a></div>
    </div>
  </div>
</div>
<script src="<?=BASE_URL?>/assets/js/app.js"></script>
</body>
</html>
