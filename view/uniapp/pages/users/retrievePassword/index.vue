<template>
	<div class="register absolute" :style="colorStyle">
		<!-- <div class="shading">
			<div class="pictrue acea-row row-center-wrapper">
				<image src="../static/logo2.png" />
			</div>
		</div> -->
		<div class="whiteBg">
			<div class="title">重置密码</div>
			<div class="list">
				<div class="item">
					<div class="acea-row row-middle">
						<image src="../static/phone_1.png"></image>
						<input type="text" placeholder="输入手机号码" v-model="account" />
					</div>
				</div>
				<div class="item">

					<div class="acea-row row-middle">
						<image src="../static/code_2.png"></image>
						<input type="text" placeholder="填写验证码" class="codeIput" v-model="captcha" />
						<button class="code" :disabled="disabled" :class="disabled === true ? 'on' : ''" @click="code">
							{{ text }}
						</button>
					</div>
				</div>
				<div class="item">
					<div class="acea-row row-middle">
						<image src="../static/code_2.png"></image>
						<input type="password" placeholder="填写您的新密码" v-model="password" />
					</div>
				</div>
				<div class="item" v-if="isShowCode">
					<div class="align-left">
						<input type="text" placeholder="填写验证码" class="codeIput" v-model="codeVal" />
						<div class="code" @click="again"><img :src="codeUrl" /></div>
					</div>
				</div>
			</div>
			<div class="logon" @click="registerReset">确认</div>
			<div class="tip">
				<span class="font-color-red" @click="back">立即登录</span>
			</div>
		</div>
		<div class="bottom"></div>
		</view>
		<Verify @success="success" :captchaType="'blockPuzzle'" :imgSize="{ width: '330px', height: '155px' }"
			ref="verify"></Verify>
	</div>
</template>

<script>
	import sendVerifyCode from "@/mixins/SendVerifyCode";
	import colors from '@/mixins/color.js';
	import Verify from '../components/verify/verify.vue';
	import {
		registerVerify,
		registerReset,
		getCodeApi
	} from "@/api/user";
	// import { validatorDefaultCatch } from "@/utils/dialog";
	// import attrs, { required, alpha_num, chs_phone } from "@utils/validate";
	// import { VUE_APP_API_URL } from "@utils";

	export default {
		name: "RetrievePassword",
		components: {
			Verify
		},
		data: function() {
			return {
				account: "",
				password: "",
				captcha: "",
				keyCode: "",
				codeUrl: "",
				codeVal: "",
				isShowCode: false
			};
		},
		mixins: [sendVerifyCode,colors],
		mounted: function() {
			this.getCode();
		},
		methods: {
			back() {
				uni.navigateBack();
			},
			again() {
				this.codeUrl =
					VUE_APP_API_URL + "/captcha?" + this.keyCode + Date.parse(new Date());
			},
			// getCode() {
			// 	getCodeApi()
			// 		.then(res => {
			// 			this.keyCode = res.data.key;
			// 		})
			// 		.catch(res => {
			// 			this.$util.Tips({
			// 				title: res.msg.msg || '加载失败'
			// 			})
			// 		});
			// },
			async registerReset() {
				var that = this;
				if (!that.account) return that.$util.Tips({
					title: '请填写手机号码'
				});
				if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(that.account)) return that.$util.Tips({
					title: '请输入正确的手机号码'
				});
				if (!that.captcha) return that.$util.Tips({
					title: '请填写验证码'
				});
				registerReset({
						account: that.account,
						captcha: that.captcha,
						password: that.password,
						code: that.codeVal
					})
					.then(res => {
						that.$util.Tips({
							title: res.msg
						}, {
							tab: 3
						})
					})
					.catch(res => {
						that.$util.Tips({
							title: res
						})
					});
			},
			success(data) {
				console.log(data,'data');
				this.$refs.verify.hide()
				getCodeApi()
					.then(res => {
						this.keyCode = res.data.key;
						this.getCode(data);
					})
					.catch(res => {
						this.$util.Tips({
							title: res
						});
					});
			},
			code(data) {
				let that = this;
				if (!that.account) return that.$util.Tips({
					title: '请填写手机号码'
				});
				if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(that.account)) return that.$util.Tips({
					title: '请输入正确的手机号码'
				});
				if (that.formItem == 2) that.type = "register";
				this.$refs.verify.show()
				// await registerVerify({
				// 		phone: that.account,
				// 		type: that.type,
				// 		key: that.keyCode,
				// 		code: that.codeVal
				// 	})
				// 	.then(res => {
				// 		this.$util.Tips({
				// 			title: res.msg || '加载失败'
				// 		})
				// 		that.sendCode();
				// 	})
				// 	.catch(res => {
				// 		that.$util.Tips({
				// 			title: res
				// 		});
				// 	});
			},
			async getCode(data){
				console.log('data-------',data);
				let that = this;
				await registerVerify({
						phone: that.account,
						type: that.type, 
						key: that.keyCode,
						captchaType: 'blockPuzzle',
						captchaVerification: data.captchaVerification,
					})
					.then(res => {
						that.$util.Tips({
							title: res.msg
						});
						that.sendCode();
					})
					.catch(res => {
						that.$util.Tips({
							title: res
						});
					});
			},
		}
	};
</script>
<style lang="scss" scoped>
	.register {
		width: 100%;
		height: 100vh;
		background-color: #fff;
	}

	.register .shading {
		background-image: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAu4AAAEeCAYAAADVZ5BOAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA4RpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMTQyIDc5LjE2MDkyNCwgMjAxNy8wNy8xMy0wMTowNjozOSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDozZDUwNjlmMy05NjI1LTRhZTItYjMwNC0yZDQ0ZTM3ZDFjNjgiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6MkQ4NDRBMEE3NzgzMTFFOTlDMEJDQjlDOUY3MzNFNTgiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6MkQ4NDRBMDk3NzgzMTFFOTlDMEJDQjlDOUY3MzNFNTgiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTggKFdpbmRvd3MpIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6MGIwNDc3MTAtZDZhYS00MzkzLWFhYTAtYzY5MTFmMmQ1ZmViIiBzdFJlZjpkb2N1bWVudElEPSJhZG9iZTpkb2NpZDpwaG90b3Nob3A6MTE1Yjg3ZjYtMjVmOS0xNjRkLWI1Mzgt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");
		background-repeat: no-repeat;
		background-size: 100% 100%;
		width: 100%;
		height: 286rpx;
		padding-top: 70rpx;
	}

	.register .shading .pictrue {
		width: 172rpx;
		height: 172rpx;
		border-radius: 50%;
		background-color: rgba(255, 255, 255, .8);
		margin: 0 auto
	}

	.register .shading .pictrue image {
		width: 164rpx;
		height: 164rpx;
		border-radius: 50%;
		display: block
	}

	.register .whiteBg {
		width: 620rpx;
		border-radius: 16rpx;
		background-color: #fff;
		margin: 0rpx auto 0 auto;
		padding: 200rpx 30rpx 0 30rpx
	}

	.register .whiteBg .title {
		font-size: 36rpx;
		color: #282828;
		text-align: center;
		font-weight: 700
	}

	.register .whiteBg .title .item~.item {
		margin-left: 85rpx
	}

	.register .whiteBg .title .item {
		color: #999;
		border-bottom: 5rpx solid #fff;
	}

	.register .whiteBg .title .item.on {
		color: #282828;
		border-bottom-color: #f35749
	}

	.register .whiteBg .list {
		margin-top: 80rpx;
	}

	.register .whiteBg .list .item {
		border-bottom: 1rpx solid #ededed;
		padding: 40rpx 0;
		position: relative;
		width: 100%;
		box-sizing: border-box;
	}

	.register .whiteBg .list .item .name {
		font-size: 26rpx;
		color: #2d3342;
		margin-bottom: 27rpx;
		text-align: left
	}

	.register .whiteBg .list .item image {
		width: 24rpx;
		height: 28rpx;
		display: block
	}

	.register .whiteBg .list .item input {
		font-size: 32rpx;
		flex: 1;
		margin-left: 20rpx
	}

	.register .whiteBg .list .item .placeholder {
		color: #ccc
	}

	.register .whiteBg .list .item .codeIput {
		width: 250rpx;
		margin-left: 20rpx
	}

	.register .whiteBg .list .item .code {
		position: absolute;
		color: var(--view-theme);
		line-height: 50rpx;
		text-align: center;
		bottom: 38rpx;
		background-color: #fff;
		right: 0;
		font-size: 26rpx
	}

	.register .whiteBg .list .item .code.on {
		background-color: #fff;
	}

	.register .whiteBg .list .forgetPwd {
		text-align: right;
		font-size: 28rpx;
		color: #666;
		margin-top: 20rpx
	}

	.register .whiteBg .list .forgetPwd .iconfont {
		font-size: 30rpx;
		margin-right: 10rpx;
		vertical-align: middle
	}

	.register .whiteBg .logon {
		font-size: 34rpx;
		color: #fff;
		background: linear-gradient(135deg, var(--view-minorColor) 0%, var(--view-theme) 100%);
		height: 86rpx;
		border-radius: 43rpx;
		text-align: center;
		line-height: 86rpx;
		margin-top: 47rpx
	}

	.register .whiteBg .tip {
		height: 110rpx;
		text-align: center;
		line-height: 105rpx;
		font-size: 30rpx;
		color: #ccc
	}

	.register .bottom {
		background-image: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAApQAAAAkCAYAAAA3mqpgAAAACXBIWXMAAAsTAAALEwEAmpwYAAAHDmlUWHRYTUw6Y29tLmFkb2JlLnhtcAAAAAAAPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4gPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iQWRvYmUgWE1QIENvcmUgNS42LWMxNDUgNzkuMTYzNDk5LCAyMDE4LzA4LzEzLTE2OjQwOjIyICAgICAgICAiPiA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPiA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtbG5zOnN0RXZ0PSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VFdmVudCMiIHhtbG5zOnhtcD0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLyIgeG1sbnM6ZGM9Imh0dHA6Ly9wdXJsLm9yZy9kYy9lbGVtZW50cy8xLjEvIiB4bWxuczpwaG90b3Nob3A9Imh0dHA6Ly9ucy5hZG9iZS5jb20vcGhvdG9zaG9wLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDozZDUwNjlmMy05NjI1LTRhZTItYjMwNC0yZDQ0ZTM3ZDFjNjgiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6REE5RjQyRjk3NzgzMTFFOTg0Q0Q5ODE4RDgwOTI2MTQiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6Nzc0OWQzMmItNmYzYS00Yzg3LWJhMDUtN2VjNWZhNTUxMzUzIiB4bXA6Q3JlYXRvclRvb2w9IkFkb2JlIFBob3Rvc2hvcCBDQyAyMDE5IChNYWNpbnRvc2gpIiB4bXA6Q3JlYXRlRGF0ZT0iMjAxOS0wOS0yNlQxNjo0MjowNCswODowMCIgeG1wOk1vZGlmeURhdGU9IjIwMTktMDktMjZUMTY6NTE6MzArMDg6MDAiIHhtcDpNZXRhZGF0YURhdGU9IjIwMTktMDktMjZUMTY6NTE6MzArMDg6MDAiIGRjOmZvcm1hdD0iaW1hZ2UvcG5nIiBwaG90b3Nob3A6Q29sb3JNb2RlPSIzIiBwaG90b3Nob3A6SUNDUHJvZmlsZT0ic1JHQiBJRUM2MTk2Ni0yLjEiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDowYjA0NzcxMC1kNmFhLTQzOTMtYWFhMC1jNjkxMWYyZDVmZWIiIHN0UmVmOmRvY3VtZW50SUQ9ImFkb2JlOmRvY2lkOnBob3Rvc2hvcDoxMTViODdmNi0yNWY5LTE2NGQtYjUzOC02YzViOTc2NzVjOTkiLz4gPHhtcE1NOkhpc3Rvcnk+IDxyZGY6U2VxPiA8cmRmOmxpIHN0RXZ0OmFjdGlvbj0ic2F2ZWQiIHN0RXZ0Omluc3RhbmNlSUQ9InhtcC5paWQ6Nzc0OWQzMmItNmYzYS00Yzg3LWJhMDUtN2VjNWZhNTUxMzUzIiBzdEV2dDp3aGVuPSIyMDE5LTA5LTI2VDE2OjUxOjMwKzA4OjAwIiBzdEV2dDpzb2Z0d2FyZUFnZW50PSJBZG9iZSBQaG90b3Nob3AgQ0MgMjAxOSAoTWFjaW50b3NoKSIgc3RFdnQ6Y2hhbmdlZD0iLyIvPiA8L3JkZjpTZXE+IDwveG1wTU06SGlzdG9yeT4gPHBob3Rvc2hvcDpEb2N1bWVudEFuY2VzdG9ycz4gPHJkZjpCYWc+IDxyZGY6bGk+MDc2MjMyOThCRjE4MjVGODcxNjcyODg4Mzk2M0VGQkM8L3JkZjpsaT4gPHJkZjpsaT5FMzYyMjRBRjBFMDAyNzI2RThGQzJFRDUxQzZGRDdBRjwvcmRmOmxpPiA8cmRmOmxpPmFkb2JlOmRvY2lkOnBob3Rvc2hvcDoxMTViODdmNi0yNWY5LTE2NGQtYjUzOC02YzViOTc2NzVjOTk8L3JkZjpsaT4gPC9yZGY6QmFnPiA8L3Bob3Rvc2hvcDpEb2N1bWVudEFuY2VzdG9ycz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz47UTtEAAADMklEQVR4nO3dv2+NURzH8Xf9ih+V0NFAIhKLxCIsJpPZSCLCYrHyN/gPMBF0EZ0xlgja0dCm2kGjE60o2quXx3DcBE3p7XPOc25P368/4Hu+yR3OJ/c853z7qqoistvAkdhFJUmSFMVr4ELMgptiFvtlIkFNSZIkxRE9q6UIlCMJakqSJCmO6FktRaB8BiwkqCtJkqR6FglZLaoUgfIr8CRBXUmSJNXzmJDVokoRKCFczGknqi1JkqTutYE7KQqnCpTTwGCi2pIkSereIPA2ReFUgRLgJt74liRJ6gUTwK1UxVMGyhZwFZhLuIYkSZL+bY6QyRZTLZAyUEI4+r4CzCZeR5IkScvNErLYdMpFUgdKgDHgEh5/S5IkNWmCkMHGUi/Ul2D04kq2AZeBs8CWphaVJEnaYNqECzg3gG9NLNhkoOzYD5wHTgPbm15ckiSpUC3gEeFpoCS3uVeSI1B27AROAseAw8A+YDf+eylJkvQ/bWAemAHGgVHCBJzoj5avRs5AGdt1YE/uJiRJklbpI3AtdxMxNHEppykzuRuQJEnqwrvcDcRSUqBMfoNJkiQpovHcDcRSUqAcBYo5v5ckSUWrCNmlCCUFyg8U9MNIkqSijRKySxFKCpQAQ4Qr85IkSb2qRcgsxSgtUM4Cd/HoW5Ik9aaKkFWKGktdWqAEGAEeYKiUJEm9pQIeErJKUUp6h/JvR4GLOI1HkiTl1wLuAa9yN5JCyYESYAA4Q5jG05e5F0mStPF0bnMPUdgx9+9KD5QdA8Bx/hzxuDlrR5IkqUTfWT4S8X3WjhqwUQJlSueAXbmbkCRJa/YFuJ+7ifWsxEs5TZvL3YAkSarFvbwmA2V9zhCXJGl9cy+vyUBZ3yQ+USRJ0npVEfZy1WCgrG8emMrdhCRJWpMpwl6uGgyUcbwElnI3IUmSurJE2MNVk4Eyjs/AcO4mJElSV4YJe7hqMlDGMwk8x+8pJUlaD17gt5PR+A5lfAeAU8DW3I1IkqRlloCnwJvcjZTEQJlGP3ACOIgjHyVJ6gUV4QLOSzzmjs5AmVY/cIgw7nEvsAM/M5AkqQk/gAXCo+UzhDD5KWtHBfsJqQ6h7m5gtScAAAAASUVORK5CYII=");
		background-repeat: no-repeat;
		background-size: 100% 100%;
		width: 620rpx;
		height: 36rpx;
		margin: 0 auto;
	}

	// 首页修改轮播内部样式；
	.code img {
		width: 100%;
		height: 100%;
	}
</style>
