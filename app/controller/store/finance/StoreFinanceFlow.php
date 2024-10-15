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
namespace app\controller\store\finance;


use app\services\store\finance\StoreFinanceFlowServices;
use app\services\store\SystemStoreStaffServices;
use think\facade\App;
use app\controller\store\AuthController;
use think\facade\Db;

/**
 * 门店流水
 * Class StoreFinanceFlow
 * @package app\controller\store\finance
 */
class StoreFinanceFlow extends AuthController
{
    /**
     * StoreFinanceFlow constructor.
     * @param App $app
     * @param StoreExtractServices $services
     */
    public function __construct(App $app, StoreFinanceFlowServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }


    /**
     * 显示资源列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['staff_id', 0],
            ['data', '', '', 'time'],
        ]);
        $where['keyword'] = $this->request->param('keywork', '');
        $where['store_id'] = $this->storeId;
        $where['is_del'] = 0;
        $where['trade_type'] = 1;
        $where['no_type'] = 1;
        return app('json')->success($this->services->getList($where));
    }


    /**
     * 线下账单列表
     * zz
     */ 
    public function offlinelogs() {
     
        [$page, $limit, $data, $status] = $this->request->getMore([
            ['page', 1],
            ['limit', 20],
            ['data', ''],
            ['status', ''],
        ], true);
        
        
        $where = [];
        if($this->request->param('keywork', '')) {
            $where['order_id|user_real_name'] = $this->request->param('keywork', '');    
        }
        
        if($status) {
            $where['status'] = $status;
        }
        $where['store_id'] = $this->storeId;
        // $where['status'] = 1;
        $page = ($page - 1) * $limit;
        
        if($data) {
            $arr = explode('-', $data);
            
            $sDate = date('Y-m-d H:i:s', strtotime($arr[0]));
            $eDate = date('Y-m-d H:i:s', strtotime($arr[1]) + 86399);
            $list = Db::name('two_open_offline_pay') 
                -> alias('a')
                -> field('a.*,b.phone')
                -> join('user b', 'a.user_id = b.uid', 'LEFT')
                -> where($where) 
                -> whereTime('create_time', 'between', [$sDate, $eDate]) 
                -> order('id', 'desc') 
                -> limit($page, $limit)  
                -> select();
                
            // 实收总金额
            $totalPrice = Db::name('two_open_offline_pay') -> where(['store_id' => $this->storeId, 'status' => 1]) -> whereTime('create_time', 'between', [$sDate, $eDate])-> sum('actual_price');
            $refundPrice = Db::name('two_open_offline_pay') -> where(['store_id' => $this->storeId, 'status' => 2]) -> whereTime('create_time', 'between', [$sDate, $eDate])-> sum('actual_price');
        }else{
            $list = Db::name('two_open_offline_pay') 
                -> alias('a')
                -> field('a.*,b.phone')
                -> join('user b', 'a.user_id = b.uid', 'LEFT')
                -> where($where)  
                -> order('id', 'desc') 
                -> limit($page, $limit) 
                -> select();
                
            // 实收总金额
            $totalPrice = Db::name('two_open_offline_pay') -> where(['store_id' => $this->storeId, 'status' => 1]) -> sum('actual_price');
            $refundPrice = Db::name('two_open_offline_pay') -> where(['store_id' => $this->storeId, 'status' => 2]) -> sum('actual_price');
        }
       
        $a = $list -> toArray();
        
        
        foreach ($a as $k => &$v) {
            $v['status_text'] = $v['status'] == 2 ? '已退款': '支付成功';
        }
        
        $list = $a;
        $heji = $totalPrice + $refundPrice;
        $heji = number_format($heji, 2);
        $count = Db::name('two_open_offline_pay') -> where($where) -> count();
        $data = compact('list', 'count','totalPrice','refundPrice','heji');
        // var_dump($data);
        return app('json')->success($data);
    }

     /**
     * 线下退款
     * zz
     */ 
    public function offlineRefund() {
         [$id, $remarks] = $this->request->getMore([
            ['id', 0],
            ['remarks', ''],
            
        ], true);
        
        $store_id = $this->storeId;
        // 查询订单是否存在
        $offlieData = Db::name('two_open_offline_pay') -> where(['id' => $id, 'status' => 1,'store_id' => $store_id]) -> find();
        if(!$offlieData) {
            return app('json')->fail('订单不存在');
        }
     
       
        Db::startTrans();
        try {
        
            $user = Db::name('user') -> where('uid', $offlieData['user_id']) -> find();
            
            // 1、修改订单状态refund_time
            Db::name('two_open_offline_pay') 
                -> where(['id' => $id, 'status' => 1,'store_id' => $store_id]) 
                -> update(['status' => 2, 'remarks' => $remarks,'refund_time' => date('Y-m-d H:i:s')]);
            
            // 2、添加对应的金额
            Db::name('user') -> where('uid', $offlieData['user_id']) -> inc('now_money', $offlieData['actual_price']) -> update();
            
            // 3、 添加余额变动记录
            $userMoneyLog = [
                'uid' => $offlieData['user_id'],
                'link_id' => $offlieData['id'],
                'type' => 'offline_scan_refund',
                'title' => '线下扫码-退款',
                'pm' => 1,
                'number' => $offlieData['actual_price'],
                'balance' => $offlieData['actual_price'] + $user['now_money'],
                'status' => 1,
                'mark' => '退款金额'. $offlieData['actual_price'] . '元,[' .$offlieData['store_name']. ']',
                'add_time' => time()
            ];
            Db::name('user_money') -> insert($userMoneyLog);
            Db::commit();
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            Db::rollback();
            return app('json')->fail('系统异常!'.$msg);
        }
        
        return app('json')->successful('操作成功');
    }

    /**
     * 增加备注
     * @param $id
     * @return mixed
     */
    public function mark($id)
    {
        [$mark] = $this->request->getMore([
            ['mark', '']
        ], true);
        if (!$id || !$mark) {
            return app('json')->fail('缺少参数');
        }
        $info = $this->services->get((int)$id);
        if (!$info) {
            return app('json')->fail('账单流水不存在');
        }
        if (!$this->services->update($id, ['mark' => $mark])) {
            return app('json')->fail('备注失败');
        }
        return app('json')->success('备注成功');
    }

    /**
     * 获取店员select
     * @param SystemStoreStaffServices $services
     * @return mixed
     */
    public function getStaffSelect(SystemStoreStaffServices $services)
    {
        $where['store_id'] = $this->storeId;
        $where['is_del'] = 0;
        $where['status'] = 1;
        return app('json')->success($services->getSelectList($where));
    }

    /**
     * 账单记录
     * @return mixed
     */
    public function fundRecord()
    {
        $where = $this->request->getMore([
            ['timeType', 'day'],
            ['data', '', '', 'time'],
        ]);
        $where['store_id'] = $this->storeId;
        $where['trade_type'] = 1;
        $where['no_type'] = 1;
        return app('json')->success($this->services->getFundRecord($where));
    }

    /**
     * 账单详情
     * @param $ids
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function fundRecordInfo()
    {
        $where = $this->request->getMore([
            ['ids', ''],
            ['staff_id', 0]
        ]);
        $where['keyword'] = $this->request->param('keyword', '');
        $where['id'] = $where['ids'] ? explode(',', $where['ids']) : [];
        unset($where['ids']);
        $where['is_del'] = 0;
        $where['store_id'] = $this->storeId;
        $where['trade_type'] = 1;
        return app('json')->success($this->services->getList($where));
    }
}
