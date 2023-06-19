define(['jquery', 'bootstrap', 'backend', 'table', 'form','ZeroClipboard'], function ($, undefined, Backend, Table, Form,ZeroClipboard) {
    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'answer/index' + location.search,
                    add_url: 'answer/add',
                    edit_url: 'answer/edit',
                    del_url: 'answer/del',
                    multi_url: 'answer/multi',
                    import_url: 'answer/import',
                    table: 'answer',
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
                        {field: 'type', title: __('Type'), searchList: {"公司并购疑难":__('公司并购疑难'),"商标注册疑难":__('商标注册疑难'),"增值电信许可证疑难":__('增值电信许可证疑难'),"知识产权疑难":__('知识产权疑难')}, formatter: Table.api.formatter.normal},
                        {field: 'keywords', title: __('Keywords'),visible:false, operate: 'LIKE'},
                        {field: 'description', title: __('Description'),visible:false, operate: false},
                        {field: 'image', title: __('Image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'status', title: __('Status'), searchList: {"上线":__('上线'),"下线":__('下线')}, formatter: Table.api.formatter.status},
                        {field: 'updatetime', title: __('Updatetime'),visible:false, operate:false, addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'createtime', title: __('Createtime'), operate:false, addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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