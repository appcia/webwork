<?

return array(
    'router' => array(
        'routes' => array(
            array(
                'name' => 'error-404',
                'path' => '/not-found',
                'module' => 'site',
                'controller' => 'error',
                'action' => 'notFound'
            ),
            array(
                'name' => 'error-500',
                'path' => '/error',
                'module' => 'site',
                'controller' => 'error',
                'action' => 'error'
            ),
            array(
                'name' => 'site-page-home',
                'path' => '/',
                'module' => 'site',
                'controller' => 'page',
                'action' => 'home'
            ),
            array(
                'name' => 'site-page-contact',
                'path' => '/contact',
                'module' => 'site',
                'controller' => 'page',
                'action' => 'contact'
            )
        )
    )
);