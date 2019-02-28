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
 * php think create 控制器名称 [--view]
 * --view为可选参数
 * @package app\admin\command
 */
class Create extends Command
{
    protected function configure()
    {
        $this->setName('create')->setDescription('创建后台控制器组件')->addOption('view',
            'view',
            Option::VALUE_OPTIONAL,
            'would you like to create view?',
            1);
        $this->addArgument('controllerName', Argument::REQUIRED); //必传参数:控制器名称
    }

    protected function execute(Input $input, Output $output)
    {
        $controllerName = $input->getArgument('controllerName');
        $controllerPath = __DIR__."/../controller/";
        $modelPath = __DIR__."/../model/";
        $fileName = $controllerPath.$controllerName.'.php';
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

            $model_code = <<<MODELCODE
<?php

namespace app\admin\model;

class {$controllerName} extends Base
{
    // 验证规则
    public \$rules = [
        
    ];

    // 验证错误信息
    public \$msg = [
        
    ];

    // 场景
    public \$scene = [
        
    ];

    // 表单-数据表字段映射
    public \$map = [
       
    ];
}

MODELCODE;

            file_put_contents($fileName,$code);//创建控制器
            file_put_contents($modelPath.$controllerName.'.php',$model_code);//创建model
            if($input->getOption('view') == 1){//创建视图
                $viewDirName = strtolower($controllerName);
                $viewPath = __DIR__."/../view/{$viewDirName}/";
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