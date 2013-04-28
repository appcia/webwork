<? $this->begin('not-found', 'module/site/view/layout.html.php') ?>

    <? $this->begin('content') ?>

        <div class="row-fluid">

            <div class="span3">

                <?= $this->render('module/site/view/main-menu.html.php') ?>

            </div>

            <div class="span9">

                <h2>Page not found!</h2>

                <br>

                <p>Verify that URL address in the top of page is correct.</p>

            </div>

        </div>

    <? $this->end() ?>

<? $this->end() ?>
