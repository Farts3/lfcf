<template>
  <div>
    <Card :bordered="false" dis-hover class="ivu-mt" :padding="0">
      <div class="new_card_pd">
        <!-- 查询条件 -->
        <Form ref="formValidate" :model="formValidate" :label-width="labelWidth" inline :label-position="labelPosition" class="tabform" @submit.native.prevent>
          <FormItem label="时间选择：">
            <DatePicker
              :editable="false"
              @on-change="onchangeTime"
              :value="timeVal"
              format="yyyy/MM/dd HH:mm"
              type="datetimerange"
              placement="bottom-start"
              placeholder="自定义时间"
              class="input-width"
              :options="options"
            ></DatePicker>
          </FormItem>

          <FormItem label="类型：">
            <Select v-model="formValidate.paid" class="input-add">
              <Option value="">全部</Option>
              <Option v-for="item in cardLists" :key="item.type" :value="item.type">{{ item.name }}</Option>
            </Select>
          </FormItem>
          <FormItem label="搜索：">
            <Input @on-search="selChange" placeholder="请输入用户手机号" element-id="name" v-model="formValidate.phone" class="mr input-add" />
            <Button type="primary" @click="orderSearch()" class="mr">查询</Button>
            <Button v-auth="['export-userRecharge']" @click="getExports">导出</Button>
          </FormItem>
        </Form>
      </div>
    </Card>
    <cards-data :cardLists="cardLists.map(i => _omit(i, ['type']))" v-if="cardLists.length >= 0"></cards-data>
    <Card :bordered="false" dis-hover>
      <!-- 表格 -->
      <Table ref="table" :columns="columns" :data="tabList" class="ivu-mt" :loading="loading" no-data-text="暂无数据" no-filtered-data-text="暂无筛选结果"></Table>
      <div class="acea-row row-right page">
        <Page :total="total" :current="formValidate.page" show-elevator show-total @on-change="pageChange" :page-size="formValidate.limit" />
      </div>
    </Card>
  </div>
</template>
<script>
import cardsData from '@/components/cards/cards'
import searchFrom from '@/components/publicSearchFrom'
import { mapState } from 'vuex'
import { balanceChangeRecordlistApi, userBalanceChangeRecordApi, getExportUserMoneyList } from '@/api/finance'
import editFrom from '@/components/from/from'
import timeOptions from '@/utils/timeOptions'
import _omit from 'lodash/omit'
import exportExcel from '@/utils/newToExcel.js'

export default {
  name: 'BalanceChangeRecord',
  components: { cardsData, searchFrom, editFrom },
  data() {
    return {
      formValidate: {
        where_time: '',
        paid: '',
        phone: '',
        excel: 0,
        page: 1,
        limit: 20
      },
      total: 0,
      cardLists: [],
      loading: false,
      columns: [
        {
          title: 'ID',
          // key: 'id',
          render: (h, { index }) => h('div', index + 1 + (this.formValidate.page - 1) * this.formValidate.limit),
          width: 80
        },
        {
          title: '用户昵称',
          render: (h, { row }) => h('div', row.user ? row.user.nickname : ''),
          minWidth: 150
        },
        {
          title: '电话号码',
          render: (h, { row }) => h('div', row.user ? row.user.phone : ''),
          minWidth: 150
        },
        {
          title: '类型',
          key: 'type_title',
          minWidth: 150
        },
        {
          title: '变动金额',
          key: 'number',
          minWidth: 150
        },
        {
          title: '变动后',
          key: 'balance',
          minWidth: 150
        },
        {
          title: '创建时间',
          key: 'add_time',
          minWidth: 150
        },
        {
          title: '备注',
          key: 'mark',
          minWidth: 150
        }
      ],
      tabList: [],
      options: timeOptions,
      timeVal: []
    }
  },
  computed: {
    ...mapState('admin/layout', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : 80
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right'
    }
  },
  mounted() {
    this.getList()
    this.getUserRecharge()
  },
  methods: {
    _omit,
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e
      this.formValidate.where_time = this.timeVal[0] ? this.timeVal.join('-') : ''
      this.formValidate.page = 1
      this.getList()
      this.getUserRecharge()
    },
    // 选择时间
    selectChange(tab) {
      this.formValidate.where_time = tab
      this.timeVal = []
      this.formValidate.page = 1
      this.getList()
      this.getUserRecharge()
    },
    // 选择
    selChange() {
      this.formValidate.page = 1
      this.getList()
      this.getUserRecharge()
    },
    // 列表
    getList() {
      this.loading = true
      balanceChangeRecordlistApi(this.formValidate)
        .then(async res => {
          let data = res.data
          this.tabList = data.list
          this.total = data.count
          this.loading = false
        })
        .catch(res => {
          this.loading = false
          this.$Message.error(res.msg)
        })
    },
    // 搜索
    orderSearch() {
      this.formValidate.page = 1
      this.getList()
    },
    pageChange(index) {
      this.formValidate.page = index
      this.getList()
    },
    // 小方块
    getUserRecharge() {
      userBalanceChangeRecordApi(this.formValidate)
        .then(async res => {
          let data = res.data
          this.cardLists = data
        })
        .catch(res => {
          this.$Message.error(res.msg)
        })
    },

    // 数据导出；
    async getExports() {
      let [th, filekey, data] = [[], [], []]
      let fileName = ''
      let excelData = JSON.parse(JSON.stringify(this.formValidate))
      excelData.page = 1
      for (let i = 0; i < excelData.page + 1; i++) {
        let lebData = (await getExportUserMoneyList(excelData)).data
        if (!fileName) fileName = lebData.filename
        if (!filekey.length) {
          filekey = lebData.filekey
        }
        if (!th.length) th = lebData.header
        if (lebData.export.length) {
          data = data.concat(lebData.export)
          excelData.page++
        } else {
          exportExcel(th, filekey, fileName, data)
          return
        }
      }
    }
  }
}
</script>
<style scoped lang="stylus">

	/deep/.ivu-col-span-xl-6 {
	    display: block;
	    flex: 0 0 25%;
	    max-width: 20%;
	}
.input-add {
width: 250px;
margin-right: 14px;
display: inline-table;
}
.ivu-mt .type .item {
  margin: 3px 0;
}

.tabform {
  margin-bottom: 10px;
}

.Refresh {
  font-size: 12px;
  color: #1890FF;
  cursor: pointer;
}

.ivu-form-item {
  margin-bottom: 10px;
}

.status >>> .item~.item {
  margin-left: 6px;
}

.status >>> .statusVal {
  margin-bottom: 7px;
}

/* .ivu-mt >>> .ivu-table-header */
/* border-top:1px dashed #ddd!important */
</style>
