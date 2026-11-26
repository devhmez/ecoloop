<?php // Petit style propre au module Centres (pour ne pas toucher au style.css de l'équipe) ?>
<style>
    .grid-centres { display: grid; grid-template-columns: repeat(auto-fill, minmax(290px, 1fr)); gap: 16px; margin-top: 20px; }
    .centre { background: #fff; border-radius: 18px; padding: 22px; box-shadow: 0 8px 30px rgba(12, 59, 43, .06); }
    .centre h3 { margin: 0 0 4px; font-size: 1.08rem; }
    .centre p { margin: 4px 0; color: var(--muted); font-size: .92rem; }
    .centre .km { color: var(--green); font-weight: 700; }
    .chips { display: flex; flex-wrap: wrap; gap: 6px; margin: 12px 0; }
    .chip { display: inline-block; padding: 4px 10px; border-radius: 999px; background: #e7eeea; color: var(--green-dark); font-size: .78rem; font-weight: 700; }
    a.chip { text-decoration: none; }
    a.chip.on { background: var(--green-dark); color: var(--lime); }
    .chip-check { display: inline-flex; align-items: center; gap: 6px; margin: 0 8px 8px 0; padding: 8px 14px; border: 1.5px solid var(--line); border-radius: 999px; cursor: pointer; font-weight: 600; }
    .chip-check input { display: inline-block; width: auto; margin: 0; }
    .toolbar { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin-top: 14px; }
    .toolbar select, .search select { width: auto; margin: 0; }
    .pager { display: flex; flex-wrap: wrap; justify-content: center; gap: 6px; margin-top: 22px; }
    .pager a { padding: 8px 14px; border-radius: 10px; background: #fff; border: 1px solid var(--line); color: var(--text); }
    .pager a.on { background: var(--green-dark); color: var(--lime); border-color: var(--green-dark); }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0 16px; }
    @media (max-width: 760px) { .form-grid { grid-template-columns: 1fr; } }
    th a { color: inherit; }
    .badge.actif { background: #d9f0e3; color: var(--green-dark); }
    .badge.inactif { background: #fdecec; color: var(--red); }
</style>
