var panels = chrome.devtools.panels;

// Grunt panel
var gruntPanel = panels.create(
  "PHP Debug",
  "extension/img/icon.png",
  "extension/panel.html"
);