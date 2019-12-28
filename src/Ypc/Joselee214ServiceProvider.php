<?php

namespace Joselee214\Ypc;

use Joselee214\Ypc\Console\YpcCommand;
use Joselee214\Support\Classify;
use Joselee214\Ypc\Model\Config;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Joselee214\Ypc\Console\YpcModelsCommand;
use Joselee214\Ypc\Model\Factory as ModelFactory;
use Joselee214\Ypc\Patch\Factory as PatchFactory;

class Joselee214ServiceProvider extends ServiceProvider
{
    /**
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
//            $this->publishes([
//                __DIR__.'/../../config/ypcmodels.php' => config_path('ypcmodels.php'),
//                __DIR__.'/../../config/pycpatch.php' => config_path('pycpatch.php'),
//            ], 'Joselee214');

            $this->commands([
                YpcCommand::class,
                YpcModelsCommand::class,
            ]);
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerPatchFactory();
        $this->registerModelFactory();
    }

    /**
     * Register Model Factory.
     *
     * @return void
     */
    protected function registerModelFactory()
    {
        $this->app->singleton(ModelFactory::class, function ($app) {

            $config = $app->make('config')->get('ypcmodels');
            if(is_null($config)){
                $config = include_once __DIR__.'/../../config/ypcmodels.php';
            }

            return new ModelFactory(
                $app->make('db'),
                $app->make(Filesystem::class),
                new Classify(),
                new Config($config)
            );
        });
    }


    /**
     * Register {Patch} Factory.
     *
     * @return void
     */
    protected function registerPatchFactory()
    {
        $this->app->singleton(PatchFactory::class, function ($app) {

            $config = $app->make('config')->get('ypcpatch');
            if(is_null($config)){
                $config = include_once __DIR__.'/../../config/ypcpatch.php';
            }
            return new PatchFactory(
                $config
            );
        });
    }

    /**
     * @return array
     */
    public function provides()
    {
        return [PatchFactory::class,ModelFactory::class];
    }
}
