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
  btn.className = "btn"; // ODER eigene CSS-Klasse
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
      });
  });
}

async function initIsland() {
  const btn = ensureSoundButton();

  const islandId = document.body.dataset.island;
  if (!islandId) return;
  setSelectedIsland(islandId);

  updateLabel(btn);

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
 if (document.body.dataset.island) {
  await initIsland();
} else {
  await initWorld();
}

});
