<?php
/**
 * Secret Notes – Caesar & Vigenère encrypt/decrypt in vanilla PHP.
 * Run:  php -S 0.0.0.0:8000   then open http://localhost:8000
 * On a phone (same Wi-Fi): http://<your-computer-ip>:8000
 */

// ---------- Cipher functions ----------

function caesar(string $text, int $shift, bool $decrypt = false): string
{
    $shift = (($shift % 26) + 26) % 26;
    if ($decrypt) {
        $shift = (26 - $shift) % 26;
    }
    $out = '';
    foreach (str_split($text) as $ch) {
        $o = ord($ch);
        if ($o >= 65 && $o <= 90) {
            $out .= chr((($o - 65 + $shift) % 26) + 65);
        } elseif ($o >= 97 && $o <= 122) {
            $out .= chr((($o - 97 + $shift) % 26) + 97);
        } else {
            $out .= $ch;
        }
    }
    return $out;
}

function vigenere(string $text, string $key, bool $decrypt = false): string
{
    $key = strtolower(preg_replace('/[^A-Za-z]/', '', $key));
    $len = strlen($key);
    $out = '';
    $k = 0;
    foreach (str_split($text) as $ch) {
        $o = ord($ch);
        $isUpper = $o >= 65 && $o <= 90;
        $isLower = $o >= 97 && $o <= 122;
        if (!$isUpper && !$isLower) {
            $out .= $ch;
            continue;
        }
        $shift = ord($key[$k % $len]) - 97;
        if ($decrypt) {
            $shift = 26 - $shift;
        }
        $base = $isUpper ? 65 : 97;
        $out .= chr((($o - $base + $shift) % 26) + $base);
        $k++;
    }
    return $out;
}

// ---------- Handle form ----------

$cipher = $_POST['cipher'] ?? 'caesar';
$mode   = $_POST['mode'] ?? 'encrypt';
$text   = $_POST['text'] ?? '';
$shift  = $_POST['shift'] ?? '3';
$key    = $_POST['key'] ?? '';
$result = null;
$error  = null;

if (!in_array($cipher, ['caesar', 'vigenere'], true)) $cipher = 'caesar';
if (!in_array($mode, ['encrypt', 'decrypt'], true))   $mode = 'encrypt';
if (strlen($text) > 20000) $text = substr($text, 0, 20000);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $decrypt = $mode === 'decrypt';

    if (trim($text) === '') {
        $error = 'Type or paste some text first.';
    } elseif ($cipher === 'caesar') {
        if (filter_var($shift, FILTER_VALIDATE_INT) === false) {
            $error = 'Shift must be a whole number, like 3 or -5.';
        } else {
            $result = caesar($text, (int)$shift, $decrypt);
        }
    } else {
        if (preg_replace('/[^A-Za-z]/', '', $key) === '') {
            $error = 'The Vigenère key needs at least one letter (A–Z).';
        } else {
            $result = vigenere($text, $key, $decrypt);
        }
    }
}

function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#f7c6e0">
<title>Secret Notes · Caesar &amp; Vigenère</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Quicksand:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root {
  --ink: #5a2a4a;
  --ink-soft: #85577a;
  --rose: #ff7eb6;
  --rose-deep: #d94a92;
  --lilac: #b79cff;
  --peach: #ffc2a8;
  --mint: #a8f0e0;
  --glass: rgba(255, 255, 255, 0.30);
  --glass-strong: rgba(255, 255, 255, 0.55);
  --glass-edge: rgba(255, 255, 255, 0.7);
  --tap: 48px; /* minimum comfortable touch target */
}
* { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
html { -webkit-text-size-adjust: 100%; }
html, body { margin: 0; }
body {
  font-family: "Quicksand", "Segoe UI", system-ui, sans-serif;
  color: var(--ink);
  background: linear-gradient(135deg, #ffd6e8 0%, #e6d4ff 50%, #ffe3d1 100%) fixed;
  min-height: 100vh;
  min-height: 100dvh;
  padding: max(24px, env(safe-area-inset-top)) max(14px, env(safe-area-inset-right))
           max(40px, env(safe-area-inset-bottom)) max(14px, env(safe-area-inset-left));
  overflow-x: hidden;
  position: relative;
}

/* blurry orbs behind the glass */
.orb { position: fixed; border-radius: 50%; filter: blur(50px); opacity: .75; z-index: 0; pointer-events: none; }
.orb.a { width: 340px; height: 340px; background: var(--rose);  top: -80px;  left: -80px; }
.orb.b { width: 300px; height: 300px; background: var(--lilac); bottom: -60px; right: -60px; }
.orb.c { width: 220px; height: 220px; background: var(--mint);  top: 45%; left: 62%; opacity: .55; }
.orb.d { width: 200px; height: 200px; background: var(--peach); bottom: 15%; left: 5%; opacity: .6; }

main { position: relative; z-index: 1; width: 100%; max-width: 680px; margin: 0 auto; }

h1 {
  font-family: "Pacifico", cursive, sans-serif;
  font-weight: 400;
  font-size: clamp(1.9rem, 8vw, 3rem);
  line-height: 1.2;
  text-align: center;
  margin: 0 0 6px;
  color: var(--rose-deep);
  text-shadow: 0 3px 0 rgba(255,255,255,.7), 0 8px 24px rgba(217,74,146,.35);
}
.sub { text-align: center; margin: 0 auto 22px; max-width: 34ch; color: var(--ink-soft); font-weight: 500; line-height: 1.45; }

.glass {
  background: var(--glass);
  -webkit-backdrop-filter: blur(22px) saturate(160%);
  backdrop-filter: blur(22px) saturate(160%);
  border: 1.5px solid var(--glass-edge);
  border-radius: 28px;
  box-shadow: 0 12px 40px rgba(183, 90, 150, .25), inset 0 1px 0 rgba(255,255,255,.8);
  padding: clamp(18px, 5vw, 28px);
}
.glass + .glass { margin-top: 16px; }
/* fallback if blur isn't supported */
@supports not ((backdrop-filter: blur(1px)) or (-webkit-backdrop-filter: blur(1px))) {
  .glass { background: rgba(255,255,255,.7); }
}

label, .label { display: block; font-weight: 700; font-size: 1rem; margin: 0 0 8px; }
.field { margin-bottom: 18px; }
.row { display: flex; justify-content: space-between; align-items: center; gap: 10px; margin-bottom: 8px; }
.row .label, .row label { margin: 0; }

/* segmented pill toggles */
.seg { display: flex; gap: 6px; padding: 5px; background: var(--glass); border: 1.5px solid var(--glass-edge); border-radius: 999px; }
.seg input { position: absolute; opacity: 0; pointer-events: none; }
.seg label {
  flex: 1; margin: 0; min-height: 44px; display: flex; align-items: center; justify-content: center;
  text-align: center; padding: 8px 10px; border-radius: 999px; cursor: pointer;
  font-weight: 700; color: var(--ink-soft); transition: background .2s, color .2s, box-shadow .2s;
  user-select: none; -webkit-user-select: none;
}
.seg input:checked + label {
  background: linear-gradient(135deg, var(--rose), var(--lilac));
  color: #fff; box-shadow: 0 6px 16px rgba(217,74,146,.4);
}
.seg input:focus-visible + label { outline: 3px solid var(--rose-deep); outline-offset: 2px; }

/* inputs – 16px font prevents iOS zoom-on-focus */
textarea, input[type="text"], input[type="number"] {
  width: 100%; font: inherit; font-size: 16px; font-weight: 500; color: var(--ink);
  background: var(--glass-strong); border: 1.5px solid var(--glass-edge);
  border-radius: 18px; padding: 14px 16px; min-height: var(--tap); outline: none;
  transition: box-shadow .2s, border-color .2s; -webkit-appearance: none; appearance: none;
}
textarea { min-height: 140px; resize: vertical; line-height: 1.5; }
textarea::placeholder, input::placeholder { color: rgba(133, 87, 122, .65); }
textarea:focus, input:focus { border-color: var(--rose); box-shadow: 0 0 0 4px rgba(255,126,182,.3); }
input[type="number"] {
  -moz-appearance: textfield;
  appearance: textfield;
  text-align: center;
  font-weight: 700;
}
input[type="number"]::-webkit-outer-spin-button, input[type="number"]::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }

.hint { font-size: .88rem; color: var(--ink-soft); margin: 8px 4px 0; line-height: 1.4; }
.meta { font-size: .85rem; color: var(--ink-soft); font-weight: 600; }
.hidden { display: none !important; }

/* buttons */
button {
  font: inherit; font-weight: 700; cursor: pointer; border: 0; border-radius: 999px;
  min-height: var(--tap); padding: 0 22px; color: var(--ink);
  transition: transform .15s, box-shadow .15s, background .15s;
  touch-action: manipulation;
}
button:focus-visible, summary:focus-visible { outline: 3px solid var(--rose-deep); outline-offset: 3px; }
button:disabled { opacity: .7; cursor: progress; }
.go {
  width: 100%; font-size: 1.05rem; color: #fff; min-height: 54px;
  background: linear-gradient(135deg, var(--rose) 0%, var(--rose-deep) 50%, var(--lilac) 100%);
  box-shadow: 0 10px 24px rgba(217,74,146,.45);
}
.go:hover { transform: translateY(-2px); }
.go:active { transform: scale(.98); }
.ghost {
  background: var(--glass-strong); border: 1.5px solid var(--glass-edge);
  min-height: 40px; padding: 0 16px; font-size: .9rem;
}
.ghost:hover { background: rgba(255,255,255,.8); }
.ghost:active { transform: scale(.96); }

/* shift stepper */
.stepper { display: flex; gap: 8px; align-items: stretch; }
.stepper button {
  flex: 0 0 var(--tap); width: var(--tap); padding: 0; font-size: 1.5rem; line-height: 1;
  background: var(--glass-strong); border: 1.5px solid var(--glass-edge);
}
.stepper button:active { transform: scale(.94); }
.stepper input { flex: 1; min-width: 0; }

/* result */
.result-head { display: flex; justify-content: space-between; align-items: center; gap: 10px; margin-bottom: 12px; flex-wrap: wrap; }
.result-head .label { margin: 0; }
.actions { display: flex; gap: 8px; flex-wrap: wrap; }
.output {
  background: var(--glass-strong); border: 1.5px dashed var(--rose); border-radius: 18px;
  padding: 16px; white-space: pre-wrap; overflow-wrap: anywhere; line-height: 1.55; font-weight: 600;
  margin: 0; font-family: inherit; font-size: 1rem; max-height: 60vh; overflow: auto;
  -webkit-user-select: all; user-select: all;
}
.error {
  background: rgba(255, 120, 150, .25); border: 1.5px solid rgba(255, 90, 130, .6);
  border-radius: 18px; padding: 14px 16px; font-weight: 600; color: #8a1f4a; line-height: 1.4;
}

/* help */
details { margin-top: 16px; }
details + details { margin-top: 10px; }
summary { cursor: pointer; font-weight: 700; padding: 12px 4px; min-height: var(--tap); list-style-position: inside; }
details p { margin: 0 4px 10px; line-height: 1.55; color: var(--ink-soft); font-weight: 500; }
details code { background: var(--glass-strong); padding: 1px 7px; border-radius: 8px; font-weight: 700; color: var(--ink); }

.toast {
  position: fixed; left: 50%; bottom: max(24px, env(safe-area-inset-bottom)); transform: translate(-50%, 30px);
  background: var(--ink); color: #fff; padding: 12px 20px; border-radius: 999px; font-weight: 700;
  opacity: 0; pointer-events: none; transition: opacity .25s, transform .25s; z-index: 10; max-width: 90vw;
}
.toast.show { opacity: 1; transform: translate(-50%, 0); }

footer { text-align: center; margin-top: 22px; font-size: .85rem; color: var(--ink-soft); line-height: 1.5; padding: 0 8px; }

/* ---------- Responsive ---------- */
@media (max-width: 480px) {
  .orb { filter: blur(40px); }
  .orb.a { width: 240px; height: 240px; }
  .orb.b { width: 220px; height: 220px; }
  .orb.c, .orb.d { display: none; }
  .glass { border-radius: 24px; }
  .actions { width: 100%; }
  .actions .ghost { flex: 1; }
  .output { max-height: 50vh; }
}
@media (max-width: 360px) {
  .seg label { font-size: .92rem; padding: 8px 4px; }
  button { padding: 0 16px; }
}
@media (min-width: 768px) {
  body { padding-top: 56px; }
  .split { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
  .split .field { margin-bottom: 18px; }
}
/* short landscape phones */
@media (max-height: 480px) and (orientation: landscape) {
  body { padding-top: 14px; }
  h1 { font-size: 1.8rem; }
  .sub { margin-bottom: 12px; }
  textarea { min-height: 90px; }
}
@media (prefers-reduced-motion: reduce) { * { transition: none !important; scroll-behavior: auto !important; } }
</style>
</head>
<body>
<div class="orb a"></div><div class="orb b"></div><div class="orb c"></div><div class="orb d"></div>

<main>
  <h1>Secret Notes ♡</h1>
  <p class="sub">Lock your messages with Caesar or Vigenère, then unlock them again.</p>

  <form method="post" class="glass" id="form" autocomplete="off">
    <div class="split">
      <div class="field">
        <span class="label" id="cipher-l">Cipher</span>
        <div class="seg" role="radiogroup" aria-labelledby="cipher-l">
          <input type="radio" name="cipher" id="c-caesar" value="caesar" <?= $cipher === 'caesar' ? 'checked' : '' ?>>
          <label for="c-caesar">Caesar</label>
          <input type="radio" name="cipher" id="c-vig" value="vigenere" <?= $cipher === 'vigenere' ? 'checked' : '' ?>>
          <label for="c-vig">Vigenère</label>
        </div>
      </div>

      <div class="field">
        <span class="label" id="mode-l">Action</span>
        <div class="seg" role="radiogroup" aria-labelledby="mode-l">
          <input type="radio" name="mode" id="m-enc" value="encrypt" <?= $mode === 'encrypt' ? 'checked' : '' ?>>
          <label for="m-enc">Encrypt</label>
          <input type="radio" name="mode" id="m-dec" value="decrypt" <?= $mode === 'decrypt' ? 'checked' : '' ?>>
          <label for="m-dec">Decrypt</label>
        </div>
      </div>
    </div>

    <div class="field">
      <div class="row">
        <label for="text" id="text-label"><?= $mode === 'decrypt' ? 'Ciphertext' : 'Plaintext' ?></label>
        <span class="actions">
          <button type="button" class="ghost" id="paste-btn">Paste</button>
          <button type="button" class="ghost" id="clear-btn">Clear</button>
        </span>
      </div>
      <textarea id="text" name="text" maxlength="20000" placeholder="Write your message here…" required><?= e($text) ?></textarea>
      <div class="hint"><span class="meta" id="count">0</span> characters</div>
    </div>

    <div class="field <?= $cipher === 'caesar' ? '' : 'hidden' ?>" id="caesar-field">
      <label for="shift">Shift (how many letters to move)</label>
      <div class="stepper">
        <button type="button" id="minus" aria-label="Decrease shift">−</button>
        <input type="number" id="shift" name="shift" value="<?= e((string)$shift) ?>" step="1" inputmode="numeric" placeholder="3">
        <button type="button" id="plus" aria-label="Increase shift">+</button>
      </div>
      <p class="hint">Use any whole number. Negative numbers move backwards.</p>
    </div>

    <div class="field <?= $cipher === 'vigenere' ? '' : 'hidden' ?>" id="vig-field">
      <label for="key">Secret key</label>
      <input type="text" id="key" name="key" value="<?= e($key) ?>" placeholder="e.g. lemon"
             autocapitalize="none" autocorrect="off" spellcheck="false" enterkeyhint="done">
      <p class="hint">Letters only. Anything else in the key is ignored.</p>
    </div>

    <button type="submit" class="go" id="go-btn"><?= $mode === 'decrypt' ? 'Decrypt message' : 'Encrypt message' ?></button>

    <details>
      <summary>How do these ciphers work?</summary>
      <p><strong>Caesar</strong> moves every letter the same number of steps. With a shift of 3, <code>A</code> becomes <code>D</code>, so “hi” becomes “kl”.</p>
      <p><strong>Vigenère</strong> uses a keyword. Each letter of the key sets a different shift, so the same letter can turn into different letters. To decrypt, use the same key.</p>
    </details>
  </form>

  <?php if ($error): ?>
    <div class="glass" id="result"><div class="error" role="alert"><?= e($error) ?></div></div>
  <?php elseif ($result !== null): ?>
    <section class="glass" id="result" aria-live="polite">
      <div class="result-head">
        <span class="label"><?= $mode === 'decrypt' ? 'Decrypted plaintext' : 'Encrypted ciphertext' ?></span>
        <span class="actions">
          <button type="button" class="ghost" id="copy-btn">Copy</button>
          <button type="button" class="ghost" id="reuse-btn"><?= $mode === 'decrypt' ? 'Encrypt it again' : 'Decrypt it back' ?></button>
        </span>
      </div>
      <pre class="output" id="output"><?= e($result) ?></pre>
    </section>
  <?php endif; ?>

  <footer>Only A–Z letters are changed. Numbers, spaces and punctuation stay the same.</footer>
</main>

<div class="toast" id="toast" role="status"></div>

<script>
const $ = (s) => document.querySelector(s);
const text = $('#text'), shift = $('#shift');

function toast(msg) {
  const t = $('#toast'); t.textContent = msg; t.classList.add('show');
  clearTimeout(toast.id); toast.id = setTimeout(() => t.classList.remove('show'), 1800);
}
function sync() {
  const cipher = document.querySelector('input[name="cipher"]:checked').value;
  const mode = document.querySelector('input[name="mode"]:checked').value;
  $('#caesar-field').classList.toggle('hidden', cipher !== 'caesar');
  $('#vig-field').classList.toggle('hidden', cipher !== 'vigenere');
  $('#text-label').textContent = mode === 'decrypt' ? 'Ciphertext' : 'Plaintext';
  $('#go-btn').textContent = mode === 'decrypt' ? 'Decrypt message' : 'Encrypt message';
}
document.querySelectorAll('input[name="cipher"], input[name="mode"]').forEach(r => r.addEventListener('change', sync));

// character counter
const count = () => $('#count').textContent = text.value.length.toLocaleString();
text.addEventListener('input', count); count();

// shift stepper
const bump = (d) => { shift.value = (parseInt(shift.value, 10) || 0) + d; };
$('#minus').addEventListener('click', () => bump(-1));
$('#plus').addEventListener('click', () => bump(1));

// clear & paste
$('#clear-btn').addEventListener('click', () => { text.value = ''; count(); text.focus(); });
$('#paste-btn').addEventListener('click', async () => {
  try {
    text.value = await navigator.clipboard.readText(); count(); toast('Pasted');
  } catch (e) {
    text.focus(); toast('Long-press the box to paste');
  }
});

// copy with fallback (clipboard API needs HTTPS; phones on local Wi-Fi use HTTP)
async function copyText(str) {
  try { await navigator.clipboard.writeText(str); return true; } catch (e) {}
  const ta = document.createElement('textarea');
  ta.value = str; ta.style.position = 'fixed'; ta.style.opacity = '0';
  document.body.appendChild(ta); ta.focus(); ta.select();
  let ok = false;
  try { ok = document.execCommand('copy'); } catch (e) {}
  ta.remove(); return ok;
}
const copyBtn = $('#copy-btn');
if (copyBtn) copyBtn.addEventListener('click', async () => {
  toast(await copyText($('#output').textContent) ? 'Copied ♡' : 'Press and hold the text to copy');
});

// use the result as the next input and flip the action
const reuse = $('#reuse-btn');
if (reuse) reuse.addEventListener('click', () => {
  const wasEncrypt = $('#m-enc').checked;
  text.value = $('#output').textContent;
  (wasEncrypt ? $('#m-dec') : $('#m-enc')).checked = true;
  sync(); count();
  window.scrollTo({ top: 0, behavior: 'smooth' });
  toast('Ready — tap ' + (wasEncrypt ? 'Decrypt' : 'Encrypt'));
});

// friendly submit: show progress, avoid double taps
$('#form').addEventListener('submit', () => {
  const b = $('#go-btn'); b.disabled = true; b.textContent = 'Working…';
});

// bring the result into view after submitting
const result = $('#result');
if (result) setTimeout(() => result.scrollIntoView({ behavior: 'smooth', block: 'start' }), 100);
</script>
</body>
</html>