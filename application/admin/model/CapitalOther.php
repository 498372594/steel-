<?php


namespace app\admin\model;


use Exception;
use think\exception\DbException;
use traits\model\SoftDelete;

class CapitalOther extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;

    public function custom()
    {
        return $this->belongsTo('Custom', 'customer_id', 'id')->cache(true, 60)
            ->field('id,custom')->bind(['customer_name' => 'custom']);
    }

    public function jsfsData()
    {
        return $this->belongsTo('Jiesuanfangshi', 'jiesuan_id', 'id')->cache(true, 60)
            ->field('id,jiesuanfangshi')->bind(['jiesuan_name' => 'jiesuanfangshi']);
    }

    public function details()
    {
        return $this->hasMany('CapitalOtherDetails', 'cap_qt_id', 'id');
    }

    public function createOperate()
    {
        return $this->belongsTo('Admin', 'create_operator_id', 'id')
            ->field('id,name')->bind(['create_operator' => 'name']);
    }

    public function updateOperate()
    {
        return $this->belongsTo('Admin', 'update_operator_id', 'id')
            ->field('id,name')->bind(['update_operator' => 'name']);
    }

    public function saleOperate()
    {
        return $this->belongsTo('Admin', 'sale_operator_id', 'id')
            ->field('id,name')->bind(['sale_operator' => 'name']);
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
        $zhongliang = empty($zhongliang) ? 0 : $zhongliang;

        $obj = self::get($id);

        if ($money != 0) {
            $obj->hxmoney = $obj['hxmoney'] + $money - $oldMoney;
        }

//        if (yfkMoney . compareTo(0) != 0) {
//        obj . setYfkhxmoney(obj . getYfkhxmoney() . add(yfkMoney . subtract(oldYfkMoney)));
//    }

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
     * @param CapitalOther $obj
     * @throws Exception
     */
    public static function ifHx(CapitalOther $obj)
    {
        if ($obj['hxmoney'] > 0 || $obj['hxzhongliang'] > 0) {
            throw new Exception("已经有结算信息!");
        }
    }
}