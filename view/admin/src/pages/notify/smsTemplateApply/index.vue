<template>
  <div>
    <div class="i-layout-page-header">
      <PageHeader
        class="product_tabs"
        :title="$route.meta.title"
        hidden-breadcrumb
      ></PageHeader>
    </div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <Form
        ref="levelFrom"
        :model="levelFrom"
        :label-width="labelWidth"
        :label-position="labelPosition"
        @submit.native.prevent
      >
        <Row
          type="flex"
          :gutter="24"
          v-if="$route.path === this.roterPre + '/setting/sms/sms_template_apply/index'"
        >
          <!-- <Col v-bind="grid">
                        <FormItem label="模板类型：">
                            <Select v-model="levelFrom.type" placeholder="请选择" clearable  @on-change="userSearchs">
                                <Option value="1">验证码</Option>
                                <Option value="2">通知</Option>
                                <Option value="3">推广</Option>
                            </Select>
                        </FormItem>
                    </Col>
                    <Col v-bind="grid">
                        <FormItem label="模板状态：">
                            <Select v-model="levelFrom.status" placeholder="请选择" clearable  @on-change="userSearchs">
                                <Option value="1">可用</Option>
                                <Option value="0">不可用</Option>
                            </Select>
                        </FormItem>
                    </Col>
                    <Col v-bind="grid">
                        <FormItem label="模板名称：" >
                            <Input search enter-button  v-model="levelFrom.title" placeholder="请输入模板名称" @on-search="userSearchs"/>
                        </FormItem>
                    </Col> -->
            <Button type="primary" class="ml20" @click="add">申请模板</Button>
            <Button class="ml20" @click="editSign">修改签名</Button>
        </Row>
        <Row type="flex" :gutter="24" v-else>
          <Col v-bind="grid">
            <FormItem label="是否拥有：">
              <Select
                v-model="levelFrom.is_have"
                placeholder="请选择"
                clearable
                @on-change="userSearchs"
              >
                <Option value="1">有</Option>
                <Option value="0">没有</Option>
              </Select>
            </FormItem>
          </Col>
        </Row>
      </Form>
      <Table
        :columns="columns1"
        :data="levelLists"
        ref="table"
        class="mt25"
        :loading="loading"
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <template slot-scope="{ row, index }" slot="status">
          <span v-show="row.status === 1">可用</span>
          <span v-show="row.status === 0">不可用</span>
        </template>
        <template
          slot-scope="{ row, index }"
          slot="is_have"
          v-if="$route.path === this.roterPre + '/setting/sms/sms_template_apply/commons'"
        >
          <span v-show="row.status === 1">有</span>
          <span v-show="row.status === 0">没有</span>
        </template>
      </Table>
      <div class="acea-row row-right page">
        <Page
          :total="total"
          :current="levelFrom.page"
          show-elevator
          show-total
          @on-change="pageChange"
          :page-size="levelFrom.limit"
        />
      </div>
    </Card>

    <!-- 新建表单-->
    <edit-from
      ref="edits"
      :FromData="FromData"
      @submitFail="submitFail"
    ></edit-from>
    <Modal
      v-model="modals"
      footer-hide
      scrollable
      closable
      title="短信账户签名修改"
      class="order_box"
      @on-cancel="cancel('formInline')"
    >
      <Form
        ref="formInline"
        :model="formInline"
        :rules="ruleInline"
        :label-width="63"
        @submit.native.prevent
      >
        <FormItem>
          <Input
            v-model="accountInfo.account"
            disabled
            prefix="ios-person-outline"
            size="large"
            class="input-add"
          ></Input>
        </FormItem>
        <FormItem prop="phone">
          <Input
            v-model="formInline.phone"
            prefix="ios-call-outline"
            placeholder="请输入您的手机号"
            size="large"
            :disabled="formInline.phone"
           class="input-add"
          ></Input>
        </FormItem>
        <FormItem>
          <Input
            v-model="sign"
            :disabled="sign"
            prefix="ios-document-outline"
            placeholder="请输入短信签名，例如：CRMEB"
            size="large"
           class="input-add"
          ></Input>
        </FormItem>
        <FormItem prop="sign">
          <Input
            v-model="formInline.sign"
            prefix="ios-document-outline"
            placeholder="请输入新的短信签名，例如：CRMEB"
            size="large"
           class="input-add"
          ></Input>
        </FormItem>
        <FormItem prop="code">
          <div class="code acea-row row-middle" style="width: 87%;">
            <Input
              type="text"
              v-model="formInline.code"
              prefix="ios-keypad-outline"
              placeholder="验证码"
              size="large"
              style="width: 74%"
            />
            <Button :disabled="!this.canClick" @click="cutDown" size="large">{{
              cutNUm
            }}</Button>
          </div>
        </FormItem>
        <FormItem>
          <Button
            type="primary"
            long
            size="large"
            @click="editSubmit('formInline')"
            class="btn input-add"
            >确认修改</Button
          >
        </FormItem>
      </Form>
    </Modal>
  </div>
</template>
<script>
import { mapState } from "vuex";
import {
  tempListApi,
  tempCreateApi,
  isLoginApi,
  serveInfoApi,
  serveSign,
  captchaApi,
} from "@/api/setting";
import editFrom from "@/components/from/from";
import Setting from '@/setting';
export default {
  name: "smsTemplateApply",
  components: { editFrom },
  data() {
    const validatePhone = (rule, value, callback) => {
      if (!value) {
        return callback(new Error("请填写手机号"));
      } else if (!/^1[3456789]\d{9}$/.test(value)) {
        callback(new Error("手机号格式不正确!"));
      } else {
        callback();
      }
    };
    return {
      roterPre: Setting.roterPre,
      modals: false,
      cutNUm: "获取验证码",
      canClick: true,
      spinShow: true,
      grid: {
        xl: 7,
        lg: 7,
        md: 12,
        sm: 24,
        xs: 24,
      },
      sign: "",
      formInline: {
        sign: "",
        phone: "",
        code: "",
      },
      ruleInline: {
        sign: [{ required: true, message: "请输入短信签名", trigger: "blur" }],
        phone: [{ required: true, validator: validatePhone, trigger: "blur" }],
        code: [{ required: true, message: "请输入验证码", trigger: "blur" }],
      },
      loading: false,
      columns1: [],
      levelFrom: {
        type: "",
        status: "",
        title: "",
        page: 1,
        limit: 20,
      },
      levelFrom2: {
        is_have: "",
        page: 1,
        limit: 20,
      },
      total: 0,
      FromData: null,
      delfromData: {},
      levelLists: [],
      accountInfo: {}, //签名审核状态： 0、待审核；1、已通过；2、未通过
    };
  },
  watch: {
    $route(to, from) {
      this.getList();
    },
  },
  created() {
    this.onIsLogin();
  },
  mounted() {
    serveInfoApi().then((res) => {
      this.accountInfo = res.data;
      this.formInline.phone = res.data.phone;
      if (res.data.sms.open != 1) {
        this.$router.push(
          this.roterPre + "/setting/sms/sms_config/index?url=" + this.$route.path
        );
      }
    });
  },
  computed: {
    ...mapState("admin/layout", ["isMobile"]),
    labelWidth() {
      return this.isMobile ? undefined : 75;
    },
    labelPosition() {
      return this.isMobile ? "top" : "right";
    },
  },
  methods: {
    // 查看是否登录
    onIsLogin() {
      this.spinShow = true;
      isLoginApi()
        .then(async (res) => {
          let data = res.data;
          if (!data.status) {
            this.$Message.warning("请先登录");
            this.$router.push(
              this.roterPre + "/setting/sms/sms_config/index?url=" + this.$route.path
            );
          } else {
            this.getList();
          }
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 等级列表
    getList() {
      this.loading = true;
      this.levelFrom.status = this.levelFrom.status || "";
      this.levelFrom.is_have = this.levelFrom.is_have || "";
      let data = {
        data:
          this.$route.path === this.roterPre + "/setting/sms/sms_template_apply/index"
            ? this.levelFrom
            : this.levelFrom2,
        url:
          this.$route.path === this.roterPre + "/setting/sms/sms_template_apply/index"
            ? "serve/sms/temps"
            : "notify/sms/public_temp",
      };
      let columns1 = [
        {
          title: "ID",
          key: "id",
          sortable: true,
          width: 80,
        },
        {
          title: "模板ID",
          key: "templateid",
          minWidth: 110,
        },
        {
          title: "模板名称",
          key: "title",
          minWidth: 150,
        },
        {
          title: "模板内容",
          key: "content",
          minWidth: 550,
        },
        {
          title: "模板类型",
          key: "type",
          minWidth: 100,
        },
        {
          title: "模板状态",
          slot: "status",
          minWidth: 100,
        },
      ];
      if (
        this.$route.path === this.roterPre + "/setting/sms/sms_template_apply/commons"
      ) {
        this.columns1 = Object.assign([], columns1)
          .slice(0, 6)
          .concat([
            {
              title: "是否拥有",
              slot: "is_have",
              minWidth: 110,
            },
          ]);
      } else {
        this.columns1 = columns1;
      }
      tempListApi(data)
        .then(async (res) => {
          let data = res.data;
          this.levelLists = data.data;
          this.total = data.count;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$Message.error(res.msg);
        });
    },
    pageChange(index) {
      this.levelFrom.page = index;
      this.getList();
    },
    // 添加
    add() {
      tempCreateApi()
        .then(async (res) => {
          this.FromData = res.data;
          this.$refs.edits.modals = true;
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 表格搜索
    userSearchs() {
      this.levelFrom.page = 1;
      this.getList();
    },
    // 修改成功
    submitFail() {
      this.getList();
    },
    //修改签名
    editSign() {
      if (this.accountInfo.sms.sign_status === 0) {
        this.$Message.warning("签名待审核，暂无法修改");
        return;
      }
      this.sign = this.accountInfo.sms.sign;
      this.modals = true;
    },
    cancel(name) {
      this.modals = false;
      this.$refs[name].resetFields();
      this.formInline.phone = this.accountInfo.phone;
    },
    // 提交
    editSubmit(name) {
      this.$refs[name].validate((valid) => {
        if (valid) {
          serveSign(this.formInline)
            .then((res) => {
              this.modals = false;
              this.$Message.success(res.msg);
              this.$refs[name].resetFields();
            })
            .catch((res) => {
              this.$Message.error(res.msg);
            });
        }
      });
    },
    // 短信验证码
    cutDown() {
      if (this.formInline.phone) {
        if (!this.canClick) return;
        this.canClick = false;
        this.cutNUm = 60;
        let data = {
          phone: this.formInline.phone,
        };
        captchaApi(data)
          .then(async (res) => {
            this.$Message.success(res.msg);
          })
          .catch((res) => {
            this.$Message.error(res.msg);
          });
        let time = setInterval(() => {
          this.cutNUm--;
          if (this.cutNUm === 0) {
            this.cutNUm = "获取验证码";
            this.canClick = true;
            clearInterval(time);
          }
        }, 1000);
      } else {
        this.$Message.warning("请填写手机号!");
      }
    },
  },
};
</script>

<style scoped lang="stylus">
.input-add {
 width: 87%;
}
.tabBox_img {
  width: 36px;
  height: 36px;
  border-radius: 4px;
  cursor: pointer;

  img {
    width: 100%;
    height: 100%;
  }
}
</style>
