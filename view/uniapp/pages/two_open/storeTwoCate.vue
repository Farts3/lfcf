<template>
	<view>
		<view class="title">
			{{ title }}
		</view>
		<view class="big-box">
			<view class="samll-box" v-for="(item, index) in storeCategroyList" @click="toUrl(item)">
				<image class="image" :src="item.pic" mode=""></image>
				<text>{{ item.name }}</text>
			</view>

		</view>
	</view>
</template>

<script>
	import {
		getTwoCategory
	} from '@/api/two_open.js'
	export default {
		data() {
			return {
				storeCategroyList: [],
				title:''
			}
		},
		onLoad(e) {
			let cate_id = e.id;
			this.title = e.title;
			uni.setNavigationBarTitle({
				title: e.title
			})
			getTwoCategory({
				cate_id
			}).then(res => {
				this.storeCategroyList = res.data
			})
		},

		methods: {
			toUrl(item) {
				uni.navigateTo({
					url: '/pages/two_open/storeList?cate_id=' + item.id
				})
			}
		}
	}
</script>

<style scoped lang="scss">
	.title {
		width: 690rpx;
		margin: 0 auto;
		background-color: #FFF;
		margin-top: 15px;
		margin-bottom: 10rpx;
		font-weight: bold;
		padding: 20rpx;
		color: #444;
		
	}
	.big-box {
		display: flex;
		flex-flow: row wrap;
		width: 690rpx;
		margin: 0 auto;
		background-color: #FFF;
		padding: 20rpx;
		
		color: #333;
		font-size: 24rpx;
		.samll-box {
			flex: 162rpx 0;
			margin-bottom: 20rpx;
			display: flex;
			flex-flow: column nowrap;
			text-align: center;
			align-items: center;

			.image {
				width: 80rpx;
				height: 80rpx;
				border-radius: 40rpx;
				overflow: hidden;
			}
		}
	}
</style>