<? $this->begin('home', 'layout.html.php') ?>

    <? $this->begin('content') ?>

        <div class="row-fluid">

            <div class="span3">

                <?= $this->render('main-menu.html.php') ?>

            </div>

            <div class="span9">

                <div class="row-fluid">

                    <div class="span12">

                        <div class="page-header">
                            <h2>
                                Users
                                <small>&dash; password change for user &quot;<?= $user->getFullname() ?>&quot;</small>
                            </h2>
                        </div>

                        <form action="<?= $this->routeUrl() ?>" method="post">

                            <div class="row-fluid">

                                <div class="span6">

                                    <div class="control-group <?= !$form->passwordActual->isValid() ? 'error' : '' ?>">
                                        <label class="control-label" for="passwordActual">Actual password</label>
                                        <div class="controls">
                                            <input id="passwordActual" name="passwordActual" type="password">
                                        </div>
                                    </div>

                                    <div class="control-group <?= !$form->password->isValid() ? 'error' : '' ?>">
                                        <label class="control-label" for="password">Password</label>
                                        <div class="controls">
                                            <input id="password" name="password" type="password">
                                        </div>
                                    </div>

                                    <div class="control-group <?= !$form->passwordRepeat->isValid() ? 'error' : '' ?>">
                                        <label class="control-label" for="passwordRepeat">Repeat password</label>
                                        <div class="controls">
                                            <input id="passwordRepeat" name="passwordRepeat" type="password">
                                        </div>
                                    </div>

                                </div>

                            </div>

                            <div class="form-actions">
                                <a class="btn" href="<?= $this->routeUrl('cms-user-list') ?>">Cancel</a>
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    <? $this->end() ?>

<? $this->end() ?>