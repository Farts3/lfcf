<template>
	<!-- 图片模方 -->
	<view class="pictureCube skeleton-rect" :class="{pageOn:bgStyle===1}"
		:style="{margin:'0 '+prConfig*2+'rpx',marginTop:slider*2+'rpx',background:bgColor}" v-if="picList.length"
		v-show="!isSortType">
		<view class="advertItem01" v-for="(item,index) in picList" :key="index" v-if="style==0" @click="goDetail(item)">
			<image :src="item.image" mode="widthFix"></image>
		</view>
		<view class="advertItem02 acea-row" v-if="style==1">
			<view class="item" v-for="(item,index) in picList" :key="index" @click="goDetail(item)">
				<image :src="item.image" mode="" :style="'height:'+ imageH +'rpx;'"></image>
			</view>
		</view>
		<view class="advertItem02 advertItem03 acea-row" v-if="style==2">
			<view class="item" v-for="(item,index) in picList" :key="index" @click="goDetail(item)">
				<image :src="item.image" mode="" :style="'height:'+ imageH +'rpx;'"></image>
			</view>
		</view>
		<view class="advertItem04 acea-row" v-if="style==3">
			<view class="item" style="width:40%;padding:10upx;background-color:#f3f3f3;" @click="goDetail(picList[0])">
				<image style="background-color: white;border-radius: 5px;" :src="picList[0].image" mode=""></image>
			</view>
			<view class="item" style="width:30%;background-color:#f3f3f3;">
				<view class="pic" style="padding: 10upx;" @click="goDetail(picList[1])">
					<image style="background-color: white;border-radius: 5px;overflow: hidden;" :src="picList[1].image" mode=""></image>
				</view>
				<view class="pic" style="padding:10upx;border-radius: 5px;overflow: hidden;" @click="goDetail(picList[2])">
					<image style="background-color: white;border-radius: 5px;overflow: hidden;" :src="picList[2].image" mode=""></image>
				</view>
			</view>
			<view class="item" style="width:30%;background-color:#f3f3f3">
				<view class="pic" style="padding:10upx;" @click="toNews()">
					<image style="background-color: white;border-radius: 5px;overflow: hidden;" src="@/static/images/enchiridion.png" mode=""></image>
				</view>
				<view class="pic" style="padding:10upx;">
					<image style="background-color: white;border-radius: 5px;overflow: hidden;" src="../../../static/images/暂无权限.png" mode="">
					</image>
				</view>
			</view>
		</view>
		<view class="advertItem02 advertItem05 acea-row" v-if="style==4">
			<view class="item" v-for="(item,index) in picList" :key="index" @click="goDetail(item)">
				<image :src="item.image" mode="" :style="'height:'+ imageH +'rpx;'"></image>
			</view>
		</view>
		<view class="advertItem02 advertItem06 acea-row" v-if="style==5">
			<view class="item" v-for="(item,index) in picList" :key="index" @click="goDetail(item)">
				<image :src="item.image" mode=""></image>
			</view>
		</view>
	</view>
</template>

<script>
	export default {
		name: 'pictureCube',
		props: {
			dataConfig: {
				type: Object,
				default: () => {}
			},
			isSortType: {
				type: String | Number,
				default: 0
			}
		},
		data() {
			return {
				picList: this.dataConfig.picStyle.picList,
				style: this.dataConfig.tabConfig.tabVal,
				bgStyle: this.dataConfig.bgStyle.type,
				prConfig: this.dataConfig.prConfig.val,
				slider: this.dataConfig.mbConfig.val,
				bgColor: this.dataConfig.bgColor.color[0].item,
				widthC: '',
				imageH: ''
			};
		},
		mounted() {
			if (this.picList.length) {
				let that = this;
				this.$nextTick((e) => {
					if (this.style == 1) {
						this.widthC = 375
					} else if (this.style == 2) {
						this.widthC = 250
					} else if (this.style == 4) {
						this.widthC = 188
					}
					uni.getImageInfo({
						src: that.setDomain(that.picList[0].image),
						success: (res) => {
							if (res && res.height > 0) {
								let height = res.height * ((that.widthC - that.prConfig * 2) / res
									.width)
								that.$set(that, 'imageH', height);
							} else {
								that.$set(that, 'imageH', (that.widthC - that.prConfig * 2) * 2);
							}
						},
						fail: function(error) {
							that.$set(that, 'imageH', (that.widthC - that.prConfig * 2) * 2);
						}
					})
				})
			}
		},
		created() {},
		methods: {
			// 跳转文章详情页【客户使用手册】
			toNews() {
				uni.navigateTo({
					url:'/pages/extension/news_list/index'
				})
			},
			//替换安全域名
			setDomain: function(url) {
				url = url ? url.toString() : '';
				//本地调试打开,生产请注销
				if (url.indexOf("https://") > -1) return url;
				else return url.replace('http://', 'https://');
			},
			goDetail(url) {
				let urls = url.link;
				this.$util.JumpPath(urls);
			}
		}
	}
</script>

<style lang="scss">
	.pageOn {
		border-radius: 24rpx !important;

		.advertItem01 {
			image {
				border-radius: 20rpx;
			}
		}

		.advertItem02 {
			.item {
				&:nth-child(1) {
					image {
						border-radius: 20rpx 0 0 20rpx
					}
				}

				&:nth-child(2) {
					image {
						border-radius: 0 20rpx 20rpx 0
					}
				}
			}
		}

		.advertItem03 {
			.item {
				&:nth-child(1) {
					image {
						border-radius: 20rpx 0 0 20rpx
					}
				}

				&:nth-child(2) {
					image {
						border-radius: 0
					}
				}

				&:nth-child(3) {
					image {
						border-radius: 0 20rpx 20rpx 0
					}
				}
			}
		}

		.advertItem04 {
			.item {
				&:nth-child(1) {
					image {
						border-radius: 20rpx 0 0 20rpx
					}
				}

				&:nth-child(2) {
					.pic {
						&:nth-child(1) {
							image {
								border-radius: 0 20rpx 0 0
							}
						}

						&:nth-child(2) {
							image {
								border-radius: 0 0 20rpx 0
							}
						}
					}
				}
			}
		}

		.advertItem05 {
			.item {
				&:nth-child(1) {
					image {
						border-radius: 20rpx 0 0 20rpx
					}
				}

				&:nth-child(2) {
					image {
						border-radius: 0
					}
				}

				&:nth-child(4) {
					image {
						border-radius: 0 20rpx 20rpx 0
					}
				}
			}
		}

		.advertItem06 {
			.item {
				&:nth-child(1) {
					image {
						border-radius: 20rpx 0 0 0
					}
				}

				&:nth-child(2) {
					image {
						border-radius: 0 20rpx 0 0
					}
				}

				&:nth-child(3) {
					image {
						border-radius: 0 0 0 20rpx
					}
				}

				&:nth-child(4) {
					image {
						border-radius: 0 0 20rpx 0
					}
				}
			}
		}
	}

	.pictureCube {
		background-color: #fff;

		.advertItem01 {
			width: 100%;
			height: auto;

			image {
				width: 100%;
				height: 100%;
				display: block;
			}
		}

		.advertItem02 {
			// /deep/uni-image>img{
			// 	position: unset;
			// }
			width: 100%;

			.item {
				width: 40%;
				height: auto;

				image {
					width: 100%;
					height: 100%;
					display: block;
				}
			}
		}

		.advertItem03 {
			.item {
				width: 33.3333%;
			}
		}

		.advertItem04 {
			width: 100%;

			.item {
				// width: 30%;
				height: 200px;

				.pic {
					width: 100%;
					height: 100px;
				}

				image {
					width: 100%;
					height: 100%;
					display: block;
				}
			}
		}

		.advertItem05 {
			.item {
				width: 25%;
			}
		}

		.advertItem06 {
			.item {
				width: 50%;
				height: 100px;
			}
		}
	}
</style>