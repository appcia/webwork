<!DOCTYPE html>
<html lang="en">
<head>
    <base href="<?= $this->serverUrl() ?>/">

    <meta charset="utf-8">
    <title>Appcia CMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Skeleton application">
    <meta name="author" content="Appcia">

    <link href="<?= $this->asset('cms/layout/bootstrap/css/bootstrap.css') ?>" rel="stylesheet">
    <link href="<?= $this->asset('cms/layout/bootstrap.css') ?>" rel="stylesheet">
    <link href="<?= $this->asset('cms/layout/bootstrap/css/bootstrap-responsive.css') ?>" rel="stylesheet">
    <link href="<?= $this->asset('cms/layout/bootstrap/css/datepicker.css') ?>" rel="stylesheet">
    <link href="<?= $this->asset('cms/layout/jquery/fancybox/fancybox.css') ?>" rel="stylesheet">
    <link href="<?= $this->asset('cms/layout/bootstrap/css/wysihtml5.css') ?>" rel="stylesheet">
    <link href="<?= $this->asset('cms/layout/styles.css') ?>" rel="stylesheet">

    <!--[if lt IE 9]>
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

</head>

<body>

<? $this->begin('navbar') ?>

    <div class="navbar navbar-inverse navbar-fixed-top">
        <div class="navbar-inner">

            <div class="container-fluid">

                <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse"> <span class="icon-bar"></span>
                    <span class="icon-bar"></span> <span class="icon-bar"></span> </a> <a class="brand" href="#">Skeleton application</a>

                <div class="nav-collapse collapse">

                    <? $this->begin('navbar-data') ?>

                        <? if ($this->get('auth')->isAuthorized()) :
                            $user = $this->get('auth')->getUser()
                        ?>

                            <p class="navbar-text pull-right">
                                Logged as <?= $user->getFullname() ?>
                            </p>

                        <? else : ?>

                            <p class="navbar-text pull-right">
                                Login to access admin panel&hellip;
                            </p>

                        <? endif ?>

                        <ul class="nav">
                            <li><a href="<?= $this->routeUrl('site-page-home') ?>">Client side</a></li>
                        </ul>

                    <? $this->end() ?>

                </div>

            </div>

        </div>
    </div>

<? $this->end() ?>

<div class="container-fluid">

    <?= $this->render('flash.html.php') ?>

    <? $this->block('content') ?>

    <hr>

    <? $this->begin('footer') ?>

        <footer class="row-fluid">

            <div class="span12">

                <p>Appcia &reg; 2012-<?= $this->date(null, 'Y') ?></p>

            </div>

        </footer>

    <? $this->end() ?>

</div>

<script src="<?= $this->asset('cms/layout/jquery/core-1.9.0.min.js') ?>"></script>
<script src="<?= $this->asset('cms/layout/wysihtml5.min.js') ?>"></script>

<script src="<?= $this->asset('cms/layout/bootstrap/js/bootstrap.js') ?>"></script>
<script src="<?= $this->asset('cms/layout/bootstrap/js/datepicker.js') ?>"></script>
<script src="<?= $this->asset('cms/layout/jquery/fancybox/fancybox.js') ?>"></script>
<script src="<?= $this->asset('cms/layout/bootstrap/js/wysihtml5.js') ?>"></script>
<script src="<?= $this->asset('cms/layout/scripts.js') ?>"></script>

</body>
</html>