<?php
require_once __DIR__.'/../includes/bootstrap.php';
require_once __DIR__.'/../includes/customer_layout.php';
require_login('customer');
$pageTitle='Payments'; $pdo=db(); $uid=current_uid();
$payments=$pdo->prepare('SELECT py.*,p.policy_number,pp.plan_name FROM payments py JOIN policies p ON p.id=py.policy_id JOIN policy_plans pp ON pp.id=p.plan_id WHERE py.user_id=? ORDER BY py.created_at DESC');
$payments->execute(array($uid)); $payments=$payments->fetchAll();
$total=$pdo->prepare('SELECT SUM(amount) FROM payments WHERE user_id=? AND status="completed"');
$total->execute(array($uid)); $total=(float)$total->fetchColumn();
render_customer_header('payments');
?>
<div class="page-head">
  <div><h2>Payment History</h2><p>All premium payments for your policies.</p></div>
  <div class="stat-card" style="min-width:0;padding:14px 18px"><div class="si"><?=icon('dollar')?></div><div><div class="sv"><?=format_money($total)?></div><div class="sl">Total Paid</div></div></div>
</div>
<?php if(empty($payments)): ?>
<div class="card"><div class="empty-state"><?=icon('credit-card')?><h3>No payments yet</h3><p>Once you pay a premium, your payment history will appear here.</p></div></div>
<?php else: ?>
<div class="table-wrap">
<table><thead><tr><th>Transaction Ref</th><th>Policy</th><th>Plan</th><th>Amount</th><th>Method</th><th>Status</th><th>Date</th></tr></thead>
<tbody>
<?php foreach($payments as $py): ?>
<tr>
  <td class="cell-strong" style="font-size:13px;font-family:monospace"><?=e($py['transaction_ref'])?></td>
  <td><?=e($py['policy_number']??'—')?></td>
  <td><?=e($py['plan_name'])?></td>
  <td style="font-weight:700;color:var(--navy-900)"><?=format_money($py['amount'])?></td>
  <td><?=ucfirst(str_replace('_',' ',$py['payment_method']))?><?=$py['card_last4']?' ···'.$py['card_last4']:''?></td>
  <td><?=status_badge($py['status'])?></td>
  <td><?=format_date($py['created_at'])?></td>
</tr>
<?php endforeach; ?>
</tbody></table>
</div>
<?php endif; ?>
<?php render_customer_footer(); ?>
