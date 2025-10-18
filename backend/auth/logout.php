<?php
require_once __DIR__ . '/../config.php';
session_unset(); session_destroy();

// PERBAIKAN: Path diubah dari ../ menjadi ../../
header('Location: ../../login.php'); 
exit;
?>