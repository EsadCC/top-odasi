<?php
session_start();
require_once('../dbcon.php');
$db_connection = getDB();

if (!isset($_SESSION['team_id'])) {
    header('Location: ../login.php');
    exit;
}

$teamId   = $_SESSION['team_id'];
$teamName = $_SESSION['team_name'];

$team = $db_connection->prepare("SELECT * FROM teams WHERE id = :id");
$team->execute([':id' => $teamId]);
$teamData = $team->fetch();

$membersStmt = $db_connection->prepare("SELECT member_name FROM team_members WHERE team_id = :id");
$membersStmt->execute([':id' => $teamId]);
$members = $membersStmt->fetchAll(PDO::FETCH_COLUMN);

$addMemberError   = '';
$addMemberSuccess = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_member'])) {
    $newMember = trim($_POST['new_member']);
    if ($newMember === '') {
        $addMemberError = 'Vul een naam in.';
    } else {
        $ins = $db_connection->prepare("INSERT INTO team_members (team_id, member_name) VALUES (:tid, :name)");
        $ins->execute([':tid' => $teamId, ':name' => $newMember]);
        $addMemberSuccess = htmlspecialchars($newMember) . ' is toegevoegd!';
        $membersStmt->execute([':id' => $teamId]);
        $members = $membersStmt->fetchAll(PDO::FETCH_COLUMN);
    }
}

$reviews = $db_connection->query(
    "SELECT r.*, ro.name AS room_name
     FROM reviews r
     LEFT JOIN rooms ro ON r.room_id = ro.id
     ORDER BY r.created_at DESC LIMIT 20"
)->fetchAll();

$allTeams = $db_connection->query(
    "SELECT t.id, t.team_name, t.created_at,
     (SELECT COUNT(*) FROM team_members WHERE team_id = t.id) AS member_count
     FROM teams t ORDER BY t.created_at ASC"
)->fetchAll();

// Scoreboard: beste score per team per kamer
$scoresByRoom = [];
try {
    $scoreRows = $db_connection->query(
        "SELECT s.team_name, s.room_id, ro.name AS room_name,
                MAX(s.score) AS best_score,
                MAX(s.time_left) AS best_time,
                MAX(s.lives_left) AS best_lives
         FROM scores s
         LEFT JOIN rooms ro ON s.room_id = ro.id
         GROUP BY s.team_name, s.room_id
         ORDER BY s.room_id ASC, best_score DESC"
    )->fetchAll();
    foreach ($scoreRows as $row) {
        $scoresByRoom[$row['room_id']][] = $row;
    }
} catch (PDOException $e) {
    // scores tabel bestaat nog niet — geen probleem, scoreboard blijft leeg
    $scoresByRoom = [];
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — The Dark House</title>
  <link rel="stylesheet" href="../css/style.css">
  <style>
    .dash-wrap { max-width: 960px; margin: 0 auto; padding: 36px 20px 80px; }
    .dash-top { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 32px; border-bottom: 1px solid var(--border); padding-bottom: 20px; }
    .dash-top h1 { font-family: var(--font-head); color: var(--red-light); font-size: clamp(1.6rem,4vw,2.2rem); letter-spacing: 2px; }
    .dash-top p { color: var(--muted); font-size: 0.85rem; margin-top: 4px; }
    .tab-nav { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 28px; }
    .tab-nav button { padding: 9px 18px; background: var(--surface); border: 1px solid var(--border); color: var(--muted); font-family: var(--font-body); font-size: 0.85rem; border-radius: 3px; cursor: pointer; transition: all 0.2s; letter-spacing: 1px; }
    .tab-nav button.active, .tab-nav button:hover { border-color: var(--red); color: var(--text); background: rgba(139,0,0,0.12); }
    .tab-panel { display: none; }
    .tab-panel.active { display: block; }
    .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 14px; margin-bottom: 28px; }
    .info-card { background: var(--surface); border: 1px solid var(--border); border-radius: 6px; padding: 18px 20px; }
    .info-card .label { font-size: 0.72rem; color: var(--muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px; }
    .info-card .value { font-family: var(--font-head); font-size: 1.6rem; color: var(--yellow); letter-spacing: 1px; }
    .member-list { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 20px; }
    .member-pill { background: rgba(139,0,0,0.18); border: 1px solid rgba(139,0,0,0.35); color: var(--text); padding: 5px 12px; border-radius: 20px; font-size: 0.85rem; }
    .add-member-row { display: flex; gap: 10px; align-items: flex-start; }
    .add-member-row input { flex: 1; padding: 9px 12px; background: #1a1a1a; border: 1px solid var(--border); color: var(--text); font-family: var(--font-body); font-size: 0.9rem; border-radius: 3px; outline: none; }
    .add-member-row input:focus { border-color: var(--red); }
    .section-title { font-family: var(--font-head); color: var(--yellow); font-size: 1.1rem; letter-spacing: 1px; margin-bottom: 16px; }
    .room-section { margin-bottom: 36px; }
    .room-section-title { font-family: var(--font-head); color: var(--red-light); font-size: 1rem; letter-spacing: 2px; margin-bottom: 12px; padding-bottom: 6px; border-bottom: 1px solid var(--border); }
    .scoreboard-item { display: flex; align-items: center; gap: 14px; padding: 12px 16px; background: var(--surface); border: 1px solid var(--border); border-radius: 4px; margin-bottom: 8px; }
    .scoreboard-item.highlight { border-color: var(--red); background: rgba(139,0,0,0.1); }
    .rank { font-family: var(--font-head); font-size: 1.3rem; color: var(--muted); width: 28px; text-align: center; }
    .rank.gold   { color: #ffd700; }
    .rank.silver { color: #c0c0c0; }
    .rank.bronze { color: #cd7f32; }
    .score-info { flex: 1; }
    .score-name    { font-size: 0.95rem; }
    .score-details { font-size: 0.75rem; color: var(--muted); margin-top: 2px; }
    .score-pts { font-family: var(--font-head); font-size: 1.2rem; color: var(--yellow); letter-spacing: 1px; }
    .rooms-nav-dash { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 20px; }
  </style>
</head>
<body>
<div class="dash-wrap">

  <div class="dash-top">
    <div>
      <h1>👤 <?= htmlspecialchars($teamName) ?></h1>
      <p>Welkom terug in The Dark House</p>
    </div>
    <a class="btn btn-muted" href="logout.php">Uitloggen</a>
  </div>

  <div class="tab-nav">
    <button class="active" onclick="showTab('team', this)">👥 Mijn Team</button>
    <button onclick="showTab('scoreboard', this)">🏆 Scoreboard</button>
    <button onclick="showTab('reviews', this)">★ Reviews</button>
    <button onclick="showTab('spelen', this)">🎮 Spelen</button>
  </div>

  <!-- TAB: TEAM -->
  <div id="tab-team" class="tab-panel active">
    <div class="info-grid">
      <div class="info-card">
        <div class="label">Teamnaam</div>
        <div class="value" style="font-size:1.1rem;margin-top:4px"><?= htmlspecialchars($teamName) ?></div>
      </div>
      <div class="info-card">
        <div class="label">Teamleden</div>
        <div class="value"><?= count($members) ?></div>
      </div>
      <div class="info-card">
        <div class="label">Lid sinds</div>
        <div class="value" style="font-size:1rem;margin-top:4px"><?= date('d-m-Y', strtotime($teamData['created_at'])) ?></div>
      </div>
    </div>

    <p class="section-title">Teamleden</p>
    <div class="member-list">
      <?php foreach ($members as $m): ?>
        <span class="member-pill"><?= htmlspecialchars($m) ?></span>
      <?php endforeach; ?>
      <?php if (empty($members)): ?>
        <span style="color:var(--muted);font-size:0.85rem">Nog geen leden.</span>
      <?php endif; ?>
    </div>

    <p class="section-title" style="margin-top:24px">Teamlid toevoegen</p>
    <?php if ($addMemberSuccess): ?>
      <div class="form-success" style="margin-bottom:12px"><?= $addMemberSuccess ?></div>
    <?php endif; ?>
    <?php if ($addMemberError): ?>
      <div class="form-error" style="margin-bottom:12px"><?= htmlspecialchars($addMemberError) ?></div>
    <?php endif; ?>
    <form method="POST" action="">
      <div class="add-member-row">
        <input type="text" name="new_member" placeholder="Naam van nieuw teamlid">
        <button type="submit" class="btn btn-solid">Toevoegen</button>
      </div>
    </form>
  </div>

  <!-- TAB: SCOREBOARD -->
  <div id="tab-scoreboard" class="tab-panel">
    <p class="section-title">🏆 Scoreboard per kamer</p>
    <p style="color:var(--muted);font-size:0.82rem;margin-bottom:24px">
      Score = seconden over bij winst + (levens over × 20 punten).
    </p>

    <?php if (empty($scoresByRoom)): ?>
      <div class="empty-state">Nog geen scores. Speel een kamer om op het scoreboard te komen!</div>
    <?php else: ?>
      <?php
      $roomLabels = [
        1 => '🕯 Kamer 1 — De Verlaten Kelder',
        2 => '🩸 Kamer 2 — De Operatiekamer',
        3 => '💀 Kamer 3 — Het Kerkhof',
      ];
      foreach ($roomLabels as $rid => $label):
        if (empty($scoresByRoom[$rid])) continue;
      ?>
      <div class="room-section">
        <p class="room-section-title"><?= $label ?></p>
        <?php foreach ($scoresByRoom[$rid] as $i => $s): ?>
          <div class="scoreboard-item <?= strtolower($s['team_name']) === strtolower($teamName) ? 'highlight' : '' ?>">
            <div class="rank <?= $i === 0 ? 'gold' : ($i === 1 ? 'silver' : ($i === 2 ? 'bronze' : '')) ?>">
              <?= $i + 1 ?>
            </div>
            <div class="score-info">
              <div class="score-name">
                <?= htmlspecialchars($s['team_name']) ?>
                <?php if (strtolower($s['team_name']) === strtolower($teamName)): ?>
                  <span style="color:var(--yellow);font-size:0.72rem">(jij)</span>
                <?php endif; ?>
              </div>
              <div class="score-details">
                ⏱ <?= gmdate('i:s', $s['best_time']) ?> over &nbsp;|&nbsp;
                ♥ <?= $s['best_lives'] ?> leven<?= $s['best_lives'] != 1 ? 's' : '' ?> over
              </div>
            </div>
            <div class="score-pts"><?= $s['best_score'] ?> pts</div>
          </div>
        <?php endforeach; ?>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- TAB: REVIEWS -->
  <div id="tab-reviews" class="tab-panel">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px">
      <p class="section-title" style="margin:0">Laatste reviews</p>
      <a class="btn btn-solid" href="add_review.php" style="font-size:0.82rem;padding:8px 16px">+ Review toevoegen</a>
    </div>
    <div class="table-wrap">
      <?php if (empty($reviews)): ?>
        <div class="empty-state">Nog geen reviews.</div>
      <?php else: ?>
        <table class="data-table">
          <thead>
            <tr>
              <th>Team</th><th>Kamer</th><th>Beoordeling</th><th>Moeilijkheid</th><th>Feedback</th><th>Datum</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($reviews as $rev): ?>
            <tr>
              <td><?= htmlspecialchars($rev['team_name']) ?></td>
              <td><span class="badge"><?= htmlspecialchars($rev['room_name'] ?? 'Onbekend') ?></span></td>
              <td class="stars"><?= str_repeat('★', $rev['rating']) . str_repeat('☆', 5 - $rev['rating']) ?></td>
              <td><?= $rev['difficulty'] ?>/5</td>
              <td style="max-width:200px;color:var(--muted);font-size:0.83rem"><?= $rev['feedback'] ? htmlspecialchars($rev['feedback']) : '—' ?></td>
              <td style="color:var(--muted)"><?= date('d-m-Y', strtotime($rev['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>

  <!-- TAB: SPELEN -->
  <div id="tab-spelen" class="tab-panel">
    <p class="section-title">Kies een kamer</p>
    <p style="color:var(--muted);font-size:0.9rem;margin-bottom:20px">
      Je speelt als team: <span style="color:var(--yellow)"><?= htmlspecialchars($teamName) ?></span>
    </p>
    <div class="rooms-nav-dash">
      <a class="btn" href="room_1.php">🕯 De Verlaten Kelder</a>
      <a class="btn" href="room_2.php">🩸 De Operatiekamer</a>
      <a class="btn" href="room_3.php">💀 Het Kerkhof</a>
    </div>
  </div>

</div>

<script>
function showTab(name, btn) {
  document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.tab-nav button').forEach(b => b.classList.remove('active'));
  document.getElementById('tab-' + name).classList.add('active');
  btn.classList.add('active');
}
</script>
</body>
</html>
