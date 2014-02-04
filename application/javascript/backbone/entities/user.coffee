@Collector.module "Entities", (Entities, App, Backbone, Marionette, $, _) ->

  class Entities.User extends Backbone.Model
    defaults:
      id: 0
      username: ''
      email: ''

    logout: ->
      $.get App.options.url + '/user/logout'
      delete App.user
      App.request "user:logout"