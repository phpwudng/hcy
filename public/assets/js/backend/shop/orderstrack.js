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
                        {field: 'id', title: __('Id')},
                        {field: 'orders_id', title: __('Orders_id')},
                        {field: 'orders_status', title: __('Orders_status'), formatter: Table.api.formatter.status},
                        {field: 'orders_pay_date', title: __('Orders_pay_date'),operate:'LIKE'},
                        {field: 'orders_ship_date', title: __('Orders_ship_date'),operate:'LIKE'},
                        {field: 'orders_to_factory_date', title: __('Orders_to_factory_date'),operate:'LIKE'},
                        {field: 'orders_from_factory_date', title: __('Orders_from_factory_date'),operate:'LIKE'},
                        {field: 'factory_number', title: __('Factory_number')},
                        {field: 'factory_remark', title: __('Factory_remark')},
                        {field: 'factory_number_remark', title: __('Factory_number_remark')},
                        {field: 'orders_to_yw_date', title: __('Orders_to_yw_date'),operate:'LIKE'},
                        {field: 'orders_from_yw_date', title: __('Orders_from_yw_date'),operate:'LIKE'},
                        {field: 'yw_number', title: __('Yw_number')},
                        {field: 'yw_number_remark', title: __('Yw_number_remark')},
                        {field: 'orders_to_ck_date', title: __('Orders_to_ck_date'),operate:'LIKE'},
                        {field: 'goods_id', title: __('Goods_id')},
                        {field: 'update_date', title: __('Update_date'),operate: false},
                        {field: 'create_date', title: __('Create_date'),operate: false},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
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