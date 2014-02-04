@Collector.module "Entities", (Entities, App, Backbone, Marionette, $, _) ->

  class Entities.Suggestion extends Backbone.Model
    _class: "Suggestion"
    defaults:
      domain: ""
      source_id: 0
      source_name: ""
      relevance: 0

  # --------------------------------------------------------------------------

  class Entities.Suggestions extends Backbone.Collection
    _class: "Suggestions"
    model: Entities.Suggestion

    url: ->
    	App.options.url + "/suggestions?q=" + encodeURIComponent(@query)