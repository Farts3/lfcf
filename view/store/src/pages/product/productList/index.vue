<template>
  <div>
    <Card :bordered="false" dis-hover class="ivu-mt pt10">
      <Form ref="formValidate" :model="formValidate" :label-width="labelWidth" :label-position="labelPosition">
        <Row type="flex" :gutter="24">
          <Col>
            <FormItem label="商品分类：" :labelWidth="80" label-for="pid">
              <Cascader :data="data" v-model="value1" style="width:250px" change-on-select @on-change="cascaderSearchs"></Cascader>
            </FormItem>
          </Col>
          <Col>
            <FormItem label="商品搜索：" :labelWidth="80" label-for="store_name">
              <Input enter-button style="width:250px"  placeholder="请输入商品名称,关键字,ID" v-model="formValidate.store_name" />
            </FormItem>
          </Col>
          <Col>
          <div class="search" @click="search">搜索</div>
          </Col>
          <Col>
          <div class="reset" @click="reset">重置</div>
          </Col>
        </Row>
      </Form>
    </Card>
    <!--  -->
     <!-- <Card :bordered="false" dis-hover class="ivu-mt">
      <div class="product_tabs tabbox">
        <Tabs v-model="formValidate.type" @on-click="onClickTab">
          <TabPane v-for="(item, index) in headerList" :key="index" :label="item.name + '(' + item.count + ')'"
            :name="item.type.toString()" />
        </Tabs>
      </div>
    </Card> -->
    <Card :bordered="false" dis-hover class="mt15 tablebox">
      <div class="product_tabs tabbox">
        <Tabs v-model="formValidate.type" @on-click="onClickTab">
          <TabPane v-for="(item, index) in headerList" :key="index" :label="item.name + '(' + item.count + ')'"
                   :name="item.type.toString()" />
        </Tabs>
      </div>
      <div class="btnbox"></div>
      <div class="table">
        <!--<div class="reset mb15" @click="setStock">同步库存</div>-->
        <router-link :to="routePre+'/product/edit_product'" v-if="product_status==1">
           <Button type="primary" class="bnt mb15">添加商品</Button
        ></router-link>
        <Table :columns="formValidate.type == -1?columns01:formValidate.type == -2?columns02:columns" :data="orderList" ref="table"  @on-select-all="selectall"
          @on-select-all-cancel="selectall" @on-selection-change="select" :loading="loading" highlight-row
          no-userFrom-text="暂无数据" no-filtered-userFrom-text="暂无筛选结果">
          <template slot-scope="{ row }" slot="image">
            <viewer>
              <div class="tabBox_img">
                <img v-lazy="row.image" />
              </div>
            </viewer>
          </template>
          <template slot-scope="{ row, index }" slot="state">
            <i-switch v-model="row.is_show" :value="row.is_show" :true-value="1" :false-value="0"
              @on-change="changeSwitch(row)" size="large" v-if="formValidate.type != 7">
              <span slot="open">上架</span>
              <span slot="close">下架</span>
            </i-switch>
            <div v-else>{{ row.is_del ? '已删除' : !row.is_show ? '已下架' : '' }}</div>
          </template>
          <template slot-scope="{ row, index }" slot="action">
            <a @click="edit(row)" v-if="row.pid == 0">编辑</a>
            <Divider type="vertical" v-if="row.pid == 0" />
            <a @click="detail(row.id)">详情</a>
            <Divider type="vertical" />
            <a @click="reply(row.id)">查看评论</a>
            <Divider type="vertical" v-if="!openErp" />
            <a @click="stockControl(row)" v-if="!openErp">库存管理</a>
			<Divider type="vertical" v-if="row.pid == 0" />
			<a @click="del(row,index)" v-if="row.pid == 0">{{row.is_del ? '恢复' : '删除' }}</a>
            <Divider type="vertical" />
            <a @click="copy(row)">复制</a>
          </template>
        </Table>
        <div class="acea-row row-right page">
          <Page :total="total" :current="formValidate.page" show-elevator show-total @on-change="pageChange"
            :page-size="formValidate.limit" />
        </div>
      </div>
    </Card>
    <!-- 商品详情弹窗 -->
    <!-- <div v-if="isProductBox">
      <div class="bg" @click.stop="isProductBox = false"></div>
      <goodsDetail :goodsId="goodsId" :product="1"></goodsDetail>
    </div> -->
    <stockEdit ref="stock" @stockChange="stockChange"></stockEdit>
    <productDetails :visible.sync="detailsVisible" :product-id="productId"></productDetails>
  </div>
</template>

<script>
import Setting from "@/setting";
import { mapState } from "vuex";
import goodsDetail from "../components/goods_detail.vue";
import stockEdit from "../components/stockEdit.vue";
import productDetails from '../components/productDetails.vue';
import {
  productListInfo,
  productHeaderInfo,
  cascaderList,
  setShowApi,
  synchStocks,
} from "@/api/product.js";
import { erpConfig } from "@/api/erp";
export default {
  name: "index",
  components: {
    goodsDetail,
    stockEdit,
    productDetails
  },
  data() {
    return {
      routePre:Setting.routePre,
      openErp:false,
      // isProductBox: false,
      goodsId: "",
      data: [],
      value1: [],
      headerList: [],
      total: 0,
      loading: false,
      columns: [
        {
          type: "selection",
          width: 60,
          align: "center",
        },
        {
          title: "商品ID",
          key: "id",
          width: 80,
        },
        {
          title: "商品图",
          slot: "image",
          minWidth: 80,
        },
        {
          title: "商品名称",
          key: "store_name",
          minWidth: 250,
        },
        {
          title: "商品售价",
          key: "price",
          minWidth: 90,
        },
        {
          title: "销量",
          key: "branch_sales",
          minWidth: 90,
        },
        {
          title: "库存",
          key: "branch_stock",
          minWidth: 80,
        },
        {
          title: "排序",
          key: "sort",
          minWidth: 70,
        },
        {
          title: "状态",
          slot: "state",
          width: 100,
          // filters: [
          //   {
          //     label: "上架",
          //     value: 1,
          //   },
          //   {
          //     label: "下架",
          //     value: 0,
          //   },
          // ],
          // filterMethod(value, row) {
          //   return row.is_show === value;
          // },
          // filterMultiple: false,
        },
        {
          title: "操作",
          slot: "action",
          fixed: "right",
          minWidth: 300,
        },
      ],
      columns01:[],
      columns02:[],
      orderList: [],
      formValidate: {
        store_name: "",
        cate_id: "",
        type: "1",
        page: 1,
        limit: 15,
      },
      datanew: [],
      dataid: [],
      product_status:1,
      detailsVisible: false,
      productId: 0
    };
  },
  watch: {
    $route() {
      if (this.$route.fullPath === `${this.routePre}/product/index?type=5`) {
        this.getPath();
      }
    },
  },
  computed: {
    ...mapState("store/layout", ["isMobile"]),
    labelWidth() {
      return this.isMobile ? undefined : 120;
    },
    labelPosition() {
      return this.isMobile ? "top" : "right";
    },
  },
  mounted() {
    this.goodsCategory();
    this.getHeader();
    this.getErpConfig();
    let data = {
      title: "拒绝原因",
      key: "refusal",
      minWidth: 150,
    };
    this.columns01 = this.columns.slice(0, 10);
    this.columns01.splice(9,0,data);
    let data02 = {
      title: "下架原因",
      key: "refusal",
      minWidth: 150,
    };
    this.columns02 = this.columns.slice(0, 10);
    this.columns02.splice(9,0,data02);
  },
  methods: {
    //erp配置
    getErpConfig(){
      erpConfig().then(res=>{
        this.openErp = res.data.open_erp;
        this.product_status = res.data.product_status;
      }).catch(err=>{
        this.$Message.error(err.msg);
      })
    },
    // 批量设置标签；
    setStock() {
      if (this.datanew.length == 0) {
        this.$Message.warning("请选择要同步的商品");
      } else {
        console.log(this.dataid);
        let a = [];
        a = this.dataid.join(",");

        let ids = { ids: [] };
        ids.ids = this.dataid;
        this.$Modal.confirm({
          title: "同步库存",
          content: "<p>此操作会覆盖商品的库存数量，请谨慎使用！</p>",
          onOk: () => {
            synchStocks(ids).then((res) => {
              this.datanew = [];
              this.getList();
              this.$Message.success(res.msg);
            });
          },
          onCancel: () => {
            this.$Message.info("已取消");
          },
        });
      }
    },
    select(e) {
      this.datanew = e;
      let data = [];
      this.datanew.map((item) => {
        data.push(item.id);
      });
      this.dataid = data;
    },
    selectall(e) {
      if (e.length == 0) {
        this.dataid = [];
      } else {
        this.datanew = e;
        let data = [];
        this.datanew.map((item) => {
          data.push(item.id);
        });
        this.dataid = data;
      }
    },
    stockChange(stock) {
      this.orderList.forEach((item) => {
        if (this.goodsId == item.id) {
          item.branch_stock = stock;
        }
      });
    },
    // 库存管理
    stockControl(row) {
      this.goodsId = row.id;
      this.$refs.stock.modals = true;
      this.$refs.stock.productAttrs(row);
    },
    //跳转刷新
    getPath() {
      this.formValidate.page = 1;
      this.formValidate.type = this.$route.query.type.toString();
      this.getList();
    },
    // 上下架
    changeSwitch(row) {
      setShowApi(row.id, row.is_show)
        .then((res) => {
          this.$Message.success(res.msg);
          this.getHeader();
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    //获取列表
    getList() {
      this.loading = true;
      productListInfo(this.formValidate).then((res) => {
        this.orderList = res.data.list;
        this.total = res.data.count;
        this.loading = false;
      });
    },
    //头部列表
    getHeader() {
      this.loading = true;
      productHeaderInfo(this.formValidate).then((res) => {
        this.headerList = res.data.list;
        if (this.$route.fullPath === `${Setting.routePre}/product/index?type=5`) {
          this.getPath();
        } else {
          this.getList();
        }
      });
    },
    // 商品分类；
    goodsCategory() {
      cascaderList(1)
        .then((res) => {
          this.data = res.data;
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    //详情
    detail(id) {
      // this.goodsId = id;
      // this.isProductBox = true;
      this.detailsVisible = true;
      this.productId = id;
    },
    // 编辑
    edit(row) {
      this.$router.push({ path: Setting.routePre+"/product/edit_product/" + row.id });
    },
    // 编辑
    reply(id) {
      this.$router.push({ path: Setting.routePre+'/product/product_reply?id=' + id });
    },
	// 删除
	del(row,num) {
	  let delfromData = {
	    title: row.is_del ? '恢复商品' : '移入回收站',
	    num: num,
	    url: `product/product/${row.id}`,
	    method: "DELETE",
	    ids: "",
        tips:  row.is_del ? '确定恢复商品吗' : '确定移入回收站吗',
	  };
	  this.$modalSure(delfromData)
	    .then((res) => {
	      this.$Message.success(res.msg);
	      this.orderList.splice(num, 1);
	      this.getHeader();
	    })
	    .catch((res) => {
	      this.$Message.error(res.msg);
	    });
	},
    // 复制
    copy(row) {
        this.$router.push({ path: `${Setting.routePre}/product/edit_product/`, query: { copy: row.id } });
    },
    //搜索
    search() {
      this.formValidate.page = 1;
      this.getHeader();
    },
    //重置
    reset() {
      this.formValidate.page = 1;
      this.value1 = [];
      this.formValidate.store_name = "";
      this.formValidate.cate_id = "";
      this.getHeader();
    },
    //获取商品分类列表
    cascaderSearchs(value) {
      this.formValidate.page = 1;
      this.formValidate.cate_id = value[value.length - 1];
      this.getHeader();
    },
    //切换头部列表
    onClickTab(e) {
      this.formValidate.type = e;
      this.formValidate.page = 1;
      this.getList();
    },
    //分页
    pageChange(page) {
      this.formValidate.page = page;
      this.getList();
    },
  },
};
</script>

<style scoped lang="less">
/deep/.ivu-form-label-left .ivu-form-item-label {
  text-align: right;
}

/deep/.ivu-page-header,
/deep/.ivu-tabs-bar {
    margin-bottom: 0px !important;
  border-bottom: 1px solid #E9E9E9;
}

/deep/.ivu-card-body {
  padding: 14px 20px 0 20px !important;
} 


/deep/.ivu-tabs-nav {
  height: 45px;
}

.bg {
  z-index: 100;
  position: fixed;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
}

.tabbox {
  padding: 0px 20px 0px;
  // border-bottom: 1px solid #DCDFE6;
}

.box {
  padding: 20px;
  padding-bottom: 0px;
}

.tablebox {
  margin-top: 15px;
}

.btnbox {
  padding: 20px 0px 0px 30px;

  .btns {
    width: 99px;
    height: 32px;
    background: #1890ff;
    border-radius: 4px;
    text-align: center;
    line-height: 32px;
    color: #ffffff;
    cursor: pointer;
  }
}

.table {
  padding: 0px 30px 15px 30px;
}
.ivu-table {
  background-color: #182328;
  color: #fff;
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

.search {
  width: 86px;
  height: 32px;
  background: #1890ff;
  border-radius: 4px;
  text-align: center;
  line-height: 32px;
  font-size: 13px;
  font-family: PingFangSC-Regular, PingFang SC;
  font-weight: 400;
  color: #ffffff;
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
font-weight: 500;
color: #515a6e;
// background: #2D8CF0;

  cursor: pointer;
}
</style>
