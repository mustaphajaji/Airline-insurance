<?php
require_once __DIR__.'/../includes/bootstrap.php';
require_once __DIR__.'/../includes/customer_layout.php';
require_login('customer');
$pageTitle='My Claims'; $pdo=db(); $uid=current_uid();
$claims=$pdo->prepare('SELECT c.*,p.policy_number,pp.plan_name FROM claims c JOIN policies p ON p.id=c.policy_id JOIN policy_plans pp ON pp.id=p.plan_id WHERE c.user_id=? ORDER BY c.created_at DESC');
$claims->execute(array($uid)); $claims=$claims->fetchAll();
$activePolicies=$pdo->prepare('SELECT p.*,pp.plan_name FROM policies p JOIN policy_plans pp ON pp.id=p.plan_id WHERE p.user_id=? AND p.status="active"');
$activePolicies->execute(array($uid)); $activePolicies=$activePolicies->fetchAll();
render_customer_header('claims');
?>
<div class="page-head">
  <div><h2>My Claims</h2><p>Track all your submitted insurance claims.</p></div>
  <?php if(!empty($activePolicies)): ?><a href="<?=BASE_URL?>/customer/file_claim.php" class="btn btn-primary"><?=icon('plus')?> File a Claim</a><?php endif; ?>
</div>
<?php if(empty($claims)): ?>
<div class="card"><div class="empty-state"><?=icon('file-text')?><h3>No claims filed</h3><p>You have no claims yet. If you have an active policy and encounter an issue, you can file a claim.</p><?php if(!empty($activePolicies)): ?><a href="<?=BASE_URL?>/customer/file_claim.php" class="btn btn-primary btn-sm">File Your First Claim</a><?php endif; ?></div></div>
<?php else: ?>
<div class="table-wrap">
<table><thead><tr><th>Claim #</th><th>Policy</th><th>Type</th><th>Incident Date</th><th>Claimed</th><th>Approved</th><th>Status</th><th>Filed</th></tr></thead>
<tbody>
<?php foreach($claims as $cl): ?>
<tr>
  <td class="cell-strong"><?=e($cl['claim_number'])?></td>
  <td><?=e($cl['policy_number']??'—')?><div style="font-size:11.5px;color:var(--ink-500)"><?=e($cl['plan_name'])?></div></td>
  <td><?=e($cl['claim_type'])?></td>
  <td><?=format_date($cl['incident_date'])?></td>
  <td><?=format_money($cl['amount_claimed'])?></td>
  <td><?=$cl['amount_approved']!==null?format_money($cl['amount_approved']):'<span style="color:var(--ink-300)">—</span>'?></td>
  <td><?=status_badge($cl['status'])?></td>
  <td><?=format_date($cl['created_at'])?></td>
</tr>
<?php if($cl['admin_notes']): ?>
<tr><td colspan="8" style="padding:4px 16px 12px;background:var(--blue-50)"><span style="font-size:12.5px;color:var(--ink-700)"><strong>Admin note:</strong> <?=e($cl['admin_notes'])?></span></td></tr>
<?php endif; ?>
<?php endforeach; ?>
</tbody></table>
</div>
<?php endif; ?>
<?php render_customer_footer(); ?>
