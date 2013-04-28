<? $this->begin('list', 'layout.html.php') ?>

    <? $this->begin('content') ?>

        <div class="row-fluid">

            <div class="span3">

                <?= $this->render('main-menu.html.php') ?>

            </div>

            <div class="span9">

                <div class="row-fluid">

                    <div class="span12">

                        <div class="page-header">
                            <h2>Users <small>&dash; registered list</small></h2>
                        </div>

                        <? if (empty($users)) : ?>

                            <div class="well">
                                Lack of any users.
                            </div>

                        <? else : ?>

                            <table class="table table-hover">

                                <thead>

                                <tr>
                                    <th>#</th>
                                    <th>Avatar</th>
                                    <th>Full name</th>
                                    <th>E-mail</th>
                                    <th>Groups</th>
                                    <th>Registered</th>
                                    <th>Age</th>
                                    <th>Actions</th>
                                </tr>

                                </thead>

                                <tbody>

                                    <?
                                        $i = 0;
                                        foreach ($users as $user) :
                                            $i++;
                                    ?>

                                        <tr>
                                            <td><?= $i ?></td>
                                            <td>
                                                <a class="fancybox" href="<?= $user->getAvatar() ?>">
                                                    <img class="img-rounded" src="<?= $user->getAvatar()->getType('thumbnail-mini') ?>" alt="">
                                                </a>
                                            </td>
                                            <td><?= $user->getFullname() ?></td>
                                            <td><a href="mailto:<?= $user->getEmail() ?>"><?= $user->getEmail() ?></a></td>
                                            <td><?= $this->join($user->getGroups(), 'name') ?></td>
                                            <td><?= $user->getRegistered()->format('Y-m-d, H:i:s') ?></td>
                                            <td><?= $this->age($user->getBirth()) ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <button class="btn dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
                                                    <ul class="dropdown-menu pull-right">
                                                        <li>
                                                            <a href="<?= $this->routeUrl('cms-user-change-password', array('userId' => $user->getId())) ?>">
                                                                <i class="icon-lock"></i>&nbsp;Change password
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="<?= $this->routeUrl('cms-user-edit', array('userId' => $user->getId())) ?>">
                                                                <i class="icon-pencil"></i>&nbsp;Edit personal data
                                                            </a>
                                                        </li>

                                                        <li class="divider"></li>

                                                        <li>
                                                            <a href="<?= $this->routeUrl('cms-user-remove', array('userId' => $user->getId())) ?>">
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
                            <a class="btn btn-primary" href="<?= $this->routeUrl('cms-user-add') ?>">Add</a>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    <? $this->end() ?>

<? $this->end() ?>