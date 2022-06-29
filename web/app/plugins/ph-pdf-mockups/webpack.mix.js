const { mix } = require("laravel-mix");
// worker
mix.js("./assets/src/js/worker/main.js", "./assets/dist/js/ph-pdf-worker.js");
// main
mix.js("./assets/src/js/main.js", "./assets/dist/js/ph-pdf-mockups.js");
// special admin bundle
mix.js("./assets/src/js/admin.js", "./assets/dist/js/ph-pdf-mockups-admin.js");

// deprecated
mix.js(
  "./assets/src/js/deprecated/main-front.js",
  "./assets/dist/js/ph-pdf-mockups-front.js"
);

mix.js(
  "./assets/src/js/deprecated/main.js",
  "./assets/dist/js/ph-pdf-mockups-admin-deprecated.js"
);

mix.js(
  "./assets/src/js/deprecated/dashboard.js",
  "./assets/dist/js/ph-pdf-mockups-dashboard-deprecated.js"
);

mix.disableSuccessNotifications();
