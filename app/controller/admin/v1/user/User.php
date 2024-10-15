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
namespace app\controller\admin\v1\user;

use app\dao\user\UserMoneyDao;
use app\jobs\BatchHandleJob;
use app\services\other\queue\QueueServices;
use app\services\product\product\StoreProductLogServices;
use app\services\user\group\UserGroupServices;
use app\services\user\label\UserLabelServices;
use app\services\user\UserBatchProcessServices;
use app\services\user\UserMoneyServices;
use app\services\user\UserServices;
use app\controller\admin\AuthController;
use app\services\user\FuyouServices;
use app\services\user\UserSpreadServices;
use app\services\user\UserWechatuserServices;
use PhpOffice\PhpSpreadsheet\IOFactory;
use think\exception\ValidateException;
use think\facade\App;
use think\facade\Db;
use crmeb\services\FileService;
use app\services\other\export\ExportServices;
use think\facade\Log;
class User extends AuthController
{
    /**
     * user constructor.
     * @param App $app
     * @param UserServices $services
     */
    public function __construct(App $app, UserServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 显示资源列表头部
     *
     * @return \think\Response
     */
    public function type_header()
    {
        $list = $this->services->typeHead();
        return $this->success(compact('list'));
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['page', 1],
            ['limit', 20],
            ['nickname', ''],
            ['status', ''],
            ['pay_count', ''],
            ['is_promoter', ''],
            ['order', ''],
            ['data', ''],
            ['user_type', ''],
            ['country', ''],
            ['province', ''],
            ['city', ''],
            ['user_time_type', ''],
            ['user_time', ''],
            ['sex', ''],
            [['level', 0], 0],
            [['group_id', 'd'], 0],
            [['label_id', 'd'], 0],
            ['now_money', 'normal'],
            ['field_key', ''],
            ['isMember', ''],
            ['label_ids', '']
        ]);
        if ($where['label_ids']) {
            $where['label_id'] = explode(',', $where['label_ids']);
            unset($where['label_ids']);
        }
        $where['user_time_type'] = $where['user_time_type'] == 'all' ? '' : $where['user_time_type'];
        return $this->success($this->services->index($where));
    }

    /**
     * 补充信息表单
     * @param $id
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function extendInfoForm($id)
    {
        return $this->success($this->services->extendInfoForm((int)$id));
    }

    /**
     * 保存用户补充信息
     * @param $id
     * @return mixed
     */
    public function saveExtendForm($id)
    {
        $data = $this->request->post();
        if (!$data) {
			return $this->fail('请提交要保存信息');
		}
		$this->services->saveExtendForm((int)$id, $data);
        return $this->success('保存成功');
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        return $this->success($this->services->saveForm());
    }

    /**
     * 保存新建的资源
     *
     * @param \think\Request $request
     * @return \think\Response
     */
    public function save()
    {
        $data = $this->request->postMore([
            ['is_promoter', 0],
            ['real_name', ''],
            ['card_id', ''],
            ['birthday', ''],
            ['mark', ''],
            ['status', 0],
            ['level', 0],
            ['phone', 0],
            ['addres', ''],
            ['label_id', []],
            ['group_id', 0],
            ['pwd', ''],
            ['true_pwd', ''],
            ['spread_open', 1],
            ['sex', 0],
            ['provincials', ''],
            ['province', 0],
            ['city', 0],
            ['area', 0],
            ['street', 0],
            ['extend_info', []],
        ]);
		$this->validate(['pwd' => $data['pwd']],  \app\validate\admin\user\UserValidate::class);
        if ($data['phone']) {
            if (!check_phone($data['phone'])) {
                return $this->fail('手机号码格式不正确');
            }
            if ($this->services->count(['phone' => $data['phone']])) {
                return $this->fail('手机号已经存在不能添加相同的手机号用户');
            }
            $data['nickname'] = substr_replace($data['phone'], '****', 3, 4);
        }
        if ($data['card_id']) {
			try {
				if (!check_card($data['card_id'])) return $this->fail('请输入正确的身份证');
 			} catch (\Throwable $e) {
//				return $this->fail('请输入正确的身份证');
 			}
        }
        if ($data['birthday']) {
            if (strtotime($data['birthday']) > time()) return $this->fail('生日请选择今天之前日期');
        }
        if ($data['pwd']) {
            if (!$data['true_pwd']) {
                return $this->fail('请输入确认密码');
            }
            if ($data['pwd'] != $data['true_pwd']) {
                return $this->fail('两次输入的密码不一致');
            }
            // if ($data['pwd'] == '123456') {
            //     return $this->fail('您设置的密码太过简单');
            // }
            $data['pwd'] = md5($data['pwd']);
        } else {
            unset($data['pwd']);
        }
        unset($data['true_pwd']);
        $data['avatar'] = sys_config('h5_avatar');
        $data['adminId'] = $this->adminId;
        $data['user_type'] = 'h5';
        $lables = $data['label_id'];
        unset($data['label_id']);
        foreach ($lables as $k => $v) {
            if (!$v) {
                unset($lables[$k]);
            }
        }
        $data['birthday'] = empty($data['birthday']) ? 0 : strtotime($data['birthday']);
        $data['add_time'] = time();
        $data['extend_info'] = $this->services->handelExtendInfo($data['extend_info']);
        $this->services->transaction(function () use ($data, $lables) {
            $res = true;
            $userInfo = $this->services->save($data);
            if ($lables) {
                $res = $this->services->saveSetLabel([$userInfo->uid], $lables);
            }
            if ($data['level']) {
                $res = $this->services->saveGiveLevel((int)$userInfo->uid, (int)$data['level']);
            }
            if (!$res) {
                throw new ValidateException('保存添加用户失败');
            }
            event('user.register', [$this->services->get((int)$userInfo->uid), true, 0]);
        });
        event('user.create', $data);
        return $this->success('添加成功');
    }

    /**
     * 显示指定的资源
     *
     * @param int $id
     * @return \think\Response
     */
    public function read($id)
    {
        if (is_string($id)) {
            $id = (int)$id;
        }
        return $this->success($this->services->read($id));
    }

    /**
     * 赠送会员等级
     * @param int $uid
     * @return mixed
     * */
    public function give_level($id)
    {
        if (!$id) return $this->fail('缺少参数');
        return $this->success($this->services->giveLevel((int)$id));
    }

    /**
     * 执行赠送会员等级
     * @param int $uid
     * @return mixed
     * */
    public function save_give_level($id)
    {
        if (!$id) return $this->fail('缺少参数');
        [$level_id] = $this->request->postMore([
            ['level_id', 0],
        ], true);
        return $this->success($this->services->saveGiveLevel((int)$id, (int)$level_id) ? '赠送成功' : '赠送失败');
    }

    /**
     * 赠送付费会员时长
     * @param int $uid
     * @return mixed
     * */
    public function give_level_time($id)
    {
        if (!$id) return $this->fail('缺少参数');
        return $this->success($this->services->giveLevelTime((int)$id));
    }

    /**
     * 执行赠送付费会员时长
     * @param int $uid
     * @return mixed
     * */
    public function save_give_level_time($id)
    {
        if (!$id) return $this->fail('缺少参数');
		[$days_status, $days] = $this->request->postMore([
			['days_status', 1],
			['days', 0],
		], true);
		return $this->success($this->services->saveGiveLevelTime((int)$id, (int)$days, (int)$days_status) ? '赠送成功' : '赠送失败');
    }

    /**
     * 清除会员等级
     * @param int $uid
     * @return json
     */
    public function del_level($id)
    {
        if (!$id) return $this->fail('缺少参数');
        return $this->success($this->services->cleanUpLevel((int)$id) ? '清除成功' : '清除失败');
    }

    /**
     * 设置会员分组
     * @param $id
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function set_group()
    {
        [$uids, $all, $where] = $this->request->postMore([
            ['uids', []],
            ['all', 0],
            ['where', []],
        ], true);
        return $this->success($this->services->setGroup($uids, $all, $where));
    }

    /**
     * 保存会员分组
     * @param $id
     * @return mixed
     */
    public function save_set_group()
    {
        [$group_id, $uids, $all, $where] = $this->request->postMore([
            ['group_id', 0],
            ['uids', ''],
            ['all', 0],
            ['where', ""],
        ], true);
        if (!$uids && $all == 0) return $this->fail('缺少参数');
        if (!$group_id) return $this->fail('请选择分组');
        $type = 2;//代表设置用户标签
        if ($all == 0) {
            $uids = explode(',', $uids);
            $where = [];
        }
        if ($all == 1) {
            $where = $where ? json_decode($where, true) : [];
            /** @var UserWechatuserServices $userWechatUser */
            $userWechatUser = app()->make(UserWechatuserServices::class);
            $fields = 'u.uid';
            [$list, $count] = $userWechatUser->getWhereUserList($where, $fields);
            $uids = array_unique(array_column($list, 'uid'));
            $where = [];
        }
        /** @var UserGroupServices $userGroup */
        $userGroup = app()->make(UserGroupServices::class);
        if (!$userGroup->getGroup($group_id)) {
            return $this->fail('该分组不存在');
        }
        /** @var QueueServices $queueService */
        $queueService = app()->make(QueueServices::class);
        $queueService->setQueueData($where, 'uid', $uids, $type, $group_id);
        //加入队列
        BatchHandleJob::dispatch([$group_id, $type]);
        return $this->success('后台程序已执行用户分组任务!');
    }

    /**
     * 设置用户标签
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function set_label()
    {
        [$uids, $all, $where] = $this->request->postMore([
            ['uids', []],
            ['all', 0],
            ['where', ""],
        ], true);
        return $this->success($this->services->setLabel($uids, $all, $where));
    }

    /**
     * 保存用户标签
     * @return mixed
     */
    public function save_set_label()
    {
        [$lables, $uids, $all, $where] = $this->request->postMore([
            ['label_id', []],
            ['uids', ''],
            ['all', 0],
            ['where', ""],
        ], true);
        if (!$uids && $all == 0) return $this->fail('缺少参数');
        if (!$lables) return $this->fail('请选择标签');
        if ($all == 0) {
            $uids = is_array($uids) ? $uids : explode(',', $uids);
            $where = [];
        }
        if ($all == 1) {
            $where = $where ? (is_string($where) ? json_decode($where, true) : $where) : [];
            /** @var UserWechatuserServices $userWechatUser */
            $userWechatUser = app()->make(UserWechatuserServices::class);
            $fields = 'u.uid';
            [$list, $count] = $userWechatUser->getWhereUserList($where, $fields);
            $uids = array_unique(array_column($list, 'uid'));
            $where = [];
        }
        /** @var UserLabelServices $userLabelServices */
        $userLabelServices = app()->make(UserLabelServices::class);
        $count = $userLabelServices->getCount([['id', 'IN', $lables]]);
        if ($count != count($lables)) {
            return app('json')->fail('用户标签不存在或被删除');
        }
        $type = 3;//批量设置用户标签
        /** @var QueueServices $queueService */
        $queueService = app()->make(QueueServices::class);
        $queueService->setQueueData($where, 'uid', $uids, $type, $lables);
        //加入队列
        BatchHandleJob::dispatch([$lables, $type]);
        return $this->success('后台程序已执行批量设置用户标签任务!');
    }

    /**
     * 编辑其他
     * @param $id
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function edit_other($id)
    {
        if (!$id) return $this->fail('数据不存在');
        return $this->success($this->services->editOther((int)$id));
    }

    /**
     * 执行编辑其他
     * @param int $id
     * @return mixed
     */
    public function update_other($id)
    {
        $data = $this->request->postMore([
            ['money_status', 0],
            ['money', 0],
            ['integration_status', 0],
            ['integration', 0],
        ]);
        if (!$id) return $this->fail('数据不存在');
        $data['adminId'] = $this->adminId;
        $data['money'] = (string)$data['money'];
        $data['integration'] = (string)$data['integration'];
        $data['is_other'] = true;
        $res=$this->services->updateInfo($id, $data);
        if($res&&$data['money']>0){
            $userInfo = $this->services->get($id);
            $FuyouServices = app()->make(FuyouServices::class);
            $user=$FuyouServices->queryUserInfo($userInfo['phone']);
            // echo json_encode($user);die;
            // echo $data['money_status'];
            if($user['resultCode']=="000000"){
                if($data['money_status']==1){
                    $fuyoures=$FuyouServices->recharge($user['data']['openId'],$data['money'],0);
                }else if($data['money_status']==2){
                    // echo ;
                    $fuyoures=$FuyouServices->adjust($userInfo['phone'],$user['data']['openId'],$userInfo['now_money']*100,"");
                }
            }else{
                $res=$FuyouServices->registerUser($userInfo['phone'],"123456",time(),$userInfo['real_name'],01,"");
                if($res['resultCode']=="000000"){
                    $user=$FuyouServices->queryUserInfo($userInfo['phone']);
                    if($data['money_status']==1){
                        $fuyoures=$FuyouServices->recharge($user['data']['openId'],$data['money'],0);
                    }else if($data['money_status']==2){
                        // $fuyoures=$FuyouServices->recharge($user['data']['openId'],$data['money'],0);
                        $fuyoures=$FuyouServices->adjust($userInfo['phone'],$user['data']['openId'],$userInfo['now_money']*100,"");
                    }
                }
            }
            // echo $fuyoures['resultCode'];die;
            // if($fuyoures['resultCode']=="000000"){
                return $this->success( $res? '修改成功' : '修改失败');
            // }
        }else{
            return $this->success( $res? '修改成功' : '修改失败');
        }
        
    }

    /**
     * 修改user表状态
     *
     * @return array
     */
    public function set_status($status, $id)
    {
//        if ($status == '' || $id == 0) return $this->fail('参数错误');
//        UserModel::where(['uid' => $id])->update(['status' => $status]);
        return $this->success($status == 0 ? '禁用成功' : '解禁成功');
    }

    /**
     * 编辑会员信息
     * @param $id
     * @return mixed|\think\response\Json|void
     */
    public function edit($id)
    {
        if (!$id) return $this->fail('数据不存在');
        return $this->success($this->services->edit($id));
    }

    public function update($id)
    {
        $data = $this->request->postMore([
            ['money_status', 0],
            ['is_promoter', 0],
            ['real_name', ''],
            ['card_id', ''],
            ['birthday', ''],
            ['mark', ''],
            ['money', 0],
            ['integration_status', 0],
            ['integration', 0],
            ['status', 0],
            ['level', 0],
            ['phone', 0],
            ['addres', ''],
            ['label_id', []],
            ['group_id', 0],
            ['pwd', ''],
            ['true_pwd'],
            ['spread_open', 1],
            ['sex', 0],
            ['provincials', ''],
            ['province', 0],
            ['city', 0],
            ['area', 0],
            ['street', 0],
            ['spread_uid', -1],
            ['extend_info', []]
        ]);
        if ($data['phone']) {
            if (!check_phone($data['phone'])) return $this->fail('手机号码格式不正确');
        }
        if ($data['card_id']) {
			try {
				if (!check_card($data['card_id'])) return $this->fail('请输入正确的身份证');
 			} catch (\Throwable $e) {
//				return $this->fail('请输入正确的身份证');
 			}
        }
        if ($data['birthday']) {
            if (strtotime($data['birthday']) > time()) return $this->fail('生日请选择今天之前日期');
        }
        if ($data['pwd']) {
            if (!$data['true_pwd']) {
                return $this->fail('请输入确认密码');
            }
            if ($data['pwd'] != $data['true_pwd']) {
                return $this->fail('两次输入的密码不一致');
            }
			if ($data['pwd'] == '123456') {
				return $this->fail('您设置的密码太过简单');
			}
			$this->validate(['pwd' => $data['pwd']],  \app\validate\admin\user\UserValidate::class);
            $data['pwd'] = md5($data['pwd']);
        } else {
            unset($data['pwd']);
        }
        $userInfo = $this->services->get($id);
        if (!$userInfo) {
            return $this->fail('用户不存在');
        }
        if (!in_array($data['spread_uid'], [0, -1])) {
            $spreadUid = $data['spread_uid'];
            if ($id == $spreadUid) {
                return $this->fail('上级推广人不能为自己');
            }
            if (!$this->services->count(['uid' => $spreadUid])) {
                return $this->fail('上级用户不存在');
            }
            $spreadInfo = $this->services->get($spreadUid);
            if ($spreadInfo->spread_uid == $id) {
                return $this->fail('上级推广人不能为自己下级');
            }
        }
        unset($data['true_pwd']);
        if (!$id) return $this->fail('数据不存在');
        $data['adminId'] = $this->adminId;
        $data['money'] = (string)$data['money'];
        $data['integration'] = (string)$data['integration'];
		if ($data['extend_info']) {
			$data['extend_info'] = $this->services->handelExtendInfo($data['extend_info']);
		}
        return $this->success($this->services->updateInfo((int)$id, $data) ? '修改成功' : '修改失败');
    }

    /**
     * 获取单个用户信息
     * @param $id 用户id
     * @return mixed
     */
    public function oneUserInfo($id)
    {
        $data = $this->request->getMore([
            ['type', ''],
        ]);
        $id = (int)$id;
        if ($data['type'] == '') return $this->fail('缺少参数');
        return $this->success($this->services->oneUserInfo($id, $data['type']));
    }

    /**
     * 同步微信粉丝用户
     * @return mixed
     */
    public function syncWechatUsers()
    {
        $this->services->syncWechatUsers();
        return $this->success('加入消息队列成功，正在异步执行中');
    }

    /**
     * 商品浏览记录
     * @param $id
     * @param StoreProductLogServices $services
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function visitList($id, StoreProductLogServices $services)
    {
        $where['uid'] = (int)$id;
        $where['type'] = 'visit';
        return app('json')->success($services->getList($where, 'product_id'));
    }

    /**
     * 获取推广人记录
     * @param $id
     * @param UserSpreadServices $services
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function spreadList($id, UserSpreadServices $services)
    {
        $where['store_id'] = 0;
        $where['staff_id'] = 0;
        $where['uid'] = $id;
        return app('json')->success($services->getSpreadList($where, '*', ['spreadUser', 'admin'], false));
    }

	/**
	 * 用户批量操作
	 * @return mixed
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\DbException
	 * @throws \think\db\exception\ModelNotFoundException
	 */
	public function batchProcess(UserBatchProcessServices $batchProcessServices)
	{
		[$type, $uids, $all, $where, $data] = $this->request->postMore([
			['type', 1],
			['uids', ''],
			['all', 0],
			['where', ""],
			['data', []]
		], true);
		if (!$uids && $all == 0) return $this->fail('请选择批处理用户');
		if (!$data) {
			return $this->fail('请选择批处理数据');
		}
		//批量操作
		$batchProcessServices->batchProcess((int)$type, $uids, $data, !!$all, (array)$where);
		return app('json')->success('已加入消息队列,请稍后查看');
	}
	//余额变动列表
    public function userMoneyList()
    {
        [$where_time, $paid, $phone] = $this->request->postMore([
                                                                    ['where_time', null],
                                                                    ['paid', null],
                                                                    ['phone', null],
                                                                ], true);
        $userMoneyServices = app()->make(UserMoneyServices::class);
        
        return app('json')->success($userMoneyServices->getUserMoneyListAdmin(0, $where_time, '*', $paid, $phone));
    }
	//余额变动小项
    public function userMoneyItem()
    {
        [$where_time, $paid, $phone] = $this->request->postMore([
                                                                    ['where_time', null],
                                                                    ['paid', null],
                                                                    ['phone', null],
                                                                ], true);
        $where = [];
        $system_add = Db::name('user_money');
        $system_sub = Db::name('user_money');
        $lvguxiaofei = Db::name('user_money');
        $otherxiaofei = Db::name('user_money');
        $refund = Db::name('user_money');
        if ($phone) {
            //$userService = app()->make(UserServices::class);
            $userInfo = Db::name('user')->where('phone', 'like', '%' . $phone . "%")->field('uid')->select()->toArray();
            if ($userInfo) {
                $where['uid'] = array_column($userInfo, 'uid');
                $system_add = $system_add->whereIn('uid', $where['uid']);
                $system_sub = $system_sub->whereIn('uid', $where['uid']);
                $lvguxiaofei = $lvguxiaofei->whereIn('uid', $where['uid']);
                $otherxiaofei = $otherxiaofei->whereIn('uid', $where['uid']);
                $refund = $refund->whereIn('uid', $where['uid']);
            }
        }

        if ($where_time) {

            $where['add_time'] = explode('-', $where_time);//str_replace('-', '/', $where_time);
            $where['add_time'][0] = strtotime($where['add_time'][0]);
            $where['add_time'][1] = strtotime($where['add_time'][1]);
            $system_add = $system_add->whereBetween('add_time', $where['add_time']);
            $system_sub = $system_sub->whereBetween('add_time', $where['add_time']);
            $lvguxiaofei = $lvguxiaofei->whereBetween('add_time', $where['add_time']);
            $otherxiaofei = $otherxiaofei->whereBetween('add_time', $where['add_time']);
            $refund = $refund->whereBetween('add_time', $where['add_time']);
        }

        $data = [];
        $data['system_add'] = $system_add->where('type', 'system_add')->sum('number');
        //return app('json')->success($data['system_add']);
        $data['system_sub'] = $system_sub->where('type', 'system_sub')->sum('number');
        $data['lvguxiaofei'] = $lvguxiaofei->where('type', 'pay_product')->where('link_id', 0)->sum('number');
        $data['otherxiaofei'] = $otherxiaofei->where('type', 'pay_product')->where('link_id', '>', 0)->sum('number');
        $data['refund'] = $refund->where('type', 'pay_product_refund')->sum('number');
        return app('json')->success([
                                        [
                                            'type'      => 'system_add',
                                            'name'      => '系统充值金额',
                                            'field'     => '元',
                                            'count'     => $data['system_add'],
                                            'className' => 'logo-yen',
                                            'col'       => 6,
                                        ],
                                        [
                                            'type'      => 'system_sub',
                                            'name'      => '系统减少金额',
                                            'field'     => '元',
                                            'count'     => $data['system_sub'],
                                            'className' => 'logo-usd',
                                            'col'       => 6,
                                        ],
                                        [
                                            'type'      => 'lvguxiaofei',
                                            'name'      => '绿谷消费金额',
                                            'field'     => '元',
                                            'count'     => $data['lvguxiaofei'],
                                            'className' => 'logo-bitcoin',
                                            'col'       => 6,
                                        ],
                                        [
                                            'type'      => 'otherxiaofei',
                                            'name'      => '其他消费金额',
                                            'field'     => '元',
                                            'count'     => $data['otherxiaofei'],
                                            'className' => 'ios-bicycle',
                                            'col'       => 6,
                                        ],
                                        [
                                            'type'      => 'refund',
                                            'name'      => '退款金额',
                                            'field'     => '元',
                                            'count'     => $data['refund'],
                                            'className' => 'ios-bicycle',
                                            'col'       => 6,
                                        ],
                                    ]);
    }
    /**
     * User:LiaoYi
     * DateTime:2023-07-30 11:01
     * Describe:导入usermoney
     * @return \think\Response
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function import()
    {
        $data = $this->request->getMore([
                                            ['file', ""]
                                        ]);
        if (!$data['file']) return app('json')->fail('请上传文件');
        $file = public_path() . substr($data['file'], 1);

        if (!$file) return app('json')->error('找不到文件');;
        /* $pathInfo = pathinfo($file, PATHINFO_EXTENSION);
        if (!$pathInfo || $pathInfo != "xlsx") throw new ValidateException('必须上传xlsx格式文件');*/

        $suffix = ucwords(pathinfo($file, PATHINFO_EXTENSION));//获取文件扩展名
        //加载读取模型
        $readModel = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($suffix);
        // 创建读操作
        // 打开文件 载入excel表格
        $spreadsheet = $readModel->load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->getHighestColumn();
        $highestRow = $sheet->getHighestRow();
        $lines = $highestRow - 1;
        if ($lines <= 0) {
            throw new ValidateException('数据不能为空');
        }
        $readExcelService = \app()->make(FileService::class);

        for ($i = 2; $i <= $highestRow; $i++) {
            $t1 = $readExcelService->objToStr($sheet->getCellByColumnAndRow(1, $i)->getValue());
            $t2 = $readExcelService->objToStr($sheet->getCellByColumnAndRow(2, $i)->getValue());
            $t3 = $readExcelService->objToStr($sheet->getCellByColumnAndRow(3, $i)->getValue());
            $t4 = $readExcelService->objToStr($sheet->getCellByColumnAndRow(4, $i)->getValue());
            $t5 = $readExcelService->objToStr($sheet->getCellByColumnAndRow(5, $i)->getValue());
            if ($t3 && $t4) {
                $data[] = [$t1, $t2, $t3, $t4, $t5];
            }

        }
       
        $fuyou = \app()->make(FuyouServices::class);
        unset($data['file']);

        foreach (array_values($data) as  $i=>$d){
            
            $user = Db::name('user')->where('delete_time',null)->where('phone',$d[2])->field('uid,now_money')->find();
             Log::error('&&&&'.json_encode($user,JSON_UNESCAPED_UNICODE));
            $d[5] = '失败';
            $data[$i] = $d;
            if (! $user){
                $d[5] = '用户不存在';
                $data[$i] = $d;
                continue;
            }
           
            $new_now_money = bcadd((string)$user['now_money'] ,(string)$d[3],2);
            Db::name("user_money")->insert(array(
                                               'uid'      => $user['uid'], 'type' => 'system_add',
                                               'title'    => '系统增加余额', 'mark' => '系统增加'.round($d[3],2).'余额',
                                               'number'   => $d[3],
                                               'balance'  => $new_now_money,
                                               'pm'       => 1,
                                               'status'   => 1,
                                               'add_time' => strtotime(date('Y-m-d H:i:s'))));
            Db::name('user')->where('phone',$d[2])->where('delete_time',null)->update(['now_money'=>$new_now_money]);
            $fuyou->adjustFuyouByUid($user['uid'],$new_now_money);
            $d[5] = '成功';
            $data[$i] =$d;
        }
        $ex = \app()->make(ExportServices::class);
        
        return app('json')->success($ex->userMoneyListResult($data,0));
    }
}
