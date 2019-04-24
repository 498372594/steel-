<?php

namespace app\admin\controller;

use app\admin\validate\InvXskpHx;
use Exception;
use think\{Db,
    db\exception\DataNotFoundException,
    db\exception\ModelNotFoundException,
    exception\DbException,
    Request,
    response\Json};

class Invxskp extends Right
{
    /**
     * 添加销售开票单
     * @param Request $request
     * @return Json
     */
    public function add(Request $request)
    {
        if (!$request->isPost()) {
            return returnFail('请求方式错误');
        }

        Db::startTrans();
        try {
            $data = $request->post();
            $validate = new \app\admin\validate\InvXskp();
            if (!$validate->check($data)) {
                throw new Exception($validate->getError());
            }

            $addList = [];
            $updateList = [];
            $ja = $data['details'];
            if (!empty($ja)) {
                $mxValidate = new InvXskpHx();
                foreach ($ja as $object) {

                    if (!$mxValidate->check($object)) {
                        throw new Exception($mxValidate->getError());
                    }

                    if (empty($object['id'])) {
                        $addList[] = $object;
                    } else {
                        $updateList[] = $object;
                    }
                }
            }

            if (empty($data['id'])) {
                $xskp = new \app\admin\model\InvXskp();

                $count = \app\admin\model\InvXskp::withTrashed()->where('company_id', $this->getCompanyId())->whereTime('create_time', 'today')->count();

                $data['system_number'] = 'XSKP' . date('Ymd') . str_pad($count, 3, 0, STR_PAD_LEFT);
                $data['create_operator_id'] = $this->getAccountId();
                $data['company_id'] = $this->getCompanyId();
                $xskp->allowField(true)->data($data)->save();
            } else {
                $xskp = \app\admin\model\InvXskp::get($data['id']);
                if (empty($xskp)) {
                    throw new Exception("对象不存在");
                }
                if ($xskp->company_id != $this->getCompanyId()) {
                    throw new Exception("对象不存在");
                }
                if ($xskp['status'] == 2) {
                    throw new Exception("该单据已经作废");
                }
                if (!empty($xskp['jcx_id'])) {
                    throw new Exception("该单据只读状态,不能修改");
                }
                $xskp->allowField(true)->save($data);
            }


            if (!empty($data['deleteMxIds'])) {
                if (is_string($data['deleteMxIds'])) {
                    $data['deleteMxIds'] = explode(',', $data['deleteMxIds']);
                }
                foreach ($data['deleteMxIds'] as $string) {
                    $mx = \app\admin\model\InvXskpHx::get($string);
                    $inv = new \app\admin\model\Inv();
                    if (!empty($mx['data_id'])) {
                        $inv->jianMoney($mx['data_id'], $mx['sum_shui_price'], $mx['zhongliang']);
                    }
                    $mx->delete();
                }
            }

            foreach ($updateList as $mjo) {
                $hx = \app\admin\model\InvXskpHx::get($mjo['id']);
                if (empty($hx['data_id'])) {
                    $inv->tiaoMoney($hx['dta_id'], $hx['sum_shui_price'], $mjo['sum_shui_price'], $hx['zhongliang'], $mjo['zhongliang']);
                }
                $mjo['companyid'] = $this->getCompanyId();
                $hx->allowField(true)->save($mjo);
            }

            foreach ($addList as $mjo) {
                $hx = new \app\admin\model\InvXskpHx();

                if (!empty($hx['data_id'])) {
                    $inv = \app\admin\model\Inv::get($hx['data_id']);
                    if (!empty($inv)) {
                        $mjo['yw_type'] = $inv['yw_type'];
                        $mjo['system_number'] = $inv['system_number'];
                        $mjo['kuandu'] = $inv['kuandu'];
                        $mjo['houdu'] = $inv['houdu'];
                        $mjo['kuandu'] = $inv['kuandu'];
                        $mjo['yw_time'] = $inv['yw_time'];

                        $inv->addMoney($mjo['data_id'], $mjo['sum_shui_price'], $mjo['zhongliang']);
                    } else {
                        throw new Exception('未找到源单');
                    }
                } else {
                    $mjo['yw_time'] = date('Y-m-d H:i:s');
                }
                $mjo['inv_xskp_id'] = $xskp['id'];
                $hx->allowField(true)->isUpdate(false)->data($mjo)->save();
            }
            Db::commit();
            return returnSuc(['id' => $xskp['id']]);
        } catch (Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }

    /**
     * 获取销售开票列表
     * @param Request $request
     * @param int $pageLimit
     * @return Json
     * @throws DbException
     */
    public function getList(Request $request, $pageLimit = 10)
    {
        $params = $request->param();
        $list = \app\admin\model\InvXskp::with(["customData", "pjlxData"])->where('company_id', $this->getCompanyId());
        //业务时间
        if (!empty($params['ywsjStart'])) {
            $list->where('yw_time', '>=', $params['ywsjStart']);
        }
        if (!empty($params['ywsjEnd'])) {
            $list->where('yw_time', '<', date('Y-m-d', strtotime($params['ywsjEnd'] . ' +1 day')));
        }
        //往来单位
        if (!empty($params['customer_id'])) {
            $list->where('customer_id', $params['customer_id']);
        }
        //票据类型
        if (!empty($params['piaoju_id'])) {
            $list->where('piaoju_id', $params['piaoju_id']);
        }
        //系统单号
        if (!empty($params['system_number'])) {
            $list->where('system_number', 'like', '%' . $params['system_number'] . '%');
        }
        //备注
        if (!empty($params['beizhu'])) {
            $list->where('beizhu', 'like', '%' . $params['beizhu'] . '%');
        }
        $list = $list->paginate($pageLimit);
        return returnSuc($list);
    }

    /**
     * 查看销售开票详情
     * @param int $id
     * @return Json
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function details($id = 0)
    {
        $data = $list = \app\admin\model\InvXskp::with([
            'pjlxData',
            'customData',
            'details' => ['guigeData', 'pinmingData'],
        ])
            ->where('company_id', $this->getCompanyId())
            ->where('id', $id)
            ->find();
        if (empty($data)) {
            return returnFail('数据不存在');
        } else {
            return returnSuc($data);
        }
    }

    /**
     * 销售开票作废
     * @param Request $request
     * @param int $id
     * @return Json
     */
    public function cancle(Request $request, $id = 0)
    {
        if (!$request->isPost()) {
            return returnFail('请求方式错误');
        }
        Db::startTrans();
        try {
            $sp = \app\admin\model\InvXskp::get($id);
            if (empty($sp)) {
                throw new Exception("对象不存在");
            }
            if ($sp->company != $this->getCompanyId()) {
                throw new Exception("对象不存在");
            }
            if ($sp['status'] == 1) {
                throw new Exception("该单据已经作废");
            }
            if (!empty($sp['jcx_id'])) {
                throw new Exception("该单据只读状态,不能作废");
            }

            $list1 = \app\admin\model\InvXskpHx::where('inv_xskp_id', $sp['id'])->select();
            $invModel = new \app\admin\model\Inv();
            foreach ($list1 as $hx) {
                if (!empty($hx['data_id'])) {
                    $invModel->jianMoney($hx['data_id'], $hx['sum_shui_price'], $hx['zhongliang']);
                }
            }
            $sp->status = 2;
            $sp->save();
            Db::commit();
            return returnSuc();
        } catch (Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }
}