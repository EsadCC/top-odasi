// =============================================
// The Dark House — app.js
// Bijhoudt voortgang, levens, win- en verliesscherm
// =============================================

const MAX_LIVES = 3;

let totalBoxes  = 0;
let solvedBoxes = 0;
let livesLeft   = MAX_LIVES;

// ---- Init ----
document.addEventListener('DOMContentLoaded', () => {
  totalBoxes = document.querySelectorAll('.box').length;
  updateProgress();
  renderLives();
});

// ---- Open modal ----
function openModal(index) {
  if (livesLeft <= 0) return; // geen levens meer, spel voorbij

  let box = document.querySelector(`.box[data-index='${index}']`);
  if (!box || box.classList.contains('solved')) return;

  // Vul modal
  document.getElementById('riddle').innerText = box.dataset.riddle;
  document.getElementById('modal').dataset.answer = box.dataset.answer;
  document.getElementById('modal').dataset.index  = index;

  // Hint
  let hintEl     = document.getElementById('hint-text');
  let hintToggle = document.getElementById('hint-toggle');
  let hintText   = box.dataset.hint || '';

  if (hintText) {
    hintEl.innerText = hintText;
    hintEl.style.display = 'none';
    hintToggle.style.display = 'inline-block';
    hintToggle.innerText = 'Toon hint';
  } else {
    hintEl.style.display = 'none';
    hintToggle.style.display = 'none';
  }

  // Reset
  document.getElementById('answer').value    = '';
  document.getElementById('feedback').innerText = '';
  document.getElementById('answer').style.borderColor = '';

  document.getElementById('overlay').style.display = 'block';
  document.getElementById('modal').style.display   = 'block';

  setTimeout(() => document.getElementById('answer').focus(), 80);
}

// ---- Hint toggle ----
function toggleHint() {
  let hintEl = document.getElementById('hint-text');
  let toggle = document.getElementById('hint-toggle');
  let hidden = hintEl.style.display === 'none' || hintEl.style.display === '';
  hintEl.style.display = hidden ? 'block' : 'none';
  toggle.innerText = hidden ? 'Verberg hint' : 'Toon hint';
}

// ---- Close modal ----
function closeModal() {
  document.getElementById('overlay').style.display = 'none';
  document.getElementById('modal').style.display   = 'none';
  document.getElementById('feedback').innerText    = '';
}

// ---- Check answer ----
function checkAnswer() {
  let userAnswer    = document.getElementById('answer').value.trim();
  let correctAnswer = document.getElementById('modal').dataset.answer;
  let index         = document.getElementById('modal').dataset.index;
  let feedback      = document.getElementById('feedback');
  let input         = document.getElementById('answer');

  if (!userAnswer) return;

  if (userAnswer.toLowerCase() === correctAnswer.toLowerCase()) {
    // ✅ Correct
    feedback.innerText   = '✓ Correct! Goed gedaan!';
    feedback.style.color = '#4caf50';

    let box = document.querySelector(`.box[data-index='${index}']`);
    if (box) {
      box.classList.add('solved');
      let icon = box.querySelector('.box-icon');
      if (icon) icon.innerText = '🔓';
    }

    solvedBoxes++;
    updateProgress();

    setTimeout(() => {
      closeModal();
      if (solvedBoxes >= totalBoxes) showWinScreen();
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
        showLoseScreen();
      }, 1000);
    } else {
      feedback.innerText   = `✗ Fout antwoord. Nog ${livesLeft} ${livesLeft === 1 ? 'leven' : 'levens'} over.`;
      feedback.style.color = '#cc2222';
    }
  }
}

// ---- Render lives (♥ icons) ----
function renderLives() {
  let el = document.getElementById('lives-display');
  if (!el) return;
  let full  = '♥'.repeat(livesLeft);
  let empty = '♡'.repeat(MAX_LIVES - livesLeft);
  el.innerHTML =
    `<span style="color:var(--red-light)">${full}</span>` +
    `<span style="color:var(--border)">${empty}</span>`;
}

// ---- Update progress bar ----
function updateProgress() {
  let bar   = document.getElementById('progress-fill');
  let label = document.getElementById('progress-label');
  if (!totalBoxes) return;
  let pct = (solvedBoxes / totalBoxes) * 100;
  if (bar)   bar.style.width = pct + '%';
  if (label) label.innerText = `${solvedBoxes} / ${totalBoxes} raadsels opgelost`;
}

// ---- Win screen ----
function showWinScreen() {
  document.querySelector('.container').style.display  = 'none';
  document.querySelector('.progress-wrap').style.display = 'none';
  let lives = document.querySelector('.lives-wrap');
  if (lives) lives.style.display = 'none';

  let win = document.getElementById('win-screen');
  if (win) win.style.display = 'block';
}

// ---- Lose screen ----
function showLoseScreen() {
  document.querySelector('.container').style.display  = 'none';
  document.querySelector('.progress-wrap').style.display = 'none';
  let lives = document.querySelector('.lives-wrap');
  if (lives) lives.style.display = 'none';

  let lose = document.getElementById('lose-screen');
  if (lose) lose.style.display = 'block';
}

// ---- Keyboard shortcuts ----
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') closeModal();
  if (e.key === 'Enter' && document.getElementById('modal').style.display === 'block') {
    checkAnswer();
  }
});
