Appcia Webwork
================
PHP Framework, codename 'Webwork', version 0.6

#### Main ideas

* very lightweight and clean MVC - 'simplicity as solution'
* modern code using namespaces, dependency injection, closures (PHP >= 5.3)
* standarized code via PSR-0, composer
* easy for unit test implementing, mock injecting / free from static methods etc
* scalable by modules
* views with block extending

#### Quickstart

To start developing, clone application skeleton from repo:
```
git clone git@bitbucket.org:appcia/webwork.git [your_directory] -b skeleton
```
* Create a virtual host with document root pointing to your directory. If you want to simplify this, I recommend: http://code.google.com/p/virtualhost-sh/ill 
* Make public directories accessible (via symlinks): module/[name]/public -> public/[name]
* Run (download if you do not have) composer to satisfy framework dependency:
```
composer update
```

#### Questions, cooperation?

Please send me messages on priv.
Also it would be great, if you like this project and wanna cooperate. 

#### News

**0.6**
* dispatcher events, useful for listening (error handling, authorization)
* unit tests (in progress)

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

#### Live example (old)
http://webwork.appcia.linuxpl.info/
