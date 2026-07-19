<?php
require_once __DIR__.'/../includes/bootstrap.php';
require_once __DIR__.'/../includes/customer_layout.php';
require_login('customer');
$u=current_user(); $pdo=db(); $uid=current_uid();
$pageTitle='Dashboard';

$totalPolicies=(int)$pdo->prepare('SELECT COUNT(*) FROM policies WHERE user_id=?')->execute(array($uid))||0;
$s=$pdo->prepare('SELECT COUNT(*) FROM policies WHERE user_id=?'); $s->execute(array($uid)); $totalPolicies=(int)$s->fetchColumn();
$s=$pdo->prepare('SELECT COUNT(*) FROM policies WHERE user_id=? AND status="active"'); $s->execute(array($uid)); $activePolicies=(int)$s->fetchColumn();
$s=$pdo->prepare('SELECT COUNT(*) FROM claims WHERE user_id=?'); $s->execute(array($uid)); $totalClaims=(int)$s->fetchColumn();
$s=$pdo->prepare('SELECT COUNT(*) FROM payments WHERE user_id=? AND status="completed"'); $s->execute(array($uid)); $totalPayments=(int)$s->fetchColumn();
$s=$pdo->prepare('SELECT SUM(amount) FROM payments WHERE user_id=? AND status="completed"'); $s->execute(array($uid)); $totalPaid=(float)$s->fetchColumn();

$recentPolicies=$pdo->prepare('SELECT p.*,pp.plan_name,pp.category FROM policies p JOIN policy_plans pp ON pp.id=p.plan_id WHERE p.user_id=? ORDER BY p.created_at DESC LIMIT 5');
$recentPolicies->execute(array($uid)); $recentPolicies=$recentPolicies->fetchAll();

$recentClaims=$pdo->prepare('SELECT c.*,p.policy_number,pp.plan_name FROM claims c JOIN policies p ON p.id=c.policy_id JOIN policy_plans pp ON pp.id=p.plan_id WHERE c.user_id=? ORDER BY c.created_at DESC LIMIT 4');
$recentClaims->execute(array($uid)); $recentClaims=$recentClaims->fetchAll();

$notifs=$pdo->prepare('SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 5');
$notifs->execute(array($uid)); $notifs=$notifs->fetchAll();

render_customer_header('dashboard');
?>
<div class="greet-row">
  <div>
    <h2>Good <?=date('H')<12?'morning':(date('H')<17?'afternoon':'evening')?>, <?=e(explode(' ',$u['full_name'])[0])?></h2>
    <p>Here is a summary of your insurance account.</p>
  </div>
  <a href="<?=BASE_URL?>/customer/apply.php" class="btn btn-primary"><?=icon('plus')?> New Application</a>
</div>

<!-- Stats -->
<div class="stat-grid">
  <div class="stat-card"><div class="si"><?=icon('shield')?></div><div><div class="sv"><?=$totalPolicies?></div><div class="sl">Total Policies</div></div></div>
  <div class="stat-card"><div class="si"><?=icon('check-circle')?></div><div><div class="sv"><?=$activePolicies?></div><div class="sl">Active Policies</div></div></div>
  <div class="stat-card"><div class="si"><?=icon('file-text')?></div><div><div class="sv"><?=$totalClaims?></div><div class="sl">Claims Filed</div></div></div>
  <div class="stat-card"><div class="si"><?=icon('credit-card')?></div><div><div class="sv"><?=format_money($totalPaid)?></div><div class="sl">Premiums Paid</div></div></div>
</div>

<!-- Quick actions -->
<div class="quick-grid">
  <a href="<?=BASE_URL?>/customer/plans.php" class="qa"><div class="qi"><?=icon('briefcase')?></div><div class="qt"><strong>Browse Plans</strong><span>View all coverage options</span></div></a>
  <a href="<?=BASE_URL?>/customer/apply.php" class="qa"><div class="qi"><?=icon('file-text')?></div><div class="qt"><strong>New Application</strong><span>Apply for a policy</span></div></a>
  <a href="<?=BASE_URL?>/customer/claims.php" class="qa"><div class="qi"><?=icon('alert-triangle')?></div><div class="qt"><strong>File a Claim</strong><span>Report an incident</span></div></a>
  <a href="<?=BASE_URL?>/customer/payments.php" class="qa"><div class="qi"><?=icon('credit-card')?></div><div class="qt"><strong>Payments</strong><span>View payment history</span></div></a>
</div>

<div class="dash-grid">
  <!-- Recent Policies -->
  <div class="card">
    <div class="card-head"><h3>Recent Policies</h3><a href="<?=BASE_URL?>/customer/policies.php" class="btn btn-ghost btn-sm">View all <?=icon('chevron-right')?></a></div>
    <div class="card-body" style="padding:0 22px">
      <?php if(empty($recentPolicies)): ?>
        <div class="empty-state"><?=icon('shield')?><h3>No policies yet</h3><p>Apply for your first policy to get started.</p><a href="<?=BASE_URL?>/customer/apply.php" class="btn btn-primary btn-sm">Apply Now</a></div>
      <?php else: foreach($recentPolicies as $pol): ?>
        <div class="list-row">
          <div class="lr-left">
            <div class="lr-ico"><?=icon(category_icon($pol['category']))?></div>
            <div><div class="lr-title"><?=e($pol['plan_name'])?></div><div class="lr-sub"><?=e($pol['policy_number']??'Pending')?> &middot; <?=format_date($pol['created_at'])?></div></div>
          </div>
          <?=status_badge($pol['status'])?>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>

  <!-- Notifications -->
  <div class="card">
    <div class="card-head"><h3>Notifications</h3><a href="<?=BASE_URL?>/customer/notifications.php" class="btn btn-ghost btn-sm">All <?=icon('chevron-right')?></a></div>
    <div class="card-body" style="padding:0 22px">
      <?php if(empty($notifs)): ?>
        <div class="empty-state" style="padding:30px 0"><?=icon('bell')?><p>No notifications yet.</p></div>
      <?php else: foreach($notifs as $n): ?>
        <div class="notif-item<?=$n['is_read']?'':' unread'?>">
          <div class="ni-ico"><?=icon('bell')?></div>
          <div><div class="ni-title"><?=e($n['title'])?></div><div class="ni-msg"><?=e(mb_strimwidth($n['message'],0,80,'…'))?></div><div class="ni-time"><?=format_date($n['created_at'],'M j · g:i A')?></div></div>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
</div>

<!-- Recent Claims -->
<?php if(!empty($recentClaims)): ?>
<div class="card" style="margin-top:20px">
  <div class="card-head"><h3>Recent Claims</h3><a href="<?=BASE_URL?>/customer/claims.php" class="btn btn-ghost btn-sm">View all <?=icon('chevron-right')?></a></div>
  <div class="table-wrap" style="border:none">
    <table><thead><tr><th>Claim #</th><th>Policy</th><th>Type</th><th>Amount Claimed</th><th>Date</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach($recentClaims as $cl): ?>
      <tr>
        <td class="cell-strong"><?=e($cl['claim_number'])?></td>
        <td><?=e($cl['policy_number']??'—')?></td>
        <td><?=e($cl['claim_type'])?></td>
        <td><?=format_money($cl['amount_claimed'])?></td>
        <td><?=format_date($cl['created_at'])?></td>
        <td><?=status_badge($cl['status'])?></td>
      </tr>
    <?php endforeach; ?>
    </tbody></table>
  </div>
</div>
<?php endif; ?>
<?php render_customer_footer(); ?>
