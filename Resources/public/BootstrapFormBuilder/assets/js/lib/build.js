({
  name: "../main",
  out: "../main-built.js",
  baseUrl: ".",
  shim: {
    'underscore': {
      exports: '_'
    },
    'bootstrap': {
      deps: ['jquery'],
      exports: '$.fn.popover'
    }
  },
  paths: {
    app         : ".."
    ,'mustache' : "mustache"
    ,'underscore' :  "underscore-min"
    ,'text'  : "text"
    , collections : "../collections"
    , data        : "../data"
    , models      : "../models"
    , helper      : "../helper"
    , templates   : "../templates"
    , views       : "../views"
  }
})
