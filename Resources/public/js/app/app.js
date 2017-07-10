var APP = APP || {};

APP.trace = function (message) {
    if (typeof console !== 'undefined' && typeof console.log !== 'undefined') {
        console.log(message);
    }
};

APP.ctrace = function (message) {
    if (typeof console !== 'undefined' && typeof console.log !== 'undefined') {
        console.clear();
        console.log(message);
    }
};

APP.typeof = function (message) {
    APP.trace(typeof message);
};