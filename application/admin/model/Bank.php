<?php

namespace app\admin\model;

use Exception;
use think\db\exception\{DataNotFoundException, ModelNotFoundException};
use think\exception\DbException;
use traits\model\SoftDelete;

class Bank extends Base
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $autoWriteTimestamp = 'datetime';

    /**
     * @param $dataId
     * @param $ywType
     * @param $fangxiang
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     * @throws Exception
     */
    public static function deleteBank($dataId, $ywType, $fangxiang)
    {
        $bank = CapitalBank::where('data_id', $dataId)->where('capbank_type', $ywType)->where('fangxiang', $fangxiang)->find();
        if (!empty($bank)) {

            $ba = Bank::get($bank['bank_id']);
            if (!empty($ba)) {
                if ($fangxiang == 1) {
                    $ba->money = $ba->money - $bank->money;
                } else {
                    $ba->money = $ba->money + $bank->money;
                    if ($ba->money < 0) {
                        throw new Exception("余额不足时禁止出账");
                    }
                }

                $ba->save();
            }
            $bank->delete();
        }
    }

    /**
     * @param $dataId
     * @param $oldBankId
     * @param $ywType
     * @param $bankId
     * @param $fangxiang
     * @param $ywTime
     * @param $money
     * @param $oldMoney
     * @param $cacheCustomer
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws Exception
     */
    public function updateBank($dataId, $oldBankId, $ywType, $bankId, $fangxiang, $ywTime, $money, $oldMoney)
    {
        if ($bankId == $oldBankId) {
            $ba = self::get($bankId);
            if (2 == $fangxiang) {
                $ba->money = $ba->money + $oldMoney - $money;
                if ($ba->money < 0) {
                    throw new Exception("余额不足时禁止出账");
                }
            } else {
                $ba->money = $ba->money + $money - $oldMoney;
            }
            $ba->save();
        } else {
            $ba = self::get($oldBankId);
            $ba->money = $ba->money - ($fangxiang == 2 ? -$oldMoney : $oldMoney);
            $ba->save();

            $bank = self::get($bankId);
            $bank->money = $bank->money + ($fangxiang == 2 ? -$money : $money);

            if ($fangxiang == 2 && $ba->money < 0) {
                throw new Exception("余额不足时禁止出账");
            }

            $bank->save();
        }

        $bank = CapitalBank::where('data_id', $dataId)
            ->where('capbank_type', $ywType)
            ->where('fangxiang', $fangxiang)
            ->find();
        $bank->fangxiang = $fangxiang;
        $bank->yw_time = $ywTime;
        $bank->money = $bank->money + $money - $oldMoney;
        $bank->bank_id = $bankId;
        $bank->save();
    }

    /**
     * @param $dataId
     * @param $ywType
     * @param $bankId
     * @param $fangxiang
     * @param $ywTime
     * @param $money
     * @param $cacheCustomer
     * @param $cacheYwSystem
     * @param $companyId
     * @throws DbException
     */
    public function insertBank($dataId, $ywType, $bankId, $fangxiang, $ywTime, $money, $cacheCustomer, $cacheYwSystem, $companyId)
    {
        $bank = new CapitalBank();
        $bank->companyid = $companyId;
        $bank->capbank_type = $ywType;
        $bank->data_id = $dataId;
        $bank->fangxiang = $fangxiang;
        $bank->money = $money;
        $bank->yw_time = $ywTime;
        $bank->bank_id = $bankId;
        $bank->cache_customer_id = $cacheCustomer;
        $bank->cache_system_number = $cacheYwSystem;
        $bank->save();

        $ba = self::get($bankId);
        if (!empty($ba)) {
            $ba->money = $ba['money'] + (2 == $fangxiang ? -$bank['money'] : $bank['money']);
            if ($fangxiang == 2 && $ba['money'] < 0) {
                throw new Exception("余额不足时禁止出账");
            }

            $ba->save();
        }
    }
}
