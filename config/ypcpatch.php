<?php
return [
    'patchTraitsNamespace' => 'App\Traits',
    'exec' => ['services', 'repositories'],

    'services' => [
        'dirPath' => 'app/Services',
        'fileFilter' => 'Service.php',
        'usePathAsNameSapce' => true,
        'excludeFiles' => [], //要写相对路径//  YpcUser/YpcUserService.php //后面点改成正则匹配
        'exportTraitsFile' => 'app/Traits/YpcPatchServicesTraits.php',
        'namespace' => 'App\Services',
    ],

    'repositories' => [
        'dirPath' => 'app/Repositories',
        'fileFilter' => 'Repository.php',
        'usePathAsNameSapce' => true,
        'excludeFiles' => [], //要写相对路径//  YpcUser/YpcUserService.php //后面点改成正则匹配
        'exportTraitsFile' => 'app/Traits/YpcPatchRepositoriesTraits.php',
        'namespace' => '',
    ],

];
