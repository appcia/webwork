<?

return array(
    'router' => array(
        'routes' => array(
            // Pages
            array(
                'name' => 'cms-page-home',
                'path' => '/cms',
                'module' => 'cms',
                'controller' => 'page',
                'action' => 'home'
            ),
            // Users
            array(
                'name' => 'cms-user-login',
                'path' => '/cms/users/login',
                'module' => 'cms',
                'controller' => 'auth/user',
                'action' => 'login'
            ),
            array(
                'name' => 'cms-user-logout',
                'path' => '/cms/users/logout',
                'module' => 'cms',
                'controller' => 'auth/user',
                'action' => 'logout'
            ),
            array(
                'name' => 'cms-user-change-password',
                'path' => '/cms/users/change-password/{userId}',
                'module' => 'cms',
                'controller' => 'auth/user',
                'action' => 'changePassword'
            ),
            array(
                'name' => 'cms-user-list',
                'path' => '/cms/users',
                'module' => 'cms',
                'controller' => 'auth/user',
                'action' => 'list'
            ),
            array(
                'name' => 'cms-user-add',
                'path' => '/cms/users/add',
                'module' => 'cms',
                'controller' => 'auth/user',
                'action' => 'add'
            ),
            array(
                'name' => 'cms-user-edit',
                'path' => '/cms/users/edit/{userId}',
                'module' => 'cms',
                'controller' => 'auth/user',
                'action' => 'edit'
            ),
            array(
                'name' => 'cms-user-remove',
                'path' => '/cms/users/remove/{userId}',
                'module' => 'cms',
                'controller' => 'auth/user',
                'action' => 'remove'
            ),
            // Groups
            array(
                'name' => 'cms-group-list',
                'path' => '/cms/groups',
                'module' => 'cms',
                'controller' => 'auth/group',
                'action' => 'list'
            ),
            array(
                'name' => 'cms-group-add',
                'path' => '/cms/groups/add',
                'module' => 'cms',
                'controller' => 'auth/group',
                'action' => 'add'
            ),
            array(
                'name' => 'cms-group-edit',
                'path' => '/cms/groups/edit/{groupId}',
                'module' => 'cms',
                'controller' => 'auth/group',
                'action' => 'edit'
            ),
            array(
                'name' => 'cms-group-remove',
                'path' => '/cms/groups/remove/{groupId}',
                'module' => 'cms',
                'controller' => 'auth/group',
                'action' => 'remove'
            )
        )
    )
);