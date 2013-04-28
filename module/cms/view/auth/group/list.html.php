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
                            <h2>Groups <small>&dash; access level list</small></h2>
                        </div>

                        <? if (empty($groups)) : ?>

                            <div class="well">
                                Lack of any groups.
                            </div>

                        <? else : ?>

                            <table class="table table-hover">

                                <thead>

                                <tr>
                                    <th>#</th>
                                    <th>Icon</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>

                                </thead>

                                <tbody>

                                    <?
                                    $i = 0;
                                    foreach ($groups as $group) :
                                        $i++;
                                    ?>

                                    <tr>
                                        <td><?= $i ?></td>
                                        <td>
                                            <a class="fancybox" href="<?= $group->getIcon() ?>">
                                                <img class="img-rounded" src="<?= $group->getIcon()->getType('thumbnail-mini') ?>" alt="">
                                            </a>
                                        </td>
                                        <td><?= $group->getName() ?></td>
                                        <td><?= $group->getDescription() ?></td>
                                        <td><?= $group->getCreated()->format('Y-m-d, H:i:s') ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
                                                <ul class="dropdown-menu pull-right">
                                                    <li>
                                                        <a href="<?= $this->routeUrl('cms-group-edit', array('groupId' => $group->getId())) ?>">
                                                            <i class="icon-pencil"></i>&nbsp;Edit
                                                        </a>
                                                    </li>

                                                    <li class="divider"></li>

                                                    <li>
                                                        <a href="<?= $this->routeUrl('cms-group-remove', array('groupId' => $group->getId())) ?>">
                                                            <i class="icon-trash"></i>&nbsp;Remove
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>

                                    <? endforeach ?>

                                </tbody>

                            </table>

                        <? endif ?>

                        <div class="form-actions">
                            <a class="btn btn-primary" href="<?= $this->routeUrl('cms-group-add') ?>">Add</a>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    <? $this->end() ?>

<? $this->end() ?>