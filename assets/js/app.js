// include styling
require("../sass/app.sass");

// jquery & attach to window
const $ = require("jquery");
window.$ = $;

// bootstrap
const bootstrap = require("bootstrap");


// other parts of the application
require("./utils");
require("./usability");
require("./vue_apps");