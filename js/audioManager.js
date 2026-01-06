const BASE = window.location.pathname.startsWith("/Lumora/") ? "/Lumora" : "";
let audioMap = null;
let current = { bgm: null, ambience: [] };
let unlocked = sessionStorage.getItem("lumora_audio_unlocked") === "1";

async function loadAudioMap() {
  if (audioMap) return audioMap;
  const res = await fetch(`${BASE}/config/audioMap.json`);
  audioMap = await res.json();
  return audioMap;
}

export async function unlockAudio() {
  unlocked = true;
  sessionStorage.setItem("lumora_audio_unlocked", "1");
}


function fadeTo(audio, target, ms = 700) {
  const start = audio.volume;
  const t0 = performance.now();
  function step(t) {
    const p = Math.min(1, (t - t0) / ms);
    audio.volume = start + (target - start) * p;
    if (p < 1) requestAnimationFrame(step);
  }
  requestAnimationFrame(step);
}

function stopCurrent() {
  if (current.bgm) {
    current.bgm.pause();
    current.bgm = null;
  }
  current.ambience.forEach(a => a.pause());
  current.ambience = [];
}

export async function playIsland(islandId, { fade = true } = {}) {
  const map = await loadAudioMap();
  const cfg = map[islandId] || map["peace"];

  // Alte Audio stoppen
  stopCurrent();

  // Neue Audio anlegen
  const bgm = new Audio(cfg.bgm.startsWith("/") ? `${BASE}${cfg.bgm}` : cfg.bgm);
  bgm.loop = true;
  bgm.volume = fade ? 0 : (cfg.volumes?.bgm ?? 0.18);

  const amb = (cfg.ambience || []).map(src => {
  const url = src.startsWith("/") ? `${BASE}${src}` : src;
  const a = new Audio(url);
  a.loop = true;
  a.volume = fade ? 0 : (cfg.volumes?.ambience ?? 0.10);
  return a;
});


  current.bgm = bgm;
  current.ambience = amb;

  if (!unlocked) return; // falls Inselseite ohne vorherigen Click

  try {
    await bgm.play();
    for (const a of amb) await a.play();
    if (fade) {
      fadeTo(bgm, cfg.volumes?.bgm ?? 0.18);
      amb.forEach(a => fadeTo(a, cfg.volumes?.ambience ?? 0.10));
    }
  } catch (e) {
    console.warn("Audio blocked by browser:", e);
  }
}

export function setSelectedIsland(islandId) {
  sessionStorage.setItem("lumora_selected_island", islandId);
}

export function getSelectedIsland() {
  return sessionStorage.getItem("lumora_selected_island") || "peace";
}

export function stopAudio() {
  stopCurrent();
  setAudioEnabled(false);
}

export function isAudioPlaying() {
  return !!(current.bgm && !current.bgm.paused);
}

export async function toggleAudioForIsland(islandId) {
  // Wenn aus -> an
  if (!isAudioPlaying()) {
    await unlockAudio();
    return await playIsland(islandId);
  }
  // Wenn an -> aus
  stopAudio();
  return true;
}

export function setAudioEnabled(enabled) {
  sessionStorage.setItem("lumora_audio_enabled", enabled ? "1" : "0");
}

export function getAudioEnabled() {
  return sessionStorage.getItem("lumora_audio_enabled") !== "0";
}
