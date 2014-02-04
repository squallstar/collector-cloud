@Collector.module "Auth", (Auth, App, Backbone, Marionette, $, _) ->
  @startWithParent = false
  
  class Auth.Router extends Marionette.AppRouter
    appRoutes:
      "login" : "showLogin"
      "profile" : "showProfile"
      "forgot-password" : "showForgotPassword"

  Auth.Controller =
    _resetMenu: ->
      $('#sidebar .menu > a').removeClass 'active'

    Login: ->
      Auth.Controller._resetMenu()
      return App.request "search" if App.user
      App.content.show new Auth.LoginView

    ForgotPassword: ->
      App.content.show new Auth.ForgotPasswordView

    Profile: ->
      Auth.Controller._resetMenu()
      return App.request "search" unless App.user
      App.content.show new Auth.ProfileView
        model: App.user

  API =
    showLogin: ->
      $('.titlebar span').html "Login"
      Auth.Controller.Login()

    showProfile: ->
      $('.titlebar span').html "Your profile"
      Auth.Controller.Profile()

    showForgotPassword: ->
      $('.titlebar span').html "Forgot password"
      Auth.Controller.ForgotPassword()

  App.addInitializer ->
    new Auth.Router
      controller: API