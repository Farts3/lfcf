<template>
	<div class="group-con" :style="colorStyle">
		<div class="header acea-row row-between-wrapper">
			<div class="pictrue"><img :src="storeCombination.image" /></div>
			<div class="text">
				<div class="line1" v-text="storeCombination.title"></div>
				<div class="money">
					￥
					<span class="num" v-text="storeCombination.price || 0"></span>
					<span class="team cart-color" v-text="(storeCombination.people || 0) + '人拼'"></span>
				</div>
			</div>
			<div v-if="pinkBool === -1" class="iconfont icon-pintuanshibai"></div>
			<div v-else-if="pinkBool === 1" class="iconfont icon-pintuanchenggong font-num"></div>
		</div>
		<div class="wrapper">
			<div class="title acea-row row-center-wrapper" v-if="pinkBool === 0">
				<div class="line"></div>
				<div class="name acea-row row-center-wrapper">
					剩余
					<CountDown :is-day="false" :tip-text="' '" :day-text="' '" :hour-text="' : '" :minute-text="' : '"
						:second-text="' '" :datatime="pinkT.stop_time"></CountDown>
					结束
				</div>
				<div class="line"></div>
			</div>
			<div class="tips font-num" v-if="pinkBool === 1">恭喜您拼团成功</div>
			<div class="tips" v-else-if="pinkBool === -1">还差{{ count }}人，拼团失败</div>
			<div class="tips font-num" v-else-if="pinkBool === 0">拼团中，还差{{ count }}人拼团成功</div>
			<div class="list acea-row row-middle"
				:class="[pinkBool === 1 || pinkBool === -1 ? 'result' : '', iShidden ? 'on' : '']">
				<div class="pictrue">
					<img :src="pinkT.avatar" />
					<div class="label">团长</div>
				</div>
				<div class="pictrue" v-if="pinkAll.length > 0" v-for="(item, index) in pinkAll" :key="index"><img :src="item.avatar" /></div>
				<div class="pictrue" v-for="index in count" :key="index">
					<image class="img-none" src="../static/vacancy.png"> </image>
				</div>
			</div>
			<div v-if="(pinkBool === 1 || pinkBool === -1) && pinkAll.length > 9" class="lookAll acea-row row-center-wrapper"
				@click="lookAll">
				{{ iShidden ? '收起' : '查看全部' }}
				<span class="iconfont" :class="iShidden ? 'icon-xiangshang' : 'icon-xiangxia'"></span>
			</div>
			<view class="teamBnt bg-color-red" v-if="!isLogin" @click="getIsLogin">立即登录</view>
			<div v-if="userBool === 1 && isOk == 0 && pinkBool === 0">
				<div class="teamBnt bg-color-red" @click="listenerActionSheet">邀请好友参团</div>
			</div>
			<div class="teamBnt bg-color-red" v-else-if="userBool === 0 && pinkBool === 0 && count > 0">
			 <div v-if="endTime" @click="goDetail(storeCombination.id)">我要开团</div>
			 <div v-else @click="pay">我要参团</div>
			</div>
			<div class="teamBnt bg-color-red" v-if="pinkBool === 1 || pinkBool === -1"
				@click="goDetail(storeCombination.id)">再次开团</div>
			<div class="cancel" @click="getCombinationRemove"
				v-if="pinkBool === 0 && userBool === 1 && pinkT.uid == userInfo.uid">
				<span class="iconfont icon-guanbi3"></span>
				取消开团
			</div>
			<div class="lookOrder" v-if="pinkBool === 1" @click="goOrder">
				查看订单信息
				<span class="iconfont icon-xiangyou"></span>
			</div>
		</div>
		<div class="group-recommend">
			<div class="title acea-row row-between-wrapper">
				<div>大家都在拼</div>
				<div class="more" @click="goList">
					更多拼团
					<span class="iconfont icon-jiantou"></span>
				</div>
			</div>
			<div class="list acea-row row-middle">
				<div class="item" v-for="(item, index) in storeCombinationHost" :key="index" @click="goDetail(item.id)">
					<div class="pictrue">
						<img :src="item.image" />
						<div class="team" v-text="item.people + '人团'"></div>
					</div>
					<div class="name line1" v-text="item.title"></div>
					<div class="money font-color-red" v-text="'￥' + item.price"></div>
				</div>
			</div>
		</div>
		<product-window :attr="attr" :limitNum="1" :iSbnt="1" :productType="storeCombination.product_type" :productId="storeCombination.product_id" @myevent="onMyEvent" @ChangeAttr="ChangeAttr"
			@ChangeCartNum="ChangeCartNum" @iptCartNum="iptCartNum" @attrVal="attrVal" @goCat="goPay" @deliveryFun="deliveryFun" @onAddressId="onAddressId" @onstoreId="onstoreId"></product-window>
		<!-- 分享按钮 -->
		<view class="generate-posters acea-row row-middle" :class="posters ? 'on' : ''">
			<!-- #ifndef MP -->
			<button class="item" hover-class='none' v-if="weixinStatus === true" @click="H5ShareBox = true">
				<!-- <button class="item" hover-class='none' v-if="weixinStatus === true" @click="setShareInfoStatus"> -->
				<view class="iconfont icon-weixin3"></view>
				<view class="">发送给朋友</view>
			</button>
			<!-- #endif -->
			<!-- #ifdef MP -->
			<button class="item" open-type="share" hover-class='none' @click="goFriend">
				<view class="iconfont icon-weixin3"></view>
				<view class="">发送给朋友</view>
			</button>
			<!-- #endif -->
			<!-- #ifdef APP-PLUS -->
			<view class="item" @click="appShare('WXSceneSession')">
				<view class="iconfont icon-weixin3"></view>
				<view class="">微信好友</view>
			</view>
			<view class="item" @click="appShare('WXSenceTimeline')">
				<view class="iconfont icon-pengyouquan"></view>
				<view class="">微信朋友圈</view>
			</view>
			<!-- #endif -->
			<!-- #ifndef APP-PLUS -->
			<button class="item" hover-class='none' @tap="goPoster">
				<view class="iconfont icon-haibao"></view>
				<view class="">生成海报</view>
			</button>
			<!-- #endif -->
		</view>
		<view class="mask" v-if="posters" @click="listenerActionClose"></view>
		<!-- 发送给朋友图片 -->
		<view class="share-box" v-if="H5ShareBox">
			<image :src="imgHost + '/statics/images/share-info.png'" @click="H5ShareBox = false"></image>
		</view>
		<!-- <Product-window v-on:changeFun="changeFun" :attr="attr" :limitNum='1' :iSbnt='1'></Product-window> -->
		<home v-if="navigation"></home>
		<!-- #ifdef MP -->
		<authorize v-if="isShowAuth" @authColse="authColse" @onLoadFun="onLoadFun"></authorize>
		<!-- #endif -->
	</div>
</template>
<script>
	import CountDown from '@/components/countDown';
	import ProductWindow from '@/components/productWindow';
	import util from '../../../utils/util.js';
	import {
		toLogin
	} from '@/libs/login.js';
	import {
		mapGetters
	} from 'vuex';
	import {
		getCombinationPink,
		postCombinationRemove
	} from '@/api/activity';
	import {
		postCartAdd
	} from '@/api/store';
	import home from '@/components/home';
	const NAME = 'GroupRule';
	import {
		TOKENNAME,
		HTTP_REQUEST_URL
	} from '@/config/app.js';
	const app = getApp();
	import colors from '@/mixins/color.js';
	export default {
		name: NAME,
		components: {
			CountDown,
			ProductWindow,
			home
		},
		props: {},
		mixins:[colors],
		data: function() {
			return {
				currentPinkOrder: '', //当前拼团订单
				isOk: 0, //判断拼团是否完成
				pinkBool: 0, //判断拼团是否成功|0=失败,1=成功
				userBool: 0, //判断当前用户是否在团内|0=未在,1=在
				pinkAll: [], //团员
				pinkT: [], //团长信息
				storeCombination: [], //拼团产品
				storeCombinationHost: [], //拼团推荐
				pinkId: 0,
				count: 0, //拼团剩余人数
				iShidden: false,
				isOpen: false, //是否打开属性组件
				attr: {
					cartAttr: false,
					productSelect: {
						image: '',
						store_name: '',
						price: '',
						quota: 0,
						unique: '',
						cart_num: 1,
						quota_show: 0,
						product_stock: 0,
						num: 0
					},
					productAttr: [],
					deliveryType:[],
					isType:0,
					relation_id:0,
					store_self_mention:0
				},
				cart_num: '',
				userInfo: {},
				posters: false,
				weixinStatus: false,
				H5ShareBox: false, //公众号分享图片
				isAuto: false, //没有授权的不会自动授权
				isShowAuth: false, //是否隐藏授权
				attrTxt: '请选择', //属性页面提示
				attrValue: '', //已选属性
				imgHost:HTTP_REQUEST_URL,
				addressId:'',
				store_id :'',
				delivery_type:0,
				store_name:'',
				endTime:false
			};
		},
		computed: mapGetters({
			'isLogin': 'isLogin',
			'userData': 'userInfo'
		}),
		watch: {
			isLogin: {
				handler: function(newV, oldV) {
					if (newV) {
						this.getCombinationPink();
					}else{
						this.getIsLogin();
					}
				},
				deep: true
			},
			userData: {
				handler: function(newV, oldV) {
					if (newV) {
						this.userInfo = newV;
						app.globalData.openPages = '/pages/activity/goods_combination_status/index?id=' + this.pinkId;
					}
				},
				deep: true
			}
		},
		onLoad(options) {
			var that = this;
			// #ifdef MP
			if (options.scene) {
				var value = util.getUrlParams(decodeURIComponent(options.scene));
				if (typeof value === 'object') {
					if (value.id) options.id = value.id;
					//记录推广人uid
					if (value.spid) app.globalData.spid = value.spid;
				}
			}
			// #endif
			if (options.id) {
				that.pinkId = options.id;
			}
			// 记录推广人uid；
			if (options.spid) app.globalData.spid = options.spid;
			if (that.isLogin == false) {
				this.$Cache.set('login_back_url', `/pages/activity/goods_combination_status/index?id=${options.id}`);
				this.getIsLogin();
			} else {
				this.getCombinationPink();
			}
		},
		onShow(){
			uni.removeStorageSync('form_type_cart');
		},
		//#ifdef MP
		/**
		 * 用户点击右上角分享
		 */
		onShareAppMessage: function() {
			let that = this;
			return {
				title: '您的好友' + that.userInfo.nickname + '邀请您参团' + that.storeCombination.title,
				path: app.globalData.openPages,
				imageUrl: that.storeCombination.image
			};
		},
		//#endif
		methods: {
			onLoadFun(){
				this.getCombinationPink();
				this.isShowAuth = false;
			},
			getIsLogin(){
				// #ifndef MP
				toLogin()
				// #endif
				// #ifdef MP
				this.isShowAuth = true;
				// #endif
			},
			// 授权关闭
			authColse: function(e) {
			  this.isShowAuth = e
			},
			// 获取配送方式
			deliveryFun(e){
				this.delivery_type = e;
			},
			// 获取配送地址id
			onAddressId(row){
				this.addressId=row.id
			},
			// 获取自提地址id
			onstoreId(row) {
			  this.store_id = row.id;
				this.store_name = row.name;
			},
			// app分享
			// #ifdef APP-PLUS
			appShare(scene) {
				let that = this
				let routes = getCurrentPages(); // 获取当前打开过的页面路由数组
				let curRoute = routes[routes.length - 1].$page.fullPath // 获取当前页面路由，也就是最后一个打开的页面路由
				uni.share({
					provider: "weixin",
					scene: scene,
					type: 0,
					href: `${HTTP_REQUEST_URL}${curRoute}`,
					title: '您的好友' + that.userInfo.nickname + '邀请您参团' + that.storeCombination.title,
					imageUrl: that.storeCombination.small_image,
					success: function(res) {
						uni.showToast({
							title: '分享成功',
							icon: 'success'
						})
						that.posters = false;
					},
					fail: function(err) {
						uni.showToast({
							title: '分享失败',
							icon: 'none',
							duration: 2000
						})
						that.posters = false;
					}
				});
			},
			// #endif
			/**
			 * 分享打开
			 * 
			 */
			listenerActionSheet: function() {
				if (this.isLogin == false) {
					this.getIsLogin();
				} else {
					// #ifdef H5
					if (this.$wechat.isWeixin() === true) {
						this.weixinStatus = true;
					}
					// #endif
					this.posters = true;

				}
			},
			// 分享关闭
			listenerActionClose: function() {
				this.posters = false;
			},
			// 小程序关闭分享弹窗；
			goFriend: function() {
				this.posters = false;
			},
			/**
			 * 购物车手动填写
			 *
			 */
			iptCartNum: function(e) {
				this.$set(this.attr.productSelect, 'cart_num', e);
				this.$set(this, 'cart_num', e);
			},
			attrVal(val) {
				this.attr.productAttr[val.indexw].index = this.attr.productAttr[val.indexw].attr_values[val.indexn];
			},
			onMyEvent: function() {
				this.$set(this.attr, 'cartAttr', false);
				this.$set(this, 'isOpen', false);
			},
			//将父级向子集多次传送的函数合二为一；
			// changeFun: function(opt) {
			// 	if (typeof opt !== "object") opt = {};
			// 	let action = opt.action || "";
			// 	let value = opt.value === undefined ? "" : opt.value;
			// 	this[action] && this[action](value);
			// },
			// changeattr: function(res) {
			// 	var that = this;
			// 	that.attr.cartAttr = res;
			// },
			//选择属性；
			ChangeAttr: function(res) {
				this.$set(this, 'cart_num', 1);
				let productSelect = this.productValue[res];
				if (productSelect) {
					this.$set(this.attr.productSelect, 'image', productSelect.image);
					this.$set(this.attr.productSelect, 'price', productSelect.price);
					this.$set(this.attr.productSelect, 'quota', productSelect.quota);
					this.$set(this.attr.productSelect, 'unique', productSelect.unique);
					this.$set(this.attr.productSelect, 'cart_num', 1);
					this.$set(this.attr.productSelect, 'product_stock', productSelect.product_stock);
					this.$set(this.attr.productSelect, 'quota_show', productSelect.quota_show);
					this.$set(this, 'attrValue', res);
					this.$set(this, 'attrTxt', '已选择');
				} else {
					this.$set(this.attr.productSelect, 'image', this.storeCombination.image);
					this.$set(this.attr.productSelect, 'price', this.storeCombination.price);
					this.$set(this.attr.productSelect, 'quota', 0);
					this.$set(this.attr.productSelect, 'unique', '');
					this.$set(this.attr.productSelect, 'cart_num', 0);
					this.$set(this.attr.productSelect, 'quota_show', 0);
					this.$set(this.attr.productSelect, 'product_stock', 0);
					this.$set(this, 'attrValue', '');
					this.$set(this, 'attrTxt', '请选择');
				}
			},
			ChangeCartNum: function(res) {
				//changeValue:是否 加|减
				//获取当前变动属性
				let productSelect = this.productValue[this.attrValue];
				if (this.cart_num) {
					productSelect.cart_num = this.cart_num;
					this.attr.productSelect.cart_num = this.cart_num;
				}
				//如果没有属性,赋值给商品默认库存
				if (productSelect === undefined && !this.attr.productAttr.length) productSelect = this.attr
					.productSelect;
				if (productSelect === undefined) return;
				let stock = productSelect.stock || 0;
				let quotaShow = productSelect.quota_show || 0;
				let quota = productSelect.quota || 0;
				let productStock = productSelect.product_stock || 0;
				let num = this.attr.productSelect;
				let nums = this.storeCombination.num || 0;
				//设置默认数据
				if (productSelect.cart_num == undefined) productSelect.cart_num = 1;
				if (res) {
					num.cart_num++;
					let arrMin = [];
					arrMin.push(nums);
					arrMin.push(quota);
					arrMin.push(productStock);
					let minN = Math.min.apply(null, arrMin);
					if (num.cart_num >= minN) {
						this.$set(this.attr.productSelect, 'cart_num', minN ? minN : 1);
						this.$set(this, 'cart_num', minN ? minN : 1);
					}
					// if(quotaShow >= productStock){
					// 	 if (num.cart_num > productStock) {
					// 	 	this.$set(this.attr.productSelect, "cart_num", productStock);
					// 	 	this.$set(this, "cart_num", productStock);
					// 	 }
					// }else{
					// 	if (num.cart_num > quotaShow) {
					// 		this.$set(this.attr.productSelect, "cart_num", quotaShow);
					// 		this.$set(this, "cart_num", quotaShow);
					// 	}
					// }
					this.$set(this, 'cart_num', num.cart_num);
					this.$set(this.attr.productSelect, 'cart_num', num.cart_num);
				} else {
					num.cart_num--;
					if (num.cart_num < 1) {
						this.$set(this.attr.productSelect, 'cart_num', 1);
						this.$set(this, 'cart_num', 1);
					}
					this.$set(this, 'cart_num', num.cart_num);
					this.$set(this.attr.productSelect, 'cart_num', num.cart_num);
				}
				// if (res) {
				// 	num.cart_num++;
				// 	if (num.cart_num > quota) {
				// 		this.$set(this.attr.productSelect, "cart_num", quota);
				// 		this.$set(this, "cart_num", quota);
				// 	}
				// } else {
				// 	num.cart_num--;
				// 	if (num.cart_num < 1) {
				// 		this.$set(this.attr.productSelect, "cart_num", 1);
				// 		this.$set(this, "cart_num", 1);
				// 	}
				// }
			},
			//默认选中属性；
			DefaultSelect() {
				let productAttr = this.attr.productAttr,
					value = [];
				for (var key in this.productValue) {
					if (this.productValue[key].quota > 0) {
						value = this.attr.productAttr.length ? key.split(',') : [];
						break;
					}
				}
				for (let i = 0; i < productAttr.length; i++) {
					this.$set(productAttr[i], 'index', value[i]);
				}
				//sort();排序函数:数字-英文-汉字；
				let productSelect = this.productValue[value.join(',')];
				if (productSelect && productAttr.length) {
					this.$set(this.attr.productSelect, 'store_name', this.storeCombination.title);
					this.$set(this.attr.productSelect, 'image', productSelect.image);
					this.$set(this.attr.productSelect, 'price', productSelect.price);
					this.$set(this.attr.productSelect, 'quota', productSelect.quota);
					this.$set(this.attr.productSelect, 'unique', productSelect.unique);
					this.$set(this.attr.productSelect, 'cart_num', 1);
					this.$set(this.attr.productSelect, 'product_stock', productSelect.product_stock);
					this.$set(this.attr.productSelect, 'quota_show', productSelect.quota_show);
					this.$set(this, 'attrValue', value.join(','));
					this.attrValue = value.join(',');
					this.$set(this, 'attrTxt', '已选择');
				} else if (!productSelect && productAttr.length) {
					this.$set(this.attr.productSelect, 'store_name', this.storeCombination.title);
					this.$set(this.attr.productSelect, 'image', this.storeCombination.image);
					this.$set(this.attr.productSelect, 'price', this.storeCombination.price);
					this.$set(this.attr.productSelect, 'quota', 0);
					this.$set(this.attr.productSelect, 'unique', '');
					this.$set(this.attr.productSelect, 'cart_num', 0);
					this.$set(this.attr.productSelect, 'product_stock', 0);
					this.$set(this.attr.productSelect, 'quota_show', 0);
					this.$set(this, 'attrValue', '');
					this.$set(this, 'attrTxt', '请选择');
				} else if (!productSelect && !productAttr.length) {
					this.$set(this.attr.productSelect, 'store_name', this.storeCombination.title);
					this.$set(this.attr.productSelect, 'image', this.storeCombination.image);
					this.$set(this.attr.productSelect, 'price', this.storeCombination.price);
					this.$set(this.attr.productSelect, 'quota', 0);
					this.$set(this.attr.productSelect, 'unique', this.storeCombination.unique || '');
					this.$set(this.attr.productSelect, 'cart_num', 1);
					this.$set(this.attr.productSelect, 'quota_show', 0);
					this.$set(this.attr.productSelect, 'product_stock', 0);
					this.$set(this, 'attrValue', '');
					this.$set(this, 'attrTxt', '请选择');
				}
			},
			setProductSelect: function() {
				var that = this;
				var attr = that.attr;
				attr.productSelect.image = that.storeCombination.image;
				attr.productSelect.store_name = that.storeCombination.title;
				attr.productSelect.price = that.storeCombination.price;
				attr.productSelect.quota = 0;
				attr.productSelect.quota_show = 0;
				attr.productSelect.product_stock = 0;
				attr.cartAttr = false;
				that.$set(that, 'attr', attr);
			},
			pay: function() {
				var that = this;
				that.attr.cartAttr = true;
				that.isOpen = true;
			},
			goPay() {
				var that = this;
				var data = {};
				// that.attr.cartAttr = res;
				data.productId = that.storeCombination.product_id;
				data.cartNum = that.attr.productSelect.cart_num;
				data.uniqueId = that.attr.productSelect.unique;
				data.combinationId = that.storeCombination.id;
				data.new = 1;
				data.store_id = this.store_id;
				data.addressId = this.addressId;
				data.delivery_type = this.delivery_type;
				postCartAdd(data)
					.then(res => {
						uni.navigateTo({
						  url: '/pages/goods/order_confirm/index?new=1&cartId=' + res.data.cartId+'&pinkId=' + that.pinkId+'&delivery_type='+that.delivery_type+'&addressId='+that.addressId+'&store_id='+that.store_id+'&store_name='+that.store_name+'&product_id=' + that.storeCombination.product_id
						});
					})
					.catch(res => {
						that.$util.Tips({
							title: res
						});
					});
			},
			goPoster: function() {
				var that = this;
				that.posters = false;
				uni.navigateTo({
					url: '/pages/activity/poster-poster/index?type=2&id=' + that.pinkId
				});
			},
			goOrder: function() {
				var that = this;
				uni.navigateTo({
					url: '/pages/goods/order_details/index?order_id=' + that.currentPinkOrder
				});
			},
			//拼团列表
			goList: function() {
				uni.navigateTo({
					url: '/pages/activity/goods_combination/index'
				});
			},
			//拼团详情
			goDetail: function(id) {
				this.pinkId = id;
				uni.navigateTo({
					url: '/pages/activity/goods_combination_details/index?id=' + id
				});
			},
			//拼团信息
			getCombinationPink: function() {
				var that = this;
				getCombinationPink(that.pinkId)
					.then(res => {
						that.$set(that.attr, 'isType', res.data.store_combination.type);
						that.$set(that.attr, 'relation_id', res.data.store_combination.relation_id);
						that.$set(that.attr, 'store_self_mention', res.data.store_self_mention);
						that.$set(that, 'storeCombinationHost', res.data.store_combination_host);
						res.data.pinkT.stop_time = parseInt(res.data.pinkT.stop_time);
						let timestamp = Date.parse(new Date())/1000;
						that.endTime = parseInt(timestamp)>res.data.pinkT.stop_time;
						that.$set(that, 'storeCombination', res.data.store_combination);
						that.$set(that.attr.productSelect, 'num', res.data.store_combination.num);
						that.$set(that, 'pinkT', res.data.pinkT);
						that.$set(that, 'pinkAll', res.data.pinkAll);
						that.$set(that, 'count', res.data.count);
						that.$set(that, 'userBool', res.data.userBool);
						that.$set(that, 'pinkBool', res.data.pinkBool);
						that.$set(that, 'isOk', res.data.is_ok);
						that.$set(that, 'currentPinkOrder', res.data.current_pink_order);
						that.$set(that, 'userInfo', res.data.userInfo);
						app.globalData.openPages = '/pages/activity/goods_combination_status/index?id=' + this.pinkId;
						res.data.store_combination.delivery_type.sort((x,y)=>x - y);
						that.$set(that.attr, 'deliveryType', res.data.store_combination.delivery_type);
						that.attr.productAttr = res.data.store_combination.productAttr;
						that.productValue = res.data.store_combination.productValue;
						//#ifdef H5
						that.setOpenShare();
						//#endif
						that.setProductSelect();
						if (that.attr.productAttr != 0) that.DefaultSelect();
						if(res.data.is_ok==1&&res.data.userBool==0){
							 return this.$util.Tips({
							 	title: '你不是该团的成员',
							 }, () => {
								 uni.navigateTo({
									 url: '/pages/activity/goods_combination/index'
								 })
							 });
						}
					})
					.catch(err => {
						return this.$util.Tips({
							title: err,
						}, () => {
							uni.navigateBack()
							 // uni.switchTab({
							 // 	 url: '/pages/index/index'
							 // })
						});
					});
			},
			//#ifdef H5
			setOpenShare() {
				let that = this;
				let configTimeline = {
					title: '您的好友' + that.userInfo.nickname + '邀请您参团' + that.storeCombination.title,
					desc: that.storeCombination.title,
					link: window.location.protocol + '//' + window.location.host +
						'/pages/activity/goods_combination_status/index?id=' + that.pinkId + '&spid='+ that.userInfo.uid,
					imgUrl: that.storeCombination.image
				};
				if (this.$wechat.isWeixin()) {
					this.$wechat
						.wechatEvevt(['updateAppMessageShareData', 'updateTimelineShareData', 'onMenuShareAppMessage',
							'onMenuShareTimeline'
						], configTimeline)
						.then(res => {})
						.catch(res => {
							if (res.is_ready) {
								res.wx.updateAppMessageShareData(configTimeline);
								res.wx.updateTimelineShareData(configTimeline);
								res.wx.onMenuShareAppMessage(configTimeline);
								res.wx.onMenuShareTimeline(configTimeline);
							}
						});
				}
			},
			//#endif
			//拼团取消
			getCombinationRemove: function() {
				var that = this;
				postCombinationRemove({
						id: that.pinkId,
						cid: that.storeCombination.id
					})
					.then(res => {
						that.$util.Tips({
							title: res.msg
						}, {
							tab: 3
						});
					})
					.catch(res => {
						that.$util.Tips({
							title: res
						});
					});
			},
			lookAll: function() {
				this.iShidden = !this.iShidden;
			}
		}
	};
</script>
<style lang="scss" scoped>
	.generate-posters {
		width: 100%;
		height: 170rpx;
		background-color: #fff;
		position: fixed;
		left: 0;
		bottom: 0;
		z-index: 300;
		transform: translate3d(0, 100%, 0);
		transition: all 0.3s cubic-bezier(0.25, 0.5, 0.5, 0.9);
		border-top: 1rpx solid #eee;
	}

	.generate-posters.on {
		transform: translate3d(0, 0, 0);
	}

	.generate-posters .item {
		flex: 1;
		text-align: center;
		font-size: 30rpx;
	}

	.generate-posters .item .iconfont {
		font-size: 80rpx;
		color: #5eae72;
	}

	.generate-posters .item .iconfont.icon-haibao {
		color: #5391f1;
	}

	/*开团*/
	.group-con .header {
		width: 100%;
		height: 186rpx;
		background-color: #fff;
		border-top: 1px solid #f5f5f5;
		padding: 0 30rpx;
		position: relative;
	}

	.group-con .header .iconfont {
		font-size: 100rpx;
		position: absolute;
		color: #ccc;
		right: 33rpx;
		bottom: 20rpx;
	}

	.group-con .header .pictrue {
		width: 140rpx;
		height: 140rpx;
	}

	.group-con .header .pictrue img {
		width: 100%;
		height: 100%;
		border-radius: 6rpx;
	}

	.group-con .header .text {
		width: 540rpx;
		font-size: 30rpx;
		color: #222;
	}

	.group-con .header .text .money {
		font-size: 24rpx;
		font-weight: bold;
		margin-top: 15rpx;
	}

	.group-con .header .text .money .num {
		font-size: 32rpx;
	}

	.group-con .header .text .money .team {
		padding: 1rpx 10rpx;
		font-weight: normal;
		border-radius: 50rpx;
		font-size: 20rpx;
		vertical-align: 4rpx;
		margin-left: 15rpx;
	}

	.group-con .wrapper {
		background-color: #fff;
		margin-top: 20rpx;
		padding: 2rpx 0 35rpx 0;
	}

	.group-con .wrapper .title {
		margin-top: 30rpx;
	}

	.group-con .wrapper .title .line {
		width: 136rpx;
		height: 1px;
		background-color: #ddd;
	}

	.group-con .wrapper .title .name {
		margin: 0 45rpx;
		font-size: 28rpx;
		color: #282828;
	}

	.group-con .wrapper .title .name .time {
		margin: 0 14rpx;
	}

	.group-con .wrapper .title .name .timeTxt {
		color: #fc4141;
	}

	.group-con .wrapper .title .name /deep/.time .styleAll {
	    background-color: var(--view-minorColorT);
	    text-align: center;
	    border-radius: 3rpx;
	    font-size: 28rpx;
	    font-weight: bold;
	    display: inline-block;
	    vertical-align: middle;
	    color: var(--view-theme);
	    padding: 2rpx 5rpx;
	}

	.group-con .wrapper .tips {
		font-size: 30rpx;
		font-weight: bold;
		text-align: center;
		margin-top: 30rpx;
		color: #999;
	}

	.group-con .wrapper .list {
		padding: 0 30rpx;
		margin-top: 25rpx;
	}

	.group-con .wrapper .list.result {
		max-height: 240rpx;
		overflow: hidden;
		padding-top: 20rpx;
	}

	.group-con .wrapper .list.result.on {
		max-height: 2000rpx;
	}

	.group-con .wrapper .list .pictrue {
		width: 94rpx;
		height: 94rpx;
		margin: 0 0 29rpx 35rpx;
		position: relative;
	}
	
	.group-con .wrapper .list .pictrue .label{
		position: absolute;
		top:-14rpx;
		right: -26rpx;
		width: 78rpx;
		height: 34rpx;
		color: #fff;
		font-size: 24rpx;
		background-color: var(--view-theme);
		text-align: center;
		line-height: 34rpx;
		border-radius: 40rpx;
	}

	.group-con .wrapper .list .pictrue img,
	.group-con .wrapper .list .pictrue image {
		width: 100%;
		height: 100%;
		border-radius: 50%;
		border: 2rpx solid var(--view-theme);
	}

	.group-con .wrapper .list .pictrue image.img-none {
		border: none;
	}

	.group-con .wrapper .lookAll {
		font-size: 24rpx;
		color: #282828;
		padding-top: 10rpx;
	}

	.group-con .wrapper .lookAll .iconfont {
		font-size: 25rpx;
		margin: 2rpx 0 0 10rpx;
	}

	.group-con .wrapper .teamBnt {
		font-size: 30rpx;
		width: 620rpx;
		height: 86rpx;
		border-radius: 50rpx;
		text-align: center;
		line-height: 86rpx;
		color: #fff;
		margin: 21rpx auto 0 auto;
	}

	.group-con .wrapper .cancel,
	.group-con .wrapper .lookOrder {
		text-align: center;
		font-size: 24rpx;
		color: #282828;
		padding-top: 30rpx;
	}

	.group-con .wrapper .cancel .iconfont {
		font-size: 35rpx;
		color: #2c2c2c;
		vertical-align: -4rpx;
		margin-right: 9rpx;
	}

	.group-con .wrapper .lookOrder .iconfont {
		font-size: 25rpx;
		color: #2c2c2c;
		margin-left: 10rpx;
	}

	.group-con .group-recommend {
		background-color: #fff;
		margin-top: 25rpx;
	}

	.group-con .group-recommend .title {
		padding-right: 30rpx;
		margin-left: 30rpx;
		height: 85rpx;
		border-bottom: 1px solid #eee;
		font-size: 28rpx;
		color: #282828;
	}

	.group-con .group-recommend .title .more {
		color: #808080;
	}

	.group-con .group-recommend .title .more .iconfont {
		margin-left: 13rpx;
		font-size: 28rpx;
	}

	.group-con .group-recommend .list {
		margin-top: 30rpx;
	}

	.group-con .group-recommend .list .item {
		width: 210rpx;
		margin: 0 0 25rpx 30rpx;
	}

	.group-con .group-recommend .list .item .pictrue {
		width: 100%;
		height: 210rpx;
		position: relative;
	}

	.group-con .group-recommend .list .item .pictrue img {
		width: 100%;
		height: 100%;
		border-radius: 10rpx;
	}

	.group-con .group-recommend .list .item .pictrue .team {
		position: absolute;
		top: 28rpx;
		left: -5rpx;
		min-width: 100rpx;
		height: 36rpx;
		line-height: 36rpx;
		text-align: center;
		border-radius: 0 18rpx 18rpx 0;
		font-size: 20rpx;
		color: #fff;
		background-color: var(--view-theme);
		// background-image: linear-gradient(to right, #fb5445 0%, #e93323 100%);
	}

	.group-con .group-recommend .list .item .name {
		font-size: 28rpx;
		color: #333;
		margin-top: 0.18rem;
	}

	.group-con .group-recommend .list .item .money {
		font-weight: bold;
		font-size: 28rpx;
	}

	.share-box {
		z-index: 1000;
		position: fixed;
		left: 0;
		top: 0;
		width: 100%;
		height: 100%;

		image {
			width: 100%;
			height: 100%;
		}
	}
</style>
