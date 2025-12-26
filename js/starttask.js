async function startTask(category = "") {
  const url = category
    ? `/Lumora/php/start_task.php?category=${encodeURIComponent(category)}`
    : `/Lumora/php/start_task.php`;

  const res = await fetch(url);
  const data = await res.json();

  if (!res.ok) {
    // z.B. limit_reached
    alert(data.error || "Could not start task");
    return;
  }

  // Task-Daten für das neue Fenster ablegen
  localStorage.setItem("lumora_active_task", JSON.stringify(data));

  // Neues Fenster öffnen
  window.open("/Lumora/html/task_window.html", "_blank", "width=900,height=600");
}