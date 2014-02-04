@Collector.module "Entities", (Entities, App, Backbone, Marionette, $, _) ->

  class Entities.Article extends Backbone.Model
    _class: "Article"
    defaults:
      title: ""

  # --------------------------------------------------------------------------

  class Entities.Articles extends Backbone.Collection
    _class: "Articles"
    model: Entities.Article
    collection: false

    url: ->
      str = if @collection then "?collection=" + encodeURIComponent(@collection) else ''
      App.options.url + "/articles#{str}"

    fetchMore: (callback) ->
      url = @url()
      return url unless @length
      url = if url.indexOf('?') is -1 then url + '?' else url + '&'
      
      @fetch
        url: url + "limit=25&ts=" + encodeURIComponent(@last().get('datepublish'))
        update: true
        add: true
        remove: false
        success: ->
          callback true
        error: =>
          callback false


  # --------------------------------------------------------------------------

  class Entities.SearchArticles extends Entities.Articles
    _class: "SearchArticles"

    initialize: (models, query) ->
      @query = query

    url: ->
      App.options.url + "/search?q=" + encodeURIComponent(@query)