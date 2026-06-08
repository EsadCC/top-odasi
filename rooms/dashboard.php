<?php
session_start();
require_once('../dbcon.php');

if (!isset($_SESSION['team_id'])) {
    header('Location: ../login.php');
    exit;
}

$teamId = $_SESSION['team_id'];
$teamName = $_SESSION['team_name'];

$team = $db_connection->prepare("SELECT * FROM teams WHERE id = :id");
$team->execute([':id' => $teamId]);
$teamData = $team->fetch();

$membersStmt = $db_connection->prepare("SELECT member_name FROM team_members WHERE team_id = :id");
$membersStmt->execute([':id' => $teamId]);
$members = $membersStmt->fetchAll(PDO::FETCH_COLUMN);

$addMemberError = '';
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
    "SELECT r.*, ro.name AS room_name FROM reviews r LEFT JOIN rooms ro ON r.room_id = ro.id ORDER BY r.created_at DESC LIMIT 20"
)->fetchAll();

$allTeams = $db_connection->query(
    "SELECT t.id, t.team_name, t.created_at,
     (SELECT COUNT(*) FROM team_members WHERE team_id = t.id) AS member_count
     FROM teams t ORDER BY t.created_at ASC"
)->fetchAll();
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
    .scoreboard-item { display: flex; align-items: center; gap: 14px; padding: 12px 16px; background: var(--surface); border: 1px solid var(--border); border-radius: 4px; margin-bottom: 8px; }
    .scoreboard-item.highlight { border-color: var(--red); background: rgba(139,0,0,0.1); }
    .rank { font-family: var(--font-head); font-size: 1.3rem; color: var(--muted); width: 28px; text-align: center; }
    .rank.gold { color: #ffd700; }
    .rank.silver { color: #c0c0c0; }
    .rank.bronze { color: #cd7f32; }
    .score-name { flex: 1; font-size: 0.95rem; }
    .score-members { font-size: 0.78rem; color: var(--muted); }
    .score-date { font-size: 0.75rem; color: var(--muted); }
    .play-btns { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 8px; }
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
    <div style="display:flex;gap:10px;flex-wrap:wrap">
      <a class="btn btn-muted" href="logout.php">Uitloggen</a>
    </div>
  </div>

  <div class="tab-nav">
    <button class="active" onclick="showTab('team')">👥 Mijn Team</button>
    <button onclick="showTab('scoreboard')">🏆 Scoreboard</button>
    <button onclick="showTab('reviews')">★ Reviews</button>
    <button onclick="showTab('spelen')">🎮 Spelen</button>
  </div>

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

  <div id="tab-scoreboard" class="tab-panel">
    <p class="section-title">Geregistreerde teams</p>
    <?php foreach ($allTeams as $i => $t): ?>
      <div class="scoreboard-item <?= $t['id'] == $teamId ? 'highlight' : '' ?>">
        <div class="rank <?= $i === 0 ? 'gold' : ($i === 1 ? 'silver' : ($i === 2 ? 'bronze' : '')) ?>"><?= $i + 1 ?></div>
        <div>
          <div class="score-name"><?= htmlspecialchars($t['team_name']) ?> <?= $t['id'] == $teamId ? '<span style="color:var(--yellow);font-size:0.75rem">(jij)</span>' : '' ?></div>
          <div class="score-members"><?= $t['member_count'] ?> <?= $t['member_count'] == 1 ? 'lid' : 'leden' ?></div>
        </div>
        <div class="score-date"><?= date('d-m-Y', strtotime($t['created_at'])) ?></div>
      </div>
    <?php endforeach; ?>
    <?php if (empty($allTeams)): ?>
      <div class="empty-state">Nog geen teams.</div>
    <?php endif; ?>
  </div>

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

  <div id="tab-spelen" class="tab-panel">
    <p class="section-title">Kies een kamer</p>
    <p style="color:var(--muted);font-size:0.9rem;margin-bottom:20px">Je speelt als team: <span style="color:var(--yellow)"><?= htmlspecialchars($teamName) ?></span></p>
    <div class="rooms-nav-dash">
      <a class="btn" href="room_1.php">🕯 De Verlaten Kelder</a>
      <a class="btn" href="room_2.php">🩸 De Operatiekamer</a>
      <a class="btn" href="room_3.php">💀 Het Kerkhof</a>
    </div>
  </div>

</div>

<script>
function showTab(name) {
  document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.tab-nav button').forEach(b => b.classList.remove('active'));
  document.getElementById('tab-' + name).classList.add('active');
  event.target.classList.add('active');
}
</script>
</body>
</html>
