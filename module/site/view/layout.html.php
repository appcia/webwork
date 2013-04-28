<!DOCTYPE html>
<html lang="en">
<head>
    <base href="<?= $this->serverUrl() ?>/">

    <meta charset="utf-8">
    <title>Skeleton application</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Skeleton application - client side">
    <meta name="author" content="Appcia">

    <link href="<?= $this->asset('site/layout/bootstrap/css/bootstrap.css') ?>" rel="stylesheet">
    <link href="<?= $this->asset('site/layout/bootstrap.css') ?>" rel="stylesheet">
    <link href="<?= $this->asset('site/layout/bootstrap/css/bootstrap-responsive.css') ?>" rel="stylesheet">
    <link href="<?= $this->asset('site/layout/styles.css') ?>" rel="stylesheet">

    <!--[if lt IE 9]>
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

</head>

<body>

<div class="container">

    <?= $this->render('flash.html.php') ?>

    <div class="masthead">
        <h3 class="muted">Skeleton application</h3>

        <div class="navbar">
            <div class="navbar-inner">
                <div class="container">
                    <ul class="nav">
                        <li class="active"><a href="<?= $this->routeUrl('site-page-home') ?>">Home</a></li>
                        <li><a href="<?= $this->routeUrl('cms-page-home') ?>">Admin panel</a></li>
                        <li><a href="https://github.com/appcia/webwork" target="_blank">Webwork on GitHub</a></li>
                        <li><a href="https://www.facebook.com/Appcia" target="_blank">Appcia on Facebook</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /.navbar -->
    </div>

    <!-- Jumbotron -->
    <div class="jumbotron">
        <h1>Hello World!</h1>

        <p class="lead">Hurray! You made it! This is basic skeleton for you application based on Appcia Webwork.<br>If
            you want to help popularize our framework, please give us a like on Facebook.</p>
        <a class="btn btn-large btn-success" href="https://github.com/appcia/webwork" target="_blank">Visit webwork
            site!</a>
    </div>

    <hr>

    <!-- Example row of columns -->
    <div class="row-fluid">
        <div class="span4">
            <h2>Phing support</h2>

            <p>Use automated build targets to simplify your work with application.</p>

            <p><a class="btn" href="http://www.phing.info/trac/wiki/Users/Documentation"
                  target="_blank">Documentation &raquo;</a></p>
        </div>
        <div class="span4">
            <h2>Composer vendors</h2>

            <p>If you need to use some vendor library in your application you should:</p>
            <ul>
                <li>Find it in Packagist</li>
                <li>Put require config into composer.json file</li>
                <li>Run composer using command:<br>bin/phing vendor:update</li>
            </ul>
            <p><a class="btn" href="https://packagist.org/" target="_blank">Packagist repository &raquo;</a></p>
        </div>
        <div class="span4">
            <h2>Help & support?</h2>

            <p>For any questions, bugs and improvements, please use dedicated issue tracker on&nbsp;GitHub.</p>

            <p><a class="btn" href="https://github.coma/appcia/webwork/issues" target="_blank">View issues &raquo;</a></p>
        </div>
    </div>

    <hr>

    <div class="footer">
        <p>&copy; Appcia Webwork <?= $this->date(null, 'Y') ?></p>
    </div>

</div>
<!-- /container -->

<script src="<?= $this->asset('site/layout/jquery/jquery-1.9.0.min.js') ?>"></script>
<script src="<?= $this->asset('site/layout/bootstrap/js/bootstrap.js') ?>"></script>
<script src="<?= $this->asset('site/layout/scripts.js') ?>"></script>

</body>
</html>