<?php

use Alexusmai\LaravelFileManager\Services\ConfigService\DefaultConfigRepository;
use Alexusmai\LaravelFileManager\Services\ACLService\ConfigACLRepository;

return [

    'configRepository' => DefaultConfigRepository::class,
    'aclRepository' => ConfigACLRepository::class,
    'routePrefix' => 'file-manager',
    'diskList' => ['public'],
    'leftDisk' => null,
    'rightDisk' => null,
    'leftPath' => null,
    'rightPath' => null,
    'windowsConfig' => 2,
    'maxUploadFileSize' => null,
    'allowFileTypes' => [],
    'hiddenFiles' => true,
    'middleware' => ['web'],
    'acl' => false,
    'aclHideFromFM' => true,
    'aclStrategy' => 'blacklist',
    'aclRulesCache' => null,
    'aclRules' => [
        null => [
        ],
        1 => [
        ],
    ],
    'slugifyNames' => false,
];
