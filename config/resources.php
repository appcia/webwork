<?

return array(
    'rm' => array(
        'resources' => array(
            'gallery-item-resource' => array(
                'path' => 'resource/gallery/item/{id}/source.{ext}',
                'type' => array(
                    'thumbnail-mini' => array(
                        'path' => 'resource/gallery/item/{id}/{type}.{ext}',
                        'processor' => array(
                            'class' => 'App\Resource\Processor\Thumbnail',
                            'settings' => array(
                                'width' => 50,
                                'height' => 50
                            )
                        )
                    ),
                    'thumbnail-small' => array(
                        'path' => 'resource/gallery/item/{id}/{type}.{ext}',
                        'processor' => array(
                            'class' => 'App\Resource\Processor\Thumbnail',
                            'settings' => array(
                                'width' => 320,
                                'height' => 240
                            )
                        )
                    ),
                    'thumbnail-big' => array(
                        'path' => 'resource/gallery/item/{id}/{type}.{ext}',
                        'processor' => array(
                            'class' => 'App\Resource\Processor\Thumbnail',
                            'settings' => array(
                                'width' => 800,
                                'height' => 600
                            )
                        )
                    )
                )
            ),
            'auth-user-avatar' => array(
                'path' => 'resource/auth/user/{id}/source.{ext}',
                'type' => array(
                    'thumbnail-mini' => array(
                        'path' => 'resource/auth/user/{id}/{type}.{ext}',
                        'processor' => array(
                            'class' => 'App\Resource\Processor\Thumbnail',
                            'settings' => array(
                                'width' => 50,
                                'height' => 50
                            )
                        )
                    ),
                    'thumbnail-small' => array(
                        'path' => 'resource/auth/user/{id}/{type}.{ext}',
                        'processor' => array(
                            'class' => 'App\Resource\Processor\Thumbnail',
                            'settings' => array(
                                'width' => 320,
                                'height' => 240
                            )
                        )
                    )
                )
            ),
            'auth-group-icon' => array(
                'path' => 'resource/auth/group/{id}/source.{ext}',
                'type' => array(
                    'thumbnail-mini' => array(
                        'path' => 'resource/auth/group/{id}/{type}.{ext}',
                        'processor' => array(
                            'class' => 'App\Resource\Processor\Thumbnail',
                            'settings' => array(
                                'width' => 50,
                                'height' => 50
                            )
                        )
                    ),
                    'thumbnail-small' => array(
                        'path' => 'resource/auth/group/{id}/{type}.{ext}',
                        'processor' => array(
                            'class' => 'App\Resource\Processor\Thumbnail',
                            'settings' => array(
                                'width' => 320,
                                'height' => 240
                            )
                        )
                    )
                )
            )
        )
    )
);