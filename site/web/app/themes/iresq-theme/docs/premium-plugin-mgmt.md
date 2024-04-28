# Premium Plugin Management

Since Bedrock uses Composer to install plugins, we can't upload plugins to the site directly.

Some plugin authors have a repository that can be accessed through composer, and in that case go ahead and leave this doc go to this [guide](https://roots.io/guides/acf-pro-as-a-composer-dependency-with-encrypted-license-key/) written by one of the authors of Bedrock.

You can also view an example in `/site/composer.json` for Advanced Custom Fields.

#

## Installation

WooCommerce premium plugins don't provide direct repository access with API keys, so we can't just use composer.json. There are a few different ways to handle this but we chose to create our own private repositories on BitBucket.

1.  First you need to create a new repository on bitbucket
    - Each plugin needs to have it's own respository, there can't be more than one plugin in the respoitory.
2.  Create a `composer.json` file in the root of the repo that looks something like this

    ```
    {
      "name": "makedigitalgroup/plugin-name",
      "description": "Plugin Description",
      "keywords": ["wordpress", "plugin"],
      "homepage": "https://bitbucket.org/makedigitalgroup/plugin-repo",
      "authors": [
        {
          "name": "Plugin name",
          "homepage": "Plugin URL"
        }
      ],
      "type": "wordpress-plugin",
      "require": {
        "php": ">=7.0"
      }
    }

    ```

3.  Add in the plugin files to the root of the repository
4.  Now in the project repository, the one you are currently in because you're reading this doc, load the plugin that you've just created a private repo for in `/site/composer.json`

    ```
    "repositories": [
      ...
      {
        "type": "vcs",
        "url": "git@bitbucket.org:makedigitalgroup/plugin-repo.git"
      },
      ...
    ]
    ```

    and then

    ```
    "require": {
      "makedigitalgroup/plugin-name": "x.x.x"
    }
    ```

    **This name must match the name in `composer.json` file in the plugin repo**

    - You can have the version set to `dev-master` to pull the master branch but ideally you'd want to set up tags to keep track of the plugin version.

    - That would mean on the next push to master for the plugin's repo, you'd run

           $ git tag 1.0.0
           $ git push origin --tags

    - Now our require statement would look like this

      ```
      "require": {
        "makedigitalgroup/plugin-name": "1.0.0"
      }
      ```

5) Now in the projects `/site` folder run `composer update` and you have successfully loaded in a private plugin repository.

#

## Updating the plugin

Since our plugin is loaded into the site via our private repo, we won't be notified by wordpress that a plugin is out of date. When you see that a plugin needs to be updated you'll follow these steps.

1. Download the latest .zip file from the author and copy it to the plugin repository
2. Let's say the new plugin version is 4.3.1. In that case we'd run these git commands

   ```
     $ git add .
     $ git commit -m "updating to version 4.3.1"
     $ git tag 4.3.1
     $ git push origin --tags
   ```

3. Now we'd go to our projects `/site/composer.json` file and change the require statement to

   ```
   ...

   "require": {
     "makedigitalgroup/plugin-name": "4.3.1",
   }

   ...
   ```

   and then run

   ```
   $ composer clear-cache
   $ composer update
   ```

#

## Other resources

Here's a guide written by a member of the Roots team
https://roots.io/guides/private-or-commercial-wordpress-plugins-as-composer-dependencies/
