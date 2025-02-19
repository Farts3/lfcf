<template>
  <Card :bordered="false" dis-hover class="ivu-mt">
    <div class="acea-row row-between-wrapper mb20">
      <div class="header-title">商品概况</div>
      <div class="acea-row">
        <div class="acea-row row-middle">
          <span class="font-sm">时间筛选：</span>
          <DatePicker
          :editable="false"
          :clearable="false"
          @on-change="onchangeTime"
          :value="timeVal"
          format="yyyy/MM/dd"
          type="datetimerange"
          placement="bottom-start"
          placeholder="自定义时间"
          class="mr15 input-add"
          :options="options"
        ></DatePicker>
        </div>
        <Button type="primary" class="mr15" @click="onSeach">查询</Button>
        <Button type="primary" @click="exports">导出</Button>
      </div>
    </div>
    <div class="acea-row mb20">
      <div
        class="infoBox acea-row mb30"
        v-for="(item, index) in list"
        :key="index"
      >
        <div
          class="iconCrl mr15"
          :class="{
            one: index % 4 == 0,
            two: index % 4 == 1,
            three: index % 4 == 2,
            four: index % 4 == 3,
          }"
        >
          <i :class="item.icon" class="iconfont"></i>
        </div>
        <div class="info">
          <div class="sp1">
            <span v-text="item.name" class="mr5"></span>
            <Poptip word-wrap trigger="hover" placement="right-start">
              <Icon type="ios-information-circle-outline" />
              <div slot="content">{{ item.txt }}</div>
            </Poptip>
          </div>
          <span
            class="sp2"
            v-if="index === list.length - 1"
            v-text="item.list.num"
          ></span>
          <span class="sp2" v-else v-text="item.list.num"></span>
          <span class="content-time spBlock"
            >环比增长：<i
              class="content-is"
              :class="Number(item.list.percent) >= 0 ? 'up' : 'down'"
              >{{ item.list.percent }}%</i
            ><Icon
              :color="Number(item.list.percent) >= 0 ? '#F5222D' : '#39C15B'"
              :type="
                Number(item.list.percent) >= 0
                  ? 'md-arrow-dropup'
                  : 'md-arrow-dropdown'
              "
          /></span>
        </div>
      </div>
    </div>
    <echarts-new
      :option-data="optionData"
      :styles="style"
      height="100%"
      width="100%"
      v-if="optionData"
    ></echarts-new>
    <Spin size="large" fix v-if="spinShow"></Spin>
  </Card>
</template>

<script>
import {
  statisticBasicApi,
  statisticTrendApi,
  statisticProductExcel,
} from "@/api/statistic";
import echartsNew from "@/components/echartsNew/index";
import { formatDate } from "@/utils/validate";
import exportExcel from "@/utils/newToExcel.js";
import timeOptions from "@/utils/timeOptions"
export default {
  name: "productInfo",
  components: {
    echartsNew,
  },
  data() {
    return {
      spinShow: false,
      grid: {
        xl: 8,
        lg: 8,
        md: 8,
        sm: 24,
        xs: 24,
      },
      options: timeOptions,
      name: "近30天",
      timeVal: [],
      dataTime: "",
      list: [],
      optionData: {},
      style: { height: "400px" },
    };
  },
  created() {
    const end = new Date();
    const start = new Date();
    start.setTime(
      start.setTime(
        new Date(
          new Date().getFullYear(),
          new Date().getMonth(),
          new Date().getDate() - 29
        )
      )
    );
    this.timeVal = [start, end];
    this.dataTime =
      formatDate(start, "yyyy/MM/dd") + "-" + formatDate(end, "yyyy/MM/dd");
  },
  mounted() {
    this.getStatistics();
    this.getTrend();
  },
  methods: {
    // 导出
    // exports() {
    //   statisticProductExcel({ data: this.dataTime }).then(async (res) => {
    //     res.data.url.map((item) => {
    //       window.location.href = item;
    //     });
    //   });
    // },
    // 数据导出；
    async exports() {
      let [th, filekey, data, fileName] = [[], [], [], ""];
      let excelData = { data: this.dataTime, page: 1 };
      let lebData = await this.getExcelData(excelData);
      if (!fileName) fileName = lebData.filename;
      if (!filekey.length) {
        filekey = lebData.filekey;
      }
      if (!th.length) th = lebData.header;
      data = data.concat(lebData.export);
      exportExcel(th, filekey, fileName, data);
    },
    getExcelData(excelData) {
      return new Promise((resolve, reject) => {
        statisticProductExcel(excelData).then((res) => {
          return resolve(res.data);
        });
      });
    },
    onSeach() {
      this.getStatistics();
      this.getTrend();
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.dataTime = this.timeVal[0] ? this.timeVal.join("-") : "";
    },
    // 统计
    getStatistics() {
      statisticBasicApi({ data: this.dataTime })
        .then(async (res) => {
          const cardLists = res.data;
          this.list = [
            {
              name: "商品浏览量",
              icon: "iconshangpinliulanliang",
              list: cardLists.browse,
              txt: "在选定条件下，所有商品详情页被访问的次数，一个人在统计时间内访问多次记为多次",
            },
            {
              name: "商品访客数",
              icon: "iconshangpinfangkeshu",
              list: cardLists.user,
              txt: "在选定条件下，访问任何商品详情页的人数，一个人在统计时间范围内访问多次只记为一个",
            },
            {
              name: "加购件数",
              icon: "iconjiagoujianshu",
              list: cardLists.cart,
              txt: "在选定条件下，添加商品进入购物车的商品件数",
            },
            {
              name: "下单件数",
              icon: "iconxiadanjianshu",
              list: cardLists.order,
              txt: "在选定条件下，成功下单的商品件数之和（拼团商品在成团之后计入，线下支付订单在后台确认支付后计入，不剔除退款订单）",
            },
            {
              name: "支付件数",
              icon: "iconzhifujianshu",
              list: cardLists.pay,
              txt: "在选定条件下，成功付款订单的商品件数之和（拼团商品在成团之后计入，线下支付订单在后台确认支付后计入，不剔除退款订单）",
            },
            {
              name: "支付金额",
              icon: "iconzhifujine",
              list: cardLists.payPrice,
              txt: "在选定条件下，成功付款订单的商品金额之和（拼团商品在成团之后计入，线下支付订单在后台确认支付后计入，不剔除退款订单）",
            },
            {
              name: "成本金额",
              icon: "iconchengbenjine",
              list: cardLists.cost,
              txt: "在选定条件下，成功付款订单的商品成本金额之和",
            },
            {
              name: "退款金额",
              icon: "icontuikuan",
              list: cardLists.refundPrice,
              txt: "在选定条件下，成功退款的商品金额之和",
            },
            {
              name: "退款件数",
              icon: "icontuikuanjianshu",
              list: cardLists.refund,
              txt: "在选定条件下，成功退款的商品件数之和",
            },
            {
              name: "访客-支付转化率",
              icon: "iconfangke-zhifuzhuanhuashuai",
              list: cardLists.payPercent,
              txt: "在选定条件下， 付款人数 / 访客数",
            },
          ];
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 统计图
    getTrend() {
      this.spinShow = true;
      statisticTrendApi({ data: this.dataTime })
        .then(async (res) => {
          let legend = res.data.series.map((item) => {
            return item.name;
          });
          let xAxis = res.data.xAxis;
          let col = ["#B37FEB", "#FFAB2B", "#1890FF", "#00C050"];
          res.data.series.map((item, index) => {
            item.itemStyle = {
              normal: {
                color: col[index],
              },
            };
          });
          this.optionData = {
            tooltip: {
              trigger: "axis",
              axisPointer: {
                type: "cross",
                label: {
                  backgroundColor: "#6a7985",
                },
              },
            },
            legend: {
              x: "center",
              data: legend,
            },
            grid: {
              left: "3%",
              right: "4%",
              bottom: "3%",
              containLabel: true,
            },
            toolbox: {
              show: true,
              right: "1.5%",
              feature: {
                saveAsImage: {
									name: '商品统计_'+formatDate(new Date(Number(new Date().getTime())), 'yyyyMMddhhmmss')
								},
              },
            },
            xAxis: {
              type: "category",
              boundaryGap: true,
              // axisTick:{
              //     show:false
              // },
              // axisLine:{
              //     show:false
              // },
              // splitLine: {
              //     show: false
              // },
              axisLabel: {
                interval: 0,
                rotate: 40,
                textStyle: {
                  color: "#000000",
                },
              },
              data: xAxis,
            },
            yAxis: [
              {
                type: "value",
                name: "金额",
                axisLine: {
                  show: false,
                },
                axisTick: {
                  show: false,
                },
                axisLabel: {
                  textStyle: {
                    color: "#7F8B9C",
                  },
                },
                splitLine: {
                  show: true,
                  lineStyle: {
                    color: "#F5F7F9",
                  },
                },
              },
              {
                type: "value",
                name: "数量",
                axisLine: {
                  show: false,
                },
                axisTick: {
                  show: false,
                },
                axisLabel: {
                  textStyle: {
                    color: "#7F8B9C",
                  },
                },
                splitLine: {
                  show: true,
                  lineStyle: {
                    color: "#F5F7F9",
                  },
                },
                // axisLabel: {
                //     formatter: '{value} °C'
                // }
              },
            ],
            series: res.data.series,
          };
          this.spinShow = false;
        })
        .catch((res) => {
          this.$Message.error(res.msg);
          this.spinShow = false;
        });
    },
  },
};
</script>

<style scoped lang="less">
.one {
  background: #1890ff;
}
.two {
  background: #00c050;
}
.three {
  background: #ffab2b;
}
.four {
  background: #b37feb;
}
.up,
.el-icon-caret-top {
  color: #f5222d;
  font-size: 12px;
  opacity: 1 !important;
}

.down,
.el-icon-caret-bottom {
  color: #39c15b;
  font-size: 12px;
}
.curP {
  cursor: pointer;
}
.header {
  &-title {
    font-size: 16px;
    color: rgba(0, 0, 0, 0.85);
  }
  &-time {
    font-size: 12px;
    color: #000000;
    opacity: 0.45;
  }
}

.iconfont {
  font-size: 16px;
  color: #fff;
}

.iconCrl {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  text-align: center;
  line-height: 32px;
  opacity: 0.7;
}

.lan {
  background: #1890ff;
}

.iconshangpinliulanliang {
  color: #fff;
}

.infoBox {
  width: 20%;
  @media screen and (max-width: 1400px) {
    width: 25%;
  }
  @media screen and (max-width: 1200px) {
    width: 33%;
  }
  @media screen and (max-width: 900px) {
    width: 50%;
  }
}
.font-sm{
  font-size: 12px;
}
.info {
  .sp1 {
    color: #666;
    font-size: 14px;
    display: block;
  }
  .sp2 {
    font-weight: 400;
    font-size: 30px;
    color: rgba(0, 0, 0, 0.85);
    display: block;
  }
  .sp3 {
    font-size: 12px;
    font-weight: 400;
    color: rgba(0, 0, 0, 0.45);
    display: block;
  }
}
</style>