@Collector.module "Auth", (Auth, App, Backbone, Marionette, $, _) ->

  class Auth.ProfileView extends Marionette.ItemView
    template: "profile"
    className: "window"

    events:
      "click .btn-logout" : "doLogout"

    doLogout: (event) ->
      do event.preventDefault
      App.user.logout()
      if App.collections then delete App.collections
      App.navigate "login", trigger: true