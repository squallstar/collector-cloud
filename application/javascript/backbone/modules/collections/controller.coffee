@Collector.module "Collections", (Collections, App, Backbone, Marionette, $, _) ->
  @startWithParent = false
  
  class Collections.Router extends Marionette.AppRouter
    appRoutes:
      "collections" : "listCollections"
      "collections/:name": "viewCollection"

  Collections.Controller =
    View: ->
      if not App.collections then App.collections = new App.Entities.Collections
      App.content.show new Collections.View
        collection: App.collections

  API =
    listCollections: ->
      $('.titlebar span').html 'Viewing ' + (if App.user then 'your' else 'public') + ' collections'
      Collections.Controller.View()

    viewCollection: (name) ->
      collection = if App.collection
        App.collection
      else
        new App.Entities.Collection name: name
      if App.collection then delete App.collection

      App.request "collection:articles", collection

  App.addInitializer ->
    new Collections.Router
      controller: API

  App.reqres.setHandler "collections", ->
    App.navigate "collections", trigger: true