<? $this->begin('home', 'module/site/view/layout.html.php') ?>

    <? $this->begin('content') ?>

        <div class="row-fluid">

            <div class="span3">

                <?= $this->render('module/site/view/main-menu.html.php') ?>

            </div>

            <div class="span9">

                <div class="hero-unit">
                    <h1>Welcome to Appcia Webwork!</h1>

                    <p>This is a basic skeleton for your application.</p>

                    <p><a class="btn btn-primary btn-large" href="http://github.com/appcia/webwork" target="_blank">Framework official site &raquo;</a></p>
                </div>

                <div class="row-fluid">

                    <div class="span4">
                        <h2>Example content</h2>

                        <p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor
                            mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada
                            magna mollis euismod. Donec sed odio dui. </p>

                        <p><a class="btn" href="#">View details &raquo;</a></p>
                    </div>

                    <div class="span4">
                        <h2>Another</h2>

                        <p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor
                            mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada
                            magna mollis euismod. Donec sed odio dui. </p>

                        <p><a class="btn" href="#">View details &raquo;</a></p>
                    </div>

                    <div class="span4">
                        <h2>Some box</h2>

                        <ul>
                            <li>Cognosce te ipsum ablar</li>
                            <li>Lorem ipsum</li>
                            <li>Amis nontengo</li>
                        </ul>
                    </div>

                </div>

            </div>

        </div>

    <? $this->end() ?>

<? $this->end() ?>