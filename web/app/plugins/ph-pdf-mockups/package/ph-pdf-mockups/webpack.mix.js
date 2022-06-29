const { mix } = require("laravel-mix");
const path = require("path");
const package = require("./package.json");
const suffix = mix.inProduction() ? ".min" : "";

mix.js(
  "./assets/src/js/main.js",
  "./assets/dist/js/ph-pdf-mockups" + suffix + ".js"
);
mix.js("./assets/src/js/worker/main.js", "./assets/dist/js/ph-pdf-worker.js");
mix.js(
  "./assets/src/js/main-front.js",
  "./assets/dist/js/ph-pdf-mockups-front" + suffix + ".js"
);

mix.js(
  "./assets/src/js/dashboard.js",
  "./assets/dist/js/ph-pdf-mockups-dashboard.js"
);

mix.disableSuccessNotifications();
