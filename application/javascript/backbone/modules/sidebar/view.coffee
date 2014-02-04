@Collector.module "Sidebar", (Sidebar, App, Backbone, Marionette, $, _) ->

  class Sidebar.View extends Marionette.ItemView
    template: "sidebar"
    tagName: "section"
    className: "sidebar"

    events:
      "click .menu a": "clickMenu"

    templateHelpers:
      "logged": false

    clickMenu: (event) ->
      do event.preventDefault

      $el = $(event.currentTarget)
      return if $el.hasClass 'disabled'

      @$el.find('.menu a').removeClass 'active'

      if $el.hasClass 'articles'
      	App.request "search"
      else if $el.hasClass 'collections'
        App.request "collections"