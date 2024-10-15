<template>
	<view>
		<view class="header_search" v-if="!header">
		<!-- 	<view class="title">
				搜索门店
			</view> -->
			<view class="box acea-row row-center-wrapper">
				<input type="text" placeholder="搜索门店" class="input" focus @input="setValue"
					v-model="content.keyword"></input>
				<text class="iconfont icon-sousuo6" />
			</view>

			<view class="store-list-box">
				<view class="lists-box" v-for="(item, index) in storeList" @click="toUrl(item)">
					<view class="">
						<image :src="item.image" mode=""></image>
					</view>
					<view class="right-box">
						<view class="store-name">{{ item.name }}</view>
						<view class="">营业时间：{{ item.day_time }}</view>
						<view class="">电话：{{ item.phone }}</view>
						<view class="">地址：{{ item.detailed_address }}</view>
					</view>
				</view>

			</view>

			<!-- 数据为空时 -->
			<view class="default" v-if="storeList.length == 0">
				<image :src="imgHost+'/statics/images/store-default.png'" mode="" class="img"></image>
				<view class="text">
					暂无门店信息，再去试试其他分类吧~
				</view>
			</view>

		</view>
	</view>
</template>

<script>
	import {
		getList
	} from '@/api/new_store.js';
	import {
		HTTP_REQUEST_URL
	} from '@/config/app';
	export default {
		data() {
			return {
				imgHost: HTTP_REQUEST_URL,
				storeList: [],
				header: 0,
				content: {
					// 自己的位置
					store_type: 1,
					keyword: "",
					// limit: 10,
					page: 1
				},
			}
		},

		onLoad(e) {
			this.content.cate_id = e.cate_id
			this.getStoreList()
		},

		methods: {
			// 输入关键字搜索门店
			setValue(e) {
				this.getStoreList()
			},
			// 展示所有门店
			getStoreList() {
				getList(this.content).then(res => {
					this.storeList = res.data
				})
			},
			// 跳转到对应的门店
			toUrl(item) {
				console.log(item)
				uni.navigateTo({
					// url:'/pages/store_cate/store_cate?mapFrom=1&id='+ item.id
					url:'/pages/two_open/shoplist?mapFrom=1&id='+ item.id
				})
				
				// uni.reLaunch({
				// 	url:'/pages/store_cate/store_cate?mapFrom=1&id='+ item.id
				// })
			}
		}
	}
</script>

<style lang="scss" scoped>
	.header_search {
		.title {
			width: 100%;
			height: 86rpx;
			text-align: center;
			line-height: 86rpx;
			font-size: 30rpx;
			font-weight: 600;
			color: #282828;
			background-color: #fff;
		}

		.box {
			width: 100%;
			height: 92rpx;
			background: #fff;
			position: relative;

			.input {
				// margin-left: 30rpx;
				padding-right: 40rpx;
				padding-left: 80rpx;
				width: 690rpx;
				height: 60rpx;
				background: #F5F5F5;
				border-radius: 30rpx;
				font-size: 26rpx;
				font-weight: 400;
				box-sizing: border-box;
				// color: #CCCCCC;
			}

			.iconfont {
				content: "\e79b";
				position: absolute;
				left: 60rpx;
				top: 34rpx;
				font-size: 30rpx;
				color: #CCCCCC;
			}
		}
	}

	.store-list-box {
		width: 700rpx;
		margin: 0 auto;
		margin-top: 20upx;

		.lists-box {
			background-color: #FFF;
			display: flex;
			align-items: center;
			color: #666;
			font-size: 24rpx;
			margin-bottom: 10rpx;
			padding: 20upx;
			border-radius: 10rpx;
			overflow: hidden;

			image {
				width: 160rpx;
				height: 160rpx;
			}

			.right-box {
				margin-left: 20rpx;

				.store-name {
					font-size: 28rpx;
					font-weight: bold;
					color: #333;
				}
			}
		}
	}

	.default {
		display: flex;
		flex-direction: column;
		align-items: center;
		padding-top: 300rpx;
		.img {
			width: 414rpx;
			height: 256rpx;
		}
	
		.text {
			margin-top: 54rpx;
			font-size: 26rpx;
			font-weight: 400;
			color: #999999;
		}
	}
</style>