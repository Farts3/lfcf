<template>
	<!-- 分类二商品列表 -->
	<view class="goodsList">
		<view class="item" v-for="(item,index) in tempArr" :key='index' @click="goDetail(item)">
			<view class="pictrue">
				<span class="pictrue_log pictrue_log_class" v-if="item.activity && item.activity.type === '1'">秒杀</span>
				<span class="pictrue_log pictrue_log_class" v-if="item.activity && item.activity.type === '2'">砍价</span>
				<span class="pictrue_log pictrue_log_class" v-if="item.activity && item.activity.type === '3'">拼团</span>
				<image :src="item.recommend_image" mode="aspectFill" v-if="item.recommend_image"></image>
				<image :src="item.image" mode="aspectFill" v-else></image>
			</view>
			<view class="text line2">{{item.store_name}}</view>
			<!-- #ifdef H5 || APP-PLUS -->
			<slot name="center" :item="item"></slot>
			<!-- #endif -->
			<!-- #ifdef MP -->
			<slot name="center{{index}}"></slot>
			<!-- #endif -->
			<view class="bottom acea-row row-between-wrapper">
				<view class="sales acea-row row-middle">
					<view class="money font-color"><text>￥</text>{{item.price}}</view>
					<view>已售 {{item.sales}}</view>
				</view>
				<view v-if="item.stock>0">
				    <view class="bnt acea-row row-center-wrapper" v-if="(item.activity && (item.activity.type === '1' || item.activity.type === '2' || item.activity.type === '3')) || item.product_type!=0 || item.custom_form.length">立即购买</view>
					<view v-else>
						<!-- 多规格 -->
						<!-- <view class="bnt acea-row row-center-wrapper" @click.stop="goCartDuo(item)" v-if="item.spec_type">
							加入购物车
							<text class="num" v-if="isLogin && item.cart_num">{{item.cart_num}}</text>
						</view> -->
						<uni-badge class="uni-badge-left-margin" :text="item.cart_num" absolute="rightTop" v-if="item.spec_type">
						  <!-- 多规格 -->
						  <view class="bnt acea-row row-center-wrapper" @click.stop="goCartDuo(item)">
						  	选规格
						  </view>
						</uni-badge>
						<!-- 单规格 -->
						<view v-if="!item.spec_type && !item.cart_num">
							<view v-if="item.cart_button">
								<view class="bnt acea-row row-center-wrapper end" v-if="item.is_presale_product && (item.presale_pay_status == 1 || item.presale_pay_status == 3)">>
									{{item.presale_pay_status === 1?'未开始':'已结束'}}
								</view>
								<view v-else class="bnt acea-row row-center-wrapper" @click.stop="goCartDan(item,index)">加入购物车</view>
							</view>
							<view v-else class="bnt acea-row row-center-wrapper">立即购买</view>
						</view>
						<view class="cart acea-row row-middle" v-if="!item.spec_type && item.cart_num">
							<view class="pictrue iconfont icon-jianhao" @click.stop="CartNumDes(index,item)"></view>
							<view class="num">{{item.cart_num}}</view>
							<view class="pictrue iconfont icon-jiahao" @click.stop="CartNumAdd(index,item)"></view>
						</view>
					</view>
				</view>
				<view class="bnt end acea-row row-center-wrapper" v-else>已售罄</view>
			</view>
		</view>
	</view>
</template>

<script>
	export default {
		name: 'd_goodList',
		props: {
			dataConfig: {
				type: Object,
				default: () => {}
			},
			tempArr:{
				type: Array,
				default: () => []
			},
			isLogin:{
				type: Boolean,
				default:false
			}
		},
		data() {
			return {
			};
		},
		created() {},
		mounted() {},
		methods: {
			goDetail(item){
				this.$emit('detail',item);
			},
			goCartDuo(item){
				this.$emit('gocartduo',item);
			},
			goCartDan(item,index){
				this.$emit('gocartdan',item,index);
			},
			CartNumDes(index,item){
				this.$emit('ChangeCartNumDan', false,index,item);
			},
			CartNumAdd(index,item){
				if(item.is_limit && item.cart_num>=item.limit_num){
					this.$util.Tips({
					  title: "购买最多不能超过"+item.limit_num
					});
				}else{
					this.$emit('ChangeCartNumDan', true,index,item);
				}
			}
		}
	};
</script>

<style lang="scss">
	.goodsList{
		padding: 0 30rpx;
		.item{
			width: 100%;
			box-sizing: border-box;
			margin-bottom: 63rpx;
			.pictrue{
				width: 100%;
				height: 290rpx;
				border-radius: 16rpx;
				position: relative;
				image{
					width: 100%;
					height: 100%;
					border-radius: 16rpx;
				}
			}
			.text{
				font-size:30rpx;
				font-family:PingFang SC;
				font-weight:bold;
				color: #282828;
				margin: 20rpx 0;
			}
			.bottom{
				.sales{
					font-size: 22rpx;
					color: #8E8E8E;
					.money{
						font-size: 42rpx;
						font-weight: bold;
						margin-right: 16rpx;
						text{
							font-size: 28rpx;
						}
					}
				}
				.cart{
					height: 56rpx;
					.pictrue{
						color: var(--view-theme);
						font-size:46rpx;
						width: 50rpx;
						height: 50rpx;
						text-align: center;
						line-height: 50rpx;
					}
					.num{
						font-size: 30rpx;
						color: #282828;
						font-weight: bold;
						width: 80rpx;
						text-align: center;
					}
				}
				.bnt{
					padding: 0 30rpx;
					height: 55rpx;
					background:var(--view-theme);
					border-radius:42rpx;
					font-size: 26rpx;
					color: #fff;
					position: relative;
					&.end{
						background:rgba(203,203,203,1);
					}
					.num{
						min-width: 14rpx;
						background-color: #fff;
						color: var(--view-theme);
						border-radius: 15px;
						position: absolute;
						right: -14rpx;
						top: -15rpx;
						font-size: 20rpx;
						padding: 0 10rpx;
						border: 1px solid var(--view-theme);
					}
				}
			}
		}
	}
</style>
