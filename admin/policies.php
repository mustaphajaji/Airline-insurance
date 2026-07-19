<?php
require_once __DIR__.'/../includes/bootstrap.php';
require_once __DIR__.'/../includes/admin_layout.php';
require_login('admin');
$pageTitle='Policies'; $pdo=db();
$status=trim($_GET['status']??'');
$search=trim($_GET['q']??'');
$sql='SELECT p.*,u.full_name,u.email,pp.plan_name,pp.category FROM policies p JOIN users u ON u.id=p.user_id JOIN policy_plans pp ON pp.id=p.plan_id WHERE p.status NOT IN ("pending")';
$params=array();
if($status){ $sql.=' AND p.status=?'; $params[]=$status; }
if($search){ $sql.=' AND (u.full_name LIKE ? OR p.policy_number LIKE ?)'; $like="%$search%"; $params=array_merge($params,array($like,$like)); }
$sql.=' ORDER BY p.updated_at DESC';
$stmt=$pdo->prepare($sql); $stmt->execute($params); $policies=$stmt->fetchAll();
render_admin_header('policies');
?>
<div class="page-head"><div><h2>Active Policies</h2><p>All approved, active and expired policies.</p></div></div>
<div class="filter-bar">
  <form method="get" style="display:flex;gap:10px;flex-wrap:wrap;flex:1">
    <select name="status" onchange="this.form.submit()" style="min-width:160px">
      <option value="">All Statuses</option>
      <?php foreach(array('approved','active','expired','cancelled','rejected') as $st): ?>
      <option value="<?=$st?>" <?=$status===$st?'selected':''?>><?=ucfirst($st)?></option>
      <?php endforeach; ?>
    </select>
    <div class="search-box"><?=icon('search')?><input type="text" name="q" value="<?=e($search)?>" placeholder="Name or policy number…"></div>
    <button type="submit" class="btn btn-secondary btn-sm"><?=icon('search')?> Search</button>
    <?php if($search||$status): ?><a href="<?=BASE_URL?>/admin/policies.php" class="btn btn-ghost btn-sm">Clear</a><?php endif; ?>
  </form>
</div>
<?php if(empty($policies)): ?>
<div class="card"><div class="empty-state"><?=icon('shield')?><h3>No policies found</h3></div></div>
<?php else: ?>
<div class="table-wrap">
<table><thead><tr><th>Policy #</th><th>Customer</th><th>Plan</th><th>Premium</th><th>Coverage</th><th>Start</th><th>End</th><th>Status</th></tr></thead>
<tbody>
<?php foreach($policies as $pol): ?>
<tr>
  <td class="cell-strong"><?=e($pol['policy_number']??'—')?></td>
  <td><div class="cell-strong"><?=e($pol['full_name'])?></div><div style="font-size:11.5px;color:var(--ink-500)"><?=e($pol['email'])?></div></td>
  <td><?=e($pol['plan_name'])?></td>
  <td><?=format_money($pol['premium_amount'])?></td>
  <td><?=format_money($pol['coverage_amount'])?></td>
  <td><?=format_date($pol['start_date'])?></td>
  <td><?=format_date($pol['end_date'])?></td>
  <td><?=status_badge($pol['status'])?></td>
</tr>
<?php endforeach; ?>
</tbody></table>
</div>
<?php endif; ?>
<?php render_admin_footer(); ?>
