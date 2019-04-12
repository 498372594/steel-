<?php


namespace app\admin\controller;



use think\{db\exception\DataNotFoundException,
    db\exception\ModelNotFoundException,
    exception\DbException,
    Request,
    response\Json};

class Iniv extends Right
{
    public function add($data)
    {
        /*$data = {[ 'companyid' => $companyId,
                        'fx_type'=>2,
                        'yw_type'=>6,
                        'yw_time'=>$v["yw_time"]?? '',
                        'system_number'=>$v["system_number"]."1"?? '',
                        'pinming_id'=>$v["pinming_id"]?? '',
                        'guige_id'=>$v["guige_id"]?? '',
                        'houdu'=>$v["houdu"]?? '',
                        'changdu'=>$v["changdu"]?? '',
                        'kuandu'=>$v["kuandu"]?? '',
                        'zhongliang'=>$v["zhongliang"]?? '',
                        'price'=>$v["price"]?? '',
                        'price'=>$v["price"]?? '',
                        'customer_id'=>$data["customer_id"]?? '',
                        'jijiafangshi_id'=>$v["jijiafangshi_id"]?? '',
                        'piaoju_id'=>$v["piaoju_id"]?? '',
                        'yhx_zhongliang'=>0,
                        'yhx_price'=>0,
                        'data_id'=>$id,
                        'shui_price'=>$v["shui_price"]?? '',
                        'sum_price'=>$v["sum_price"]?? '',};*/
        model("iniv")->allowField(true)->saveALl($data);
    }
}