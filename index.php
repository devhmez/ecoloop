<?php
require_once __DIR__ . '/includes/functions.php';
$u = current_user();
redirect(!$u ? 'frontoffice/index.php' : ($u['role'] === 'admin' ? 'backoffice/index.php' : 'frontoffice/recompenses.php'));
