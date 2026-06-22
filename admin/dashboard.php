<?php
require_once dirname(__DIR__) . '/includes/auth.php';
require_login();
$pdo = db();
$total = (int) $pdo->query('SELECT COUNT(*) FROM leads')->fetchColumn();
$new = (int) $pdo->query("SELECT COUNT(*) FROM leads WHERE status = 'new'")->fetchColumn();
$qualified = (int) $pdo->query("SELECT COUNT(*) FROM leads WHERE status = 'qualified'")->fetchColumn();
$converted = (int) $pdo->query("SELECT COUNT(*) FROM leads WHERE status = 'converted'")->fetchColumn();
$latest = $pdo->query('SELECT id, full_name, email, phone, preferred_course, status, created_at FROM leads ORDER BY created_at DESC LIMIT 10')->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard | TeamSource</title><link rel="stylesheet" href="admin.css">
</head>
<body>
<header class="header"><div class="brand">TeamSource Admin</div><nav class="nav"><a href="dashboard.php">Dashboard</a><a href="leads.php">Leads</a><a href="logout.php">Logout</a></nav></header>
<main class="container">
  <h1>Dashboard</h1>
  <div class="stats">
    <div class="card stat"><span>Total Leads</span><strong><?= e((string) $total) ?></strong></div>
    <div class="card stat"><span>New Leads</span><strong><?= e((string) $new) ?></strong></div>
    <div class="card stat"><span>Qualified</span><strong><?= e((string) $qualified) ?></strong></div>
    <div class="card stat"><span>Converted</span><strong><?= e((string) $converted) ?></strong></div>
  </div>
  <section class="card">
    <h2>Latest Applications</h2>
    <table>
      <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Course</th><th>Status</th><th>Submitted</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($latest as $lead): ?>
        <tr><td><?= e($lead['full_name']) ?></td><td><?= e($lead['email']) ?></td><td><?= e($lead['phone']) ?></td><td><?= e($lead['preferred_course']) ?></td><td><span class="badge"><?= e($lead['status']) ?></span></td><td><?= e($lead['created_at']) ?></td><td><a href="lead-view.php?id=<?= e((string) $lead['id']) ?>">View</a></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </section>
</main>
</body>
</html>
