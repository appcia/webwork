<? $this->begin('edit', 'layout.html.php') ?>

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
                                <small>
                                    &dash; editing existing user &quot;<?= $user->getFullname() ?>&quot;
                                </small>
                            </h2>
                        </div>

                        <form action="<?= $this->routeUrl() ?>" method="post" enctype="multipart/form-data">

                            <input type="hidden" name="metadata" value="<?= $form->metadata ?>">

                            <div class="row-fluid">

                                <div class="span6">

                                    <div class="control-group <?= !$form->email->isValid() ? 'error' : '' ?>">
                                        <label class="control-label" for="email">E-mail</label>
                                        <div class="controls">
                                            <input id="email" name="email" type="email" value="<?= $form->email ?>">
                                        </div>
                                    </div>


                                    <div class="control-group <?= !$form->group->isValid() ? 'error' : '' ?>">

                                        <div class="control-label">Groups</div>
                                        <div class="controls">

                                            <? foreach ($groups as $group) : ?>

                                                <label class="checkbox inline">
                                                    <input id="group-<?= $group->getId() ?>" name="group[]" type="checkbox" value="<?= $group->getId() ?>" <?= $form->group->contains($group->getId()) ? ' checked ' : '' ?>>
                                                    <?= $group->getName() ?>
                                                </label>

                                            <? endforeach ?>

                                        </div>

                                    </div>

                                    <div class="control-group <?= !$form->birth->isValid() ? 'error' : '' ?>">

                                        <label class="control-label" for="birth">Birth</label>
                                        <div class="controls">

                                            <div class="datepicker input-append date" data-date="" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                <input id="birth" name="birth" class="span3" type="text" value="<?= $this->date($form->birth->getValue(), 'Y-m-d') ?>" readonly="">
                                                <span class="add-on"><i class="icon-calendar"></i></span>
                                            </div>

                                        </div>
                                    </div>

                                </div>

                                <div class="span6">

                                    <div class="control-group <?= !$form->name->isValid() ? 'error' : '' ?>">
                                        <label class="control-label" for="name">Name</label>
                                        <div class="controls">
                                            <input id="name" name="name" type="text" value="<?= $form->name ?>">
                                        </div>
                                    </div>

                                    <div class="control-group <?= !$form->surname->isValid() ? 'error' : '' ?>">
                                        <label class="control-label" for="surname">Surname</label>
                                        <div class="controls">
                                            <input id="surname" name="surname" type="text" value="<?= $form->surname ?>">
                                        </div>
                                    </div>

                                    <div class="control-group <?= !$form->nick->isValid() ? 'error' : '' ?>">
                                        <label class="control-label" for="name">Nick</label>
                                        <div class="controls">
                                            <input id="nick" name="nick" type="text" value="<?= $form->nick ?>">
                                        </div>
                                    </div>

                                    <div class="control-group <?= !$form->avatar->isValid() ? 'error' : '' ?>">
                                        <label class="control-label" for="avatar">Avatar </label>
                                        <div class="controls">

                                                <div class="pull-right">

                                                    <? if ($form->avatar->isEmpty()) : ?>
                                                        <button name="unskip" class="clear-file btn btn-info" value="avatar"><i class="icon-share-alt icon-white"></i>&nbsp;Restore</button>
                                                    <? else : ?>
                                                        <a class="fancybox btn" href="<?= $form->avatar ?>"><i class="icon-zoom-in"></i>&nbsp;Preview</a>
                                                        <button name="skip" class="clear-file btn btn-danger" value="avatar"><i class="icon-trash icon-white"></i>&nbsp;Remove</button>
                                                    <? endif ?>

                                                </div>

                                            <input id="avatar" name="avatar" type="file" class="span6">
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
