<?php

namespace app\admin\model;

use Exception;
use think\db\{exception\DataNotFoundException, exception\ModelNotFoundException, Query};
use think\exception\DbException;
use traits\model\SoftDelete;

class CapitalFy extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;

    /**
     * @param $id
     * @param $money
     * @param $zhongliang
     * @throws DbException
     */
    public static function jianMoney($id, $money, $zhongliang)
    {
        $money = empty($money) ? 0 : $money;
        $zhongliang = empty($zhongliang) ? 0 : $zhongliang;
        $obj = self::get($id);
        if ($money != 0) {
            $obj['hxmoney'] -= $money;
        }
        if ($zhongliang != 0) {
            $obj['hxzhongliang'] -= $zhongliang;
        }
        $obj->save();
    }

    /**
     * 作废费用单
     * @param $dataId
     * @param $type
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws Exception
     */
    public static function invalidByDataIdAndType($dataId, $type)
    {
        $list = CapitalFyhx::where('data_id', $dataId)->where('fyhx_type', $type)->select();
        foreach ($list as $hx) {
            $fy = self::get($hx['cap_fy_id']);
            if ($fy['fymx_create_type'] == 1) {
                if ($fy['hxmoney'] > 0 || $fy['hxzhongliang'] > 0) {
                    throw new Exception("已经有结算信息!");
                }

                $fy->status = 2;
                $fy->save();
            } else {
                throw new Exception("已做费用单,禁止删除!");
            }
        }
    }

    public function szmcData()
    {
        return $this->belongsTo('Paymenttype', 'shouzhimingcheng_id', 'id')->cache(true, 60)
            ->field('id,name')->bind(['szmc_name' => 'name']);
    }

    public function szflData()
    {
        return $this->belongsTo('Paymentclass', 'shouzhifenlei_id', 'id')->cache(true, 60)
            ->field('id,name')->bind(['szfl_name' => 'name']);
    }

    public function pjlxData()
    {
        return $this->belongsTo('Pjlx', 'piaoju_id', 'id')->cache(true, 60)
            ->field('id,pjlx')->bind(['pjlx_name' => 'pjlx']);
    }

    public function custom()
    {
        return $this->belongsTo('Custom', 'customer_id', 'id')->cache(true, 60)
            ->field('id,custom')->bind(['dfdw_name' => 'custom']);
    }

    public function customForTongji()
    {
        return $this->belongsTo('Admin', 'moren_yewuyuan', 'id')->cache(true, 60)
            ->field('id,name')->bind(['yewuyuan' => 'name']);
    }

    public function saleOperator()
    {
        return $this->belongsTo('Admin', 'sale_operator_id', 'id')->cache(true, 60)
            ->field('id,name')->bind(['sale_operator_name' => 'name']);
    }

    public function createOperator()
    {
        return $this->belongsTo('Admin', 'create_operator_id', 'id')->cache(true, 60)
            ->field('id,name')->bind(['create_operator_name' => 'name']);
    }

    public function updateOperator()
    {
        return $this->belongsTo('Admin', 'update_operator_id', 'id')->cache(true, 60)
            ->field('id,name')->bind(['update_operator_name' => 'name']);
    }

    public function details()
    {
        return $this->hasMany('CapitalFyhx', 'cap_fy_id', 'id');
    }

    /**
     * @param $fyLists
     * @param $deleteIds
     * @param $dataId
     * @param $ywTime
     * @param $ywType
     * @param $groupId
     * @param $saleOperatorId
     * @param $beizhu
     * @param $userId
     * @param $companyId
     * @return bool
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws Exception
     */
    public function fymxSave($fyLists, $deleteIds, $dataId, $ywTime, $ywType, $groupId, $saleOperatorId, $beizhu, $userId, $companyId)
    {
        $addFyList = [];
        $updateFyList = [];
        $flag = true;

        if (!empty($fyLists)) {
            $validate = new \app\admin\validate\CapitalFy();
            foreach ($fyLists as $index => $jo) {
                if (!$validate->check($jo)) {
                    throw new Exception($validate->getError());
                }
                if (empty($jo['id'])) {
                    $addFyList[] = $jo;
                } else {
                    $updateFyList[] = $jo;
                }
            }

        } else {
            $flag = false;
        }

        if (!empty($deleteIds)) {
            $deleteList = self::where('id', 'in', $deleteIds)->select();
            foreach ($deleteList as $obj) {
                $this->deleteFyMx($obj);
            }
        }

        foreach ($updateFyList as $obj) {
            $this->updateFyMx($obj['id'], $obj['beizhu'], $obj['customer_id'], $obj['fang_xiang'], $obj['piaoju_id'],
                $groupId, $saleOperatorId, $obj['danjia'], $obj['money'], $obj['price_and_tax'] ?? 0, $obj['tax_rate'] ?? 0,
                $obj['tax'] ?? 0, $obj['zhongliang'] ?? 0, $obj['shouzhifenlei_id'], $obj['shouzhimingcheng_id'], $beizhu);
        }

        foreach ($addFyList as $obj) {
            $this->insertFyMx($dataId, $ywType, $obj['fang_xiang'], $obj['customer_id'], $obj['shouzhifenlei_id'],
                $obj['danjia'], $obj['money'], $obj['price_and_tax'] ?? 0, $obj['tax_rate'] ?? 0, $obj['tax'] ?? 0, $obj['zhongliang'] ?? 0,
                $obj['shouzhimingcheng_id'], $obj['piaoju_id'], $ywTime, $obj['beizhu'] ?? '', $groupId, $saleOperatorId,
                $beizhu, $userId, $companyId);
        }

//        fyhx(ywType, dataId, user . getId(), jg . getId(), zt . getId());
        return $flag;
    }

    /**
     * @param CapitalFy $fy
     * @throws Exception
     */
    public function deleteFyMx(CapitalFy $fy)
    {
        if (empty($fy)) {
            throw new Exception('未找到费用单');
        }
        if ($fy->fymx_create_type == 1) {
            if ($fy->hxmoney > 0 || $fy->hxzhongliang > 0) {
                throw new Exception("已经有结算信息!");
            }

            CapitalFyhx::destroy(function (Query $query) use ($fy) {
                $query->where('cap_fy_id', $fy->id);
            });

            $fy->delete();
        } else {
            throw new Exception("已做过费用单禁止删除原单");
        }
    }

    /**
     * @param $id
     * @param $beizhu
     * @param $customerId
     * @param $fangxiang
     * @param $piaojuId
     * @param $groupId
     * @param $saleOperatorId
     * @param $danjia
     * @param $money
     * @param $sumPrice
     * @param $shuiPrice
     * @param $shuie
     * @param $zhongliang
     * @param $shouzhiFenleiId
     * @param $shouzhimingchengId
     * @param $beizhu2
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws Exception
     */
    public function updateFyMx($id, $beizhu, $customerId, $fangxiang, $piaojuId, $groupId, $saleOperatorId, $danjia,
                               $money, $sumPrice, $shuiPrice, $shuie, $zhongliang, $shouzhiFenleiId, $shouzhimingchengId, $beizhu2)
    {
        $fy = CapitalFy::get($id);
        if ($fy->hxmoney > 0 || $fy->hxzhongliang > 0) {
            throw new Exception("已经有结算信息!");
        }
        $fy->customerId = $customerId;
        if (empty($beizhu2)) {
            $fy->beizhu = $beizhu;
        } else {
            $fy->beizhu = $beizhu2;
        }
        $fy->fang_xiang = $fangxiang;
        $fy->danjia = $danjia;
        $fy->piaoju_id = $piaojuId;
        $fy->tax = $shuie;
        $fy->price_and_tax = $sumPrice;
        $fy->tax_rate = $shuiPrice;
        $fy->money = $money;
        $fy->shouzhifeilei_id = $shouzhiFenleiId;
        $fy->shouzhimingcheng_id = $shouzhimingchengId;
        $fy->zhongliang = $zhongliang;
        $fy->group_id = $groupId;
        $fy->sale_operator_id = $saleOperatorId;
        $fy->save();

        $fyhx = CapitalFyhx::where('cap_fy_id', $fy['id'])->find();
        $fyhx->customerId = $customerId;
        $fyhx->hx_money = $money;
        $fyhx->heji_zhongliang = $zhongliang;
        $fyhx->save();
        (new Inv())->updateInv($fy['id'], 7, $fy['fang_xiang'], $fy['customer_id'], $fy['yw_time'], null,
            null, null, null, null, $fy['piaoju_id'], null, $fy['zhongliang'],
            $fy['danjia'], $fy['price_and_tax'], $fy['money'], $fy['tax_rate']);
    }

    /**
     * @param $dataId
     * @param $ywType
     * @param $fangxiang
     * @param $customerId
     * @param $shouzhiFenleiId
     * @param $danjia
     * @param $money
     * @param $sumPrice
     * @param $shuiPrice
     * @param $shuie
     * @param $zhongliang
     * @param $shouzhimingchengId
     * @param $piaojuId
     * @param $ywTime
     * @param $beizhu
     * @param $groupId
     * @param $saleOperatorId
     * @param $beizhu2
     * @param $userId
     * @param $companyId
     * @throws \think\Exception
     */
    public function insertFyMx($dataId, $ywType, $fangxiang, $customerId, $shouzhiFenleiId, $danjia, $money, $sumPrice,
                               $shuiPrice, $shuie, $zhongliang, $shouzhimingchengId, $piaojuId, $ywTime, $beizhu, $groupId, $saleOperatorId, $beizhu2, $userId, $companyId)
    {
        $fy = new self();
        $fy->yw_type = 2;
        if (empty($beizhu2)) {
            $fy->beizhu = $beizhu;
        } else {
            $fy->beizhu = $beizhu2;
        }
        $fy->companyid = $companyId;
        $fy->create_operator_id = $userId;
        $fy->customer_id = $customerId;
        $fy->group_id = $groupId;
        $fy->sale_operator_id = $saleOperatorId;
        $count = self::withTrashed()
            ->whereTime('create_time', 'today')
            ->where('companyid', $companyId)
            ->count();
        $fy->system_number = "FYD" . date('Ymd') . str_pad(++$count, 3, '0', STR_PAD_LEFT);
        $fy->yw_time = $ywTime;
        $fy->fymx_create_type = "1";
        $fy->fang_xiang = $fangxiang;
        $fy->shouzhifenlei_id = $shouzhiFenleiId;
        $fy->money = $money;
        $fy->tax = $shuie;
        $fy->price_and_tax = $sumPrice;
        $fy->tax_rate = $shuiPrice;
        $fy->zhongliang = $zhongliang;
        $fy->shouzhimingcheng_id = $shouzhimingchengId;
        $fy->piaoju_id = $piaojuId;
        $fy->danjia = $danjia;
        $fy->save();

        $hx = new CapitalFyhx();
        $hx->cache_yw_time = $ywTime;
        $hx->cap_fy_id = $fy->id;
        $hx->customer_id = $customerId;
        $hx->data_id = $dataId;
        $hx->heji_zhongliang = $zhongliang;
        $hx->fyhx_type = $ywType;
        $hx->hx_money = $money;
        $hx->save();
        (new Inv())->insertInv($fy['id'], 7, $fy['fang_xiang'], null, null, null, null, null,
            $fy['piaoju_id'], null, $fy['system_number'], $fy['customer_id'], $fy['yw_time'], $fy['danjia'], $fy['tax_rate'], $fy['price_and_tax'], $fy['money'],
            $fy['zhongliang'], $companyId);
    }

    /**
     * @param $id
     * @throws DbException
     * @throws Exception
     */
    public function deleteFyMxById($id)
    {
        $fy = self::get($id);
        $this->deleteFyMx($fy);
    }

    /**
     * @param $id
     * @param $oldMoney
     * @param $money
     * @param $oldZhongliang
     * @param $zhongliang
     * @throws DbException
     * @throws Exception
     */
    public function tiaoMoney($id, $oldMoney, $money, $oldZhongliang, $zhongliang)
    {
        $money = empty($money) ? 0 : $money;
        $zhongliang = $zhongliang == null ? 0 : $zhongliang;
        $obj = self::get($id);

        if ($money != 0) {
            $obj->hxmoney = $obj['hxmoney'] + $money - $oldMoney;
        }

        if ($obj['hxmoney'] > $obj['money']) {
            throw new Exception("核销金额不能大于总金额");
        }
        if ($zhongliang != 0) {
            $obj->hxzhongliang = $obj['hxzhongliang'] + $zhongliang - $oldZhongliang;
        }

        if ($obj['hxzhongliang'] > $obj['zhongliang']) {
            throw new Exception("核销重量不能大于总重量");
        }
        $obj->save();
    }

    /**
     * @param $id
     * @param $money
     * @param $zhongliang
     * @throws DbException
     * @throws Exception
     */
    public function addMoney($id, $money, $zhongliang)
    {
        $money = empty($money) ? 0 : $money;
        $zhongliang = empty($zhongliang) ? 0 : $zhongliang;


        $obj = self::get($id);
        if ($money != 0) {
            $hxmoney = empty($obj['hxmoney']) ? 0 : $obj['hxmoney'];
            $obj->hxmoney = $hxmoney + $money;
        }

        if ($obj['hxmoney'] > $obj['money']) {
            throw new Exception("核销金额不能大于总金额");
        }
        if ($zhongliang != 0) {
            $hxzhongliang = empty($obj['hxzhongliang']) ? 0 : $obj['hxzhongliang'];
            $obj['hxzhongliang'] = $hxzhongliang + $zhongliang;
        }

        if ($obj['hxzhongliang'] > $obj['zhongliang']) {
            throw new Exception("核销重量不能大于总重量");
        }

        $obj->save();
    }

    /**
     * @param $dataId
     * @param $type
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws Exception
     */
    public function deleteByDataIdAndType($dataId, $type)
    {
        $list = self::where('data_id', $dataId)->where('fyhx_type', $type)->select();
        foreach ($list as $hx) {
            $fy = CapitalFy::get($hx['cap_fy_id']);
            if (!empty($fy)) {
                if ($fy['fymx_create_type'] == 1) {
                    if ($fy['hxmoney'] != 0 || $fy['hxzhongliang'] != 0) {
                        throw new Exception("已经有结算信息!");
                    }
                    $fy->delete();
                } else {
                    throw new Exception("已做费用单,禁止删除!");
                }
            }

            $hx->delete();
        }
    }
}
