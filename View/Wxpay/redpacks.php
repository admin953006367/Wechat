<extend name="../../Admin/View/Common/element_layout"/>
<block name="content">
    <div id="app" v-cloak>
        <el-card>
            <div slot="header" class="clearfix">
                <span>红包发送列表</span>
            </div>
            <div>
                <el-form :inline="true" :model="searchData" class="demo-form-inline">
                    <el-form-item label="appid">
                        <el-input v-model="searchData.app_id" placeholder="请输入小程序appid"></el-input>
                    </el-form-item>
                    <el-form-item label="open_id">
                        <el-input v-model="searchData.open_id" placeholder="请输入用户openid"></el-input>
                    </el-form-item>
                    <el-form-item label="订单号">
                        <el-input v-model="searchData.mch_billno" placeholder="请输入订单号"></el-input>
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" @click="searchEvent">查询</el-button>
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" @click="handleEvent">调用处理</el-button>
                    </el-form-item>
                </el-form>
            </div>
            <div>
                <el-table
                        :data="users"
                        border
                        style="width: 100%">
                    <el-table-column
                            prop="app_id"
                            align="center"
                            label="appid"
                            min-width="180">
                    </el-table-column>
                    <el-table-column
                            prop="mch_billno"
                            label="订单号"
                            align="center"
                            min-width="180">
                    </el-table-column>
                    <el-table-column
                            prop="open_id"
                            label="open_id"
                            align="center"
                            min-width="250">
                    </el-table-column>
                    <el-table-column
                            label="发放金额"
                            align="center"
                            min-width="100">
                        <template slot-scope="scope">
                            <div>{{ scope.row.total_amount/100 }}</div>
                        </template>
                    </el-table-column>
                    <el-table-column
                            label="红包信息"
                            align="center"
                            min-width="250">
                        <template slot-scope="scope">
                            <div><b>发送者</b>：{{ scope.row.send_name }}</div>
                            <div><b>祝福语</b>：{{ scope.row.wishing }}</div>
                            <div><b>活动名称</b>：{{ scope.row.act_name }}</div>
                        </template>
                    </el-table-column>
                    <el-table-column
                            prop="process_count"
                            label="处理次数"
                            align="center"
                            min-width="100">
                    </el-table-column>
                    <el-table-column
                            label="处理状态"
                            align="center"
                            min-width="100">
                        <template slot-scope="scope">
                            <div v-if="scope.row.status==1">已完成</div>
                            <div v-else>未完成</div>
                        </template>
                    </el-table-column>
                    <el-table-column
                            align="center"
                            label="下次处理时间"
                            min-width="180">
                        <template slot-scope="scope">
                            {{scope.row.next_process_time|getFormatDatetime}}
                        </template>
                    </el-table-column>
                    <el-table-column
                            align="center"
                            label="创建时间"
                            min-width="180">
                        <template slot-scope="scope">
                            {{scope.row.create_time|getFormatDatetime}}
                        </template>
                    </el-table-column>
                    <el-table-column
                            align="center"
                            label="更新时间"
                            min-width="180">
                        <template slot-scope="scope">
                            {{scope.row.update_time|getFormatDatetime}}
                        </template>
                    </el-table-column>
                    <el-table-column
                            fixed="right"
                            label="操作"
                            align="center"
                            min-width="200">
                        <template slot-scope="scope">
                            <el-button @click="detailEvent(scope.row.send_result)" type="primary">结果</el-button>
                            <el-button @click="deleteEvent(scope.row)" type="danger">删除</el-button>
                        </template>
                    </el-table-column>
                </el-table>
            </div>
            <div class="page-container">
                <el-pagination
                        background
                        :page-size="limit"
                        :page-count="totalPages"
                        :current-page="page"
                        :total="totalItems"
                        layout="prev, pager, next"
                        @current-change="currentChangeEvent">
                </el-pagination>
            </div>
        </el-card>
        <el-dialog
                size="small"
                :visible.sync="detailDialogVisible"
                width="600px">
            <el-form ref="form" size="small" label-width="140px">
                <div v-for="(item,key) in resultDetail">
                    <el-form-item v-if="item" :label="key">
                        {{ item }}
                    </el-form-item>
                </div>
            </el-form>
        </el-dialog>
    </div>
    <style>
        .avatar {
            width: 60px;
            height: 60px;
        }

        .page-container {
            margin-top: 0px;
            text-align: center;
            padding: 10px;
        }
    </style>
    <script>
        $(document).ready(function () {
            new Vue({
                el: "#app",
                data: {
                    searchData: {
                        open_id: "",
                        app_id: "",
                        mch_billno: ""
                    },
                    users: [],
                    page: 1,
                    limit: 20,
                    totalPages: 0,
                    totalItems: 0,
                    resultDetail: {},
                    detailDialogVisible: false
                },
                mounted() {
                    this.getRedpacks();
                },
                methods: {
                    handleEvent() {
                        let _this = this;
                        this.httpPost('{:U("Wechat/Wxpay/handleRedpack")}', {}, function (res) {
                            if (res.status) {
                                _this.$message.success('处理成功');
                                _this.getRedpacks();
                            } else {
                                this.$message.error(res.msg);
                            }
                        })
                    },
                    deleteEvent(row) {
                        var postData = {
                            id: row.id
                        };
                        console.log('callback', postData);
                        var _this = this;
                        this.$confirm('是否确认删除该记录', '提示', {
                            callback: function (e) {
                                if (e !== 'confirm') {
                                    return;
                                }
                                _this.httpPost('{:U("Wechat/Wxpay/deleteRedpacks")}', postData, function (res) {
                                    if (res.status) {
                                        _this.$message.success('删除成功');
                                        _this.getRedpacks();
                                    } else {
                                        _this.$message.error(res.msg);
                                    }
                                })
                            }
                        });

                    },
                    detailEvent(refund_result) {
                        console.log('refund_result', refund_result);
                        if (refund_result) {
                            this.resultDetail = JSON.parse(refund_result);
                        }
                        this.detailDialogVisible = true;
                    },
                    searchEvent() {
                        this.page = 1;
                        this.getRedpacks();
                    },
                    currentChangeEvent(page) {
                        this.page = page;
                        this.getRedpacks();
                    },
                    getRedpacks: function () {
                        var _this = this;
                        var where = Object.assign({
                            page: this.page,
                            limit: this.limit
                        }, this.searchData);
                        $.ajax({
                            url: "{:U('Wechat/Wxpay/redpacks')}",
                            dataType: 'json',
                            type: 'get',
                            data: where,
                            success: function (res) {
                                console.log("res", res);
                                if (res.status) {
                                    _this.users = res.data.items;
                                    _this.page = res.data.page;
                                    _this.limit = res.data.limit;
                                    _this.totalPages = res.data.total_pages;
                                    _this.totalItems = res.data.total_items
                                }
                            }
                        })
                    }
                }
            })
        });
    </script>
</block>

