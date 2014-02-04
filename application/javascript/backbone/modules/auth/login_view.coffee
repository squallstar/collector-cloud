@Collector.module "Auth", (Auth, App, Backbone, Marionette, $, _) ->

  class Auth.LoginView extends Marionette.Layout
    template: "login"
    className: "window"

    events:
      "click .btn-login": "doLogin"
      "submit form": "doLogin"
      "click .forgot-password": "showForgotPassword"
      "keyup input.email, input.password": "keyUp"

    ui:
      email: "input.email"
      password: "input.password"

    showForgotPassword: (event) ->
      do event.preventDefault
      App.navigate "forgot-password", trigger: true

    keyUp: (e) ->
      if e.which is 13
        @doLogin()

    doLogin: (event) ->
      if event then do event.preventDefault

      @email = @ui.email.val()
      if not @email then return @ui.email.focus()

      @password = @ui.password.val()
      if not @password then return @ui.password.focus()

      @ui.email.blur()
      @ui.password.blur()

      $.ajax
        method: 'POST'
        url: App.options.url + '/user'
        data:
          email: @email
          password: @password
        success: (data) ->
          App.user = new App.Entities.User data
          App.request "user:login"
          App.request "search"
        error: ->
          alert 'The e-mail address or password you entered is not valid.'
      false

    onDomRefresh: ->
      @ui.email.focus()

      window.scrollTo 0, 0