<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace app\controller\admin\v1\store;

use app\controller\admin\AuthController;
use app\services\store\StoreCategoryServices;
use crmeb\exceptions\AdminException;
use think\facade\App;
use think\facade\Db;

use app\services\other\QrcodeServices;
/**
 * 门店分类控制器
 * Class StoreCategory
 * @package app\controller\admin\v1\store
 */
class StoreCategory extends AuthController
{
    /**
     * @var StoreCategoryServices
     */
    protected $services;

    /**
     * StoreCategory constructor.
     * @param App $app
     * @param StoreCategoryServices $services
     */
    public function __construct(App $app, StoreCategoryServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 分类列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['is_show', ''],
            ['pid', 0],
            ['name', ''],
            ['id', 0],
        ]);
		$where['pid'] = (int)$where['pid'];
// 		$where['id'] = 68;
        $data = $this->services->getList($where);
       
        return $this->success($data);
    }

	/**
	 * 商品分类搜索
	 * @return mixed
	 */
	public function tree_list($type)
	{
		$list = $this->services->getTierList(1, $type);
		return $this->success($list);
	}

	/**
	 * 获取分类cascader格式数据
	 * @param $type
	 * @return mixed
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\DbException
	 * @throws \think\db\exception\ModelNotFoundException
	 */
	public function cascader_list($type = 1)
	{
		return $this->success($this->services->cascaderList(!$type));
	}

    /**
     * 修改状态
     * @param string $is_show
     * @param string $id
     */
    public function set_show($is_show = '', $id = '')
    {
        if ($is_show == '' || $id == '') return $this->fail('缺少参数');
        $this->services->setShow($id, $is_show);
        return $this->success($is_show == 1 ? '显示成功' : '隐藏成功');
    }

    /**
     * 生成添加、编辑表单
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function create($id = 0)
    {
        return $this->success($this->services->createForm($id));
    }

    /**
     * 保存分类
     * @return mixed
     */
    public function save($id = 0)
    {
        $data = $this->request->postMore([
            ['pid', 0],
            ['name', ''],
            ['sort', 0],
            ['is_show', 0],
            ['pic', '']
        ]);
        if (!$data['name']) {
            return $this->fail('请输入分类名称');
        }
		$data['type'] = 1;
		$data['group'] = 5;
		if ($id) {
			$info = $this->services->get((int)$id);
			if (!$info) {
				return $this->fail('数据不存在');
			}
			$this->services->update($id, $data);
		} else {
			$data['add_time'] = time();
			$this->services->save($data);
		}
        return $this->success('保存分类成功!');
    }

    /**
     * 删除分类
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
		if ($this->services->count(['pid' => $id])) {
			throw new AdminException('请先删除子分类!');
		}
        $this->services->delete((int)$id);
        return $this->success('删除成功!');
    }
    
    
    /**
     * 获取设备列表
     * @param $id
     * @return mixed
     */
    public function getDevice() {
        [$page, $limit, $store_name, $device_id] = $this->request->getMore([
            ['page', 1],
            ['limit', 15],
            ['store_name',''],
            ['device_id', '']
        ], true);
        $where = [];
        
        if($store_name){
            $where[] = ['store_name', 'like', '%'.$store_name.'%'];
            
        }
        if($device_id){
            $where[] = ['device_id','=',$device_id];
        }
        $page = ($page - 1) * $limit;
        $list = Db::name('two_open_device') -> where($where) -> limit($page, $limit) -> select() -> toArray();
        
        
        $count = Db::name('two_open_device') -> where($where) -> count();
        $data = compact('list', 'count');
        
        return app('json')->success($data);
    }
    
    public function saveDevice(){
        [$device_id, $store_id, $feie_sn,$feie_key,$store_name,$id,$remark] = $this->request->getMore([
            ['device_id', 0],
            ['store_id', 0],
            ['feie_sn', 0],
            ['feie_key', 0],
            ['store_name',''],
            ['id',0],
            ['remark','']
        ], true);
        Db::startTrans();
        try {
            /** @var QrcodeServices $QrcodeService */
            $QrcodeService = app()->make(QrcodeServices::class);
            $routineQrcode = $QrcodeService->getRoutineQrcodePath($store_id, 0, 11, ['device_id'=> $device_id], true);
            $data = [
                    'device_id' => $device_id,
                    'store_id' => $store_id,
                    'store_name' => $store_name,
                    'code_path' => $routineQrcode,
                    'remark' => $remark,
                    'feie_key' => $feie_key,
                    'feie_sn' => $feie_sn
                ];
                
            if($id) {
                //生成小程序地址
                
                Db::name('two_open_device') -> where('id', $id) -> update($data);
                 // 提交事务
                Db::commit();
            }else {
                
                $data['create_time'] = date('Y-m-d H:i:s');
                
                // var_dump($data);
                Db::name('two_open_device') -> where('id', $id) -> insertGetId($data);
                 // 提交事务
                Db::commit();
            }
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            Db::rollback();
            return app('json')->fail('系统异常!'.$msg);
        }
        return app('json')->successful('操作成功');
    }
    
    public function delDevice() {
        [$id] = $this->request->getMore([
            ['id',0],
        ], true);
        if(!$id) {
            return app('json')->fail('系统异常!'.$msg);
        }
        Db::startTrans();
        try {
            Db::name('two_open_device') -> where('id', $id) -> delete();
           // 提交事务
            Db::commit();
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            Db::rollback();
            return app('json')->fail('系统异常!'.$msg);
        }
        return app('json')->successful('操作成功');
    }
}
