<?php
require_once __DIR__.'/../includes/bootstrap.php';
require_once __DIR__.'/../includes/customer_layout.php';
require_login('customer');
$pageTitle='My Policies'; $pdo=db(); $uid=current_uid();
$filter=trim($_GET['status']??'');
$sql='SELECT p.*,pp.plan_name,pp.category FROM policies p JOIN policy_plans pp ON pp.id=p.plan_id WHERE p.user_id=?';
$params=array($uid);
if($filter){ $sql.=' AND p.status=?'; $params[]=$filter; }
$sql.=' ORDER BY p.created_at DESC';
$stmt=$pdo->prepare($sql); $stmt->execute($params); $policies=$stmt->fetchAll();
render_customer_header('policies');
?>
<div class="page-head">
  <div><h2>My Policies</h2><p>All your insurance applications and active policies.</p></div>
  <a href="<?=BASE_URL?>/customer/apply.php" class="btn btn-primary"><?=icon('plus')?> New Application</a>
</div>
<div class="filter-bar">
  <form method="get" style="display:flex;gap:10px;flex-wrap:wrap;width:100%">
    <select name="status" onchange="this.form.submit()" style="min-width:180px">
      <option value="">All Statuses</option>
      <?php foreach(array('pending','approved','rejected','active','expired','cancelled') as $st): ?>
      <option value="<?=$st?>" <?=$filter===$st?'selected':''?>><?=ucfirst($st)?></option>
      <?php endforeach; ?>
    </select>
    <?php if($filter): ?><a href="<?=BASE_URL?>/customer/policies.php" class="btn btn-ghost btn-sm">Clear filter</a><?php endif; ?>
  </form>
</div>
<?php if(empty($policies)): ?>
<div class="card"><div class="empty-state"><?=icon('shield')?><h3>No policies found</h3><p>You have not applied for any insurance policies yet.</p><a href="<?=BASE_URL?>/customer/apply.php" class="btn btn-primary">Apply Now</a></div></div>
<?php else: ?>
<div style="display:flex;flex-direction:column;gap:14px">
<?php foreach($policies as $pol): ?>
<div class="bc">
  <div class="bc-main">
    <div class="bc-eye"><?=icon(category_icon($pol['category']))?> <?=e(category_label($pol['category']))?></div>
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap">
      <div>
        <h3 style="margin-bottom:4px"><?=e($pol['plan_name'])?></h3>
        <div style="font-size:13px;color:var(--ink-500)"><?=e($pol['policy_number']??'Pending assignment')?></div>
      </div>
      <?=status_badge($pol['status'])?>
    </div>
    <div class="bc-amounts">
      <div><span>Premium</span><strong><?=format_money($pol['premium_amount'])?></strong></div>
      <div><span>Coverage</span><strong><?=format_money($pol['coverage_amount'])?></strong></div>
      <?php if($pol['start_date']): ?><div><span>Valid from</span><strong><?=format_date($pol['start_date'])?></strong></div><?php endif; ?>
    </div>
    <div style="margin-top:12px;font-size:13px;color:var(--ink-500)">
      <?php if($pol['airline_name']): ?>✈ <?=e($pol['airline_name'])?> &middot; <?=e($pol['departure_city'])?> → <?=e($pol['destination_city'])?> &middot; <?=format_date($pol['flight_date'])?><?php endif; ?>
    </div>
    <?php if($pol['status']==='approved'): ?>
    <div style="margin-top:12px"><a href="<?=BASE_URL?>/customer/pay.php?policy=<?=$pol['id']?>" class="btn btn-primary btn-sm"><?=icon('credit-card')?> Pay Premium</a></div>
    <?php elseif($pol['status']==='active'): ?>
    <div style="margin-top:12px"><a href="<?=BASE_URL?>/customer/file_claim.php?policy=<?=$pol['id']?>" class="btn btn-secondary btn-sm"><?=icon('alert-triangle')?> File a Claim</a></div>
    <?php endif; ?>
    <?php if($pol['admin_notes']): ?><div style="margin-top:12px;padding:10px;background:var(--blue-50);border-radius:var(--r-sm);font-size:13px;color:var(--ink-700)"><strong>Admin note:</strong> <?=e($pol['admin_notes'])?></div><?php endif; ?>
  </div>
  <div class="bc-stub">
    <div class="si"><?=icon(category_icon($pol['category']))?></div>
    <div class="sc" style="font-size:11px"><?=format_date($pol['created_at'],'M j, Y')?></div>
    <?=status_badge($pol['status'])?>
  </div>
</div>
<?php endforeach; endif; ?>
</div>
<?php render_customer_footer(); ?>
