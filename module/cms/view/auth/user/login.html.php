<? $this->begin('login', 'layout.html.php') ?>

    <? $this->begin('content') ?>

        <div class="row-fluid">

            <div class="span12">

                <div class="row-fluid">

                    <div class="span12">

                        <div class="page-header">
                            <h2>
                                Users
                                <small>&dash; logging to admin panel</small>
                            </h2>
                        </div>

                        <p class="well">
                            Default login data:<br>
                            <strong>e-mail:</strong> appcia.dev@gmail.com<br>
                            <strong>password:</strong> qwa2_pp2op2
                        </p>

                        <form action="<?= $this->routeUrl() ?>" method="post">

                            <div class="row-fluid">

                                <div class="span6">

                                    <div class="control-group <?= !$form->email->isValid() ? 'error' : '' ?>">
                                        <label class="control-label" for="email">E-mail</label>
                                        <div class="controls">
                                            <input id="email" name="email" type="email" value="<?= $form->email ?>">
                                        </div>
                                    </div>

                                    <div class="control-group <?= !$form->password->isValid() ? 'error' : '' ?>">
                                        <label class="control-label" for="password">Password</label>
                                        <div class="controls">
                                            <input id="password" name="password" type="password"">
                                        </div>
                                    </div>

                                </div>

                                <div class="span6"></div>

                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Login</button>
                            </div>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    <? $this->end() ?>

<? $this->end() ?>