<template>
  <!-- 我的有优惠券模块 -->
	<view :style="colorStyle">
		<view class="navbar acea-row row-around">
			<view class="item acea-row row-center-wrapper" :class="{ on: navOn === 1 }" @click="onNav(1)">未使用</view>
			<view class="item acea-row row-center-wrapper" :class="{ on: navOn === 2 }" @click="onNav(2)">已使用/过期</view>
		</view>
		<view class='coupon-list' v-if="couponsList.length">
			<view class='item acea-row row-center-wrapper' v-for='(item,index) in couponsList' :key="index"
				:class="{svip: item.receive_type === 4}" @click="useCoupon(item)">
				<view class="moneyCon acea-row row-center-wrapper">
					<view class='money' :class='item._type == 0 ? "moneyGray" : ""'>
						<view><text v-if="item.coupon_type==1">￥</text><text class='num'>{{item.coupon_type==1?item.coupon_price:parseFloat(item.coupon_price)/10}}</text><text v-if="item.coupon_type==2">折</text></view>
						<view class="pic-num" v-if="item.use_min_price > 0">满{{item.use_min_price}}元可用</view>
						<view class="pic-num" v-else>无门槛券</view>
					</view>
				</view>
				<view class='text'>
					<view class='condition'>
						<view class="name line2">
							<view class="line-title acea-row row-center-wrapper" :class="item._type === 0 ? 'bg-color-huic' : 'bg-color-check'"
								v-if="item.applicable_type === 0">通用券</view>
							<view class="line-title acea-row row-center-wrapper" :class="item._type === 0 ? 'bg-color-huic' : 'bg-color-check'"
								v-else-if="item.applicable_type === 1">品类券</view>
							<view class="line-title acea-row row-center-wrapper" :class="item._type === 0 ? 'bg-color-huic' : 'bg-color-check'"
								v-else-if="item.applicable_type === 3">品牌券</view>
							<view class="line-title acea-row row-center-wrapper" :class="item._type === 0 ? 'bg-color-huic' : 'bg-color-check'"
								v-else>商品券</view>
							<image src="../../../static/images/fvip.png" class="pic" v-if="item.receive_type===4">
							</image>
							<text class="title">{{item.coupon_title}}</text>
						</view>
					</view>
					<view class='data acea-row row-between-wrapper'>
						<view>{{item.add_time}}-{{item.end_time}}</view>
						<view class='bnt gray' v-if="item._type==0">{{item._msg}}</view>
						<view class='bnt bg-color' v-else>{{item._msg}}</view>
					</view>
				</view>
			</view>
		</view>
		<view class='noCommodity' v-if="!couponsList.length && page === 2">
			<view class='pictrue'>
				<image :src="imgHost + '/statics/images/noCoupon.png'"></image>
			</view>
		</view>
		<home v-if="navigation"></home>
		<!-- #ifdef MP -->
		<authorize v-if="isShowAuth" @authColse="authColse" @onLoadFun="onLoadFun"></authorize>
		<!-- #endif -->
	</view>
</template>

<script>
	import {
		getUserCoupons
	} from '@/api/api.js';
	import {
		toLogin
	} from '@/libs/login.js';
	import {
		mapGetters
	} from "vuex";
	import home from '@/components/home';
	import colors from '@/mixins/color.js';
	import {HTTP_REQUEST_URL} from '@/config/app';
	export default {
		components: {
			home
		},
		mixins:[colors],
		data() {
			return {
				couponsList: [],
				loading: false,
				isAuto: false, //没有授权的不会自动授权
				isShowAuth: false, //是否隐藏授权
				navOn: 1,
				page: 1,
				limit: 15,
				finished: false,
				imgHost:HTTP_REQUEST_URL
			};
		},
		computed: mapGetters(['isLogin']),
		watch: {
			isLogin: {
				handler: function(newV, oldV) {
					if (newV) {
						// #ifdef H5 || APP-PLUS
						this.getUseCoupons();
						// #endif
					}
				},
				deep: true
			}
		},
		onLoad() {
			if (this.isLogin) {
				this.getUseCoupons();
			} else {
				// #ifdef H5 || APP-PLUS
				toLogin()
				// #endif
				// #ifdef MP
				this.isShowAuth = true;
				// #endif
			}
		},
		onShow() {
			uni.removeStorageSync('form_type_cart');
		},
		onReachBottom() {
			this.getUseCoupons();
		},
		methods: {
			onNav: function(type) {
				this.navOn = type;
				this.couponsList = [];
				this.page = 1;
				this.finished = false;
				this.getUseCoupons();
			},
			useCoupon(item){
				let url = '';
				// 通用券
				if (item.category_id == 0 && item.product_id == '' && item.brand_id == 0) {
					url = '/pages/goods/goods_list/index?title=默认'
				}
				// 品类券
				if (item.category_id != 0) {
					if (item.category_type == 1) {
						url = '/pages/goods/goods_list/index?cid='+item.category_id+'&title='+item.category_name
					}else{
						url = '/pages/goods/goods_list/index?sid='+item.category_id+'&title='+item.category_name
					}
				}
				//商品券
				if (item.product_id != '') {
					let arr = item.product_id.split(',');
					let num = arr.length;
					if (num == 1) {
						url = '/pages/goods_details/index?id='+item.product_id
					} else {
						url = '/pages/goods/goods_list/index?productId='+item.product_id+'&title=默认'
					}
				}
				//品牌券
				if(item.brand_id != 0){
					url = '/pages/goods/goods_list/index?brandId='+item.brand_id+'&title=默认'
				}
				uni.navigateTo({
					url: url
				});
			},
			/**
			 * 授权回调
			 */
			onLoadFun: function() {
				this.getUseCoupons();
				this.isShowAuth = false;
			},
			// 授权关闭
			authColse: function(e) {
				this.isShowAuth = e
			},
			/**
			 * 获取领取优惠券列表
			 */
			getUseCoupons: function() {
				let that = this;
				if (that.loading || that.finished) {
					return;
				}
				that.loading = true;
				uni.showLoading({
					title: '正在加载…'
				});
				getUserCoupons(this.navOn, {
					page: this.page,
					limit: this.limit
				}).then(res => {
					that.loading = false;
					uni.hideLoading();
					that.couponsList = that.couponsList.concat(res.data);
					that.finished = res.data.length < that.limit;
					that.page += 1;
				}).catch(err => {
					that.loading = false;
					uni.showToast({
						title: err,
						icon: 'none'
					});
				});
			}
		}
	}
</script>

<style>
	.money {
		display: flex;
		flex-direction: column;
		justify-content: center;
	}

	.pic-num {
		color: #ffffff;
		font-size: 24rpx;
	}

	.coupon-list .item .text .condition {
		display: flex;
		align-items: center;
	}

	.coupon-list .item .text .condition .name {
		font-size: 26rpx;
		font-weight: 500;
	}

	.coupon-list .item .text .condition .pic {
		width: 30rpx;
		height: 30rpx;
		display: block;
		margin-right: 10rpx;
		display: inline-block;
		vertical-align: sub;
	}

	.condition .line-title {
		width: 70rpx;
		height: 32rpx !important;
		/* #ifndef APP */
		line-height: 28rpx;
		/* #endif */
		/* #ifdef APP */
		line-height: 30rpx;
		/* #endif */
		text-align: center;
		box-sizing: border-box;
		background: var(--view-minorColorT);
		border: 1px solid var(--view-theme);
		opacity: 1;
		border-radius: 20rpx;
		font-size: 18rpx !important;
		color: var(--view-theme);
		margin-right: 12rpx;
		text-align: center;
		display: inline-block;
		vertical-align: middle;
	}
	
	.condition .title{
		vertical-align: middle;
	}

	.condition .line-title.bg-color-huic {
		border-color: #BBB !important;
		color: #bbb !important;
		background-color: #F5F5F5 !important;
	}
</style>

<style lang="scss" scoped>
	.navbar {
		position: fixed;
		top: 0;
		left: 0;
		width: 100%;
		height: 106rpx;
		background-color: #FFFFFF;
		z-index: 9;

		.item {
			border-top: 5rpx solid transparent;
			border-bottom: 5rpx solid transparent;
			font-size: 30rpx;
			color: #999999;

			&.on {
				border-bottom-color: var(--view-theme);
				color: #282828;
			}
		}
	}

	.coupon-list {
		margin-top: 122rpx;
	}

	.noCommodity {
		margin-top: 300rpx;
	}
</style>
