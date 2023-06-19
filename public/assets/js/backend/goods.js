define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'goods/index' + location.search,
                    add_url: 'goods/add',
                    edit_url: 'goods/edit',
                    del_url: 'goods/del',
                    multi_url: 'goods/multi',
                    import_url: 'goods/import',
                    table: 'goods',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                showToggle: false,
                showExport: false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'campany_name', title: __('Campany_name'),operate: 'LIKE'},
                        {field: 'campany_name_show', title: __('Campany_name_show'), visible:false, operate: false},
                        {field: 'industry_type', title: __('Industry_type'),visible:false, operate: false},
                        {field: 'campany_type', title: __('Campany_type'), searchList: {"有限责任公司":__('有限责任公司'),"独资公司":__('独资公司')}, formatter: Table.api.formatter.normal},
                        {field: 'property', title: __('Property'), visible:false, operate: false},
                        {field: 'create_date', title: __('Create_date'), visible:false, operate: false},
                        {field: 'create_money', title: __('Create_money'),visible:false, operate: false},
                        {field: 'current_money', title: __('Current_money'),visible:false, operate: false},
                        {field: 'sell_money', title: __('Sell_money'),visible:false, operate: false},
                        {field: 'business_scope', title: __('Business_scope'), visible:false, operate: false},
                        {field: 'image', title: __('Image'), visible:false, operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'status', title: __('Status'), searchList: {"待审核":__('待审核'),"审核通过":__('审核通过'),"审核未通过":__('审核未通过'),"已下线":__('已下线')}, formatter: Table.api.formatter.status},
                        {field: 'tas_types', title: __('Tas_types'), searchList: {"小规模":__('小规模'),"一般纳税":__('一般纳税'),"未纳税":__('未纳税')}, formatter: Table.api.formatter.normal},
                        {field: 'tas_returns', title: __('Tas_returns'), searchList: {"正常":__('正常'),"非正常":__('非正常')}, formatter: Table.api.formatter.normal},
                        {field: 'invoice_status', title: __('Invoice_status'), searchList: {"已领过发票":__('已领过发票'),"未领过发票":__('未领过发票')}, formatter: Table.api.formatter.status},
                        {field: 'open_account', title: __('Open_account'), searchList: {"已开户":__('已开户'),"未开户":__('未开户')}, formatter: Table.api.formatter.normal},
                        {field: 'contacts', title: __('Contacts'), visible:false, operate: false},
                        {field: 'user_id', title: __('所属用户ID'), visible:false, operate: false},
                        {field: 'contacts_tel', title: __('Contacts_tel'),visible:false, operate: false},
                        {field: 'adviser_id', title: __('Adviser_id'), operate: 'LIKE'},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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
            $(document).on('click', '.data-tips-image', function () {
                var img = new Image();
                var imgWidth = this.getAttribute('data-width') || '560px';
                img.onload = function () {
                    var $content = $(img).appendTo('body').css({background: '#fff', width: imgWidth, height: 'auto'});
                    Layer.open({
                        type: 1, area: imgWidth, title: false, closeBtn: 1,
                        skin: 'layui-layer-nobg', shadeClose: true, content: $content,
                        end: function () {
                            $(img).remove();
                        },
                        success: function () {

                        }
                    });
                };
                img.onerror = function (e) {

                };
                img.src = this.getAttribute('.data-tips-image') || this.src;
            });
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