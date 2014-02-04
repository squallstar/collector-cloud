@Collector.module "Header", (Header, App, Backbone, Marionette, $, _) ->

    Header.Show = ->
      @layout = new Header.View
      App.header.show @layout

    App.reqres.setHandler "user:login", ->
      Header.layout.render()

    App.reqres.setHandler "user:logout", ->
      Header.layout.render()