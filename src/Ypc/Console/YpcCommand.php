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
    protected $signature = 'zz';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '扫描services/repository生成辅助结构';

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
        $allconfig = $this->models->config;
        if( isset($allconfig['exec']) && $allconfig['exec'] )
        {
            foreach ($allconfig['exec'] as $c)
            {
                $pconfig = $allconfig[$c];

                $this->handleExportFile(
                    $allconfig['patchTraitsNamespace'],
                    $pconfig['dirPath'],
                    $pconfig['fileFilter'],
                    $pconfig['usePathAsNamePrefix'],
                    $pconfig['usePathAsNameSpace'],
                    $pconfig['excludeFiles'],
                    $pconfig['exportTraitsFile'],
                    $pconfig['namespace'],
                    isset($pconfig['updateMap'])?$pconfig['updateMap']:[]
                  );
            }
        }
    }

    public function handleExportFile($patchTraitsNamespace,$dirPath,$fileFilter,$usePathAsNamePrefix,$usePathAsNameSpace,$excludeFiles,$exportTraitsFile,$namespace,$updateMap=[])
    {

        $dirPath = trim($dirPath,DIRECTORY_SEPARATOR);

        $allHandled = $this->scanDir($dirPath,$fileFilter,$usePathAsNamePrefix,$usePathAsNameSpace,'',$excludeFiles);
        $lengthofFilter = strlen($fileFilter);

        if($updateMap)
            $allHandled = array_merge($allHandled,$updateMap);

        $file = $exportTraitsFile;

        $explode1 = explode(DIRECTORY_SEPARATOR,$file);
        $classename = substr(end($explode1),0,-4);

        $helpfile = substr($exportTraitsFile,0,-4).'Helper.php';
        $explode2 = explode(DIRECTORY_SEPARATOR,$helpfile);
        $helpclassename = substr(end($explode2),0,-4);
        $helpcontent = '<?php
namespace '.$patchTraitsNamespace.';
trait '.$helpclassename.' {
';

        if( $allHandled )
        {
            foreach ( $allHandled as $sname=>$sv )
            {
                $helpcontent .= '
  /**
   * @var '.($namespace?'\\'.trim($namespace,'\\'):'').'\\'.trim($sv[1],'\\').'
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
namespace '.$patchTraitsNamespace.';
/**
 * Trait '.$classename.'
 * @package '.$patchTraitsNamespace.'
 * @mixin \\'.$patchTraitsNamespace.'\\'.$helpclassename.'
 */
trait '.$classename.' {

  public function __get($name)
  {
      if( substr($name,-'.($lengthofFilter-4).')===\''.substr($fileFilter,0,-4).'\' ){
          return self::singleton_'.$classename.'($name);
      }
  }

  public static $instance_'.$classename.'=[];
  public static function singleton_'.$classename.'($class)
  {
      if( !isset(self::$instance_'.$classename.'[$class]) || is_null(self::$instance_'.$classename.'[$class]) ){
          if( isset(self::$classmap_'.$classename.'[$class]) && ($setting=self::$classmap_'.$classename.'[$class]) )
          {
              if( !class_exists($setting[0]) ){
                  require_once base_path().DIRECTORY_SEPARATOR.$setting[1];
              }
              self::$instance_'.$classename.'[$class] = \App::make($setting[0]);
          }
          else
          {
              throw new \Exception(\'找不到 ...\'.$class.\'...\');
          }
      }
      return self::$instance_'.$classename.'[$class];
  }

  protected static $classmap_'.$classename.' = [
  ';

        if( $allHandled )
        {
            foreach ( $allHandled as $sname=>$sv )
            {
                $content .= '   \''.$sname.'\'     =>   [\''.($namespace?'\\'.trim($namespace,'\\'):'').'\\'.trim($sv[1],'\\').'\',\''.$sv[2].'\'],
  ';
            }
        }

        $content .= '  ];
}

';

        file_put_contents($file,$content);

        $this->info("已生成文件 $file 与  $helpfile");
        $this->info('请在代码里(根Controller|...)引入traits : use  \\'.$patchTraitsNamespace.'\\'.$classename);

    }

    public function scanDir($path,$checkFname,$pathAsNamePrefix=true,$usePathAsNameSpace=true,$namePrefix='',$excludeHandledFiles,$passedRelativePath='')
    {
        $result = [];
        $dirArr = scandir($path);
        foreach($dirArr as $v){
            if($v!='.' && $v!='..'){
                if(is_dir($path.DIRECTORY_SEPARATOR.$v)){
                    $namePrefixDir = $pathAsNamePrefix?($namePrefix.$v):$namePrefix;
                    $dirResult = $this->scanDir($path.DIRECTORY_SEPARATOR.$v,$checkFname,$pathAsNamePrefix,$usePathAsNameSpace,$namePrefixDir,$excludeHandledFiles,$passedRelativePath.DIRECTORY_SEPARATOR.$v);
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
                        if( empty($excludeHandledFiles) ||  !in_array(trim($passedRelativePath.DIRECTORY_SEPARATOR.$v,DIRECTORY_SEPARATOR),$excludeHandledFiles) )
                        {
                            $result[ $namePrefix.substr($v,0,-4) ] = [
                                $v, //文件名
                                ($usePathAsNameSpace? str_replace(DIRECTORY_SEPARATOR,'\\',$passedRelativePath).'\\':'').substr($v,0,-4), //类名
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
