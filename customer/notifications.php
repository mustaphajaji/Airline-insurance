<?php
require_once __DIR__.'/../includes/bootstrap.php';
require_once __DIR__.'/../includes/customer_layout.php';
require_login('customer');
$pageTitle='Notifications'; $pdo=db(); $uid=current_uid();
// Mark all as read
$pdo->prepare('UPDATE notifications SET is_read=1 WHERE user_id=?')->execute(array($uid));
$notifs=$pdo->prepare('SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC');
$notifs->execute(array($uid)); $notifs=$notifs->fetchAll();
render_customer_header('notifications');
?>
<div class="page-head"><div><h2>Notifications</h2><p>All updates regarding your account, policies and claims.</p></div></div>
<?php if(empty($notifs)): ?>
<div class="card"><div class="empty-state"><?=icon('bell')?><h3>No notifications</h3><p>You are all caught up.</p></div></div>
<?php else: ?>
<div class="card card-pad">
<?php foreach($notifs as $n): ?>
<div class="notif-item">
  <div class="ni-ico"><?=icon('bell')?></div>
  <div style="flex:1;min-width:0">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px">
      <div class="ni-title"><?=e($n['title'])?></div>
      <div class="ni-time" style="white-space:nowrap"><?=format_date($n['created_at'],'M j, Y · g:i A')?></div>
    </div>
    <div class="ni-msg" style="margin-top:4px"><?=e($n['message'])?></div>
  </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
<?php render_customer_footer(); ?>
