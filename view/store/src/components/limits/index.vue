<template>
    <div>
        <Form :label-width="100" ref="formInline" :model="formInline" :rules="ruleValidate" @submit.native.prevent style="padding-top: 16px">
            <FormItem label="身份名称："  v-if="manage" prop="role_name">
                <Input v-model="formInline.role_name" placeholder="请输入身份名称" style="width: 95%"></Input>
            </FormItem>
            <FormItem label="菜单搜索：" v-else>
                <Input suffix="ios-search" placeholder="请输入菜单名称" style="width: 50%"></Input>
            </FormItem>
            <FormItem label="状态：" v-if="manage" prop="status">
                <Switch size="large" v-model="formInline.status" :false-value="0" :true-value="1">
                    <span slot="open" :true-value="1">开启</span>
                    <span slot="close" :false-value="0">关闭</span>
                </Switch>
            </FormItem>
            <Row :gutter="24">
                <Col>
                    <div class="treeList">
                        <FormItem label="菜单设置：">
													<Tree :data="menusList" ref="tree" show-checkbox></Tree>
                            <!-- <Tree :data="menusList" ref="tree" show-checkbox @on-check-change="getSonMenusList"></Tree> -->
                        </FormItem>
                        <FormItem label="收银台设置：">
                          <Tree :data="cashList" ref="treeCash" show-checkbox></Tree>
                        </FormItem>
                    </div>
                </Col>
                <!-- <Col v-bind="grid" class="treeChildren" v-if="SonMenusList.length">
                    <CheckboxGroup v-model="formInline.son_menus" size="small">
                        <Checkbox :label="item.id" v-for="(item,index) in SonMenusList" :key="index">{{item.menu_name}}</Checkbox>
                    </CheckboxGroup>
                </Col> -->
            </Row>
            <Spin size="large" fix v-if="spinShow"></Spin>
            <div class="footer acea-row row-center-wrapper">
                <Button style="margin-right:14px" type="default" @click="cancle('formInline')">取消</Button>
                <Button type="primary" @click="handleSubmit('formInline')">确认</Button>
            </div>
        </Form>
    </div>
</template>
<script>
    import Setting from "@/setting";
    import { menuList,roleInfoApi,roleCreatApi,sonMenusList,cashierMenusList } from '@/api/setting';
    export default {
        name: 'limits',
        props: {
            manage: {
                type: Number,
                default: 0,
            }
        },
        data () {
            return {
                grid: {
                    xl: 12,
                    lg: 12,
                    md: 8,
                    sm: 20,
                    xs: 24,
                },
                routePre: Setting.routePre,
                menusList: [],
                cashList: [],
                SonMenusList: [],
                spinShow: false,
                formInline:{
                    role_name: '',
                    status: 1,
                    checked_menus: [],
                    id:0,
                    son_menus: [],
                    checked_cashier_menus: []
                },
                status:1,
                ruleValidate: {
                    role_name: [
                        { required: true, message: '请输入管理员身份', trigger: 'blur' }
                    ],
                    status: [
                        { required: true, type: 'number', message: '请选择是否开启', trigger: 'change' }
                    ]
                }
            }
        },
        created(){
        },
        methods: {
            onCancel () {
                this.formInline = {
                    role_name: '',
                    status: 1,
                    checked_menus: [],
                    checked_cashier_menus: [],
                    id:0,
                    son_menus: []
                }
                this.SonMenusList = []
            },
						//默认首页被选中并禁止更改
						checkedFun(data){
							data.forEach(item=>{
								if(item.menu_path == `${this.routePre}/home`){
									 item.disabled = true;
									 item.children.forEach(j=>{
										 if(j.menu_path==`${this.routePre}/home/index`){
											  this.formInline.checked_menus.push(j.id);
												j.checked = true;
												j.disabled = true;
												if(j.children.length){
													 j.children.forEach(v=>{
														  v.checked = true;
														  v.disabled = true;
													 })
												}
										 }
									 })
								}
							})
						},
            getMenuList(){
                this.spinShow = true
                menuList().then(res=>{
                    this.spinShow = false
									  this.checkedFun(res.data);
										this.$set(this, 'menusList', res.data);
                }).catch(err=>{
                    this.spinShow = false
                    this.$Message.error(err.msg)
                })
            },
            getCashierList(){
              cashierMenusList().then(res=>{
                this.$set(this, 'cashList', res.data);
              }).catch(err=>{
                this.$Message.error(err.msg)
              })
            },
            // 详情
            getInfo (id) {
                this.spinShow = true;
                this.formInline.id = id;
                roleInfoApi(id).then(async res => {
                    let data = res.data
                    this.formInline = data.role || this.formInline;
                    this.formInline.checked_menus = Array.from(new Set(this.formInline.rules));
                    this.formInline.checked_cashier_menus = Array.from(new Set(this.formInline.cashier_rules));
                    this.tidyRes(data.menus,0);
                    this.tidyRes(data.cashier_menus,1);
                    this.spinShow = false;
                }).catch(res => {
                    this.spinShow = false;
                    this.$Message.error(res.msg);
                })
            },
            tidyRes (menus,num) {
                let data = [];
                menus.map((menu) => {
                  data.push(this.initMenu(menu,num));
                });
                if(num){
                  this.$set(this, 'cashList', data);
                }else{
                  this.checkedFun(data);
                  this.$set(this, 'menusList', data);
                }
            },
            initMenu (menu,num) {
                let data = {};
                let checkMenus = ',' + this.formInline.checked_menus.join(',') + ',';
                let checkCashMenus = ',' + this.formInline.checked_cashier_menus.join(',') + ',';
                // if(num){
                //   checkMenus = ',' + this.formInline.checked_menus.join(',') + ',';
                // }else{
                //   checkMenus = ',' + this.formInline.checked_cashier_menus.join(',') + ',';
                // }
                data.title = menu.title;
                data.id = menu.id;
								data.menu_path = menu.menu_path;
                // data.expand = true;
                if (menu.children && menu.children.length > 0) {
                    data.children = [];
                    menu.children.map((child) => {
                        data.children.push(this.initMenu(child,num));
                    })
                } else {
                    if(num){
                      data.checked = checkCashMenus.indexOf(String(',' + data.id + ',')) !== -1;
                    }else{
                      data.checked = checkMenus.indexOf(String(',' + data.id + ',')) !== -1;
                    }
                }
                return data;
            },
            // getSonMenusList(data, node){
            //     if(!node.children && node.checked){
            //         sonMenusList(this.formInline.id,node.id).then(res=>{
            //             let data = res.data;
            //             this.SonMenusList = data.menus;
            //             this.formInline.son_menus = data.checked_rules;
            //         }).catch(err=>{
            //             this.$Message.error(err.msg)
            //         })
            //     }
            // },
            cancle(){
              this.onCancel();
              this.$emit('updateRole',false)
            },
            // 提交
            handleSubmit (name) {
                this.$refs[name].validate((valid) => {
                    if (valid) {
											  this.formInline.checked_menus = [];
                        this.formInline.checked_cashier_menus = [];
                        this.$refs.tree.getCheckedAndIndeterminateNodes().map((node) => {
                            this.formInline.checked_menus.push(node.id);
                        });
                        this.$refs.treeCash.getCheckedAndIndeterminateNodes().map((node) => {
                          this.formInline.checked_cashier_menus.push(node.id);
                        });
                        // if (this.formInline.checked_menus.length === 0) return this.$Message.warning('请至少选择一个菜单权限');
                        // if (this.formInline.checked_cashier_menus.length === 0) return this.$Message.warning('请至少选择一个收银台权限');
                        let checkedMenus = this.formInline.checked_menus;
                        checkedMenus = checkedMenus.concat(this.formInline.son_menus);
                        this.formInline.checked_menus = Array.from(new Set(checkedMenus))
                        roleCreatApi(this.formInline).then(async res => {
                            this.$Message.success(res.msg);
                            this.$emit('updateRole',false)
                            this.$refs[name].resetFields();
                            this.onCancel();
                        }).catch(res => {
                            this.$Message.error(res.msg);
                        })
                    } else {
                        return false
                    }
                })
            }
        }
    }
</script>
<style scoped lang="stylus">
    /deep/.ivu-tree ul li{
        margin: 0;
    }
    // /deep/.ivu-btn-primary{
    //     padding 0 30px;
    // }
    .tr
    .treeChildren /deep/.ivu-checkbox-wrapper{
        margin 0 30px 13px 0;
    }
    .treeList{
        min-height: 500px;
        // border-right 1px solid #eee;
        // width 285px;
				width 676px;
    }
    .footer{
        display: block;
        width: 100%;
        text-align: right;
        padding: 12px 18px;
        box-shadow: 0px -2px 4px 0px rgba(0, 0, 0, 0.05);
    }
</style>