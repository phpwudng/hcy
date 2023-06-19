define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'service/index' + location.search,
                    add_url: 'service/add',
                    edit_url: 'service/edit',
                    del_url: 'service/del',
                    multi_url: 'service/multi',
                    import_url: 'service/import',
                    table: 'service',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                showToggle: false,
                showColumns: false,
                showExport: false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'description', title: __('Description'), operate: false},
                        {field: 'qq', title: __('Qq'), operate: false},
                        {field: 'image', title: __('Image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'type', title: __('Type'), searchList: {"增值电信":__('增值电信'),"认证服务":__('认证服务'),"只是产品":__('只是产品'),"工商服务":__('工商服务')}, formatter: Table.api.formatter.normal},
                        {field: 'status', title: __('Status'), searchList: {"上线":__('上线'),"下线":__('下线'),"删除":__('删除')}, formatter: Table.api.formatter.status},
                        {field: 'publishtime', title: __('Publishtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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