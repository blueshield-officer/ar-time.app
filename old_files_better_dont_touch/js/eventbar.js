if (event_bar) {
  event_bar = JSON.parse(event_bar);
  document.addEventListener("DOMContentLoaded", () => {
    const event_bar_text = document.getElementById('event-bar-text')
    event_bar_text.innerHTML = event_bar.text;
    event_bar_text.style.color = event_bar.color;
    document.getElementById('event-bar').style.backgroundColor = event_bar.bg_color;
  });
}