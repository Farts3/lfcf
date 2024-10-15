<template>
	<view>
		<view class="big-box">
			<view class="samll-box" v-for="(item, index) in storeCategroyList" @click="toUrl(item)">
				<image class="image" :src="item.pic" mode=""></image>
				<text>{{ item.name }}</text>
			</view>

		</view>
		
		<pageFooter></pageFooter>
	</view>
</template>

<script>
	import {
		getStoreCategroy
	} from '@/api/new_store.js'
	
	import pageFooter from '@/components/pageFooter/index.vue';
	export default {
		components:{
			pageFooter
		},
		data() {
			return {
				storeCategroyList: []
			}
		},
		onLoad() {
			getStoreCategroy().then(res => {
				this.storeCategroyList = res.data
			})
		},
		methods: {
			toUrl(item) {
				// 如果有二级分类，就跳转到二级分类，没有直接跳转到门列表里
				if(item.children.length > 0) {
					uni.navigateTo({
						url: '/pages/two_open/storeTwoCate?id=' + item.id + '&title=' + item.name
					})
				}else {
					uni.navigateTo({
						url: '/pages/two_open/storeList?cate_id=' + item.id
					})
				}
				
			}
		}
	}
</script>

<style scoped lang="scss">
	
	.big-box {
		display: grid;
		grid-template-columns: 25% 25% 25% 25%; 
		width: 700rpx;
		margin: 0 auto;
		background-color: #FFF;
		// padding: 20rpx;
		margin-top: 15px;
		color: #333;
		font-size: 24rpx;
		.samll-box {
			border: 1px solid #eee;
			/* 边框线条重复显示 */
			margin: 0 0 -1px -1px;
			padding: 10px 0;
			flex: 165rpx 0;
			display: flex;
			flex-flow: column nowrap;
			text-align: center;
			align-items: center;
			&:last-child {
				// margin:  0;
			}
			.image {
				width: 80rpx;
				height: 80rpx;
				border-radius: 40rpx;
				overflow: hidden;
			}
		}


	}
</style>