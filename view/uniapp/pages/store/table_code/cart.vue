<template>
  <!-- 商品分类第三种布局样式 -->
  <view class="goodCate">
    <!-- #ifdef MP || APP-PLUS -->
    <view class="mp-header">
      <view class="sys-head" :style="{ height: sysHeight }"></view>
      <view class="serch-box" style="height: 43px;">
        <view class="serch-wrapper acea-row row-middle">
          <navigator url="/pages/goods/goods_search/index" class="input acea-row row-center-wrapper" hover-class="none">
            <text class="iconfont icon-xiazai5"></text>
            搜索商品信息
          </navigator>
        </view>
      </view>
      <!-- 门店地址详情 -->
      <!-- <view class="store-address">
        <view class="address acea-row row-middle" @click="goMap()">
					<view class="name line1">{{info.name || '暂无门店'}}</view>
          <text class="iconfont icon-xiangyou" />
        </view>
        <view class="distance">
          门店距您{{info.range||0}}km
        </view>
        <view class="time">
          营业时间：{{info.day_time || '-'}}
        </view>
				<view class="switch acea-row row-between-wrapper">
					<view class="title" :class="{'on': switchNum == item.key,'onLeft':switchNum != 1}"
					  v-for="(item, index) in deliveryList" :key="item.key" @click="swithFn(item.key)">{{item.name}}</view>
				</view>
      </view> -->
      <!-- 门店详情 -->
    </view>
    <!-- #endif -->
    <!-- #ifdef H5 -->
    <view class="header acea-row row-center-wrapper">
      <navigator url="/pages/goods/goods_search/index" class="search acea-row row-middle" hover-class="none">
        <text class="iconfont icon-sousuo5"></text>
        搜索商品信息
      </navigator>

      <view class="store-address">
        <view class="address acea-row row-middle" @click="goMap()">
					<view class="name line1">{{info.name || '暂无门店'}}</view>
          <text class="iconfont icon-xiangyou" />
        </view>
        <view class="distance">
          门店距您{{info.range||0}}km
        </view>
        <view class="time">
          营业时间：{{info.day_time || '-'}}
        </view>
				<view class="switch acea-row row-between-wrapper">
					<view class="title" :class="{'on': switchNum == item.key,'onLeft':switchNum != 1}"
					  v-for="(item, index) in deliveryList" :key="item.key" @click="swithFn(item.key)">{{item.name}}</view>
				</view>
      </view>
    </view>
    <view class="conter">
      <!-- #endif -->
      <!-- #ifndef H5 -->
      <view class="conter" :style="'padding-top:'+marTop+'px'">
      <!-- #endif -->
        <!-- #ifndef H5 -->
        <view class='aside' :style="'top:'+marTop+'px'">
          <!-- #endif -->
          <!-- #ifdef H5 -->
          <view class='aside'>
            <!-- #endif -->
            <view class='item acea-row row-center-wrapper' :class='index==navActive?"on":""'
              v-for="(item,index) in categoryList" :key="index" @click="tapNav(index,item)">
              <text>{{item.cate_name}}</text>
            </view>
          </view>
          <view class="wrapper">
            <view class="bgcolor" v-if="iSlong">
              <!-- #ifndef H5 -->
              <view class="longTab acea-row row-middle" :style="'top:'+marTop+'px'">
                <!-- #endif -->
                <!-- #ifdef H5 -->
                <view class="longTab acea-row row-middle">
                  <!-- #endif -->
                  <scroll-view scroll-x="true" style="white-space: nowrap; display: flex;height:44rpx;"
                    scroll-with-animation :scroll-left="tabLeft" show-scrollbar="true">
                    <view class="longItem" :style='"width:"+isWidth+"px"' :class="index===tabClick?'click':''"
                      v-for="(item,index) in categoryErList" :key="index" @click="longClick(index)">
                      {{item.cate_name}}
                    </view>
                  </scroll-view>
                </view>
                <!-- #ifndef H5 -->
                <view class="openList" :style="'top:'+marTop+'px'" @click="openTap"><text
                    class="iconfont icon-xiangxia"></text></view>
                <!-- #endif -->
                <!-- #ifdef H5 -->
                <view class="openList" @click="openTap"><text class="iconfont icon-xiangxia"></text></view>
                <!-- #endif -->
              </view>
              <view v-else>
                <!-- #ifndef H5 -->
                <view class="downTab" :style="'margin-top:'+marTop+'px'">
                  <!-- #endif -->
                  <!-- #ifdef H5 -->
                  <view class="downTab">
                    <!-- #endif -->
                    <view class="title acea-row row-between-wrapper">
                      <view>{{categoryTitle}}</view>
                      <view class="closeList" @click="closeTap"><text class="iconfont icon-xiangxia"></text>
                      </view>
                    </view>
                    <view class="children">
                      <view class="acea-row row-middle">
                        <view class="item line1" :class="index===tabClick?'click':''"
                          v-for="(item,index) in categoryErList" :key="index" @click="longClick(index)">
                          {{item.cate_name}}
                        </view>
                      </view>
                    </view>
                  </view>
                  <view class="mask" @click="closeTap"></view>
                </view>
                <!--商品列表 -->
                  <goodClass :tempArr="tempArr" :isLogin="isLogin" @gocartduo="goCartDuo" @gocartdan="goCartDan"
                    @ChangeCartNumDan="ChangeCartNumDan" @detail="goDetail"></goodClass>
                  <view class='loadingicon acea-row row-center-wrapper'>
                    <text class='loading iconfont icon-jiazai' :hidden='loading==false'></text>{{loadTitle}}
                  </view>
              </view>
            </view>
            <view class="footer acea-row row-between-wrapper" :class="isFooter?'':'on'">
              <view class="cartIcon acea-row row-center-wrapper" @click="getCartList(0)" v-if="cartNum>0">
                <!-- <view class="iconfont icon-gouwuche-yangshi2"></view>
                <text class="num">{{cartNum}}</text> -->
				<uni-badge class="uni-badge-left-margin" :text="cartNum" absolute="rightTop">
				  <view class="iconfont icon-gouwuche-yangshi2"></view>
				</uni-badge>
              </view>
              <view class="cartIcon acea-row row-center-wrapper noCart" v-else>
                <view class="iconfont icon-gouwuche-yangshi2"></view>
              </view>
              <view class="acea-row row-middle" :class="cartNum>0?'':'noCart'">
                <view class="money">￥<text class="num">{{totalPrice}}</text></view>
                <view class="bnt" @click="subOrder">提交订单</view>
              </view>
            </view>
            <!-- 分类购物车下拉列表 -->
            <cartList :cartData="cartData" :isFooter='isFooter' @closeList="closeList" @ChangeCartNumDan="ChangeCartList"
              @ChangeSubDel="ChangeSubDel" @ChangeOneDel="ChangeOneDel"></cartList>
            <!-- 产品属性组件 -->
            <productWindow :attr="attr" :isShow='1' :iSplus='1' :iScart='1' @myevent="onMyEvent"
              @ChangeAttr="ChangeAttr" @ChangeCartNum="ChangeCartNumDuo" @attrVal="attrVal" @iptCartNum="iptCartNum"
              @goCat="goCatNum" :is_vip="is_vip" id='product-window'></productWindow>
						<!-- #ifdef MP -->
						<authorize v-if="isShowAuth" @authColse="authColse" @onLoadFun="onLoadFun"></authorize>
						<!-- #endif -->
          </view>
</template>

<script>
  let sysHeight = uni.getSystemInfoSync().statusBarHeight + 'px';

  import {
    vcartList,
    // getCartCounts,
    cartDel
  } from '@/api/order.js';
  import {
  	getCategoryList,
  	getAttr,
  	postCartNum,
	getCartCount,
	getCateList,
	addTableCate,
	placeOrder,
	emptyTableData,
  } from '@/api/store.js';
  import {
    getProducts,
    getCustomerList,
    getStoreCategory,
    getStoreBrand
  } from '@/api/new_store.js';
  import productWindow from '@/components/productWindow/index';
  import goodClass from '@/components/goodClass';
  import cartList from '@/components/cartList';
  import {
    mapState,
    mapGetters
  } from 'vuex';
  import {
    goShopDetail
  } from '@/libs/order.js';
  import {
    toLogin
  } from '@/libs/login.js';
  export default {
    computed: {
      ...mapGetters(['isLogin', 'uid'])
    },
    components: {
      productWindow,
      goodClass,
      cartList
    },
    props: {
      isFooter: {
        type: Boolean,
        default: false
      },
      info: {
        type: Object,
        default: {}
      },
	  pageVisible: {
		  type: Boolean,
		  default: true
	  }
    },
    data() {
      return {
				deliveryList:[
					{name:'配送',key:1},
					{name:'自提',key:2}
				],
        marTop: 0,
        switchNum: 1,
        sysHeight: sysHeight,
        categoryList: [],
        navActive: 0,
        categoryTitle: '',
        categoryErList: [],
        tabLeft: 0,
        isWidth: 0, //每个导航栏占位
        tabClick: 0, //导航栏被点击
        iSlong: true,
        tempArr: [],
        loading: false,
        loadend: false,
        loadTitle: '加载更多',
        page: 1,
        limit: 10,
        cid: 0, //一级分类
        sid: 0, //二级分类
        delivery_type:3, // 配送方式
        isAuto: false, //没有授权的不会自动授权
        isShowAuth: false, //是否隐藏授权
        attr: {
          cartAttr: false,
          productAttr: [],
          productSelect: {}
        },
        productValue: [],
        attrValue: '', //已选属性
        storeName: '', //多属性产品名称
        id: 0,
        cartData: {
          cartList: [],
          iScart: false
        },
        totalPrice: 0.00,
        lengthCart: 0,
        is_vip: 0, //是否是会员
        cart_num: 0,
        storeInfo: {},
		cartNum: 0
      }
    },
	watch: {
		pageVisible(value) {
			if (value) {
				return;
			}
			if (!this.timer || this.timer.constructor.name != 'Object') {
				return;
			}
			Object.values(this.timer).forEach(this.clearTimer);
		},
		info: {
			handler(value) {
				if (!value.store_id || !value.tableId) {
					return;
				}
				this.getCartNum();
				this.getCartList(1);
			},
			deep: true
		}
	},
    mounted() {
      let that = this;
      // 获取设备宽度
      uni.getSystemInfo({
        success(e) {
          that.isWidth = e.windowWidth / 5
        }
      });
    },
    methods: {
		// 取消定时器
		clearTimer(timerID) {
			if (timerID) {
				clearTimeout(timerID);
			}
		},
		// 设置定时器
		setTimer(timerID, callback) {
			if (typeof callback != 'function') {
				return;
			}
			if (!this.timer || this.timer.constructor.name != 'Object') {
				this.timer = {};
			}
			this.clearTimer(this.timer[timerID]);
			this.timer[timerID] = setTimeout(callback, 5000);
		},
		// 重载商品数据
		reloadProducts() {
			getProducts({
				page: 1,
				limit: this.tempArr.length,
				type: 1,
				cid: this.cid,
				sid: this.sid,
				store_id: this.info.id,
				delivery_type: '',
				collate_code_id: this.info.tableId,
			}).then(res => {
				this.$set(this, 'tempArr', res.data);
				this.setTimer('reloadProducts', this.reloadProducts);
			});
		},
			// 授权回调
			onLoadFun() {
				setTimeout(function(){
					this.isShowAuth = false;
				},10)
			},
			// 授权关闭
			authColse: function(e) {
			  this.isShowAuth = e
			},
      updateFun(e) {
        if (e.cartNum) {
          this.tempArr.forEach((item) => {
            if (item.id == e.id) {
              item.cart_num = e.cartNum
            }
          })
        }
      },
      // 商品列表
      getProducts() {
        let that = this;
				if (that.loadend) return;
				if (that.loading) return;
				that.loading = true;
        getProducts({
            page: that.page,
            limit: that.limit,
            type: 1,
            cid: that.cid,
            sid: that.sid,
            store_id: that.info.id,
            delivery_type:'',
            collate_code_id:that.info.tableId,
          }).then(res => {
          console.log(res,'res')
          let list = res.data,
            loadend = list.length < that.limit;
          that.tempArr = that.$util.SplitArray(list, that.tempArr);
          that.$set(that, 'tempArr', that.tempArr);
          that.loading = false;
          that.loadend = loadend;
          that.loadTitle = loadend ? "没有更多内容啦~" : "加载更多";
          that.page = that.page + 1;
		  this.setTimer('reloadProducts', this.reloadProducts);
        }).catch(err => {
          that.loading = false;
          that.loadTitle = '加载更多'
        });
      },
      // 切换自提方式
      swithFn(num) {
        switch (num) {
          case 1:
            this.switchNum = 1
            this.delivery_type = '3'
            break;
          case 2:
            this.switchNum = 2
            this.delivery_type='2'
            break;
        }
				this.page = 1;
				this.loadend = false;
				this.tempArr = []
        this.getProducts()
      },
      // 跳转到门店列表
      goMap() {
        uni.navigateTo({
			url: "/pages/store/map/index?storeFrom=1&type=1&storeId="+this.info.id,
          success(res) {
            console.log('成功啦', res);
          },
          fail(err) {
            console.log('失败啦', err);
          }
        })
      },
      getMarTop() {
        // #ifdef MP || APP-PLUS
        let that = this;
        setTimeout(() => {
          // 获取小程序头部高度
          let info = uni.createSelectorQuery().in(this).select(".mp-header");
          info.boundingClientRect(function(data) {
            that.marTop = data.height
          }).exec()
        }, 100)
        // #endif
      },
      // 生成订单；
      subOrder: function() {
		  placeOrder({
			  tableId: this.info.tableId,
			  storeId: this.info.store_id,
		  }).then(() => {
			  uni.navigateTo({
			  	url: `/pages/store/table_confirm/index?tableId=${this.info.tableId}`
			  });
		  }).catch(err => {
			  this.$util.Tips({
			    title: err
			  });
		  });
      },
      // 计算总价；
      getTotalPrice: function() {
        let that = this,
          list = that.cartData.cartList,
          totalPrice = 0.00;
        list.forEach(item => {
          if (item.attrStatus && item.status) {
            totalPrice = that.$util.$h.Add(totalPrice, that.$util.$h.Mul(item.cart_num, item
              .truePrice));
          }
        })
        that.$set(that, 'totalPrice', totalPrice);
      },
      ChangeSubDel: function(event) {
		 uni.showModal({
			title: '确定要清空吗？',
			content: '清空后，已点的商品会被删除',
			success: (res) => {
				if (res.confirm) {
					emptyTableData({
						tableId: this.info.tableId
					}).then(res => {
						this.$util.Tips({
							title: '清空成功'
						});
						this.getCartNum();
						this.getCartList(1);
					});
				}
			}
		 });
      },
      ChangeOneDel: function(id, index) {
        let that = this,
          list = that.cartData.cartList,
					storeId = uni.getStorageSync('user_store_id');
        cartDel(id.toString(),storeId).then(res => {
          list.splice(index, 1);
          if (!list.length) {
            that.cartData.iScart = false;
            that.page = 1;
            that.loadend = false;
            that.tempArr = [];
            that.getProducts();
          };
          that.getCartNum();
		  if (!cart) {
		    that.getCartList(1);
		  }
        })
      },
      getCartList(iSshow) {
        let that = this;
        let { store_id, tableId } = this.info;
		getCateList({ store_id, tableId }).then(res => {
		  that.$set(that.cartData, 'cartList', res.data);
		  if (res.data.length) {
			that.$set(that.cartData, 'iScart', iSshow ? false : !that.cartData.iScart);
		  } else {
			that.$set(that.cartData, 'iScart', false);
		  }
		  that.getTotalPrice();
		});
      },
      closeList(e) {
        this.$set(this.cartData, 'iScart', e);
      },
	  // 获取购物车商品数量
      getCartNum: function() {
		  let { store_id, tableId } = this.info;
		getCartCount({ store_id, tableId }).then(res => {
			this.cartNum = res.data.count;
			this.setTimer('getCartNum', this.getCartNum);
		});
      },
      onMyEvent: function() {
        this.$set(this.attr, 'cartAttr', false);
      },
      /**
       * 默认选中属性
       * 
       */
      DefaultSelect: function() {
        let productAttr = this.attr.productAttr;
        let value = [];
        for (let key in this.productValue) {
          if (this.productValue[key].stock > 0) {
            value = this.attr.productAttr.length ? key.split(",") : [];
            break;
          }
        }
        for (let i = 0; i < productAttr.length; i++) {
          this.$set(productAttr[i], "index", value[i]);
        }
        //sort();排序函数:数字-英文-汉字；
        let productSelect = this.productValue[value.join(",")];
        if (productSelect && productAttr.length) {
          this.$set(
            this.attr.productSelect,
            "store_name",
            this.storeName
          );
          this.$set(this.attr.productSelect, "image", productSelect.image);
          this.$set(this.attr.productSelect, "price", productSelect.price);
          this.$set(this.attr.productSelect, "stock", productSelect.stock);
          this.$set(this.attr.productSelect, "unique", productSelect.unique);
          this.$set(this.attr.productSelect, "cart_num", 1);
          this.$set(this.attr.productSelect, 'vip_price', productSelect.vip_price);
          this.$set(this, "attrValue", value.join(","));
        } else if (!productSelect && productAttr.length) {
          this.$set(
            this.attr.productSelect,
            "store_name",
            this.storeName
          );
          this.$set(this.attr.productSelect, "image", this.storeInfo.image);
          this.$set(this.attr.productSelect, "price", this.storeInfo.price);
          this.$set(this.attr.productSelect, "stock", 0);
          this.$set(this.attr.productSelect, "unique", "");
          this.$set(this.attr.productSelect, "cart_num", 0);
          this.$set(this, "attrValue", "");
          this.$set(this.attr.productSelect, 'vip_price', this.storeInfo.vip_price);
        } else if (!productSelect && !productAttr.length) {
          this.$set(
            this.attr.productSelect,
            "store_name",
            this.storeName
          );
          this.$set(this.attr.productSelect, "image", this.storeInfo.image);
          this.$set(this.attr.productSelect, "price", this.storeInfo.price);
          this.$set(this.attr.productSelect, "stock", this.storeInfo.stock);
          this.$set(
            this.attr.productSelect,
            "unique",
            this.storeInfo.unique || ""
          );
          this.$set(this.attr.productSelect, "cart_num", 1);
          this.$set(this, "attrValue", "");
          this.$set(this.attr.productSelect, 'vip_price', this.storeInfo.vip_price);
        }
      },
      /**
       * 属性变动赋值
       * 
       */
      ChangeAttr: function(res) {
        let productSelect = this.productValue[res];
        if (productSelect && productSelect.stock > 0) {
          this.$set(this.attr.productSelect, "image", productSelect.image);
          this.$set(this.attr.productSelect, "price", productSelect.price);
          this.$set(this.attr.productSelect, "stock", productSelect.stock);
          this.$set(this.attr.productSelect, "unique", productSelect.unique);
          this.$set(this.attr.productSelect, 'vip_price', productSelect.vip_price);
          this.$set(this.attr.productSelect, "cart_num", 1);
          this.$set(this, "attrValue", res);
        } else if (productSelect && productSelect.stock == 0) {
          this.$set(this.attr.productSelect, "image", productSelect.image);
          this.$set(this.attr.productSelect, "price", productSelect.price);
          this.$set(this.attr.productSelect, "stock", 0);
          this.$set(this.attr.productSelect, "unique", "");
          this.$set(this.attr.productSelect, 'vip_price', productSelect.vip_price);
          this.$set(this.attr.productSelect, "cart_num", 0);
          this.$set(this, "attrValue", "");
        } else {
          this.$set(this.attr.productSelect, "image", this.storeInfo.image);
          this.$set(this.attr.productSelect, "price", this.storeInfo.price);
          this.$set(this.attr.productSelect, "stock", 0);
          this.$set(this.attr.productSelect, "unique", "");
          this.$set(this.attr.productSelect, 'vip_price', this.storeInfo.vip_price);
          this.$set(this.attr.productSelect, "cart_num", 0);
          this.$set(this, "attrValue", "");
        }
      },
      attrVal(val) {
        this.$set(this.attr.productAttr[val.indexw], 'index', this.attr.productAttr[val.indexw].attr_values[val
          .indexn]);
      },
      /**
       * 购物车手动填写
       * 
       */
      iptCartNum: function(e) {
        this.$set(this.attr.productSelect, 'cart_num', e);
      },
      // 点击默认单属性购物车
      goCartDan(item, index) {
        if (!this.isLogin) {
          this.getIsLogin();
        } else {
          this.tempArr[index].cart_num = 1;
          this.$set(this, 'tempArr', this.tempArr);
          this.goCat(0, item.id, 1);
        }
      },
      // 改变单属性购物车
      ChangeCartNumDan(changeValue, index, item) {
        let num = this.tempArr[index];
        let stock = this.tempArr[index].stock;
        this.ChangeCartNum(changeValue, num, stock, 0, item.id);
      },
      // 改变多属性购物车
      ChangeCartNumDuo(changeValue) {
        //获取当前变动属性
        let productSelect = this.productValue[this.attrValue];
        //如果没有属性,赋值给商品默认库存
        if (productSelect === undefined && !this.attr.productAttr.length)
          productSelect = this.attr.productSelect;
        //无属性值即库存为0；不存在加减；
        if (productSelect === undefined) return;
        let stock = productSelect.stock || 0;
        let num = this.attr.productSelect;
        this.ChangeCartNum(changeValue, num, stock, 1, this.id);
      },
      // 已经加入购物车时的购物加减；
      ChangeCartList(changeValue, index) {
        let list = this.cartData.cartList;
        let num = list[index];
        let stock = list[index].trueStock;
        this.ChangeCartNum(changeValue, num, stock, 0, num.product_id, index, 1);
        if (!list.length) {
          this.cartData.iScart = false;
          this.page = 1;
          this.loadend = false;
          this.tempArr = [];
          this.getProducts();
        }
      },
      // 购物车加减计算函数
      ChangeCartNum(changeValue, num, stock, isDuo, id, index, cart) {
        if (changeValue) {
          num.cart_num++;
          if (num.cart_num > stock) {
            if (isDuo) {
              this.$set(this.attr.productSelect, "cart_num", stock ? stock : 1);
              this.$set(this, "cart_num", stock ? stock : 1);
            } else {
              num.cart_num = stock ? stock : 0;
              this.$set(this, 'tempArr', this.tempArr);
              this.$set(this.cartData, 'cartList', this.cartData.cartList);
            }
            return this.$util.Tips({
              title: "该产品没有更多库存了"
            });
          } else {
            if (!isDuo) {
              if (cart) {
                this.goCat(0, id, 1, 1, num.product_attr_unique);
                this.getTotalPrice();
				this.tempArr.forEach((item) => {
				  if (item.id == id) {
				    item.cart_num++;
				  }
				})
              } else {
                this.goCat(0, id, 1);
              }
            } else {
				this.tempArr.forEach((item) => {
				  if (item.id == id) {
				    item.cart_num++;
				  }
				})
			}
          }
        } else {
          num.cart_num--;
          if (num.cart_num == 0) {
            this.cartData.cartList.splice(index, 1);
            if (isDuo) {
              this.$set(this.attr.productSelect, "cart_num", 1);
              this.$set(this, "cart_num", 1);
            }
          }
          if (num.cart_num < 0) {
            if (isDuo) {
              this.$set(this.attr.productSelect, "cart_num", 1);
              this.$set(this, "cart_num", 1);
            } else {
              num.cart_num = 0;
              this.$set(this, 'tempArr', this.tempArr);
              this.$set(this.cartData, 'cartList', this.cartData.cartList);
            }
          } else {
            if (!isDuo) {
              if (cart) {
                this.goCat(0, id, 0, 1, num.product_attr_unique);
                this.getTotalPrice();
				this.tempArr.forEach((item) => {
				  if (item.id == id) {
				    item.cart_num--;
				  }
				})
              } else {
                this.goCat(0, id, 0);
              }
            } else {
				this.tempArr.forEach((item) => {
				  if (item.id == id) {
				    item.cart_num--;
				  }
				})
			}
          }
        }
      },
      // 多规格加入购物车；
      goCatNum() {
        this.goCat(1, this.id, 1);
      },
      /*
       * 加入购物车
       */
      goCat: function(duo, id, type, cart, unique) {
        let that = this;
        if (duo) {
          let productSelect = that.productValue[this.attrValue];
          //如果有属性,没有选择,提示用户选择
          if (
            that.attr.productAttr.length &&
            productSelect === undefined
          )
            return that.$util.Tips({
              title: "产品库存不足，请选择其它属性"
            });
        }
        let q = {
          productId: id,
          cartNum: duo ? that.attr.productSelect.cart_num : 1,
          isAdd: type,
          uniqueId: duo ? that.attr.productSelect.unique : cart ? unique : "",
		  tableId: this.info.tableId,
		  storeId: this.info.id,
        };
		addTableCate(q).then(res => {
			if (duo) {
			  that.attr.cartAttr = false;
			  that.$util.Tips({
			    title: "添加购物车成功"
			  });
			  // that.page = 1;
			  // that.loadend = false;
			  that.tempArr.forEach((item, index) => {
			    if (item.id == that.id) {
			      let arrtStock = that.attr.productSelect.stock
			      let objNum = parseInt(item.cart_num) + parseInt(that.attr.productSelect.cart_num);
			      item.cart_num = objNum > arrtStock ? arrtStock : objNum
			    }
			  })
			  // that.productslist();
			}
			that.getCartNum();
			if (!cart) {
			  that.getCartList(1);
			}
		}).catch(err => {
			that.$util.Tips({
			  title: err
			});
		});
      },
      goCartDuo(item) {
        if (!this.isLogin) {
          this.getIsLogin();
        } else {
          // uni.showLoading({
          //   title: '加载中'
          // });
          this.storeName = item.store_name;
          this.getAttrs(item.id);
          this.$set(this, 'id', item.id);
          this.$set(this.attr, 'cartAttr', true);
        }
      },
      getIsLogin() {
				//#ifndef MP
				toLogin();
				//#endif
				//#ifdef MP
				this.isShowAuth = true;
				//#endif
      },
      // 商品详情接口；
      getAttrs(id) {
        let that = this;
        getAttr(id, 0).then(res => {
          // uni.hideLoading();
          that.$set(that.attr, 'productAttr', res.data.productAttr);
          that.$set(that, 'productValue', res.data.productValue);
          that.$set(that, 'is_vip', res.data.storeInfo.is_vip);
          that.$set(that, 'storeInfo', res.data.storeInfo);
          that.DefaultSelect();
        })
      },
      // 去详情页
      goDetail(item) {
        goShopDetail(item, this.uid).then(res => {
          uni.navigateTo({
            url: `/pages/goods_details/index?id=${item.id}&fromType=1`
          });
        });
      },
      openTap() {
        this.iSlong = false
      },
      closeTap() {
        this.iSlong = true
      },
      // 分类数据

      getAllCategory: function() {
        let that = this;
        getStoreCategory().then(res => {
          let data = res.data;
          data.forEach(item => {
            item.children.unshift({
              'id': 0,
              'cate_name': '全部'
            })
          })
          that.categoryTitle = data[0].cate_name;
          that.cid = data[0].id;
          that.sid = 0;
          that.navActive = 0;
          that.tabClick = 0;
          that.categoryList = data;
          that.categoryErList = res.data[0].children ? res.data[0].children : [];
          that.page = 1;
          that.loadend = false;
          that.tempArr = [];
          that.getProducts();
        })
      },
      tapNav(index, item) {
        uni.pageScrollTo({
          duration: 0,
          scrollTop: 0
        })
        let list = this.categoryList[index];
        this.navActive = index;
        this.categoryTitle = list.cate_name;
        this.categoryErList = item.children ? item.children : [];
        this.tabClick = 0;
        this.tabLeft = 0;
        this.cid = list.id;
        this.sid = 0;
        this.page = 1;
        this.loadend = false;
        this.tempArr = [];
        this.getProducts();
      },
      // 导航栏点击
      longClick(index) {
        if (this.categoryErList.length > 3) {
          this.tabLeft = (index - 1) * (this.isWidth + 6) //设置下划线位置
        };
        this.tabClick = index; //设置导航点击了哪一个
        this.iSlong = true;
        this.sid = this.categoryErList[index].id;
        this.page = 1;
        this.loadend = false;
        this.tempArr = [];
        this.getProducts();
      },
    },
    onReachBottom: function() {
       this.getProducts();
    }
  }
</script>

<style lang="scss">
  /* #ifdef MP || APP-PLUS */
  .mp-header {
    z-index: 30;
    position: fixed;
    left: 0;
    top: 0;
    width: 100%;

    background: #fff;
    border-bottom: 1px solid #F0F0F0;

    .serch-wrapper {
      height: 100%;
      /* #ifdef MP */
      padding: 0 220rpx 0 53rpx;
      /* #endif */
      /* #ifdef APP-PLUS */
      padding: 0 50rpx 0 40rpx;

      /* #endif */
      .input {
        flex: 1;
        height: 55rpx;
        padding: 0 0 0 30rpx;
        background: #F8F8F8;
        color: #ADADAD;
        font-size: 26rpx;
        /* #ifdef MP */
        width: 70%;
        /* #endif */
        /* #ifdef APP-PLUS */
        width: 100%;
        /* #endif */
        border-radius: 50rpx;

        .iconfont {
          margin-right: 20rpx;
        }
      }
    }
  }

  /* #endif */
  page {
    background-color: #fff;
  }

  /deep/.product-window.joinCart {
    z-index: 999;
  }

  ::-webkit-scrollbar {
    width: 0;
    height: 0;
    color: transparent;
    display: none;
  }

  .goodCate {
    /deep/.mask {
      z-index: 99;
    }

    /deep/.attrProduct {
      .mask {
        z-index: 100;
      }
    }

    .header {
      position: fixed;
      background-color: #fff;
      top: 0;
      left: 0;
      width: 100%;
      z-index: 99;
      border-bottom: 1px solid #F0F0F0;
      /* #ifdef H5 */
      padding-top: 20rpx;
      /* #endif */

      .pageIndex {
        width: 68rpx;
        height: 68rpx;
        border-radius: 50%;
        background-color: #F7F7F7;

        .iconfont {
          color: #666666;
          font-size: 30rpx;
        }

        // image{
        // 	width: 29rpx;
        // 	height: 30rpx;
        // }
      }

      .search {
        width: 690rpx;
        height: 68rpx;
        border-radius: 36rpx;
        background-color: #F7F7F7;
        font-size: 26rpx;
        color: #cccccc;
        padding: 0 30rpx;
        box-sizing: border-box;

        .iconfont {
          font-size: 30rpx;
          margin-right: 18rpx;
          color: #666666;
        }
      }
    }

    .conter {
      /* #ifdef H5 */
      padding-top: 288rpx;
      /* #endif */
      height: 100vh;
      background-color: #fff;

      .aside {
        position: fixed;
        width: 23%;
        left: 0;
        bottom: 0;
				/* #ifdef H5 */
				top: 288rpx;
				/* #endif */
        background-color: #F7F7F7;
        overflow-y: auto;
        overflow-x: hidden;
        /* #ifdef H5 */
        // margin-top: 128rpx;
        /* #endif */
        z-index: 99;
        padding-bottom: 194rpx;

        .item {
          height: 100rpx;
          width: 100%;
          font-size: 26rpx;
          color: #333333;

          &.on {
            background-color: #FFFFFF;
            width: 100%;
            text-align: center;
            color: var(--view-theme);
            font-weight: 500;
            position: relative;

            &::after {
              content: "";
              position: absolute;
              width: 6rpx;
              height: 46rpx;
              background: var(--view-theme);
              border-radius: 0 4rpx 4rpx 0;
              left: 0
            }
          }
        }
      }

      .wrapper {
        margin-top: 104rpx;
       padding-bottom: 250rpx;
        /* #ifdef H5 */
        padding-bottom: 200rpx;
        /* #endif */
        width: 77%;
        float: right;
        background-color: #FFFFFF;
        // padding-bottom: 240rpx;

        .bgcolor {
          width: 100%;
          background-color: #FFFFFF;
        }

        // .goodsList {
        //   margin-top: 0 !important;
        // }

        .mask {
          z-index: 9;
        }

        .longTab {
          width: 65%;
          position: fixed;
          /* #ifdef H5 */
          top: 288rpx;
          /* #endif */
          height: 100rpx;
          z-index: 99;
          background-color: #FFFFFF;

          .longItem {
            height: 44rpx;
            display: inline-block;
            line-height: 44rpx;
            text-align: center;
            font-size: 26rpx;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: #333333;
            background-color: #F7F7F7;
            border-radius: 22rpx;
            margin-left: 12rpx;

            &.click {
              font-weight: bold;
              background-color: var(--view-theme);
              color: #ffffff;
            }
          }

          .underlineBox {
            height: 3px;
            width: 20%;
            display: flex;
            align-content: center;
            justify-content: center;
            transition: .5s;

            .underline {
              width: 33rpx;
              height: 4rpx;
              background-color: #FFFFFF;
            }
          }
        }

        .openList {
          width: 12%;
          height: 100rpx;
          background-color: #FFFFFF;
          line-height: 100rpx;
          padding-left: 30rpx;
          position: fixed;
          right: 0;
          /* #ifdef H5 */
          top: 288rpx;
          /* #endif */
          z-index: 99;

          .iconfont {
            font-size: 22rpx;
            color: #666666;
          }

        }


        .downTab {
          width: 78%;
          position: fixed;
          top: 0rpx;
          /* #ifdef H5 */
          top: 288rpx;
          /* #endif */
          z-index: 102;
          background-color: #FFFFFF;

          .title {
            height: 100rpx;
            font-size: 26rpx;
            color: #999999;
            padding-left: 20rpx;

            .closeList {
              width: 90rpx;
              height: 100%;
              line-height: 100rpx;
              padding-left: 30rpx;
              transform: rotate(180deg);

              .iconfont {
                font-size: 22rpx;
                color: #666666;
              }
            }
          }

          .children {
            max-height: 500rpx;
            overflow-x: hidden;
            overflow-y: auto;
            padding-bottom: 20rpx;

            .item {
              height: 60rpx;
              background-color: #F7F7F7;
              border-radius: 30rpx;
              line-height: 60rpx;
              padding: 0 15rpx;
              margin: 0 0 20rpx 20rpx;
              width: 165rpx;
              text-align: center;

              &.click {
                font-weight: bold;
                background-color: var(--view-theme);
                color: #ffffff;
              }
            }
          }
        }

        .goodsList {
          margin-top: 0rpx;
          padding: 0 30rpx 0 20rpx;
          /deep/.item {
            margin-bottom: 33rpx !important;
            .text {
              font-size: 26rpx;
            }

            .bottom {
              .sales {
                .money {
                  font-size: 34rpx;

                  text {
                    font-size: 26rpx;
                  }
                }
              }

              .cart {
                .pictrue {
                  width: 50rpx;
                  height: 50rpx;
                }
              }
            }
          }
        }
      }
    }

    .store-address {
      width: 100%;
      margin-top: 15rpx;
      position: relative;
      padding: 12rpx 30rpx 0 30rpx;
      height: 185rpx;
      background-color: #fff;

      .address {
        font-size: 32rpx;
        font-weight: 500;
        color: #333333;
				.name{
					max-width: 400rpx;
				}

        .icon-xiangyou {
          font-size: 24rpx;
          margin-left: 10rpx;
        }
      }

      .distance {
        margin-top: 16rpx;
        font-size: 24rpx;
        font-weight: 400;
        color: #999999;
      }

      .time {
        margin-top: 16rpx;
        font-size: 24rpx;
        font-weight: 400;
        color: #333333;
      }

      .switch {
        position: absolute;
        top: 15rpx;
        /* #ifdef H5 */
        top: 20rpx;
        /* #endif */
        right: 30rpx;
				width: 194rpx;
				height: 58rpx;
				background: #F5F5F5;
				border-radius: 33rpx;
        .title {
          width: 82rpx;
          height: 100%;
          line-height: 58rpx;
          border-radius: 33rpx;
          text-align: center;
          padding-right: 20rpx;
        
          &.onLeft {
            padding-left: 20rpx;
            padding-right: 0;
          }
        
          &.on {
            width: 100rpx;
            background-color: var(--view-theme) !important;
            color: #fff;
            padding: 0 !important;
          }
        }
      }
    }

    .footer {
      width: 100%;
      position: fixed;
      left: 0;
      background-color: #fff;
      box-shadow: 0px -3px 16px rgba(36, 12, 12, 0.05);
      z-index: 100;
      padding-left: 30rpx;
      box-sizing: border-box;
      height: 100rpx;
	  // #ifdef H5
	  bottom: 94rpx;
	  bottom: calc(94rpx+ constant(safe-area-inset-bottom)); ///兼容 IOS<11.2/
	  bottom: calc(94rpx + env(safe-area-inset-bottom)); ///兼容 IOS>11.2/
	  // #endif
	  // #ifndef H5
	  bottom: 98rpx;
	  bottom: calc(98rpx+ constant(safe-area-inset-bottom)); ///兼容 IOS<11.2/
	  bottom: calc(98rpx + env(safe-area-inset-bottom)); ///兼容 IOS>11.2/
	  // #endif

      &.on {
        // #ifndef H5
        bottom: 0rpx;
        // #endif
      }

      .cartIcon {
        width: 80rpx;
        height: 80rpx;
        border-radius: 50%;
        position: relative;
        margin-top: -36rpx;

        .iconfont {
          font-size: 94rpx;
          margin-top: 12rpx;
          color: var(--view-theme);
        }

        &.noCart {
          .iconfont {
            color: #CBCBCB;
          }
        }

        .num {
          min-width: 14rpx;
          background-color: #fff;
          color: var(--view-theme);
          border-radius: 15px;
          position: absolute;
          right: -10rpx;
          top: 20rpx;
          font-size: 20rpx;
          padding: 0 10rpx;
          border: 1px solid var(--view-theme);
        }
      }

      .money {
        font-size: 26rpx;
        font-weight: bold;
        color: var(--view-priceColor);
        margin-right: 34rpx;

        .num {
          font-size: 34rpx;
        }
      }

      .bnt {
        width: 192rpx;
        height: 76rpx;
        background-color: var(--view-theme);
        border-radius: 46px;
        line-height: 76rpx;
        text-align: center;
        color: #fff;
        font-size: 28rpx;
        margin-right: 30rpx;
      }

      .noCart {

        .money {
          color: #CBCBCB;
        }

        .bnt {
          background-color: #CBCBCB;
        }
      }
    }
  }
</style>
