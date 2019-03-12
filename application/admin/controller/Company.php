<?php

namespace app\admin\controller;

use think\Db;
use think\Request;
use app\admin\model\Company as CompanyModel;

class Company extends Right
{
    /**
     * 查
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $data = CompanyModel::order('id desc')->paginate(10);
        return returnRes($data->toArray()['data'],'没有公司数据，请添加后重试',$data);
    }

    /**
     * 增
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function add(Request $request)
    {
        if($request->isPost()){
            $params = $request->param();
            if(Db::table('company')->where(['name' => $params['name']])->find()){
                return returnFail('企业名称不能重复');
            }else{
                $company = new CompanyModel($params);
                $res = $company->allowField(true)->save();
                return returnRes($res,'企业添加失败');
            }
        }
    }

    /**
     * 改
     * @param Request $request
     * @return \think\response\Json
     */
    public function update(Request $request)
    {
        if($request->isPut()){
            $params = $request->param();
            $company = new CompanyModel();
            $res = $company->allowField(true)->save($params,['id' => $params['id']]);
            return returnRes($res,'企业数据编辑失败或数据没有修改');
        }
    }

    /**
     * 删
     * @param Request $request
     * @return \think\response\Json
     */
    public function delete(Request $request)
    {
        if($request->isPost()){
            $res = CompanyModel::destroy($request->param('id'));
            return returnRes($res,'企业数据删除失败');
        }
    }
}