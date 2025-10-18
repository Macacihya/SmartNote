<?php
// inc/header.php
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>SmartNote - <?= isset($page_title) ? htmlspecialchars($page_title) : 'SmartNote' ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= (strpos($_SERVER['PHP_SELF'],'/notulen/') !== false) ? '../assets/css/style.css' : 'assets/css/style.css' ?>">
</head>
<body data-page="<?= isset($page_slug) ? $page_slug : '' ?>">
