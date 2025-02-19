// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2021 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
import Setting from '@/setting';

const pre = 'kefu_';

export default [
    // 登录
    {
        path: `${Setting.roterPre}/login`,
        name: 'login',
        meta: {
            title: '$t:page.login.title'
        },
        component: () => import('@/pages/account/login')
    },
    {
        path: `${Setting.roterPre}/order/distribution`,
        name: `distribution`,
        meta: {
            auth: true,
            title: '配货单'
        },
        component: () => import('@/pages/order/distribution')
    }
]
