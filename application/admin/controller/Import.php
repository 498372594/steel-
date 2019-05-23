<?php


namespace app\admin\controller;


use app\admin\model\Classname;
use app\admin\model\Jsfs;
use app\admin\model\Productname;
use app\admin\model\Specification;
use Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use think\Cache;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\Request;
use think\response\Json;

class Import extends Right
{

    /**
     * @param Request $request
     * @return Json
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function wuzi(Request $request)
    {
        $file = $request->file('file');

        if (!$file) {
            return returnFail('请上传文件');
        }
        $info = $file->validate(['ext' => 'xls,xlsx'])->move(ROOT_PATH . 'public' . DS . 'uploads');
        if (!$info) {
            return returnFail($file->getError());
        }
        $path = $info->getRealPath();

        $spreadSheet = IOFactory::load($path);

        $data = $spreadSheet->getSheet(0)->toArray(null, true, true, true);
        Db::startTrans();
        try {
            foreach ($data as $index => $row) {
                if ($index <= 2) {
                    continue;
                }
                if (empty($row['A'])) {
                    throw new Exception('一级分类不能为空');
                }

                $classid = $this->getClassId($row['A'], $row['B']);

                if (!empty($row['C'])) {
                    $classid = $this->getClassId($row['C'], $row['D'], $classid);
                }

                $productid = $this->getProductId($row['E'], $row['F'], $row['G'], $row['H'], $row['I'], $row['J'], $classid);
                $caizhi = $this->getCaizhiId($row['K']);
                $chandi = $this->getChandiId($row['L']);

                $this->dealSpecification($row['M'], $row['N'], $productid, $row['O'], $row['P'], $row['Q'], $row['R'], $chandi, $caizhi, $row['S']);
            }
            Db::commit();
            return returnSuc();
        } catch (Exception $e) {
            Db::rollback();
            return returnFail('第' . $index . '行报错：' . $e->getMessage());
        }

    }

    /**
     * @param $name
     * @param $zjm
     * @param int $parentId
     * @return mixed
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     * @throws Exception
     */
    private function getClassId($name, $zjm, $parentId = 0)
    {
        $cacheId = 'import_class_' . $parentId . $name;
        $model = Classname::where('classname', $name)
            ->where('companyid', $this->getCompanyId())
            ->cache($cacheId, 60, 'import_data');
        if (!empty($parentId)) {
            $model->where('pid', $parentId);
        }
        $model = $model->find();
        if (empty($model)) {
            if (empty($zjm)) {
                throw new Exception('分类编码不能为空');
            }
            $data = [
                'classname' => $name,
                'companyid' => $this->getCompanyId(),
                'zjm' => $zjm,
                'add_name' => $this->getAccount()['name'],
                'add_id' => $this->getAccountId(),
                'pid' => $parentId
            ];
            Cache::tag('import_data')->rm($cacheId);
            $model = new Classname();
            $model->save($data);
        }
        return $model['id'];
    }

    /**
     * @param $name
     * @param $zjm
     * @param $pinming_type
     * @param $jsfs
     * @param $length_unit
     * @param $heavy_unit
     * @param $classid
     * @return mixed
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     * @throws Exception
     */
    private function getProductId($name, $zjm, $pinming_type, $jsfs, $length_unit, $heavy_unit, $classid)
    {
        $cacheId = 'import_product_' . $classid . $name;

        $data = Productname::where('name', $name)
            ->where('companyid', $this->getCompanyId())
            ->where('classid', $classid)
            ->cache($cacheId, 60, 'import_data')
            ->find();

        if (empty($data)) {
            if (empty($zjm)) {
                throw new Exception('品名编码不能为空');
            }
            if ($pinming_type == '卷板') {
                $pinming_type = 1;
            } elseif ($pinming_type == '平板') {
                $pinming_type = 2;
            } else {
                throw new Exception('卷板/平板数据错误');
            }
            if ($jsfs == '磅计') {
                $jsfs = 1;
            } elseif ($jsfs == '理计') {
                $jsfs = 2;
            } elseif ($jsfs == '计数') {
                $jsfs = 3;
            } else {
                throw new Exception('计算方式数据错误');
            }
            if ($length_unit == '毫米') {
                $length_unit = 1;
            } elseif ($length_unit == '米') {
                $length_unit = 2;
            } else {
                throw new Exception('长（宽）度单位数据错误');
            }
            if ($heavy_unit == '千克') {
                $heavy_unit = 1;
            } elseif ($heavy_unit == '吨') {
                $heavy_unit = 2;
            } else {
                throw new Exception('长（宽）度单位数据错误');
            }
            $data = [
                'name' => $name,
                'companyid' => $this->getCompanyId(),
                'zjm' => $zjm,
                'add_name' => $this->getAccount()['name'],
                'add_id' => $this->getAccountId(),
                'classid' => $classid,
                'pinming_type' => $pinming_type,
                'jsfs' => $jsfs,
                'heavy_type' => 2,
                'length_unit' => $length_unit,
                'heavy_unit' => $heavy_unit
            ];
            $model = new Productname();
            Cache::tag('import_data')->rm($cacheId);
            $model->save($data);
            $data = $model;
        } else {
            if (!empty($zjm)) {
                $data->zjm = $zjm;
            }
            if ($pinming_type == '卷板') {
                $data->pinming_type = 1;
            } elseif ($pinming_type == '平板') {
                $data->pinming_type = 2;
            }
            if ($jsfs == '磅计') {
                $data->jsfs = 1;
            } elseif ($jsfs == '理计') {
                $data->jsfs = 2;
            } elseif ($jsfs == '计数') {
                $data->jsfs = 3;
            }
            if ($length_unit == '毫米') {
                $data->length_unit = 1;
            } elseif ($length_unit == '米') {
                $data->length_unit = 2;
            }
            if ($heavy_unit == '千克') {
                $data->heavy_unit = 1;
            } elseif ($heavy_unit == '吨') {
                $data->heavy_unit = 2;
            }
            Cache::tag('import_data')->rm($cacheId);
            $data->save();
        }
        return $data['id'];
    }

    /**
     * @param $specification
     * @param $zjm
     * @param $productId
     * @param $houdu
     * @param $length
     * @param $width
     * @param $zhijian
     * @param $originAreaId
     * @param $textureId
     * @param $mizhong
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws Exception
     */
    private function dealSpecification($specification, $zjm, $productId, $houdu, $length, $width, $zhijian, $originAreaId, $textureId, $mizhong)
    {
        if (empty($zjm)) {
            throw new Exception('规格编码不能为空');
        }
        if (is_null($houdu)) {
            throw new Exception('厚度数据错误');
        }
        if (is_null($length)) {
            throw new Exception('长度数据错误');
        }
        if (is_null($width)) {
            throw new Exception('宽度数据错误');
        }
        if (is_null($zhijian)) {
            throw new Exception('件支数数据错误');
        }
        if (is_null($mizhong)) {
            throw new Exception('支重数据错误');
        }

        $cacheId = 'import_specification_' . $productId . $specification;
        $model = Specification::where('specification', $specification)
            ->where('companyid', $this->getCompanyId())
            ->where('productname_id', $productId)
            ->cache($cacheId, 60, 'import_data')
            ->find();

        $data = [
            'specification' => $specification,
            'companyid' => $this->getCompanyId(),
            'zjm' => $zjm,
            'add_name' => $this->getAccount()['name'],
            'add_id' => $this->getAccountId(),
            'productname_id' => $productId,
            'houdu_name' => $houdu,
            'length' => $length,
            'width' => $width,
            'zhijian' => $zhijian,
            'originarea_id' => $originAreaId,
            'texture_id' => $textureId,
            'mizhong_name' => $mizhong,
        ];
        if (empty($model)) {
            $model = new Specification();
            $model->save($data);
        } else {
            $model->save($data);
        }
    }

    public function init(Request $request)
    {
        $file = $request->file('file');

        if (!$file) {
            return returnFail('请上传文件');
        }
        $info = $file->validate(['ext' => 'xls,xlsx'])->move(ROOT_PATH . 'public' . DS . 'uploads');
        if (!$info) {
            return returnFail($file->getError());
        }
        $path = $info->getRealPath();

        $spreadSheet = IOFactory::load($path);

        $data = $spreadSheet->getSheet(0)->toArray(null, true, true, true);

        $list = [];
        foreach ($data as $index => $row) {
            if ($index <= 2) {
                continue;
            }

            $mx = [];

            $cate = $row['A'];
            $pinming = $row['B'];
            $guige = $row['C'];
            $houdu = $row['D'];
            $kuandu = $row['E'];
            $changdu = $row['F'];
            $caizhi = $row['G'];
            $chandi = $row['H'];
            $mizhong = $row['I'];
            $jianzhong = $row['J'];
            $jisuanfangshi = $row['K'];
            $lingzhi = $row['L'];
            $jianshu = $row['M'];
            $zhijian = $row['N'];
            $counts = $row['O'];
            $zhongliang = $row['P'];
            $price = $row['Q'];
            $sumprice = $row['R'];
            $shuiprice = $row['S'];
            $shuie = $row['T'];
            $sumshuiprice = $row['U'];
            $pihao = $row['V'];
            $huowei = $row['W'];
            if (!empty($cate) || !empty($pinming) || !empty($guige) || !empty($jisuanfangshi) || !empty($zhijian) || !empty($zhongliang)) {

                //类别
                if (!empty($cate)) {
                    $cateData = Classname::where('classname', $cate)
                        ->cache(true, 60)
                        ->where('companyid', $this->getCompanyId())
                        ->find();
                    $cateId = $cateData['id'];
                    $cateCode = $cateData['zjm'];
                    $mx['cate_name'] = $cate;
                    $mx['cate_id'] = $cateId;
                    $mx['cate_code'] = $cateCode;
                }
                if (empty($cateId)) {
                    continue;
                }

                //品名
                if (!empty($pinming)) {
                    $pinmingId = Productname::where('companyid', $this->getCompanyId())
                        ->where('classid', $cateId)
                        ->where('name', $pinming)
                        ->cache(true, 60)
                        ->value('id');
                    $mx['pinming_id'] = $pinmingId;
                    $mx['pinming_name'] = $pinming;
                }
                if (empty($pinmingId)) {
                    continue;
                }

                if (!empty($guige)) {
                    $guigeId = Specification::where('companyid', $this->getCompanyId())
                        ->where('specification', $guige)
                        ->where('productname_id', $pinmingId)
                        ->cache(true, 60)
                        ->value('id');
                    $mx['guige_id'] = $guigeId;
                    $mx['guige_name'] = $guige;
                }
                if (empty($guigeId)) {
                    continue;
                }

                $mx['houdu'] = $houdu ?? '';
                $mx['kuandu'] = $kuandu ?? '';
                $mx['changdu'] = $changdu ?? '';
                if (!empty($caizhi)) {
                    $caizhiId = $this->getCaizhiId($caizhi);
                    $mx['caizhi_name'] = $caizhi;
                    $mx['caizhi_id'] = $caizhiId;
                }
                if (!empty($chandi)) {
                    $chandiId = $this->getChandiId($chandi);
                    $mx['chandi_name'] = $chandi;
                    $mx['chandi_id'] = $chandiId;
                }
                $mx['mizhong'] = $mizhong ?? '';
                $mx['jianzhong'] = $jianzhong ?? '';
                if (!empty($jisuanfangshi)) {
                    $jisuanfangshiId = Jsfs::where('jsfs', $jisuanfangshi)
                        ->where('companyid', $this->getCompanyId())
                        ->cache(true, 60)
                        ->value('id');
                    $mx['jisuanfangshi_id'] = $jisuanfangshiId;
                    $mx['jisuanfangshi_name'] = $jisuanfangshi;
                }
                if (empty($jisuanfangshiId)) {
                    continue;
                }
                $mx['lingzhi'] = $lingzhi ?? '';
                $mx['jianshu'] = $jianshu ?? '';
                if (!empty($zhijian)) {
                    $mx['zhijian'] = $zhijian;
                } else {
                    continue;
                }
                $mx['counts'] = $counts ?? '';
                if (!empty($zhongliang)) {
                    $mx['zhongliang'] = $zhongliang;
                } else {
                    continue;
                }
                $mx['price'] = $price ?? '';
                $mx['shuiprice'] = $shuiprice ?? '';
                $mx['shuie'] = $shuie ?? '';
                $mx['sumshuiprice'] = $sumshuiprice ?? '';
                $mx['pihao'] = $pihao ?? '';
                $mx['huowei'] = $huowei ?? '';
                $mx['sumprice'] = $sumprice ?? '';
                $list[] = $mx;
            }
        }
        return returnSuc($list);
    }
}