<?php return array(
    'root' => array(
        'name' => 'meta4creations/post-duplicator',
        'pretty_version' => '2.45',
        'version' => '2.45.0.0',
        'reference' => null,
        'type' => 'wordpress-plugin',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        'meta4creations/mtphr-settings' => array(
            'pretty_version' => 'dev-master',
            'version' => 'dev-master',
            'reference' => '07de9c1c4e0454f6be12e253b04f1e0092ef5962',
            'type' => 'library',
            'install_path' => __DIR__ . '/../meta4creations/mtphr-settings',
            'aliases' => array(
                0 => '9999999-dev',
            ),
            'dev_requirement' => false,
        ),
        'meta4creations/post-duplicator' => array(
            'pretty_version' => '2.45',
            'version' => '2.45.0.0',
            'reference' => null,
            'type' => 'wordpress-plugin',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
    ),
);
