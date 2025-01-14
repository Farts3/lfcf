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

namespace crmeb\services\express\storage;

use app\services\other\ExpressServices;
use crmeb\basic\BaseExpress;
use crmeb\exceptions\ApiException;
use crmeb\services\AccessTokenServeService;

/**
 * Class Express
 * @package crmeb\services\express\storage
 */
class Express extends BaseExpress
{
    /**
     * 注册服务
     */
    const EXPRESS_OPEN = 'expr/open';

    /**
     * 电子面单模版
     */
    const EXPRESS_TEMP = 'expr/temp';

    /**
     * 快递公司
     */
    const EXPRESS_LIST = 'expr/express';

    /**
     * 快递查询
     */
    const EXPRESS_QUERY = 'expr/query';

    /**
     * 面单打印
     */
    const EXPRESS_DUMP = 'expr/dump';

    /** 初始化
     * @param array $config
     * @return mixed|void
     */

    protected function initialize(array $config = [])
    {
        parent::initialize($config);
    }

    /**
     * 开通物流服务
     * @return bool|mixed
     */
    public function open()
    {
        return $this->accessToken->httpRequest(self::EXPRESS_OPEN, []);
    }

    /**
     * 获取电子面单模版
     * @param $com 快递公司编号
     * @param int $page
     * @param int $limit
     * @return bool|mixed
     */
    public function temp(string $com)
    {
        $param = [
            'com' => $com
        ];
		$header = [];
		if (!sys_config('config_export_siid')) {
			$header = ['version:v1.1'];
		}
        return $this->accessToken->httpRequest(self::EXPRESS_TEMP, $param, 'POST', true, $header);
    }

    /**
     * 获取物流公司列表
     * @param int $type 快递类型：1，国内运输商；2，国际运输商；3，国际邮政
     * @return bool|mixed
     */
    public function express(int $type = 0, int $page = 0, int $limit = 20)
    {
        if ($type) {
            $param = [
                'type' => $type,
                'page' => $page,
                'limit' => $limit
            ];
        } else {
            $param = [];
        }

        return $this->accessToken->httpRequest(self::EXPRESS_LIST, $param);
    }

    /**
     * 查询物流信息
     * @param $com
     * @param $num
     * @return bool|mixed
     * @return 是否签收 ischeck
     * @return 物流状态：status 0在途，1揽收，2疑难，3签收，4退签，5派件，6退回，7转单，10待清关，11清关中，12已清关，13清关异常，14收件人拒签
     * @return 物流详情 content
     */
    public function query(string $num, string $com = '')
    {
        $param = [
            'com' => $com,
            'num' => $num
        ];
        if ($com === null) {
            unset($param['com']);
        }
        return $this->accessToken->httpRequest(self::EXPRESS_QUERY, $param);
    }

    /**
     * 电子面单打印
     * @param array $data 必需参数: com(快递公司编码)、to_name(寄件人)、to_tel（寄件人电话）、to_addr（寄件人详细地址）、from_name（收件人）、from_tel（收件人电话)、from_addr（收件人地址）、temp_id（电子面单模板ID）、siid（云打印机编号）、count（商品数量）
     * @return bool|mixed
     */
    public function dump($data)
    {
        $param = $data;
        $param['com'] = $data['com'] ?? '';
        if (!$param['com']) throw new ApiException('快递公司编码缺失');
        $param['to_name'] = $data['to_name'] ?? '';
        $param['to_tel'] = $data['to_tel'] ?? '';
        $param['order_id'] = $data['order_id'] ?? '';
        $param['to_addr'] = $data['to_addr'] ?? '';
        if (!$param['to_addr'] || !$param['to_tel'] || !$param['to_name']) throw new ApiException('寄件人信息缺失');
        $param['from_name'] = $data['from_name'] ?? '';
        $param['from_tel'] = $data['from_tel'] ?? '';
        $param['from_addr'] = $data['from_addr'] ?? '';
        if (!$param['from_name'] || !$param['from_tel'] || !$param['from_addr']) throw new ApiException('收件人信息缺失');
        $param['temp_id'] = $data['temp_id'] ?? '';
        if (!$param['temp_id']) {
            throw new ApiException('电子面单模板ID缺失');
        }
        $param['siid'] = sys_config('config_export_siid');
//        if (!$param['siid']) {
//            throw new ApiException('云打印机编号缺失');
//        }
        $param['count'] = $data['count'] ?? '';
        $param['cargo'] = $data['cargo'] ?? '';

        if (!$param['count']) {
            throw new ApiException('商品数量缺失');
        }

        /** @var ExpressServices $expressServices */
        $expressServices = app()->make(ExpressServices::class);
        $expressData = $expressServices->getOneByWhere(['code' => $param['com']])->toArray();
        if (isset($data['cargo'])) $param['cargo'] = $data['cargo'];
        if ($expressData['partner_id'] == 1) $param['partner_id'] = $expressData['account'];
        if ($expressData['partner_key'] == 1) $param['partner_key'] = $expressData['key'];
        if ($expressData['net'] == 1) $param['net'] = $expressData['net_name'];
        if ($expressData['check_man'] == 1) $param['checkMan'] = $expressData['courier_name'];
        if ($expressData['partner_name'] == 1) $param['partnerName'] = $expressData['customer_name'];
        if ($expressData['is_code'] == 1) $param['code'] = $expressData['code_name'];
		//修改没有打印机的时候print_type=IMAGE，就会返回面单图片
		if (!$data['siid']) {
			$param['print_type'] = 'IMAGE';
		}
        return $this->accessToken->httpRequest(self::EXPRESS_DUMP, $param, 'POST', true, ['version:v1.1']);
    }

}
