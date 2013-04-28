<? $this->begin('error', 'module/site/view/layout.html.php') ?>

    <? $this->begin('content') ?>

        <div class="row-fluid">

            <div class="span3">

                <?= $this->render('module/site/view/main-menu.html.php') ?>

            </div>

            <div class="span9">

                <h2>Application error!</h2>

                <br>

                <p><strong>File:</strong> <?= $error->getFile() ?>:<?= $error->getLine() ?></p>
                <p><strong>Message:</strong> <?= $error->getMessage() ?></p>
                <p><strong>Error code:</strong> <?= $error->getCode() ?></p>

                <p><strong>Stack trace:</strong></p>
                <pre><?= $error->getTraceAsString() ?></pre>

            </div>

        </div>

    <? $this->end() ?>

<? $this->end() ?>
