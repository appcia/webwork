Appcia Webwork
================
PHP Framework, codename 'Webwork'

#### Main ideas
* lightweight and clean MVC - 'simplicity as solution'
* highly configurable
* scalable by independent modules
* simple, native view mechanism with block extending
* easy for unit test implementing, mock injecting
* modern code using namespaces, dependency injection, closures (PHP >= 5.3)
* standarized code via PSR-0, composer

#### Quickstart

Use application skeleton: https://github.com/appcia/webwork/blob/skeleton/README.md

#### Questions, cooperation?

Please send me messages on priv.
Also it would be great, if you like this project and wanna cooperate. 

#### News
**0.8.1**
* config reader / writer interface (easy to implement your own)

**0.8**
* validator improvements + new: DateBetween, DateOverlap, Not, Callback

**0.7.7**
* request tracker
* view extending improved for nesting
* router bugfixes

**0.7.6**
* intl translator (gettext)

**0.7.5**
* framework structure change
* app class rewritten, bootstrap incorporated

**0.7.4**
* view renderers (php, json, xml, ini)
* controller improvements

**0.7.3**
* routes group (for more simple config)

**0.7.2**
* CSRF protection
* locale in context
* slug filter

**0.7.1**
* filter, validators and helpers now use application context (custom configuration changing on the fly)
* minor convention fixes

**0.7**
* resource manager
  * for mapping files (images, pdfs etc) with database rows by parameters like ID or others
  * processing derivatives types based on origin resource (thumbnails in many different sizes, format conversions etc)
  * form extension with resource mapping, file upload with temporary state

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
