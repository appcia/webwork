<? $this->begin('home', 'layout.html.php') ?>

    <? $this->begin('content') ?>

        <div class="row-fluid">

            <div class="span3">

                <?= $this->render('main-menu.html.php') ?>

            </div>

            <div class="span9">

                <div class="hero-unit">
                    <h1>Welcome in administration panel!</h1>

                    <p>This is a demo for webwork features and coding standards.</p>

                    <p><a class="btn btn-primary btn-large">More&raquo;</a></p>
                </div>

                <div class="row-fluid">

                    <div class="span4">
                        <h2>Activity feed</h2>

                        <p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor
                            mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada
                            magna mollis euismod. Donec sed odio dui. </p>

                        <p><a class="btn" href="#">View details &raquo;</a></p>
                    </div>

                    <div class="span4">
                        <h2>Others</h2>

                        <p>Ut fermentum massa justo sit amet risus. Etiam porta sem malesuada
                            magna mollis euismod. Donec sed odio dui. Porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor
                            mauris condimentum nibh. </p>

                        <p><a class="btn" href="#">View details &raquo;</a></p>
                    </div>

                    <div class="span4">
                        <h2>Statistics</h2>

                        <ul>
                            <li>Cognosce te ipsum ablar</li>
                            <li>Nontengo fusce amis</li>
                        </ul>
                    </div>

                </div>

            </div>

        </div>

    <? $this->end() ?>

<? $this->end() ?>