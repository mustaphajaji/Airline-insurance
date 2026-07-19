<?php
/**
 * Airline Insurance — One-Click Setup Script
 * Visit http://localhost/airline-insurance/setup.php in your browser
 * AFTER importing database/schema.sql in phpMyAdmin.
 * This script inserts demo accounts with correctly hashed passwords.
 */

define('BASE_PATH', __DIR__);
require_once __DIR__.'/config/config.php';
require_once __DIR__.'/config/database.php';

$msg = '';
$ok  = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_setup'])) {
    try {
        $pdo = db();

        // Check if tables exist
        $tables = $pdo->query("SHOW TABLES LIKE 'users'")->fetchColumn();
        if (!$tables) {
            throw new RuntimeException('Tables not found. Please import database/schema.sql in phpMyAdmin first.');
        }

        // Remove existing seed users
        $pdo->exec("DELETE FROM users WHERE email IN ('admin@airlineinsurance.com','customer@airlineinsurance.com')");

        // Insert with PHP-hashed passwords
        $adminHash    = password_hash('Admin@12345',   PASSWORD_BCRYPT);
        $customerHash = password_hash('Customer@123',  PASSWORD_BCRYPT);

        $ins = $pdo->prepare("INSERT INTO users (full_name,email,phone,password_hash,role,status) VALUES (?,?,?,?,?,?)");
        $ins->execute(['System Administrator','admin@airlineinsurance.com','+1 555 010 0001',$adminHash,'admin','active']);
        $ins->execute(['Sarah Mitchell','customer@airlineinsurance.com','+1 555 010 0002',$customerHash,'customer','active']);

        // Ensure plans exist
        $planCount = (int)$pdo->query("SELECT COUNT(*) FROM policy_plans")->fetchColumn();
        if ($planCount === 0) {
            $pdo->exec("INSERT INTO policy_plans (plan_code,category,plan_name,description,coverage_amount,premium_amount,duration_days,status) VALUES
            ('FD-100','flight_delay','Flight Delay Shield','Compensation for covered delays of 3 hours or more, including meals and rebooking costs.',1500.00,18.00,365,'active'),
            ('FC-200','flight_cancellation','Cancellation Cover Plus','Reimbursement for non-refundable tickets and reasonable rebooking expenses.',4000.00,32.00,365,'active'),
            ('LB-300','lost_baggage','Baggage Protect','Covers lost, damaged or delayed baggage including essential item replacement.',2000.00,14.00,365,'active'),
            ('PA-400','personal_accident','Personal Accident Guard','Lump-sum benefit for accidental injury or death occurring during air travel.',50000.00,45.00,365,'active'),
            ('ME-500','medical_emergency','Travel Medical Care','Covers emergency medical treatment, hospitalisation and medical evacuation.',25000.00,38.00,365,'active'),
            ('OT-600','other','Trip Hardship Cover','A flexible plan covering other unexpected travel disruptions.',3000.00,22.00,365,'active')");
        }

        // Ensure uploads directory exists
        if (!is_dir(__DIR__.'/uploads/claims')) {
            mkdir(__DIR__.'/uploads/claims', 0755, true);
        }

        $ok  = true;
        $msg = 'Setup complete! Demo accounts are ready.';

    } catch (Exception $e) {
        $msg = 'Error: ' . $e->getMessage();
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Setup — Airline Insurance</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root{--blue-600:#1a46c4;--navy-900:#0a1f44;--ok-600:#157a5a;--ok-50:#e9f7f1;--bad-600:#b3261e;--bad-50:#fdecec;}
*{box-sizing:border-box;}
body{margin:0;font-family:'Inter','Segoe UI',sans-serif;background:#f6f9fd;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;}
.box{background:#fff;border-radius:18px;box-shadow:0 16px 40px rgba(10,31,68,.12);max-width:540px;width:100%;padding:40px;}
.logo{display:flex;align-items:center;gap:10px;font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;font-size:18px;color:var(--navy-900);margin-bottom:28px;}
.logo svg{color:var(--blue-600);}
h2{font-family:'Plus Jakarta Sans',sans-serif;font-size:22px;font-weight:700;color:var(--navy-900);margin:0 0 6px;}
p{color:#62718c;font-size:14.5px;line-height:1.6;}
.steps{background:#f6f9fd;border-radius:10px;padding:18px;margin:20px 0;list-style:none;padding-left:18px;}
.steps li{color:#34415c;font-size:14px;margin-bottom:10px;position:relative;padding-left:22px;}
.steps li::before{content:attr(data-n);position:absolute;left:0;background:var(--blue-600);color:#fff;width:18px;height:18px;border-radius:50%;font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center;top:1px;}
.alert{padding:14px 16px;border-radius:8px;font-size:14px;margin-bottom:18px;display:flex;align-items:flex-start;gap:10px;}
.alert-ok{background:var(--ok-50);color:var(--ok-600);border:1px solid #c7e9da;}
.alert-err{background:var(--bad-50);color:var(--bad-600);border:1px solid #f3cdcb;}
.btn{display:inline-flex;align-items:center;justify-content:center;padding:13px 24px;background:var(--blue-600);color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:600;cursor:pointer;width:100%;font-family:inherit;}
.btn:hover{background:#14379c;}
.creds{background:#eef4ff;border:1px solid #dde9ff;border-radius:8px;padding:14px;font-size:13px;color:var(--navy-900);margin-top:20px;line-height:1.8;}
.creds strong{display:block;margin-bottom:4px;font-size:13.5px;}
code{background:rgba(36,88,232,.1);padding:1px 6px;border-radius:4px;font-family:monospace;}
.links{display:flex;gap:12px;margin-top:18px;flex-wrap:wrap;}
.links a{display:inline-flex;align-items:center;padding:10px 18px;border-radius:8px;font-size:14px;font-weight:600;text-decoration:none;}
.link-admin{background:var(--navy-900);color:#fff;}
.link-cust{background:#fff;color:var(--blue-600);border:1.5px solid #b9d1ff;}
</style>
</head>
<body>
<div class="box">
  <div class="logo">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l8 3v6c0 4.5-3.4 8.1-8 9-4.6-.9-8-4.5-8-9V6l8-3z"/><path d="M9 12l2 2 4-4"/></svg>
    Airline Insurance
  </div>
  <h2>One-Click Setup</h2>
  <p>This script seeds the database with demo accounts so you can log in immediately.</p>

  <?php if ($msg): ?>
    <div class="alert <?= $ok ? 'alert-ok' : 'alert-err' ?>">
      <?= $ok ? '✓' : '✗' ?> <?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?>
    </div>
  <?php endif; ?>

  <?php if (!$ok): ?>
  <ol class="steps" style="list-style:none;padding:16px 18px">
    <li data-n="1">Start <strong>Apache</strong> and <strong>MySQL</strong> in the XAMPP Control Panel.</li>
    <li data-n="2">Open <strong>phpMyAdmin</strong> → Create database <code>airline_insurance</code>.</li>
    <li data-n="3">Import <code>database/schema.sql</code> (Import tab → Choose file).</li>
    <li data-n="4">Click <strong>Run Setup</strong> below to hash passwords correctly.</li>
  </ol>
  <form method="post" action="">
    <button type="submit" name="run_setup" value="1" class="btn">▶ Run Setup Now</button>
  </form>
  <?php else: ?>
  <div class="creds">
    <strong>Admin Account</strong>
    Email: <code>admin@airlineinsurance.com</code><br>
    Password: <code>Admin@12345</code>
    <br><br>
    <strong>Customer Account</strong>
    Email: <code>customer@airlineinsurance.com</code><br>
    Password: <code>Customer@123</code>
  </div>
  <div class="links">
    <a href="admin/dashboard.php" class="links a link-admin">→ Admin Panel</a>
    <a href="auth/login.php" class="links a link-cust">→ Customer Login</a>
  </div>
  <p style="margin-top:14px;font-size:12.5px;color:#9aa6bc">Security: delete or rename <code>setup.php</code> after setup in a production environment.</p>
  <?php endif; ?>
</div>
</body>
</html>
