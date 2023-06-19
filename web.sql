drop table if exists service;
CREATE TABLE `service` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar (256) NOT NULL DEFAULT '' COMMENT '服务名称',
    `description` varchar (512) NOT NULL DEFAULT '' COMMENT '简介',
    `qq` varchar (30)  NOT NULL DEFAULT '0' COMMENT 'qq号',
    `image` varchar (256) NOT NULL DEFAULT '' COMMENT '图片',
    `type` enum('增值电信','认证服务','知识产权','工商服务') not null default '增值电信' COMMENT '服务类型',
    `status` enum('上线','下线','删除') not null default '上线' COMMENT '状态',
    `publishtime` int(10) unsigned NOT NULL COMMENT '发布时间',
    `updatetime` int(10) unsigned NOT NULL COMMENT '更新时间',
    `createtime` int(10) unsigned NOT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`),
    KEY `name` (`name`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='服务列表';
drop table if exists adviser;
CREATE TABLE `adviser` (
       `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
       `name` varchar (256) NOT NULL DEFAULT '' COMMENT '顾问名称',
       `description` varchar (512) NOT NULL DEFAULT '' COMMENT '简介',
       `qq` varchar (30)  NOT NULL DEFAULT '0' COMMENT 'qq号',
       `image` varchar (256) NOT NULL DEFAULT '' COMMENT '图片',
       `status` enum('上线','下线') not null default '上线' COMMENT '状态',
       `updatetime` int(10) unsigned NOT NULL COMMENT '更新时间',
       `createtime` int(10) unsigned NOT NULL COMMENT '创建时间',
       PRIMARY KEY (`id`),
       KEY `name` (`name`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='顾问列表';
drop table if exists successcase;
CREATE TABLE `successcase` (
                           `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                           `name` varchar (256) NOT NULL DEFAULT '' COMMENT '名称',
                           `description` varchar (512) NOT NULL DEFAULT '' COMMENT '简介',
                           `image` varchar (256) NOT NULL DEFAULT '' COMMENT '图片',
                           `status` enum('上线','下线') not null default '上线' COMMENT '状态',
                           `updatetime` int(10) unsigned NOT NULL COMMENT '更新时间',
                           `createtime` int(10) unsigned NOT NULL COMMENT '创建时间',
                           PRIMARY KEY (`id`),
                           KEY `name` (`name`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='经典案例';

drop table if exists answer;
CREATE TABLE `answer` (
           `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
           `name` varchar (256) NOT NULL DEFAULT '' COMMENT '名称',
           `type` enum('公司并购疑难','商标注册疑难','增值电信许可证疑难','知识产权疑难') not null default '公司并购疑难' COMMENT '类型',
           `keywords` varchar (256) NOT NULL DEFAULT '' COMMENT '关键字',
           `description` varchar (512) NOT NULL DEFAULT '' COMMENT '摘要',
           `article` text NULL COMMENT '摘要',
           `image` varchar (256) NOT NULL DEFAULT '' COMMENT '图片',
           `status` enum('上线','下线') not null default '上线' COMMENT '状态',
           `updatetime` int(10) unsigned NOT NULL COMMENT '更新时间',
           `createtime` int(10) unsigned NOT NULL COMMENT '创建时间',
           PRIMARY KEY (`id`),
           KEY `name` (`name`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='疑难解答专栏';