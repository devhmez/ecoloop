// Menu mobile
const side = document.getElementById('side'), scrim = document.getElementById('scrim'), btn = document.getElementById('menuBtn');
function toggleMenu(open) { side.classList.toggle('open', open); scrim.hidden = !open; }
btn?.addEventListener('click', () => toggleMenu(true));
scrim?.addEventListener('click', () => toggleMenu(false));

// Fermer les messages flash (et disparition automatique)
document.querySelectorAll('.flash').forEach(f => {
  f.querySelector('.flash-x')?.addEventListener('click', () => f.remove());
  setTimeout(() => f.remove(), 7000);
});

// Confirmation avant les actions sensibles : <form data-confirm="Message">
document.querySelectorAll('form[data-confirm]').forEach(form => {
  form.addEventListener('submit', e => { if (!confirm(form.dataset.confirm)) e.preventDefault(); });
});

// Copier le code d'échange : <button data-copy="ECO-XXXX">
document.querySelectorAll('[data-copy]').forEach(b => b.addEventListener('click', async () => {
  try { await navigator.clipboard.writeText(b.dataset.copy); const t = b.textContent; b.textContent = 'Copié !'; setTimeout(() => b.textContent = t, 1500); } catch (_) {}
}));
