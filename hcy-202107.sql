
CREATE TABLE `hcynews` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `title` varchar (256) NOT NULL DEFAULT '' COMMENT '名称',
              `keywords` varchar (256) NOT NULL DEFAULT '' COMMENT '关键字',
              `description` varchar (512) NOT NULL DEFAULT '' COMMENT '摘要',
              `content` text NULL COMMENT '新闻内容',
              `num` int(10) unsigned not null default 0 COMMENT '浏览量',
              `image` varchar (256) NOT NULL DEFAULT '' COMMENT '图片',
              `status` enum('上线','下线') not null default '上线' COMMENT '状态',
              `updatetime` int(10) unsigned NOT NULL COMMENT '更新时间',
              `createtime` int(10) unsigned NOT NULL COMMENT '创建时间',
              PRIMARY KEY (`id`),
              KEY `name` (`title`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='游企帮新闻资讯';
CREATE TABLE `content_manage` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `title` varchar (256) NOT NULL DEFAULT '' COMMENT '名称',
                `keywords` varchar (256) NOT NULL DEFAULT '' COMMENT '关键字',
                `description` varchar (512) NOT NULL DEFAULT '' COMMENT '摘要',
                `content` text NULL COMMENT '新闻内容',
                `image` varchar (256) NOT NULL DEFAULT '' COMMENT '图片',
                `sort` int(10) unsigned not null default 0 comment '排序',
                `status` enum('上线','下线') not null default '上线' COMMENT '状态',
                `updatetime` int(10) unsigned NOT NULL COMMENT '更新时间',
                `createtime` int(10) unsigned NOT NULL COMMENT '创建时间',
                PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='游企帮关于我们';
CREATE TABLE `banners` (
               `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
               `title` varchar (256) NOT NULL DEFAULT '' COMMENT '名称',
               `image` varchar (256) NOT NULL DEFAULT '' COMMENT '图片',
               `url` varchar (256) NOT NULL DEFAULT '' COMMENT '跳转链接',
               `sort` int(10) unsigned not null default 0 comment '排序',
               `status` enum('上线','下线','删除') not null default '上线' COMMENT '状态',
               `updatetime` int(10) unsigned NOT NULL COMMENT '更新时间',
               `createtime` int(10) unsigned NOT NULL COMMENT '创建时间',
               PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='游企帮首页轮播图';
CREATE TABLE `recommend_goods` (
               `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
               `title` varchar (256) NOT NULL DEFAULT '' COMMENT '名称',
               `goods_id` int(10) NOT NULL DEFAULT 1 COMMENT '商品ID',
               `sort`   int(10) not null default 1 comment '排序',
               `status` enum('上线','下线','删除') not null default '上线' COMMENT '状态',
               `updatetime` int(10) unsigned NOT NULL COMMENT '更新时间',
               `createtime` int(10) unsigned NOT NULL COMMENT '创建时间',
               PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='游企帮推荐公司';
CREATE TABLE `ad_space` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `title` varchar (256) NOT NULL DEFAULT '' COMMENT '名称',
                `image` varchar (256) NOT NULL DEFAULT '' COMMENT '图片',
                `url` varchar (256) NOT NULL DEFAULT '' COMMENT '跳转链接',
                `sort` int(10) unsigned not null default 0 comment '排序',
                `status` enum('上线','下线','删除') not null default '上线' COMMENT '状态',
                `updatetime` int(10) unsigned NOT NULL COMMENT '更新时间',
                `createtime` int(10) unsigned NOT NULL COMMENT '创建时间',
                PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='游企帮广告位列表';
CREATE TABLE `tags_list` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `title` varchar (256) NOT NULL DEFAULT '' COMMENT '名称',
                `type` enum('附带资产','行业类型','所属城市') not null default '所属城市' COMMENT '标签类型',
                `status` enum('上线','下线') not null default '上线' COMMENT '状态',
                `updatetime` int(10) unsigned NOT NULL COMMENT '更新时间',
                `createtime` int(10) unsigned NOT NULL COMMENT '创建时间',
                PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='游企帮标签管理';
CREATE TABLE `goods` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `campany_name` varchar (256) NOT NULL DEFAULT '' COMMENT '公司名称',
              `campany_name_show` varchar (256) NOT NULL DEFAULT '' COMMENT '前台展示公司名称',
              `industry_type` varchar(256) default '' COMMENT '行业类型',
              `campany_type` enum('有限责任公司','独资公司') not null default '有限责任公司' COMMENT '公司类型',
              `property` varchar(512) default '' COMMENT '附带资产；多个用","隔开',
              `create_date` varchar (10) NOT NULL DEFAULT '' COMMENT '创建日期',
              `city`  varchar(20) not null default '其他' COMMENT '所在城市',
              `create_money` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '注册资金（万元）',
              `current_money` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '实际资本（万元）',
              `sell_money` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '出售金额（万元）',
              `business_scope` varchar(512) default '' COMMENT '经营范围',
              `image` varchar (256) NOT NULL DEFAULT '' COMMENT '营业执照',
              `status` enum('待审核','审核通过','审核未通过','已下线') not null default '待审核' COMMENT '审核状态',
              `tas_types` enum('小规模','一般纳税','未纳税') not null default '小规模' COMMENT '纳税类型',
              `tas_returns` enum('正常','非正常') not null default '正常' COMMENT '报税情况',
              `invoice_status` enum('已领过发票','未领过发票') not null default '已领过发票' COMMENT '发票情况',
              `open_account` enum('已开户','未开户') not null default '已开户' COMMENT '是否开户',
              `contacts` varchar(20) default '' COMMENT '联系人姓名',
              `contacts_tel` varchar(20) default '' COMMENT '联系人电话',
              `user_id` int(10) default 0 COMMENT '用户ID',
              `adviser_id` varchar(128) default '' COMMENT '顾问ID；多个用","隔开；第一个是主顾问',
              `reason`  varchar(256) default '' comment '审核未通过原因',
              `updatetime` int(10) unsigned NOT NULL COMMENT '更新时间',
              `createtime` int(10) unsigned NOT NULL COMMENT '创建时间',
              PRIMARY KEY (`id`),
              KEY `user_id` (`user_id`),
              KEY `campany_name` (`campany_name`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='游企帮审核列表';

CREATE TABLE `user_subscribe` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `user_id` int(10) unsigned NOT NULL default 1 COMMENT '用户ID',
              `goods_id` int(10) unsigned NOT NULL default 1 COMMENT '商品ID',
              `updatetime` int(10) unsigned NOT NULL COMMENT '更新时间',
              `createtime` int(10) unsigned NOT NULL COMMENT '创建时间',
              PRIMARY KEY (`id`),
              UNIQUE KEY `user_goods_id` (`user_id`,`goods_id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='游企帮用户关注列表';

CREATE TABLE `orders_track` (
                                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                `orders_id` varchar(50) default '' COMMENT '订单号',
                                `orders_status` varchar(50) default '' COMMENT '订单状态',
                                `orders_pay_date` varchar(20) default '' COMMENT '订单支付时间',
                                `orders_ship_date` varchar(20) default '' comment '最晚发货时间',
                                `orders_to_factory_date` varchar(20) default '' comment '发给工厂时间',
                                `orders_from_factory_date` varchar(20) default '' comment '工厂发出时间',
                                `factory_number` varchar(40) default '' COMMENT '工厂物流号',
                                `factory_remark` varchar(256) default '' COMMENT '工厂备注信息',
                                `factory_number_remark` varchar(256) default '' COMMENT '工厂物流信息',
                                `orders_to_yw_date` varchar(20) default '' comment '到达义乌时间',
                                `orders_from_yw_date` varchar(20) default '' comment '义乌发出时间',
                                `yw_number` varchar(40) default "" comment '跨境物流订单号',
                                `yw_number_remark` varchar(256) default '' COMMENT '跨境物流信息',
                                `orders_to_ck_date` varchar(20) default null comment '到达仓库时间',
                                `goods_id` int(10) unsigned NOT NULL default 1 COMMENT '商品ID',
                                `update_date` timestamp default CURRENT_TIMESTAMP  COMMENT '更新时间',
                                `create_date` timestamp default CURRENT_TIMESTAMP  COMMENT '创建时间',
                                PRIMARY KEY (`id`),
                                UNIQUE orders_id(orders_id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='订单跟进表';
alter table orders_track add shop_id varchar(64) default "" comment '店铺ID';

