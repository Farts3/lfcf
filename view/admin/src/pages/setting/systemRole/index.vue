<template>
<!-- 角色管理 -->
  <div>
    <Card :bordered="false" dis-hover class="ivu-mt" :padding="0">
      <div class="new_card_pd">
        <Form
          ref="formValidate"
          :model="formValidate"
          inline
          :label-width="labelWidth"
          :label-position="labelPosition"
          @submit.native.prevent
        >
          <FormItem label="状态：" label-for="status">
            <Select
              v-model="formValidate.status"
              placeholder="请选择"
              clearable
              class="input-add"
            >
              <Option value="1">显示</Option>
              <Option value="0">不显示</Option>
            </Select>
          </FormItem>
          <FormItem label="身份昵称：" label-for="role_name">
            <Input
              placeholder="请输入身份昵称"
              v-model="formValidate.role_name"
              class="input-add mr14"
            />
            <Button type="primary" @click="userSearchs()">查询</Button>
          </FormItem>
        </Form>
      </div>
    </Card>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <Button
        v-auth="['setting-system_role-add']"
        type="primary"
        @click="add('添加')"
        >添加身份</Button
      >
      <Table
        :columns="columns1"
        :data="tableList"
        ref="table"
        class="mt25"
        :loading="loading"
        highlight-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <template slot-scope="{ row, index }" slot="is_shows">
          <i-switch
            v-model="row.status"
            :value="row.status"
            :true-value="1"
            :false-value="0"
            @on-change="onchangeIsShow(row)"
            size="large"
          >
            <span slot="open">显示</span>
            <span slot="close">隐藏</span>
          </i-switch>
        </template>
        <template slot-scope="{ row, index }" slot="action">
          <a @click="edit(row, '编辑')">编辑</a>
          <Divider type="vertical" />
          <a @click="del(row, '删除', index)">删除</a>
        </template>
      </Table>
      <div class="acea-row row-right page">
        <Page
          :total="total"
          :current="formValidate.page"
          show-elevator
          show-total
          @on-change="pageChange"
          :page-size="formValidate.limit"
        />
      </div>
    </Card>
    <!-- 新增编辑-->
    <Modal
      v-model="modals"
      @on-cancel="onCancel"
      scrollable
      footer-hide
      closable
      :title="`${modelTit}身份`"
      :mask-closable="false"
      width="600"
    >
      <Form
        ref="formInline"
        :model="formInline"
        :rules="ruleValidate"
        :label-width="100"
        :label-position="labelPosition2"
        @submit.native.prevent
      >
        <FormItem label="身份名称：" label-for="role_name" prop="role_name">
          <Input placeholder="请输入身份昵称" v-model="formInline.role_name" />
        </FormItem>
        <FormItem label="是否开启：" prop="status">
          <RadioGroup v-model="formInline.status">
            <Radio :label="1">开启</Radio>
            <Radio :label="0">关闭</Radio>
          </RadioGroup>
        </FormItem>
        <FormItem label="权限：">
          <div class="trees-coadd">
            <div class="scollhide">
              <div class="iconlist">
                <Tree :data="menusList" show-checkbox ref="tree"></Tree>
              </div>
            </div>
          </div>
        </FormItem>
        <Spin size="large" fix v-if="spinShow"></Spin>
        <Button
          type="primary"
          size="large"
          long
          @click="handleSubmit('formInline')"
          >提交</Button
        >
      </Form>
    </Modal>
  </div>
</template>
<script>
import { mapState } from 'vuex'
import {
  roleListApi,
  roleSetStatusApi,
  menusListApi,
  roleCreatApi,
  roleInfoApi,
} from '@/api/setting'
export default {
  name: 'systemrRole',
  data() {
    return {
      spinShow: false,
      modals: false,
      total: 0,
      grid: {
        xl: 7,
        lg: 7,
        md: 12,
        sm: 24,
        xs: 24,
      },
      loading: false,
      formValidate: {
        status: '',
        role_name: '',
        page: 1,
        limit: 20,
      },
      columns1: [
        {
          title: 'ID',
          key: 'id',
          width: 80,
        },
        {
          title: '身份昵称',
          key: 'role_name',
          minWidth: 120,
        },
        {
          title: '权限',
          key: 'rules',
          tooltip: true,
          width: 1000,
        },
        {
          title: '状态',
          slot: 'is_shows',
          minWidth: 120,
        },
        {
          title: '操作',
          slot: 'action',
          fixed: 'right',
          minWidth: 120,
        },
      ],
      tableList: [],
      formInline: {
        role_name: '',
        status: 0,
        checked_menus: [],
        id: 0,
      },
      menusList: [],
      modelTit: '',
      ruleValidate: {
        role_name: [
          { required: true, message: '请输入身份昵称', trigger: 'blur' },
        ],
        status: [
          {
            required: true,
            type: 'number',
            message: '请选择是否开启',
            trigger: 'change',
          },
        ],
        // checked_menus: [
        //     { required: true, validator: validateStatus, trigger: 'change' }
        // ]
      },
    }
  },
  computed: {
    ...mapState('admin/layout', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : 96
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right'
    },
    labelPosition2() {
      return this.isMobile ? 'top' : 'right'
    },
  },
  created() {
    this.getList()
  },
  methods: {
    // 添加
    add(name) {
      this.formInline.id = 0
      this.modelTit = name
      this.modals = true
      this.getmenusList()
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `setting/role/${row.id}`,
        method: 'DELETE',
        ids: '',
      }
      this.$modalSure(delfromData)
        .then((res) => {
          this.$Message.success(res.msg)
          this.tableList.splice(num, 1)
          if (!this.tableList.length) {
            this.formValidate.page =
                this.formValidate.page == 1 ? 1 : this.formValidate.page - 1;
          }
          this.getList();
        })
        .catch((res) => {
          this.$Message.error(res.msg)
        })
    },
    // 修改是否显示
    onchangeIsShow(row) {
      let data = {
        id: row.id,
        status: row.status,
      }
      roleSetStatusApi(data)
        .then(async (res) => {
          this.$Message.success(res.msg)
        })
        .catch((res) => {
          this.$Message.error(res.msg)
        })
    },
    // 列表
    getList() {
      this.loading = true
      this.formValidate.status = this.formValidate.status || ''
      roleListApi(this.formValidate)
        .then(async (res) => {
          let data = res.data
          this.tableList = data.list
          this.total = res.data.count
          this.loading = false
        })
        .catch((res) => {
          this.loading = false
          this.$Message.error(res.msg)
        })
    },
    pageChange(index) {
      this.formValidate.page = index
      this.getList()
    },
    // 表格搜索
    userSearchs() {
      this.formValidate.page = 1
      this.getList()
    },
    // 编辑
    edit(row, name) {
      this.modelTit = name
      this.formInline.id = row.id
      this.modals = true
      this.rows = row
      this.getIofo(row)
    },
    //默认首页被选中并禁止更改
    checkedFun(data) {
      // data.forEach((item) => {
      //   if (item.title == '运营') {
      //     item.disabled = true
      //     if (item.children.length) {
      //       item.children.forEach((j) => {
      //         if (j.title == '首页') {
      //           this.formInline.checked_menus.push(j.id)
      //           j.checked = true
      //           j.disabled = true
      //           if (j.children.length) {
      //             j.children.forEach((v) => {
      //               v.checked = true
      //               v.disabled = true
      //             })
      //           }
      //         }
      //       })
      //     }
      //   }
      //   item.expand = false
      // })
      for (let i = 0; i < data.length; i++) {
        if (data[i].title != '运营') {
          break;
        }
        for (let j = 0; j < data[i].children.length; j++) {
          if (data[i].children[j].title != '首页') {
            break;
          }
          if (typeof this.formInline.checked_menus == 'string') {
            this.formInline.checked_menus = this.formInline.checked_menus.split(',');
          }
          this.formInline.checked_menus.push(data[i].children[j].id);
          this.formInline.checked_menus = this.formInline.checked_menus.join();
          data[i].children[j].disabled = true;
          data[i].children[j].checked = true;
          for (let k = 0; k < data[i].children[j].children.length; k++) {
            data[i].children[j].children[k].disabled = true;
            data[i].children[j].children[k].checked = true;
          }
        }
      }
    },
    // 菜单列表
    getmenusList() {
      this.spinShow = true
      menusListApi()
        .then(async (res) => {
          let data = res.data
          // const mList = data.menus[0].children.splice(0,1);
          this.menusList = data.menus
          this.checkedFun(res.data.menus)
          // this.menusList.map((item, index) => {
          //   if (item.title === '主页') {
          //     item.checked = true
          //     item.disableCheckbox = true
          //     if (item.children.length) {
          //       item.children.map((v) => {
          //         v.checked = true
          //         v.disableCheckbox = true
          //       })
          //     }
          //   }
          //   item.expand = false
          // })
          this.spinShow = false
        })
        .catch((res) => {
          this.spinShow = false
          this.$Message.error(res.msg)
        })
    },
    // 详情
    getIofo(row) {
      this.spinShow = true
      roleInfoApi(row.id)
        .then(async (res) => {
          let data = res.data
          this.formInline = data.role || this.formInline
          this.formInline.checked_menus = this.formInline.rules
          this.tidyRes(data.menus)
          this.spinShow = false
        })
        .catch((res) => {
          this.spinShow = false
          this.$Message.error(res.msg)
        })
    },
    tidyRes(menus) {
      let data = []
      menus.map((menu) => {
        data.push(this.initMenu(menu))
      })
      this.checkedFun(data);
      this.$set(this, 'menusList', data)
      // this.menusList.map((item, index) => {
      //     if (item.title === '主页') {
      //         item.checked = true;
      //         item.disableCheckbox = true;
      //         if (item.children.length) {
      //             item.children.map(v => {
      //                 v.checked = true;
      //                 v.disableCheckbox = true;
      //             });
      //         }
      //     }
      // });
    },
    initMenu(menu) {
      let data = {},
        checkMenus = ',' + this.formInline.checked_menus + ','
      data.title = menu.title
      data.id = menu.id
      if (menu.children && menu.children.length > 0) {
        data.children = []
        menu.children.map((child) => {
          data.children.push(this.initMenu(child))
        })
      } else {
        data.checked = checkMenus.indexOf(String(',' + data.id + ',')) !== -1
        data.expand = !data.checked
      }
      return data
    },
    // 递归函数
    // getNestedChildren(arr, parent) {
    //   var out = []
    //   for (var i in arr) {
    //     if (arr[i].parent == parent) {
    //       var children = getNestedChildren(arr, arr[i].id)

    //       if (children.length) {
    //         arr[i].children = children
    //       }
    //       out.push(arr[i])
    //     }
    //   }
    //   return out
    // },
    // 提交
    handleSubmit(name) {
      this.$refs[name].validate((valid) => {
        if (valid) {
          this.formInline.checked_menus = []
          this.$refs.tree.getCheckedAndIndeterminateNodes().map((node) => {
            this.formInline.checked_menus.push(node.id)
          })

          if (this.formInline.checked_menus.length === 0)
            return this.$Message.warning('请至少选择一个权限')
          roleCreatApi(this.formInline)
            .then(async (res) => {
              this.$Message.success(res.msg)
              this.modals = false
              this.getList()
              this.$refs[name].resetFields()
              this.formInline.checked_menus = []
            })
            .catch((res) => {
              this.$Message.error(res.msg)
            })
        } else {
          return false
        }
      })
    },
    onCancel() {
      this.$refs['formInline'].resetFields()
      this.formInline.checked_menus = []
    },
  },
}
</script>

<style scoped lang="stylus">
.input-add {
 width: 250px;
}
.mr14 {
 margin-right: 14px;
}
.trees-coadd {
  width: 100%;
  height: 385px;

  .scollhide {
    width: 100%;
    height: 100%;
    overflow-x: hidden;
    overflow-y: scroll;
  }
}

.scollhide::-webkit-scrollbar {
  display: none;
}
</style>
