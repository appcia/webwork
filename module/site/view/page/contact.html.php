<? $this->begin('not-found', 'layout.html.php') ?>

    <? $this->begin('content') ?>

    <div class="row-fluid">

        <div class="span3">

            <?= $this->render('main-menu.html.php') ?>

        </div>

        <div class="span9">

            <h2>Contact</h2>

            <form class="form-horizontal" method="post">
                <div class="control-group">
                    <label class="control-label" for="email">Your e-mail</label>

                    <div class="controls">
                        <input type="text" id="email" placeholder="Email">
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="content">Content</label>

                    <div class="controls">
                        <textarea rows="3" id="content" name="content"></textarea>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">
                        <button type="submit" class="btn btn-primary">Send</button>
                    </div>
                </div>
            </form>

        </div>

    </div>

    <? $this->end() ?>

<? $this->end() ?>
