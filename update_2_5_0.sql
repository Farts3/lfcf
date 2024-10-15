-- 增加配置分类
INSERT INTO `eb_system_config_tab` (`id`, `is_store`, `pid`, `title`, `eng_title`, `status`, `info`, `icon`, `type`, `sort`) VALUES
    (NULL, 1, 5, '桌码配置', 'store_table_code', 1, 0, 'ios-bonfire', 0, 0);

-- 增加佣金计算方式配置
INSERT INTO `eb_system_config` (`id`, `is_store`, `menu_name`, `type`, `input_type`, `config_tab_id`, `parameter`, `upload_type`, `required`, `width`, `high`, `value`, `info`, `desc`, `sort`, `status`) VALUES
(NULL, '0', 'brokerage_compute_type', 'radio', '', '77', '1=>商品售价\r\n2=>实付金额\r\n3=>商品利润', 0, '', '0', '0', '1', '佣金计算方式', '佣金计算方式，商品售价：订单商品售价*佣金比例，实付金额：订单商品实际支付金额*佣金比例，商品利润：（商品实付金额-商品成本价）*佣金比率', '0', '1'),
(NULL, '0', 'store_user_avatar', 'radio', '', 26, '1=>开启\n0=>关闭', 0, '', 0, 0, '0', '强制获取昵称头像', '是否在小程序用户授权之后，弹窗获取用户的昵称和头像', 0, 1),
(NULL, '0', 'store_delivery_scope', 'radio', '', 5, '1=>开启\r\n0=>关闭', 0, '', 0, 0, '1', '配送范围', '开启：用户下单需要验证是否在门店配送范围内，如果不在无法下单；关闭：不做门店范围验证，用户可选择所有门店', 0, 1),
(NULL, '0', 'store_splicing_switch', 'radio', '', 5, '1=>开启\r\n0=>关闭', 0, '', 0, 0, '1', '拼单开关', '开启后，用户可以参与拼单', 0, 1),
(NULL, 1, 'store_code_switch', 'radio', '', 90, '0=>关闭\n1=>开启', 1, '', 0, 0, '\"0\"', '桌码开关', '门店桌码开关', 9, 1),
(NULL, 1, 'store_checkout_method', 'radio', '', 90, '1=>合并结账\n2=>单独结账', 1, '', 0, 0, '\"1\"', '结账方式', '桌码结账方式', 8, 1),
(NULL, 1, 'store_number_diners_window', 'radio', '', 90, '0=>关闭\n1=>开启', 1, '', 0, 0, '\"0\"', '用餐人数弹窗', '用餐人数弹窗', 7, 1),
(NULL, 1, 'store_printing_timing', 'checkbox', '', 79, '1=>下单后打印\n2=>支付后打印', 0, '', 0, 0, '[\"1\",\"2\"]', '桌码自动打印', '小票自动打印选择【注：按钮控制桌码小票打印】', 5, 1),
(NULL, '0', 'rebate_points_orders_time', 'text', 'number', 27, '', 0, '', 100, 0, '1', '积分商品未支付', '积分商品未支付取消订单时间，单位（小时）', 0, 1),
(NULL, '0', 'collate_not_operating_time', 'text', 'number', 27, '', 0, '', 100, 0, '1', '拼单未操作', '拼单长期未操作取消拼单记录，单位（小时）', 0, 1),
(NULL, '0', 'table_code_not_operating_time', 'text', 'number', 27, '', 0, '', 100, 0, '1', '桌码未操作', '桌码长期未操作取消桌码记录时间，单位（小时）', 0, 1),
(NULL, 0, 'is_cashier_yue_pay_verify', 'radio', '', 30, '0=>否\n1=>是', 0, '', 0, 0, '1', '收银台余额支付验证', '收银台余额支付是否需要验证【是/否】', 0, 1);

UPDATE `eb_system_config` SET `sort` = '6' WHERE `menu_name` = 'store_pay_success_printing_switch';


-- 优惠券
ALTER TABLE `eb_store_coupon_issue` MODIFY column  `type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '优惠券类型 0-通用 1-品类券 2-商品券 3-品牌';
ALTER TABLE `eb_store_coupon_issue` MODIFY column  `receive_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 手动领取，3赠送券';
ALTER TABLE `eb_store_coupon_issue` ADD `brand_id` INT(10) NOT NULL DEFAULT '0' COMMENT '品牌ID' AFTER `category_id`;

-- 删除唯一索引
ALTER TABLE `eb_store_coupon_issue_user` DROP INDEX `uid`;

--
-- 转存表中的数据 `eb_luck_lottery` 修改数据表 添加字段
--
ALTER TABLE `eb_luck_lottery` ADD COLUMN  `total_lottery_num` smallint(12) NOT NULL DEFAULT '1' COMMENT '积分抽奖总次数' AFTER `lottery_num`;

--
-- 转存表中的数据 `eb_luck_lottery_record` 修改数据表 添加字段
--
ALTER TABLE `eb_luck_lottery_record` ADD COLUMN  `oid` INT(12) NULL DEFAULT '0' COMMENT '订单id' AFTER `uid`;

--
-- 转存表中的数据 `eb_store_order` 修改数据表 修改字段
--
ALTER TABLE `eb_store_order` MODIFY column `type` SMALLINT(5) NOT NULL DEFAULT '0' COMMENT '类型 0:普通、1：秒杀、2:砍价、3:拼团、4:积分、5:套餐、6:预售、7:新人礼、8:抽奖、9:拼单、10:桌码';

ALTER TABLE `eb_store_order` ADD `kuaidi_label` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '快递单号图片' AFTER `express_dump`;

ALTER TABLE `eb_store_order` ADD `change_price` DECIMAL(8,2) NOT NULL DEFAULT '0.00' COMMENT '改价优惠金额' AFTER `first_order_price`;

--
-- 转存表中的数据 `eb_store_cart` 修改数据表 修改字段
--
ALTER TABLE `eb_store_cart` MODIFY column `type` SMALLINT(5) NOT NULL DEFAULT '0' COMMENT '类型 0:普通、1：秒杀、2:砍价、3:拼团、4:积分、5:套餐、9:拼单、10:桌码';
ALTER TABLE `eb_store_cart` ADD COLUMN  `collate_code_id` int(12) NOT NULL DEFAULT '0' COMMENT '拼单ID/桌码ID';

-- 商品表增加系统表单ID
ALTER TABLE `eb_store_product` ADD `system_form_id` INT(10) NOT NULL DEFAULT '0' COMMENT '系统表单ID' AFTER `custom_form`;
ALTER TABLE `eb_store_seckill` ADD `system_form_id` INT(10) NOT NULL DEFAULT '0' COMMENT '系统表单ID' AFTER `custom_form`;
ALTER TABLE `eb_store_combination` ADD `system_form_id` INT(10) NOT NULL DEFAULT '0' COMMENT '系统表单ID' AFTER `custom_form`;
ALTER TABLE `eb_store_bargain` ADD `system_form_id` INT(10) NOT NULL DEFAULT '0' COMMENT '系统表单ID' AFTER `custom_form`;
ALTER TABLE `eb_store_integral` ADD `system_form_id` INT(10) NOT NULL DEFAULT '0' COMMENT '系统表单ID' AFTER `custom_form`;

-- 更改商品分类表
RENAME TABLE `eb_store_category` TO `eb_store_product_category`;

-- 分类表
ALTER TABLE `eb_category` ADD `is_show` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '是否显示' AFTER `other`;
--
-- 转存表中的数据 `eb_category` 修改数据表 修改字段
--
ALTER TABLE `eb_category` MODIFY column `group` tinyint(1) NOT NULL DEFAULT '0' COMMENT '分类类型0=标签分类，1=快捷短语分类,2=商品标签分类，3=商品参数模版,4=企业渠道码，5=门店分类，6=桌码分类';

ALTER TABLE `eb_category` ADD INDEX `group` (`group`);
-- 门店表
ALTER TABLE `eb_system_store` ADD `type` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '门店类型：1自营2加盟' AFTER `id`;
ALTER TABLE `eb_system_store` ADD `cate_id` INT(10) NOT NULL DEFAULT '0' COMMENT '门店分类ID' AFTER `type`;
ALTER TABLE `eb_system_store` ADD `cate_com` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '门店分类组合' AFTER `cate_id`;

-- 增加商品、秒杀适用门店
ALTER TABLE `eb_store_product` ADD `applicable_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '适用门店：0：仅平台1：所有2：部分' AFTER `system_form_id`;
ALTER TABLE `eb_store_product` ADD `applicable_store_id` LONGTEXT NULL DEFAULT NULL COMMENT '适用门店ids' AFTER `applicable_type`;

ALTER TABLE `eb_store_seckill` ADD `applicable_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '适用门店：0：仅平台1：所有2：部分' AFTER `system_form_id`;
ALTER TABLE `eb_store_seckill` ADD `applicable_store_id` LONGTEXT NULL DEFAULT NULL COMMENT '适用门店ids' AFTER `applicable_type`;

ALTER TABLE `eb_store_combination` ADD `applicable_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '适用门店：0：仅平台1：所有2：部分' AFTER `system_form_id`;
ALTER TABLE `eb_store_combination` ADD `applicable_store_id` LONGTEXT NULL DEFAULT NULL COMMENT '适用门店ids' AFTER `applicable_type`;

ALTER TABLE `eb_store_bargain` ADD `applicable_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '适用门店：0：仅平台1：所有2：部分' AFTER `system_form_id`;
ALTER TABLE `eb_store_bargain` ADD `applicable_store_id` LONGTEXT NULL DEFAULT NULL COMMENT '适用门店ids' AFTER `applicable_type`;

ALTER TABLE `eb_store_integral` ADD `applicable_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '适用门店：0：仅平台1：所有2：部分' AFTER `system_form_id`;
ALTER TABLE `eb_store_integral` ADD `applicable_store_id` LONGTEXT NULL DEFAULT NULL COMMENT '适用门店ids' AFTER `applicable_type`;

ALTER TABLE `eb_store_promotions` ADD `applicable_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '适用门店：0：仅平台1：所有2：部分' AFTER `product_id`;
ALTER TABLE `eb_store_promotions` ADD `applicable_store_id` LONGTEXT NULL DEFAULT NULL COMMENT '适用门店ids' AFTER `applicable_type`;

-- 秒杀商品
ALTER TABLE `eb_store_seckill` ADD `activity_id` INT(10) NOT NULL DEFAULT '0' COMMENT '活动ID' AFTER `id`;
-- 修改秒杀商品表：时间段ID
ALTER TABLE `eb_store_seckill` CHANGE `time_id` `time_id` LONGTEXT NOT NULL COMMENT '时间段ID，多个';

-- 次卡商品
ALTER TABLE `eb_store_product` MODIFY column `product_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '商品类型0:普通商品，1：卡密，2：优惠券，3：虚拟商品,4：次卡商品';

ALTER TABLE `eb_store_seckill` MODIFY column `product_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '商品类型0:普通商品，1：卡密，2：优惠券，3：虚拟商品,4：次卡商品';

ALTER TABLE `eb_store_combination` MODIFY column `product_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '商品类型0:普通商品，1：卡密，2：优惠券，3：虚拟商品,4：次卡商品';

ALTER TABLE `eb_store_integral` MODIFY column `product_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '商品类型0:普通商品，1：卡密，2：优惠券，3：虚拟商品,4：次卡商品';

ALTER TABLE `eb_store_discounts_products` MODIFY column `product_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '商品类型0:普通商品，1：卡密，2：优惠券，3：虚拟商品,4：次卡商品';

ALTER TABLE `eb_store_newcomer` MODIFY column `product_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '商品类型0:普通商品，1：卡密，2：优惠券，3：虚拟商品,4：次卡商品';

ALTER TABLE `eb_store_product_attr_value` MODIFY column `product_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '商品类型0:普通商品，1：卡密，2：优惠券，3：虚拟商品,4：次卡商品';

-- 核销相关字段
ALTER TABLE `eb_store_product_attr_value` ADD `write_times` INT(10) NOT NULL DEFAULT '1' COMMENT '核销次数' AFTER `disk_info`,
    ADD `write_valid` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '核销时效：1、永久； 2、购买后几天；3、固定' AFTER `write_times`,
    ADD `write_days` INT(10) NOT NULL DEFAULT '0' COMMENT '购买后：N天有效' AFTER `write_valid`,
    ADD `write_start` INT(10) NOT NULL DEFAULT '0' COMMENT '核销开始时间' AFTER `write_days`,
    ADD `write_end` INT(10) NOT NULL DEFAULT '0' COMMENT '核销结束时间' AFTER `write_start`;

-- 增加sku唯一值
ALTER TABLE `eb_store_order_cart_info` ADD `sku_unique` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'sku唯一值' AFTER `product_type`;

-- 订单商品核销数据
ALTER TABLE `eb_store_order_cart_info` ADD `write_times` INT(10) NOT NULL DEFAULT '0' COMMENT '核销总次数' AFTER `split_status`,
    ADD `write_surplus_times` INT(10) NOT NULL DEFAULT '0' COMMENT '核销剩余次数' AFTER `write_times`,
    ADD `write_start` INT(10) NOT NULL DEFAULT '0' COMMENT '核销开始时间：0不限制' AFTER `write_surplus_times`,
    ADD `write_end` INT(10) NOT NULL DEFAULT '0' COMMENT '核销结束时间：0不限制' AFTER `write_start`;

-- 修改核销剩余次数
UPDATE `eb_store_order_cart_info` set `write_times` = `cart_num` , `write_surplus_times` = `surplus_num`;

-- 增加权限
ALTER TABLE `eb_system_role` ADD `cashier_rules` LONGTEXT NULL DEFAULT NULL COMMENT '门店角色管理收银台权限(menus_id)' AFTER `rules`;

--
-- 转存表中的数据 `eb_other_order` 修改数据表 添加字段
--
ALTER TABLE `eb_other_order` ADD COLUMN  `auth_code` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '收款码条码值';

-- 增加表字段
ALTER TABLE `eb_store_product` ADD `is_police` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '是否警告' AFTER `refusal`;
ALTER TABLE `eb_store_product` ADD `is_sold` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '是否售罄' AFTER `is_police`;

INSERT INTO `eb_system_timer` (`name`, `mark`, `type`, `title`, `is_open`, `cycle`, `last_execution_time`, `update_execution_time`, `is_del`, `add_time`) VALUES
    ('未支付积分订单退积分', 'rebate_points_orders', 1, '', 1, '30', 0, 0, 0, 1682065829),
    ('桌码长期未操作取消桌码记录', 'code_not_operating', 1, '', 1, '5', 0, 0, 0, 1682065829),
    ('拼单长期未操作取消拼单记录', 'collate_not_operating', 1, '', 1, '10', 0, 0, 0, 1682065829);

--
-- 表的结构 `eb_wechat_qrcode`
--

CREATE TABLE IF NOT EXISTS `eb_wechat_qrcode` (
    `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '编号',
    `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
    `name` varchar(255) NOT NULL DEFAULT '' COMMENT '二维码名称',
    `image` varchar(500) NOT NULL DEFAULT '' COMMENT '二维码图片',
    `cate_id` int(11) NOT NULL DEFAULT '0' COMMENT '分类id',
    `label_id` varchar(32) NOT NULL DEFAULT '' COMMENT '标签id',
    `type` varchar(32) NOT NULL DEFAULT '' COMMENT '回复类型',
    `content` text COMMENT '回复内容',
    `data` text COMMENT '发送数据',
    `follow` int(11) NOT NULL DEFAULT '0' COMMENT '关注人数',
    `scan` int(11) NOT NULL DEFAULT '0' COMMENT '扫码人数',
    `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
    `continue_time` int(11) NOT NULL DEFAULT '0' COMMENT '有效期',
    `end_time` int(11) NOT NULL DEFAULT '0' COMMENT '到期时间',
    `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态',
    `is_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除',
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='二维码表';

-- --------------------------------------------------------

--
-- 表的结构 `eb_wechat_qrcode_cate`
--

CREATE TABLE IF NOT EXISTS `eb_wechat_qrcode_cate` (
    `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键id',
    `cate_name` varchar(255) NOT NULL DEFAULT '' COMMENT '渠道码分类名称',
    `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
    `is_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除',
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='二维码类型表';

--
-- 转存表中的数据 `eb_wechat_qrcode_cate`
--

INSERT INTO `eb_wechat_qrcode_cate` (`id`, `cate_name`, `add_time`, `is_del`) VALUES
(1, '线上推广', 1677489851, 0),
(2, '线下活动', 1677489851, 0),
(3, '产品渠道', 1677489851, 0);

-- --------------------------------------------------------

--
-- 表的结构 `eb_wechat_qrcode_record`
--

CREATE TABLE IF NOT EXISTS `eb_wechat_qrcode_record` (
    `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
    `qid` int(11) NOT NULL DEFAULT '0' COMMENT '渠道码id',
    `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
    `is_follow` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否关注',
    `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '扫码时间',
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='渠道码扫码记录表';

--
-- 表的结构 `eb_user_collage_code`
--
CREATE TABLE IF NOT EXISTS `eb_user_collage_code` (
    `id` int(12) NOT NULL AUTO_INCREMENT,
    `uid` int(12) NOT NULL DEFAULT '0' COMMENT 'UID',
    `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '类别 9=拼单,10=桌码',
    `oid` int(12) NOT NULL DEFAULT '0' COMMENT '订单ID',
    `store_id` int(12) NOT NULL DEFAULT '0' COMMENT '门店id',
    `checkout_method` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '结账方式',
    `qrcode_id` int(12) NOT NULL DEFAULT '0' COMMENT '二维码ID',
    `number_diners` INT(5) NOT NULL DEFAULT '0' COMMENT '用餐人数',
    `serial_number` VARCHAR(32) NULL DEFAULT NULL COMMENT '流水号',
    `address_id` int(10) NOT NULL DEFAULT '0' COMMENT '地址id',
    `shipping_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '配送方式 1=快递,2=到店自提,3=门店配送',
    `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 0=下单中 1=结算中 2=提交成功 3=已完成 -1=取消拼单/桌码',
    `add_time` int(12) NOT NULL DEFAULT '0' COMMENT '添加时间',
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户拼单/桌码表';

--
-- 表的结构 `eb_user_collage_code_partake`
--
CREATE TABLE IF NOT EXISTS `eb_user_collage_code_partake` (
    `id` int(12) NOT NULL AUTO_INCREMENT,
    `uid` int(12) NOT NULL DEFAULT '0' COMMENT 'UID',
    `collate_code_id` int(12) NOT NULL DEFAULT '0' COMMENT '拼单ID/桌码ID',
    `product_id` int(10) NOT NULL DEFAULT '0' COMMENT '商品ID',
    `product_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '商品类型',
    `store_id` int(10) NOT NULL DEFAULT '0' COMMENT '门店id',
    `product_attr_unique` varchar(20) NOT NULL COMMENT '商品属性',
    `cart_num` smallint(5) NOT NULL DEFAULT '0' COMMENT '商品数量',
    `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态',
    `is_print` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否打印',
    `is_settle` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '是否结算',
    `add_time` int(12) NOT NULL DEFAULT '0' COMMENT '添加时间',
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='参与拼单/桌码表';

--
-- 表的结构 `eb_table_qrcode`
--
CREATE TABLE IF NOT EXISTS `eb_table_qrcode` (
    `id` int(12) NOT NULL AUTO_INCREMENT,
    `store_id` int(12) NOT NULL DEFAULT '0' COMMENT '门店ID',
    `cate_id` int(5) NOT NULL DEFAULT '0' COMMENT '分类ID',
    `seat_num` int(5) NOT NULL DEFAULT '0' COMMENT '座位数',
    `qrcode` varchar(256) NOT NULL COMMENT '二维码',
    `table_number` int(10) NOT NULL DEFAULT '0' COMMENT '桌号',
    `remarks` varchar(256) NOT NULL COMMENT '备注',
    `is_using` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否启用',
    `is_use` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否使用',
    `eat_number` int(5) NOT NULL DEFAULT '0' COMMENT '就餐人数',
    `order_time` int(12) NOT NULL DEFAULT '0' COMMENT '下单时间',
    `is_del` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '是否删除',
    `add_time` int(12) NOT NULL COMMENT '添加时间',
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='桌码二维码';

--
-- 表的结构 `eb_table_seats`
--
CREATE TABLE IF NOT EXISTS `eb_table_seats` (
    `id` int(10) NOT NULL AUTO_INCREMENT,
    `store_id` int(12) NOT NULL COMMENT '门店id',
    `number` int(5) NOT NULL COMMENT '座位数',
    `add_time` int(12) NOT NULL DEFAULT '0' COMMENT '添加时间',
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='座位数';

-- 删除原来这张表
drop TABLE IF  EXISTS `eb_store_seckill_time`;

-- 秒杀时间段
CREATE TABLE IF NOT EXISTS `eb_store_seckill_time`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
  `pic` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '图片',
  `describe` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '描述',
  `start_time` varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '开始时间',
  `end_time` varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '结束时间',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '1，0状态',
  `add_time` int(10) NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET=utf8mb4 COMMENT = '秒杀时间段配置';

-- 活动
CREATE TABLE IF NOT EXISTS `eb_store_activity`  (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1:秒杀',
  `name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '活动名称',
  `image` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '氛围图片',
  `start_day` int(10) NOT NULL DEFAULT 0 COMMENT '开始日期',
  `end_day` int(10) NOT NULL DEFAULT 0 COMMENT '结束日期',
  `start_time` int(4) UNSIGNED NULL DEFAULT NULL COMMENT '开始时间',
  `end_time` int(4) UNSIGNED NULL DEFAULT NULL COMMENT '结束时间',
  `time_id` LONGTEXT NULL DEFAULT NULL COMMENT '时间段ID多个',
  `once_num` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '活动期间每人每日购买数量，0不限制',
  `num` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '全部活动期间，用户购买总数限制，0不限制	',
  `discount` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '优惠方式',
  `status` tinyint(1) UNSIGNED NULL DEFAULT 0 COMMENT '是否显示',
  `is_recommend` tinyint(1) UNSIGNED NULL DEFAULT 0 COMMENT '是否推荐',
  `link_id` int(4) UNSIGNED NULL DEFAULT 0 COMMENT '关联ID',
  `applicable_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '适用门店：0：仅平台1：所有2：部分',
  `applicable_store_id` LONGTEXT NULL DEFAULT NULL COMMENT '适用门店ids',
  `is_del` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否删除',
  `add_time` int(10) NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `start_day`(`start_day`, `end_day`) USING BTREE,
  INDEX `start_time`(`start_time`, `end_time`) USING BTREE,
  INDEX `type`(`type`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '活动表';

-- 活动关联表
CREATE TABLE IF NOT EXISTS `eb_store_activity_relation`  (
 `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
 `activity_id` int(10) UNSIGNED NOT NULL,
 `product_id` int(10) UNSIGNED NOT NULL,
 PRIMARY KEY (`id`) USING BTREE,
 INDEX `type`(`activity_id`, `product_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '活动关联表中间表(多对多)';


CREATE TABLE IF NOT EXISTS `eb_store_order_writeoff` (
    `id` INT(10) NOT NULL AUTO_INCREMENT ,
    `uid` INT(10) NOT NULL DEFAULT '0' COMMENT '用户UID' ,
    `oid` INT(10) NOT NULL DEFAULT '0' COMMENT '订单ID' ,
    `order_cart_id` INT(10) NOT NULL DEFAULT '0' COMMENT '订单商品ID' ,
    `type` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '商品所属：0：平台1:门店2:供应商' ,
    `relation_id` INT(10) NOT NULL DEFAULT '0' COMMENT '关联门店、供应商ID' ,
    `staff_id` INT(10) NOT NULL DEFAULT '0' COMMENT '店员ID' ,
    `product_id` INT(10) NOT NULL DEFAULT '0' COMMENT '商品ID' ,
    `product_type` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '商品类型0:普通商品，1：卡密，2：优惠券，3：虚拟商品,4：次卡商品' ,
    `writeoff_num` INT(10) NOT NULL DEFAULT '1' COMMENT '核销数量' ,
    `writeoff_price` DECIMAL(10,2) NOT NULL DEFAULT '0.00' COMMENT '核销金额' ,
    `writeoff_code` VARCHAR(30) NOT NULL DEFAULT '' COMMENT '核销码' ,
    `add_time` INT(10) NOT NULL DEFAULT '0' COMMENT '核销时间' ,
    PRIMARY KEY (`id`) USING BTREE
) CHARSET=utf8mb4 COLLATE utf8mb4_general_ci COMMENT = '订单核销记录';


CREATE TABLE IF NOT EXISTS `eb_store_product_category_brand` (
    `id` INT(10) NOT NULL AUTO_INCREMENT ,
    `product_id` INT(10) NOT NULL DEFAULT '0' COMMENT '商品ID' ,
    `cate_id` INT(10) NOT NULL DEFAULT '0' COMMENT '分类ID' ,
    `brand_id` INT(10) NOT NULL DEFAULT '0' COMMENT '品牌ID' ,
    `brand_name` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '品牌名称',
    `status` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '状态' ,
    `add_time` INT(10) NOT NULL DEFAULT '0' COMMENT '添加时间' ,
     PRIMARY KEY (`id`) USING BTREE,
    INDEX `cate_id`(`cate_id`) USING BTREE
) CHARSET=utf8mb4 COLLATE utf8mb4_general_ci COMMENT = '商品分类品牌关联';



CREATE TABLE IF NOT EXISTS `eb_system_form` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `version` varchar(255) NOT NULL DEFAULT '' COMMENT '版本号',
    `name` varchar(255) NOT NULL DEFAULT '' COMMENT '表单名称',
    `cover_image` varchar(255) NOT NULL DEFAULT '' COMMENT '封面图',
    `value` longtext CHARACTER SET utf8mb4 NULL DEFAULT NULL COMMENT '表单数据',
    `default_value` longtext CHARACTER SET utf8mb4 NULL DEFAULT NULL COMMENT '默认数据',
    `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否使用',
    `is_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除',
    `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
    `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='系统表单数据表';



CREATE TABLE IF NOT EXISTS `eb_system_form_data` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `uid` INT(10) NOT NULL DEFAULT '0' COMMENT '用户UID' ,
    `system_form_id` varchar(255) NOT NULL DEFAULT '' COMMENT '表单名称',
    `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '收集数据来源：1订单',
    `relation_id` int(10) NOT NULL DEFAULT '0' COMMENT '关联数据来源ID',
    `value` longtext CHARACTER SET utf8mb4 NULL DEFAULT NULL COMMENT '收集数据',
    `is_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除',
    `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='表单收集数据记录表';


