<?php
require_once __DIR__.'/includes/bootstrap.php';
if(is_logged_in()){
    if(current_role()==='admin') redirect('/admin/dashboard.php');
    else redirect('/customer/dashboard.php');
}
$pdo=db();
$plans=$pdo->query('SELECT * FROM policy_plans WHERE status="active" ORDER BY premium_amount ASC')->fetchAll();
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Airline Insurance — Fly with Confidence</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?=BASE_URL?>/assets/css/style.css">
<link rel="stylesheet" href="<?=BASE_URL?>/assets/css/auth.css">
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>
<!-- Header -->
<header class="site-header">
  <div class="container">
    <div class="brand light"><?=icon('shield')?> Airline Insurance</div>
    <nav>
      <a href="#plans">Plans</a>
      <a href="#how">How It Works</a>
      <a href="#contact">Contact</a>
    </nav>
    <div class="hdr-actions">
      <a href="<?=BASE_URL?>/auth/login.php" class="btn btn-secondary btn-sm">Log In</a>
      <a href="<?=BASE_URL?>/auth/register.php" class="btn btn-primary btn-sm">Get Started</a>
    </div>
  </div>
</header>

<!-- Hero -->
<main id="main">
<section class="hero">
  <div class="container">
    <div>
      <div class="eyebrow"><?=icon('shield')?> Licensed & Regulated Insurance</div>
      <h1>Protection for Every Journey</h1>
      <p class="lead">From flight delays and cancellations to lost baggage and medical emergencies — our plans cover the unexpected so you can travel without concern.</p>
      <div class="hero-actions">
        <a href="<?=BASE_URL?>/auth/register.php" class="btn btn-primary btn-lg"><?=icon('arrow-right')?> Get Covered</a>
        <a href="#plans" class="btn btn-secondary btn-lg">View Plans</a>
      </div>
      <div class="trust-row">
        <div class="ti"><strong>$50K+</strong><span>Max coverage</span></div>
        <div class="ti"><strong>6 Plans</strong><span>Tailored options</span></div>
        <div class="ti"><strong>24h</strong><span>Claim processing</span></div>
      </div>
    </div>
    <div class="hero-vis">
      <div class="hv-top">
        <div class="brand" style="color:var(--white)"><?=icon('shield')?> Airline Insurance</div>
        <?=status_badge('active')?>
      </div>
      <div class="hv-route">
        <span class="city">JFK</span>
        <div class="rline"><?=icon('plane')?></div>
        <span class="city">LHR</span>
      </div>
      <div class="hv-card">
        <div class="hv-card-top"><span>Personal Accident Guard</span><span>Active</span></div>
        <div class="hv-card-amt">$50,000.00</div>
      </div>
      <div class="hv-card">
        <div class="hv-card-top"><span>Travel Medical Care</span><span>Active</span></div>
        <div class="hv-card-amt">$25,000.00</div>
      </div>
    </div>
  </div>
</section>

<!-- Plans -->
<section class="section section-alt" id="plans">
  <div class="container">
    <div class="section-head">
      <div class="eyebrow"><?=icon('briefcase')?> Coverage Options</div>
      <h2>Choose Your Plan</h2>
      <p>Each plan is designed to cover a specific risk category. Pick one or combine multiple plans for complete peace of mind.</p>
    </div>
    <div class="plan-grid">
    <?php foreach($plans as $plan): ?>
      <div class="plan-tile">
        <div class="pi"><?=icon(category_icon($plan['category']))?></div>
        <div class="bc-eye" style="margin-bottom:4px"><?=e(category_label($plan['category']))?></div>
        <h3><?=e($plan['plan_name'])?></h3>
        <p><?=e($plan['description'])?></p>
        <div class="plan-price"><?=format_money($plan['premium_amount'])?> <span>/ year &middot; <?=format_money($plan['coverage_amount'])?> coverage</span></div>
      </div>
    <?php endforeach; ?>
    </div>
    <div style="text-align:center;margin-top:32px">
      <a href="<?=BASE_URL?>/auth/register.php" class="btn btn-primary"><?=icon('arrow-right')?> Start an Application</a>
    </div>
  </div>
</section>

<!-- How it works -->
<section class="section" id="how">
  <div class="container">
    <div class="section-head">
      <div class="eyebrow"><?=icon('list')?> Process</div>
      <h2>How It Works</h2>
    </div>
    <div class="steps-row">
      <div><div class="step-num">1</div><h4>Create an Account</h4><p>Register with your email and set a secure password. It takes less than a minute.</p></div>
      <div><div class="step-num">2</div><h4>Choose a Plan</h4><p>Browse our six coverage categories and select the plan that matches your travel needs.</p></div>
      <div><div class="step-num">3</div><h4>Pay the Premium</h4><p>Pay securely by card or bank transfer. Your policy activates once payment is confirmed.</p></div>
      <div><div class="step-num">4</div><h4>File a Claim</h4><p>If the unexpected happens, submit a claim online. Our team reviews it within 24 hours.</p></div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="section section-alt">
  <div class="container">
    <div class="cta-banner">
      <div><h2>Ready to Fly with Confidence?</h2><p>Join thousands of travellers who protect every journey with Airline Insurance.</p></div>
      <a href="<?=BASE_URL?>/auth/register.php" class="btn btn-primary btn-lg" style="white-space:nowrap"><?=icon('arrow-right')?> Get Covered Today</a>
    </div>
  </div>
</section>
</main>

<!-- Footer -->
<footer class="site-footer" id="contact">
  <div class="container">
    <div class="footer-grid">
      <div>
        <div class="brand" style="margin-bottom:14px"><?=icon('shield')?> Airline Insurance</div>
        <p style="color:rgba(255,255,255,.65);font-size:13.5px;max-width:280px;margin:0">Protecting air travellers against flight disruptions and unexpected events since 2010.</p>
      </div>
      <div><h4>Products</h4><ul>
        <li><a href="#plans">Flight Delay</a></li><li><a href="#plans">Cancellation</a></li>
        <li><a href="#plans">Lost Baggage</a></li><li><a href="#plans">Personal Accident</a></li>
      </ul></div>
      <div><h4>Company</h4><ul>
        <li><a href="#how">How It Works</a></li><li><a href="#contact">Contact Us</a></li>
        <li><a href="<?=BASE_URL?>/auth/login.php">Log In</a></li><li><a href="<?=BASE_URL?>/auth/register.php">Register</a></li>
      </ul></div>
      <div><h4>Support</h4><ul>
        <li><a href="mailto:support@airlineinsurance.com">Email Support</a></li>
        <li><a href="tel:+15550100001">+1 555 010 0001</a></li>
        <li><a href="#">Claims Portal</a></li>
      </ul></div>
    </div>
    <div class="footer-bottom">
      <span>&copy; <?=date('Y')?> Airline Insurance. All rights reserved.</span>
      <span>Licensed insurer &middot; Regulatory compliant</span>
    </div>
  </div>
</footer>
<style>.skip-link{position:absolute;left:-999px}.skip-link:focus{left:12px;top:12px;background:var(--navy-900);color:#fff;padding:8px 14px;border-radius:var(--r-sm);z-index:2000}</style>
<script src="<?=BASE_URL?>/assets/js/app.js"></script>
</body>
</html>
