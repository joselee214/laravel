<?php
return [
    'patchTraitsNamespace' => 'App\Traits',
    'exec' => ['services', 'repositories'],

    'services' => [
        'dirPath' => 'app/Services',
        'fileFilter' => 'Service.php',
        'usePathAsNamePrefix' => true,
        'namespace' => 'App\Services',
        'usePathAsNameSpace'=>true,
        'excludeFiles' => [], //要写相对路径//  YpcUser/YpcUserService.php //后面点改成正则匹配
        'exportTraitsFile' => 'app/Traits/YpcPatchServicesTraits.php',
        'updateMap' => [],
    ],

    'repositories' => [
        'dirPath' => 'app/Repositories',
        'fileFilter' => 'Repository.php',
        'usePathAsNamePrefix' => true,
        'namespace' => '',
        'usePathAsNameSpace' => true,
        'excludeFiles' => [], //要写相对路径//  YpcUser/YpcUserService.php //后面点改成正则匹配
        'exportTraitsFile' => 'app/Traits/YpcPatchRepositoriesTraits.php',
        'updateMap' => [],
    ],

];
