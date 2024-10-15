(global["webpackJsonp"]=global["webpackJsonp"]||[]).push([["components/kefuIcon/index"],{"536d":function(t,e,n){},"94d0":function(t,e,n){"use strict";n.r(e);var u=n("9cda"),o=n.n(u);for(var c in u)["default"].indexOf(c)<0&&function(t){n.d(e,t,(function(){return u[t]}))}(c);e["default"]=o.a},"98d2":function(t,e,n){"use strict";n.d(e,"b",(function(){return u})),n.d(e,"c",(function(){return o})),n.d(e,"a",(function(){}));var u=function(){var t=this.$createElement;this._self._c},o=[]},"9cda":function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var u=n("26cb"),o=(getApp(),{name:"kefuIcon",props:{ids:{type:Number,default:0},routineContact:{type:Number,default:0},storeInfo:{type:Object,default:function(){return{}}},goodsCon:{type:Number,default:0}},computed:(0,u.mapGetters)(["userInfo"]),data:function(){return{top:"480"}},mounted:function(){},methods:{setTouchMove:function(t){t.touches[0].clientY<480&&t.touches[0].clientY>66&&(this.top=t.touches[0].clientY)},licks:function(){var t={};t="string"===typeof this.userInfo?JSON.parse(this.userInfo):this.userInfo;var e="/pages/extension/customer_list/chat?productId=".concat(this.ids);this.$util.getCustomer(t,e)}},created:function(){}});e.default=o},c4c6:function(t,e,n){"use strict";n.r(e);var u=n("98d2"),o=n("94d0");for(var c in o)["default"].indexOf(c)<0&&function(t){n.d(e,t,(function(){return o[t]}))}(c);n("d815");var r=n("f0c5"),i=Object(r["a"])(o["default"],u["b"],u["c"],!1,null,null,null,!1,u["a"],void 0);e["default"]=i.exports},d815:function(t,e,n){"use strict";var u=n("536d"),o=n.n(u);o.a}}]);
;(global["webpackJsonp"] = global["webpackJsonp"] || []).push([
    'components/kefuIcon/index-create-component',
    {
        'components/kefuIcon/index-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('543d')['createComponent'](__webpack_require__("c4c6"))
        })
    },
    [['components/kefuIcon/index-create-component']]
]);
