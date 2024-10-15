<template>
    <!-- 门店-设备管理 -->
    <div>
        <Card :bordered="false" dis-hover class="ivu-mt" :padding="0">
            <div class="new_card_pd">
                <!-- 筛选条件 -->
                <Form ref="artFrom" :model="artFrom" :label-width="labelWidth" inline :label-position="labelPosition"
                    @submit.native.prevent>
                    <FormItem label="门店名称" label-for="device_id">
                        <Input placeholder="请输入门店名称" v-model="artFrom.store_name" class="input-add mr14" />
                        
                    </FormItem>
                    <FormItem label="设备ID" label-for="device_id">
                        <Input placeholder="请输入设备ID" v-model="artFrom.device_id" class="input-add mr14" />
                        <Button type="primary" @click="deviceSearchs()">查询</Button>
                    </FormItem>
                </Form>
            </div>
        </Card>
        <Card :bordered="false" dis-hover class="ivu-mt">
            <!-- 相关操作 -->
            <Row type="flex">
                <Col v-bind="grid">
                <Button v-auth="['admin-user-group']" type="primary" @click="add">添加设备</Button>
                </Col>
            </Row>
            <!-- 用户分组表格 -->
            <Table :columns="columns1" :data="groupLists" ref="table" class="mt25" :loading="loading" highlight-row
                no-userFrom-text="暂无数据" no-filtered-userFrom-text="暂无筛选结果">
                <template slot-scope="{ row, index }" slot="icons">
                    <viewer>
                        <div class="tabBox_img">
                            <img v-lazy="row.icon">
                        </div>
                    </viewer>
                </template>
                <template slot-scope="{ row, index }" slot="action">
                    <a @click="edit(row)">修改</a>
                    <Divider type="vertical" />
                    <a @click="del(row, '删除设备', index)">删除</a>
                </template>
                <!-- 二维码图片 -->
                <template slot-scope="{ row, index }" slot="info">
                    <div class="tabBox">
                        <div class="tabBox_img" v-viewer>
                            <img v-lazy="row.code_path" width="50" />
                        </div>
                    </div>
                </template>
            </Table>
            <div class="acea-row row-right page">
                <Page :total="total" show-elevator show-total @on-change="pageChange" :page-size="groupFrom.limit" />
            </div>
        </Card>

        <add-device ref="template"></add-device>
    </div>
</template>
    
<script>
import { mapState } from 'vuex';
import { getDevice } from '@/api/store';

import addDevice from '../components/addDevice.vue'
export default {
    name: "device",
    components: { addDevice },
    data() {
        return {
            artFrom: {
                page: 1,
                store_name: '',
                limit: 15,
                device_id: ''
            },
            grid: {
                xl: 7,
                lg: 7,
                md: 12,
                sm: 24,
                xs: 24
            },
            loading: false,
            columns1: [
                {
                    title: 'ID',
                    key: 'id',
                    width: 80
                },
                {
                    title: '门店名称',
                    key: 'store_name',
                },
                {
                    title: '语音播报设备ID',
                    key: 'device_id',
                },

                {
                    title: '飞鹅打印机SN',
                    key: 'feie_sn',
                },
                {
                    title: '飞鹅打印机KEY',
                    key: 'feie_key',
                },

                
                {
                    title: '备注',
                    key: 'remark',
                },
                {
                    title: '门店收款二维码',
                    slot: "info",
                },

                {
                    title: '操作',
                    slot: 'action',
                    fixed: 'right',
                    minWidth: 120,
                    maxWidth: 140
                }
            ],
            groupFrom: {
                page: 1,
                limit: 15
            },
            groupLists: [],
            total: 0
        }
    },
    computed: {
        ...mapState('admin/layout', [
            'isMobile'
        ]),
        labelWidth() {
            return this.isMobile ? undefined : 75;
        },
        labelPosition() {
            return this.isMobile ? 'top' : 'left';
        }
    },
    created() {
        this.getList();
    },
    methods: {
        deviceSearchs() {
            this.getList()
        },
        // 添加
        add() {
            this.$refs.template.title = "添加设备";
            this.$refs.template.add = 1;
            this.$refs.template.isTemplate = true;
        },
        // 设备列表
        getList() {
            this.loading = true;
            getDevice(this.artFrom).then(async res => {
                let data = res.data;
                this.groupLists = data.list;
                this.total = data.count;
                this.loading = false;
            }).catch(res => {
                this.loading = false;
                this.$Message.error(res.msg);
            })
        },
        pageChange(index) {
            this.groupFrom.page = index;
            this.getList();
        },
        //修改
        edit(row) {
            this.$refs.template.title = "编辑设备";
            this.$refs.template.isTemplate = true;

            row.store_id = [row.store_id]
            this.$refs.template.formItem = row
            console.log(row)
        },
        // 删除
        del(row, tit, num) {
            let delfromData = {
                title: tit,
                num: num,
                url: `store/del_device/${row.id}`,
                method: 'DELETE',
                ids: ''
            };
            this.$modalSure(delfromData).then((res) => {
                this.$Message.success(res.msg);
                this.groupLists.splice(num, 1);
                if (!this.groupLists.length) {
                    this.groupFrom.page =
                        this.groupFrom.page == 1 ? 1 : this.groupFrom.page - 1;
                }
                this.getList();
            }).catch(res => {
                this.$Message.error(res.msg);
            });
        },
    }
}
</script>