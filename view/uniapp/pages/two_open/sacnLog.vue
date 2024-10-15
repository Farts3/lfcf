<template>
	<view>
		<view class="list-box" v-for="(item, index) in list">
			<view class="order-date">
				<text>{{ item.order_id }}</text>
				<text>{{ item.create_time }}</text>
			</view>

			<view class="view">门店名称：<text>{{ item.store_name }}</text></view>
			<view class="view">实付金额：<text>￥{{ item.price }}</text></view>
			<view class="view">门店折扣：<text>{{ item.discount }}%折</text></view>
			<view class="price">支付金额：￥<text>{{ item.actual_price}}</text></view>
		</view>
	</view>
	</view>
</template>

<script>
	import {
		getOfflineLogs
	} from '@/api/two_open.js'
	export default {
		name:'扫码支付记录',
		data() {
			return {
				list: []
			}
		},
		onLoad() {
			getOfflineLogs().then(res => {
				this.list = res.data
			})
		},

		methods: {

		}
	}
</script>

<style lang="scss">
	.list-box {
		width: 700rpx;
		margin: 10rpx auto;
		background-color: #fff;
		border-radius: 20rpx;
		padding: 20upx;
		font-size: 12px;
		color: #666;
		.order-date {
			display: flex;
			flex-flow: row nowrap;
			align-items: center;
			justify-content: space-between;
			margin-bottom: 10upx;
			color: #333;
			text:last-child{
				color: #777;
			}
		}
		.view {
			text {
				color: #333;
			}
		}
		.price {
			text {
				color: red;
				font-size: 28rpx;
			}
		}
	}
</style>