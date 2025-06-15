<?php
require_once 'includes/auth.php';
require_once 'includes/items.php';
Auth::requireLogin();
$id = (int)($_GET['id'] ?? 0);
Items::delete($id, Auth::user()['id']);
header('Location: dashboard.php');
exit;
?>
