<?php
require_once __DIR__.'/../includes/bootstrap.php';
require_once __DIR__.'/../includes/admin_layout.php';
require_login('admin');
$pageTitle='Dashboard'; $pdo=db();
$stats=array(
    'total_users'=>(int)$pdo->query('SELECT COUNT(*) FROM users WHERE role="customer"')->fetchColumn(),
    'total_policies'=>(int)$pdo->query('SELECT COUNT(*) FROM policies')->fetchColumn(),
    'pending_apps'=>(int)$pdo->query('SELECT COUNT(*) FROM policies WHERE status="pending"')->fetchColumn(),
    'active_policies'=>(int)$pdo->query('SELECT COUNT(*) FROM policies WHERE status="active"')->fetchColumn(),
    'total_claims'=>(int)$pdo->query('SELECT COUNT(*) FROM claims')->fetchColumn(),
    'pending_claims'=>(int)$pdo->query('SELECT COUNT(*) FROM claims WHERE status="submitted"')->fetchColumn(),
    'total_revenue'=>(float)$pdo->query('SELECT COALESCE(SUM(amount),0) FROM payments WHERE status="completed"')->fetchColumn(),
    'monthly_revenue'=>(float)$pdo->query('SELECT COALESCE(SUM(amount),0) FROM payments WHERE status="completed" AND MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())')->fetchColumn(),
);
$recentApps=$pdo->query('SELECT p.*,u.full_name,u.email,pp.plan_name FROM policies p JOIN users u ON u.id=p.user_id JOIN policy_plans pp ON pp.id=p.plan_id ORDER BY p.created_at DESC LIMIT 6')->fetchAll();
$recentClaims=$pdo->query('SELECT c.*,u.full_name,p.policy_number,pp.plan_name FROM claims c JOIN users u ON u.id=c.user_id JOIN policies p ON p.id=c.policy_id JOIN policy_plans pp ON pp.id=p.plan_id ORDER BY c.created_at DESC LIMIT 5')->fetchAll();
render_admin_header('dashboard');
?>
<div class="stat-grid" style="margin-bottom:24px">
  <div class="stat-card"><div class="si"><?=icon('users')?></div><div><div class="sv"><?=$stats['total_users']?></div><div class="sl">Total Customers</div></div></div>
  <div class="stat-card"><div class="si"><?=icon('shield')?></div><div><div class="sv"><?=$stats['active_policies']?></div><div class="sl">Active Policies</div></div></div>
  <div class="stat-card"><div class="si"><?=icon('alert-triangle')?></div><div><div class="sv"><?=$stats['pending_claims']?></div><div class="sl">Pending Claims</div></div></div>
  <div class="stat-card"><div class="si"><?=icon('dollar')?></div><div><div class="sv"><?=format_money($stats['total_revenue'])?></div><div class="sl">Total Revenue</div></div></div>
</div>
<div class="stat-grid" style="margin-bottom:24px">
  <div class="stat-card"><div class="si"><?=icon('file-text')?></div><div><div class="sv"><?=$stats['pending_apps']?></div><div class="sl">Pending Applications</div></div></div>
  <div class="stat-card"><div class="si"><?=icon('trending-up')?></div><div><div class="sv"><?=format_money($stats['monthly_revenue'])?></div><div class="sl">This Month's Revenue</div></div></div>
  <div class="stat-card"><div class="si"><?=icon('briefcase')?></div><div><div class="sv"><?=$stats['total_policies']?></div><div class="sl">Total Applications</div></div></div>
  <div class="stat-card"><div class="si"><?=icon('check-circle')?></div><div><div class="sv"><?=$stats['total_claims']?></div><div class="sl">Total Claims</div></div></div>
</div>

<div style="display:grid;grid-template-columns:1.4fr 1fr;gap:20px">
<div class="card">
  <div class="card-head"><h3>Recent Applications</h3><a href="<?=BASE_URL?>/admin/applications.php" class="btn btn-ghost btn-sm">View All <?=icon('chevron-right')?></a></div>
  <div class="table-wrap" style="border:none">
    <table><thead><tr><th>Customer</th><th>Plan</th><th>Premium</th><th>Status</th><th>Date</th><th></th></tr></thead>
    <tbody>
    <?php foreach($recentApps as $app): ?>
    <tr>
      <td><div class="cell-strong"><?=e($app['full_name'])?></div><div style="font-size:11.5px;color:var(--ink-500)"><?=e($app['email'])?></div></td>
      <td><?=e($app['plan_name'])?></td>
      <td><?=format_money($app['premium_amount'])?></td>
      <td><?=status_badge($app['status'])?></td>
      <td><?=format_date($app['created_at'])?></td>
      <td><a href="<?=BASE_URL?>/admin/applications.php?id=<?=$app['id']?>" class="btn btn-ghost btn-sm"><?=icon('eye')?></a></td>
    </tr>
    <?php endforeach; ?>
    </tbody></table>
  </div>
</div>
<div class="card">
  <div class="card-head"><h3>Recent Claims</h3><a href="<?=BASE_URL?>/admin/claims.php" class="btn btn-ghost btn-sm">View All <?=icon('chevron-right')?></a></div>
  <div class="card-body" style="padding:0 22px">
    <?php foreach($recentClaims as $cl): ?>
    <div class="list-row">
      <div class="lr-left">
        <div class="lr-ico"><?=icon('alert-triangle')?></div>
        <div><div class="lr-title"><?=e($cl['full_name'])?></div><div class="lr-sub"><?=e($cl['claim_number'])?> &middot; <?=format_money($cl['amount_claimed'])?></div></div>
      </div>
      <?=status_badge($cl['status'])?>
    </div>
    <?php endforeach; ?>
  </div>
</div>
</div>
<?php render_admin_footer(); ?>
