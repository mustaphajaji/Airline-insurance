<?php
require_once __DIR__.'/../includes/bootstrap.php';
require_once __DIR__.'/../includes/customer_layout.php';
require_login('customer');
$pageTitle='Pay Premium'; $pdo=db(); $uid=current_uid();
$pid=(int)($_GET['policy']??0);
$s=$pdo->prepare('SELECT p.*,pp.plan_name,pp.category FROM policies p JOIN policy_plans pp ON pp.id=p.plan_id WHERE p.id=? AND p.user_id=? AND p.status="approved" LIMIT 1');
$s->execute(array($pid,$uid)); $policy=$s->fetch();
if(!$policy){ flash('error','Policy not found or not eligible for payment.'); redirect('/customer/policies.php'); }
$errors=array();
if($_SERVER['REQUEST_METHOD']==='POST'){
    csrf_verify();
    $method=$_POST['payment_method']??'card';
    $card4=preg_replace('/\D/','',($_POST['card_number']??''));
    $card4=substr($card4,-4);
    if($method==='card'&&strlen($card4)<4) $errors[]='Please enter a valid card number.';
    if(empty($errors)){
        $ref=gen_txn_ref();
        $pdo->prepare('INSERT INTO payments(policy_id,user_id,amount,payment_method,card_last4,transaction_ref,status,paid_at) VALUES(?,?,?,?,?,?,"completed",NOW())')->execute(array($policy['id'],$uid,$policy['premium_amount'],$method,$card4,$ref));
        $start=date('Y-m-d'); $end=date('Y-m-d',strtotime('+365 days'));
        $pno=gen_policy_num();
        $pdo->prepare('UPDATE policies SET status="active",policy_number=?,start_date=?,end_date=? WHERE id=?')->execute(array($pno,$start,$end,$policy['id']));
        notify_user($uid,'Payment Confirmed','Your premium of '.format_money($policy['premium_amount']).' has been received. Policy '.$pno.' is now active.');
        flash('success','Payment successful. Your policy is now active!');
        redirect('/customer/policies.php');
    }
}
render_customer_header('policies');
?>
<div class="page-head"><div><h2>Pay Premium</h2><p>Complete payment to activate your policy.</p></div></div>
<?php if(!empty($errors)): ?><div class="alert alert-error"><?=icon('alert-triangle')?><div><?php foreach($errors as $e) echo '<div>'.e($e).'</div>'; ?></div></div><?php endif; ?>
<div style="display:grid;grid-template-columns:1.3fr 1fr;gap:22px">
<div class="card card-pad">
  <h3 style="margin-bottom:18px"><?=icon('credit-card')?> Payment Details</h3>
  <form method="post" action="" novalidate>
    <?=csrf_field()?>
    <div class="field"><label>Payment Method</label>
      <div style="display:flex;gap:10px">
        <label class="cb-row"><input type="radio" name="payment_method" value="card" checked> Credit / Debit Card</label>
        <label class="cb-row"><input type="radio" name="payment_method" value="bank_transfer"> Bank Transfer</label>
      </div>
    </div>
    <div class="field"><label for="card_number">Card Number</label>
      <div class="input-wrap"><?=icon('credit-card')?>
        <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" maxlength="19" autocomplete="cc-number">
      </div>
    </div>
    <div class="form-grid">
      <div class="field"><label for="expiry">Expiry Date</label><input type="text" id="expiry" name="expiry" placeholder="MM / YY" maxlength="7" autocomplete="cc-exp"></div>
      <div class="field"><label for="cvv">CVV</label><input type="password" id="cvv" name="cvv" placeholder="•••" maxlength="4" autocomplete="cc-csc"></div>
    </div>
    <div class="field"><label for="card_name">Cardholder Name</label><input type="text" id="card_name" name="card_name" placeholder="As it appears on card" autocomplete="cc-name"></div>
    <button type="submit" class="btn btn-primary btn-block btn-lg"><?=icon('lock')?> Pay <?=format_money($policy['premium_amount'])?></button>
    <p style="text-align:center;font-size:12px;color:var(--ink-300);margin-top:12px"><?=icon('lock')?> Secure payment — demo mode, no real charges</p>
  </form>
</div>
<div>
  <div class="card card-pad">
    <h4 style="margin-bottom:14px">Order Summary</h4>
    <div class="kv-list">
      <div class="kv-row"><span class="k">Plan</span><span class="v"><?=e($policy['plan_name'])?></span></div>
      <div class="kv-row"><span class="k">Coverage</span><span class="v"><?=format_money($policy['coverage_amount'])?></span></div>
      <div class="kv-row"><span class="k">Duration</span><span class="v">365 days</span></div>
      <div class="kv-row"><span class="k">Start Date</span><span class="v"><?=date('M j, Y')?></span></div>
      <div class="kv-row" style="border-top:2px solid var(--line-200);padding-top:14px;margin-top:4px"><span class="k" style="font-weight:700;color:var(--navy-900)">Total Due</span><span class="v" style="font-size:18px;color:var(--blue-600)"><?=format_money($policy['premium_amount'])?></span></div>
    </div>
  </div>
</div>
</div>
<?php render_customer_footer(); ?>
