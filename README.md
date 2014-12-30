# The PHP Skeleton App

The PHP Skeleton App creates an object-oriented PHP MVC environment, tailored for rapid development. The environment makes use of the [Slim PHP micro framework](http://slimframework.com/), [Twig](http://twig.sensiolabs.org/) for templating, and [Twitter Bootstrap 3](http://getbootstrap.com/) for the theme.

Out of the box, it provides the ability to set up and run a public-facing website, authenticate to the administrative side, and create users and groups. All of the baseline database tables are created on first run, along with the first "Universal Administrator".

## Features

* **Quick 5-minute installation**

* **Simple configuration**

* **Easy templating with custom views using Twig**

* **Twitter Bootstrap 3.3.x**

    "Carousel" template included for the public website

    "Dashboard" template included for the administrative interface

* **Site Module**

    The public site

* **Authenticate Module**

    With local authentication, out-of-the-box. Oauth schemes coming soon (e.g. Twitter, Google, Facebook, Github).

* **User Account Module**

    For user management, complete with a self-registration form and the ability to reset forgotten passwords.

* **Group Module**

    Assign users to groups for greater control over permissions

* **Dashboard Module**

    Default landing page for the administrative side

* **More coming soon...**

## Requirements (LAMP)

*These requirements are what I have found to be true. It's likely that I may have missed something along the way. If so, please let me know.*

##### Linux
* So far, only tested on Linux Ubuntu 14.04 (trusty)

##### Apache
* Modules: alias, autoindex, deflate, dir, env, headers, mime, mime_magic, negotiation, php5, reqtimeout, rewrite, setenvif, status

##### MySQL

##### PHP >= 5.3
* Extensions: PDO, pdo_mysql, mysql, php5-curl, php5-mcrypt, php5-json

##### Git

##### Composer

#### Environment Check

To check to see if you have all of the necessary components in place, you can run the "Environment Check" script:

```bash
http://YOUR_DOMAIN/webapp_installer/library/env.php
```

* * *

## Get Started

### Install the PHP Skeleton

#### Clone the Repository Into the Web Root

For example: */var/www/*

```bash
git clone git@github.com:ghalusa/PHP-Skeleton-App.git /PATH/TO/YOUR_EMPTY_WEB_ROOT_DIRECTORY/
```

#### Run Composer (non global installation)

```bash
php composer.phar install
```

#### OR... Run Composer (global installation)

```bash
composer install
```

#### Make Sure Apache Has Permissions to Do Stuff
*(This can be changed back after the installation is finished.)*

```bash
sudo chown -R www-data:www-data /var/www
```

## Run the Web App Installer

##### Point your browser to the root of your web environment...

```bash
http://YOUR_DOMAIN/
```

##### Fill Out the Form

You will need:

* A valid email address for the creation of the administrative account.
* The location, username, and password of a MySQL database.

* * *

## Documentation

* Coming soon... <URL_HERE>

## About the Author

The PHP Skeleton App is created and maintained by [Goran Halusa](http://halusanation.com/), a Web Architect, artist, musician, and chef.

### Twitter

Follow [@goranhalusa](http://www.twitter.com/goranhalusa) on Twitter to receive the very latest news and updates about The PHP Skeleton App.

### Disclaimer

The PHP Skeleton App is in active development, and test coverage is continually improving.

* * *

## Open Source License

The PHP Skeleton App is released under the MIT public license.
