Appcia Framework
================
codename webwork, version 0.5

Main ideas

* light and fast MVC, //simplicity is a solution,//
* modern code using namespaces, dependency injection, closures (PHP >= 5.3.1)
* absolutely non-static, easy to unit test implementing / mock injecting
* unified configuration, all in one place, per app / module
* scalable by modules
* views with multiple block extending

News

**0.5**
* PSR-0 autoloader, structure modified
* composer/packagist support

**0.4** 
* new set of view helpers: baseUrl, serverUrl, routeUrl, asset
* router refactored
* demo with some css
* minor structure fixes

**0.3**
* configurable view helpers
* demo improved (views use same layout by extending, blocks and helpers example usage and more...)
* components creation simplified
* config injection moved outside objects

**0.2**
* route parameters support
* view block mechanism (alpha)

**0.1**
* working framework skeleton

Live example

http://webwork.appcia.linuxpl.info/

Quickstart

To start developing, clone repo:
{{{
git clone git@bitbucket.org:appcia/webwork.git [your_directory]
}}}

Make a virtual host with document root pointing to [your_directory]. If you want to simplify this, I recommend: http://code.google.com/p/virtualhost-sh/

Also you must to create writable directory for logs, temporarily you can just:
{{{
mkdir app/logs -m 0777
}}}

At the end, you have to setup your application by running in command line:
{{{
php index.php -c setup
}}}
It will init your modules and makes public directories accessible (via symlinks).

Questions, cooperation?

Please send me messages on priv.
Also it would be great, if you like this project and wanna cooperate. 
