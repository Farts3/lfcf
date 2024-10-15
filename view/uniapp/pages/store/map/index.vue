<template>
	<view :style="colorStyle">
		<view class="header_search" v-if="!header">
			<view class="title">
				搜索门店
			</view>
			<view class="box acea-row row-center-wrapper">
				<input type="text" placeholder="搜索门店" class="input" focus @input="setValue"
					v-model="content.keyword"></input>
				<text class="iconfont icon-sousuo6" />
			</view>
		</view>
		<transition-group name="lyric" v-if="header">
			<map style="width: 100%; height: 668rpx;" :latitude="latitudeStore" :longitude="longitudeStore"
				:markers="covers" v-if="map" :key="1" />
		</transition-group>

		<!-- 附近门店 -->
		<view class="nearby" v-if="header">
			<view class="store acea-row row-middle">
				<view class="btn" :class="{'activeColor':active==1}" @click="btnFn(1)">
					附近门店
				</view>
				<view class="btn" :class="{'activeColor':active==2}" @click="btnFn(2)">
					常用门店
				</view>
				<view class="btn" :class="{'activeColor':active==3}" @tap="openLevel">
					门店分类
				</view>
				<view class="btn put" @click="putMap()">
					<text class="iconfont icon-xiangshang" v-if="map==1" />
					<text class="iconfont icon-xiangxia" v-else />
					{{map==1?"收起地图":"展开地图"}}
				</view>
				<view class="search" @click="searchMap()">
					<text class="iconfont icon-sousuo6" />
				</view>
			</view>
		</view>
		<!-- 门店列表 -->
		<view class="content" v-if="info.length>0">
			<view class="list acea-row row-between-wrapper" v-for="(item,index) in info" :key="index"
				@click="activeFn(item)" :class="{active:sortIndex==item.id}">
				<text class="iconfont icon-xuanzhong6" v-if="sortIndex==item.id"></text>
				<view class="left">
					<view class="name line2">
						{{item.name}}
					</view>
					<view class="adress acea-row">
						<text class="iconfont icon-dingwei2" />
						<view class="con">{{item.detailed_address}}</view>
					</view>
					<view class="adress">
						<text class="iconfont icon-yingyeshijian2" />
						营业时间：{{item.day_time}}
					</view>
				</view>
				<view class="right">
					<view class="gostore">
						进店选购
					</view>
					<view class="distance">
						距离{{item.range}}km
					</view>
					<view class="telephone">
						<view class="phone" @click.stop="callPhone(item)">
							<text class="iconfont icon-dianhua"></text>
						</view>
						<view class="phone" @click.stop="showMaoLocation(item)">
							<text class="iconfont icon-dingwei2"></text>
						</view>
					</view>
				</view>
			</view>
		</view>
		<!-- 缺省页 -->
		<view class="default" v-if="info.length==0">
			<image :src="imgHost+'/statics/images/store-default.png'" mode="" class="img"></image>
			<view class="text">
				暂无门店信息，再去试试其他地址吧~
			</view>
		</view>
		<!-- 门店分类弹窗 -->
		<level-linkage ref="levelLinkage" :pickerValueDefault="pickerValueDefault" :allData="storeCategroyList"
			@onConfirm="onConfirm" themeColor='#007AFF'></level-linkage>


	</view>
</template>

<script>
	import {
		HTTP_REQUEST_URL
	} from '@/config/app';
	import colors from "@/mixins/color";
	import {
		getList,
		getStoreCategroy
	} from '@/api/new_store.js'

	import levelLinkage from "@/components/three-level-linkage/linkage.nvue"
	export default {
		mixins: [colors],
		components: {
			levelLinkage
		},
		data() {
			return {
				imgHost: HTTP_REQUEST_URL,
				sortIndex: 0,
				active: 1,
				content: {
					// 自己的位置
					latitude: uni.getStorageSync('user_latitude'),
					longitude: uni.getStorageSync('user_longitude'),
					store_type: 1,
					keyword: "",
					// limit: 10,
					page: 1
				},
				info: [],
				map: 1,
				header: 1,
				comeType: 0,
				// 门店位置
				latitudeStore: 0,
				longitudeStore: 0,
				covers: [],
				pickerValueDefault: [0, 0],
				storeCategroyList: []
			};
		},
		onLoad(options) {
			this.comeType = options.type
			this.sortIndex = options.storeId
			this.isCollage = options.isCollage
			try {
				if (this.content.latitude && this.content.longitude && options.storeFrom == 1) {
					this.getlist()
				} else {
					this.selfLocation();
				}
			} catch (e) {
				this.getlist()
			}

			this.getCategroy()
		},
		methods: {
			// 门店分类 Start
			openLevel() {
				this.$refs.levelLinkage.open();
				this.active = 3
			},

			onConfirm(e) {
				// e 确认后选中的数据
				console.log(e)
				let params = {store_type: 1}
				if(e.secondPick.id) {
					params.cate_id = e.secondPick.id
				}else {
					params.cate_id = e.firstPick.id
				}
				this.content = params
				console.log(params)
				this.getlist()
			},
			getCategroy(data) {
				console.log('getCategroy',data)
				getStoreCategroy(data).then(res => {
					this.storeCategroyList = res.data
				})
			},
			// 门店分类 End
			selfLocation() {
				let self = this
				// #ifdef H5
				if (self.$wechat.isWeixin()) {
					self.$wechat.location().then(res => {
						this.content.latitude = res.latitude;
						this.content.longitude = res.longitude;
						uni.setStorageSync('user_latitude', res.latitude);
						uni.setStorageSync('user_longitude', res.longitude);
						self.getlist();
					}).catch(err => {
						self.getlist();
					})
				} else {
					// #endif	
					uni.getLocation({
						type: 'wgs84',
						success: (res) => {
							try {
								this.content.latitude = res.latitude;
								this.content.longitude = res.longitude;
								uni.setStorageSync('user_latitude', res.latitude);
								uni.setStorageSync('user_longitude', res.longitude);
							} catch {}
							self.getlist();
						},
						complete: function() {
							self.getlist();
						}
					});
					// #ifdef H5	
				}
				// #endif
			},
			// 门店列表
			getlist() {
				getList(this.content).then(res => {
					this.info = res.data
					res.data.forEach(item => {
						if (this.sortIndex == item.id) {
							this.latitudeStore = item.latitude;
							this.longitudeStore = item.longitude;
							this.covers.push({
								title: item.name,
								latitude: item.latitude,
								longitude: item.longitude,
								iconPath: item.image,
								width: 30,
								height: 30
							})
						}
					})
				})
			},
			// 输入关键字搜索门店
			setValue(e) {
				this.getlist()

			},
			// 点击高亮
			activeFn(row) {
				this.sortIndex = row.id
				if (this.comeType) {
					if (this.isCollage == 1) {
						uni.$emit('activeFn', row);
						uni.navigateBack();
						return;
					}
					uni.reLaunch({
						url: `/pages/store_cate/store_cate?mapFrom=1&id=` + row.id
					});
				}
			},
			// 搜索门店
			searchMap() {
				this.header = 0
			},
			// 收起地图
			putMap() {
				if (this.map == 1) {
					this.map = 0
				} else if (this.map == 0) {
					this.map = 1
				}

			},
			// 打电话
			callPhone(row) {
				uni.makePhoneCall({
					phoneNumber: row.phone //仅为示例
				});
			},
			btnFn(num) {
				switch (num) {
					case 1:
						this.active = 1
						this.content.store_type = 1
						break;
					case 2:
						this.active = 2
						this.content.store_type = 2
						break;

				}
				this.getlist()
			},
			showMaoLocation(e) {
				let self = this;
				// #ifdef H5
				if (self.$wechat.isWeixin()) {
					self.$wechat.seeLocation({
						latitude: Number(e.latitude),
						longitude: Number(e.longitude),
						name: e.name,
						scale: 13,
						address: `${e.address}-${e.detailed_address}`,
					}).then(res => {})
				} else {
					// #endif	
					uni.openLocation({
						latitude: Number(e.latitude),
						longitude: Number(e.longitude),
						name: e.name,
						address: `${e.address}-${e.detailed_address}`,
						success: function() {
							Number
						}
					});
					// #ifdef H5	
				}
				// #endif
			},
		}
	}
</script>

<style lang="scss">
	.active {
		position: relative;
		border: 1px solid var(--view-theme) !important;

		.icon-xuanzhong6 {
			font-size: 46rpx;
			position: absolute;
			bottom: -8rpx;
			right: -6rpx;
			color: var(--view-theme);
		}
	}

	.activeColor {
		background-color: var(--view-theme) !important;
		color: #fff !important;
	}

	.nearby {
		width: 100%;
		height: 132rpx;
		background-color: #fff;
		border-radius: 40rpx 40rpx 0rpx 0rpx;
		box-shadow: 0px -2px 22px 0px rgba(0, 0, 0, 0.0400);

		.store {
			height: 132rpx;
			padding: 0 10rpx;

			.btn {
				margin-right: 16rpx;
				width: 140rpx;
				height: 56rpx;
				background: #F5F5F5;
				border-radius: 29rpx;
				text-align: center;
				line-height: 56rpx;
				font-size: 26rpx;
				font-weight: 400;
				color: #999999;
			}

			.put {
				color: #666666 !important;
				margin-left: 20rpx;
				background-color: #fff !important;

				.icon-xiangshang {
					margin-right: 10rpx;
					color: #666666;
					font-size: 24rpx;
				}

				.icon-xiangxia {
					margin-right: 10rpx;
					color: #666666;
					font-size: 24rpx;
				}

			}

			.search {
				position: absolute;
				right: 30rpx;
				text-align: center;
				line-height: 56rpx;
				width: 56rpx;
				height: 56rpx;
				background: #F5F5F5;
				border-radius: 50%;
			}
		}
	}

	.content {

		width: 100%;
		height: 100%;
		padding: 0 30rpx;

		.list {
			display: flex;
			margin-top: 20rpx;
			width: 100%;
			background: #FFFFFF;
			border-radius: 12rpx;
			padding: 28rpx 30rpx;
			border: 1px solid #fff;
			overflow: hidden;

			.left {
				width: 450rpx;
				border-right: 1px solid #eee;
				padding-right: 20rpx;

				.name {
					font-size: 28rpx;
					font-weight: 500;
					color: #333333;
				}

				.adress {
					width: 400rpx;
					margin-top: 18rpx;
					word-wrap: break-word;
					font-size: 22rpx;
					font-weight: 400;
					color: #888888;

					.con {
						width: 360rpx;
					}

					.icon-dingwei2 {
						margin-right: 8rpx;
						font-size: 18rpx;
						color: #ccc;
						margin-top: 6rpx;
					}

					.icon-yingyeshijian2 {
						margin-right: 8rpx;
						font-size: 18rpx;
						color: #ccc;
					}
				}
			}

			.right {

				.gostore {
					font-size: 22rpx;
					font-weight: 400;
					color: var(--view-theme);
				}

				.distance {
					margin-top: 14rpx;
					font-size: 20rpx;
					font-weight: 400;
					color: #999999;
				}

				.telephone {
					margin-top: 14rpx;
					display: flex;

					.phone {
						position: relative;
						margin-right: 24rpx;
						width: 40rpx;
						height: 40rpx;
						background: var(--view-minorColorT);
						border-radius: 50%;
						text-align: center;
						line-height: 40rpx;
						z-index: 10;

						.icon-dingwei2 {
							font-size: 25rpx;
							color: var(--view-theme) !important;
						}

						.icon-dianhua {
							font-size: 25rpx;
							color: var(--view-theme) !important;
						}
					}

				}
			}
		}
	}

	.lyric-enter,
	.lyric-leave-to {
		opacity: 0;
		transform: translateY(60px);
	}

	.lyric-enter-to,
	.lyric-leave {
		opacity: 1;

	}

	.lyric-enter-active,
	.lyric-leave-active {
		transition: all 0.3s;
	}

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

	.default {
		display: flex;
		flex-direction: column;
		align-items: center;

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

	.store-popup {
		position: fixed;
		left: 0;
		top: 0;
		background-color: rgba(0, 0, 0, 0.4);
		width: 100%;
		height: 100vh;
	}
</style>