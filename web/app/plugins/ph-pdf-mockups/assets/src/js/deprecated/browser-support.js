import * as compareVersions from "compare-versions";

export function supported() {
  const { detect } = require("detect-browser");
  const browser = detect();

  // handle the case where we don't detect the browser
  switch (browser && browser.name) {
    case "ie":
    case "edge":
      // if less than IE 11
      if (compareVersions(browser.version, "11") === -1) {
        return false;
      }
      break;

    case "safari":
      // if less than IE 11
      if (compareVersions(browser.version, "10") === -1) {
        return false;
      }
      break;

    case "android":
      // if less than IE 11
      if (compareVersions(browser.version, "5") === -1) {
        return false;
      }
      break;

    default:
      return true;
  }

  return true;
}

import { __ } from "@wordpress/i18n";

export function vexDialog() {
  if (typeof vex !== "object") {
    return;
  }
  vex.dialog.alert({
    message: __(
      "Browser Not Supported. Please update your browser to view this project.",
      "project-huddle"
    )
  });
}

export function vueDialog() {
  if (typeof Vue !== "function") {
    return;
  }
  Vue.prototype.$alert(
    __("Please update your browser to view this project.", "project-huddle"),
    __("Browser Not Supported.", "project-huddle"),
    {
      confirmButtonText: __("OK", "project-huddle")
    }
  );
}
