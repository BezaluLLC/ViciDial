var viciwebdial = {
  onLoad: function() {
    this.initialized = true;
    this.strings = document.getElementById("viciwebdial-strings");
  },

  onMenuItemCommand: function(e) {
    var promptService = Components.classes["@mozilla.org/embedcomp/prompt-service;1"]
                                  .getService(Components.interfaces.nsIPromptService);
    promptService.alert(window, this.strings.getString("helloMessageTitle"),
                                this.strings.getString("helloMessage"));
  },

  onToolbarButtonCommand: function(e) {
    viciwebdial.onMenuItemCommand(e);
  }
};
