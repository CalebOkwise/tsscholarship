<?php
require_once dirname(__DIR__) . '/includes/auth.php';
require_login();
$pdo = db();
$id = max(1, (int) ($_GET['id'] ?? $_POST['id'] ?? 0));
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newStatus = clean_string($_POST['status'] ?? '', 30);
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Invalid session token. Please try again.';
    } elseif (!is_valid_status($newStatus)) {
        $error = 'Invalid lead status.';
    } else {
        $stmt = $pdo->prepare('UPDATE leads SET status = :status WHERE id = :id');
        $stmt->execute(['status' => $newStatus, 'id' => $id]);
        redirect_to('lead-view.php?id=' . $id . '&updated=1');
    }
}

$stmt = $pdo->prepare('SELECT * FROM leads WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $id]);
$lead = $stmt->fetch();
if (!$lead) {
    http_response_code(404);
    echo 'Lead not found.';
    exit;
}
$fields = [
    'Full Name' => $lead['full_name'],
    'Email' => $lead['email'],
    'Phone' => $lead['phone'],
    'WhatsApp' => $lead['whatsapp'],
    'Gender' => $lead['gender'],
    'Age Range' => $lead['age_range'],
    'State' => $lead['state_of_residence'],
    'Qualification' => $lead['highest_qualification'],
    'Occupation' => $lead['current_occupation'],
    'Employment Status' => $lead['employment_status'],
    'Preferred Course' => $lead['preferred_course'],
    'Prior Tech Experience' => $lead['prior_tech_experience'],
    'Career Goals' => $lead['career_goals'],
    'Message' => $lead['message'],
    'Source' => $lead['source'],
    'IP Address' => $lead['ip_address'],
    'User Agent' => $lead['user_agent'],
    'Submitted' => $lead['created_at'],
];
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Lead Detail | TeamSource</title><link rel="stylesheet" href="admin.css"></head>
<body>
<header class="header"><div class="brand">TeamSource Admin</div><nav class="nav"><a href="dashboard.php">Dashboard</a><a href="leads.php">Leads</a><a href="logout.php">Logout</a></nav></header>
<main class="container">
  <p><a href="leads.php">Back to leads</a></p>
  <section class="card">
    <h1><?= e($lead['full_name']) ?></h1>
    <?php if (isset($_GET['updated'])): ?><p><strong>Status updated.</strong></p><?php endif; ?>
    <?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
    <form method="post">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="id" value="<?= e((string) $lead['id']) ?>">
      <label>Status
        <select name="status">
          <?php foreach (VALID_STATUSES as $option): ?><option value="<?= e($option) ?>" <?= $lead['status'] === $option ? 'selected' : '' ?>><?= e(ucfirst($option)) ?></option><?php endforeach; ?>
        </select>
      </label>
      <button type="submit">Update Status</button>
    </form>
  </section>
  <section class="card" style="margin-top:16px;">
    <h2>Lead Details</h2>
    <div class="detail-grid">
      <?php foreach ($fields as $label => $value): ?><div><?= e($label) ?></div><div><?= nl2br(e($value)) ?></div><?php endforeach; ?>
    </div>
  </section>
</main>
</body>
</html>
