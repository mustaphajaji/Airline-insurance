<?php
require_once __DIR__.'/../includes/bootstrap.php';
require_once __DIR__.'/../includes/customer_layout.php';
require_login('customer');
$pageTitle='Apply for Insurance';
$pdo=db(); $uid=current_uid();
$plans=$pdo->query('SELECT * FROM policy_plans WHERE status="active" ORDER BY premium_amount ASC')->fetchAll();
$errors=array(); $success=false;

if($_SERVER['REQUEST_METHOD']==='POST'){
    csrf_verify();
    $planId=(int)($_POST['plan_id']??0);
    $airline=trim($_POST['airline_name']??'');
    $flight=trim($_POST['flight_number']??'');
    $dep=trim($_POST['departure_city']??'');
    $dest=trim($_POST['destination_city']??'');
    $fdate=trim($_POST['flight_date']??'');
    $benName=trim($_POST['beneficiary_name']??'');
    $benRel=trim($_POST['beneficiary_relationship']??'');
    $notes=trim($_POST['notes']??'');
    if(!$planId) $errors[]='Please select an insurance plan.';
    if(empty($airline)) $errors[]='Airline name is required.';
    if(empty($dep)) $errors[]='Departure city is required.';
    if(empty($dest)) $errors[]='Destination city is required.';
    if(empty($fdate)) $errors[]='Flight date is required.';
    $plan=null;
    if($planId){
        $s=$pdo->prepare('SELECT * FROM policy_plans WHERE id=? AND status="active" LIMIT 1');
        $s->execute(array($planId)); $plan=$s->fetch();
        if(!$plan) $errors[]='Selected plan is not available.';
    }
    if(empty($errors)&&$plan){
        $pdo->prepare('INSERT INTO policies(user_id,plan_id,airline_name,flight_number,departure_city,destination_city,flight_date,coverage_amount,premium_amount,beneficiary_name,beneficiary_relationship,notes,status) VALUES(?,?,?,?,?,?,?,?,?,?,?,"pending")')->execute(array($uid,$planId,$airline,$flight,$dep,$dest,$fdate,$plan['coverage_amount'],$plan['premium_amount'],$benName,$benRel));
        $pid=$pdo->lastInsertId();
        notify_user($uid,'Application Submitted','Your application for '.$plan['plan_name'].' has been submitted and is under review.');
        clear_old(); flash('success','Your application has been submitted and is pending review.');
        redirect('/customer/policies.php');
    } else {
        set_old($_POST);
    }
}
$preselect=isset($_GET['plan'])?(int)$_GET['plan']:0;
render_customer_header('apply');
?>
<div class="page-head">
  <div><h2>New Insurance Application</h2><p>Complete the form below to apply for coverage.</p></div>
</div>
<?php if(!empty($errors)): ?>
<div class="alert alert-error"><?=icon('alert-triangle')?>
  <div><?php foreach($errors as $e) echo '<div>'.htmlspecialchars($e,ENT_QUOTES,'UTF-8').'</div>'; ?></div>
</div>
<?php endif; ?>
<div style="display:grid;grid-template-columns:1.4fr 1fr;gap:22px">
<div>
<form method="post" action="" novalidate>
<?=csrf_field()?>
<div class="card card-pad">
  <fieldset>
    <legend><?=icon('briefcase')?> Coverage Plan</legend>
    <div class="field">
      <label for="plan_id">Select a Plan <span class="req">*</span></label>
      <select id="plan_id" name="plan_id" required>
        <option value="">— Choose a plan —</option>
        <?php foreach($plans as $pl): $sel=((old('plan_id')||$preselect)&&(int)(old('plan_id',$preselect))===$pl['id'])?'selected':''; ?>
        <option value="<?=$pl['id']?>" data-coverage="<?=format_money($pl['coverage_amount'])?>" data-premium="<?=format_money($pl['premium_amount'])?>" data-coverage-val="<?=$pl['coverage_amount']?>" data-premium-val="<?=$pl['premium_amount']?>" <?=$sel?>><?=e($pl['plan_name'])?> — <?=format_money($pl['premium_amount'])?>/yr</option>
        <?php endforeach; ?>
      </select>
    </div>
    <input type="hidden" id="coverage_amount" name="coverage_amount">
    <input type="hidden" id="premium_amount" name="premium_amount">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;padding:14px;background:var(--blue-50);border-radius:var(--r-sm)">
      <div><div style="font-size:12px;color:var(--ink-500);margin-bottom:2px">Annual Premium</div><div style="font-family:var(--font-d);font-size:18px;font-weight:700;color:var(--navy-900)" id="premium_display">—</div></div>
      <div><div style="font-size:12px;color:var(--ink-500);margin-bottom:2px">Coverage Amount</div><div style="font-family:var(--font-d);font-size:18px;font-weight:700;color:var(--navy-900)" id="coverage_display">—</div></div>
    </div>
  </fieldset>
  <fieldset>
    <legend><?=icon('plane')?> Flight Details</legend>
    <div class="form-grid">
      <div class="field"><label for="airline_name">Airline Name <span class="req">*</span></label><input type="text" id="airline_name" name="airline_name" value="<?=old('airline_name')?>" placeholder="e.g. British Airways" required></div>
      <div class="field"><label for="flight_number">Flight Number</label><input type="text" id="flight_number" name="flight_number" value="<?=old('flight_number')?>" placeholder="e.g. BA117"></div>
    </div>
    <div class="form-grid">
      <div class="field"><label for="departure_city">Departure City <span class="req">*</span></label><input type="text" id="departure_city" name="departure_city" value="<?=old('departure_city')?>" placeholder="e.g. New York" required></div>
      <div class="field"><label for="destination_city">Destination City <span class="req">*</span></label><input type="text" id="destination_city" name="destination_city" value="<?=old('destination_city')?>" placeholder="e.g. London" required></div>
    </div>
    <div class="field"><label for="flight_date">Flight Date <span class="req">*</span></label><input type="date" id="flight_date" name="flight_date" value="<?=old('flight_date')?>" required min="<?=date('Y-m-d')?>"></div>
  </fieldset>
  <fieldset>
    <legend><?=icon('user')?> Beneficiary</legend>
    <div class="form-grid">
      <div class="field"><label for="beneficiary_name">Beneficiary Name</label><input type="text" id="beneficiary_name" name="beneficiary_name" value="<?=old('beneficiary_name')?>" placeholder="Full name"></div>
      <div class="field"><label for="beneficiary_relationship">Relationship</label>
        <select id="beneficiary_relationship" name="beneficiary_relationship">
          <option value="">— Select —</option>
          <?php foreach(array('Spouse','Child','Parent','Sibling','Other') as $r): ?><option value="<?=$r?>" <?=old('beneficiary_relationship')===$r?'selected':''?>><?=$r?></option><?php endforeach; ?>
        </select>
      </div>
    </div>
  </fieldset>
  <fieldset>
    <legend><?=icon('file-text')?> Additional Notes</legend>
    <div class="field"><label for="notes">Notes</label><textarea id="notes" name="notes" placeholder="Any additional information about your trip…" maxlength="1000"><?=old('notes')?></textarea></div>
  </fieldset>
  <button type="submit" class="btn btn-primary btn-block btn-lg"><?=icon('send')?> Submit Application</button>
</div>
</form>
</div>
<!-- Info panel -->
<div>
  <div class="card card-pad" style="margin-bottom:16px">
    <h4 style="margin-bottom:14px"><?=icon('info')?> Application Process</h4>
    <div class="kv-list">
      <div class="kv-row"><span class="k">Step 1</span><span class="v">Submit application</span></div>
      <div class="kv-row"><span class="k">Step 2</span><span class="v">Admin review (24h)</span></div>
      <div class="kv-row"><span class="k">Step 3</span><span class="v">Pay premium</span></div>
      <div class="kv-row"><span class="k">Step 4</span><span class="v">Policy activated</span></div>
    </div>
  </div>
  <div class="card card-pad">
    <h4 style="margin-bottom:14px"><?=icon('shield')?> What's Covered</h4>
    <?php foreach($plans as $pl): ?>
    <div style="display:flex;gap:10px;align-items:flex-start;margin-bottom:12px">
      <div style="width:30px;height:30px;border-radius:var(--r-sm);background:var(--blue-50);color:var(--blue-600);display:flex;align-items:center;justify-content:center;flex-shrink:0"><?=icon(category_icon($pl['category']),'')?></div>
      <div><div style="font-size:13px;font-weight:600;color:var(--navy-900)"><?=e($pl['plan_name'])?></div><div style="font-size:12px;color:var(--ink-500)"><?=format_money($pl['coverage_amount'])?> coverage</div></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
</div>
<?php render_customer_footer(); ?>
