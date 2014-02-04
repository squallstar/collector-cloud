@Collector.module "Entities", (Entities, App, Backbone, Marionette, $, _) ->

  class Entities.Collection extends Backbone.Model
    _class: "Collection"
    defaults:
      name: ""
      color: "#FF0000"
      sources: []
      articles: []

    initialize: ->
      @articles = new Entities.Articles
      @articles.collection = @get 'name'

  # --------------------------------------------------------------------------

  class Entities.Collections extends Backbone.Collection
    _class: "Collections"
    model: Entities.Collection

    url: ->
        App.options.url + "/collections"