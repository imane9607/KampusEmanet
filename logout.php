<?php
require_once 'includes/auth.php';
Auth::logout();
header('Location: index.php');
exit;
?>