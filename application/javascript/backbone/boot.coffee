@Collector = do (Backbone, Marionette) ->

  App = new Marionette.Application

  App.rootRoute = "articles"
  App.vent = _.extend {}, Backbone.Events

  App.addRegions
    header:  "#header"
    sidebar: "#sidebar"
    content: "#content"

  App.addInitializer ->
    do @module(module).Show for module in ["Header", "Sidebar"]

  App.on "initialize:before", (options) ->
    @options =
      url: options.url.replace(/^\/|\/$/g, '')

    if options.user
      App.user = new App.Entities.User options.user

    App.body = $('body')

  App.on "initialize:after", ->
    if Backbone.history
      Backbone.history.start( )
      @navigate(@rootRoute, trigger: true) if Backbone.history.fragment is ""

  App