<?php
require_once('../dbcon.php');

$reviews = $db_connection->query(
    "SELECT r.*, ro.name AS room_name FROM reviews r LEFT JOIN rooms ro ON r.room_id = ro.id ORDER BY r.created_at DESC"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Alle reviews — The Dark House</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="admin-page">

  <a class="back-link" href="../index.php">← Terug naar de ingang</a>

  <div class="admin-header">
    <h1>★ Alle reviews</h1>
    <p>Overzicht van alle beoordelingen.</p>
  </div>

  <nav class="admin-nav">
    <a class="btn btn-solid" href="add_review.php">+ Nieuwe review</a>
    <a class="btn" href="show_all_teams.php">Alle teams</a>
    <a class="btn" href="../admin/show_all_riddles.php">Alle raadsels</a>
  </nav>

  <div class="table-wrap">
    <?php if (empty($reviews)): ?>
      <div class="empty-state">Nog geen reviews geplaatst.</div>
    <?php else: ?>
      <table class="data-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Team</th>
            <th>Kamer</th>
            <th>Beoordeling</th>
            <th>Moeilijkheid</th>
            <th>Feedback</th>
            <th>Datum</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($reviews as $rev): ?>
          <tr>
            <td><?= $rev['id'] ?></td>
            <td><?= htmlspecialchars($rev['team_name']) ?></td>
            <td><span class="badge"><?= htmlspecialchars($rev['room_name'] ?? 'Onbekend') ?></span></td>
            <td class="stars"><?= str_repeat('★', $rev['rating']) . str_repeat('☆', 5 - $rev['rating']) ?></td>
            <td><?= $rev['difficulty'] ?>/5</td>
            <td style="max-width:220px;color:var(--muted);font-size:0.85rem">
              <?= $rev['feedback'] ? htmlspecialchars($rev['feedback']) : '—' ?>
            </td>
            <td style="color:var(--muted)"><?= date('d-m-Y', strtotime($rev['created_at'])) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

</div>
</body>
</html>
