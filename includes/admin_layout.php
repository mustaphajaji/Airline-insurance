<?php
function render_admin_header($active='dashboard'){
    $u=current_user();
    $initials=strtoupper(substr($u['full_name'],0,1));
    $bu=BASE_URL;
    $pdo=db();
    $pendingPol=(int)$pdo->query('SELECT COUNT(*) FROM policies WHERE status="pending"')->fetchColumn();
    $pendingCla=(int)$pdo->query('SELECT COUNT(*) FROM claims WHERE status="submitted"')->fetchColumn();
    echo '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>'.htmlspecialchars($GLOBALS['pageTitle']??'Admin',ENT_QUOTES).' — Airline Insurance Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="'.$bu.'/assets/css/style.css">
<link rel="stylesheet" href="'.$bu.'/assets/css/admin.css">
</head>
<body>
<div class="admin-shell">
<div class="sb-overlay" id="sb-overlay"></div>
<aside class="admin-sidebar" id="admin-sidebar">
  <div class="sb-brand">'.icon('shield').' Airline Insurance<span style="font-size:11px;background:var(--blue-600);padding:2px 8px;border-radius:var(--r-pill);margin-left:auto">Admin</span></div>
  <div class="sb-section">Main</div>
  <ul class="sb-nav">';
    $nav=array(
        'dashboard'=>array('icon'=>'grid','label'=>'Dashboard','href'=>$bu.'/admin/dashboard.php'),
        'applications'=>array('icon'=>'file-text','label'=>'Applications','href'=>$bu.'/admin/applications.php','count'=>$pendingPol),
        'policies'=>array('icon'=>'shield','label'=>'Policies','href'=>$bu.'/admin/policies.php'),
        'claims'=>array('icon'=>'alert-triangle','label'=>'Claims','href'=>$bu.'/admin/claims.php','count'=>$pendingCla),
        'payments'=>array('icon'=>'credit-card','label'=>'Payments','href'=>$bu.'/admin/payments.php'),
    );
    foreach($nav as $k=>$n){
        $cls=$k===$active?'active':'';
        $count=isset($n['count'])&&$n['count']>0?'<span class="nc">'.$n['count'].'</span>':'';
        echo '<li><a href="'.htmlspecialchars($n['href'],ENT_QUOTES).'" class="'.$cls.'">'.icon($n['icon']).' '.htmlspecialchars($n['label'],ENT_QUOTES).$count.'</a></li>';
    }
    echo '</ul>
  <div class="sb-section">Management</div>
  <ul class="sb-nav">';
    $nav2=array(
        'users'=>array('icon'=>'users','label'=>'Customers','href'=>$bu.'/admin/users.php'),
        'plans'=>array('icon'=>'briefcase','label'=>'Insurance Plans','href'=>$bu.'/admin/plans.php'),
        'settings'=>array('icon'=>'settings','label'=>'Settings','href'=>$bu.'/admin/settings.php'),
    );
    foreach($nav2 as $k=>$n){
        $cls=$k===$active?'active':'';
        echo '<li><a href="'.htmlspecialchars($n['href'],ENT_QUOTES).'" class="'.$cls.'">'.icon($n['icon']).' '.htmlspecialchars($n['label'],ENT_QUOTES).'</a></li>';
    }
    echo '</ul>
  <div class="sb-bottom">
    <div class="sb-user">
      <div class="av">'.htmlspecialchars($initials,ENT_QUOTES).'</div>
      <div><div class="su-name">'.htmlspecialchars($u['full_name'],ENT_QUOTES).'</div><div class="su-role">Administrator</div></div>
    </div>
    <form method="post" action="'.$bu.'/auth/logout.php" style="margin:4px 0 0">
      '.csrf_field().'
      <button type="submit" style="display:flex;align-items:center;gap:10px;width:100%;padding:10px 12px;border-radius:var(--r-sm);color:rgba(255,255,255,.6);background:none;border:none;cursor:pointer;font-size:14px;font-family:inherit">'.icon('logout').' Log Out</button>
    </form>
  </div>
</aside>
<div class="admin-content">
<header class="admin-topbar">
  <div class="at-left">
    <button class="sb-toggle" id="sb-toggle" aria-label="Toggle sidebar">'.icon('menu').'</button>
    <span class="at-title">'.htmlspecialchars($GLOBALS['pageTitle']??'Dashboard',ENT_QUOTES).'</span>
  </div>
  <div class="at-right">
    <span style="font-size:13px;color:var(--ink-500)">'.date('M j, Y').'</span>
  </div>
</header>
<div class="admin-main">';
}

function render_admin_footer(){
    $f=get_flash();
    echo '</div></div></div>';
    if($f){
        $type=$f['type']==='success'?'success':($f['type']==='error'?'error':'info');
        echo '<div class="flash-msg alert alert-'.$type.'" style="position:fixed;bottom:20px;right:20px;z-index:999;max-width:380px;box-shadow:var(--shadow-lg)">'.icon($type==='success'?'check-circle':($type==='error'?'x-circle':'info')).' '.htmlspecialchars($f['msg'],ENT_QUOTES).'</div>';
    }
    echo '<script src="'.BASE_URL.'/assets/js/app.js"></script></body></html>';
}
