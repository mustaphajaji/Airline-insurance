<?php
require_once __DIR__.'/../includes/bootstrap.php';
require_once __DIR__.'/../includes/customer_layout.php';
require_login('customer');
$pageTitle='Insurance Plans';
$pdo=db();
$plans=$pdo->query('SELECT * FROM policy_plans WHERE status="active" ORDER BY premium_amount ASC')->fetchAll();
render_customer_header('plans');
?>
<div class="page-head">
  <div><h2>Insurance Plans</h2><p>Choose the coverage that matches your travel needs.</p></div>
  <a href="<?=BASE_URL?>/customer/apply.php" class="btn btn-primary"><?=icon('plus')?> Apply Now</a>
</div>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:20px">
<?php foreach($plans as $plan): ?>
  <div class="bc">
    <div class="bc-main">
      <div class="bc-eye"><?=icon(category_icon($plan['category']))?> <?=e(category_label($plan['category']))?></div>
      <h3><?=e($plan['plan_name'])?></h3>
      <p style="font-size:13.5px;color:var(--ink-500);margin:8px 0 0"><?=e($plan['description'])?></p>
      <div class="bc-amounts">
        <div><span>Premium</span><strong><?=format_money($plan['premium_amount'])?>/yr</strong></div>
        <div><span>Coverage</span><strong><?=format_money($plan['coverage_amount'])?></strong></div>
        <div><span>Duration</span><strong><?=$plan['duration_days']?> days</strong></div>
      </div>
      <div style="margin-top:16px">
        <a href="<?=BASE_URL?>/customer/apply.php?plan=<?=$plan['id']?>" class="btn btn-primary btn-sm"><?=icon('arrow-right')?> Apply for This Plan</a>
      </div>
    </div>
    <div class="bc-stub">
      <div class="si"><?=icon(category_icon($plan['category']))?></div>
      <div class="sc"><?=e($plan['plan_code'])?></div>
      <?=status_badge($plan['status'])?>
    </div>
  </div>
<?php endforeach; ?>
</div>
<?php render_customer_footer(); ?>
