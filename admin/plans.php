<?php
require_once __DIR__.'/../includes/bootstrap.php';
require_once __DIR__.'/../includes/admin_layout.php';
require_login('admin');
$pageTitle='Insurance Plans'; $pdo=db();
$errors=array(); $editPlan=null; $mode='create';

if(isset($_GET['edit'])){
    $s=$pdo->prepare('SELECT * FROM policy_plans WHERE id=? LIMIT 1');
    $s->execute(array((int)$_GET['edit'])); $editPlan=$s->fetch();
    if($editPlan) $mode='edit';
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    csrf_verify();
    $action=$_POST['action']??'';
    if($action==='save'){
        $id=(int)($_POST['plan_id']??0);
        $code=strtoupper(trim($_POST['plan_code']??''));
        $cat=$_POST['category']??'';
        $name=trim($_POST['plan_name']??'');
        $desc=trim($_POST['description']??'');
        $cov=(float)str_replace(',','',($_POST['coverage_amount']??0));
        $prem=(float)str_replace(',','',($_POST['premium_amount']??0));
        $days=(int)($_POST['duration_days']??365);
        $st=$_POST['status']??'active';
        $cats=array('flight_delay','flight_cancellation','lost_baggage','personal_accident','medical_emergency','other');
        if(empty($code)) $errors[]='Plan code required.';
        if(!in_array($cat,$cats)) $errors[]='Invalid category.';
        if(empty($name)) $errors[]='Plan name required.';
        if($cov<=0) $errors[]='Coverage amount must be positive.';
        if($prem<=0) $errors[]='Premium amount must be positive.';
        if(empty($errors)){
            if($id){
                $pdo->prepare('UPDATE policy_plans SET plan_code=?,category=?,plan_name=?,description=?,coverage_amount=?,premium_amount=?,duration_days=?,status=? WHERE id=?')->execute(array($code,$cat,$name,$desc,$cov,$prem,$days,$st,$id));
                flash('success','Plan updated.'); redirect('/admin/plans.php');
            } else {
                $pdo->prepare('INSERT INTO policy_plans(plan_code,category,plan_name,description,coverage_amount,premium_amount,duration_days,status) VALUES(?,?,?,?,?,?,?,?)')->execute(array($code,$cat,$name,$desc,$cov,$prem,$days,$st));
                flash('success','Plan created.'); redirect('/admin/plans.php');
            }
        }
    } elseif($action==='toggle'){
        $id=(int)($_POST['plan_id']??0);
        $cur=$pdo->prepare('SELECT status FROM policy_plans WHERE id=?'); $cur->execute(array($id)); $cur=$cur->fetchColumn();
        $new=$cur==='active'?'inactive':'active';
        $pdo->prepare('UPDATE policy_plans SET status=? WHERE id=?')->execute(array($new,$id));
        flash('success','Plan '.$new.'.'); redirect('/admin/plans.php');
    }
}

$plans=$pdo->query('SELECT *,(SELECT COUNT(*) FROM policies WHERE plan_id=policy_plans.id) as usage_count FROM policy_plans ORDER BY premium_amount ASC')->fetchAll();
render_admin_header('plans');
$ef=$editPlan??array();
?>
<div class="page-head"><div><h2>Insurance Plans</h2><p>Create and manage coverage products.</p></div></div>
<?php if(!empty($errors)): ?><div class="alert alert-error"><?=icon('alert-triangle')?><div><?php foreach($errors as $e) echo '<div>'.e($e).'</div>'; ?></div></div><?php endif; ?>

<div style="display:grid;grid-template-columns:1.4fr 1fr;gap:22px">
<div>
<div class="table-wrap">
<table><thead><tr><th>Code</th><th>Plan Name</th><th>Category</th><th>Premium/yr</th><th>Coverage</th><th>Usage</th><th>Status</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach($plans as $pl): ?>
<tr>
  <td class="cell-strong" style="font-family:monospace"><?=e($pl['plan_code'])?></td>
  <td><?=e($pl['plan_name'])?></td>
  <td><div class="tag-pill"><?=icon(category_icon($pl['category']))?> <?=e(category_label($pl['category']))?></div></td>
  <td><?=format_money($pl['premium_amount'])?></td>
  <td><?=format_money($pl['coverage_amount'])?></td>
  <td><?=$pl['usage_count']?> polic<?=$pl['usage_count']===1?'y':'ies'?></td>
  <td><?=status_badge($pl['status'])?></td>
  <td>
    <div class="row-actions">
      <a href="?edit=<?=$pl['id']?>" class="btn btn-sm btn-ghost"><?=icon('edit')?></a>
      <form method="post" action="" style="display:inline">
        <?=csrf_field()?><input type="hidden" name="plan_id" value="<?=$pl['id']?>">
        <button type="submit" name="action" value="toggle" class="btn btn-sm btn-ghost" data-confirm="Toggle this plan status?"><?=$pl['status']==='active'?icon('x-circle'):icon('check-circle')?></button>
      </form>
    </div>
  </td>
</tr>
<?php endforeach; ?>
</tbody></table>
</div>
</div>
<div class="card card-pad">
  <h3 style="margin-bottom:18px"><?=$mode==='edit'?icon('edit').' Edit Plan':icon('plus').' New Plan'?></h3>
  <form method="post" action="" novalidate>
    <?=csrf_field()?><input type="hidden" name="action" value="save">
    <?php if($mode==='edit'): ?><input type="hidden" name="plan_id" value="<?=$ef['id']?>"> <?php endif; ?>
    <div class="field"><label for="plan_code">Plan Code <span class="req">*</span></label><input type="text" id="plan_code" name="plan_code" value="<?=e($ef['plan_code']??'')?>" placeholder="FD-100" required></div>
    <div class="field"><label for="category">Category <span class="req">*</span></label>
      <select id="category" name="category" required>
        <option value="">— Select —</option>
        <?php foreach(array('flight_delay'=>'Flight Delay','flight_cancellation'=>'Flight Cancellation','lost_baggage'=>'Lost Baggage','personal_accident'=>'Personal Accident','medical_emergency'=>'Medical Emergency','other'=>'Other Hardship') as $k=>$l): ?>
        <option value="<?=$k?>" <?=(($ef['category']??'')===$k)?'selected':''?>><?=$l?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field"><label for="plan_name">Plan Name <span class="req">*</span></label><input type="text" id="plan_name" name="plan_name" value="<?=e($ef['plan_name']??'')?>" required></div>
    <div class="field"><label for="description">Description</label><textarea id="description" name="description" rows="3"><?=e($ef['description']??'')?></textarea></div>
    <div class="form-grid">
      <div class="field"><label for="coverage_amount">Coverage ($) <span class="req">*</span></label><input type="number" id="coverage_amount" name="coverage_amount" value="<?=$ef['coverage_amount']??''?>" min="1" step="0.01" required></div>
      <div class="field"><label for="premium_amount">Premium/yr ($) <span class="req">*</span></label><input type="number" id="premium_amount" name="premium_amount" value="<?=$ef['premium_amount']??''?>" min="1" step="0.01" required></div>
    </div>
    <div class="form-grid">
      <div class="field"><label for="duration_days">Duration (days)</label><input type="number" id="duration_days" name="duration_days" value="<?=$ef['duration_days']??365?>" min="1"></div>
      <div class="field"><label for="status">Status</label>
        <select id="status" name="status">
          <option value="active" <?=(($ef['status']??'active')==='active')?'selected':''?>>Active</option>
          <option value="inactive" <?=(($ef['status']??'')==='inactive')?'selected':''?>>Inactive</option>
        </select>
      </div>
    </div>
    <div style="display:flex;gap:10px">
      <button type="submit" class="btn btn-primary"><?=$mode==='edit'?icon('check-circle').' Save Changes':icon('plus').' Create Plan'?></button>
      <?php if($mode==='edit'): ?><a href="<?=BASE_URL?>/admin/plans.php" class="btn btn-ghost">Cancel</a><?php endif; ?>
    </div>
  </form>
</div>
</div>
<?php render_admin_footer(); ?>
