# iResQ Documentation

## Key Commands

Deploy to production

```
# Navigate to the Trellis folder
./bin/deploy.sh production production-iresq.com
```

Deploy to Staging

```
# Navigate to the Trellis folder
./bin/deploy.sh staging staging-iresq.com
```

Open the Local Database 

```
# Navigate to the Trellis folder. Must have Sequel installed.
vagrant trellis-sequel open
```

Edit Production Secrets

```
# Navigate to the Trellis folder.
# Replace production with staging or dev, as needed.
EDITOR=nano ansible-vault edit group_vars/production/vault.yml
```

## Requirements

- [Virtualbox](https://www.virtualbox.org/wiki/Downloads) >= 4.3.10
  Clone the repository to your local machine
- [Vagrant](https://www.vagrantup.com/downloads.html) => 2.1.0
  ```
  $ brew cask install vagrant
  ```
- [Homebrew](https://brew.sh)
- Composer
  ```
  $ brew install composer
  ```
- Ansible

      $ sudo easy_install pip
      $ pip install ansible

  - If you get a successful install of ansible but still get a _command not found error_ then run this

          $ sudo -H pip install ansible

#

## Local Development

- Clone the repository to your local machine

        $ git clone git@bitbucket.org:makedigitalgroup/iresq.git

- Navigate to the `/site` directory and install all composer dependicies (where WP plugins are installed). If you get an error regarding bitbucket access, then you need to add your public SSH key to the respective BitBucket respository (all of our premium plugins are hosted on a private repository)

        $ cd site
        $ composer install

- Navigate to the trellis directory and create a file called `.vault_pass`. Ask for the password from another developer and paste the password in this file.
- Now run this command in the trellis directory

        $ vagrant up

  _Note: to shut down the server:_ `vagrant halt`

  This will take 10-15 minutes and the site will be viewable at **http://iresq.local/**.

- Set up a self-signed certificate to enable SSL on your local environment

  - Check the settings in _/trellis/group_vars/development/wordpress_sites.yml_ and make sure that **ssl** is enabled and **provider** is set to **self signed**
  - Run

        $ vagrant provision

  - Now go back to the development URL and you should see a security warning. This is because self-signed certificate are not actual certificates. They are dummy certificates for your machine only. To get around this run these commands in the _trellis_ folder

        $ vagrant plugin install vagrant-trellis-cert
        $ vagrant trellis-cert trust

  - Enter your password to allow the changes and now you should have a secure connection on your local development site and can visit the site at `https://iresq.local`

- Pull down the production database. Navigate to the `site/scripts` directory and runWe are running two Shopify stores and want to review our store map application, align the store map design with our over all design and update the store list


        $ ./sync-kinsta.sh production development

  - This uses the **production** and **development** aliases from `site/wp-cli.yml`
  - For more information on database syncing, visit the section about that near the end of this document

#

### Startup troubleshooting

- If `vagrant up` errors out then run

        $ vagrant provision

- If that doesn't work make sure you have composer, ansible, and ansible-vault installed correctly.

- These commands will ensure you have the correct versions of ansible and ansible-galaxy installed

        $ pip install -r requirements.txt
        $ ansible-galaxy install -r galaxy.yml

  - If you are getting an error with ansible-galaxy run this

          $ sudo pip2 install --upgrade ansible

- After you run any of these commands you must re-build your vagrant box

        $ vagrant reload

- If none of these fix your issues check to see if you missed some configuration [here](https://www.vagrantup.com/docs/cli)

### Debugging

- XDebug is what you need to debug in Trellis. PHPStorm comes with it already installed but you can install it on most code editors.
- In VSCode, install the PHP Debug extension by _Felix Becker_
- Go to the debug window and set up a PHP launch.json file if you don't already have one.
- Once the debug window is set to _Listen for XDebug_, place your breakpoints and hit play. Now when you go through your local URL you will be able to debug from VSCode

  - XDebug comes already installed on Trellis and is set to automatically run on the development environment only

- Server logs

  - Server logs are stored in
    - _/srv/www/iresq.local/logs/access.log_
    - _/srv/www/iresq.local/error.log_
  - You can access the server to view these by running

        $ vagrant ssh

    - The vagrant server must be running for this command to work

  - **Any** server 500 errors or white screen issues should be debugged by viewing the errors logs in \_src/www/iresq.local/logs/

### Mail Testing on your Local machine

- Mailhog can be accessed by going to http://iresq.local:8025 to view any outgoing mail for testing

### Local Database Access

- Download [Sequel Pro](https://www.sequelpro.com/)
- Configure your .vault_pass file (see below documentation for ansible-vault)

- Once your vault password is configured and you've downloaded Sequel Pro run this command in your _/trellis_ directory.

        $ vagrant trellis-sequel open --site iresq.local

  - This should automatically open Sequel Pro and connect to your vagrant box. Voila! you have full development database access.

###

#

## Theme Development

Theme code is located in `iresq.com/site/web/app/themes/iresq-theme`

- Install the composer dependicies and build the assets.

        $ cd site/web/app/themes/iresq-theme
        $ composer install
        $ yarn && yarn build

* Run `yarn start` for live updates at [localhost:3000](localhost:3000)
  - I'd suggest only using this for any styling changes since it includes hot reloading. Other than that use the vagrant server
* To get the assets ready for production run

        $ yarn build:production

  - This is very important so that our CSS, JavaScript, images, font, etc. get minified for the production server.
  - Resulting files will be saved in themes/sage/dist/

#

## Plugin installation and updates

- Plugins are managed through composer.

  - _Do not install plugins through the CMS or by dropping their folders into site/web/app/plugins._
  - Instead use [**wp-packagist**](https://wpackagist.org/). Any plugin found in WordPress Packagist always has a namespace of _wpackagist-plugin_. To add a plugin go to the `site` folder and run

          $ composer require <namespace>/<packagename>
          $ composer update

- Here's an example for akismet. _dev-trunk_ is the plugin version we are going to download (versions can be found on the right hand side of a plugin in wpackagist). This is optional.

        $ composer require "wpackagist-plugin/akismet": "dev-trunk"
        $ composer update

- To update plugins just change the version number in composer.json and then run

        $ composer update

#

## Deployment to our Remote Server

- You will need to setup ssh keys with Kinsta in order to deploy. In the file _/trellis/group_vars/all/users.yml_ you will see that trellis is looking for your public ssh key in either **~/.ssh/id_rsa_personal.pub** or **~/.ssh/id_rsa.pub**

  - You can add more lines under the **keys** section if you want to name your ssh key something else

- To set up your ssh keys for deployment with Trellis

  1.  Navigate to User Settings
  1.  Scroll to the bottom and click _Add SSH key_
  1.  Open terminal and run (replace _id_rsa.pub_ with any other public key name)

          $ pbcopy < ~/.ssh/id_rsa.pub

      - If you don't have any keys set up then run `ssh-keygen` and follow the prompts

  1.  Once you have copied your public ssh key, navigate back to Kinsta and paste in your SSH key.
  1.  Go ahead and test some deployments!

  _Note: it is very important that you only copy keys that end in **.pub**. You never want to upload your private key (**id_rsa**) to any outside source._

* Now that your SSH key is set up, run this command in the **/trellis** directory

        ./bin/deploy.sh <environment> <domain>

  - _Important!_ This command pulls the code from the respective BitBucket branch and pushes it to Kinsta. Therefore, the environment you are trying to deploy must have an up-to-date repository.
  - `<environment>` will be either staging or production. `<domain>` will be the name of the wordpress site in **/staging/wordpress_sites.yml** or **/production/wordpress_sites.yml**

  - To roll back the most recent deployment run

          $ ansible-playbook rollback.yml -e "site=<environemnt>-iresq.com" env=<environment>"

#

## Mail Server

- Mail server SMTP credentials need to be set in _trellis/groups_vars/all/mail.yml_. The password for the SMTP also needs to be set in _trellis/groups_vars/all/vault.yml_ so it can be encrypted.

#

## SSL Certificate

- SSL is enabled for production only in _trellis/group_vars/production/wordpress_sites.yml_. As you can see in that file, our provider is set to **letsencrypt**.

- Kinsta will handle the SSL setup for our site and they automatically renew it, so once it's set we don't need to worry about it again!

#

## Password Encrpytion using ansible-vault

- You might have noticed that all of the _vault.yml_ files under _trellis/group_vars_ look like gibberish. That's the point. These are where our server access keys, database passwords, CMS admin passwords, mail passwords, and other important information is stored and we don't want this available in plain-text on our BitBucket repository.

- Through the use of [**ansible-vault**](https://docs.ansible.com/ansible/latest/user_guide/vault.html) all of our vault.yml files are encrypted using the AES256 algorithm.

- So how do edit these files? You need the master key. If you look in _trellis/ansible.cfg_ you'll see a variable called **vault_password_file**. This variable has a value of **.vault_pass**.

- You must create a file called **.vault_pass** in your local trellis directory and store the master password inside of it. Once that file is created you can safely edit the _vault.yml_ files using the command

        $ ansible-vault edit group_vars/<environment>/vault.yml

- This opens up a CMD text editor. To edit the file type `i` to go into insert mode. When finished hit the `esc` key and then type `:wd`. If you wish to exit without saving anything then type `:!q` instead.

#

## Database and media syncing

- You can easily sync the databases between production, staging and local by using the script written in `/site/scripts/sync-kinsta.sh`

- This script uses the wp aliases in `site/wpi-cli.yml` to use the WP-CLI to sync the databases and media folders.

- The basic command in terminal to run this is

        $ ./sync-kinsta.sh origin target

* Visit the README in `/site/scripts` to view the possible commands to run.

* If I wanted to pull down the live production database and media files, I would run

        $ ./sync-kinsta.sh production development

* And that's it! The script runs a search and replace for the URL on execution, so there's no need for any additional steps after the script is ran.
