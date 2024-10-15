<template>
	<!-- 金刚区 -->
	<view v-show="!isSortType && menus.length" :class="bgStyle?'borderRadius15':''"
		:style="{background:bgColor,margin:'0 '+prConfig*2+'rpx',marginTop:mbConfig*2+'rpx'}">
		<view v-if="isMany">
			<view class="swiper">
				<swiper :interval="interval" :duration="duration" :style="'height:'+(navHigh+13)+'px;'"
					@change='bannerfun'>
					<block>
						<swiper-item v-for="(item,indexw) in menuList" :key="indexw">
							<view class="nav acea-row" :id="'nav' + indexw">
								<view :style="'color:' + titleColor" class="item"
									:class="number===1?'four':number===2?'five':''" v-for="(itemn,indexn) in item.list"
									:key="indexn" @click="menusTap(itemn.info[1].value)">
									<view class="pictrue skeleton-radius" :class="menuStyle?'':'on'">
										<image :src="itemn.img" mode="aspectFill"></image>
									</view>
									<view class="menu-txt">{{ itemn.info[0].value }}</view>
								</view>
							</view>
						</swiper-item>
					</block>
				</swiper>
			</view>
			<view class="dot acea-row row-center-wrapper" v-if="docConfig<2 && menuList.length>1">
				<view class="dot-item" :class="{ 'line_dot-item': docConfig === 0,'': docConfig === 1}"
					:style="active==index?'background:'+ dotColor:''" v-for="(item,index) in menuList"></view>
			</view>
		</view>
		<view class="nav oneNav" v-else>
			<!-- <scroll-view scroll-x="true" style="white-space: nowrap; display: flex" show-scrollbar="false"> -->
				<block v-for="(item, index) in menus" :key="index">
					<view class="item" v-if="item.info[0].value=='付款码'"
						:style="'color:' + titleColor+';width:'+(100/menus.length)+'%;position:relative'" @click="tapQrCode">
						<view class="pictrue skeleton-radius" :class="menuStyle?'':'on'" style="position:absolute;top:-120rpx;left:60rpx">
							
							<!-- #ifdef H5 -->
							<image style="width: 160rpx;height: 160rpx;margin: -16px -10px;border:16rpx solid #FFF;border-radius: 90rpx;" :src="item.img"
								mode="aspectFill"></image>
							<!-- #endif -->	
							
							<!-- #ifdef MP -->
							<image style="width: 130rpx;height: 130rpx;margin: -16px -10px;border:16rpx solid #FFF;border-radius: 90rpx;" :src="item.img"
								mode="aspectFill"></image>
							<!-- #endif -->
								
						</view>
						<view class="menu-txt">{{ item.info[0].value }}</view>
					</view>
					
					<view class="item" v-else :style="'color:' + titleColor+';width:'+(100/menus.length)+'%'"
						@click="menusTap(item.info[1].value)">
						<view class="pictrue skeleton-radius" :class="menuStyle?'':'on'">
							<image :src="item.img" mode="aspectFill"></image>
						</view>
						<view class="menu-txt">{{ item.info[0].value }}</view>
					</view>
				</block>
			<!-- </scroll-view> -->
		</view>
		<view class="codePopup" :style="colorStyle" v-show="isCode">
			<view class="header acea-row row-between-wrapper">
				<view class="title" :class="{'on': codeIndex == index,'onLeft':codeIndex == 1}"
					v-for="(item, index) in codeList" :key="index" @click="tapCode(index)">{{item.name}}</view>
			</view>
			<view>
				<view class="acea-row row-center-wrapper">
					<w-barcode :options="config.bar"></w-barcode>
				</view>
				<view class="acea-row row-center-wrapper" style="margin-top: 35rpx;">
					<w-qrcode :options="config.qrc" @generate="hello"></w-qrcode>
				</view>
				<view class="codeNum">{{config.bar.code}}</view>
				<view class="tip">如遇到扫码失败请将屏幕调至最亮重新扫码</view>
			</view>
			<view class="iconfont icon-guanbi2" @click="closeCode"></view>
		</view>
		<view class="mark" v-if="isCode"></view>
	</view>
</template>

<script>
	export default {
		// computed: mapGetters(['isLogin']),
		name: 'menus',
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
				interval: 3000,
				duration: 500,
				menus: this.dataConfig.menuConfig.list || [],
				titleColor: this.dataConfig.titleColor.color[0].item,
				mbConfig: this.dataConfig.mbConfig.val,
				rowsNum: this.dataConfig.rowsNum.type,
				number: this.dataConfig.number.type,
				isMany: this.dataConfig.tabConfig.tabVal,
				menuStyle: this.dataConfig.menuStyle.type,
				docConfig: this.dataConfig.pointerStyle.type,
				dotColor: this.dataConfig.pointerColor.color[0].item,
				bgColor: this.dataConfig.bgColor.color[0].item,
				bgStyle: this.dataConfig.bgStyle.type,
				prConfig: this.dataConfig.prConfig.val,
				navHigh: 170,
				menuList: [],
				active: 0,
				config: {
					bar: {
						code: '',
						color: ['#000'],
						bgColor: '#FFFFFF', // 背景色
						width: 480, // 宽度
						height: 110 // 高度
					},
					qrc: {
						code: '',
						size: 380, // 二维码大小
						level: 3, //等级 0～4
						bgColor: '#FFFFFF', //二维码背景色 默认白色
						border: {
							color: ['#eee', '#eee'], //边框颜色支持渐变色
							lineWidth: 3, //边框宽度
						},
						// img: '/static/logo.png', //图片
						// iconSize: 40, //二维码图标的大小
						color: ['#333', '#333'], //边框颜色支持渐变色
					}
				},
				codeList: [{
					name: '会员码'
				}, {
					name: '付款码'
				}],
				codeIndex: 0,
				isCode: false,
				diyInfo: {}
			};
		},
		created() {
			if (this.isLogin) {
				this.getDiyUserInfo();
			}
		},
		mounted() {
			if (this.rowsNum === 0) {
				if (this.number === 0) {
					this.pageNum(6)
				} else if (this.number === 1) {
					this.pageNum(8)
				} else {
					this.pageNum(10)
				}
			} else if (this.rowsNum === 1) {
				if (this.number === 0) {
					this.pageNum(9)
				} else if (this.number === 1) {
					this.pageNum(12)
				} else {
					this.pageNum(15)
				}
			} else {
				if (this.number === 0) {
					this.pageNum(12)
				} else if (this.number === 1) {
					this.pageNum(16)
				} else {
					this.pageNum(20)
				}
			}
			this.$nextTick(() => {
				if (this.menuList.length && this.isMany) {
					let that = this
					// #ifdef H5
					that.menuHeight()
					// #endif
					// #ifndef H5
					setTimeout(() => {
						that.menuHeight()
					}, 150)
					// #endif
				}
			})
		},
		methods: {
			tapQrCode() {
				uni.$emit('opencode', 1);
			},
			bannerfun(e) {
				this.active = e.detail.current;
			},
			menuHeight() {
				let that = this;
				const query = uni.createSelectorQuery().in(this);
				query.select('#nav0').boundingClientRect(data => {
					that.navHigh = data.height;
				}).exec();
			},
			pageNum(num) {
				let count = Math.ceil(this.menus.length / num);
				let goodArray = new Array();
				for (let i = 0; i < count; i++) {
					let list = this.menus.slice(i * num, i * num + num);
					if (list.length)
						goodArray.push({
							list: list
						});
				}
				this.$set(this, 'menuList', goodArray);
			},
			menusTap(url) {
				this.$util.JumpPath(url);
			}
		}
	};
</script>

<style lang="scss">
	.dot {
		width: 100%;
		padding-bottom: 20rpx;

		.instruct {
			width: 50rpx;
			height: 36rpx;
			line-height: 36rpx;
			background-color: rgba(0, 0, 0, 0.8);
			color: #fff;
			border-radius: 16rpx;
			font-size: 24rpx;
			text-align: center;
		}

		.dot-item {
			width: 10rpx;
			height: 10rpx;
			background: rgba(0, 0, 0, .4);
			border-radius: 50%;
			margin: 0 4px;

			&.line_dot-item {
				width: 20rpx;
				height: 5rpx;
				border-radius: 3rpx;
			}
		}
	}

	.nav {
		&.oneNav {
			padding-bottom: 25rpx;
		}

		.item {
			margin-top: 30rpx;
			width: 150rpx;
			text-align: center;
			font-size: 24rpx;
			display: inline-block;

			.pictrue {
				width: 90rpx;
				height: 90rpx;
				margin: 0 auto;

				image {
					width: 100%;
					height: 100%;
					border-radius: 50%;
				}

				&.on {
					image {
						border-radius: 0;
					}
				}
			}

			.menu-txt {
				margin-top: 15rpx;
			}
		}
	}

	.swiper {
		z-index: 20;
		position: relative;
		overflow: hidden;

		.nav {
			.item {
				width: 33.3333%;

				&.four {
					width: 25%;
				}

				&.five {
					width: 20%;
				}
			}
		}

		swiper,
		.swiper-item {
			width: 100%;
			display: block;
		}
	}
</style>