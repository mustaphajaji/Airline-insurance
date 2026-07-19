<?php
require_once __DIR__.'/../includes/bootstrap.php';
require_once __DIR__.'/../includes/admin_layout.php';
require_login('admin');
$pageTitle='Customers'; $pdo=db(); $adminId=current_uid();

if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['toggle_status'])){
    csrf_verify();
    $uid=(int)$_POST['user_id'];
    $cur=$pdo->prepare('SELECT status FROM users WHERE id=? AND role="customer"');
    $cur->execute(array($uid)); $cur=$cur->fetchColumn();
    if($cur){
        $new=$cur==='active'?'suspended':'active';
        $pdo->prepare('UPDATE users SET status=? WHERE id=?')->execute(array($new,$uid));
        flash('success','Customer account '.$new.'.');
    }
    redirect('/admin/users.php');
}

$search=trim($_GET['q']??'');
$status=trim($_GET['status']??'');
$sql='SELECT u.*,(SELECT COUNT(*) FROM policies WHERE user_id=u.id) as policy_count,(SELECT COUNT(*) FROM claims WHERE user_id=u.id) as claim_count FROM users u WHERE u.role="customer"';
$params=array();
if($status){ $sql.=' AND u.status=?'; $params[]=$status; }
if($search){ $sql.=' AND (u.full_name LIKE ? OR u.email LIKE ?)'; $like="%$search%"; $params=array_merge($params,array($like,$like)); }
$sql.=' ORDER BY u.created_at DESC';
$stmt=$pdo->prepare($sql); $stmt->execute($params); $users=$stmt->fetchAll();
render_admin_header('users');
?>
<div class="page-head"><div><h2>Customers</h2><p>Manage customer accounts.</p></div></div>
<div class="filter-bar">
  <form method="get" style="display:flex;gap:10px;flex-wrap:wrap;flex:1">
    <select name="status" onchange="this.form.submit()" style="min-width:160px">
      <option value="">All Statuses</option>
      <option value="active" <?=$status==='active'?'selected':''?>>Active</option>
      <option value="suspended" <?=$status==='suspended'?'selected':''?>>Suspended</option>
    </select>
    <div class="search-box"><?=icon('search')?><input type="text" name="q" value="<?=e($search)?>" placeholder="Search name or email…"></div>
    <button type="submit" class="btn btn-secondary btn-sm"><?=icon('search')?> Search</button>
    <?php if($search||$status): ?><a href="<?=BASE_URL?>/admin/users.php" class="btn btn-ghost btn-sm">Clear</a><?php endif; ?>
  </form>
</div>
<?php if(empty($users)): ?>
<div class="card"><div class="empty-state"><?=icon('users')?><h3>No customers found</h3></div></div>
<?php else: ?>
<div class="table-wrap">
<table><thead><tr><th>Customer</th><th>Phone</th><th>Policies</th><th>Claims</th><th>Joined</th><th>Last Login</th><th>Status</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach($users as $u): ?>
<tr>
  <td><div class="cell-strong"><?=e($u['full_name'])?></div><div style="font-size:11.5px;color:var(--ink-500)"><?=e($u['email'])?></div></td>
  <td><?=e($u['phone']??'—')?></td>
  <td><?=$u['policy_count']?></td>
  <td><?=$u['claim_count']?></td>
  <td><?=format_date($u['created_at'])?></td>
  <td><?=$u['last_login']?format_date($u['last_login']):'Never'?></td>
  <td><?=status_badge($u['status'])?></td>
  <td>
    <form method="post" action="" style="display:inline">
      <?=csrf_field()?><input type="hidden" name="user_id" value="<?=$u['id']?>">
      <button type="submit" name="toggle_status" value="1" class="btn btn-sm <?=$u['status']==='active'?'btn-danger':'btn-secondary'?>" data-confirm="<?=$u['status']==='active'?'Suspend this account?':'Reactivate this account?'?>">
        <?=$u['status']==='active'?icon('x-circle').' Suspend':icon('check-circle').' Reactivate'?>
      </button>
    </form>
  </td>
</tr>
<?php endforeach; ?>
</tbody></table>
</div>
<?php endif; ?>
<?php render_admin_footer(); ?>
