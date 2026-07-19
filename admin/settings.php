<?php
require_once __DIR__.'/../includes/bootstrap.php';
require_once __DIR__.'/../includes/admin_layout.php';
require_login('admin');
$pageTitle='Settings'; $pdo=db(); $uid=current_uid(); $u=current_user();
$errors=array();
if($_SERVER['REQUEST_METHOD']==='POST'){
    csrf_verify();
    $section=$_POST['section']??'';
    if($section==='profile'){
        $name=trim($_POST['full_name']??'');
        $phone=trim($_POST['phone']??'');
        if(strlen($name)<2) $errors[]='Name must be at least 2 characters.';
        if(empty($errors)){
            $pdo->prepare('UPDATE users SET full_name=?,phone=? WHERE id=?')->execute(array($name,$phone,$uid));
            $_SESSION['name']=$name;
            flash('success','Profile updated.'); redirect('/admin/settings.php');
        }
    } elseif($section==='password'){
        $cur=$_POST['current_password']??'';
        $new=$_POST['new_password']??'';
        $con=$_POST['confirm_password']??'';
        if(!password_verify($cur,$u['password_hash'])) $errors[]='Current password is incorrect.';
        else {
            $perr=validate_password($new);
            if($perr) $errors[]=$perr;
            elseif($new!==$con) $errors[]='New passwords do not match.';
            else {
                $pdo->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute(array(password_hash($new,PASSWORD_BCRYPT),$uid));
                flash('success','Password updated.'); redirect('/admin/settings.php');
            }
        }
    }
}
$u=current_user();
render_admin_header('settings');
?>
<div class="page-head"><div><h2>Settings</h2><p>Manage your admin account and preferences.</p></div></div>
<?php if(!empty($errors)): ?><div class="alert alert-error"><?=icon('alert-triangle')?><div><?php foreach($errors as $e) echo '<div>'.e($e).'</div>'; ?></div></div><?php endif; ?>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;max-width:900px">
<div class="card card-pad">
  <h3 style="margin-bottom:18px"><?=icon('user')?> Admin Profile</h3>
  <form method="post" action="" novalidate>
    <?=csrf_field()?><input type="hidden" name="section" value="profile">
    <div class="field"><label for="full_name">Full Name</label><input type="text" id="full_name" name="full_name" value="<?=e($u['full_name'])?>" required></div>
    <div class="field"><label for="email">Email</label><input type="email" value="<?=e($u['email'])?>" disabled style="background:var(--surface);cursor:not-allowed"></div>
    <div class="field"><label for="phone">Phone</label><input type="tel" id="phone" name="phone" value="<?=e($u['phone']??'')?>"></div>
    <button type="submit" class="btn btn-primary"><?=icon('check-circle')?> Save Changes</button>
  </form>
</div>
<div class="card card-pad">
  <h3 style="margin-bottom:18px"><?=icon('lock')?> Change Password</h3>
  <form method="post" action="" novalidate>
    <?=csrf_field()?><input type="hidden" name="section" value="password">
    <div class="field"><label for="current_password">Current Password</label><div class="input-wrap"><?=icon('lock')?><input type="password" id="current_password" name="current_password" required><button type="button" class="pw-toggle"><span class="eye-on"><?=icon('eye')?></span><span class="eye-off" style="display:none"><?=icon('eye-off')?></span></button></div></div>
    <div class="field"><label for="new_password">New Password</label><div class="input-wrap"><?=icon('lock')?><input type="password" id="new_password" name="new_password" required><button type="button" class="pw-toggle"><span class="eye-on"><?=icon('eye')?></span><span class="eye-off" style="display:none"><?=icon('eye-off')?></span></button></div></div>
    <div class="field"><label for="confirm_password">Confirm New Password</label><div class="input-wrap"><?=icon('lock')?><input type="password" id="confirm_password" name="confirm_password" required></div></div>
    <button type="submit" class="btn btn-secondary"><?=icon('lock')?> Update Password</button>
  </form>
  <hr class="divider">
  <div class="kv-list">
    <div class="kv-row"><span class="k">Role</span><span class="v"><span class="badge badge-active">Administrator</span></span></div>
    <div class="kv-row"><span class="k">Last Login</span><span class="v"><?=$u['last_login']?format_datetime($u['last_login']):'—'?></span></div>
    <div class="kv-row"><span class="k">Member Since</span><span class="v"><?=format_date($u['created_at'])?></span></div>
  </div>
</div>
</div>
<?php render_admin_footer(); ?>
