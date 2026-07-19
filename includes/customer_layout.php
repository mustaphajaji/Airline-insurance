<?php
// Call render_customer_header($activeNav) after setting $pageTitle
function render_customer_header($active='dashboard'){
    $u=current_user();
    $initials=strtoupper(substr($u['full_name'],0,1));
    $uc=unread_count($u['id']);
    $bu=BASE_URL;
    echo '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>'.htmlspecialchars($GLOBALS['pageTitle']??'Dashboard',ENT_QUOTES).' — Airline Insurance</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="'.$bu.'/assets/css/style.css">
<link rel="stylesheet" href="'.$bu.'/assets/css/customer.css">
</head>
<body>';
    $navLinks=array(
        'dashboard'=>array('icon'=>'home','label'=>'Dashboard','href'=>$bu.'/customer/dashboard.php'),
        'plans'=>array('icon'=>'briefcase','label'=>'Plans','href'=>$bu.'/customer/plans.php'),
        'apply'=>array('icon'=>'file-text','label'=>'Apply','href'=>$bu.'/customer/apply.php'),
        'policies'=>array('icon'=>'shield','label'=>'My Policies','href'=>$bu.'/customer/policies.php'),
        'claims'=>array('icon'=>'alert-triangle','label'=>'Claims','href'=>$bu.'/customer/claims.php'),
        'payments'=>array('icon'=>'credit-card','label'=>'Payments','href'=>$bu.'/customer/payments.php'),
        'notifications'=>array('icon'=>'bell','label'=>'Notifications','href'=>$bu.'/customer/notifications.php'),
        'profile'=>array('icon'=>'user','label'=>'Profile','href'=>$bu.'/customer/profile.php'),
    );
    echo '<div class="nav-overlay" id="nav-overlay"></div>
<header class="cust-topbar">
  <div class="topbar-inner">
    <div style="display:flex;align-items:center;gap:14px">
      <button class="ham-btn" id="ham-btn" aria-label="Toggle navigation" aria-expanded="false" aria-controls="cust-nav">'.icon('menu').'</button>
      <a href="'.$bu.'/" class="brand light">'.icon('shield').' Airline Insurance</a>
    </div>
    <nav class="cust-nav" id="cust-nav">';
    foreach($navLinks as $key=>$nl){
        $cls=$key===$active?'active':'';
        echo '<a href="'.htmlspecialchars($nl['href'],ENT_QUOTES).'" class="'.$cls.'">'.icon($nl['icon']).' '.htmlspecialchars($nl['label'],ENT_QUOTES).'</a>';
    }
    echo '</nav>
    <div class="topbar-right">
      <a href="'.$bu.'/customer/notifications.php" class="icon-btn notif-btn" aria-label="Notifications">'.icon('bell').($uc>0?'<span class="ndot"></span>':'').'</a>
      <div class="user-chip" id="user-chip">
        <div class="av">'.htmlspecialchars($initials,ENT_QUOTES).'</div>
        <span class="uname">'.htmlspecialchars(explode(' ',$u['full_name'])[0],ENT_QUOTES).'</span>
        '.icon('chevron-down').'
        <div class="dropdown" id="user-dropdown">
          <a href="'.$bu.'/customer/profile.php">'.icon('user').' Profile</a>
          <a href="'.$bu.'/customer/notifications.php">'.icon('bell').' Notifications'.($uc>0?' <span class="badge badge-active" style="margin-left:auto;padding:2px 7px">'.$uc.'</span>':'').'</a>
          <hr>
          <form method="post" action="'.$bu.'/auth/logout.php" style="margin:0">
            '.csrf_field().'
            <button type="submit">'.icon('logout').' Log Out</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</header>
<main class="cust-main">';
}

function render_customer_footer(){
    $f=get_flash();
    echo '</main>';
    if($f){
        $type=$f['type']==='success'?'success':($f['type']==='error'?'error':'info');
        echo '<div class="flash-msg alert alert-'.$type.'" style="position:fixed;bottom:20px;right:20px;z-index:999;max-width:380px;box-shadow:var(--shadow-lg)">'.icon($type==='success'?'check-circle':($type==='error'?'x-circle':'info')).' '.htmlspecialchars($f['msg'],ENT_QUOTES).'</div>';
    }
    echo '<script src="'.BASE_URL.'/assets/js/app.js"></script></body></html>';
}
