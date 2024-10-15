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
namespace app\controller\api\v1\order;

use app\Request;
use app\services\pay\PayServices;
use app\services\other\ExpressServices;
use app\services\product\branch\StoreBranchProductServices;
use app\services\user\UserAddressServices;
use app\services\user\UserInvoiceServices;
use app\services\user\UserServices;
use app\services\wechat\WechatUserServices;
use app\services\activity\{collage\UserCollageServices,
    discounts\StoreDiscountsServices,
    lottery\LuckLotteryServices,
    bargain\StoreBargainServices,
    combination\StorePinkServices
};
use app\services\activity\coupon\StoreCouponIssueServices;
use crmeb\services\wechat\MiniProgram;
use app\services\order\{OtherOrderServices,
    StoreCartServices,
    StoreDeliveryOrderServices,
    StoreOrderCartInfoServices,
    StoreOrderCommentServices,
    StoreOrderComputedServices,
    StoreOrderCreateServices,
    StoreOrderEconomizeServices,
    StoreOrderRefundServices,
    StoreOrderServices,
    StoreOrderStatusServices,
    StoreOrderSuccessServices,
    StoreOrderTakeServices,
    StoreOrderPromotionsServices,
    StoreOrderWriteOffServices
};
use app\services\pay\OrderPayServices;
use app\services\pay\YuePayServices;
use app\services\product\product\StoreProductReplyServices;
use app\services\product\shipping\ShippingTemplatesServices;
use app\services\store\SystemStoreServices;
use crmeb\services\CacheService;

use think\facade\Db;


use think\exception\ValidateException;
use crmeb\services\wechat\PaymentTwoOpen;

/**
 * 订单控制器
 * Class StoreOrderController
 * @package app\controller\api\order
 */
class TwoOpenOrderController
{

    /**
     * @var StoreOrderServices
     */
    protected $services;

    /**
     * @var int[]
     */
    protected $getChennel = [
        'weixin' => 0,
        'routine' => 1,
        'weixinh5' => 2,
        'pc' => 3,
        'app' => 4
    ];


    /**
     * StoreOrderController constructor.
     * @param StoreOrderServices $services
     */
    public function __construct(StoreOrderServices $services)
    {
        $this->services = $services;
    }

    /**
     * 订单创建
     * @param Request $request
     * @param StoreOrderCreateServices $createServices
     * @param $key
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function create(Request $request)
    {
        
        $app =  array(
            'id' => 'wtyVWcgiEw', //请在接口文档中查看自己的应用ID
            'secret' => '5940c96d92cffd1f7e1dfb43a20b8dcf', //你设置的secret
        );

        $ts = time();
        $sign = md5(md5($app['secret']) . $ts);
        $api = 'http://iot-api.unisoft.cn/' . $app['id'];
        $url = $api . "/device/control/?sign=$sign&ts=$ts";

    
        $uid = (int)$request->uid();
        [$store_name, $store_id, $price, $actual_price,$user_real_name, $discount, $pay_type,$from, $password,$device_id] = $request->postMore([
            ['store_name', ''],
            ['store_id', ''],
            ['price', ''],
            ['actual_price', ''],
            ['user_real_name', ''],
            ['discount', ''],
            ['pay_type', 'weixin'],
            ['from', 'weixin'],
            ['password',''],
            ['device_id', 0]
        ], true);
        $order_id = date('YmdHis'). str_pad($uid, 6, '0', STR_PAD_LEFT) . mt_rand(1000, 9999);
        
        
        $data = array(
            'device' => $device_id, //设备id,在控制台查看，或通过接口拉取，可一次传多台
            'order' => array('play:gbk:16' => '城投金卡已支付成功'.$actual_price.'元'),
        );

        $pwd = Db::name('user') -> where('uid', $uid) -> value('fuyou_pwd');
        if($pwd != $password) {
            return app('json')->fail('支付密码不正确');
        }
        
        Db::startTrans();
        try {
            $orderData = [
                'store_name' => $store_name,
                'store_id' => $store_id,
                'price' => $price,
                'actual_price' => $actual_price,
                'discount' => $discount,
                'pay_type' => $pay_type,
                'user_real_name' => $user_real_name,
                'create_time' => date('Y-m-d H:i:s'),
                'order_id' => $order_id,
                'user_id' => $uid,
                'status' => 1
            ];
            
            $now_money = Db::name('user') -> where('uid', $uid) -> value('now_money');
            
            if($actual_price < 1) {
                return app('json')->fail('支付金额不能小于 1!');
            }
            
            if($actual_price > $now_money) {
                return app('json')->fail('余额不足请先充值!');
            }
            
            $id = Db::name('two_open_offline_pay') -> insertGetId($orderData);
           
            //  扣出对应的余额
            Db::name('user') -> where('uid', $uid) -> where('now_money' ,'>=', $actual_price ) -> dec('now_money', $actual_price) -> update();
            
            // 添加余额变动记录
            $userMoneyLog = [
                'uid' => $uid,
                'link_id' => $id,
                'type' => 'offline_scan_yue_pay',
                'title' => '线下扫码余额支付',
                'pm' => 0,
                'number' => $actual_price,
                'balance' => $now_money - $actual_price,
                'status' => 1,
                'mark' => '余额支付'. $actual_price . '元,['.$store_name.']门店扫码',
                'add_time' => time()
            ];
            Db::name('user_money') -> insert($userMoneyLog);
            
           // 提交事务
            Db::commit();
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            Db::rollback();
            return app('json')->fail('系统异常!'.$msg);
        }
        
        
        if($device_id) {
            
            $httpRet = $this->httpAction($url, $data); 
            
            $this -> printer($device_id, $store_name, $price, $actual_price, $order_id);
            
            // var_dump($httpRet);return;
        }
        
        return app('json')->successful('支付成功', ['msg' => '支付成功']);
    }

    /**
     * 线下支付记录
     * @param Request $request
     * @return mixed
     */
    public function getOfflineLogs(Request $request) {
        $uid = (int)$request->uid();
        $res = Db::name('two_open_offline_pay') -> where(['user_id' => $uid, 'status' => 1]) -> order('id', 'desc') -> select() -> toArray();
        return app('json')->successful('ok', $res);
    }
    
    // 打印机接口对接 ，
    public function printer($device_id, $store_name, $price, $actual_price, $order_id){
        
        $device = Db::name('two_open_device') -> where('device_id', $device_id)  -> find();
        $times = 1;
        $time = time();         //请求时间
        $user = 'lfctxpjdy@163.com';
        $ukey = 'qGgmyCNeuuvPDL5s';
        
        
        // var_dump($device); return;
        $content = '<CB>城投金卡</CB><BR><BR>';
        $content .= ' ' .$store_name . ' <BR><BR>';
        $content .= ' 时间：' . date('Y.m.d H:i:s') . ' <BR><BR>';
        $content .= ' 原金额:'.number_format($price, 2) .' <BR><BR>';
        $content .= ' 折后金额:'.number_format($actual_price,  2).' <BR><BR>';
        $content .= ' 订单号:'.$order_id.' <BR>';
        $content .= '<CB>***城投金卡***</CB><BR><BR>';
        $content .= '--------------------------------';
        
        // $content .= '<QR>http://www.feieyun.com</QR>';//把二维码字符串用标签套上即可自动生成二维码
        if($device) {
            $sn = $device['feie_sn'];
            $msgInfo = array(
                'user'=>$user,
                'stime'=>$time,
                'sig'=> $this -> signature($user, $ukey, $time),
                'apiname'=>'Open_printMsg',
                'sn'=>$sn,
                'content'=>$content,
                'times'=>$times//打印次数
            );
            $ip = 'api.feieyun.cn';      //接口IP或域名
            $client = new HttpClient($ip, 80);
            if(!$client->post('/Api/Open/',$msgInfo)){
                echo 'error';
            }else{
                //服务器返回的JSON字符串，建议要当做日志记录起来
                $result = $client->getContent();
                // echo $result;
                // var_dump(json_decode($result));
            }
        }
    }
    
    // 简单http请求方法
    public function httpAction($url, $data) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, true);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        //curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        //curl_setopt($ch, CURLOPT_TIMEOUT, 2);

        $ret = curl_exec($ch);
        curl_close($ch);
        return json_decode($ret, true);
    }
    
    
      /**
   * [signature 生成签名]
   * @param  [string] $time [当前UNIX时间戳，10位，精确到秒]
   * @return [string]       [接口返回值]
   */
  public function signature($user,$ukey,$time){
    return sha1($user.$ukey.$time);//公共参数，请求公钥
  }
}
