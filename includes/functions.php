<?php
function e($v){ return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8'); }
function old($k,$d=''){ return isset($_SESSION['old'][$k])?e($_SESSION['old'][$k]):e($d); }
function set_old($d){ $_SESSION['old']=$d; }
function clear_old(){ unset($_SESSION['old']); }
function format_money($a){ return '$'.number_format((float)$a,2); }
function format_date($d,$f='M j, Y'){
    if(empty($d)||$d==='0000-00-00') return '—';
    $ts=is_numeric($d)?(int)$d:strtotime($d);
    return $ts?date($f,$ts):'—';
}
function format_datetime($d){ return format_date($d,'M j, Y \a\t g:i A'); }
function gen_policy_num(){ return 'POL-'.date('Y').'-'.strtoupper(substr(bin2hex(random_bytes(4)),0,6)); }
function gen_claim_num(){ return 'CLM-'.date('Y').'-'.strtoupper(substr(bin2hex(random_bytes(4)),0,6)); }
function gen_txn_ref(){ return 'TXN-'.strtoupper(substr(bin2hex(random_bytes(6)),0,12)); }
function category_label($c){
    $m=array('flight_delay'=>'Flight Delay','flight_cancellation'=>'Flight Cancellation','lost_baggage'=>'Lost Baggage','personal_accident'=>'Personal Accident','medical_emergency'=>'Medical Emergency','other'=>'Other Hardship');
    return isset($m[$c])?$m[$c]:ucwords(str_replace('_',' ',$c));
}
function category_icon($c){
    $m=array('flight_delay'=>'clock','flight_cancellation'=>'x-circle','lost_baggage'=>'suitcase','personal_accident'=>'user-shield','medical_emergency'=>'heart-pulse','other'=>'alert-triangle');
    return isset($m[$c])?$m[$c]:'shield';
}
function status_badge($s){
    $s=strtolower($s);
    $labels=array('pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected','active'=>'Active','expired'=>'Expired','cancelled'=>'Cancelled','submitted'=>'Submitted','under_review'=>'Under Review','paid'=>'Paid','completed'=>'Completed','failed'=>'Failed','suspended'=>'Suspended','inactive'=>'Inactive');
    $l=isset($labels[$s])?$labels[$s]:ucwords(str_replace('_',' ',$s));
    return '<span class="badge badge-'.e($s).'">'.e($l).'</span>';
}
function notify_user($uid,$title,$msg){
    $pdo=db();
    $pdo->prepare('INSERT INTO notifications(user_id,title,message) VALUES(?,?,?)')->execute(array($uid,$title,$msg));
}
function unread_count($uid){
    $s=db()->prepare('SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0');
    $s->execute(array($uid));
    return (int)$s->fetchColumn();
}
function validate_password($p){
    if(strlen($p)<8) return 'Password must be at least 8 characters.';
    if(!preg_match('/[A-Z]/',$p)) return 'Password must contain an uppercase letter.';
    if(!preg_match('/[a-z]/',$p)) return 'Password must contain a lowercase letter.';
    if(!preg_match('/[0-9]/',$p)) return 'Password must contain a number.';
    return null;
}
function handle_upload($field){
    if(empty($_FILES[$field])||$_FILES[$field]['error']===UPLOAD_ERR_NO_FILE) return null;
    $f=$_FILES[$field];
    if($f['error']!==UPLOAD_ERR_OK) throw new RuntimeException('Upload error. Please try again.');
    if($f['size']>MAX_UPLOAD_SIZE) throw new RuntimeException('File too large. Max 5MB.');
    $ext=strtolower(pathinfo($f['name'],PATHINFO_EXTENSION));
    if(!in_array($ext,ALLOWED_UPLOAD_TYPES,true)) throw new RuntimeException('Invalid file type. Use PDF, JPG or PNG.');
    if(!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR,0755,true);
    $safe='doc_'.bin2hex(random_bytes(8)).'.'.$ext;
    if(!move_uploaded_file($f['tmp_name'],UPLOAD_DIR.$safe)) throw new RuntimeException('Could not save file.');
    return array('path'=>$safe,'name'=>$f['name']);
}
