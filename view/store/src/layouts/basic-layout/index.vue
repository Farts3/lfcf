<template>
  <Layout class="i-layout">
    <Sider
      class="i-layout-sider"
      :class="siderClasses"
      :width="isChildren ? 220 : 60"
    >
      <i-menu-side :hide-logo="isHeaderStick && headerFix && showHeader" />
    </Sider>
    <Layout
      class="i-layout-inside"
      :class="isChildren ? 'bodyBig' : 'bodySmall'"
    >
      <transition name="fade-quick">
        <Header
          class="i-layout-header i-layout-header-fix"
          :class="isChildren ? 'onBig' : 'onSmall'"
          :style="headerStyle"
          v-show="showHeader"
          v-resize="handleHeaderWidthChange"
        >
          <!--<i-header-logo v-if="isMobile && showMobileLogo" />-->
          <!--<i-header-logo v-if="!isMobile && isHeaderStick && headerFix" />-->
          <!--<i-header-collapse v-if="(isMobile || showSiderCollapse) && !hideSider" @on-toggle-drawer="handleToggleDrawer" />-->
          <!--<i-header-reload v-if="!isMobile && showReload" @on-reload="handleReload" />-->
          <!--<i-menu-head v-if="headerMenu && !isMobile" ref="menuHead" />-->
          <i-header-breadcrumb ref="breadcrumb" />
          <div class="i-layout-header-right">
            <span class="cashier" @click="getStoreQRCode">
              <span class="iconfont iconerweima"></span>
              门店码</span
            >
            <span class="cashier" @click="goCashier">
              <span class="iconfont iconshouyintai"></span>
              收银台</span
            >
            <i-header-fullscreen v-if="isDesktop && showFullscreen" />
            <!--<i-menu-head v-if="headerMenu && isMobile" />-->
            <i-header-notice v-if="showNotice" />
            <i-header-user />
            <!--<i-header-i18n v-if="showI18n" />-->
          </div>
        </Header>
      </transition>
      <Content class="i-layout-content" :class="contentClasses">
        <transition name="fade-quick">
          <i-tabs v-if="tabs" v-show="showHeader" />
        </transition>
        <div class="i-layout-content-main">
          <keep-alive :include="keepAlive">
            <router-view v-if="loadRouter" />
          </keep-alive>
        </div>
      </Content>
      <i-copyright />
    </Layout>
    <!--<div v-if="isMobile && !hideSider">-->
    <!--<Drawer v-model="showDrawer" placement="left" :closable="false" :class-name="drawerClasses">-->
    <!--<i-menu-side />-->
    <!--</Drawer>-->
    <!--</div>-->
    <!--        <div class="open-image" @click="clear" v-if="openImage"><img src="@/assets/images/wechat_demo.png" alt=""></div>-->
    <Modal v-model="modal" title="门店码" footer-hide>
      <div v-viewer class="acea-row row-around code">
        <Spin fix v-if="spin"></Spin>
        <div class="acea-row row-column-around row-between-wrapper">
          <div class="QRpic">
            <img v-lazy="wechat" />
          </div>
          <span class="mt10">公众号二维码</span>
        </div>
        <div class="acea-row row-column-around row-between-wrapper">
          <div class="QRpic">
            <img v-lazy="routine" />
          </div>
          <span class="mt10">小程序二维码</span>
        </div>
      </div>
    </Modal>
  </Layout>
</template>
<script>
import iMenuHead from "./menu-head";
import iMenuSide from "./menu-side/index";
import iHeaderLogo from "./header-logo";
import iHeaderCollapse from "./header-collapse";
import iHeaderReload from "./header-reload";
import iHeaderBreadcrumb from "./header-breadcrumb";
import iHeaderSearch from "./header-search";
import iHeaderLog from "./header-log";
import iHeaderFullscreen from "./header-fullscreen";
import iHeaderNotice from "./header-notice";
import iHeaderUser from "./header-user";
import iHeaderI18n from "./header-i18n";
import iHeaderSetting from "./header-setting";
import iTabs from "./tabs";
import iCopyright from "@/components/copyright";

import { mapState, mapGetters, mapMutations } from "vuex";
import Setting from "@/setting";

import { requestAnimation } from "@/libs/util";
import util from "@/libs/util";
import Cookies from "js-cookie";

import { cashierLogin } from "@/api/staff.js";
import { getStoreQRCode } from "@/api/common.js";
// import { copyright } from '@/api/account';
export default {
  name: "BasicLayout",
  components: {
    iMenuHead,
    iMenuSide,
    iCopyright,
    iHeaderLogo,
    iHeaderCollapse,
    iHeaderReload,
    iHeaderBreadcrumb,
    iHeaderSearch,
    iHeaderUser,
    iHeaderI18n,
    iHeaderLog,
    iHeaderFullscreen,
    iHeaderSetting,
    iHeaderNotice,
    iTabs,
  },
  // provide (){
  //     return {
  //         reload:this.handleReload
  //     }
  // },
  data() {
    return {
      showDrawer: false,
      ticking: false,
      headerVisible: true,
      oldScrollTop: 0,
      isDelayHideSider: false, // hack，当从隐藏侧边栏的 header 切换到正常 header 时，防止 Logo 抖动
      loadRouter: true,
      openImage: true,
      modal: false,
      spin: false,
      wechat: '',
      routine: '',
    };
  },
  computed: {
    ...mapState("store/layout", [
      "siderTheme",
      "headerTheme",
      "headerStick",
      "tabs",
      "tabsFix",
      "siderFix",
      "headerFix",
      "headerHide",
      "headerMenu",
      "isMobile",
      "isTablet",
      "isDesktop",
      "menuCollapse",
      "showMobileLogo",
      "showSearch",
      "showNotice",
      "showFullscreen",
      "showSiderCollapse",
      "showBreadcrumb",
      "showLog",
      "showI18n",
      "showReload",
      "enableSetting",
      "isChildren",
    ]),
    ...mapState("store/page", ["keepAlive"]),
    ...mapGetters("store/menu", ["hideSider"]),
    // 如果开启 headerMenu，且当前 header 的 hideSider 为 true，则将顶部按 headerStick 处理
    // 这时，即使没有开启 headerStick，仍然按开启处理
    isHeaderStick() {
      let state = this.headerStick;
      if (this.hideSider) state = true;
      return state;
    },
    showHeader() {
      let visible = true;
      if (this.headerFix && this.headerHide && !this.headerVisible)
        visible = false;
      return visible;
    },
    headerClasses() {
      return [
        `i-layout-header-color-${this.headerTheme}`,
        {
          "i-layout-header-fix": this.headerFix,
          "i-layout-header-fix-collapse": this.headerFix && this.menuCollapse,
          "i-layout-header-mobile": this.isMobile,
          "i-layout-header-stick": this.isHeaderStick && !this.isMobile,
          "i-layout-header-with-menu": this.headerMenu,
          "i-layout-header-with-hide-sider":
            this.hideSider || this.isDelayHideSider,
        },
      ];
    },
    headerStyle() {
      // const menuWidth = this.isHeaderStick ? 0 : this.menuCollapse ? 80 : Setting.menuSideWidth;
      const menuWidth = this.isChildren ? 220 : 60;
      return { width: `calc(100% - ${menuWidth}px)` };
      // return this.isMobile || !this.headerFix ? {} : {
      //     width: `calc(100% - ${menuWidth}px)`
      // };
    },
    siderClasses() {
      return {
        "i-layout-sider-fix": this.siderFix,
        "i-layout-sider-dark": this.siderTheme === "dark",
      };
    },
    contentClasses() {
      return {
        "i-layout-content-fix-with-header": this.headerFix,
        "i-layout-content-with-tabs": this.tabs,
        "i-layout-content-with-tabs-fix": this.tabs && this.tabsFix,
      };
    },
    insideClasses() {
      return {
        "i-layout-inside-fix-with-sider": this.siderFix,
        "i-layout-inside-fix-with-sider-collapse":
          this.siderFix && this.menuCollapse,
        "i-layout-inside-with-hide-sider": this.hideSider,
        "i-layout-inside-mobile": this.isMobile,
      };
    },
    drawerClasses() {
      let className = "i-layout-drawer";
      if (this.siderTheme === "dark") className += " i-layout-drawer-dark";
      return className;
    },
    menuSideWidth() {
      return this.menuCollapse ? 60 : Setting.menuSideWidth;
    },
  },
  watch: {
    hideSider() {
      this.isDelayHideSider = true;
      setTimeout(() => {
        this.isDelayHideSider = false;
      }, 0);
    },
    $route(to, from) {
      if (to.path === from.path) {
        // 相同路由，不同参数，跳转时，重载页面
        if (Setting.sameRouteForceUpdate) {
          this.handleReload();
        }
      }
    },
  },
  methods: {
    ...mapMutations("store/layout", ["updateMenuCollapse"]),
    ...mapMutations("store/order", [
      "getOrderStatus",
      "getOrderTime",
      "getOrderNum",
    ]),
    // 获取门店二维码
    getStoreQRCode() {
      this.spin = true;
      getStoreQRCode().then(res => {
        const { wechat, routine } = res.data;
        this.spin = false;
        this.wechat = wechat;
        this.routine = routine;
        this.modal = true;
      }).catch(res => {
        this.spin = false;
        this.$Message.error(res.msg);
      });
    },
    handleToggleDrawer(state) {
      if (typeof state === "boolean") {
        this.showDrawer = state;
      } else {
        this.showDrawer = !this.showDrawer;
      }
    },
    goCashier() {
      console.log(util.cookies.get("uuid"));
      let id = util.cookies.get("uuid");
      cashierLogin(id)
        .then((res) => {
          Cookies.set("cashierData", JSON.stringify(res));
          // copyright().then(()=>{
            
          // }).catch(errr=>{
          //     this.$Message.error(errr.msg)
          // })
          window.open(
            window.location.protocol +
              "//" +
              window.location.host +
              "/" +
              res.data.prefix +
              "/login"
          );
        })
        .catch((err) => {
          console.log(err);
          this.$Message.error(err.msg);
        });
    },
    handleScroll() {
      if (!this.headerHide) return;

      const scrollTop =
        document.body.scrollTop + document.documentElement.scrollTop;

      if (!this.ticking) {
        this.ticking = true;
        requestAnimation(() => {
          if (this.oldScrollTop > scrollTop) {
            this.headerVisible = true;
          } else if (scrollTop > 300 && this.headerVisible) {
            this.headerVisible = false;
          } else if (scrollTop < 300 && !this.headerVisible) {
            this.headerVisible = true;
          }
          this.oldScrollTop = scrollTop;
          this.ticking = false;
        });
      }
    },
    handleHeaderWidthChange() {
      const $breadcrumb = this.$refs.breadcrumb;
      if ($breadcrumb) {
        $breadcrumb.handleGetWidth();
        $breadcrumb.handleCheckWidth();
      }
      const $menuHead = this.$refs.menuHead;
      if ($menuHead) {
        $menuHead.handleGetMenuHeight();
      }
    },
    handleReload() {
      this.loadRouter = false;
      this.getOrderStatus("");
      this.getOrderTime("");
      this.getOrderNum("");
      this.$nextTick(() => {
        this.loadRouter = true;
      });
    },
    clear() {
      this.openImage = false;
    },
  },
  mounted() {
    document.addEventListener("scroll", this.handleScroll, { passive: true });
  },
  beforeDestroy() {
    document.removeEventListener("scroll", this.handleScroll);
  },
  created() {
    if (this.isTablet && this.showSiderCollapse) this.updateMenuCollapse(true);
  },
};
</script>
<style scoped lang="stylus">
/deep/.i-layout-menu-head-title-text {
  font-size: 15px;
  margin-left: 16px;
}

/deep/.ivu-layout-header {
  height: 60px;
  line-height: 60px;
}

/deep/.bodyBig {
  padding-left: 220px;
}

/deep/.bodySmall {
  padding-left: 60px;
}

/deep/.onBig {
  left: 220px !important;
}

/deep/.onSmall {
  left: 60px !important;
}

.i-layout-content-main {
  /* height 100%; */
}

.i-layout-header {
  z-index: 19;
}

.open-image {
  display: flex;
  align-items: center;
  justify-content: center;
  position: fixed;
  background-color: rgba(0, 0, 0, 0.6);
  height: 100%;
  width: 100%;
  top: 0;
  left: 0;
  z-index: 1000;
}

.cashier {
  width: max-content;
  height: 30px;
  background-color: rgba(64, 144, 247, 1);
  padding: 10px 15px;
  color: #fff;
  border-radius: 20px;
  cursor: pointer;
  margin-right 10px
}

.QRpic {
  width: 180px;
  height: 180px;

  img {
    width: 100%;
    height: 100%;
  }
}
</style>
