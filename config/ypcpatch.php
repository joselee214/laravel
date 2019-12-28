<?php
return [
    'traitsPatchServicesFilePath' => 'app/Traits/YpcPatchServicesTraits.php',
    'patchTraitsNamespace' => 'App\Traits',
    'servicesPath' => 'app/Services',
    'excludeServicesFiles'=>[
        'zzz','zzz' //要写相对路径//  YpcUser/YpcUserService.php
    ],
    'usePathAsServiceNameSapce'=>true, //true:新建的Service 会把路径放到namespace里，生成的traits/PatchServices.php 也是把路径放在前面...

    'servicesNamespace' => 'App\Services',

    'servicesTemplate'=>'', //

];
