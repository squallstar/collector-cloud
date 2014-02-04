@Collector.module "Articles", (Articles, App, Backbone, Marionette, $, _) ->

  class Articles.Empty extends Marionette.ItemView
    template: "articles_empty"
    className: "empty-search"

    initialize: (options) ->
      @fetched = options.fetched

    templateHelpers: ->
      "error" : false
      "fetched" : @fetched
      "searching": App.searchQuery
      "query" : "<em>#{App.searchQuery}</em>"
      "user" : App.user?

  class Articles.Article extends Marionette.ItemView
    template: "article"
    tagName: "article"

    initialize: (options) ->
      model = options.model
      if model.collection._class is 'SearchArticles'
        words = model.collection.query.replace('#', '').split ' '
        for word in words
          regexp = new RegExp(word, "gim")
          for field in ['title', 'content']
            model.set(field, model.get(field).replace regexp, (match) ->
              "<em>#{match}</em>"
            )

    events:
      "click" : "clickArticle"
      "click .action.open-website" : "didClickOpenWebsite"
      "click .action.more-this-source" : "didClickMore"
      "click .action.cancel" : "didClickCancel"

    clickArticle: (event) ->
      do event.preventDefault

      if not @$el.hasClass 'focus'
        App.request "focus", @
        @$el.addClass 'focus'
      else
        App.request "focus"

    didClickOpenWebsite: (event) ->
      do event.preventDefault
      window.open @model.get('url')
      App.request "focus"

    didClickMore: (event) ->
      do event.preventDefault
      App.request "search", @model.get('domain')

    didClickCancel: (event) ->
      do event.preventDefault
      App.request "focus"

    removeFocus: ->
      @$el.removeClass 'focus'

    templateHelpers: ->
      link = document.createElement 'a'
      link.href = @model.get 'url'

      title = @model.get 'title'
      if title.length > 95 then title = title.substring(0, 94) + '&hellip;'

      source = @model.get 'source_title'
      if source
        tmp = source.split '-'
        source = tmp[0].trim()
      if source and source.length > 30 then source = source.substring(0, 29) + '&hellip;'

      domain = @model.get('domain')
      if domain then domain = domain.replace(/^(feeds|www)\./, '')

      "domain": domain
      "title_trimmed": title
      "source_trimmed": source
      "time_ago": moment(@model.get('datepublish'), "YYYY-MM-DD HH:mm:ss").fromNow()
      "has_collection": @model.get('collection')?
      "show_more": if not App.searchQuery or App.searchQuery isnt @model.get('domain') then true else false

  class Articles.View extends Marionette.CompositeView
    className: "articles"
    tagName: "section"
    template: "articles"
    itemView: Articles.Article
    itemViewContainer: ".wrapper"
    emptyView: Articles.Empty
    fetched: false
    fetching: false

    # Indicates whether the masonry instance has been initialized
    setup: false

    # Indicates whether the first masonry layout has been called after appending all the elements
    firstLayout: false

    # Indicates whether the view has already fetched for extra articles
    loadMore: false

    itemViewOptions: ->
      fetched: @fetched

    ui: ->
      wrapper: ".wrapper"

    initialize: ->
      if not @collection then return throw new Error 'A collection is required'

      $('#sidebar .menu .articles').addClass 'active'

      _.bindAll @, 'pageScroll'
      $(window).scroll @pageScroll

      if not @collection.length
        window.setTimeout (=>
          @fetch()
        ), 50

    onClose: ->
      $(window).unbind 'scroll'
      if @setup then @ui.wrapper.masonry 'destroy'

    fetch: ->
      @fetching = true
      @collection.fetch
        success: =>
          @fetched = true
          @fetching = false
          if @collection.length is 0
            @render()
        error: =>
          @$el.html '<div class="empty-search">' + (Marionette.Renderer.render "articles_empty", error: true) + '</div>'

    onDomRefresh: ->
      $(window).scrollTop 0
      if @setup then @ui.wrapper.masonry()

    pageScroll: (event) ->
      return if @fetching or not @firstLayout
      $target = $ event.target
      height = $(window).height()

      if $target.scrollTop() >= ($target.height() - height - 500)
        @fetching = true
        if @firstLayout then @loadMore = true
        @collection.fetchMore =>
          @fetching = false

    appendHtml: (collectionView, itemView, index) ->
      if not @setup
        @ui.wrapper.masonry
          itemSelector: 'article'
          columnWidth: "article"
          isAnimated: false
          gutter: 0
        @setup = true

      @ui.wrapper.append itemView.el
      @ui.wrapper.masonry 'appended', itemView.el

      if @collection.length > 0 and index is (@collection.length-1) and not @loadMore
        @firstLayout = true
        @ui.wrapper.masonry()
