<? $this->begin('not-found', 'layout.html.php') ?>

    <? $this->begin('content') ?>

        <div class="row-fluid">

            <div class="span3">

                <?= $this->render('main-menu.html.php') ?>

            </div>

            <div class="span9">

                <h2>Page not found!</h2>

                <br>

                <p>Check whether URL address in the top of page is correct.</p>

            </div>

        </div>

    <? $this->end() ?>

<? $this->end() ?>
