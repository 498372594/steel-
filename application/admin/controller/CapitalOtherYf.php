<?php


namespace app\admin\controller;


use app\admin\validate\CapitalOtherDetails;
use Exception;
use think\Db;
use think\db\Query;
use think\Request;
use think\response\Json;

class CapitalOtherYf extends Right
{
    /**
     * 添加其他应付
     * @param Request $request
     * @return Json
     */
    public function add(Request $request)
    {
        $data = $request->post();
        Db::startTrans();
        try {
            $validate = new \app\admin\validate\CapitalOtherYf();
            if (!$validate->check($data)) {
                throw new Exception($validate->getError());
            }
            $companyid = $this->getCompanyId();

            $addList = [];
            $updateList = [];
            $ja = $data['details'];
            if (!empty($ja)) {
                $num = 1;
                $detailsValidate = new CapitalOtherDetails();
                foreach ($ja as $object) {
                    if (!$detailsValidate->check($object)) {
                        throw new Exception('请检查第' . $num . '行' . $detailsValidate->getError());
                    }
                    $num++;

                    $object['companyid'] = $companyid;

                    if (empty($object['id'])) {
                        $addList[] = $object;
                    } else {
                        $updateList[] = $object;
                    }
                }
            }


            if (empty($data['id'])) {

                $count = \app\admin\model\CapitalOther::withTrashed()
                    ->whereTime('create_time', 'today')
                    ->where('fangxiang', 2)
                    ->where('companyid', $companyid)
                    ->count();

                $data['create_operator_id'] = $this->getAccountId();
                $data['fangxiang'] = 2;
                $data['system_number'] = 'QTYFK' . date('Ymd') . str_pad(++$count, 3, 0, STR_PAD_LEFT);
                $data['yw_type'] = 1;
                $data['companyid'] = $companyid;

                $qt = new \app\admin\model\CapitalOther();
                $qt->allowField(true)->data($data)->save();

            } else {
                $qt = \app\admin\model\CapitalOther::get($data['id']);
                if (empty($qt)) {
                    throw new Exception("对象不存在");
                }
                if ($qt['status'] == 2) {
                    throw new Exception("该单据已经作废");
                }
                $data['update_operator_id'] = $this->getAccountId();
                $qt->allowField(true)->isUpdate(true)->save($data);
            }

            if (!empty($data['deleteMxIds'])) {
                \app\admin\model\CapitalOtherDetails::destroy(function (Query $query) use ($data) {
                    $query->where('id', 'in', $data['deleteMxIds']);
                });
            }

            foreach ($addList as $mjo) {
                $mjo['companyid'] = $companyid;
                $mjo['cap_qt_id'] = $qt['id'];
                $mx = new \app\admin\model\CapitalOtherDetails();
                $mx->allowField(true)->data($mjo)->save();
            }

            foreach ($updateList as $mjo) {
                $mx = new \app\admin\model\CapitalOtherDetails();
                $mx->isUpdate(true)->allowField(true)->save($mjo);
            }

            $sumMoney = \app\admin\model\CapitalOtherDetails::where('cap_qt_id', $qt['id'])->sum('money');
            $qt->money = $sumMoney;
            $qt->save();
            Db::commit();
            return returnSuc();
        } catch (Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }
}