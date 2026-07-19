<?php
require_once __DIR__.'/../includes/bootstrap.php';
require_once __DIR__.'/../includes/customer_layout.php';
require_login('customer');
$pageTitle='File a Claim'; $pdo=db(); $uid=current_uid();
$prePolicy=(int)($_GET['policy']??0);
$policies=$pdo->prepare('SELECT p.*,pp.plan_name FROM policies p JOIN policy_plans pp ON pp.id=p.plan_id WHERE p.user_id=? AND p.status="active" ORDER BY p.created_at DESC');
$policies->execute(array($uid)); $policies=$policies->fetchAll();
if(empty($policies)){ flash('error','You need an active policy to file a claim.'); redirect('/customer/policies.php'); }
$errors=array();
if($_SERVER['REQUEST_METHOD']==='POST'){
    csrf_verify();
    $polId=(int)($_POST['policy_id']??0);
    $type=trim($_POST['claim_type']??'');
    $idate=trim($_POST['incident_date']??'');
    $desc=trim($_POST['description']??'');
    $amt=(float)str_replace(',','',($_POST['amount_claimed']??0));
    if(!$polId) $errors[]='Please select a policy.';
    if(empty($type)) $errors[]='Claim type is required.';
    if(empty($idate)) $errors[]='Incident date is required.';
    if(empty($desc)||strlen($desc)<20) $errors[]='Description must be at least 20 characters.';
    if($amt<=0) $errors[]='Please enter a valid claim amount.';
    $pol=null;
    if($polId){
        $s=$pdo->prepare('SELECT * FROM policies WHERE id=? AND user_id=? AND status="active" LIMIT 1');
        $s->execute(array($polId,$uid)); $pol=$s->fetch();
        if(!$pol) $errors[]='Selected policy is not active.';
    }
    $doc=null;
    if(empty($errors)){
        try{ $doc=handle_upload('document'); } catch(RuntimeException $ex){ $errors[]=$ex->getMessage(); }
    }
    if(empty($errors)&&$pol){
        if($amt>$pol['coverage_amount']) $errors[]='Claim amount cannot exceed coverage amount of '.format_money($pol['coverage_amount']).'.';
    }
    if(empty($errors)){
        $cnum=gen_claim_num();
        $pdo->prepare('INSERT INTO claims(claim_number,policy_id,user_id,claim_type,incident_date,description,amount_claimed,document_path,document_name,status) VALUES(?,?,?,?,?,?,?,?,?,"submitted")')->execute(array($cnum,$pol['id'],$uid,$type,$idate,$desc,$amt,$doc?$doc['path']:null,$doc?$doc['name']:null));
        notify_user($uid,'Claim Submitted','Your claim '.$cnum.' has been submitted and is under review.');
        clear_old(); flash('success','Claim '.$cnum.' submitted successfully.');
        redirect('/customer/claims.php');
    } else { set_old($_POST); }
}
render_customer_header('claims');
?>
<div class="page-head"><div><h2>File a Claim</h2><p>Submit a claim against one of your active policies.</p></div></div>
<?php if(!empty($errors)): ?><div class="alert alert-error"><?=icon('alert-triangle')?><div><?php foreach($errors as $e) echo '<div>'.htmlspecialchars($e,ENT_QUOTES,'UTF-8').'</div>'; ?></div></div><?php endif; ?>
<div style="display:grid;grid-template-columns:1.4fr 1fr;gap:22px">
<form method="post" action="" enctype="multipart/form-data" novalidate>
<?=csrf_field()?>
<div class="card card-pad">
  <fieldset>
    <legend><?=icon('shield')?> Select Policy</legend>
    <div class="field"><label for="policy_id">Active Policy <span class="req">*</span></label>
      <select id="policy_id" name="policy_id" required>
        <option value="">— Choose a policy —</option>
        <?php foreach($policies as $pol): $sel=(int)(old('policy_id',$prePolicy))===$pol['id']?'selected':''; ?>
        <option value="<?=$pol['id']?>" <?=$sel?>><?=e($pol['plan_name'])?> — <?=e($pol['policy_number'])?> (<?=format_money($pol['coverage_amount'])?> coverage)</option>
        <?php endforeach; ?>
      </select>
    </div>
  </fieldset>
  <fieldset>
    <legend><?=icon('alert-triangle')?> Claim Details</legend>
    <div class="form-grid">
      <div class="field"><label for="claim_type">Claim Type <span class="req">*</span></label>
        <select id="claim_type" name="claim_type" required>
          <option value="">— Select type —</option>
          <?php foreach(array('Flight Delay','Flight Cancellation','Lost Baggage','Damaged Baggage','Personal Accident','Medical Emergency','Other') as $t): ?>
          <option value="<?=e($t)?>" <?=old('claim_type')===$t?'selected':''?>><?=e($t)?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field"><label for="incident_date">Incident Date <span class="req">*</span></label><input type="date" id="incident_date" name="incident_date" value="<?=old('incident_date')?>" required max="<?=date('Y-m-d')?>"></div>
    </div>
    <div class="field"><label for="amount_claimed">Amount Claimed ($) <span class="req">*</span></label><input type="number" id="amount_claimed" name="amount_claimed" value="<?=old('amount_claimed')?>" min="1" step="0.01" placeholder="0.00" required></div>
    <div class="field"><label for="description">Description <span class="req">*</span></label><textarea id="description" name="description" placeholder="Describe the incident in detail (minimum 20 characters)…" required maxlength="2000"><?=old('description')?></textarea></div>
  </fieldset>
  <fieldset>
    <legend><?=icon('paperclip')?> Supporting Document</legend>
    <div class="field"><label for="document">Upload Evidence</label>
      <input type="file" id="document" name="document" accept=".pdf,.jpg,.jpeg,.png" style="padding:8px">
      <div class="hint">PDF, JPG or PNG · Max 5MB · Optional but recommended</div>
    </div>
  </fieldset>
  <button type="submit" class="btn btn-primary btn-block btn-lg"><?=icon('send')?> Submit Claim</button>
</div>
</form>
<div>
  <div class="card card-pad" style="margin-bottom:16px">
    <h4 style="margin-bottom:12px"><?=icon('info')?> Claim Guidelines</h4>
    <div style="font-size:13.5px;color:var(--ink-700);line-height:1.7">
      <p>Include as much detail as possible about the incident. Supporting documents such as boarding passes, medical reports, or baggage receipts will speed up the review process.</p>
      <p>Claims are reviewed within 24–48 hours. You will receive a notification when your claim status changes.</p>
    </div>
  </div>
  <div class="card card-pad">
    <h4 style="margin-bottom:12px"><?=icon('clock')?> Claim Process</h4>
    <div class="kv-list">
      <div class="kv-row"><span class="k">Submitted</span><span class="v">Claim received</span></div>
      <div class="kv-row"><span class="k">Under Review</span><span class="v">Admin reviewing</span></div>
      <div class="kv-row"><span class="k">Approved</span><span class="v">Amount confirmed</span></div>
      <div class="kv-row"><span class="k">Paid</span><span class="v">Settlement complete</span></div>
    </div>
  </div>
</div>
</div>
<?php render_customer_footer(); ?>
