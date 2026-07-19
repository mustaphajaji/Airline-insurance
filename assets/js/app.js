
/* Airline Insurance — shared JS */
document.addEventListener('DOMContentLoaded', function () {

  /* ---- Hamburger nav (customer) ---- */
  var hamBtn = document.getElementById('ham-btn');
  var custNav = document.getElementById('cust-nav');
  var navOverlay = document.getElementById('nav-overlay');
  if (hamBtn && custNav) {
    function openNav() { custNav.classList.add('open'); navOverlay && navOverlay.classList.add('open'); hamBtn.setAttribute('aria-expanded','true'); }
    function closeNav() { custNav.classList.remove('open'); navOverlay && navOverlay.classList.remove('open'); hamBtn.setAttribute('aria-expanded','false'); }
    hamBtn.addEventListener('click', function () { custNav.classList.contains('open') ? closeNav() : openNav(); });
    if (navOverlay) navOverlay.addEventListener('click', closeNav);
  }

  /* ---- Sidebar toggle (admin) ---- */
  var sbToggle = document.getElementById('sb-toggle');
  var adminSidebar = document.getElementById('admin-sidebar');
  var sbOverlay = document.getElementById('sb-overlay');
  if (sbToggle && adminSidebar) {
    function openSb() { adminSidebar.classList.add('open'); sbOverlay && sbOverlay.classList.add('open'); }
    function closeSb() { adminSidebar.classList.remove('open'); sbOverlay && sbOverlay.classList.remove('open'); }
    sbToggle.addEventListener('click', function () { adminSidebar.classList.contains('open') ? closeSb() : openSb(); });
    if (sbOverlay) sbOverlay.addEventListener('click', closeSb);
  }

  /* ---- User dropdown ---- */
  var chip = document.getElementById('user-chip');
  var dropdown = document.getElementById('user-dropdown');
  if (chip && dropdown) {
    chip.addEventListener('click', function (e) { e.stopPropagation(); dropdown.classList.toggle('open'); });
    document.addEventListener('click', function () { dropdown && dropdown.classList.remove('open'); });
  }

  /* ---- Password visibility toggle ---- */
  document.querySelectorAll('.pw-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var inp = this.parentNode.querySelector('input');
      if (!inp) return;
      var isText = inp.type === 'text';
      inp.type = isText ? 'password' : 'text';
      var eyeIcon = this.querySelector('.eye-on');
      var eyeOffIcon = this.querySelector('.eye-off');
      if (eyeIcon) eyeIcon.style.display = isText ? '' : 'none';
      if (eyeOffIcon) eyeOffIcon.style.display = isText ? 'none' : '';
    });
  });

  /* ---- Modal open/close ---- */
  document.querySelectorAll('[data-modal-open]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var id = this.getAttribute('data-modal-open');
      var modal = document.getElementById(id);
      if (modal) modal.classList.add('is-open');
    });
  });
  document.querySelectorAll('[data-modal-close]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var id = this.getAttribute('data-modal-close');
      var modal = document.getElementById(id);
      if (modal) modal.classList.remove('is-open');
    });
  });
  document.querySelectorAll('.modal-overlay').forEach(function (overlay) {
    overlay.addEventListener('click', function (e) {
      if (e.target === this) this.classList.remove('is-open');
    });
  });

  /* ---- Auto-dismiss flash after 5 s ---- */
  var flash = document.querySelector('.flash-msg');
  if (flash) { setTimeout(function () { flash.style.opacity = '0'; flash.style.transition = 'opacity .5s'; setTimeout(function () { flash.remove(); }, 500); }, 5000); }

  /* ---- Plan select — update amounts on apply form ---- */
  var planSel = document.getElementById('plan_id');
  if (planSel) {
    planSel.addEventListener('change', function () {
      var opt = this.options[this.selectedIndex];
      var cov = document.getElementById('coverage_display');
      var prem = document.getElementById('premium_display');
      var premInput = document.getElementById('premium_amount');
      var covInput  = document.getElementById('coverage_amount');
      if (cov)  cov.textContent  = opt.dataset.coverage  || '—';
      if (prem) prem.textContent = opt.dataset.premium   || '—';
      if (premInput) premInput.value = opt.dataset.premiumVal || '';
      if (covInput)  covInput.value  = opt.dataset.coverageVal || '';
    });
  }

  /* ---- Confirm delete / action dialogs ---- */
  document.querySelectorAll('[data-confirm]').forEach(function (el) {
    el.addEventListener('click', function (e) {
      if (!confirm(this.dataset.confirm)) e.preventDefault();
    });
  });

  /* ---- Character counter for textareas ---- */
  document.querySelectorAll('textarea[maxlength]').forEach(function (ta) {
    var max = parseInt(ta.getAttribute('maxlength'), 10);
    var counter = document.createElement('div');
    counter.className = 'text-sm text-muted mt-8';
    counter.textContent = '0 / ' + max;
    ta.parentNode.insertBefore(counter, ta.nextSibling);
    ta.addEventListener('input', function () { counter.textContent = this.value.length + ' / ' + max; });
  });

});
