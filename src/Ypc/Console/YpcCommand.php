<?php

namespace Joselee214\Ypc\Console;

use Illuminate\Console\Command;
use Joselee214\Ypc\Patch\Factory;
use Illuminate\Contracts\Config\Repository;

class YpcCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ypc';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '扫描services/repository';

    /**
     * @var \Joselee214\Ypc\Patch\Factory
     */
    protected $models;

    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * Create a new command instance.
     *
     * @param \Joselee214\Ypc\Patch\Factory $models
     * @param \Illuminate\Contracts\Config\Repository $config
     */
    public function __construct(Factory $models, Repository $config)
    {
        parent::__construct();

        $this->models = $models;
        $this->config = $config;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $pconfig = $this->models->config;
//        print_r($pconfig);
        $servicePath = trim($pconfig['servicesPath'],DIRECTORY_SEPARATOR);

        $allServices = $this->scanDir($servicePath,'Service.php',$pconfig['usePathAsServiceNameSapce'],'',$pconfig['excludeServicesFiles']);
        $file = $pconfig['traitsPatchServicesFilePath'];

        $classename = substr(last(explode(DIRECTORY_SEPARATOR,$file)),0,-4);

        $helpfile = substr($pconfig['traitsPatchServicesFilePath'],0,-4).'Helper.php';
        $helpclassename = substr(last(explode(DIRECTORY_SEPARATOR,$helpfile)),0,-4);
$helpcontent = '<?php
namespace '.$pconfig['patchTraitsNamespace'].';
trait '.$helpclassename.' {
';

if( $allServices )
{
    foreach ( $allServices as $sname=>$sv )
    {
        $helpcontent .= '
  /**
   * @var \\'.$pconfig['servicesNamespace'].'\\'.$sv[1].'
   */
  public $'.$sname.';
';
    }
}

        $helpcontent .= '
}
';
    file_put_contents($helpfile,$helpcontent);

//    $mapfile = substr($file,-4).'-mapconfig.php';
//    file_put_contents($mapfile,expor)


$content = '<?php
namespace '.$pconfig['patchTraitsNamespace'].';
/**
 * Trait '.$classename.'
 * @package '.$pconfig['patchTraitsNamespace'].'
 * @mixin \\'.$pconfig['patchTraitsNamespace'].'\\'.$helpclassename.'
 */
trait '.$classename.' {

  public function __get($name)
  {
      if( substr($name,-7)===\'Service\' ){
          return self::singleton_'.$classename.'($name);
      }
  }

  public static $instance_'.$classename.'=[];
  public static function singleton_'.$classename.'($class)
  {
      if( !isset(self::$instance_YpcPatchServicesTraits[$class]) || is_null(self::$instance_YpcPatchServicesTraits[$class]) ){
          if( isset(self::$classmap_'.$classename.'[$class]) && ($setting=self::$classmap_'.$classename.'[$class]) )
          {
              if( !class_exists($setting[0]) ){
                  require_once base_path().DIRECTORY_SEPARATOR.$setting[1];
              }
              self::$instance_YpcPatchServicesTraits[$class] = \App::make($setting[0]);
          }
          else
          {
              throw new \Exception(\'找不到Service...\');
          }
      }
      return self::$instance_YpcPatchServicesTraits[$class];
  }

  protected static $classmap_'.$classename.' = [
  ';

if( $allServices )
{
    foreach ( $allServices as $sname=>$sv )
    {
        $content .= '   \''.$sname.'\'     =>   [\''.$pconfig['servicesNamespace'].'\\'.$sv[1].'\',\''.$sv[2].'\'],
        ';
    }
}

$content .= '  ];
}

';

        file_put_contents($file,$content);

        $this->info("已生成文件 $file 与  $helpfile");
        $this->info('请在代码里(根Controller|...)引入traits : use  \\'.$pconfig['patchTraitsNamespace'].'\\'.$classename);

    }

    public function scanDir($path,$checkFname='Service.php',$pathAsNamePrefix=true,$namePrefix='',$excludeServicesFiles,$passedRelativePath='')
    {
        $result = [];
        $dirArr = scandir($path);
        foreach($dirArr as $v){
            if($v!='.' && $v!='..'){
                if(is_dir($path.DIRECTORY_SEPARATOR.$v)){
                    $namePrefixDir = $pathAsNamePrefix?($v.$namePrefix):$namePrefix;
                    $dirResult = $this->scanDir($path.DIRECTORY_SEPARATOR.$v,$checkFname,$pathAsNamePrefix,$namePrefixDir,$excludeServicesFiles,$v);
                    if( $pathAsNamePrefix )
                    {
                        //需要检查冲突//
                        if( $confilict = array_intersect( array_keys($result),array_keys($dirResult) ) )
                        {
                            echo $checkFname.'冲突:'.PHP_EOL;
                            $this->error('发生命名冲突!!!'.PHP_EOL);
                            print_r($confilict);
                            print_r($dirResult);
                            return;
                        }
                    }
                    $result = array_merge($result,$dirResult);

                } else {
                    if( $checkFname == substr($v,-strlen($checkFname)) )
                    {
                        if( empty($excludeServicesFiles) ||  !in_array(trim($passedRelativePath.DIRECTORY_SEPARATOR.$v,DIRECTORY_SEPARATOR),$excludeServicesFiles) )
                        {
                            $result[ $namePrefix.substr($v,0,-4) ] = [
                                $v, //文件名
                                $passedRelativePath.'\\'.substr($v,0,-4),  //类名
                                $path.DIRECTORY_SEPARATOR.$v    //文件路径
                            ];

                        }
                    }
                }
            }
        }
        return $result;
    }

}
