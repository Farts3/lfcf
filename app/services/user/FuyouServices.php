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
declare (strict_types=1);

namespace app\services\user;

use app\jobs\notice\SmsJob;
use app\services\BaseServices;
use app\dao\user\UserDao;
use app\services\message\sms\SmsRecordServices;
use app\services\wechat\WechatUserServices;
use crmeb\services\CacheService;
use think\exception\ValidateException;
use think\facade\Config;
use think\facade\Log;
use think\facade\Db;
/**
 *
 * Class LoginServices
 * @package app\services\user
 * @mixin UserDao
 */
class FuyouServices
{
    // private $fuyouurl="http://spht-test.fuioupay.com/api";
    // private $mchntCd="0002900F0313432";
    // private $openId="oESfm1ZyxsWF-wGEiM-vw1MjR5mE";
    // private $salt="XyUbZQmfejRMQMUQ79SBCBORgWs9A38x";
    // private $shopId="22258";
    
    private  $fuyouurl="https://sp-ht.fuioupay.com/api";
    private $mchntCd="0005410F5985054";
    private $openId="oESfm1ZyxsWF-wGEiM-vw1MjR5mE";
    private $salt="c4ri7MrUjSjSSh6vuYYhHPcnqda4CXSA";
    private $shopId="682086";

    /**
     * LoginServices constructor.
     * @param UserDao $dao
     */
    public function __construct(UserDao $dao)
    {
        $this->dao = $dao;
    }
    
    //发送post请求
    function send_post_curl_header($url, $post_data){
        $header_data=[
            "Cache-Control:no-cache",
            "Content-length:".mb_strlen(json_encode($post_data)),
            "Content-Type: application/json",
            "Accept:*/*",
            // "Host:moreshop.escloud.online",
            "Accept-Encoding:gzip, deflate,br",
            "Connection:keep-alive",
            "User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.90 Safari/537.36"
            ];

        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        curl_setopt($ch, CURLOPT_URL, $url);
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header_data);
        
        curl_setopt($ch, CURLOPT_POST, true);
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $sResult = curl_exec($ch);
        
        if($sError=curl_error($ch)){
        
            die($sError);
        
        }
        
        curl_close($ch);
        // echo $sResult;//die;
        // return $sResult;
        return json_decode($sResult,true);
        
    }
    
    //分页查询会员信息
    public function queryUserInfo($phone){
        $url=$this->fuyouurl."/queryUserInfo.action";
        $sign=md5($phone."|".$this->mchntCd."|". $this->salt);
        $data=array(
            'mchntCd'=>$this->mchntCd,
            'phone'=>$phone,
            'key'=>$sign
            );
        $res=$this->send_post_curl_header($url,$data);
        return $res;
    }
    //充值
    public function recharge($openId,$money,$freeAmt=0){
        $url=$this->fuyouurl."/recharge.action";
        $sign=md5($this->mchntCd."|".$openId."|". $this->shopId."|" .$money."|".$freeAmt."|".$this->salt);
        $data=array(
            'mchntCd'=>$this->mchntCd,
            'openId'=>$openId,
            'shopId'=>$this->shopId,
            'chargeAmt'=>$money,
            'freeAmt'=>$freeAmt,
            'key'=>$sign,
            'amtType'=>01,
            );
        $res=$this->send_post_curl_header($url,$data);
        return $res;
    }
    
    //调账
    public function adjust($phone,$openId,$adjustAmt,$adjustPwd){
        $url=$this->fuyouurl."/adjust.action";
        $sign=md5($this->mchntCd."|".$phone."|".$adjustPwd."|". $adjustAmt."|".$this->salt);
        $data=array(
            'mchntCd'=>$this->mchntCd,
            'phone'=>$phone,
            'openId'=>$openId,
            'adjustPwd'=>$adjustPwd,
            'adjustAmt'=>$adjustAmt,
            'operator'=>'城投金卡',
            'key'=>$sign,
            'adjustType'=>01,
            );
        Log::error('调账报文'.json_encode($data,JSON_UNESCAPED_UNICODE));
        $res=$this->send_post_curl_header($url,$data);
        return $res;
    }
    
    //余额消费
    public function consume($openId,$termId,$consumeAmt){
        $url=$this->fuyouurl."/consume.action";
        $sign=md5($this->mchntCd."|".$openId."|". $termId."|" .$consumeAmt."|".$this->salt);
        $data=array(
            'mchntCd'=>$this->mchntCd,
            'openId'=>$openId,
            'termId'=>$termId,
            'consumeAmt'=>$consumeAmt,
            'key'=>$sign,
            'amtType'=>01,
            );
        $res=$this->send_post_curl_header($url,$data);
        return $res;
    }
    //余额查询
    public function queryBalance($phone){
        $url=$this->fuyouurl."/queryBalance.action";
        $sign=md5($phone."|".$this->mchntCd."|".$this->salt);
        $data=array(
            'mchntCd'=>$this->mchntCd,
            'phone'=>$phone,
            'key'=>$sign
            );
        $res=$this->send_post_curl_header($url,$data);
        return $res;
    }
    //积分查询
    public function queryPoint($phone){
        $url=$this->fuyouurl."/queryPoint.action";
        $sign=md5($phone."|".$this->mchntCd."|".$this->salt);
        $data=array(
            'mchntCd'=>$this->mchntCd,
            'phone'=>$phone,
            'key'=>$sign
            );
        $res=$this->send_post_curl_header($url,$data);
        return $res;
    }
    
    
    //积分修改
    public function editPoint($phone,$oldpoint,$point){
        $url=$this->fuyouurl."/editPoint.action";
        $sign=md5($phone."|".$this->mchntCd."|".$oldpoint."|".$point."|".$this->salt);
        $data=array(
            'mchntCd'=>$this->mchntCd,
            'phone'=>$phone,
            'key'=>$sign,
            'point'=>$point,
            'oldpoint'=>$oldpoint,
            'operator'=>'城投金卡'
            );
        $res=$this->send_post_curl_header($url,$data);
        // echo json_encode($res['resultMsg']);
        Db::name('user_adjust_integral_fuyou')->insert([
                                                           'phone'           => $phone,
                                                           'adjust_integral' => $point,
                                                           'result_code'     => $res['resultCode'],
                                                           'result_msg'      => json_encode($res['resultMsg'], JSON_UNESCAPED_UNICODE),
                                                           'add_time'        => date('Y-m-d H:i:s')]);
        return $res;
    }
    //新增会员
    public function registerUser($phone,$pwd,$offlineCardNo,$userName,$sex,$userBirth){
        $url=$this->fuyouurl."/registerUserApi.action";
        $sign=md5($this->mchntCd."|".$pwd."|".$phone."|".$offlineCardNo."|".$this->salt);
        $data=array(
            'mchntCd'=>$this->mchntCd,
            'phone'=>$phone,
            'pwd'=>$pwd,
            'offlineCardNo'=>$offlineCardNo,
            'userName'=>$userName,
            'sex'=>$sex,
            // 'userBirth'=>$userBirth,
            'userType'=>'02',
            'key'=>$sign
            );
        
        $res=$this->send_post_curl_header($url,$data);
        // var_dump($res);die;
        return $res;
    }
    //会员卡账户余额变动查询
    //交易类型（充值 00，赠送 04，调账消费 02，调账充值 03，余额支付 01）
    public function queryMemOrderList($phone,$beginDate,$endDate,$order_type='01')
    {
        //Log::error('开始');
        $url=$this->fuyouurl."/memOrderList.action";
        //$url="https://sp-ht.fuioupay.com/api/memOrderList.action";
         $sign = md5($this->mchntCd."|".$this->salt);//TODO
        $data=array(
            'key'=>$sign,
            'mchntCd'=>$this->mchntCd,
            'phone'=>$phone,
            'beginDate'=>$beginDate,
            'endDate'=>$endDate,
            'orderType'=>$order_type,//交易类型（充值 00，赠送 04，调账消费 02，调账充值 03，余额支付 01）
        );
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                "Authorization: {{usertoken}}",
                "content-type: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        //$err = curl_error($curl);
        curl_close($curl);
        return json_decode($response,true);
    }
    //修改富友用户信息
    public function editFuyouUserInfo($phone,$fuyou_pwd)
    {
        $url=$this->fuyouurl."/modifyUserInfoApi.action";
        $rs = $this->queryUserInfo($phone);
        
        if (isset($rs['resultCode']) && $rs['resultCode'] === '000000') {
                $open_id = $rs['data']['openId'];
        }else{
            return false;
        }
        //mchntCd +"|" +openId+"|"+ phone+"|"+ salt做MD5加密
        $sign = md5($this->mchntCd."|".$open_id."|".$phone."|".$this->salt);//TODO
        $data=array(
            'key'=>$sign,
            'mchntCd'=>$this->mchntCd,
            'openId'=>$open_id,
            'phone'=>$phone,
            'pwd'=>$fuyou_pwd,
            'pwdSure'=>$fuyou_pwd,
        );
        $res=$this->send_post_curl_header($url,$data);
        Log::error('修改用户：'.json_encode($res,JSON_UNESCAPED_UNICODE));
        return $res;
    }
    //对富友调账通过uid
    function adjustFuyouByUid($uid,$adjust_money=0)
    {
        $fuyouServices = app()->make(FuyouServices::class);
        $sys_user = Db::name("user")->find($uid);
        /*if ($sys_user['uid'] != 21){
            return '不是严';
        }*/
         Log::error('调账开始:uid'.$uid);
        //return $fuyouServices->queryMemOrderList($sys_user['phone'],'2023-01-01',date('Y-m-d'));
        $fuyou_user=$fuyouServices->queryUserInfo($sys_user['phone']);
        if (isset($fuyou_user['resultCode']) && $fuyou_user['resultCode'] === '000000'){
            Log::error('变动金额'.($adjust_money)*100);
            Log::error('调账金额'.($sys_user['now_money']+$adjust_money)*100);
            $r = $fuyouServices->adjust($sys_user['phone'],$fuyou_user['data']['openId'],intval($adjust_money*100),$sys_user['fuyou_pwd']);
            Log::error('调账结果:uid'.$sys_user['uid'].json_encode($r,JSON_UNESCAPED_UNICODE));
            Db::name("user_adjust_fuyou")->insert([
                'uid'=>$sys_user['uid'],
                'trd_balance'=>$adjust_money*100,
                'result_code'=>$r['resultCode'],
                'result'=>json_encode($r,JSON_UNESCAPED_UNICODE),
                'add_time'=>date('Y-m-d H:i:s'),
            ]);
            
             //改富友积分
        $pointdata = $fuyouServices->queryPoint($sys_user['phone']);
        $fuyouServices->editPoint($sys_user['phone'], $pointdata['point'], $sys_user['integral']);
            if (isset($r['resultCode']) && $r['resultCode'] === '000000'){
                return true;
            }

        }
        return false;
    }
    
    // 富友方，消费后，更新本系统金额
    // 富友方，消费后，更新本系统金额
    function handleFuyouChange($sys_user)
    {
        $fuyouServices = app()->make(FuyouServices::class);
         $userBillServices = app()->make(UserBillServices::class);
        //$sys_user = Db::name("user")->find($sys_user['uid']);
        /*if ($sys_user['uid'] != 21){
            return '不是严';
        }*/
        // return $fuyouServices->queryMemOrderList($sys_user['phone'],'2023-01-01',date('Y-m-d'));
        $fuyou_user=$fuyouServices->queryUserInfo($sys_user['phone']);
        if (isset($fuyou_user['resultCode']) && $fuyou_user['resultCode'] === '000000'){

            $change_log = Db::name('user_fuyou_change_log')->where('uid',$sys_user['uid'])->where('pay_time','<',date("Y-m-d H:i:s", strtotime("-1 hour")))->order('pay_time desc')->select()->toArray();

            if (empty($change_log)){
                $begin_date = '2023-01-01';
            }else{

                $begin_date = date('Y-m-d',strtotime($change_log[0]['pay_time']));
            }
            $sys_mchnt_order_nos = array_column($change_log,'mchnt_order_no','mchnt_order_no');//系统已经调整的来自富友的订单
            $rs= $fuyouServices->queryMemOrderList($sys_user['phone'],$begin_date,date('Y-m-d H:i:s'));
           
           // return $rs;
            if (isset($rs['data']) && !empty($rs['data'])){
                $adjust_money = 0;
                foreach ($rs['data'] as $i=>$d){

                    if (in_array($d['mchntOrderNo'],$sys_mchnt_order_nos)){
                        continue;
                    }
                    //如果不是富友消费
                    if ($d['orderType'] != '01'){
                        continue;
                    }
                    if (Db::name('user_fuyou_change_log')->where('mchnt_order_no',$d['mchntOrderNo'])->find()){
                        continue;
                    }
                    Db::name('user_fuyou_change_log')->insert([
                        'uid'=>$sys_user['uid'],
                        'mchnt_order_no'=>$d['mchntOrderNo'],
                        'order_one_amt'=>$d['orderOneAmt'],
                        'order_type'=>$d['orderType'],
                        'pay_time'=>$d['payTime'],
                        'add_time'=>date('Y-m-d H:i:s'),
                    ]);
                    
                    $adjust_money += $d['orderOneAmt'];
                    Db::name("user_money")->insert(array(
                        'uid'=>$sys_user['uid'],'type'=>'pay_product',
                        'title'=>'城投金卡刷卡','mark'=>'城投金卡刷卡',
                        'number'=>$d['orderOneAmt']/100,
                        'balance'=>$sys_user['now_money'] - $adjust_money/100,
                        'pm'=>0,
                        'status'=>1,
                        'add_time'=>strtotime($d['payTime'])));
                      
                        $userBillServices->incomeIntegral($sys_user['uid'], 'gain', [
                            'link_id'=>0,
                            'title'=>'城投金卡刷卡赠送积分',
                             'mark'=>'城投金卡刷卡赠送'.($adjust_money/100).'积分',
                             'number'=>$d['orderOneAmt']/100,
                            'balance'=>$sys_user['integral']+$adjust_money/100
                        ]);
                    
                }
                
                 Log::error('富友支付积分增长：' . ($sys_user['integral'] + $adjust_money / 100));
                Log::error('系统积分：' . ($sys_user['integral'] + $adjust_money / 100));

                $pointdata = $fuyouServices->queryPoint($sys_user['phone']);
                //修改富友积分
                $fuyouServices->editPoint($sys_user['phone'], $pointdata['point'], floor($sys_user['integral'] + $adjust_money / 100));

                Db::name("user")->where('uid', $sys_user['uid'])->update(['integral' => floor($sys_user['integral'] + $adjust_money / 100), 'now_money' => ($sys_user['now_money'] - $adjust_money / 100)]);
                return true;
            }

        }
        return false;



    }
}
