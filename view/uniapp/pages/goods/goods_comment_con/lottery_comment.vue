<template>
	<view>
		<view class="header" v-show="lotteryShow">
			<view class="pay-status">
				<text class="iconfont icon-gou"></text>
				<view class="pay-status-r">
					<text class="pay-status-text">
						评价完成
					</text>
					<text>

					</text>
				</view>
			</view>
			<view class="jump">
				<view class="jump-index" @click="goIndex">
					返回首页
				</view>
			</view>
		</view>
		<view class="grids-top" v-show="lotteryShow">
			<image src="../static/pay-lottery-l.png" mode=""></image>
			<view class="grids-title">
				<view>恭喜您，</view>
				<view class="grids-frequency">获得{{lottery_num}}次</view>
				<view>抽奖机会</view>
			</view>
			<image src="../static/pay-lottery-r.png" mode=""></image>
		</view>
		<view class='termValidity acea-row row-center-wrapper' v-show="lotteryShow">
			<view class='timeItem acea-row row-center-wrapper'>
				<view>距有效期仅剩</view>
				<countDown :is-day="false" :tip-text="' '" :day-text="' '" :hour-text="' : '"
					:minute-text="' : '" :second-text="' '" :datatime="datatime"
					style="margin-top: 4rpx;"></countDown>
			</view>
		</view>
		<view class="grids" v-show="lotteryShow">
			<image class="grids-bag" :src="imgHost + '/statics/images/pay-lottery-bag.png'" mode=""></image>
			<view class="grids-box">
				<gridsLottery class="" :prizeData="prize" :userCount="userCount" @get_winingIndex='getWiningIndex'
					@luck_draw_finish='luck_draw_finish' :lotteryType='1' :datatime="datatime">
				</gridsLottery>
			</view>
		</view>
		<lotteryAleart :aleartStatus="aleartStatus" @close="closeLottery" :alData="alData" :aleartType="aleartType">
		</lotteryAleart>
		<view class="mask" v-if="aleartStatus || addressModel" @click="lotteryAleartClose"></view>
		<userAddress :aleartStatus="addressModel" @getAddress="getAddress" @close="()=>{addressModel = false}">
		</userAddress>
		<!-- #ifdef MP -->
		  <authorize v-if="isShowAuth" @authColse="authColse" @onLoadFun="onLoadFun"></authorize>
		<!-- #endif -->
	</view>
</template>

<script>
	import countDown from '@/components/countDown';
	import gridsLottery from '../components/lottery/index.vue'
	import lotteryAleart from '../components/lotteryAleart/index.vue'
	import userAddress from '../components/userAddress/index.vue'
	import {
		openOrderSubscribe
	} from '@/utils/SubscribeMessage.js';
	import {
		toLogin
	} from '@/libs/login.js';
	import {
		getLotteryData,
		startLottery,
		receiveLottery
	} from '@/api/lottery.js'
	import {
		mapGetters
	} from "vuex";
	import {HTTP_REQUEST_URL} from '@/config/app';
	import { postCartAdd } from '@/api/store.js';
	export default {
		components: {
			gridsLottery,
			lotteryAleart,
			userAddress,
			countDown
		},
		computed: mapGetters(['isLogin']),
		data() {
			return {
				lotteryShow: false,
				addressModel: false,
				lottery_num: 0,
				aleartType: 0,
				aleartStatus: false,
				lottery_draw_param: {
					startIndex: 3, //开始抽奖位置，从0开始
					totalCount: 3, //一共要转的圈数
					winingIndex: 1, //中奖的位置，从0开始
					speed: 100 //抽奖动画的速度 [数字越大越慢,默认100]
				},
				alData: {},
				type: '',
				prize: [],
				orderId: '',
				order_pay_info: {
					paid: 1,
					_status: {}
				},
				isAuto: false, //没有授权的不会自动授权
				isShowAuth: false, //是否隐藏授权
				couponsHidden: true,
				couponList: [],
				datatime:0,
				imgHost:HTTP_REQUEST_URL,
				userCount: {}
			};
		},
		computed: mapGetters(['isLogin']),
		watch: {
			isLogin: {
				handler: function(newV, oldV) {
					if (newV) {
						// this.getOrderPayInfo();
					}
				},
				deep: true
			}
		},
		onLoad(options) {
			this.orderId = options.order_id;
			this.type = options.type;
			if (this.isLogin) {
				// this.getOrderPayInfo();
				this.getLotteryData(this.type)
			}
			// #ifdef H5
			document.addEventListener('visibilitychange', (e) => {
				let state = document.visibilityState
				if (state == 'hidden') {
				}
				if (state == 'visible') {
					// this.getOrderPayInfo();
				}
			});
			// #endif
		},
		onShow() {
			uni.removeStorageSync('form_type_cart');
			if(!this.isLogin){
				// #ifndef MP
				toLogin()
				// #endif
				// #ifdef MP
				this.isShowAuth = true;
				// #endif
			}
		},
		methods: {
			onLoadFun(){
				this.getLotteryData(this.type)
				this.isShowAuth = false
			},
			// 授权关闭
			authColse: function(e) {
				this.isShowAuth = e
			},
			openTap() {
				this.$set(this, 'couponsHidden', !this.couponsHidden);
			},
			getWiningIndex(callback) {
				this.aleartType = 0
				startLottery({
					id: this.id
				}).then(res => {
					this.prize.forEach((item, index) => {
						if (res.data.id === item.id) {
							this.alData = res.data
							this.lottery_draw_param.winingIndex = index;
							callback(this.lottery_draw_param);
						}
					})
				}).catch(err => {
					this.$util.Tips({
						title: err
					});
				})
			},
			/**
			 * 去首页关闭当前所有页面
			 */
			goIndex: function(e) {
				uni.switchTab({
					url: '/pages/index/index'
				});
			},
			getLotteryData(type) {
				getLotteryData(type).then(res => {
					this.lotteryShow = true
					this.factor_num = res.data.lottery.factor_num
					this.id = res.data.lottery.id
					this.prize = res.data.lottery.prize
					this.lottery_num = res.data.lottery_num
					this.prize.push({
						a: 1
					})
					this.datatime = parseInt(res.data.cache_time);
					this.userCount = {
						today: res.data.todayCount,
						total: res.data.totalCount
					};
				}).catch(err => {
					uni.redirectTo({
						url: '/pages/goods/order_details/index?order_id=' + this.orderId
					})
				})
			},
			closeLottery(status) {
				this.aleartStatus = false
				this.getLotteryData(this.type)
				if (this.alData.type === 6) {
					// this.addressModel = true
					postCartAdd({
						cartNum: 1,
						new: 1,
						is_new: 1,
						productId: this.alData.product_id,
						uniqueId: this.alData.unique,
						luckRecordId: this.alData.lottery_record_id,
					}).then(({ data }) => {
						uni.navigateTo({
							url: `/pages/goods/lottery/grids/order?luckRecordId=${this.alData.lottery_record_id}&cartId=${data.cartId}`
						});
					}).catch(err => {
						this.$util.Tips({
							title: `${err},请联系客服`
						});
					});
				}
			},
			getAddress(data) {
				let addData = data
				addData.id = this.alData.lottery_record_id
				addData.address = data.address.province + data.address.city + data.address.district + data.detail
				receiveLottery(addData).then(res => {
					this.$util.Tips({
						title: '领取成功'
					});
					this.addressModel = false
				}).catch(err => {
					this.$util.Tips({
						title: err
					});
				})
			},
			getWiningIndex(callback) {
				this.aleartType = 0
				startLottery({
					id: this.id
				}).then(res => {
					this.prize.forEach((item, index) => {
						if (res.data.id === item.id) {
							this.alData = res.data
							this.lottery_draw_param.winingIndex = index;
							callback(this.lottery_draw_param);
						}
					})
				}).catch(err => {
					this.$util.Tips({
						title: err
					});
				})
				// //props修改在小程序和APP端不成功，所以在这里使用回调函数传参，
			},
			// 抽奖完成
			luck_draw_finish(param) {
				this.aleartType = 2
				this.aleartStatus = true
			},

		}
	}
</script>

<style lang="scss" scoped>
	/deep/.timeItem .time .styleAll{
		  padding: 0 4rpx;
		  font-size: 26rpx;
		  color: #fff;
		  background-color: #ff3d3d;
	}
	 
	/deep/.timeItem .time .red{
		color: #ff3d3d;
	}
	 
	.termValidity{
		margin-top: 10rpx;
	} 
	.header {
		color: #fff;
		background-color: #E93323;
		display: flex;
		align-items: center;
		justify-content: center;
		flex-direction: column;
		padding: 80rpx 0;

		.pay-status {
			display: flex;
			align-items: center;

			.iconfont {
				font-size: 74rpx;
				background: rgba(#000, 0.08);
				border-radius: 50%;
				margin-right: 30rpx;
				padding: 9rpx;
			}

			.pay-status-r {
				display: flex;
				flex-direction: column;

				.pay-status-text {
					font-size: 38rpx;
					font-weight: bold;
					padding-bottom: 10rpx;
				}
			}
		}

		.grids /deep/ .grid_wrap .lottery_wrap .lottery_grid li:nth-of-type(9) {
			background: rgba(#fff, 0.2) !important;
		}

		.jump {
			display: flex;
			padding-top: 40rpx;

			.jump-det {
				background: #FFFFFF;
				opacity: 1;
				border-radius: 22px;
				color: #E93323;
				padding: 10rpx 38rpx;
				margin-right: 30rpx;
			}

			.jump-index {
				border: 1px solid #FEFFFF;
				opacity: 1;
				padding: 10rpx 38rpx;
				border-radius: 22px;
			}
		}
	}

	.grids-top {
		display: flex;
		justify-content: center;
		padding: 30rpx 0 0 0;

		image {
			width: 40rpx;
			height: 40rpx;
		}

		.grids-title {
			display: flex;
			justify-content: center;
			font-size: 20px;
			color: #E93323;
			z-index: 999;
			padding: 0 14rpx;
			font-weight: bold;

			.grids-frequency {}
		}
	}

	/deep/ .lottery_grid {
		background-color: #E93323;
		border-radius: 12rpx;
	}

	.grids {
		width: 100%;
		// height: 800rpx;
		display: flex;
		flex-direction: column;
		justify-content: center;
		align-items: center;
		margin-top: 20rpx;
		position: relative;
		padding: 30rpx;

		.grids-bag {
			position: absolute;
			top: 0;
			left: 0;
			width: 750rpx;
			height: 750rpx;
			padding: 20rpx;
			display: flex;
			justify-content: center;
			align-items: center;
		}

		.grids-box {
			width: 700rpx;
			height: 700rpx;
			// z-index: 10000;
			padding: 20rpx;
			background-color: #E74435;
		}

		.winning-tips-list {
			display: flex;
			align-items: center;
			justify-content: center;
			width: 50%;
			font-size: 20rpx;
			line-height: 40rpx;
			height: 40rpx;
			font-weight: 400;
			color: #FFF8F8;
			margin: 30rpx 0;
			z-index: 999;
			background: linear-gradient(90deg, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 0.3) 51%, rgba(255, 255, 255, 0) 100%);

			.iconfont {
				font-size: 20rpx;
				margin-right: 10rpx;
			}
		}
	}
</style>
