<?php
require_once __DIR__.'/../includes/bootstrap.php';
require_once __DIR__.'/../includes/admin_layout.php';
require_login('admin');
$pageTitle='Payments'; $pdo=db();
$search=trim($_GET['q']??'');
$sql='SELECT py.*,u.full_name,u.email,p.policy_number,pp.plan_name FROM payments py JOIN users u ON u.id=py.user_id JOIN policies p ON p.id=py.policy_id JOIN policy_plans pp ON pp.id=p.plan_id WHERE 1=1';
$params=array();
if($search){ $sql.=' AND (u.full_name LIKE ? OR py.transaction_ref LIKE ?)'; $like="%$search%"; $params=array_merge($params,array($like,$like)); }
$sql.=' ORDER BY py.created_at DESC';
$stmt=$pdo->prepare($sql); $stmt->execute($params); $payments=$stmt->fetchAll();
$total=$pdo->query('SELECT COALESCE(SUM(amount),0) FROM payments WHERE status="completed"')->fetchColumn();
$month=$pdo->query('SELECT COALESCE(SUM(amount),0) FROM payments WHERE status="completed" AND MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())')->fetchColumn();
render_admin_header('payments');
?>
<div class="page-head">
  <div><h2>Payment Records</h2><p>All premium payments received.</p></div>
  <div style="display:flex;gap:12px">
    <div class="stat-card" style="padding:12px 18px"><div class="si"><?=icon('dollar')?></div><div><div class="sv" style="font-size:18px"><?=format_money($total)?></div><div class="sl">Total Revenue</div></div></div>
    <div class="stat-card" style="padding:12px 18px"><div class="si"><?=icon('trending-up')?></div><div><div class="sv" style="font-size:18px"><?=format_money($month)?></div><div class="sl">This Month</div></div></div>
  </div>
</div>
<div class="filter-bar">
  <form method="get" style="display:flex;gap:10px;flex-wrap:wrap;flex:1">
    <div class="search-box"><?=icon('search')?><input type="text" name="q" value="<?=e($search)?>" placeholder="Customer name or transaction ref…"></div>
    <button type="submit" class="btn btn-secondary btn-sm"><?=icon('search')?> Search</button>
    <?php if($search): ?><a href="<?=BASE_URL?>/admin/payments.php" class="btn btn-ghost btn-sm">Clear</a><?php endif; ?>
  </form>
</div>
<?php if(empty($payments)): ?>
<div class="card"><div class="empty-state"><?=icon('credit-card')?><h3>No payments found</h3></div></div>
<?php else: ?>
<div class="table-wrap">
<table><thead><tr><th>Transaction Ref</th><th>Customer</th><th>Policy</th><th>Plan</th><th>Amount</th><th>Method</th><th>Status</th><th>Date</th></tr></thead>
<tbody>
<?php foreach($payments as $py): ?>
<tr>
  <td class="cell-strong" style="font-size:12.5px;font-family:monospace"><?=e($py['transaction_ref'])?></td>
  <td><div class="cell-strong"><?=e($py['full_name'])?></div><div style="font-size:11.5px;color:var(--ink-500)"><?=e($py['email'])?></div></td>
  <td><?=e($py['policy_number']??'—')?></td>
  <td><?=e($py['plan_name'])?></td>
  <td style="font-weight:700;color:var(--ok-600)"><?=format_money($py['amount'])?></td>
  <td><?=ucfirst(str_replace('_',' ',$py['payment_method']))?><?=$py['card_last4']?' ···'.$py['card_last4']:''?></td>
  <td><?=status_badge($py['status'])?></td>
  <td><?=format_date($py['created_at'])?></td>
</tr>
<?php endforeach; ?>
</tbody></table>
</div>
<?php endif; ?>
<?php render_admin_footer(); ?>
