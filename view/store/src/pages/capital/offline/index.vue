<template>
	<div>
		<Card :bordered="false" dis-hover class="ivu-mt box">
			<Form ref="formValidate" :model="formValidate" :label-width="labelWidth" :label-position="labelPosition"
				@submit.native.prevent>
				<Row>
					<Col class="mr">
					<FormItem label="创建时间：">
						<DatePicker :editable="false" :clearable="true" @on-change="onchangeTime" :value="timeVal"
							format="yyyy/MM/dd" type="datetimerange" placement="bottom-start" placeholder="自定义时间"
							style="width: 200px" :options="options">
						</DatePicker>
						<!-- <RadioGroup
				    v-model="formValidate.data"
				    type="button"
				    @on-change="selectChange(formValidate.data)"
					style="margin-right: 20px"
				  >
				    <Radio
				      :label="item.val"
				      v-for="(item, i) in fromList.fromTxt"
				      :key="i"
				      >{{ item.text }}</Radio
				    >
				  </RadioGroup>
				  <DatePicker
				    :editable="false"
				    @on-change="onchangeTime"
				    :value="timeVal"
				    format="yyyy/MM/dd"
				    type="daterange"
				    placement="bottom-end"
				    placeholder="自定义时间"
				    style="width: 200px"
				  ></DatePicker> -->
					</FormItem>
					</Col>
					<Col class="mr">
					<FormItem label="订单搜索：" label-for="status1">
						<Input v-model="formValidate.keywork" placeholder="请输入交易单号/交易人" class="input"
							style="width: 200px"></Input>
					</FormItem>
					</Col>
					<Col class="mr">
					<div class="search" @click="search">搜索</div>
					</Col>
					<Col>
					<div class="reset" @click="reset">重置</div>
					</Col>
					<Col>
					<div class="reset" style="margin-left: 10px;" @click="exportOfflineList">导出</div>
					</Col>
				</Row>
			</Form>
		</Card>

		<Card :bordered="false" dis-hover class="ive-mt tablebox">
			<div class="btnbox">
				<span style="margin-right: 10px;">总金额：<span style="color: red;">￥{{ heji }}</span></span>
				<span style="margin-right: 10px;">应收总金额：<span style="color: red;">￥{{ totalPrice }}</span></span>
				<span style="margin-right: 10px;">退款总金额：<span style="color: red;">￥{{ refundPrice }}</span></span>
			</div>
			<div class="table">
				<Table :columns="columns" :data="orderList" ref="table" class="mt25" :loading="loading" highlight-row
					no-userFrom-text="暂无数据" no-filtered-userFrom-text="暂无筛选结果">
					<template slot-scope="{ row, index }" slot="number">
						<!-- <span class="color">{{row.number}}</span> -->
						<span v-if="row.pm == 0" class="colorgreen">- {{ row.number }}</span>
						<span v-if="row.pm == 1" class="colorred">+ {{ row.number }}</span>
					</template>
					<template slot-scope="{ row, index }" slot="user_nickname">
						<span>{{ row.uid ? row.user_nickname : '游客' }}</span>
					</template>
					<template slot-scope="{ row, index }" slot="action">
						<a @click="refund(row)" v-if="row.status == 1">退款</a>
						<span style="color: #888;" v-if="row.status == 2">已退款</span>

					</template>
					<template slot-scope="{ row, index }" slot="mark">
						<Tooltip max-width="300" placement="bottom">
							<span class="line2">{{ row.mark }}</span>
							<p slot="content">{{ row.mark }}</p>
						</Tooltip>
					</template>
				</Table>
			</div>
			<div class="acea-row row-right page">
				<Page :total="total" :current="formValidate.page" show-elevator show-total @on-change="pageChange"
					:page-size="formValidate.limit" />
			</div>
		</Card>
		<!-- 备注 -->
		<Modal v-model="modalmark" scrollable title="退款请谨慎操作" class="order_box" :closable="false" :mask-closable="false">
			<Form ref="remarks" :model="remarks" :label-width="80" @submit.native.prevent>
				<FormItem label="备注：">
					<Input v-model="remarks.mark" maxlength="200" show-word-limit type="textarea" placeholder="请填写备注~"
						style="width: 100%" />
				</FormItem>
			</Form>
			<div slot="footer">
				<Button type="primary" @click="putRemark()">提交</Button>
				<Button @click="cancel()">取消</Button>
			</div>
		</Modal>
	</div>
</template>

<script>
import { mapState } from 'vuex';
import { getOfflineList, offlineRefund } from "@/api/capital"
import timeOptions from "@/utils/timeOptions2"
import exportExcel from '@/utils/newToExcel.js'

export default {
	name: 'order',
	data() {
		return {
			modalmark: false,
			remarks: {
				mark: ''
			},
			staff: [],
			total: 0,
			grid: {
				xl: 7,
				lg: 7,
				md: 12,
				sm: 24,
				xs: 24
			},
			options: timeOptions,
			loading: false,
			columns: [
				{
					title: '交易单号',
					key: 'order_id',
					minWidth: 180
				},
				{
					title: '交易时间',
					key: 'create_time',
					minWidth: 150
				},

				{
					title: '原金额',
					key: 'price',
					minWidth: 80
				},
				{
					title: '折扣',
					key: 'discount',
					minWidth: 80
				},
				{
					title: '支付金额',
					key: 'actual_price',
					minWidth: 80
				},
				{
					title: '用户ID',
					key: 'user_id',
					minWidth: 80
				},
				{
					title: '交易人',
					key: 'user_real_name',
					ellipsis: true,
					minWidth: 80
				},
				{
					title: '手机号',
					key: 'phone',
					ellipsis: true,
					minWidth: 80
				},
				{
					title: '退款时间',
					key: 'refund_time',
					ellipsis: true,
					minWidth: 80
				},
				{
					title: '备注',
					key: 'remarks',
					ellipsis: true,
					minWidth: 80
				},
				{
					title: '操作',
					slot: 'action',
					fixed: 'right',
					minWidth: 120,
					align: 'center'
				}
			],
			orderList: [],
			formValidate: {
				keywork: "",
				data: "",
				page: 1,
				limit: 20,
			},
			timeVal: [],
			fromList: {
				title: "选择时间",
				custom: true,
				fromTxt: [
					{ text: "全部", val: "" },
					{ text: "昨天", val: "yesterday" },
					{ text: "今天", val: "today" },
					{ text: "本周", val: "week" },
					{ text: "本月", val: "month" },
					{ text: "本季度", val: "quarter" },
					{ text: "本年", val: "year" },
				],
			},
			totalPrice: '0.00',
			refundPrice: '0.00',
			heji: '0.00'
		}
	},
	computed: {
		...mapState('store/layout', [
			'isMobile'
		]),
		labelWidth() {
			return this.isMobile ? undefined : 80;
		},
		labelPosition() {
			return this.isMobile ? "top" : "left";
		},
	},
	mounted() {
		this.getList()
		this.staffApi()
		// 本季度日期测试
		//   var theMonth = nowMonth + 1;
	},
	methods: {
		async exportOfflineList() {
			let [th, filekey, data] = [
				['交易单号', '交易时间', '	原金额', '折扣', '支付金额', '交易人','状态', '退款时间', '备注'],
				['order_id', 'create_time', 'price', 'discount', 'actual_price', 'user_real_name','status_text', 'refund_time', 'remarks'],
				[]
			]

			let fileName = this.getymd();
			let excelData = JSON.parse(JSON.stringify(this.formValidate))
			excelData.page = 1
			for (let i = 0; i < excelData.page + 1; i++) {
				let lebData = (await getOfflineList({
					...excelData,
				})).data

				if (lebData.list.length) {
					data = data.concat(lebData.list)

					excelData.page++
				} else {
					exportExcel(th, filekey, fileName, data)
					return
				}
			}
		},

		getymd() {
			const currentTime = new Date();
			const year = currentTime.getFullYear();
			const month = currentTime.getMonth() + 1; // 月份从0开始，因此需要加1
			const day = currentTime.getDate();
			const hours = currentTime.getHours();
			const minutes = currentTime.getMinutes();
			const seconds = currentTime.getSeconds();
			console.log(year, month, day, hours, minutes, seconds);
			return year.toString() + month.toString() + day.toString() + hours.toString() + minutes.toString() + seconds.toString()
		},


		getList() {
			this.loading = true
			getOfflineList(this.formValidate).then(res => {
				this.orderList = res.data.list
				this.total = res.data.count
				this.totalPrice = res.data.totalPrice
				this.refundPrice = res.data.refundPrice
				this.heji = res.data.heji

				this.loading = false
			})
		},
		staffApi() {
			storeFinanceflowApi().then(res => {
				this.staff = res.data
			})
		},
		searchs() {
			this.getList()
		},
		search() {
			this.formValidate.page = 1
			this.getList()
		},
		reset() {
			this.formValidate = {
				staff_id: '',
				keywork: "",
				data: "",
				page: 1,
				limit: 15,
			}
			this.timeVal = [];
			this.getList()
		},
		// 选择时间
		selectChange(tab) {

			this.formValidate.page = 1;
			this.formValidate.data = tab;
			this.timeVal = [];
			this.getList();
		},
		// 具体日期
		onchangeTime(e) {
			console.log(e)
			this.timeVal = e;
			this.formValidate.data = this.timeVal[0] ? this.timeVal.join("-") : "";
			this.formValidate.page = 1;
			this.getList();
		},
		//分页
		pageChange(status) {
			this.formValidate.page = status;
			this.getList()
		},
		refund(e) {
			this.remarkId = e.id
			this.modalmark = true;
		},
		//退款的提交
		putRemark() {
			this.modalmark = false
			let params = { id: this.remarkId, remarks: this.remarks.mark };
			offlineRefund(params).then(res => {
				this.$Message.success(res.msg)
				this.remarks = { mark: '' }
				this.modalmark = false
				this.getList()
			}).catch(err => {
				this.$Message.error(err.msg)
				this.modalmark = false
			})
		},
		// 取消备注按钮
		cancel() {
			this.remarks = { mark: '' }
			this.modalmark = false
		}
	},
}
</script>

<style scoped lang="less">
/deep/.ivu-page-header,
/deep/.ivu-tabs-bar {
	border-bottom: 1px solid #ffffff;
}

/deep/.ivu-card-body {
	padding: 0;
}

/deep/.ivu-tabs-nav {
	height: 45px;
}

/deep/.ivu-form-label-left .ivu-form-item-label {
	text-align: right;
}

.page {
	padding-right: 30px;
	padding-bottom: 10px;
}

.box {
	padding: 20px;
	padding-bottom: 0px;
}

.tablebox {
	margin-top: 15px;
	padding-bottom: 10px;
}

.btnbox {
	padding: 20px 0px 0px 30px;

	.btns {
		width: 99px;
		height: 32px;
		background: #1890FF;
		border-radius: 4px;
		text-align: center;
		line-height: 32px;
		color: #FFFFFF;
		cursor: pointer;
	}
}

.table {
	padding: 0px 30px 15px 30px;
}

.colorred {
	color: #ff5722;
}

.colorgreen {
	color: #009688;
}

.search {
	width: 86px;
	height: 32px;
	background: #1890FF;
	border-radius: 4px;
	text-align: center;
	line-height: 32px;
	font-size: 13px;
	font-family: PingFangSC-Regular, PingFang SC;
	font-weight: 400;
	color: #FFFFFF;
	cursor: pointer;
}

.reset {
	width: 86px;
	height: 32px;
	border-radius: 4px;
	border: 1px solid rgba(151, 151, 151, 0.36);
	text-align: center;
	line-height: 32px;
	font-size: 13px;
	font-family: PingFangSC-Regular, PingFang SC;
	font-weight: 400;
	color: rgba(0, 0, 0, 0.85);
	cursor: pointer;
}
</style>
