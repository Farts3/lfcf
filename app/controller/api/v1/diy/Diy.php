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
namespace app\controller\api\v1\diy;


use app\Request;
use app\services\activity\coupon\StoreCouponUserServices;
use app\services\activity\newcomer\StoreNewcomerServices;
use app\services\activity\video\VideoServices;
use app\services\diy\DiyServices;
use app\services\user\level\SystemUserLevelServices;
use app\services\user\UserServices;
use app\services\user\FuyouServices;
use think\facade\Db;

/**
 * Class Diy
 * @package app\controller\api\v1\diy
 */
class Diy
{
    protected $services;

    public function __construct(DiyServices $services)
    {
        $this->services = $services;
    }

	/**
 	* 获取diy用户数据
	* @param Request $request
	* @param UserServices $userServices
	* @return mixed
	 */
	public function userInfo(Request $request, UserServices $userServices)
	{
		$uid = (int)$request->uid();
		$userInfo = [];
		if ($uid) {
			$userInfo = $userServices->getUserInfo($uid, 'uid,nickname,phone,avatar,level,integral,now_money,exp,is_money_level,bar_code');
			$oldpoint=0;
			$point=Db::name("point_log")->where("uid",$uid)->order("id desc")->find();
			if(!empty($point)){
			    $oldpoint=$point['point'];
			}
			if ($userInfo) {
			    $fuyoucode="";
			    $fuyouresult=array();
			    if(isset($userInfo['phone'])&&!empty($userInfo['phone'])){
                    $FuyouServices = app()->make(FuyouServices::class);
                    $user=$FuyouServices->queryUserInfo($userInfo['phone']);
                    // $fuyouresult=$FuyouServices->registerUser($userInfo['phone'],"123456",time(),$userInfo['nickname'],01,"123456");
                    if($user['resultCode']=="000000"){
                        /*$pointdata=$FuyouServices->queryPoint($userInfo['phone']);
                        Db::name("point_log")->insert(array('uid'=>$uid,'point'=>$pointdata['point']));
                        // $userInfo['integral']=bcadd($userInfo['integral'],bcsub($pointdata['point'],$oldpoint));
                        
                        if($pointdata['point']>$userInfo['integral']){
                            $userInfo['integral']=$pointdata['point'];
                        }elseif ($pointdata['point']<$userInfo['integral']) {
                            $editres=$FuyouServices->editPoint($userInfo['phone'],$pointdata['point'],$userInfo['integral']);
                        }*/
                        // $editres=$FuyouServices->editPoint($userInfo['phone'],$pointdata['point'],$userInfo['integral']);
                        // $fuyouresult=$editres;
                        // echo json_encode($editres);die;
                        $updatedata=json_encode($userInfo);
                        $updatedata=json_decode($updatedata,true);
                        
                        Db::name("user")->where("uid",$userInfo['uid'])->update($updatedata);
                        $fuyoucode=$user['data']['code'];
                        $queryBalance=$FuyouServices->queryBalance($userInfo['phone']);
                        if($queryBalance['resultCode']=="000000"){
                            // echo json_encode($queryBalance)
                            // Db::name("user_money")->insert(array('uid'=>$uid,'type'=>'pay_product','title'=>'城投金卡刷卡','mark'=>'城投金卡刷卡','number'=>bcsub($queryBalance['balance']/100,$userInfo['now_money']),'balance'=>$userInfo['now_money'],'pm'=>0,'status'=>1,'add_time'=>time()));
                            // $userInfo['fuyoucode']=$user['data']['code'];
                           // if ($userInfo['uid'] == 21){
                                $FuyouServices->handleFuyouChange($userInfo);
                            /*}else{
                                if(($queryBalance['balance']/100)>$userInfo['now_money']){
                                    $FuyouServices->adjust($userInfo['phone'],$user['data']['openId'],$userInfo['now_money']*100,'');
                                    
                                }elseif(($queryBalance['balance']/100)<$userInfo['now_money']){
                                    // adjust($userInfo['phone'],$user['data']['openId'],$userInfo['now_money']*100,'123456');
                                    
                                    $userInfo['now_money']=$queryBalance['balance']/100;
                                    $updatedata=json_encode($userInfo);
                                    $updatedata=json_decode($updatedata,true);
                                    Db::name("user")->where("uid",$userInfo['uid'])->update($updatedata);
                                    Db::name("user_money")->insert(array('uid'=>$uid,'type'=>'pay_product','title'=>'城投金卡刷卡','mark'=>'城投金卡刷卡','number'=>bcsub($userInfo['now_money'],$queryBalance['balance']/100),'balance'=>$userInfo['now_money'],'pm'=>0,'status'=>1,'add_time'=>time()));
                                }
                                
                            }*/
                        }
                        
                        // $userdata=Db::name("user")->select();
                        // foreach ($userdata as $info){
                        //     $user1=$FuyouServices->queryUserInfo($info['phone']);
                        //     if($user1['resultCode']!="000000"){
                        //         $res=$FuyouServices->registerUser($info['phone'],"",time(),$info['nickname'],01,"123456");
                        //         if($res['resultCode']=="000000"){
                        //             $user2=$FuyouServices->queryUserInfo($info['phone']);
                        //             $FuyouServices->adjust($info['phone'],$user2['data']['openId'],$info['now_money']*100,'');
                        //         }
                        //     }
                        // }
                        // echo json_encode($queryBalance);die;
                    }else{
                        $res=$FuyouServices->registerUser($userInfo['phone'],"",time(),$userInfo['nickname'],01,"123456");
                        if($res['resultCode']=="000000"){
                            $user=$FuyouServices->queryUserInfo($userInfo['phone']);
                            $FuyouServices->adjust($userInfo['phone'],$user['data']['openId'],$userInfo['now_money']*100,'');
                        }
                    }
                    
                }
				$userInfo = $userInfo->toArray();
				$userInfo['fuyoucode']=$fuyoucode;
				$userInfo['bar_code']=$fuyoucode;
				$userInfo['fuyouresult']=$fuyouresult;
				/** @var StoreCouponUserServices $storeCoupon */
        		$storeCoupon = app()->make(StoreCouponUserServices::class);
				$userInfo['coupon_num'] = $storeCoupon->getUserValidCouponCount((int)$uid);
				$userInfo['next_exp'] = 0;
				$userInfo['vip_name'] = '';
				if ($userInfo['level']) {
					/** @var SystemUserLevelServices $systemUserLevel */
					$systemUserLevel = app()->make(SystemUserLevelServices::class);
					$levelList = $systemUserLevel->getList(['is_del' => 0, 'is_show' => 1], 'id,name,exp_num');
					$i = 0;
					foreach ($levelList as &$level) {
						if ($level['id'] == $userInfo['level']) {
							$userInfo['vip_name'] = $level['name'];
						}
						$level['next_exp_num'] = $levelList[$i + 1]['exp_num'] ?? $level['exp_num'];
						$i++;
					}
					$levelList = array_combine(array_column($levelList,'id'), $levelList);
					$userInfo['next_exp'] = $levelList[$userInfo['level']]['next_exp_num'] ?? 0;
				} else {
					/** @var SystemUserLevelServices $systemUserLevel */
					$systemUserLevel = app()->make(SystemUserLevelServices::class);
					$levelList = $systemUserLevel->getList(['is_del' => 0, 'is_show' => 1], 'id,name,exp_num');
					$userInfo['next_exp'] = $levelList[0]['exp_num'] ?? 0;
				}
			}
			
// 			$uid = (int)$order['uid'];
            // $userInfo =$userServices->getUserInfo($uid);
            // // echo json_encode($userInfo);die;
            // $FuyouServices = app()->make(FuyouServices::class);
            // $user=$FuyouServices->queryUserInfo($userInfo['phone']);
            // // echo json_encode($user);die;
            // if($user['resultCode']=="000000"){
            //     $fuyoures=$FuyouServices->recharge($user['data']['openId'],0.01,0);
            // }
		}
		return app('json')->success($userInfo);
	}

	/**
 	* 获取diy短视频
	* @param Request $request
	* @param VideoServices $videoServices
	* @return mixed
	 */
	public function videoList(Request $request, VideoServices $videoServices)
	{
		$uid = (int)$request->uid();
		return app('json')->success($videoServices->getDiyVideoList($uid));
	}

	/**
 	* 获取新人礼商品
	* @param Request $request
	* @param StoreNewcomerServices $newcomerServices
	* @return mixed
	 */
	public function newcomerList(Request $request, StoreNewcomerServices $newcomerServices)
	{
		$where = $request->getMore([
            ['priceOrder', ''],
            ['salesOrder', ''],
        ]);
		$uid = (int)$request->uid();
		return app('json')->success($newcomerServices->getDiyNewcomerList($uid, $where));
	}


}
