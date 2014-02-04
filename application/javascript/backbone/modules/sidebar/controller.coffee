@Collector.module "Sidebar", (Sidebar, App, Backbone, Marionette, $, _) ->

    Sidebar.Show = ->
      App.sidebar.show new Sidebar.View