// =============================================
// The Dark House — app.js
// Antwoorden worden NOOIT in de HTML gezet.
// Verificatie gebeurt server-side via check_answer.php
// =============================================

const MAX_LIVES    = 3;
const ROOM_SECONDS = 5 * 60; // 5 minuten

let totalBoxes  = 0;
let solvedBoxes = 0;
let livesLeft   = MAX_LIVES;
let timeLeft    = ROOM_SECONDS;
let timerInterval = null;
let gameOver    = false;
let currentRiddleId = null; // database ID van het open raadsel

// =============================================
// INIT
// =============================================
document.addEventListener('DOMContentLoaded', () => {
  totalBoxes = document.querySelectorAll('.box').length;
  updateProgress();
  renderLives();
  startTimer();
});

// =============================================
// TIMER
// =============================================
function startTimer() {
  renderTimer();
  timerInterval = setInterval(() => {
    if (gameOver) return;
    timeLeft--;
    renderTimer();
    if (timeLeft <= 0) {
      clearInterval(timerInterval);
      timeLeft = 0;
      renderTimer();
      closeModal();
      triggerTimeLose();
    }
  }, 1000);
}

function renderTimer() {
  const el = document.getElementById('timer-display');
  if (!el) return;
  const mins = String(Math.floor(timeLeft / 60)).padStart(2, '0');
  const secs = String(timeLeft % 60).padStart(2, '0');
  el.textContent = `${mins}:${secs}`;

  if (timeLeft <= 30) {
    el.style.color     = 'var(--red-light)';
    el.style.animation = 'timerPulse 0.8s infinite';
  } else if (timeLeft <= 60) {
    el.style.color     = 'var(--yellow)';
    el.style.animation = '';
  } else {
    el.style.color     = 'var(--text)';
    el.style.animation = '';
  }
}

// =============================================
// LEVENS
// =============================================
function renderLives() {
  const el = document.getElementById('lives-display');
  if (!el) return;
  el.innerHTML =
    `<span style="color:var(--red-light)">${'♥'.repeat(livesLeft)}</span>` +
    `<span style="color:var(--border)">${'♡'.repeat(MAX_LIVES - livesLeft)}</span>`;
}

// =============================================
// MODAL
// =============================================
function openModal(index) {
  if (gameOver || livesLeft <= 0) return;

  const box = document.querySelector(`.box[data-index='${index}']`);
  if (!box || box.classList.contains('solved')) return;

  // Sla het database-ID op — antwoord staat NIET in de HTML
  currentRiddleId = box.dataset.id;

  document.getElementById('riddle').innerText             = box.dataset.riddle;
  document.getElementById('modal').dataset.index          = index;
  document.getElementById('answer').value                 = '';
  document.getElementById('feedback').innerText           = '';
  document.getElementById('answer').style.borderColor     = '';

  const hintEl     = document.getElementById('hint-text');
  const hintToggle = document.getElementById('hint-toggle');
  const hintText   = box.dataset.hint || '';

  if (hintText) {
    hintEl.innerText         = hintText;
    hintEl.style.display     = 'none';
    hintToggle.style.display = 'inline-block';
    hintToggle.innerText     = 'Toon hint';
  } else {
    hintEl.style.display     = 'none';
    hintToggle.style.display = 'none';
  }

  document.getElementById('overlay').style.display = 'block';
  document.getElementById('modal').style.display   = 'block';
  setTimeout(() => document.getElementById('answer').focus(), 80);
}

function toggleHint() {
  const hintEl = document.getElementById('hint-text');
  const toggle = document.getElementById('hint-toggle');
  const hidden = hintEl.style.display === 'none' || hintEl.style.display === '';
  hintEl.style.display = hidden ? 'block' : 'none';
  toggle.innerText     = hidden ? 'Verberg hint' : 'Toon hint';
}

function closeModal() {
  document.getElementById('overlay').style.display = 'none';
  document.getElementById('modal').style.display   = 'none';
  document.getElementById('feedback').innerText    = '';
  currentRiddleId = null;
}

// =============================================
// ANTWOORD CONTROLEREN — via server
// =============================================
function checkAnswer() {
  if (gameOver) return;

  const userAnswer = document.getElementById('answer').value.trim();
  const index      = document.getElementById('modal').dataset.index;
  const feedback   = document.getElementById('feedback');
  const input      = document.getElementById('answer');

  if (!userAnswer || !currentRiddleId) return;

  // Knop uitschakelen tijdens verzoek (voorkomt dubbel klikken)
  const btn = document.querySelector('.modal-actions .btn-solid');
  if (btn) btn.disabled = true;

  const formData = new FormData();
  formData.append('riddle_id', currentRiddleId);
  formData.append('answer',    userAnswer);

  fetch('check_answer.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
      if (btn) btn.disabled = false;

      if (!data.ok) {
        feedback.innerText   = 'Er ging iets mis. Probeer opnieuw.';
        feedback.style.color = 'var(--muted)';
        return;
      }

      if (data.correct) {
        // ✅ Correct
        feedback.innerText   = '✓ Correct! Goed gedaan!';
        feedback.style.color = '#4caf50';

        const box = document.querySelector(`.box[data-index='${index}']`);
        if (box) {
          box.classList.add('solved');
          const icon = box.querySelector('.box-icon');
          if (icon) icon.innerText = '🔓';
        }

        solvedBoxes++;
        updateProgress();

        setTimeout(() => {
          closeModal();
          if (solvedBoxes >= totalBoxes) triggerWin();
        }, 900);

      } else {
        // ❌ Fout
        livesLeft--;
        renderLives();

        input.style.borderColor = '#cc2222';
        setTimeout(() => { input.style.borderColor = ''; }, 600);

        if (livesLeft <= 0) {
          feedback.innerText   = '✗ Geen levens meer!';
          feedback.style.color = '#cc2222';
          setTimeout(() => {
            closeModal();
            triggerLivesLose();
          }, 1000);
        } else {
          feedback.innerText   = `✗ Fout antwoord. Nog ${livesLeft} ${livesLeft === 1 ? 'leven' : 'levens'} over.`;
          feedback.style.color = '#cc2222';
        }
      }
    })
    .catch(() => {
      if (btn) btn.disabled = false;
      feedback.innerText   = 'Verbindingsfout. Probeer opnieuw.';
      feedback.style.color = 'var(--muted)';
    });
}

// =============================================
// PROGRESS BAR
// =============================================
function updateProgress() {
  const bar   = document.getElementById('progress-fill');
  const label = document.getElementById('progress-label');
  if (!totalBoxes) return;
  if (bar)   bar.style.width = `${(solvedBoxes / totalBoxes) * 100}%`;
  if (label) label.innerText = `${solvedBoxes} / ${totalBoxes} raadsels opgelost`;
}

// =============================================
// GAME OVER
// =============================================
function hideGameUI() {
  gameOver = true;
  clearInterval(timerInterval);
  ['container', 'progress-wrap', 'lives-wrap', 'timer-wrap'].forEach(cls => {
    const el = document.querySelector('.' + cls);
    if (el) el.style.display = 'none';
  });
}

function triggerWin() {
  const roomId = parseInt(document.body.dataset.roomId || '0');

  const formData = new FormData();
  formData.append('room_id',    roomId);
  formData.append('time_left',  timeLeft);
  formData.append('lives_left', livesLeft);

  fetch('save_score.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
      const scoreEl = document.getElementById('win-score');
      if (scoreEl && data.score !== undefined) {
        scoreEl.innerText = `Score: ${data.score} punten`;
      }
    })
    .catch(() => {});

  hideGameUI();
  const win = document.getElementById('win-screen');
  if (win) win.style.display = 'block';
}

function triggerLivesLose() {
  hideGameUI();
  const el = document.getElementById('lose-screen-lives');
  if (el) el.style.display = 'block';
}

function triggerTimeLose() {
  hideGameUI();
  const el = document.getElementById('lose-screen-time');
  if (el) el.style.display = 'block';
}

// =============================================
// KEYBOARD
// =============================================
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') closeModal();
  if (e.key === 'Enter' && document.getElementById('modal').style.display === 'block') {
    checkAnswer();
  }
});
