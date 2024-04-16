define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'shop/orderstrack/index',
                    add_url: 'shop/orderstrack/add',
                    edit_url: 'shop/orderstrack/edit',
                    del_url: 'shop/orderstrack/del',
                    multi_url: 'shop/orderstrack/multi',
                    table: 'orders_track',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'orders_id', title: __('Orders_id'),operate: "LIKE"},
                        {
                            field: 'orders_status',
                            title: __('Orders_status'),
                            searchList: {
                                "pending": "待出货-未申请",
                                "packed": "待出货-已申请",
                                "packed_unconfirmed": "已申请-未确认",
                                "ready_to_ship": "待出货-已取件",
                                "shipped": "运输中",
                                "in_cancel": "取消中",
                                "completed": "已完成",
                                "canceled": "已取消",
                                "confirmed": "已收货",
                                "returned": "已退货",
                                "other": "其他"
                            },
                            formatter: Table.api.formatter.status
                        },
                        {
                            field: 'orders_pay_date',
                            title: __('Orders_pay_date'),
                            operate: 'DATE_RANGE',
                            addclass: 'datetimerange'
                        },
                        {field: 'orders_ship_date', title: __('Orders_ship_date'), operate: 'LIKE'},
                        {field: 'orders_to_factory_date', title: __('Orders_to_factory_date'), operate: 'LIKE'},
                        {field: 'orders_from_factory_date', title: __('Orders_from_factory_date'), operate: 'LIKE'},
                        {field: 'factory_number', title: __('Factory_number')},
                        {field: 'factory_remark', title: __('Factory_remark'), operate: false},
                        {field: 'factory_number_remark', title: __('Factory_number_remark'), operate: false},
                        {field: 'orders_to_yw_date', title: __('Orders_to_yw_date'), operate: false},
                        {field: 'orders_from_yw_date', title: __('Orders_from_yw_date'), operate: false},
                        {field: 'yw_number', title: __('Yw_number'), operate: false},
                        {field: 'yw_number_remark', title: __('Yw_number_remark'), operate: false},
                        {field: 'orders_to_ck_date', title: __('Orders_to_ck_date'), operate: false},
                        {
                            field: 'shop_id', title: __('店铺名称'),searchList:{"57948505":"台湾二店","954647584":"台湾一店"}, formatter: function (value, row) {
                                if (value !== '954647584') {
                                    return '台湾二店';
                                } else {
                                    return '台湾一店';
                                }
                            }
                        },
                        {field: 'update_date', title: __('Update_date'), operate: false},
                        {field: 'create_date', title: __('Create_date'), operate: false},
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
            //生效
            $('.btn-batch-update').on('click', function () {
                var ids = Table.api.selectedids(table);
                console.log(ids);
                Fast.api.open("shop/orderstrack/batch_update", '批量更新', {
                    callback: function (data) {
                        $('#table').bootstrapTable('refresh')
                    }
                });
                return false;

            });
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        batch_update: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});