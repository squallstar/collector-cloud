@Collector.module "Header", (Header, App, Backbone, Marionette, $, _) ->

  class Header.Suggestion extends Marionette.ItemView
    tagName: "div"
    className: "suggestion"
    template: "suggestion"

    events:
      "click" : "clickSuggestion"

    clickSuggestion: (event) ->
      do event.preventDefault
      query = @model.get 'domain'
      @model.collection.reset()

      App.request "search:domain", query


  # --------------------------------------------------------------------------

  class Header.Suggestions extends Marionette.CollectionView
    className: "suggestions"
    itemView: Header.Suggestion

    collectionEvents:
      "reset": ->
        @$el.removeClass 'open'

    fetch: (query) ->
      if query is ''
        return @collection.reset()

      @collection.query = query

      @collection.fetch
        success: =>
          if @collection.length > 0
            @$el.addClass 'open'
          else
            @$el.removeClass 'open'

  # --------------------------------------------------------------------------

  class Header.View extends Marionette.ItemView
    template: "header"
    tagName: "section"
    className: "header"

    events:
      "click .search": "clickSearch"
      "keyup .search input": "doSearch"
      "click .logo": "navigateRoot"

    ui:
      search: ".search"
      searchinput: ".search input"

    initialize: ->
      App.reqres.setHandler "search:domain", (domain) =>
        @ui.searchinput.val domain
        @ui.searchinput.blur()
        App.request "search", domain

      App.reqres.setHandler "search:clear", =>
        @ui.searchinput.val ''
        @closeSearchBox()

      @suggestions = new Header.Suggestions
        collection: new App.Entities.Suggestions

    templateHelpers: ->
      "search_query": if App.searchQuery then App.searchQuery else ''
      "user": if App.user then App.user.toJSON() else false

    navigateRoot: (event) ->
      do event.preventDefault
      App.request "search"

    clickSearch: (event) ->
      do event.preventDefault

      if @ui.search.hasClass 'open'
        return @ui.search.find('input').focus()

      @ui.search.addClass 'open'
      @ui.search.find('input').focus()

    doSearch: (e) ->
      return unless @ui.search.hasClass 'open'
      if @suggestionsInterval then clearInterval @suggestionsInterval

      if e.which is 13
        query = @ui.searchinput.val()
        if query
          @ui.searchinput.blur()
          @suggestions.collection.reset()
          App.request "search", query
        else
          @closeSearchBox()
      else if e.which is 27
        @ui.searchinput.val ''
        @closeSearchBox()
      else
        @suggestionsInterval = setTimeout (=>
          @suggestions.fetch @ui.searchinput.val()
        ), 380

    closeSearchBox: ->
      @ui.searchinput.blur()
      @ui.search.removeClass('open')
      if App.searchQuery
        App.request "search"

    onRender: ->
      if App.searchQuery then @ui.search.addClass 'open'

      @ui.search.append @suggestions.$el