// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2021 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

import request from "@/utils/request.js";
/**
 * 二次开发
 */
// 创建订单并支付【余额】
export function createOrder(data) {
	return request.post("order/offlinetocreate", data, {

	});
}

// 获取支付记录
export function getOfflineLogs(data) {
	return request.get("order/getOfflineLogs");
}

// 获取门店二级分类
export function getTwoCategory(data) {
	return request.get("store_two_categroy", data, {
		noAuth: true
	});
}