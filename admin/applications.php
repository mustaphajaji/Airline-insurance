<?php
require_once __DIR__.'/../includes/bootstrap.php';
require_once __DIR__.'/../includes/admin_layout.php';
require_login('admin');
$pageTitle='Applications'; $pdo=db(); $adminId=current_uid();

// Handle inline review action
if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['action'])){
    csrf_verify();
    $pid=(int)($_POST['policy_id']??0);
    $action=$_POST['action'];
    $note=trim($_POST['admin_notes']??'');
    if($pid&&in_array($action,array('approve','reject'))){
        $newStatus=$action==='approve'?'approved':'rejected';
        $pdo->prepare('UPDATE policies SET status=?,admin_notes=?,reviewed_by=?,reviewed_at=NOW() WHERE id=?')->execute(array($newStatus,$note,$adminId,$pid));
        $pol=$pdo->prepare('SELECT p.*,u.id as uid,u.full_name,pp.plan_name FROM policies p JOIN users u ON u.id=p.user_id JOIN policy_plans pp ON pp.id=p.plan_id WHERE p.id=?');
        $pol->execute(array($pid)); $pol=$pol->fetch();
        if($pol) notify_user($pol['uid'],'Application '.$newStatus,'Your application for '.$pol['plan_name'].' has been '.$newStatus.($note?' Note: '.$note:'').'.');
        flash('success','Application '.$newStatus.'.');
    }
    redirect('/admin/applications.php');
}

$status=trim($_GET['status']??'');
$search=trim($_GET['q']??'');
$sql='SELECT p.*,u.full_name,u.email,pp.plan_name,pp.category FROM policies p JOIN users u ON u.id=p.user_id JOIN policy_plans pp ON pp.id=p.plan_id WHERE 1=1';
$params=array();
if($status){ $sql.=' AND p.status=?'; $params[]=$status; }
if($search){ $sql.=' AND (u.full_name LIKE ? OR u.email LIKE ? OR p.policy_number LIKE ?)'; $like="%$search%"; $params=array_merge($params,array($like,$like,$like)); }
$sql.=' ORDER BY p.created_at DESC';
$stmt=$pdo->prepare($sql); $stmt->execute($params); $apps=$stmt->fetchAll();
$counts=array(); foreach(array('pending','approved','rejected','active','expired') as $s){ $x=$pdo->prepare('SELECT COUNT(*) FROM policies WHERE status=?'); $x->execute(array($s)); $counts[$s]=(int)$x->fetchColumn(); }
render_admin_header('applications');
?>
<div class="page-head"><div><h2>Applications</h2><p>Review and manage customer insurance applications.</p></div></div>
<div class="tab-bar">
  <a href="?status=" class="<?=$status===''?'active':''?>">All <span class="cp"><?=array_sum($counts)?></span></a>
  <?php foreach(array('pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected','active'=>'Active','expired'=>'Expired') as $k=>$l): ?>
  <a href="?status=<?=$k?>" class="<?=$status===$k?'active':''?>"><?=$l?> <span class="cp"><?=$counts[$k]?></span></a>
  <?php endforeach; ?>
</div>
<div class="filter-bar">
  <form method="get" style="display:flex;gap:10px;flex-wrap:wrap;flex:1">
    <input type="hidden" name="status" value="<?=e($status)?>">
    <div class="search-box"><?=icon('search')?><input type="text" name="q" value="<?=e($search)?>" placeholder="Search name, email, policy…"></div>
    <button type="submit" class="btn btn-secondary btn-sm"><?=icon('search')?> Search</button>
    <?php if($search): ?><a href="?status=<?=e($status)?>" class="btn btn-ghost btn-sm">Clear</a><?php endif; ?>
  </form>
</div>
<?php if(empty($apps)): ?>
<div class="card"><div class="empty-state"><?=icon('file-text')?><h3>No applications found</h3><p>No applications match your current filters.</p></div></div>
<?php else: ?>
<div class="table-wrap">
<table><thead><tr><th>Customer</th><th>Plan</th><th>Flight</th><th>Premium</th><th>Coverage</th><th>Status</th><th>Applied</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach($apps as $app): ?>
<tr>
  <td><div class="cell-strong"><?=e($app['full_name'])?></div><div style="font-size:11.5px;color:var(--ink-500)"><?=e($app['email'])?></div></td>
  <td><div style="font-size:13.5px;font-weight:600"><?=e($app['plan_name'])?></div><div style="font-size:11.5px;color:var(--ink-500)"><?=e(category_label($app['category']))?></div></td>
  <td style="font-size:13px"><?=e($app['airline_name']??'—')?><div style="font-size:11.5px;color:var(--ink-500)"><?=e($app['departure_city']??'')?> → <?=e($app['destination_city']??'')?></div></td>
  <td><?=format_money($app['premium_amount'])?></td>
  <td><?=format_money($app['coverage_amount'])?></td>
  <td><?=status_badge($app['status'])?></td>
  <td><?=format_date($app['created_at'])?></td>
  <td>
    <div class="row-actions">
      <?php if($app['status']==='pending'): ?>
      <button class="btn btn-sm btn-primary" data-modal-open="modal-<?=$app['id']?>"><?=icon('edit')?> Review</button>
      <?php else: ?>
      <button class="btn btn-sm btn-ghost" data-modal-open="modal-<?=$app['id']?>"><?=icon('eye')?> View</button>
      <?php endif; ?>
    </div>
  </td>
</tr>
<?php endforeach; ?>
</tbody></table>
</div>

<!-- Review Modals -->
<?php foreach($apps as $app): ?>
<div class="modal-overlay" id="modal-<?=$app['id']?>">
  <div class="modal-box">
    <h3><?=icon('file-text')?> <?=e($app['plan_name'])?></h3>
    <p style="color:var(--ink-500);font-size:13.5px;margin-bottom:16px"><?=e($app['full_name'])?> &middot; <?=e($app['email'])?></p>
    <div class="kv-list" style="margin-bottom:16px">
      <div class="kv-row"><span class="k">Policy #</span><span class="v"><?=e($app['policy_number']??'Not assigned')?></span></div>
      <div class="kv-row"><span class="k">Category</span><span class="v"><?=e(category_label($app['category']))?></span></div>
      <div class="kv-row"><span class="k">Airline</span><span class="v"><?=e($app['airline_name']??'—')?></span></div>
      <div class="kv-row"><span class="k">Route</span><span class="v"><?=e($app['departure_city']??'—')?> → <?=e($app['destination_city']??'—')?></span></div>
      <div class="kv-row"><span class="k">Flight Date</span><span class="v"><?=format_date($app['flight_date'])?></span></div>
      <div class="kv-row"><span class="k">Premium</span><span class="v"><?=format_money($app['premium_amount'])?>/yr</span></div>
      <div class="kv-row"><span class="k">Coverage</span><span class="v"><?=format_money($app['coverage_amount'])?></span></div>
      <div class="kv-row"><span class="k">Status</span><span class="v"><?=status_badge($app['status'])?></span></div>
      <div class="kv-row"><span class="k">Applied</span><span class="v"><?=format_datetime($app['created_at'])?></span></div>
      <?php if($app['beneficiary_name']): ?><div class="kv-row"><span class="k">Beneficiary</span><span class="v"><?=e($app['beneficiary_name'])?> (<?=e($app['beneficiary_relationship']??'—')?>)</span></div><?php endif; ?>
    </div>
    <?php if($app['notes']): ?><div style="padding:10px;background:var(--blue-50);border-radius:var(--r-sm);font-size:13px;margin-bottom:14px"><strong>Customer notes:</strong> <?=e($app['notes'])?></div><?php endif; ?>
    <?php if($app['status']==='pending'): ?>
    <form method="post" action="">
      <?=csrf_field()?><input type="hidden" name="policy_id" value="<?=$app['id']?>">
      <div class="field"><label for="notes-<?=$app['id']?>">Admin Note (optional)</label><textarea id="notes-<?=$app['id']?>" name="admin_notes" rows="2" placeholder="Add a note for the customer…"><?=e($app['admin_notes']??'')?></textarea></div>
      <div class="modal-actions">
        <button type="button" class="btn btn-ghost" data-modal-close="modal-<?=$app['id']?>">Cancel</button>
        <button type="submit" name="action" value="reject" class="btn btn-danger"><?=icon('x-circle')?> Reject</button>
        <button type="submit" name="action" value="approve" class="btn btn-primary"><?=icon('check-circle')?> Approve</button>
      </div>
    </form>
    <?php else: ?><div class="modal-actions"><button type="button" class="btn btn-secondary" data-modal-close="modal-<?=$app['id']?>">Close</button></div><?php endif; ?>
  </div>
</div>
<?php endforeach; ?>
<?php endif; ?>
<?php render_admin_footer(); ?>
