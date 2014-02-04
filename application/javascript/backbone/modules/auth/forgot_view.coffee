@Collector.module "Auth", (Auth, App, Backbone, Marionette, $, _) ->

  class Auth.ForgotPasswordView extends Marionette.Layout
    template: "forgot-password"
    className: "window"

    @email_sent: false

    events:
      "click .btn-send": "sendPassword"
      "click .login-action": "doLogin"
      "keyup input.email": "keyUp"

    templateHelpers: ->
      "email_sent": @email_sent

    ui:
      email: "input.email"

    keyUp: (e) ->
      if e.which is 13
        @sendPassword()

    doLogin: (event) ->
      do event.preventDefault
      App.navigate "login", trigger: true

    sendPassword: (event) ->
      if event then do event.preventDefault

      @email = @ui.email.val()
      if not @email then return @ui.email.focus()

      $.ajax
        method: 'POST'
        url: App.options.url + '/user/send-password'
        data:
          email: @email
          password: @password
        success: (data) =>
          @email_sent = true
          @render()
        error: ->
          alert 'The e-mail address inserted doesn\'t belong to any Collector account.'

    onDomRefresh: ->
      @ui.email.focus()
      window.scrollTo 0, 0