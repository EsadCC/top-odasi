// The Dark House — app.js

let totalBoxes = 0;
let solvedBoxes = 0;
let currentIndex = null;

document.addEventListener('DOMContentLoaded', () => {
  totalBoxes = document.querySelectorAll('.box').length;
  updateProgress();
});

function openModal(index) {
  let box = document.querySelector(`.box[data-index='${index}']`);
  if (!box || box.classList.contains('solved')) return;

  currentIndex = index;

  let riddleText   = box.dataset.riddle;
  let correctAnswer = box.dataset.answer;
  let hintText     = box.dataset.hint || '';

  document.getElementById('riddle').innerText = riddleText;
  document.getElementById('modal').dataset.answer = correctAnswer;
  document.getElementById('modal').dataset.index  = index;

  let hintEl = document.getElementById('hint-text');
  let hintToggle = document.getElementById('hint-toggle');
  hintEl.style.display = 'none';
  hintToggle.innerText = 'Toon hint';
  if (hintText) {
    hintEl.innerText = hintText;
    hintToggle.style.display = 'inline-block';
  } else {
    hintToggle.style.display = 'none';
  }

  document.getElementById('answer').value = '';
  document.getElementById('feedback').innerText = '';

  let overlay = document.getElementById('overlay');
  let modal   = document.getElementById('modal');
  overlay.style.display = 'block';
  modal.style.display   = 'block';
  // Re-trigger animation
  modal.style.animation = 'none';
  modal.offsetHeight;
  modal.style.animation = '';

  setTimeout(() => document.getElementById('answer').focus(), 80);
}

function toggleHint() {
  let hintEl = document.getElementById('hint-text');
  let toggle = document.getElementById('hint-toggle');
  let hidden = hintEl.style.display === 'none' || !hintEl.style.display;
  hintEl.style.display = hidden ? 'block' : 'none';
  toggle.innerText = hidden ? 'Verberg hint' : 'Toon hint';
}

function closeModal() {
  document.getElementById('overlay').style.display = 'none';
  document.getElementById('modal').style.display   = 'none';
  document.getElementById('feedback').innerText    = '';
  currentIndex = null;
}

function checkAnswer() {
  let input   = document.getElementById('answer');
  let userAnswer    = input.value.trim();
  let correctAnswer = document.getElementById('modal').dataset.answer;
  let index   = document.getElementById('modal').dataset.index;
  let feedback = document.getElementById('feedback');

  if (!userAnswer) return;

  if (userAnswer.toLowerCase() === correctAnswer.toLowerCase()) {
    feedback.innerText  = '✓ Correct! Goed gedaan!';
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
    feedback.innerText  = '✗ Fout antwoord. Probeer opnieuw!';
    feedback.style.color = '#cc2222';

    // Shake animation
    input.classList.remove('shake');
    input.offsetHeight; // reflow
    input.classList.add('shake');
    input.style.borderColor = '#cc2222';
    setTimeout(() => {
      input.style.borderColor = '';
      input.classList.remove('shake');
    }, 500);
  }
}

document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') closeModal();
  if (e.key === 'Enter' && document.getElementById('modal').style.display === 'block') {
    checkAnswer();
  }
});

function updateProgress() {
  if (!totalBoxes) return;
  let pct   = (solvedBoxes / totalBoxes) * 100;
  let bar   = document.getElementById('progress-fill');
  let label = document.getElementById('progress-label');
  if (bar)   bar.style.width = pct + '%';
  if (label) label.innerText = `${solvedBoxes} / ${totalBoxes} opgelost`;
}

function showWinScreen() {
  let grid     = document.querySelector('.container');
  let win      = document.getElementById('win-screen');
  let progress = document.querySelector('.progress-wrap');
  if (grid)     grid.style.display = 'none';
  if (progress) progress.style.display = 'none';
  if (win)      win.style.display = 'block';
}