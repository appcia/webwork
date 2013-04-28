Appcia Webwork
================
Application skeleton

#### Quickstart

Clone skeleton branch:
``
git clone https://github.com/appcia/webwork.git ~/Sites/skeleton.dev
``
Install phing and other vendor libraries:
``
bin/install
``
Set environmental variable for target application environment
``
export APPLICATION_ENV=dev
``
Verify settings for your environment in file: config/APPLICATION_ENV/settings.php
On default, you should have created database with name 'skeleton', user 'root', password 'qwa2_pp2op2'.

Build your environment using Phing target:
``
bin/phing project:build
``

If you need to create virtual host for your app you could:
``
sudo su
``
``
export APPLICATION_ENV=dev
``
bin/phing vhost:create
``
Proably you should also fix permission problems with user running www-server (for file uploads, temporary files, caches):
``
bin/phin filesystem:fix-permissions
``

That's all. Please report any problems via issue tracker: https://github.com/appcia/webwork/issues

#### Questions, cooperation?

Please send me messages on priv.
Also it would be great, if you like this project and wanna cooperate. 
