@Collector.module "Collections", (Collections, App, Backbone, Marionette, $, _) ->

  class Collections.Collection extends Marionette.ItemView
    template: "collection"
    tagName: "article"

    events:
      "click" : "clickCollection"

    templateHelpers: ->
      image = ''
      for article in @model.get('articles')
        if article['image_url']
          image = article['image_url']
          break

      sources = new Array
      for source in _.values(@model.get('sources'))
        if source.title then sources.push source.title

      description = sources.join ', '
      if description.length > 60 then description = description.substr(0, 59) + '&hellip;'

      description: description
      image_url: image

    clickCollection: (event) ->
      do event.preventDefault
      name = @model.get('name').split(' ').join('-')
      App.collection = @model
      App.navigate "collections/#{name}", trigger: true

  class Collections.View extends Marionette.CompositeView
    className: "collections"
    tagName: "section"
    template: "collections"
    itemView: Collections.Collection
    itemViewContainer: ".wrapper"
    fetched: false
    fetching: false
    setup: false

    ui: ->
      wrapper: ".wrapper"

    itemViewOptions: ->
      fetched: @fetched

    initialize: ->
      $('#sidebar .menu .collections').addClass 'active'

    onDomRefresh: ->
      $(window).scrollTop 0
      @ui.wrapper.masonry()

    onRender: ->
      if not @collection.length then do @fetch

    fetch: ->
      @fetching = true
      @collection.fetch
        success: =>
          @fetched = true
          @fetching = false
          if @collection.length is 0
            @render()
        error: =>
          alert 'Cannot retrieve the collections right now. Please try later'

    appendHtml: (collectionView, itemView, index) ->
      if not @setup
        @ui.wrapper.masonry
          itemSelector: 'article'
          columnWidth: "article"
          isAnimated: false
          gutter: 0
        @setup = true

      if index%4 is 0 and index > 0
        itemView.$el.addClass 'double'

      @ui.wrapper.append itemView.el
      @ui.wrapper.masonry 'appended', itemView.el

      if index is (@collection.length-1)
        @ui.wrapper.masonry()

    onClose: ->
      @ui.wrapper.masonry 'destroy'