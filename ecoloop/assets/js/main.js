// Small client-side behaviours: theme, mobile menu, scanner demo, image preview.
(function () {
  var root = document.documentElement;

  // Theme toggle (remembered in the browser)
  try { var saved = localStorage.getItem('ecoloop-theme'); if (saved) root.setAttribute('data-theme', saved); } catch (e) { /* storage unavailable */ }
  var themeBtn = document.getElementById('themeBtn');
  if (themeBtn) themeBtn.addEventListener('click', function () {
    var dark = root.getAttribute('data-theme') === 'dark' ||
      (!root.getAttribute('data-theme') && window.matchMedia('(prefers-color-scheme: dark)').matches);
    var next = dark ? 'light' : 'dark';
    root.setAttribute('data-theme', next);
    try { localStorage.setItem('ecoloop-theme', next); } catch (e) { /* ignore */ }
  });

  // Mobile menu (front navbar or back sidebar)
  var menuBtn = document.getElementById('menuBtn');
  var nav = document.getElementById('mainNav') || document.getElementById('sideNav');
  var scrim = document.getElementById('scrim');
  function setMenu(open) {
    if (!nav) return;
    nav.classList.toggle('open', open);
    if (menuBtn) menuBtn.setAttribute('aria-expanded', String(open));
    if (scrim) scrim.hidden = !open;
  }
  if (menuBtn) menuBtn.addEventListener('click', function () { setMenu(!nav.classList.contains('open')); });
  var menuClose = document.getElementById('menuClose');
  if (menuClose) menuClose.addEventListener('click', function () { setMenu(false); });
  if (scrim) scrim.addEventListener('click', function () { setMenu(false); });

  // Photo preview (scanner and report form). The image never leaves the browser.
  document.querySelectorAll('input[type=file][data-preview]').forEach(function (input) {
    input.addEventListener('change', function () {
      var target = document.getElementById(input.getAttribute('data-preview'));
      var file = input.files && input.files[0];
      if (!target || !file || file.type.indexOf('image/') !== 0) return;
      var url = URL.createObjectURL(file);
      target.querySelector('img').src = url;
      target.hidden = false;
      input.dataset.name = file.name + file.size;
    });
  });

  // Scanner demo: ask the server for a predefined sample result
  var scanner = document.getElementById('scanner');
  if (scanner) {
    var cats = ['plastic', 'paper', 'glass', 'metal', 'organic', 'electronics'];
    var fileInput = document.getElementById('scanFile');
    var out = document.getElementById('scanResult');
    var empty = document.getElementById('scanEmpty');
    var tip = document.getElementById('scanTip');

    var esc = function (s) { return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); };
    function hash(str) { var h = 0; for (var i = 0; i < str.length; i++) h = (h * 31 + str.charCodeAt(i)) >>> 0; return h; }

    function show(cat) {
      empty.innerHTML = '<b>Looking at the image…</b><span class="muted">Checking shape, material and labels (simulated).</span>';
      out.hidden = true;
      setTimeout(function () {
        fetch('/scanner/analyze?cat=' + cat, { method: 'POST' }).then(function (r) { return r.json(); }).then(function (r) {
          empty.hidden = true; out.hidden = false;
          out.innerHTML =
            '<div class="res-top"><span class="badge b-info"><i></i>' + esc(r.category) + '</span><span class="muted sm">Detected: <b>' + esc(r.item) + '</b></span></div>' +
            '<div><div class="conf"><span>Confidence</span><b>' + r.conf + '%</b></div><div class="progress"><div style="width:' + r.conf + '%"></div></div></div>' +
            '<p>' + esc(r.desc) + '</p>' +
            '<div><p class="label">How to recycle it</p><ol class="olist">' + r.steps.map(function (s) { return '<li>' + esc(s) + '</li>'; }).join('') + '</ol></div>' +
            '<p class="muted sm">Goes in: <b>' + esc(r.bin) + '</b> · +10 EcoPoints added</p>';
          tip.textContent = r.tip;
        });
      }, 1200);
    }

    document.getElementById('analyzeBtn').addEventListener('click', function () {
      var f = fileInput.files && fileInput.files[0];
      if (!f) { empty.hidden = false; empty.innerHTML = '<b>Choose a photo first</b><span class="muted">Or try one of the sample items below.</span>'; return; }
      show(cats[hash(f.name + f.size) % cats.length]);
    });
    document.querySelectorAll('[data-sample]').forEach(function (b) {
      b.addEventListener('click', function () { show(b.getAttribute('data-sample')); });
    });
  }
}());
