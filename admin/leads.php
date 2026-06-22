<?php
require_once dirname(__DIR__) . '/includes/auth.php';
require_login();
$pdo = db();

$search = clean_string($_GET['q'] ?? '', 120);
$status = clean_string($_GET['status'] ?? '', 30);
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$where = [];
$params = [];
if ($search !== '') {
    $where[] = '(full_name LIKE :search OR email LIKE :search OR phone LIKE :search OR preferred_course LIKE :search)';
    $params['search'] = '%' . $search . '%';
}
if ($status !== '' && is_valid_status($status)) {
    $where[] = 'status = :status';
    $params['status'] = $status;
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM leads {$whereSql}");
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($total / $perPage));

$sql = "SELECT id, full_name, email, phone, preferred_course, source, status, created_at FROM leads {$whereSql} ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$leads = $stmt->fetchAll();

function page_url(int $page, string $search, string $status): string {
    return 'leads.php?' . http_build_query(['q' => $search, 'status' => $status, 'page' => $page]);
}
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Leads | TeamSource</title><link rel="stylesheet" href="admin.css"></head>
<body>
<header class="header"><div class="brand">TeamSource Admin</div><nav class="nav"><a href="dashboard.php">Dashboard</a><a href="leads.php">Leads</a><a href="logout.php">Logout</a></nav></header>
<main class="container">
  <h1>Leads</h1>
  <section class="card">
    <form class="filters" method="get">
      <input type="search" name="q" value="<?= e($search) ?>" placeholder="Search name, email, phone, course">
      <select name="status">
        <option value="">All statuses</option>
        <?php foreach (VALID_STATUSES as $option): ?><option value="<?= e($option) ?>" <?= $status === $option ? 'selected' : '' ?>><?= e(ucfirst($option)) ?></option><?php endforeach; ?>
      </select>
      <button type="submit">Filter</button>
    </form>
    <table>
      <thead><tr><th>Name</th><th>Contact</th><th>Course</th><th>Source</th><th>Status</th><th>Submitted</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($leads as $lead): ?>
        <tr>
          <td><?= e($lead['full_name']) ?></td>
          <td><?= e($lead['email']) ?><br><?= e($lead['phone']) ?></td>
          <td><?= e($lead['preferred_course']) ?></td>
          <td><?= e($lead['source']) ?></td>
          <td><span class="badge"><?= e($lead['status']) ?></span></td>
          <td><?= e($lead['created_at']) ?></td>
          <td><a href="lead-view.php?id=<?= e((string) $lead['id']) ?>">View</a></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$leads): ?><tr><td colspan="7">No leads found.</td></tr><?php endif; ?>
      </tbody>
    </table>
    <div class="pagination">
      <?php if ($page > 1): ?><a class="button" href="<?= e(page_url($page - 1, $search, $status)) ?>">Previous</a><?php endif; ?>
      <span>Page <?= e((string) $page) ?> of <?= e((string) $totalPages) ?></span>
      <?php if ($page < $totalPages): ?><a class="button" href="<?= e(page_url($page + 1, $search, $status)) ?>">Next</a><?php endif; ?>
    </div>
  </section>
</main>
</body>
</html>
