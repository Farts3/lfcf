<template>
	<view style="margin-top: 30rpx;">
		<view class="zbox">
			<view class="content">
				<text>门店名称：</text>
				<view>
					{{ payPost.store_name }}
				</view>
			</view>
		</view>
		<view class="zbox">
			<view class="content">
				<text>支付金额：</text>
				<view>
					<input type="text" @input="input" placeholder="请输入支付金额" placeholder-class="placeholder">
				</view>

			</view>
			<view class="content">
				<text>折扣：</text>
				<view>{{ discount == 0 ? '无折扣': discount + ' 折'}}</view>
			</view>
		</view>
		<view class="zbox">
			<view class="content">
				<text>账户余额：</text>
				<view style="color: red;">
					￥{{ payPost.number || '0.00'}}
				</view>

			</view>
			<view class="content">
				<text>实付金额：</text>
				<view style="color: red;">
					￥{{ payPost.actual_price || '0.00'}}</view>
			</view>
			<view class="content">
				<text>支付密码：</text>
				<view>
					<input type="password" v-model="payPost.password"  placeholder="请输入支付密码" placeholder-class="placeholder">
				</view>
			
			</view>
		</view>
		<view class="button acea-row row-center-wrapper" style="margin-top: 40px;" @click="goPay">确定支付</view>

	</view>
</template>

<script>
	import {
		getStoreData
	} from '@/api/store.js'

	import {
		getUserInfo
	} from '@/api/user.js'

	import {
		postOrderComputed
	} from '@/api/order.js';

	import {
		createOrder
	} from '@/api/two_open.js'


	export default {
		name:'扫码余额支付',
		components: {


		},
		data() {
			return {
				discount: 0, //折扣
				pay_close: false,
				userInfo: {},
				price: '',
				//支付方式
				payPost: {
					pay_type: 'yue',
					price: '',
					actual_price: '',
					number: '',
					password:'',
					store_name:'',
					device_id:0
				}, // 支付参数
			}
		},

		onLoad(e) {
			let store_id = e.id
			this.payPost.device_id = e.device_id
			if(!store_id) {
				setTimeout(() => {
					uni.switchTab({
						url:'/pages/user/index'
					})
				}, 1500)
				return this.$util.Tips({
					title: '未获取到门店ID,请重新扫码'
				})
				
			}
			console.log('门店id', store_id)
			
			// 获取门店信息
			getStoreData({
				store_id
			}).then(res => {
				this.discount = res.data.discount_price / 10
				this.payPost.discount = res.data.discount_price 
				this.payPost.store_id = res.data.id
				this.payPost.store_name = res.data.name
			})

			getUserInfo().then(res => {
				this.userInfo = res.data
				this.payPost.user_real_name = res.data.real_name
				this.payPost.number = res.data.now_money
			})

		},

		methods: {
			/**
			 * 确定支付
			 *
			 */
			goPay() {
				if (this.payPost.price < 1) {
					return this.$util.Tips({
						title: '支付金额不小于 1！'
					})
				}
				if (parseFloat(this.payPost.number) < parseFloat(this.payPost.actual_price)) return this.$util.Tips({
					title: '余额不足！'
				});
				
				if(this.payPost.password.length < 5) {
					return this.$util.Tips({
						title: '请输入6位数支付密码！'
					});
				}
				uni.showLoading({
					title: '支付中'
				});

				createOrder(this.payPost).then(res => {
					uni.hideLoading();
					this.$util.Tips({
						title: res.msg
					})
					if (res.status == 200) {
						setTimeout(() => {
							uni.redirectTo({
								url: '/pages/two_open/sacnLog'
							})
						}, 1500)
					}

				}).catch(err => {
					uni.hideLoading();
					return this.$util.Tips({
						title: err
					})
				})
			},
			input(e) {
				if(this.payPost.discount == 0) {
					this.payPost.actual_price = e.detail.value
					return;
				}
				this.payPost.actual_price = (e.detail.value * this.payPost.discount / 100).toFixed(2);
				this.payPost.price = e.detail.value
			}
		}
	}
</script>

<style lang="scss" scoped>
	.button {
		background: #1db0fc;
		width: 690rpx;
		height: 90rpx;
		border-radius: 45rpx;
		color: #FFFFFF;
		margin: 20rpx auto 0 auto;
	}

	.zbox {
		width: 650rpx;
		margin: 0 auto;
		padding: 20rpx;
		background-color: #FFF;
		border-radius: 20rpx;
		color: #333;
		margin-bottom: 20rpx;

		.content {
			display: flex;
			flex-flow: row nowrap;
			align-items: center;
			border-bottom: 1px solid #EEE;
			margin-bottom: 20rpx;
			padding-bottom: 20rpx;

			&:last-child {
				border-bottom: none;
				margin-bottom: 0rpx;
				padding-bottom: 0rpx;
			}

			text {
				width: 150rpx;
			}

			view {
				flex: 1;
			}

			input {
				font-size: 28rpx;
			}
		}
	}

	.placeholder {
		font-size: 24rpx;
	}
</style>