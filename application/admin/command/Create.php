<?php
namespace app\admin\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\input\Option;
use think\console\Input\Argument;

/**
 * Class Create
 * 创建后台控制器和视图命令
 * php think create [--view=1]
 * --view=1为可选参数,1：创建视图，0默认
 * @package app\admin\command
 */
class Create extends Command
{
    protected function configure()
    {
        $this->setName('create')->setDescription('创建后台控制器组件')->addOption('view',
        null,
        Option::VALUE_REQUIRED,
        'How many messages should be print?',
        0);
        $this->addArgument('controllerName', Argument::REQUIRED); //必传参数:控制器名称
    }

    protected function execute(Input $input, Output $output)
    {
        $controllerName = $input->getArgument('controllerName');
        $catalog = __DIR__."/../controller/";
        $fileName = $catalog.$controllerName.'.php';
        if(file_exists($fileName)){//文件是否存在
            $output->writeln('File already exist');
        }else{
            $code = <<<CODE
<?php

namespace app\admin\controller;

use app\admin\library\\traits\Backend;
use think\Db;
use think\Exception;

class {$controllerName} extends Right
{
    use Backend;

}
CODE;
            file_put_contents($fileName,$code);
            if($input->getOption('view') == 1){//创建视图
                $viewPath = __DIR__."/../view/{$controllerName}/";
                if(!file_exists($viewPath)){
                    mkdir($viewPath);
                    foreach (['add.html','edit.html','index.html'] as $k => $v){
                        file_put_contents($viewPath.$v,"参考member视图");
                    }
                }
            }
            $output->writeln("{$controllerName} created successfully");
        }
    }
}