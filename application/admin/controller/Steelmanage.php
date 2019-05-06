<?php

namespace app\admin\controller;

use app\admin\library\tree\Tree;
use app\admin\model\Bank;
use app\admin\model\Classname;
use app\admin\model\Custom;
use app\admin\model\Jianzhishu;
use app\admin\model\Jiesuanfangshi;
use app\admin\model\Jsfs;
use app\admin\model\Paymenttype;
use app\admin\model\Pjlx;
use app\admin\model\Productname;
use app\admin\model\Specification;
use app\admin\model\Storage;
use app\admin\model\Texture;
use app\admin\model\Transportation;
use app\admin\model\Unit;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\db\Query;
use think\Exception;
use think\exception\DbException;
use think\Request;
use think\response\Json;

class Steelmanage extends Right
{
    /**
     * 大类列表接口
     * @return Json
     * @throws DbException
     */
    public function classname()
    {
        $list = model("classname")->where("companyid", $this->getCompanyId())->paginate(10);
        return returnSuc($list);
    }

    /**
     * 添加大类
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function addclassname()
    {
        if (request()->isPost()) {
            $data = request()->post();
            $data['companyid'] = $this->getCompanyId();
            $data['add_name'] = $this->getAccount()['name'];
            $data['add_id'] = $this->getAccountId();
            $id = request()->post('id');
            $check = Classname::where('classname', $data['classname']);
            if (empty($id)) {
                $check = $check->find();
                if (!empty($check)) {
                    return returnFail('该类已经存在');
                }
                $result = model("classname")->allowField(true)->save($data);
                return returnRes($result, '添加失败');
            } else {
                $check = $check->where('id', '<>', $id)->find();
                if (!empty($check)) {
                    return returnFail('该类已经存在');
                }
                $result = model("classname")->allowField(true)->save($data, ['id' => $id]);
                return returnRes($result, '修改失败');
            }

        } else {
            return returnRes("", '参数错误');
        }
    }

    /**
     * 删除
     * @param Request $request
     * @return Json
     * @throws Exception
     */
    public function delete(Request $request)
    {
        $data = $request->param();
        $model = $data['tablename'];
        $ids = $data["id"];
        $where["id"] = ["in", $ids];
        switch ($model) {
            case "classname":
                $re = model("classname")->where("pid", 'in', $ids)->count();
                if ($re > 0) {
                    return returnFail('该类存在子分类');
                }
                Classname::destroy(function (Query $query) use ($ids) {
                    $query->where('id', 'in', $ids);
                });
                break;
            case 'productname':
                Productname::destroy(function (Query $query) use ($ids) {
                    $query->where('id', 'in', $ids);
                });
                break;
            case 'texture':
                Texture::destroy(function (Query $query) use ($ids) {
                    $query->where('id', 'in', $ids);
                });
                break;
            case 'jianzhishu':
                Jianzhishu::destroy(function (Query $query) use ($ids) {
                    $query->where('id', 'in', $ids);
                });
                break;
            case 'unit':
                Unit::destroy(function (Query $query) use ($ids) {
                    $query->where('id', 'in', $ids);
                });
                break;
            case 'custom':
                Custom::destroy(function (Query $query) use ($ids) {
                    $query->where('id', 'in', $ids);
                });
                break;
            case 'storage':
                Storage::destroy(function (Query $query) use ($ids) {
                    $query->where('id', 'in', $ids);
                });
                break;
            case 'bank':
                Bank::destroy(function (Query $query) use ($ids) {
                    $query->where('id', 'in', $ids);
                });
                break;
            case 'jiesuanfangshi':
                Jiesuanfangshi::destroy(function (Query $query) use ($ids) {
                    $query->where('id', 'in', $ids);
                });
                break;
            case 'pjlx':
                Pjlx::destroy(function (Query $query) use ($ids) {
                    $query->where('id', 'in', $ids);
                });
                break;
            case 'paymenttype':
                Paymenttype::destroy(function (Query $query) use ($ids) {
                    $query->where('id', 'in', $ids);
                });
                break;
            case 'jsfs':
                Jsfs::destroy(function (Query $query) use ($ids) {
                    $query->where('id', 'in', $ids);
                });
                break;
            case 'specification':
                Specification::destroy(function (Query $query) use ($ids) {
                    $query->where('id', 'in', $ids);
                });
                break;
            case 'transportation':
                Transportation::destroy(function (Query $query) use ($ids) {
                    $query->where('id', 'in', $ids);
                });
                break;
            default:
               model($model)->where('id', 'in', $ids)->update(array("delete_time"=>date("Y-m-d H:s:i".time())));
        }
        return returnSuc();
    }

    /**
     * @return Json
     * @throws DbException
     */
    public function productname()
    {
        if (request()->param("id")) {
            $ids = request()->param("id");
            $where = array(
                'a.classid' => ['in', $ids],
                'a.companyid' => $this->getCompanyId()
            );
        } else {
            $where = array(
                'a.companyid' => $this->getCompanyId()
            );
        }
        $list = model("productname")->alias("a")
            ->join("classname b", "a.classid=b.id", "left")
            ->where($where)
            ->field("a.*,b.classname")
            ->paginate(10);
        return returnSuc($list);
    }

    /**
     * 添加修改大类
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function addproductname()
    {
        if (request()->isPost()) {
            $data = request()->post();
            $data['companyid'] = $this->getCompanyId();
            $data['add_name'] = $this->getAccount()['name'];
            $data['add_id'] = $this->getAccountId();
            if (empty(request()->post("id"))) {
                $result = model("productname")->allowField(true)->save($data);
                return returnRes($result, '添加失败');
            } else {
//                $id = request()->post("id");
                $result = model("productname")->allowField(true)->update($data);
                return returnRes($result, '修改失败');
            }
        } else {
            $id = request()->param("id");
            if ($id) {
                $data['info'] = model("productname")->where("id", $id)->find();
            }

            $data["unit"] = model("unit")->where("companyid", $this->getCompanyId())->field("id,unit")->select();
            $list = db("classname")->field("pid,id,classname")->where("companyid", $this->getCompanyId())->select();
            $list = new Tree($list);
            $data['classname'] = $list->leaf();
            return returnRes($data, '无相关数据', $data);
        }
    }

    /**
     * 规格列表
     * @param int $pageLimt
     * @return Json
     * @throws DbException
     */
    public function specification($pageLimt = 10)
    {
        if (request()->param("productname_id")) {
            $ids = request()->param("productname_id");
            $where = array(
                'productname_id' => ['in', $ids],
                'companyid' => $this->getCompanyId()
            );
        } else {
            $where = array(
                'companyid' => $this->getCompanyId()
            );
        }
        $list = model("view_specification")->where($where)->paginate($pageLimt);
        return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
    }

    /**
     * 添加修改规格
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function addspecification()
    {
        if (request()->isPost()) {
            $data = request()->post();
            $data['companyid'] = $this->getCompanyId();
            $data['add_name'] = $this->getAccount()['name'];
            $data['add_id'] = $this->getAccountId();
            if (empty(request()->post("id"))) {
                $result = model("specification")->allowField(true)->save($data);
                return returnRes($result, '添加失败');
            } else {
//                $id = request()->post("id");
                $result = model("specification")->allowField(true)->update($data);
                return returnRes($result, '修改失败');
            }
        } else {
            $id = request()->param("id");
            if ($id) {
                $data['info'] = model("specification")->where("id", $id)->find();
            }
            $data["productlist"] = $this->getproductlist();
            $data["originarealist"] = model("originarea")->where("companyid", $this->getCompanyId())->field("id,originarea")->select();
            $data["texturelist"] = model("texture")->where("companyid", $this->getCompanyId())->field("id,texturename")->select();
            return returnRes($data, '无相关数据', $data);
        }
    }

    /**
     * 获取产品列表附带分类
     * @return Json
     * @return mixed
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws DataNotFoundException
     */
    public function getproductlist()
    {
        $list = db("classname")->field("pid,id,classname")->where("companyid", $this->getCompanyId())->select();
        $menutree = new Tree($list);
        $menulist = $menutree->leaf();
        $digui = $this->productnamedigui($menulist);
        return $digui;
    }

    /**
     * @param $arr
     * @return mixed
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws DataNotFoundException
     */
    public function productnamedigui($arr)
    {
        foreach ($arr as $k => $v) {

            $arr[$k]['productname'] = db("productname")
                ->where("companyid", $this->getCompanyId())
                ->where("classid", $v["id"])
                ->field("id,name")->select();
            if (array_key_exists('child', $v)) {
                $v = $this->productnamedigui($v["child"]);
                $arr[$k]["child"] = $v;
            }
        }
        return $arr;
    }

    /**
     * @return Json
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws DataNotFoundException
     */
    public function getproduct()
    {
        $list = db("classname")->field("pid,id,classname")->where("companyid", $this->getCompanyId())->select();
        $menutree = new Tree($list);
        $menulist = $menutree->leaf();
        $digui = $this->productnamedigui($menulist);
        return json($digui);
    }

    /**根据类名获取产品信息
     * @return Json
     * @throws DbException
     */
    public function getproductnamelist()
    {
        $classid = request()->param("classid");
        $list = model("productname")->where("classid", $classid)->select();
        return returnRes($list, '没有数据，请添加后重试', $list);
    }

    /**
     * 根据产品id获取规格列表
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getsepcificationlist()
    {
        $productname_id = request()->param("productname_id");
        $list = model("view_specification")->where("productname_id", $productname_id)->select();
        return returnRes($list, '没有数据，请添加后重试', $list);
    }

    /**
     * @return Json
     * @throws DbException
     */
    public function texture()
    {
        $list = model("texture")->where("companyid", $this->getCompanyId())->paginate(10);
        return returnSuc($list);
    }

    /**
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function addtexture()
    {
        if (request()->isPost()) {
            $data = request()->post();
            $data['companyid'] = $this->getCompanyId();
            $data['add_name'] = $this->getAccount()['name'];
            $data['add_id'] = $this->getAccountId();
            if (empty(request()->post("id"))) {
                $result = model("texture")->allowField(true)->save($data);
                return returnRes($result, '添加失败');
            } else {
                $id = request()->post("id");
                $result = model("texture")->allowField(true)->save($data, ['id' => $id]);
                return returnRes($result, '修改失败');
            }

        } else {
            $id = request()->param("id");
            if ($id) {
                $data['info'] = model("texture")->where("id", $id)->find();
            } else {
                $data = null;
            }
            return returnRes($data, '无相关数据', $data);
        }
    }
    /**
     * @return Json
     * @throws DbException
     */
    public function originarea()
    {
        $list = model("originarea")->where("companyid", $this->getCompanyId())->paginate(10);
        return returnSuc($list);
    }

    /**
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function addoriginarea()
    {
        if (request()->isPost()) {
            $data = request()->post();
            $data['companyid'] = $this->getCompanyId();
            $data['add_id'] = $this->getAccountId();
            if (empty(request()->post("id"))) {
                $result = model("originarea")->allowField(true)->save($data);
                return returnRes($result, '添加失败');
            } else {
                $id = request()->post("id");
                $result = model("originarea")->allowField(true)->save($data, ['id' => $id]);
                return returnRes($result, '修改失败');
            }

        } else {
            $id = request()->param("id");
            if ($id) {
                $data['info'] = model("originarea")->where("id", $id)->find();
            } else {
                $data = null;
            }
            return returnRes($data, '无相关数据', $data);
        }
    }

    /**
     * @return Json
     * @throws DbException
     */
    public function jianzhishu()
    {
        $list = model("jianzhishu")->where("companyid", $this->getCompanyId())->paginate(10);
        return returnSuc($list);
    }

    /**添加件支数
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function addjianzhishu()
    {
        if (request()->isPost()) {
            $data = request()->post();
            $data['companyid'] = $this->getCompanyId();
            $data['add_name'] = $this->getAccount()['name'];
            $data['add_id'] = $this->getAccountId();
            if (empty(request()->post("id"))) {
                $result = model("jianzhishu")->allowField(true)->save($data);
                return returnRes($result, '添加失败');
            } else {
                $id = request()->post("id");
                $result = model("jianzhishu")->allowField(true)->save($data, ['id' => $id]);
                return returnRes($result, '修改失败');
            }

        } else {
            $id = request()->param("id");
            if ($id) {
                $data['info'] = model("jianzhishu")->where("id", $id)->find();
            } else {
                $data = null;
            }
            return returnRes($data, '无相关数据', $data);
        }
    }

    /**
     * 计量单位
     * @return Json
     * @throws DbException
     */
    public function unit()
    {
        $list = model("unit")->where("companyid", $this->getCompanyId())->paginate(10);
        return returnSuc($list);
    }

    /**
     * 计量单位添加修改
     * @return Json
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function addunit()
    {
        if (request()->isPost()) {
            $data = request()->post();
            $data['companyid'] = $this->getCompanyId();
            $data['add_name'] = $this->getAccount()['name'];
            $data['add_id'] = $this->getAccountId();
            if (empty(request()->post("id"))) {
                $result = model("unit")->allowField(true)->save($data);
                return returnRes($result, '添加失败');
            } else {
                $id = request()->post("id");
                $result = model("unit")->allowField(true)->save($data, ['id' => $id]);
                return returnRes($result, '修改失败');
            }

        } else {
            $id = request()->param("id");
            if ($id) {
                $data['info'] = model("unit")->where("id", $id)->find();
            } else {
                $data = null;
            }
            return returnRes($data, '无相关数据', $data);
        }
    }

    /**
     * @return Json
     * @throws DbException
     */
    public function jsfs()
    {
        $list = model("jsfs")->where("companyid", $this->getCompanyId())->paginate(10);
        return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
    }

    /**
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function addjsfs()
    {
        if (request()->isPost()) {
            $data = request()->post();
            $data['companyid'] = $this->getCompanyId();
            $data['add_name'] = $this->getAccount()['name'];
            $data['add_id'] = $this->getAccountId();
            if (empty(request()->post("id"))) {
                $result = model("jsfs")->allowField(true)->save($data);
                return returnRes($result, '添加失败');
            } else {
                $id = request()->post("id");
                $result = model("jsfs")->allowField(true)->save($data, ['id' => $id]);
                return returnRes($result, '修改失败');
            }

        } else {
            $id = request()->param("id");
            if ($id) {
                $data['info'] = model("jsfs")->where("id", $id)->find();
            } else {
                $data = null;
            }
            return returnRes($data, '无相关数据', $data);
        }
    }

    /**
     * @return Json
     */
    public function custom()
    {
        $params = request()->param();
        $list = Custom::where('companyid', $this->getCompanyId());
        if (!empty($params["other"]) && $params["other"] == 1) {
            $list = $list->where("other", $params["other"]);
        }
        if (!empty($params["name"])) {
            $list = $list->where("custom", "like", '%' . $params["name"] . '%');
        }
        if (!empty($params["iscustom"]) && $params["iscustom"] == 1) {
            $list = $list->where("iscustom", $params["iscustom"]);
        }
        if (!empty($params["issupplier"]) && $params["issupplier"] == 1) {
            $list = $list->where("issupplier", $params["issupplier"]);
        }
        $list = $this->getsearchcondition($params, $list)->paginate(10);
        return returnSuc($list);
    }

    /**
     * @return Json
     * @throws DbException
     */
    public function invcgsp()
    {
        $params = request()->param();
        $list = Custom::where('companyid', $this->getCompanyId());
        //业务时间
        if (!empty($params['name'])) {
            $list->where('custom|zjm|', '%' . $params['name'] . '%');
        }
        if (!empty($params['iscustom'] && $params['iscustom'] == 1)) {
            $list->where('iscustom', 1);
        }
        if (!empty($params['issupplier'] && $params['issupplier'] == 1)) {
            $list->where('issupplier', 1);
        }
        if (!empty($params['other'] && $params['other'] == 1)) {
            $list->where('other', 1);
        }
        $list = $list->paginate(10);
        return returnRes(true, '', $list);
    }

    /**
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function addcustom()
    {
        if (request()->isPost()) {
            $data = request()->post();
            $data['companyid'] = $this->getCompanyId();
            $data['add_name'] = $this->getAccount()['name'];
            $data['add_id'] = $this->getAccountId();
            if (empty(request()->post("id"))) {
                $result = model("custom")->allowField(true)->save($data);
                return returnRes($result, '添加失败');
            } else {
                $id = request()->post("id");
                $result = model("custom")->allowField(true)->save($data, ['id' => $id]);
                return returnRes($result, '修改失败');
            }

        } else {
            $id = request()->param("id");
            if ($id) {
                $data['info'] = model("custom")->where("id", $id)->find();
            } else {
                $data = null;
            }
            return returnRes($data, '无相关数据', $data);
        }
    }

    /**
     * @return Json
     * @throws DbException
     */
    public function storage()
    {
        $list = model("storage")->where("companyid", $this->getCompanyId())->paginate(10);
        return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
    }

    /**
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function addstorage()
    {
        if (request()->isPost()) {
            $data = request()->post();
            $data['companyid'] = $this->getCompanyId();
            $data['add_name'] = $this->getAccount()['name'];
            $data['add_id'] = $this->getAccountId();
            if (empty(request()->post("id"))) {
                $result = model("storage")->allowField(true)->save($data);
                return returnRes($result, '添加失败');
            } else {
                $id = request()->post("id");
//                $result = model("storage")->allowField(true)->save($data,['id' => $id]);
                $result = model("storage")->allowField(true)->save($data, ['id' => $id]);
                return returnRes($result, '修改失败');
            }

        } else {
            $id = request()->param("id");
            if ($id) {
                $data['info'] = model("storage")->where("id", $id)->find();
            } else {
                $data = null;
            }
            return returnRes($data, '无相关数据', $data);
        }
    }

    /**
     * @return Json
     * @throws DbException
     */
    public function transportation()
    {
        $list = model("transportation")->where("companyid", $this->getCompanyId())->paginate(10);
        return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
    }

    /**
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function addtransportation()
    {
        if (request()->isPost()) {
            $data = request()->post();
            $data['companyid'] = $this->getCompanyId();
            $data['add_name'] = $this->getAccount()['name'];
            $data['add_id'] = $this->getAccountId();
            if (empty(request()->post("id"))) {
                $result = model("transportation")->allowField(true)->save($data);
                return returnRes($result, '添加失败');
            } else {
                $id = request()->post("id");
                $result = model("transportation")->allowField(true)->save($data, ['id' => $id]);
                return returnRes($result, '修改失败');
            }

        } else {
            $id = request()->param("id");
            if ($id) {
                $data['info'] = model("transportation")->where("id", $id)->find();
            } else {
                $data = null;
            }
            return returnRes($data, '无相关数据', $data);
        }
    }

    /**
     * @return Json
     * @throws DbException
     */
    public function bank()
    {
        $list = model("bank")->where("companyid", $this->getCompanyId())->paginate(10);
        return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
    }

    /**
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function addbank()
    {
        if (request()->isPost()) {
            $data = request()->post();
            $data['companyid'] = $this->getCompanyId();
            $data['add_name'] = $this->getAccount()['name'];
            $data['add_id'] = $this->getAccountId();
            if (empty(request()->post("id"))) {
                $result = model("bank")->allowField(true)->save($data);
                return returnRes($result, '添加失败');
            } else {
                $id = request()->post("id");
                $result = model("bank")->allowField(true)->save($data, ['id' => $id]);
                return returnRes($result, '修改失败');
            }

        } else {
            $id = request()->param("id");
            if ($id) {
                $data['info'] = model("bank")->where("id", $id)->find();
            } else {
                $data = null;
            }
            return returnRes($data, '无相关数据', $data);
        }
    }

    /**
     * @return Json
     * @throws DbException
     */
    public function faxi()
    {
        $list = model("faxi")->where("companyid", $this->getCompanyId())->paginate(10);
        return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
    }

    /**
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function addfaxi()
    {
        if (request()->isPost()) {
            $data = request()->post();
            $data['companyid'] = $this->getCompanyId();
            $data['add_name'] = $this->getAccount()['name'];
            $data['add_id'] = $this->getAccountId();
            if (empty(request()->post("id"))) {
                $result = model("faxi")->allowField(true)->save($data);
                return returnRes($result, '添加失败');
            } else {
                $id = request()->post("id");
                $result = model("faxi")->allowField(true)->save($data, ['id' => $id]);
                return returnRes($result, '修改失败');
            }

        } else {
            $id = request()->param("id");
            if ($id) {
                $data['info'] = model("faxi")->where("id", $id)->find();
            } else {
                $data = null;
            }
            return returnRes($data, '无相关数据', $data);
        }
    }

    /**
     * 业务提成设置
     * @return Json
     * @throws DbException
     */
    public function salesmansetting()
    {
        $list = model("faxi")->where("companyid", $this->getCompanyId())->paginate(10);
        return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
    }

    /**
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function addsalesmansetting()
    {
        if (request()->isPost()) {
            $data = request()->post();
            $data['companyid'] = $this->getCompanyId();
            $data['add_name'] = $this->getAccount()['name'];
            $data['add_id'] = $this->getAccountId();
            if (empty(request()->post("id"))) {
                $result = model("faxi")->allowField(true)->save($data);
                return returnRes($result, '添加失败');
            } else {
                $id = request()->post("id");
                $result = model("faxi")->allowField(true)->save($data, ['id' => $id]);
                return returnRes($result, '修改失败');
            }
        } else {
            $id = request()->param("id");
            if ($id) {
                $data['info'] = model("faxi")->where("id", $id)->find();
            } else {
                $data = null;
            }
            return returnRes($data, '无相关数据', $data);
        }
    }

    /**
     * jiesuanfangshi 设置
     * @return Json
     * @throws DbException
     */
    public function jiesuanfangshi()
    {
        $list = model("jiesuanfangshi")->where("companyid", $this->getCompanyId())->paginate(10);
        return returnSuc($list);
    }

    /**
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function addjiesuanfangshi()
    {
        if (request()->isPost()) {
            $data = request()->post();
            $data['companyid'] = $this->getCompanyId();
            $data['add_name'] = $this->getAccount()['name'];
            $data['add_id'] = $this->getAccountId();
            if (empty(request()->post("id"))) {
                $result = model("jiesuanfangshi")->allowField(true)->save($data);
                return returnRes($result, '添加失败');
            } else {
                $id = request()->post("id");
                $result = model("jiesuanfangshi")->allowField(true)->save($data, ['id' => $id]);
                return returnRes($result, '修改失败');
            }

        } else {
            $id = request()->param("id");
            if ($id) {
                $data['info'] = model("jiesuanfangshi")->where("id", $id)->find();
            } else {
                $data = null;
            }
            return returnRes($data, '无相关数据', $data);
        }
    }

    /**
     * @return Json
     * @throws DbException
     */
    public function paymenttype()
    {
        $type = request()->param("type");
        $list = Paymenttype::with('classData')->where(array("companyid" => $this->getCompanyId(), 'type' => $type))->paginate(10);
        return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
    }

    /**
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function addpaymenttype()
    {
        if (request()->post()) {
            $data = request()->post();
            $info=model("paymentclass")->where("name", $data['class'])->find();
            if (empty($info)) {
                $data1['name'] = $data['class'];
                $data1['companyid'] = $this->getCompanyId();
                $data1['add_name'] = $this->getAccount()['name'];
                $data1['add_id'] = $this->getAccountId();
                model("paymentclass")->allowField(true)->save($data1);
                $data['classid']=model("paymentclass")->getLastInsID();
            }else{
                $data['classid']=$info["id"];
            }
            if (empty(request()->post("id"))) {
                $data['sort'] = request()->post("sort", 0);
                $data['companyid'] = $this->getCompanyId();
                $data['add_name'] = $this->getAccount()['name'];
                $data['add_id'] = $this->getAccountId();

                $result = model("paymenttype")->allowField(true)->save($data);
                return returnRes($result, '添加失败');
            } else {
                $result = model("paymenttype")->isUpdate(true)->save($data);
                return returnRes($result, '修改失败');
            }
        } else {
            $type = request()->param("type");
            $data['typelist'] = model("paymentclass")->where(array("companyid" => $this->getCompanyId(), 'type' => $type))->find();
            return returnRes($data, '无相关数据', $data);
        }
    }

    /**
     * 票据类型列表
     * @return Json
     * @throws DbException
     */
    public function pjlx()
    {
        $list = model("pjlx")->where("companyid", $this->getCompanyId())->paginate(10);
        return returnSuc($list);
    }

    /**
     * 票据类型添加
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function addpjlx()
    {
        if (request()->isPost()) {
            $data = request()->post();
            $data['companyid'] = $this->getCompanyId();
            $data['add_name'] = $this->getAccount()['name'];
            $data['add_id'] = $this->getAccountId();
            if (empty(request()->post("id"))) {
                $result = model("pjlx")->allowField(true)->save($data);
                return returnRes($result, '添加失败');
            } else {
                $id = request()->post("id");
                $result = model("pjlx")->allowField(true)->save($data, ['id' => $id]);
                return returnRes($result, '修改失败');
            }

        } else {
            $id = request()->param("id");
            if ($id) {
                $data['info'] = model("pjlx")->where("id", $id)->find();
            } else {
                $data = null;
            }
            return returnRes($data, '无相关数据', $data);
        }
    }

}