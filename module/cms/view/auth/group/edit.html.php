<? $this->begin('add', 'layout.html.php') ?>

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
                                Groups
                                <small>&dash; editing existing group&quot;<?= $group->getName() ?>&quot;</small>
                            </h2>
                        </div>

                        <form action="<?= $this->routeUrl() ?>" method="post" enctype="multipart/form-data">

                            <input type="hidden" name="metadata" value="<?= $form->metadata ?>">

                            <div class="row-fluid">

                                <div class="span6">

                                    <div class="control-group <?= !$form->name->isValid() ? 'error' : '' ?>">
                                        <label class="control-label" for="name">Name</label>
                                        <div class="controls">
                                            <input id="name" name="name" type="text" value="<?= $form->name ?>">
                                        </div>
                                    </div>

                                    <div class="control-group <?= !$form->description->isValid() ? 'error' : '' ?>">
                                        <label class="control-label" for="description">Description</label>
                                        <div class="controls">
                                            <input id="description" name="description" type="text" value="<?= $form->description ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="control-group <?= !$form->icon->isValid() ? 'error' : '' ?>">
                                        <label class="control-label" for="icon">Icon</label>
                                        <div class="controls">

                                            <div class="pull-right">

                                                <? if ($form->icon->isEmpty()) : ?>
                                                    <button name="unskip" class="clear-file btn btn-info" value="icon"><i class="icon-share-alt icon-white"></i>&nbsp;Restore</button>
                                                <? else : ?>
                                                    <a class="fancybox btn" href="<?= $form->icon ?>"><i class="icon-zoom-in"></i>&nbsp;Preview</a>
                                                    <button name="skip" class="clear-file btn btn-danger" value="icon"><i class="icon-trash icon-white"></i>&nbsp;Remove</button>
                                                <? endif ?>

                                            </div>

                                            <input id="icon" name="icon" type="file" class="span6">
                                        </div>

                                    </div>

                                </div>

                                <div class="span6"></div>

                            </div>

                            <div class="form-actions">
                                <a class="btn" href="<?= $this->routeUrl('cms-group-list') ?>">Cancel</a>
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    <? $this->end() ?>

<? $this->end() ?>