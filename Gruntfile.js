'use strict';

var mount = function (connect, dir) {
  return connect.static(require('path').resolve(dir));
};

var timestamp = new Date().getTime()

module.exports = function(grunt) {

  grunt.loadNpmTasks('grunt-contrib-clean');
  grunt.loadNpmTasks('grunt-contrib-coffee');
  grunt.loadNpmTasks('grunt-contrib-concat');
  grunt.loadNpmTasks('grunt-jade-plugin');
  grunt.loadNpmTasks('grunt-contrib-sass');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-watch');

  grunt.initConfig({

    clean: {
      all: ['application/assets/js', 'application/assets/css'],
      post_build: ['.sass-cache', 'npm-debug.log', '__templates.js']
    },

    coffee: {
      compileJoined: {
        options: {
          join: true
        },
        files: {
          'assets/js/backbone.js': [
            'application/javascript/backbone/**/*.coffee'
          ]
        }
      },
    },

    concat: {
      js: {
        src: [
          'application/javascript/vendor/jquery.js',
          'application/javascript/vendor/underscore.js',
          'application/javascript/vendor/backbone.js',
          'application/javascript/vendor/marionette.js',
          'application/javascript/vendor/plugins/pace.js',
          'application/javascript/vendor/plugins/masonry.js',
          'application/javascript/vendor/plugins/moment.js',
          '__templates.js',
          'assets/js/backbone.js'
        ],
        dest: 'assets/js/backbone.js'
      },
      css: {
        src: [
          'application/css/vendor/reset.css',
          'application/css/vendor/grid.css',
          'assets/css/core.css'
        ],
        dest: 'assets/css/core.css'
      }
    },

    jade2js: {
      compile: {
        files: {
          '__templates.js': ['application/views/backbone/templates/**/*.jade']
        }
      }
    },

    sass: {
      development: {
        files: {
          'assets/css/core.css' : [
            'application/css/scss/main.scss'
          ]
        },
        options: {
          lineNumbers: true,
          style: 'expanded'
        }
      },
      release: {
        files: {
          'assets/css/core.css' : [
            'application/css/scss/main.scss'
          ]
        },
        options: {
          style: 'compressed'
        }
      }
    },

    uglify: {
      release: {
        preserveComments : false,
        files: {
          'assets/js/backbone.js': ['assets/js/backbone.js']
        }
      }
    },

    watch : {
      scss: {
        files: [
          'application/css/**',
        ],
        tasks: ['clean:all', 'sass:development', 'concat:css', 'clean:post_build'],
        options: {
          livereload: true,
          debounceDelay: 1000,
        },
      },
      js: {
        files: [
          'application/javascript/**',
          'application/views/backbone/templates/**'
        ],
        tasks: ['clean:all', 'coffee', 'jade2js', 'concat:js', 'clean:post_build'],
        options: {
          livereload: true,
          debounceDelay: 1000,
        },
      },
    }

  });

  grunt.registerTask('build', [
    'clean:all',
    'sass:development',
    'concat:css',
    'coffee',
    'jade2js',
    'concat:js',
    'clean:post_build'
  ]);

  grunt.registerTask('build:production', [
    'clean:all',
    'sass:release',
    'concat:css',
    'coffee',
    'jade2js',
    'concat:js',
    'uglify',
    'clean:post_build'
  ]);

  grunt.registerTask('default', ['build', 'watch']);
}