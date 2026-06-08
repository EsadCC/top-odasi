<?php
session_start();
require_once('dbcon.php');
$db_connection = getDB();

if (isset($_SESSION['team_id'])) {
    header('Location: rooms/dashboard.php');
    exit;
}

$error = '';
$success = '';
$mode = $_POST['mode'] ?? 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teamName = trim($_POST['team_name'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($mode === 'register') {
        $members = array_filter(array_map('trim', $_POST['members'] ?? []), fn($m) => $m !== '');

        if ($teamName === '') {
            $error = 'Vul een teamnaam in.';
        } elseif (strlen($password) < 4) {
            $error = 'Wachtwoord moet minimaal 4 tekens zijn.';
        } elseif (count($members) < 2) {
            $error = 'Voeg minimaal twee teamleden toe.';
        } else {
            $check = $db_connection->prepare("SELECT id FROM teams WHERE team_name = :name");
            $check->execute([':name' => $teamName]);
            if ($check->fetch()) {
                $error = 'Deze teamnaam is al bezet. Kies een andere naam.';
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db_connection->prepare("INSERT INTO teams (team_name, password) VALUES (:name, :pw)");
                $stmt->execute([':name' => $teamName, ':pw' => $hashed]);
                $teamId = $db_connection->lastInsertId();

                $stmtM = $db_connection->prepare("INSERT INTO team_members (team_id, member_name) VALUES (:tid, :name)");
                foreach ($members as $member) {
                    $stmtM->execute([':tid' => $teamId, ':name' => $member]);
                }

                $_SESSION['team_id'] = $teamId;
                $_SESSION['team_name'] = $teamName;
                header('Location: rooms/dashboard.php');
                exit;
            }
        }
    } else {
        if ($teamName === '' || $password === '') {
            $error = 'Vul je teamnaam en wachtwoord in.';
        } else {
            $stmt = $db_connection->prepare("SELECT id, team_name, password FROM teams WHERE team_name = :name");
            $stmt->execute([':name' => $teamName]);
            $team = $stmt->fetch();

            if ($team && password_verify($password, $team['password'])) {
                $_SESSION['team_id'] = $team['id'];
                $_SESSION['team_name'] = $team['team_name'];
                header('Location: rooms/dashboard.php');
                exit;
            } else {
                $error = 'Teamnaam of wachtwoord klopt niet.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inloggen — The Dark House</title>
  <link rel="stylesheet" href="./css/style.css">
  <style>
    .auth-wrap { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; }
    .auth-card { background: var(--surface); border: 1px solid var(--red); border-radius: 6px; padding: 36px 32px; width: 100%; max-width: 440px; box-shadow: 0 0 40px rgba(139,0,0,0.2); }
    .auth-title { font-family: var(--font-head); color: var(--red-light); font-size: 2rem; letter-spacing: 3px; text-align: center; margin-bottom: 6px; }
    .auth-sub { text-align: center; color: var(--muted); font-size: 0.85rem; margin-bottom: 28px; }
    .tab-row { display: flex; gap: 0; margin-bottom: 28px; border: 1px solid var(--border); border-radius: 3px; overflow: hidden; }
    .tab-btn { flex: 1; padding: 10px; background: transparent; border: none; color: var(--muted); font-family: var(--font-body); font-size: 0.9rem; cursor: pointer; transition: background 0.2s, color 0.2s; letter-spacing: 1px; }
    .tab-btn.active { background: var(--red); color: #fff; }
    .tab-btn:hover:not(.active) { background: var(--surface2); color: var(--text); }
    .extra-members { display: none; }
    .back-home { display: block; text-align: center; margin-top: 18px; font-size: 0.82rem; color: var(--muted); }
    .back-home:hover { color: var(--red-light); }
  </style>
</head>
<body>
<div class="auth-wrap">
  <div class="auth-card">
    <h1 class="auth-title">The Dark House</h1>
    <p class="auth-sub">Log in of maak een team aan om te beginnen.</p>

    <div class="tab-row">
      <button class="tab-btn" id="tab-login" onclick="switchTab('login')">Inloggen</button>
      <button class="tab-btn" id="tab-register" onclick="switchTab('register')">Aanmelden</button>
    </div>

    <?php if ($error): ?>
      <div class="form-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="" id="auth-form">
      <input type="hidden" name="mode" id="form-mode" value="<?= htmlspecialchars($mode) ?>">

      <div class="form-group">
        <label for="team_name">Teamnaam</label>
        <input type="text" id="team_name" name="team_name" placeholder="Naam van jullie team" value="<?= htmlspecialchars($_POST['team_name'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label for="password">Wachtwoord</label>
        <input type="password" id="password" name="password" placeholder="Jullie wachtwoord">
      </div>

      <div id="register-fields" style="display:none">
        <div class="form-group">
          <label>Teamlid 1</label>
          <input type="text" name="members[]" placeholder="Naam teamlid 1" value="<?= htmlspecialchars($_POST['members'][0] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Teamlid 2</label>
          <input type="text" name="members[]" placeholder="Naam teamlid 2" value="<?= htmlspecialchars($_POST['members'][1] ?? '') ?>">
        </div>
        <div class="extra-members" id="extra-members">
          <div class="form-group">
            <label>Teamlid 3 <span style="color:var(--muted)">(optioneel)</span></label>
            <input type="text" name="members[]" placeholder="Naam teamlid 3" value="<?= htmlspecialchars($_POST['members'][2] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Teamlid 4 <span style="color:var(--muted)">(optioneel)</span></label>
            <input type="text" name="members[]" placeholder="Naam teamlid 4" value="<?= htmlspecialchars($_POST['members'][3] ?? '') ?>">
          </div>
        </div>
        <button type="button" class="btn btn-muted" id="more-btn" onclick="toggleMore()" style="margin-bottom:18px;font-size:0.8rem;padding:7px 14px">+ Meer leden</button>
      </div>

      <button type="submit" class="btn btn-solid" style="width:100%" id="submit-btn">Inloggen</button>
    </form>

    <a class="back-home" href="index.php">← Terug naar de ingang</a>
  </div>
</div>

<script>
const initialMode = "<?= htmlspecialchars($mode) ?>";

function switchTab(mode) {
  document.getElementById('form-mode').value = mode;
  document.getElementById('tab-login').classList.toggle('active', mode === 'login');
  document.getElementById('tab-register').classList.toggle('active', mode === 'register');
  document.getElementById('register-fields').style.display = mode === 'register' ? 'block' : 'none';
  document.getElementById('submit-btn').textContent = mode === 'register' ? 'Team aanmaken' : 'Inloggen';
}

function toggleMore() {
  const el = document.getElementById('extra-members');
  const btn = document.getElementById('more-btn');
  const hidden = el.style.display === 'none' || el.style.display === '';
  el.style.display = hidden ? 'block' : 'none';
  btn.textContent = hidden ? '− Minder leden' : '+ Meer leden';
}

switchTab(initialMode);
</script>
</body>
</html>
