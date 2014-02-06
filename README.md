# Collector & Fragments.me Cloud

This project consists in two pieces:

1. Back-end APIs written in PHP using CodeIgniter framework
2. A front-end webapp written in Javascript using Backbone and Marionette

You can see a working example of the app here: http://fragments.me

---

## Setup

In order to build the product you'll need **Grunt** locally installed on your machine.

You'll find a dump of the database here ``application/config/database.create.sql``, while its connection settings should go there ``application/config/database.php``. It's a typical **CodeIgniter** application, so if you understand how it works you should then be pretty much able to inspect most of the project.

Your virtualhost should point to the root of the project - also make sure that the webserver (Apache) is reading the ``.htaccess`` file.

If you want to use the system to also fetch Tweets, configure your Twitter app details in ``application/config/config.php``:

    $config['twitter_consumer_key'] = 'your_twitter_consumer_key';
    $config['twitter_consumer_secret'] = 'your_twitter_consumer_private_key';


When your database/vhost setup is done, just install the nodejs dependencies and run grunt from your command line to build the javascript/sass sources:

    $ npm install .

Then finally:

    $ grunt

---

## Cronjobs

In order to let your application auto-fetch the articles and keep the indexes updated, setup the following cron jobs o your machine:

    #Updates all the sources every 30 minutes
    */30 * * * * php /path/to/app/index.php cron update_all_sources
    #
    #Resolves proxied urls twice a hour
    10,40 * * * * php /path/to/app/fragments/index.php cron retrieve_feedproxy_urls
    #
    # Delete old articles once a day
    0 0 * * * php /path/to/app/index.php cron delete_old_articles
    #
    # Updates the sources suggestions twice a day
    0 1,13 * * * php /path/to/app/index.php cron update_suggestions
    #
    # Optimizes the tables once a day
    0 12 * * * php /path/to/app/index.php cron optimize_tables

While on development, you can manually run each job accessing your app from the browser like this:

    http://localhost/cron/update_all_sources

---

## API

Documentation will be available soon. In the meantime you can have a look at the project Apiary file in the root directory ``apiary.apib``.

---

## Adding sources

The default sources that will be loaded by the systems are defined here:

    application/config/public_collections.php
