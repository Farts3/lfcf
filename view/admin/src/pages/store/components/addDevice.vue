<template>
	<div>
		<Modal v-model="isTemplate" scrollable footer-hide closable :title="title" :z-index="1" width="700"
			@on-cancel="cancel">
			<div class="article-manager">
				<Card :bordered="false" dis-hover>
					<Form ref="formItem" :model="formItem" :label-width="labelWidth" :label-position="labelPosition"
						@submit.native.prevent>
						<Row type="flex" :gutter="24">
							<Col span="24">
							<Col v-bind="grid">
							<FormItem label="门店名称：" prop="store_id" label-for="store_id">
								<Cascader :data="treeSelect" placeholder="请选择门店" change-on-select
									v-model="formItem.store_id" filterable></Cascader>
							</FormItem>
							</Col>
							</Col>


							<Col span="24">
								<Col v-bind="grid">
									<FormItem label="语音播报设备ID：" prop="device_id" label-for="device_id">
										<Input v-model="formItem.device_id" maxlength="20" show-word-limit placeholder="请输入设备ID" />
									</FormItem>
								</Col>
							</Col>

							<!-- 飞鹅云打印机 -->
							<Col span="24">
								<Col v-bind="grid">
									<FormItem label="打印机编号SN：" prop="device_id" label-for="device_id">
										<Input v-model="formItem.feie_sn" maxlength="20" show-word-limit placeholder="请输入打印机编号SN" />
									</FormItem>
								</Col>
							</Col>
							<Col span="24">
								<Col v-bind="grid">
									<FormItem label="打印机密钥KEY：" prop="device_id" label-for="device_id">
										<Input v-model="formItem.feie_key" maxlength="20" show-word-limit placeholder="请输入打印机密钥KEY" />
									</FormItem>
								</Col>
							</Col>

							<Col span="24">
								<Col v-bind="grid">
									<FormItem label="备注：" >
										<Input v-model="formItem.remark" maxlength="255" show-word-limit placeholder="请输入备注" />
									</FormItem>
								</Col>
							</Col>

						</Row>
						<Row style="justify-content: space-around;">
							<Col>
							<Button type="primary" class="btn" @click="handleSubmit('formItem')">{{ formItem.id != 0 ? '修改'
								: '提交' }}</Button>
							</Col>
						</Row>

					</Form>
				</Card>
			</div>
		</Modal>

	</div>
</template>

<script>
// import { keyApi} from '@/api/setting';

import { saveDevice } from '@/api/store';

import {
	storeListApi,
} from "@/api/store";
import { mapState } from 'vuex';
export default {
	name: 'systemStore',
	components: {},
	props: {},
	data() {
		return {
			modals: false,
			treeSelect: [

			],
			isTemplate: false,
			title: '',
			formItem: {
				device_id: '',
				store_id: [],
				id: 0,
				remark:'',
				feie_key:'',
				feie_sn:''
			},
			grid: {
				xl: 20,
				lg: 20,
				md: 20,
				sm: 24,
				xs: 24
			},
			add: 0
		}
	},
	created() {
		storeListApi({ page: 1, limit: 999999 }).then((res) => {
			let storeList = res.data.list;

			storeList.forEach(d => {
				this.treeSelect.push({ value: d.id, label: d.name })
			})

		});
	},
	computed: {
		...mapState('admin/layout', [
			'isMobile'
		]),
		labelWidth() {
			return this.isMobile ? undefined : 120;
		},
		labelPosition() {
			return this.isMobile ? 'top' : 'right';
		}
	},
	mounted: function () { },
	methods: {
		clearFrom() {

			this.formItem = {
				store_id: [],
				id: 0,
				device_id: '',
			}
			this.add = 0;
		},
		cancel() {
			this.isTemplate = false;
			// this.$refs['formItem'].resetFields();
			this.clearFrom();
		},


		// 提交
		handleSubmit() {
			let that = this;
			let storeInfo = this.treeSelect.find( item => {
				return item.value == that.formItem.store_id[0]
			})
			
			if(this.formItem.store_id.length < 1) {
				this.$Message.error('请选择门店');
				return;
			}

			if(this.formItem.device_id.length < 1) {
				this.$Message.error('请输入设备ID');
				return;
			}

			let params = {
				store_id: storeInfo.value,
				device_id: this.formItem.device_id,
				id:this.formItem.id,
				store_name: storeInfo.label,
				remark:this.formItem.remark,
				feie_key:this.formItem.feie_key,
				feie_sn:this.formItem.feie_sn,

			}
			
			saveDevice(this.formItem.id, params).then( res => {
				this.$Message.success(res.msg);
				this.isTemplate = false;
				this.$parent.getList();
				// this.$refs[name].resetFields();
				this.clearFrom();
			}).catch(res => {
				this.$Message.error(res.msg);
			})

		}
	}
}
</script>

<style scoped lang="stylus">
.tips {
  display: inline-bolck;
  font-size: 12px;
  font-weight: 400;
  color: #999;
}
.box{
  display flex
  flex-wrap wrap
  .box-item{
    position relative
    margin-right 20px
    width 60px
    height 60px
    margin-bottom 10px
    img{
      width 100%
      height 100%
    }
    .icon{
      position absolute;
      top:-10px;
      right -10px;
    }
  }
  .upload-box{
    width 60px
    height 60px
    margin-bottom 10px
    display flex
    align-items center
    justify-content center
    background #ccc
  }
}
	.map-sty {
		width: 90%;
		text-align: right;
		margin: 0 0 0 10%;
	}
	/deep/.ivu-card-body{
		padding 16px 0 0 0!important;
	}
	.footer{
		width 100%;
		height 50px;
		box-shadow: 0px -2px 4px 0px rgba(0, 0, 0, 0.05);
		margin-top 50px;
	}
.btn /deep/.ivu-btn-primary{
		width 86px;
	}
	.btn{
		margin-top: 20px;
	}
	.inputW{
		width 400px;
	}
	.ivu-mt{
		min-width 580px;
	}
	.picBox
		display: inline-block;
		cursor: pointer;
		.upLoad
			width: 58px;
			height: 58px;
			line-height: 58px;
			border: 1px dotted rgba(0, 0, 0, 0.1);
			border-radius: 4px;
			background: rgba(0, 0, 0, 0.02);
		.pictrue
			width: 60px;
			height: 60px;
			border: 1px dotted rgba(0, 0, 0, 0.1);
			margin-right: 10px;
			img
				width: 100%;
				height: 100%;
		.iconfont
			color: #CCCCCC;
			font-size 26px;
			text-align center
			
</style>
