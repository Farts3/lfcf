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
namespace app\controller\api\v1\store;

use app\services\product\category\StoreProductCategoryServices;
use think\Request;
use think\facade\Db;
/**
 * Class CategoryController
 * @package app\api\controller\v1\store
 */
class CategoryController
{
    protected $services;

    public function __construct(StoreProductCategoryServices $services)
    {
        $this->services = $services;
    }

    /**
     * 获取分类列表
     * @return mixed
     */
    public function category(Request $request)
    {
        $where = $request->getMore([
            ['pid', 0],
            ['store_id', 0]
        ]);
        
        
        $store_id = $where['store_id'];
        unset($where['store_id']);
        
        // 根据店查询商品 [二级分类ID]
        $goodsList = Db::name('store_product') -> field('id,cate_id')-> where(['relation_id' => $store_id, 'is_del' => 0,'is_show' => 1]) -> group('cate_id') -> select() -> toArray();
        $cateList = Db::name('store_product_category') -> where(['is_show' => 1]) ->where('pid','>','0')  -> select() -> toArray();
        
        // var_dump($goodsList);
        
        $category = $this->services->getCategory($where);
        
        foreach ($cateList as $key => &$value) {
            foreach ($goodsList as $k => $v){
                if(in_array($value['id'], explode(',',$v['cate_id']))) {
                    
                    $value['isMpShow'] = true;
                }
            }
        }
        
        foreach ($category as $kk => &$vv) {
            foreach ($cateList as $kkk => $vvv) {
                if($vv['pid'] == 0 && $vvv['pid'] == $vv['id']) {
                    if(isset($vvv['isMpShow']) && $vvv['isMpShow']) {
                        $vv['isCategoryShow'] = true;
                    }
                }
            }
        }
        
        $list = [];
        foreach ($category as $uk => $uv) {
            if(isset($uv['isCategoryShow']) && $uv['isCategoryShow']) {
                array_push($list, $uv);
            }
        }
        // var_dump($category);
        // echo json_encode($category);
        // $redata=array();
        // foreach ($category as $c){
        //     if($c['id']==68){
        //         $redata[]=$c;
        //     }
        // }
        return app('json')->success($list);
    }
}
