_.extend Backbone.Marionette.Application::,
  navigate: (route, options = {}) ->
    Backbone.history.navigate route, options

Backbone.Marionette.Renderer.render = (template, data) ->
  path = Templates[template]
  unless path
    throw "Template #{template} not found!"
  path(data)