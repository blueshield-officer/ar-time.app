if (event_bar) {
  event_bar = JSON.parse(event_bar);


  document.addEventListener("DOMContentLoaded", (event) => {
    // text
    document.getElementById('event-bar-text').innerHTML = event_bar.event_text;
// color
    document.getElementById('event-bar').backgroundColor = event_bar.event_color;
  });
}