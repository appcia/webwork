<?

return array(

    'app' => array(
        'path' => '',
        'namespace' => 'App'
    ),

    'modules' => array(
        'cms' => array(
            'path' => 'module/cms',
            'namespace' => 'Cms'
        ),
        'site' => array(
            'path' => 'module/site',
            'namespace' => 'Site'
        )
    ),

    'db' => array(
        'driver' => 'pdo_mysql',
        'dbname' => 'skeleton',
        'user' => 'root',
        'password' => 'qwa2_pp2op2',
        'host' => 'localhost'
    ),

    'rm' => array(
        'tempDir' => 'resource/temp',
        'resources' => array(
            'upload' => array(
                'path' => 'resource/upload/{token}_{key}.{ext}',
            )
        )
    )
);