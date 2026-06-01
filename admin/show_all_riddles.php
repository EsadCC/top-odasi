<?php
require_once('../dbcon.php');

try {
    $riddles = $db_connection->query(
        "SELECT r.*, ro.name AS room_name
         FROM riddles r
         LEFT JOIN rooms ro ON r.roomId = ro.id
         ORDER BY r.roomId ASC, r.id ASC"
    )->fetchAll();
} catch (PDOException $e) {
    die("Databasefout: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Alle raadsels — Admin</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="admin-page">

  <a class="back-link" href="../index.php">← Terug naar de ingang</a>

  <div class="admin-header">
    <h1>🔐 Alle raadsels</h1>
    <p>Admin — overzicht van alle raadsels per kamer.</p>
  </div>

  <nav class="admin-nav">
    <a class="btn btn-solid" href="add_riddle.php">+ Raadsel toevoegen</a>
    <a class="btn" href="../rooms/add_team.php">Team aanmaken</a>
    <a class="btn" href="../rooms/show_all_teams.php">Alle teams</a>
    <a class="btn" href="../rooms/show_all_reviews.php">Alle reviews</a>
  </nav>

  <div class="table-wrap">
    <?php if (empty($riddles)): ?>
      <div class="empty-state">Nog geen raadsels in de database.</div>
    <?php else: ?>
      <table class="data-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Kamer</th>
            <th>Raadsel</th>
            <th>Antwoord</th>
            <th>Hint</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($riddles as $r): ?>
          <tr>
            <td><?= $r['id'] ?></td>
            <td><span class="badge"><?= htmlspecialchars($r['room_name'] ?? 'Kamer ' . $r['roomId']) ?></span></td>
            <td style="max-width:280px"><?= htmlspecialchars($r['riddle']) ?></td>
            <td style="color:var(--yellow)"><?= htmlspecialchars($r['answer']) ?></td>
            <td style="color:var(--muted);font-size:0.83rem">
              <?= $r['hint'] ? htmlspecialchars($r['hint']) : '—' ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

</div>
</body>
</html>
