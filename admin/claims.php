<?php
require_once __DIR__.'/../includes/bootstrap.php';
require_once __DIR__.'/../includes/admin_layout.php';
require_login('admin');
$pageTitle='Claims Management'; $pdo=db(); $adminId=current_uid();

if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['action'])){
    csrf_verify();
    $cid=(int)($_POST['claim_id']??0);
    $action=$_POST['action'];
    $note=trim($_POST['admin_notes']??'');
    $approved=(float)str_replace(',','',($_POST['amount_approved']??0));
    if($cid&&in_array($action,array('approve','reject','set_paid','set_review'))){
        $newStatus=array('approve'=>'approved','reject'=>'rejected','set_paid'=>'paid','set_review'=>'under_review')[$action];
        $pdo->prepare('UPDATE claims SET status=?,admin_notes=?,amount_approved=?,reviewed_by=?,reviewed_at=NOW() WHERE id=?')->execute(array($newStatus,$note,$approved>0?$approved:null,$adminId,$cid));
        $cl=$pdo->prepare('SELECT c.*,u.id as uid,pp.plan_name FROM claims c JOIN policies p ON p.id=c.policy_id JOIN policy_plans pp ON pp.id=p.plan_id JOIN users u ON u.id=c.user_id WHERE c.id=?');
        $cl->execute(array($cid)); $cl=$cl->fetch();
        if($cl) notify_user($cl['uid'],'Claim Update','Your claim '.$cl['claim_number'].' is now '.$newStatus.($note?'. Note: '.$note:'').'.');
        flash('success','Claim updated to '.$newStatus.'.');
    }
    redirect('/admin/claims.php');
}

$status=trim($_GET['status']??'');
$search=trim($_GET['q']??'');
$sql='SELECT c.*,u.full_name,u.email,p.policy_number,pp.plan_name FROM claims c JOIN users u ON u.id=c.user_id JOIN policies p ON p.id=c.policy_id JOIN policy_plans pp ON pp.id=p.plan_id WHERE 1=1';
$params=array();
if($status){ $sql.=' AND c.status=?'; $params[]=$status; }
if($search){ $sql.=' AND (u.full_name LIKE ? OR c.claim_number LIKE ?)'; $like="%$search%"; $params=array_merge($params,array($like,$like)); }
$sql.=' ORDER BY c.created_at DESC';
$stmt=$pdo->prepare($sql); $stmt->execute($params); $claims=$stmt->fetchAll();
$counts=array(); foreach(array('submitted','under_review','approved','rejected','paid') as $s){ $x=$pdo->prepare('SELECT COUNT(*) FROM claims WHERE status=?'); $x->execute(array($s)); $counts[$s]=(int)$x->fetchColumn(); }
render_admin_header('claims');
?>
<div class="page-head"><div><h2>Claims Management</h2><p>Review, approve and settle customer claims.</p></div></div>
<div class="tab-bar">
  <a href="?status=" class="<?=$status===''?'active':''?>">All <span class="cp"><?=array_sum($counts)?></span></a>
  <?php foreach(array('submitted'=>'Submitted','under_review'=>'Under Review','approved'=>'Approved','rejected'=>'Rejected','paid'=>'Paid') as $k=>$l): ?>
  <a href="?status=<?=$k?>" class="<?=$status===$k?'active':''?>"><?=$l?> <span class="cp"><?=$counts[$k]?></span></a>
  <?php endforeach; ?>
</div>
<div class="filter-bar">
  <form method="get" style="display:flex;gap:10px;flex-wrap:wrap;flex:1">
    <input type="hidden" name="status" value="<?=e($status)?>">
    <div class="search-box"><?=icon('search')?><input type="text" name="q" value="<?=e($search)?>" placeholder="Search customer, claim #…"></div>
    <button type="submit" class="btn btn-secondary btn-sm"><?=icon('search')?> Search</button>
    <?php if($search): ?><a href="?status=<?=e($status)?>" class="btn btn-ghost btn-sm">Clear</a><?php endif; ?>
  </form>
</div>
<?php if(empty($claims)): ?>
<div class="card"><div class="empty-state"><?=icon('alert-triangle')?><h3>No claims found</h3><p>No claims match your filters.</p></div></div>
<?php else: ?>
<div class="table-wrap">
<table><thead><tr><th>Claim #</th><th>Customer</th><th>Policy</th><th>Type</th><th>Incident</th><th>Claimed</th><th>Approved</th><th>Status</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach($claims as $cl): ?>
<tr>
  <td class="cell-strong"><?=e($cl['claim_number'])?></td>
  <td><div class="cell-strong"><?=e($cl['full_name'])?></div><div style="font-size:11.5px;color:var(--ink-500)"><?=e($cl['email'])?></div></td>
  <td style="font-size:13px"><?=e($cl['policy_number']??'—')?></td>
  <td style="font-size:13px"><?=e($cl['claim_type'])?></td>
  <td><?=format_date($cl['incident_date'])?></td>
  <td><?=format_money($cl['amount_claimed'])?></td>
  <td><?=$cl['amount_approved']!==null?'<span style="color:var(--ok-600);font-weight:600">'.format_money($cl['amount_approved']).'</span>':'<span style="color:var(--ink-300)">—</span>'?></td>
  <td><?=status_badge($cl['status'])?></td>
  <td><div class="row-actions"><button class="btn btn-sm btn-ghost" data-modal-open="claim-<?=$cl['id']?>"><?=icon('eye')?> Review</button></div></td>
</tr>
<?php endforeach; ?>
</tbody></table>
</div>

<?php foreach($claims as $cl): ?>
<div class="modal-overlay" id="claim-<?=$cl['id']?>">
  <div class="modal-box" style="max-width:520px">
    <h3><?=icon('alert-triangle')?> Claim <?=e($cl['claim_number'])?></h3>
    <p style="color:var(--ink-500);font-size:13.5px;margin-bottom:14px"><?=e($cl['full_name'])?> &middot; Policy <?=e($cl['policy_number']??'—')?></p>
    <div class="kv-list" style="margin-bottom:14px">
      <div class="kv-row"><span class="k">Plan</span><span class="v"><?=e($cl['plan_name'])?></span></div>
      <div class="kv-row"><span class="k">Type</span><span class="v"><?=e($cl['claim_type'])?></span></div>
      <div class="kv-row"><span class="k">Incident Date</span><span class="v"><?=format_date($cl['incident_date'])?></span></div>
      <div class="kv-row"><span class="k">Amount Claimed</span><span class="v"><?=format_money($cl['amount_claimed'])?></span></div>
      <div class="kv-row"><span class="k">Status</span><span class="v"><?=status_badge($cl['status'])?></span></div>
    </div>
    <div style="padding:12px;background:var(--surface);border-radius:var(--r-sm);font-size:13px;margin-bottom:14px"><strong>Description:</strong><br><?=nl2br(e($cl['description']))?></div>
    <?php if($cl['document_path']): ?><div style="margin-bottom:14px"><a href="<?=BASE_URL?>/uploads/claims/<?=e($cl['document_path'])?>" target="_blank" class="btn btn-secondary btn-sm"><?=icon('download')?> View Document (<?=e($cl['document_name']??'file')?>)</a></div><?php endif; ?>
    <?php if(!in_array($cl['status'],array('paid'))): ?>
    <form method="post" action="">
      <?=csrf_field()?><input type="hidden" name="claim_id" value="<?=$cl['id']?>">
      <div class="form-grid">
        <div class="field"><label for="amount_approved-<?=$cl['id']?>">Approved Amount ($)</label><input type="number" id="amount_approved-<?=$cl['id']?>" name="amount_approved" value="<?=$cl['amount_approved']??$cl['amount_claimed']?>" min="0" step="0.01"></div>
        <div></div>
      </div>
      <div class="field"><label for="anotes-<?=$cl['id']?>">Admin Note</label><textarea id="anotes-<?=$cl['id']?>" name="admin_notes" rows="2" placeholder="Add a note for the customer…"><?=e($cl['admin_notes']??'')?></textarea></div>
      <div class="modal-actions" style="flex-wrap:wrap">
        <button type="button" class="btn btn-ghost" data-modal-close="claim-<?=$cl['id']?>">Cancel</button>
        <?php if($cl['status']==='submitted'): ?><button type="submit" name="action" value="set_review" class="btn btn-secondary btn-sm"><?=icon('clock')?> Mark Under Review</button><?php endif; ?>
        <?php if(in_array($cl['status'],array('submitted','under_review'))): ?>
          <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm"><?=icon('x-circle')?> Reject</button>
          <button type="submit" name="action" value="approve" class="btn btn-primary btn-sm"><?=icon('check-circle')?> Approve</button>
        <?php endif; ?>
        <?php if($cl['status']==='approved'): ?><button type="submit" name="action" value="set_paid" class="btn btn-primary btn-sm"><?=icon('dollar')?> Mark as Paid</button><?php endif; ?>
      </div>
    </form>
    <?php else: ?><div class="modal-actions"><button type="button" class="btn btn-secondary" data-modal-close="claim-<?=$cl['id']?>">Close</button></div><?php endif; ?>
  </div>
</div>
<?php endforeach; ?>
<?php endif; ?>
<?php render_admin_footer(); ?>
