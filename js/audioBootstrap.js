import {
  unlockAudio,
  playIsland,
  setSelectedIsland,
  getSelectedIsland,
  stopAudio,
  isAudioPlaying,
  getAudioEnabled,
  setAudioEnabled
} from "./audioManager.js";

function ensureSoundButton() {
  let btn = document.getElementById("lumoraSoundToggle");
  if (btn) return btn;

  btn = document.createElement("button");
  btn.id = "lumoraSoundToggle";
  btn.style.position = "fixed";
  btn.style.bottom = "20px";
  btn.style.right = "20px";
  btn.style.zIndex = "9999";
  btn.className = "btn"; // wenn du willst: eigene CSS-Klasse
  document.body.appendChild(btn);
  return btn;
}

function updateLabel(btn) {
  const on = isAudioPlaying() && getAudioEnabled();
  btn.textContent = on ? "🔇 Sound off" : "🔊 Sound on";
}

function isWorldPage() {
  return document.querySelector(".island-grid") && document.querySelector("a.island[data-island]");
}

function isIslandPage() {
  // simpel: auf Insel-Seiten gibt’s den Back-Button zu world.php
  return document.querySelector('a[href$="php/world.php"], a.btn-back');
}

async function initWorld() {
  document.querySelectorAll("a.island.unlocked[data-island]").forEach(card => {
    card.addEventListener("click", async () => {
      // Insel merken + Audio freischalten (für spätere Seite)
      await unlockAudio();
      setSelectedIsland(card.dataset.island);
      // optional: kleiner "select"-sound könnte hier gespielt werden
    });
  });
}

async function initIsland() {
  const btn = ensureSoundButton();
  updateLabel(btn);

  const islandId = getSelectedIsland(); // oder per body data-island, wenn du das später setzt

  // Autostart versuchen (wenn enabled)
  if (getAudioEnabled()) {
    await unlockAudio();
    const ok = await playIsland(islandId);
    // Wenn blockiert: Button bleibt auf "Sound on"
    if (ok) updateLabel(btn);
  }

  btn.addEventListener("click", async () => {
    if (isAudioPlaying() && getAudioEnabled()) {
      stopAudio();
      updateLabel(btn);
    } else {
      setAudioEnabled(true);
      await unlockAudio();
      await playIsland(islandId);
      updateLabel(btn);
    }
  });
}

document.addEventListener("DOMContentLoaded", async () => {
  if (isWorldPage()) await initWorld();
  if (isIslandPage()) await initIsland();
});
