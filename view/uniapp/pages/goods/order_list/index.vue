<template>
	<view :style="colorStyle">
		<view class="my-order">
			<view class="header bg-color">
				<view class="picTxt acea-row row-between-wrapper">
					<view class="text">
						<view class="name">订单信息</view>
						<view>消费订单：{{ orderData.order_count || 0 }} 总消费：￥{{ orderData.sum_price || 0 }}</view>
					</view>
					<view class="pictrue">
						<image src="../static/orderTime.png"></image>
					</view>
				</view>
			</view>
			<view class="nav acea-row row-around">
				<view class="item" :class="orderStatus === '' ? 'on' : ''" @click="statusClick('')">
					<view>全部</view>
					<view class="num">{{ orderData.order_count || 0 }}</view>
				</view>
				<view class="item" :class="orderStatus === 0 ? 'on' : ''" @click="statusClick(0)">
					<view>待付款</view>
					<view class="num">{{ orderData.unpaid_count || 0 }}</view>
				</view>
				<view class="item" :class="orderStatus == 1 ? 'on' : ''" @click="statusClick(1)">
					<view>待发货</view>
					<view class="num">{{ orderData.unshipped_count || 0 }}</view>
				</view>
				<view class="item" :class="orderStatus == 2 ? 'on' : ''" @click="statusClick(2)">
					<view>待收货</view>
					<view class="num ">{{ orderData.received_count || 0 }}</view>
				</view>
				<view class="item" :class="orderStatus == 3 ? 'on' : ''" @click="statusClick(3)">
					<view>待评价</view>
					<view class="num">{{ orderData.evaluated_count || 0 }}</view>
				</view>
			<!-- 	<view class="item" :class="orderStatus == 4 ? 'on' : ''" @click="statusClick(4)">
					<view>已完成</view>
					<view class="num">{{ orderData.complete_count || 0 }}</view>
				</view> -->
			</view>
			<Loading :loaded="loaded" :loading="loading"></Loading>
			<view class="list">
				<view class="item" v-for="(item, index) in orderList" :key="index">
					<view @click="goOrderDetails(item.order_id)">
						<view class="title acea-row row-between-wrapper">
							<view class="acea-row row-middle">
								<text class="sign cart-color acea-row row-center-wrapper"
									v-if="item.type == 2">砍价</text>
								<text class="sign cart-color acea-row row-center-wrapper"
									v-else-if="item.type == 3">拼团</text>
								<text class="sign cart-color acea-row row-center-wrapper"
									v-else-if="item.type == 1">秒杀</text>
								<text class="sign cart-color acea-row row-center-wrapper"
									v-else-if="item.type == 8">抽奖</text>
								<text class="sign cart-color acea-row row-center-wrapper"
									v-else-if="item.type == 9">拼单</text>
								<text class="sign cart-color acea-row row-center-wrapper"
									v-else-if="item.type == 10">桌码</text>
								<view>{{ item._add_time }}</view>
							</view>
							<view v-if="item._status._type == 9" class="font-color">线下付款,未支付</view>
							<view v-else-if="item._status._type == 0" class="font-color">待付款</view>
							<view v-else-if="item._status._type == 1 && (item.shipping_type == 1 || item.shipping_type == 3)" class="font-color">待发货
							   <text v-if="item.refund.length">{{item.is_all_refund?'，退款中':'，部分退款中'}}</text>
							</view>
							<view v-else-if="item._status._type == 1 && (item.shipping_type == 2 || item.shipping_type == 4)"  class="font-color">
								{{item.shipping_type == 2?'待核销':'待收货'}}
								 <text v-if="item.refund.length">{{item.is_all_refund?'，退款中':'，部分退款中'}}</text>
							</view>
							<view v-else-if="item._status._type == 2" class="font-color">待收货
							   <text v-if="item.refund.length">{{item.is_all_refund?'，退款中':'，部分退款中'}}</text>
							</view>
							<view v-else-if="item._status._type == 3" class="font-color">待评价
							   <text v-if="item.refund.length">{{item.is_all_refund?'，退款中':'，部分退款中'}}</text>
							</view>
							<view v-else-if="item._status._type == 4" class="font-color">已完成
							   <text v-if="item.refund.length">{{item.is_all_refund?'，退款中':'，部分退款中'}}</text>
							</view>
							<view v-else-if="item._status._type == -2" class="font-color">已退款
							</view>
							<view v-else-if="item._status._type == 5 && item.status == 0" class="font-color">待核销
							  <text v-if="item.refund.length">{{item.is_all_refund?'，退款中':'，部分退款中'}}</text>
							</view>
							<view v-else-if="item._status._type == 5 && item.status == 5" class="font-color">部分核销
							  <text v-if="item.refund.length">{{item.is_all_refund?'，退款中':'，部分退款中'}}</text>
							</view>
							<view v-else-if="item._status._type == -1" class="font-color">申请退款
							</view>
						</view>
						<view class="item-info acea-row row-between row-top" v-for="(items, index) in item.cartInfo"
							:key="index">
							<view class="pictrue">
								<image :src="items.productInfo.image"></image>
							</view>
							<view class="text acea-row row-between">
								<view class="name line2"><text class="font-color" v-if="items.is_gift == 1">[赠品]</text>{{ items.productInfo.store_name }}</view>
								<view class="money">
									<view v-if="items.productInfo.attrInfo">￥{{ items.productInfo.attrInfo.price }}</view>
									<view v-else>￥{{ items.productInfo.price }}</view>
									<view>x{{ items.cart_num }}</view>
									<view v-if="items.refund_num && item._status._type != -2" class="return">{{ items.refund_num }}件退款中</view>
								</view>
							</view>
						</view>
						<view class="totalPrice">
							共{{ item.total_num || 0 }}件商品，总金额
							<text class="money">￥{{ item.pay_price }}</text>
						</view>
					</view>
					<view class="bottom acea-row row-right row-middle">
						<view class="bnt cancelBnt" v-if="item._status._type == 0 || item._status._type == 9"
							@click="cancelOrder(index, item.order_id)">取消订单</view>
						<view class="bnt bg-color" v-if="item._status._type == 0"
							@click="goPay(item.pay_price, item.order_id)">立即付款</view>
						<!-- <view class="bnt bg-color" v-else-if="item._status._type == 3"
							@click="goOrderDetails(item.order_id)">去评价</view> --> 
						<!-- <view class="bnt bg-color"
							v-else-if="item.seckill_id < 1 && item.bargain_id < 1 && item.combination_id < 1 && item._status._type == 4"
							@click="goOrderDetails(item.order_id)">
							再次购买
						</view> -->
						<view class="bnt cancelBnt" v-if="item._status._type == 4"
							@click="delOrder(item.order_id, index)">删除订单</view>
						<view class="bnt bg-color" @click="goOrderDetails(item.order_id)">查看详情</view>
					</view>
				</view>
			</view>
			<view class="loadingicon acea-row row-center-wrapper" v-if="orderList.length > 0">
				<text class="loading iconfont icon-jiazai" :hidden="loading == false"></text>
				{{ loadTitle }}
			</view>
			<view v-if="orderList.length == 0 && !loading">
				<emptyPage title="暂无订单信息~"></emptyPage>
			</view>
		</view>
		<home v-if="navigation"></home>
		<payment :payMode="payMode" :pay_close="pay_close" @onChangeFun="onChangeFun" :order_id="pay_order_id"
			:totalPrice="totalPrice"></payment>
		<!-- #ifdef MP -->
		<authorize v-if="isShowAuth" @authColse="authColse" @onLoadFun="onLoadFun"></authorize>
		<!-- #endif -->
	</view>
</template>

<script>
	import {
		getOrderList,
		orderData,
		orderCancel,
		orderDel,
		orderPay
	} from '@/api/order.js';
	import {
		getUserInfo
	} from '@/api/user.js';
	import {
		openOrderSubscribe
	} from '@/utils/SubscribeMessage.js';
	import home from '@/components/home';
	import payment from '@/components/payment';
	import {
		toLogin
	} from '@/libs/login.js';
	import {
		mapGetters
	} from 'vuex';
	import emptyPage from '@/components/emptyPage.vue';
	import colors from '@/mixins/color.js';
	import Loading from '@/components/Loading/index.vue'
	export default {
		components: {
			Loading,
			payment,
			home,
			emptyPage
		},
		mixins:[colors],
		data() {
			return {
				loaded: false,
				loading: false, //是否加载中
				loadend: false, //是否加载完毕
				loadTitle: '加载更多', //提示语
				orderList: [], //订单数组
				orderData: {}, //订单详细统计
				orderStatus: '', //订单状态
				page: 1,
				limit: 20,
				payMode: [{
						name: '微信支付',
						icon: 'icon-weixinzhifu',
						value: 'weixin',
						title: '使用微信快捷支付',
						payStatus: true
					},
					{
						name: '支付宝支付',
						icon: 'icon-zhifubao',
						value: 'alipay',
						title: '使用线上支付宝支付',
						payStatus: true
					},
					{
						name: '余额支付',
						icon: 'icon-yuezhifu',
						value: 'yue',
						title: '当前可用余额：',
						number: 0,
						payStatus: true
					}
				],
				pay_close: false,
				pay_order_id: '',
				totalPrice: '0',
				isAuto: false, //没有授权的不会自动授权
				isShowAuth: false //是否隐藏授权
			};
		},
		computed: mapGetters(['isLogin']),
		onShow() {
			uni.removeStorageSync('form_type_cart');
			this.page = 1;
			this.loadend = false;
			this.orderList = [];
			if (this.isLogin) {
				this.getFun();
			} else {
				// #ifdef H5 || APP-PLUS
				toLogin()
				// #endif
				// #ifdef MP
				this.isShowAuth = true;
				// #endif
			}
		},
		/**
		 * 生命周期函数--监听页面加载
		 */
		onLoad(options) {
			if (options.status) this.orderStatus = (options.status==undefined && options.status!=0)?'':parseInt(options.status);
		},
		methods: {
			getFun(){
				this.getOrderData();
				this.getOrderList();
				this.getUserInfo();
			},
			onLoadFun(){
				this.getFun();
				this.isShowAuth = false;
			},
			// 授权关闭
			authColse: function(e) {
				this.isShowAuth = e;
			},
			/**
			 * 事件回调
			 *
			 */
			onChangeFun: function(e) {
				let opt = e;
				let action = opt.action || null;
				let value = opt.value != undefined ? opt.value : null;
				action && this[action] && this[action](value);
			},
			/**
			 * 获取用户信息
			 *
			 */
			getUserInfo: function() {
				let that = this;
				getUserInfo().then(res => {
					that.payMode[2].number = res.data.now_money;
					that.$set(that, 'payMode', that.payMode);
				});
			},
			/**
			 * 关闭支付组件
			 *
			 */
			payClose: function() {
				this.pay_close = false;
			},
			/**
			 * 获取订单统计数据
			 *
			 */
			getOrderData: function() {
				let that = this;
				orderData().then(res => {
					that.$set(that, 'orderData', res.data);
					that.payMode.map(item => {
						if (item.value == 'weixin') {
							item.payStatus = res.data.pay_weixin_open ? true : false;
						}
						if (item.value == 'alipay') {
							item.payStatus = res.data.ali_pay_status ? true : false;
						}
						if (item.value == 'yue') {
							item.payStatus = res.data.yue_pay_status == 1 ? true : false;
						}
					});
					//#ifdef MP
					this.payMode[1].payStatus = false;
					//#endif
				});
			},
			/**
			 * 取消订单
			 *
			 */
			cancelOrder: function(index, order_id) {
				let that = this;
				if (!order_id)
					return that.$util.Tips({
						title: '缺少订单号无法取消订单'
					});
				orderCancel(order_id)
					.then(res => {
						return that.$util.Tips({
								title: res.msg,
								icon: 'success'
							},
							function() {
								that.orderList.splice(index, 1);
								that.$set(that, 'orderList', that.orderList);
								that.$set(that.orderData, 'unpaid_count', that.orderData.unpaid_count - 1);
								that.getOrderData();
							}
						);
					})
					.catch(err => {
						return that.$util.Tips({
							title: err
						});
					});
			},
			/**
			 * 打开支付组件
			 *
			 */
			goPay: function(pay_price, order_id) {
				this.$set(this, 'pay_close', true);
				this.$set(this, 'pay_order_id', order_id);
				this.$set(this, 'totalPrice', pay_price);
			},
			/**
			 * 支付成功回调
			 *
			 */
			pay_complete: function() {
				this.loadend = false;
				this.page = 1;
				this.$set(this, 'orderList', []);
				this.pay_close = false;
				uni.navigateTo({
					url: '/pages/goods/order_pay_status/index?order_id=' + this.pay_order_id +
						'&msg=支付成功&type=3&totalPrice=' + this.totalPrice
				})
				this.pay_order_id = '';
				this.getOrderData();
				this.getOrderList();
			},
			/**
			 * 支付失败回调
			 *
			 */
			pay_fail: function() {
				this.pay_close = false;
				this.pay_order_id = '';
			},
			/**
			 * 去订单详情
			 */
			goOrderDetails: function(order_id) {
				if (!order_id)
					return that.$util.Tips({
						title: '缺少订单号无法查看订单详情'
					});
				// #ifdef MP
				uni.showLoading({
					title: '正在加载'
				});
				openOrderSubscribe()
					.then(() => {
						uni.hideLoading();
						uni.navigateTo({
							url: '/pages/goods/order_details/index?order_id=' + order_id
						})
					})
					.catch(err => {
						uni.hideLoading();
					});
				// #endif
				// #ifndef MP
				uni.navigateTo({
					url: '/pages/goods/order_details/index?order_id=' + order_id
				});
				// #endif
			},
			/**
			 * 切换类型
			 */
			statusClick: function(status) {
				if(this.loading) return
				if (status === this.orderStatus) return;
				this.orderStatus = status;
				this.loadend = false;
				this.page = 1;
				this.$set(this, 'orderList', []);
				this.getOrderList();
			},
			/**
			 * 获取订单列表
			 */
			getOrderList: function() {
				let that = this;
				if (that.loadend) return;
				if (that.loading) return;
				that.loading = true;
				that.loadTitle = '加载更多';
				getOrderList({
						type: that.orderStatus,
						page: that.page,
						limit: that.limit
					})
					.then(res => {
						let list = res.data || [];
						let loadend = list.length < that.limit;
						that.orderList = that.$util.SplitArray(list, that.orderList);
						that.$set(that, 'orderList', that.orderList);
						that.loadend = loadend;
						that.loading = false;
						that.loadTitle = loadend ? '没有更多内容啦~' : '加载更多';
						that.page = that.page + 1;
					})
					.catch(err => {
						that.loading = false;
						that.loadTitle = '加载更多';
					});
			},

			/**
			 * 删除订单
			 */
			delOrder: function(order_id, index) {
				let that = this;
				uni.showModal({
				    title: '删除订单',
				    content: '确定删除该订单',
				    success: function (res) {
				        if (res.confirm) {
							orderDel(order_id)
								.then(res => {
									that.orderList.splice(index, 1);
									that.$set(that, 'orderList', that.orderList);
									that.$set(that.orderData, 'unpaid_count', that.orderData.unpaid_count - 1);
									that.getOrderData();
									return that.$util.Tips({
										title: '删除成功',
										icon: 'success'
									});
								})
								.catch(err => {
									return that.$util.Tips({
										title: err
									});
								});
				        } else if (res.cancel) {
							return that.$util.Tips({
										title: '已取消'
									});
				        }
				    }
				});
				
			}
		},
		onReachBottom: function() {
			this.getOrderList();
		}
	};
</script>

<style scoped lang="scss">
	.my-order .header {
		height: 260rpx;
		padding: 0 30rpx;
	}

	.my-order .header .picTxt {
		height: 190rpx;
	}

	.my-order .header .picTxt .text {
		color: rgba(255, 255, 255, 0.8);
		font-size: 26rpx;
		font-family: 'Guildford Pro';
	}

	.my-order .header .picTxt .text .name {
		font-size: 34rpx;
		font-weight: bold;
		color: #fff;
		margin-bottom: 20rpx;
	}

	.my-order .header .picTxt .pictrue {
		width: 122rpx;
		height: 109rpx;
	}

	.my-order .header .picTxt .pictrue image {
		width: 100%;
		height: 100%;
	}

	.my-order .nav {
		background-color: #fff;
		width: 690rpx;
		height: 140rpx;
		border-radius: 6rpx;
		margin: -73rpx auto 0 auto;
	}

	.my-order .nav .item {
		text-align: center;
		font-size: 26rpx;
		color: #282828;
		padding: 27rpx 0;
		border-bottom: 5rpx solid transparent;
	}

	.my-order .nav .item.on {
		/* #ifdef H5 || MP */
		font-weight: bold;
		/* #endif */
		/* #ifdef APP-PLUS */
		color: #000;
		/* #endif */
		border-color: var(--view-theme);
	}

	.my-order .nav .item .num {
		margin-top: 18rpx;
	}

	.my-order .list {
		width: 690rpx;
		margin: 14rpx auto 0 auto;
	}

	.my-order .list .item {
		background-color: #fff;
		border-radius: 6rpx;
		margin-bottom: 14rpx;
	}

	.my-order .list .item .title {
		height: 84rpx;
		padding: 0 30rpx;
		border-bottom: 1rpx solid #eee;
		font-size: 28rpx;
		color: #282828;
	}

	.my-order .list .item .title .sign {
		font-size: 24rpx;
		padding: 0 7rpx;
		height: 36rpx;
		margin-right: 15rpx;
	}

	.my-order .list .item .item-info {
		padding: 0 30rpx;
		margin-top: 22rpx;
	}

	.my-order .list .item .item-info .pictrue {
		width: 120rpx;
		height: 120rpx;
	}

	.my-order .list .item .item-info .pictrue image {
		width: 100%;
		height: 100%;
		border-radius: 6rpx;
	}

	.my-order .list .item .item-info .text {
		width: 486rpx;
		font-size: 28rpx;
		color: #999;
	}

	.my-order .list .item .item-info .text .name {
		width: 306rpx;
		color: #282828;
		height: 74rpx;
	}

	.my-order .list .item .item-info .text .money {
		text-align: right;
	}
	
	.my-order .list .item .item-info .text .money .return{
		// color: var(--view-priceColor);
		margin-top: 10rpx;
		font-size: 24rpx;
	}

	.my-order .list .item .totalPrice {
		font-size: 26rpx;
		color: #282828;
		text-align: right;
		margin: 27rpx 0 0 30rpx;
		padding: 0 30rpx 30rpx 0;
		border-bottom: 1rpx solid #eee;
	}

	.my-order .list .item .totalPrice .money {
		font-size: 28rpx;
		font-weight: bold;
		color:  var(--view-priceColor);
	}

	.my-order .list .item .bottom {
		height: 107rpx;
		padding: 0 30rpx;
	}

	.my-order .list .item .bottom .bnt {
		width: 176rpx;
		height: 60rpx;
		text-align: center;
		line-height: 60rpx;
		color: #fff;
		border-radius: 50rpx;
		font-size: 27rpx;
	}

	.my-order .list .item .bottom .bnt.cancelBnt {
		border: 1rpx solid #ddd;
		color: #aaa;
	}

	.my-order .list .item .bottom .bnt~.bnt {
		margin-left: 17rpx;
	}

	.noCart {
		margin-top: 171rpx;
		padding-top: 0.1rpx;
	}

	.noCart .pictrue {
		width: 414rpx;
		height: 336rpx;
		margin: 78rpx auto 56rpx auto;
	}

	.noCart .pictrue image {
		width: 100%;
		height: 100%;
	}

	.line2 {
		word-break: break-all;
	}
</style>
