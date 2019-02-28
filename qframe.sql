/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50553
Source Host           : localhost:3306
Source Database       : qframe

Target Server Type    : MYSQL
Target Server Version : 50553
File Encoding         : 65001

Date: 2019-02-25 15:09:11
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for admin
-- ----------------------------
DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin` (
  `id` int(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `account` varchar(255) DEFAULT NULL COMMENT '账号',
  `password` varchar(255) DEFAULT NULL COMMENT '密码',
  `name` varchar(64) DEFAULT NULL COMMENT '名称',
  `isdisable` tinyint(1) unsigned DEFAULT '2' COMMENT '是否禁用',
  `createTime` datetime DEFAULT NULL COMMENT '创建日期',
  PRIMARY KEY (`id`),
  UNIQUE KEY `accountUnique` (`account`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='管理员';

-- ----------------------------
-- Records of admin
-- ----------------------------
INSERT INTO `admin` VALUES ('1', 'admin', 'e10adc3949ba59abbe56e057f20f883e', 'Shadow', '2', '2019-02-13 20:00:58');
INSERT INTO `admin` VALUES ('2', '客服', '96e79218965eb72c92a549dd5a330112', '客服', '2', '2019-02-20 19:34:52');
INSERT INTO `admin` VALUES ('3', '财务', '96e79218965eb72c92a549dd5a330112', '财务', '1', '2019-02-20 19:35:06');

-- ----------------------------
-- Table structure for adminloginlog
-- ----------------------------
DROP TABLE IF EXISTS `adminloginlog`;
CREATE TABLE `adminloginlog` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `user` varchar(255) DEFAULT NULL COMMENT '登录用户',
  `ip` varchar(255) DEFAULT NULL COMMENT '登录ip',
  `port` varchar(255) DEFAULT NULL COMMENT '端口',
  `browser` varchar(255) DEFAULT NULL COMMENT '浏览器',
  `note` varchar(255) DEFAULT NULL COMMENT '注释',
  `status` tinyint(1) unsigned DEFAULT '0' COMMENT '是否成功登录  0-未成功 1-成功登录',
  `createTime` datetime DEFAULT NULL COMMENT '登录时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8 COMMENT='后台登录记录';

-- ----------------------------
-- Records of adminloginlog
-- ----------------------------
INSERT INTO `adminloginlog` VALUES ('1', 'admin', '127.0.0.1', '80', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36', '登录成功！', '1', '2019-02-13 15:59:41');
INSERT INTO `adminloginlog` VALUES ('2', 'admin', '127.0.0.1', '80', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36', '登录成功！', '1', '2019-02-13 16:01:32');
INSERT INTO `adminloginlog` VALUES ('3', 'admin', '127.0.0.1', '80', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36', '登录成功！', '1', '2019-02-13 16:14:29');
INSERT INTO `adminloginlog` VALUES ('4', 'admin', '127.0.0.1', '80', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36', '登录成功！', '1', '2019-02-13 16:44:44');
INSERT INTO `adminloginlog` VALUES ('5', 'admin', '127.0.0.1', '80', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36', '登录成功！', '1', '2019-02-13 17:09:03');
INSERT INTO `adminloginlog` VALUES ('6', 'admin', '127.0.0.1', '80', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36', '登录成功！', '1', '2019-02-14 08:48:17');
INSERT INTO `adminloginlog` VALUES ('7', 'admin', '127.0.0.1', '80', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36', '登录成功！', '1', '2019-02-14 09:43:14');
INSERT INTO `adminloginlog` VALUES ('8', 'admin', '127.0.0.1', '80', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36', '登录成功！', '1', '2019-02-14 10:16:59');
INSERT INTO `adminloginlog` VALUES ('9', 'admin', '127.0.0.1', '80', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36', '登录成功！', '1', '2019-02-14 10:17:56');
INSERT INTO `adminloginlog` VALUES ('10', 'admin', '127.0.0.1', '80', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36', '登录成功！', '1', '2019-02-14 10:18:50');
INSERT INTO `adminloginlog` VALUES ('11', 'admin', '127.0.0.1', '80', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36', '登录成功！', '1', '2019-02-14 10:19:46');
INSERT INTO `adminloginlog` VALUES ('12', 'admin', '127.0.0.1', '80', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36', '登录成功！', '1', '2019-02-14 10:22:07');
INSERT INTO `adminloginlog` VALUES ('13', 'admin', '127.0.0.1', '80', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36', '登录成功！', '1', '2019-02-14 10:30:42');
INSERT INTO `adminloginlog` VALUES ('14', 'admin', '127.0.0.1', '80', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36', '登录成功！', '1', '2019-02-14 10:40:09');
INSERT INTO `adminloginlog` VALUES ('15', 'admin', '127.0.0.1', '80', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36', '登录成功！', '1', '2019-02-14 10:40:22');
INSERT INTO `adminloginlog` VALUES ('16', 'admin', '127.0.0.1', '80', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36', '登录成功！', '1', '2019-02-15 09:00:31');
INSERT INTO `adminloginlog` VALUES ('17', 'admin', '127.0.0.1', '80', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36', '登录成功！', '1', '2019-02-18 08:50:26');
INSERT INTO `adminloginlog` VALUES ('18', 'admin', '127.0.0.1', '80', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36', '登录成功！', '1', '2019-02-19 08:50:02');
INSERT INTO `adminloginlog` VALUES ('19', 'admin', '127.0.0.1', '80', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36', '登录成功！', '1', '2019-02-20 08:43:39');
INSERT INTO `adminloginlog` VALUES ('20', '财务', '127.0.0.1', '80', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36', '登录成功！', '1', '2019-02-20 19:35:45');
INSERT INTO `adminloginlog` VALUES ('21', 'admin', '127.0.0.1', '80', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36', '登录成功！', '1', '2019-02-20 19:36:11');
INSERT INTO `adminloginlog` VALUES ('22', 'admin', '127.0.0.1', '80', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36', '登录成功！', '1', '2019-02-21 08:53:19');
INSERT INTO `adminloginlog` VALUES ('23', '客服', '127.0.0.1', '80', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36', '登录成功！', '1', '2019-02-21 09:02:00');
INSERT INTO `adminloginlog` VALUES ('24', 'admiin', '127.0.0.1', '80', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36', '账号不存在！', '0', '2019-02-21 09:02:43');
INSERT INTO `adminloginlog` VALUES ('25', 'admin', '127.0.0.1', '80', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36', '登录成功！', '1', '2019-02-21 09:02:55');
INSERT INTO `adminloginlog` VALUES ('26', '财务', '127.0.0.1', '80', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36', '该账号已被禁用！', '0', '2019-02-21 09:40:14');
INSERT INTO `adminloginlog` VALUES ('27', 'admin', '127.0.0.1', '80', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36', '登录成功！', '1', '2019-02-21 09:40:25');
INSERT INTO `adminloginlog` VALUES ('28', 'admin', '127.0.0.1', '80', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36', '登录成功！', '1', '2019-02-22 08:41:02');
INSERT INTO `adminloginlog` VALUES ('29', 'admin', '127.0.0.1', '80', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36', '登录成功！', '1', '2019-02-22 10:11:38');
INSERT INTO `adminloginlog` VALUES ('30', 'admin', '127.0.0.1', '80', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36', '登录成功！', '1', '2019-02-22 10:12:14');
INSERT INTO `adminloginlog` VALUES ('31', 'admin', '127.0.0.1', '80', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36', '登录成功！', '1', '2019-02-25 08:40:01');

-- ----------------------------
-- Table structure for authgroup
-- ----------------------------
DROP TABLE IF EXISTS `authgroup`;
CREATE TABLE `authgroup` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `rules` varchar(2048) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COMMENT='用户组';

-- ----------------------------
-- Records of authgroup
-- ----------------------------
INSERT INTO `authgroup` VALUES ('1', '管理员', '1', '1,2,19,18,17,16,5,3,22,21,20,4,25,24,23');
INSERT INTO `authgroup` VALUES ('5', '行政', '1', '6,10,13');
INSERT INTO `authgroup` VALUES ('6', '客服', '1', '1,3,6,10');
INSERT INTO `authgroup` VALUES ('8', '财务', '1', '1,4,6,13');

-- ----------------------------
-- Table structure for authgroupaccess
-- ----------------------------
DROP TABLE IF EXISTS `authgroupaccess`;
CREATE TABLE `authgroupaccess` (
  `uid` int(20) unsigned NOT NULL,
  `group_id` int(20) unsigned NOT NULL,
  UNIQUE KEY `uid_group_id` (`uid`,`group_id`),
  UNIQUE KEY `uid_unique` (`uid`) USING BTREE,
  KEY `uid` (`uid`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户-用户组对应关系';

-- ----------------------------
-- Records of authgroupaccess
-- ----------------------------
INSERT INTO `authgroupaccess` VALUES ('1', '1');
INSERT INTO `authgroupaccess` VALUES ('2', '6');
INSERT INTO `authgroupaccess` VALUES ('3', '8');

-- ----------------------------
-- Table structure for authrule
-- ----------------------------
DROP TABLE IF EXISTS `authrule`;
CREATE TABLE `authrule` (
  `id` mediumint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '父级id',
  `name` varchar(80) DEFAULT NULL COMMENT '节点',
  `title` char(20) NOT NULL DEFAULT '',
  `type` tinyint(1) NOT NULL DEFAULT '1',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `pid` int(20) DEFAULT NULL,
  `condition` char(100) NOT NULL DEFAULT '',
  `faicon` varchar(255) DEFAULT '' COMMENT '图标',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8 COMMENT='用户权限规则';

-- ----------------------------
-- Records of authrule
-- ----------------------------
INSERT INTO `authrule` VALUES ('1', null, '权限管理', '1', '1', '0', '', 'glyphicon glyphicon-lock');
INSERT INTO `authrule` VALUES ('2', 'admin/index', '管理员管理', '1', '1', '1', '', 'glyphicon glyphicon-home');
INSERT INTO `authrule` VALUES ('3', 'auth/group', '角色组', '1', '1', '1', '', 'fa fa-group');
INSERT INTO `authrule` VALUES ('4', 'auth/rule', '权限规则', '1', '1', '1', '', 'fa fa-bars');
INSERT INTO `authrule` VALUES ('5', 'admin/add', '管理员添加', '1', '1', '2', '', 'fa fa-list');
INSERT INTO `authrule` VALUES ('16', 'admin/edit', '编辑', '1', '1', '2', '', 'glyphicon glyphicon-edit');
INSERT INTO `authrule` VALUES ('17', 'admin/delete', '删除', '1', '1', '2', '', 'glyphicon glyphicon-trash');
INSERT INTO `authrule` VALUES ('18', 'admin/enable', '启用', '1', '1', '2', '', 'glyphicon glyphicon-check');
INSERT INTO `authrule` VALUES ('19', 'admin/disable', '禁用', '1', '1', '2', '', 'glyphicon glyphicon-remove');
INSERT INTO `authrule` VALUES ('20', 'auth/add', '添加', '1', '1', '3', '', 'glyphicon glyphicon-plus');
INSERT INTO `authrule` VALUES ('21', 'auth/edit', '编辑', '1', '1', '3', '', 'glyphicon glyphicon-edit');
INSERT INTO `authrule` VALUES ('22', 'auth/del', '删除', '1', '1', '3', '', 'glyphicon glyphicon-trash');
INSERT INTO `authrule` VALUES ('23', 'auth/addrule', '添加', '1', '1', '4', '', 'glyphicon glyphicon-plus');
INSERT INTO `authrule` VALUES ('24', 'auth/editrule', '编辑', '1', '1', '4', '', 'glyphicon glyphicon-edit');
INSERT INTO `authrule` VALUES ('25', 'auth/delrule', '删除', '1', '1', '4', '', 'glyphicon glyphicon-trash');
INSERT INTO `authrule` VALUES ('26', null, '会员管理', '1', '1', '0', '', 'glyphicon glyphicon-user');
INSERT INTO `authrule` VALUES ('31', null, '系统设置', '1', '1', '0', '', 'glyphicon glyphicon-cog');
INSERT INTO `authrule` VALUES ('32', 'member/index', '会员列表', '1', '1', '26', '', 'glyphicon glyphicon-menu-hamburger');
INSERT INTO `authrule` VALUES ('33', 'setting/index', '系统配置列表', '1', '1', '31', '', 'glyphicon glyphicon-align-justify');

-- ----------------------------
-- Table structure for dropdown
-- ----------------------------
DROP TABLE IF EXISTS `dropdown`;
CREATE TABLE `dropdown` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `module` varchar(30) DEFAULT NULL COMMENT '模块',
  `name` varchar(50) DEFAULT NULL COMMENT '名称',
  `code` int(11) DEFAULT NULL COMMENT '值',
  `val` varchar(50) DEFAULT NULL COMMENT '名称',
  `sort` int(11) DEFAULT NULL COMMENT '排序',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COMMENT='下拉框';

-- ----------------------------
-- Records of dropdown
-- ----------------------------
INSERT INTO `dropdown` VALUES ('1', 'isdisable', '是否禁用', '1', '禁用', '2');
INSERT INTO `dropdown` VALUES ('2', 'isdisable', '是否禁用', '2', '启用', '1');
INSERT INTO `dropdown` VALUES ('3', 'isDelete', '是否删除', '1', '已删除', '1');
INSERT INTO `dropdown` VALUES ('4', 'isDelete', '是否删除', '2', '未删除', '2');
INSERT INTO `dropdown` VALUES ('5', 'pageSize', '每页条数', '10', '10', '1');
INSERT INTO `dropdown` VALUES ('6', 'pageSize', '每页条数', '20', '20', '2');
INSERT INTO `dropdown` VALUES ('7', 'pageSize', '每页条数', '50', '50', '3');
INSERT INTO `dropdown` VALUES ('8', 'pageSize', '每页条数', '100', '100', '4');
INSERT INTO `dropdown` VALUES ('9', 'pageSize', '每页条数', '200', '200', '5');
INSERT INTO `dropdown` VALUES ('10', 'pageSize', '每页条数', '500', '500', '6');
INSERT INTO `dropdown` VALUES ('11', 'pageSize', '每页条数', '1000', '1000', '1000');
INSERT INTO `dropdown` VALUES ('12', 'pageSize', '每页条数', '2000', '2000', '2000');
INSERT INTO `dropdown` VALUES ('13', 'pageSize', '每页条数', '5000', '5000', '5000');

-- ----------------------------
-- Table structure for member
-- ----------------------------
DROP TABLE IF EXISTS `member`;
CREATE TABLE `member` (
  `id` int(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `nickName` varchar(128) DEFAULT '' COMMENT '昵称',
  `account` varchar(11) DEFAULT NULL COMMENT '账号',
  `password` varchar(64) DEFAULT NULL COMMENT '登录密码',
  `parentId` int(20) unsigned DEFAULT NULL COMMENT '推荐人',
  `balance` decimal(20,2) unsigned DEFAULT '0.00' COMMENT '余额',
  `isdisable` int(11) DEFAULT '2' COMMENT '是否禁用  1：禁用  2：正常',
  `createTime` datetime DEFAULT NULL COMMENT '创建时间',
  `updateTime` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `accountUnique` (`account`) USING BTREE,
  KEY `parentId` (`parentId`) USING BTREE,
  KEY `nickname` (`nickName`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=108722 DEFAULT CHARSET=utf8 COMMENT='用户表';

-- ----------------------------
-- Records of member
-- ----------------------------
INSERT INTO `member` VALUES ('1', '汪洋', '18888888888', '96e79218965eb72c92a549dd5a330112', '0', '43364.14', '2', null, null);
INSERT INTO `member` VALUES ('10046', '魏AYG', '13853130568', '96e79218965eb72c92a549dd5a330112', '10047', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10047', '大胖崔Er', '15863257901', '96e79218965eb72c92a549dd5a330112', '1', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10048', '回头已不在！！！', '13210313433', '96e79218965eb72c92a549dd5a330112', '1', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10050', '许少芳', '13954695254', '16ecbc96dba62d3252609c27f4fda2a3', '1', '102281.16', '2', null, '2018-12-19 19:36:29');
INSERT INTO `member` VALUES ('10051', '小平哥', '15066098123', 'bb6bff0d2e0bcb5b89655d3b7dfe7531', '1', '60.00', '2', null, null);
INSERT INTO `member` VALUES ('10054', '安琪儿', '13563360967', '96e79218965eb72c92a549dd5a330112', '1', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10055', '刘向斌', '15006303395', '96e79218965eb72c92a549dd5a330112', '1', '0.00', '2', null, '2018-12-20 11:31:34');
INSERT INTO `member` VALUES ('10060', '桥通天下13573609054', '13573609054', '96e79218965eb72c92a549dd5a330112', '1', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10061', '指环王', '15666676665', '96e79218965eb72c92a549dd5a330112', '10050', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10064', '☆隋莹★', '15166243437', '6419a7b91d4366703d95803b3d270d09', '10050', '1.49', '2', null, null);
INSERT INTO `member` VALUES ('10065', '小雅', '18766736939', '96e79218965eb72c92a549dd5a330112', '1', '0.00', '2', null, '2018-12-20 11:32:37');
INSERT INTO `member` VALUES ('10067', '若熙', '15345460323', '96e79218965eb72c92a549dd5a330112', '10050', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10068', '利18054646160', '18054646160', '96e79218965eb72c92a549dd5a330112', '10050', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10069', '时间别走-_-||', '13656477448', '96e79218965eb72c92a549dd5a330112', '1', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10070', '空号爱润妍', '18819680317', '898e8e9417e83a94788281f1d18b9b22', '10050', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10072', '李晨曦', '15552761727', '96e79218965eb72c92a549dd5a330112', '1', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10073', '爱润妍全国总代徐海妮', '15288996792', '46f94c8de14fb36680850768ff1b7f2a', '10050', '308.04', '2', null, '2018-12-20 11:49:52');
INSERT INTO `member` VALUES ('10075', '化妝師丹丹', '17862121112', '96e79218965eb72c92a549dd5a330112', '10067', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10076', '蒙芳玲13556207129', '13556207129', '5a4d0d691095811bb2bcb68c1a5f6a41', '10050', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10077', '许绍健~~健康、博爱', '13655465885', 'e0444a1b167e472e49b47b808ad1de98', '10050', '84.57', '2', null, '2018-12-19 19:27:39');
INSERT INTO `member` VALUES ('10078', '彩霞', '13854675988', 'be3413f3f7748497c1ccd7a1bfb0a430', '10050', '352.31', '2', null, null);
INSERT INTO `member` VALUES ('10084', '鲜冬', '15682020652', '96e79218965eb72c92a549dd5a330112', '10050', '487.78', '2', null, null);
INSERT INTO `member` VALUES ('10086', '秋凤', '13925584468', '5caa3501602770d59ef1e7c4f28f6783', '10077', '4.70', '2', null, '2018-12-19 22:02:13');
INSERT INTO `member` VALUES ('10091', '相伴今生', '15031655627', '650c38de070068a2ab82df15460beccb', '10050', '1.49', '2', null, null);
INSERT INTO `member` VALUES ('10092', '爱润妍 猪猪', '17393262336', '6539030f647ae52d6c5def632d3eeb40', '10173', '223.03', '2', null, '2018-12-23 09:23:39');
INSERT INTO `member` VALUES ('10093', '静默听雨', '18054608192', 'aad65fe774ad0c6da03592a52f8891cf', '10051', '317.66', '2', null, '2018-12-21 11:34:47');
INSERT INTO `member` VALUES ('10103', '妮妮', '15063645609', '46f94c8de14fb36680850768ff1b7f2a', '10073', '72.35', '2', null, null);
INSERT INTO `member` VALUES ('10108', '仁和-钢镚', '13780783117', '96e79218965eb72c92a549dd5a330112', '10093', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10109', '健康顾问李迦南', '15137817010', 'ae025dcd036f9ff34d1314aa7b5ee4ee', '10050', '187.79', '2', null, '2018-12-20 13:34:26');
INSERT INTO `member` VALUES ('10117', '回不去的过往', '13930569252', '13af269fd90517c88ad272acb241fdf3', '10077', '102.04', '2', null, null);
INSERT INTO `member` VALUES ('10123', '『桃子美妆』@泰国购', '15275465123', '8f8f572e32fb04dd2b0597c2bf2d6ece', '10051', '8.00', '2', null, null);
INSERT INTO `member` VALUES ('10127', '宁静致远', '13792094770', '96e79218965eb72c92a549dd5a330112', '10067', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10145', '随心i', '13455701800', '7778043713a7132d3616673816bcb334', '10093', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10156', '与子偕老', '18765461028', '96e79218965eb72c92a549dd5a330112', '10050', '25.60', '2', null, '2018-12-28 13:40:57');
INSERT INTO `member` VALUES ('10163', '章爱芳17351259139', '17351259139', '96e79218965eb72c92a549dd5a330112', '10077', '74.92', '2', null, null);
INSERT INTO `member` VALUES ('10164', '大蔓蔓', '15051653665', '0b687c1cc19809d12815de2dd0c6b8c4', '10077', '539.39', '2', null, null);
INSERT INTO `member` VALUES ('10173', '江嫚', '15051636656', '96e79218965eb72c92a549dd5a330112', '10164', '61.95', '2', null, null);
INSERT INTO `member` VALUES ('10186', '蔻蔻', '15124818508', '96e79218965eb72c92a549dd5a330112', '10050', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10193', 'Ting', '18854659219', '208a9c8c8c2fba0a818fa4e59d028f80', '1', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10195', '爱橙子', '13171262606', '96e79218965eb72c92a549dd5a330112', '10290', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10203', '鲜冬', '13056680649', 'eefa2d8666cfd8ab7c0e92a0cb4b75f6', '10084', '103.44', '2', null, null);
INSERT INTO `member` VALUES ('10210', '-   L', '15263833557', 'd76d837af5bed214ec935930254a1761', '10108', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10213', 'Lzy', '13625463877', '96e79218965eb72c92a549dd5a330112', '10123', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10219', 'Fly', '18866678915', '96e79218965eb72c92a549dd5a330112', '10108', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10220', '更改名字', '15066090597', '96e79218965eb72c92a549dd5a330112', '1', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10232', '小辣椒', '13211759328', '96e79218965eb72c92a549dd5a330112', '10050', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10241', '雪雨', '13119747516', '7548ee61a5887788e08e0e2587b1985f', '10163', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10246', '郭明杰15865986011', '15865986011', '177ce7d2fd55cdc6346745e713268a31', '10078', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10257', '过去将来现在完成进行时xushao', '17325421930', '96e79218965eb72c92a549dd5a330112', '10050', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10258', '平安保险刘红', '18654678535', '96e79218965eb72c92a549dd5a330112', '10220', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10264', '乐游～更上一层楼', '18653692070', '96e79218965eb72c92a549dd5a330112', '10123', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10269', '爱润妍艳', '18264033253', '4909ebdad40178ed9e5d6209f9238af3', '10109', '0.52', '2', null, null);
INSERT INTO `member` VALUES ('10284', '李姐', '18832496860', '96e79218965eb72c92a549dd5a330112', '10173', '0.71', '2', null, '2018-12-23 09:23:53');
INSERT INTO `member` VALUES ('10285', '边城偶遇(爱润妍领导人)', '18867220712', '4c9c49f9ddf7296e0eacd1498278d6f2', '10077', '20.56', '2', null, '2018-12-19 21:30:36');
INSERT INTO `member` VALUES ('10287', '最熟悉的陌生人', '18316065110', 'dc483e80a7a0bd9ef71d8cf973673924', '10077', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10290', '王舒', '13191485265', '04f568d049a005d49da1e0f698154daf', '10203', '18.81', '2', null, null);
INSERT INTO `member` VALUES ('10300', '小白', '18114929109', '96e79218965eb72c92a549dd5a330112', '10091', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10301', '风雪归人', '15123831384', '96e79218965eb72c92a549dd5a330112', '10145', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10316', '小俊', '18950075210', '96e79218965eb72c92a549dd5a330112', '10269', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10319', '爱润妍，李艳，！！！！', '13369012736', '4909ebdad40178ed9e5d6209f9238af3', '10269', '0.03', '2', null, null);
INSERT INTO `member` VALUES ('10322', '书瑶', '13794591200', '681fc4afb0a0034ebea5589892a31f57', '10070', '39.89', '2', null, null);
INSERT INTO `member` VALUES ('10323', '董莎莎', '15066054566', '34c7be5e4c7938248cc5fbb6f6d34239', '10078', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10327', '刘宗昌', '18866479995', '3463e6533aff82bf678b6565b4f4a472', '10145', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10328', '腊寒晶玫 13141628601', '13141628601', '9a694d54997b54ad5fb025d05a4efc46', '10285', '0.00', '2', null, '2018-12-23 17:20:59');
INSERT INTO `member` VALUES ('10332', '潇洒哥   ', '13210355522', '96e79218965eb72c92a549dd5a330112', '10327', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10335', '一生快乐', '13769066139', '96e79218965eb72c92a549dd5a330112', '10145', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10336', '新儿爱润妍全国招代理29.8元', '15292880686', '195feef47af34095b2c0dbf424812154', '10050', '2.98', '2', null, null);
INSERT INTO `member` VALUES ('10337', '二娃妈妈', '15062693101', '96e79218965eb72c92a549dd5a330112', '10173', '0.00', '2', null, '2018-12-30 15:46:16');
INSERT INTO `member` VALUES ('10340', '卜', '13589455448', '83947b8fb943b116ce6f785c0d026e40', '10050', '849.23', '2', null, null);
INSERT INTO `member` VALUES ('10341', '王春花', '13699350696', '559107ff6aaacaef1622fb13465bf0dc', '10336', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10351', '李昊芯最美不过初见 ', '13864745526', 'c57562653c783faeb8b6cd917ef258c1', '10078', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10357', '阿梅', '18074368622', '3321454579bb5a6e42d96cfd4e7b7a2b', '10285', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10358', '智慧baby母婴店', '15598499706', '96e79218965eb72c92a549dd5a330112', '10195', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10360', '玉静', '13854361110', 'db80e8eb24fda7b5e690cdc88fd2ec23', '10123', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10363', '光辉岁月', '18195228233', 'f5c3030fad5a7d09d5989d643d4f549d', '10285', '23.70', '2', null, null);
INSERT INTO `member` VALUES ('10377', '黄福银', '15965468543', '41c81da8a34243aa7ea8044c0028562e', '10093', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10379', '海纳百川', '18354633817', '96e79218965eb72c92a549dd5a330112', '10340', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10383', '于兴文', '13562686059', '46f94c8de14fb36680850768ff1b7f2a', '10103', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10386', '爱润妍护肤品销售推广15693262004', '15693262004', 'aec60231d83fe6cf81444bc536596887', '10092', '0.26', '2', null, null);
INSERT INTO `member` VALUES ('10389', '开心每一天', '15250365648', '96e79218965eb72c92a549dd5a330112', '10163', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10393', '万能艾草', '17374368490', '96e79218965eb72c92a549dd5a330112', '10285', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10394', '爱诺', '15318319826', '96e79218965eb72c92a549dd5a330112', '10077', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10397', '华夏乖吖', '13361348260', '96e79218965eb72c92a549dd5a330112', '10065', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10399', '方贤', '13592100795', 'e7a91821e5f3ea1edcaefcf9c1781c3c', '10319', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10400', '大燕', '15097802164', '96e79218965eb72c92a549dd5a330112', '10091', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10403', 'a 云漂亮', '13598763722', 'f167f369ea3ce4b61373c58dfea9c01c', '10319', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10408', '呵呵', '18608438714', 'dd34b9cefa2dc4841ea425143490a557', '10285', '111.49', '2', null, null);
INSERT INTO `member` VALUES ('10409', '阿婧～阿婧', '15006875232', 'a8340b9be54dcf182ef63859bb7e274f', '10340', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10410', '搜乐代理王军18339458528', '18339458528', '96e79218965eb72c92a549dd5a330112', '10203', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10413', '悟', '13673877018', '96e79218965eb72c92a549dd5a330112', '10203', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10414', '国通快递13323875789 15703861650', '18738860569', '96e79218965eb72c92a549dd5a330112', '10203', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10416', '依依', '17342621379', '7b1793fb366c9ebf7a054dec74f6d944', '10285', '1683.03', '2', null, null);
INSERT INTO `member` VALUES ('10417', '晚心', '15007439982', 'c101f27c02a6914840956094afb0e29d', '10416', '62.28', '2', null, null);
INSERT INTO `member` VALUES ('10419', '睿智曙光', '18974360166', '9cbf8a4dcb8e30682b927f352d6559a0', '10417', '50.47', '2', null, '2018-12-23 09:20:05');
INSERT INTO `member` VALUES ('10420', '爱跳舞的女孩', '17779610102', '96e79218965eb72c92a549dd5a330112', '10386', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10423', '随梦而飞', '15974328218', '96e79218965eb72c92a549dd5a330112', '10419', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10429', '小谢生活化保养', '13978603705', 'c1cfb6fd02fa63d3786e78e2784e3735', '10077', '335.73', '2', null, null);
INSERT INTO `member` VALUES ('10430', '王燕的妈', '18537377621', '96e79218965eb72c92a549dd5a330112', '10399', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10431', '燕ξ格格不入', '18111908803', '77de6f745efb9bad907b680b11746a90', '10429', '197.72', '2', null, null);
INSERT INTO `member` VALUES ('10433', '海赛娜', '13574330168', '86fe0d628803d2be1c9b494a4a16172c', '10419', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10436', '玫瑰', '15900692769', '96e79218965eb72c92a549dd5a330112', '10393', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10437', '曾经 以后', '13574307370', 'dd34b9cefa2dc4841ea425143490a557', '10408', '85.85', '2', null, null);
INSERT INTO `member` VALUES ('10438', 'A木子坊₁₅₀₂₀₅₉₉₅₁₆', '15020599516', '96e79218965eb72c92a549dd5a330112', '10078', '40.00', '2', null, null);
INSERT INTO `member` VALUES ('10442', '翠翠', '13307779077', '2b77954530f2d9bd85e22b7c3ebbe991', '10173', '64.99', '2', null, '2019-01-03 10:20:09');
INSERT INTO `member` VALUES ('10443', '一介草夫', '13954692008', '83dc10f05680715ca02961b13024e8d5', '10409', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10445', '有证良民', '13574336169', 'dd34b9cefa2dc4841ea425143490a557', '10437', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10446', '翰林豆', '13984099718', '96e79218965eb72c92a549dd5a330112', '10431', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10450', '凝阳', '13678619651', '96e79218965eb72c92a549dd5a330112', '10078', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10455', '李晓娟中国人寿保险公司', '13562295899', 'f6c65667c1b7f780ea31287b6cd7c03f', '10340', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10456', '百变丽人', '13475263188', '96e79218965eb72c92a549dd5a330112', '10078', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10460', '高高飞扬', '13954692452', '96e79218965eb72c92a549dd5a330112', '10078', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10462', '笔尖触碰白纸的声音', '15913892755', '681fc4afb0a0034ebea5589892a31f57', '10322', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10463', '紫冰', '13325031697', '4ddffbf766f1a14c867b4ffa48593c8f', '10078', '201.49', '2', null, null);
INSERT INTO `member` VALUES ('10464', '陈家二小姐烨', '15626525993', '96e79218965eb72c92a549dd5a330112', '10173', '0.10', '2', null, null);
INSERT INTO `member` VALUES ('10465', '上善若水', '15802125628', '96e79218965eb72c92a549dd5a330112', '10073', '8.00', '2', null, null);
INSERT INTO `member` VALUES ('10467', '一直都在', '13737760366', '96e79218965eb72c92a549dd5a330112', '10442', '2.98', '2', null, null);
INSERT INTO `member` VALUES ('10468', '孟星星', '13645499290', '96e79218965eb72c92a549dd5a330112', '10465', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10472', '白雪', '15169566421', 'b943f9f9659147dce858e063b9fa4871', '10073', '61.21', '2', null, null);
INSERT INTO `member` VALUES ('10478', '風吹む楓葉飄', '18317896079', '96e79218965eb72c92a549dd5a330112', '10399', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10479', 'A姑娘', '13561054933', '96e79218965eb72c92a549dd5a330112', '10438', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10481', '蓝调魅影', '15953656631', 'cb1ee4cb679798217e5500a6c595f347', '10472', '37.34', '2', null, null);
INSERT INTO `member` VALUES ('10483', '舍我其谁', '13371533977', '96e79218965eb72c92a549dd5a330112', '10220', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10486', '青山绿水', '15554691198', '96e79218965eb72c92a549dd5a330112', '10078', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10489', '木子坊*^O^*18866677010', '18866677010', '96e79218965eb72c92a549dd5a330112', '10438', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10493', '燕子爱润妍母婴全国总代', '15086575131', '01ac80877a095137432366117c6c1fc6', '10173', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10494', '有你们我就是幸福的', '15564616226', 'a2e03ab03bddcc7966f87a25fdf92d1f', '10078', '0.55', '2', null, null);
INSERT INTO `member` VALUES ('10500', '铁厂沟振星汽修汽配厂', '15963020165', '4909ebdad40178ed9e5d6209f9238af3', '10319', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10501', '清心的爱:张健老师', '15865450772', '96e79218965eb72c92a549dd5a330112', '10078', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10503', '阿龙', '18856480564', '96e79218965eb72c92a549dd5a330112', '10173', '0.00', '2', null, '2018-12-30 15:49:16');
INSERT INTO `member` VALUES ('10507', '宝贝儿', '13465286960', '96e79218965eb72c92a549dd5a330112', '10501', '20.00', '2', null, null);
INSERT INTO `member` VALUES ('10511', '炜炜一笑很倾城', '18765463024', '96e79218965eb72c92a549dd5a330112', '10507', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10528', '碧玉', '18585600003', 'b1f99dac4b41bd749060e0d55aaf7d1e', '10429', '50.00', '2', null, null);
INSERT INTO `member` VALUES ('10533', '李小林', '18906476644', '96e79218965eb72c92a549dd5a330112', '10156', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10534', '健康就好', '13854616787', '3662ac69bd20b1c77666bf2d73586b20', '10156', '2.98', '2', null, null);
INSERT INTO `member` VALUES ('10538', '王桂全', '18563051070', '96e79218965eb72c92a549dd5a330112', '0', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10542', 'ℳ๓三世、℘', '13589971599', '96e79218965eb72c92a549dd5a330112', '10534', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10543', '乐逍遥', '15006865527', '96e79218965eb72c92a549dd5a330112', '10534', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10545', 'yo～', '13792052995', '3662ac69bd20b1c77666bf2d73586b20', '10534', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10546', '若水沉香', '13561075966', '96e79218965eb72c92a549dd5a330112', '10534', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10548', '明天会更好', '15254682198', '96e79218965eb72c92a549dd5a330112', '10534', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10549', '珍惜当前', '15554699899', '96e79218965eb72c92a549dd5a330112', '10534', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10551', '嗯！', '18854612537', '96e79218965eb72c92a549dd5a330112', '10534', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10552', '小话痨-', '13589994611', '96e79218965eb72c92a549dd5a330112', '10534', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10553', '高', '13678610308', '96e79218965eb72c92a549dd5a330112', '10534', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10554', '一念笙', '15205465329', '96e79218965eb72c92a549dd5a330112', '10534', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10555', '阳光45度', '15266061356', '96e79218965eb72c92a549dd5a330112', '10534', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10556', '一张好网', '15254693344', '96e79218965eb72c92a549dd5a330112', '10534', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10557', '春暖花开', '15263887872', '96e79218965eb72c92a549dd5a330112', '10534', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10558', '兵', '15954678652', '96e79218965eb72c92a549dd5a330112', '10534', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10559', '岁月静好', '13255466771', '96e79218965eb72c92a549dd5a330112', '10534', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10562', '高山流水', '13455729877', '96e79218965eb72c92a549dd5a330112', '10534', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10563', '梦儿', '15966166266', '96e79218965eb72c92a549dd5a330112', '10078', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10566', '雪儿', '18608568036', '6e078bf305e4de23ff7b9eb6cedc2183', '10528', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10573', '爱家', '13963353468', '96e79218965eb72c92a549dd5a330112', '10078', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10577', '爱润妍玻尿酸创业教育', '13937873675', 'fa51a7c63ae02790d3119bec21bd7f56', '10109', '113.51', '2', null, null);
INSERT INTO `member` VALUES ('10580', '山东金昊田秀英', '13884914766', '96e79218965eb72c92a549dd5a330112', '10078', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10581', '秋月', '17615204860', '96e79218965eb72c92a549dd5a330112', '10399', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10585', '方杰', '18537377620', '96e79218965eb72c92a549dd5a330112', '10430', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10586', '平淡', '13605463297', '96e79218965eb72c92a549dd5a330112', '10534', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10589', '大梅', '15097303350', 'a05f4ef49d6ff8554fe1b580dab953bc', '10050', '85.70', '2', null, null);
INSERT INTO `member` VALUES ('10591', '依然快乐', '15807060820', '96e79218965eb72c92a549dd5a330112', '10328', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10592', '晨曦印月', '17605415389', '96e79218965eb72c92a549dd5a330112', '10093', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10593', '吾', '13854635126', '002978bbb06a9249d44f846261163b2c', '10463', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10594', '刘晓娟', '15941535849', '34d20b573ccc0fbaa2c1b19644f64ddf', '10073', '0.10', '2', null, null);
INSERT INTO `member` VALUES ('10598', '在路上', '13930566353', '96e79218965eb72c92a549dd5a330112', '10117', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10603', '琪琪', '15263832503', '96e79218965eb72c92a549dd5a330112', '10323', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10604', '云天', '18766730851', '96e79218965eb72c92a549dd5a330112', '10064', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10608', '海英', '15050226301', '96e79218965eb72c92a549dd5a330112', '10173', '0.00', '2', null, null);
INSERT INTO `member` VALUES ('10609', '夏天', '18763895687', '5f8591a3d4fe06393cad53edff27ba05', '1', '14.70', '2', '2018-12-11 20:46:09', '2019-01-04 15:09:22');
INSERT INTO `member` VALUES ('10610', '用户15064229286', '15064229286', '5f8591a3d4fe06393cad53edff27ba05', '1', '0.00', '2', '2018-12-12 08:40:41', '2018-12-12 08:49:30');
INSERT INTO `member` VALUES ('10616', '1彩', '13395361364', 'd29bcb9481def9450eadac7a3ceb9ff7', '1', '109.00', '2', '2018-12-12 10:31:55', '2018-12-14 13:27:02');
INSERT INTO `member` VALUES ('10617', '用户18753959513', '18753959513', '96e79218965eb72c92a549dd5a330112', '107899', '0.00', '2', '2018-12-12 10:41:36', '2019-01-02 13:06:16');
INSERT INTO `member` VALUES ('10686', '用户17686926906', '17686926906', '45a889166b085f4c77e46134ef01f46f', '1', '0.00', '2', '2018-12-12 17:39:50', null);
INSERT INTO `member` VALUES ('10690', '贾睿', '13426325001', 'bc32ce5f544b326ef815c115e0688804', '107922', '86.61', '2', '2018-12-13 07:57:25', '2018-12-23 09:21:33');
INSERT INTO `member` VALUES ('10746', '用户13869380207', '13869380207', '6bd2b5193d47bab575647b8560edc39c', '1', '0.00', '2', '2018-12-13 14:11:25', '2018-12-24 22:48:04');
INSERT INTO `member` VALUES ('10761', '宏信', '13054785988', '48b2675fe0f423df2983c02326760345', '1', '0.00', '2', '2018-12-13 15:04:18', '2018-12-30 15:14:23');
INSERT INTO `member` VALUES ('10762', '用户15734746008', '15734746008', '11c4ef0420613dfbec219338e22d4dd5', '1', '0.00', '2', '2018-12-13 19:39:29', null);
INSERT INTO `member` VALUES ('10763', '用户15307324918', '15307324918', '722723c3174e03c00d8e1aab58bf9439', '10077', '4.47', '2', '2018-12-13 20:01:32', null);
INSERT INTO `member` VALUES ('107898', '清风', '15588510586', 'd1e608e601297b9494a45a4dda769593', '1', '0.00', '2', '2018-12-14 14:15:45', null);
INSERT INTO `member` VALUES ('107899', '15610330902', '15610330902', '96e79218965eb72c92a549dd5a330112', '1', '147.98', '2', '2018-12-14 15:31:27', null);
INSERT INTO `member` VALUES ('107900', '宏信仁和', '16606381859', '7604780ac6699757fff2b39569d891a7', '1', '63.05', '2', '2018-12-15 17:39:57', '2018-12-17 12:15:06');
INSERT INTO `member` VALUES ('107901', '用户17616408689', '17616408689', '18e8229edb366397da42781d4e96920c', '10077', '2.98', '2', '2018-12-15 21:00:18', null);
INSERT INTO `member` VALUES ('107902', '李委彤', '13831407625', 'f1c910cc8ef22ea00792ec40ffd38d1d', '10077', '266.94', '2', '2018-12-16 18:41:45', null);
INSERT INTO `member` VALUES ('107903', '用户18287122092', '18287122092', 'cb4cdf4039e82219453db5639fc1a792', '1', '0.00', '2', '2018-12-16 20:39:46', null);
INSERT INTO `member` VALUES ('107904', '优宝儿', '15344170026', 'fcf78f82d51aaadf935c7378c9d8d4a6', '107905', '0.00', '2', '2018-12-16 23:28:49', null);
INSERT INTO `member` VALUES ('107905', '小番茄', '18687784545', '2e081d0c50858f843a954dadba8dc73b', '1', '100507.01', '2', '2018-12-17 14:08:33', null);
INSERT INTO `member` VALUES ('107906', '翟歌', '18587395939', '98fe6e7c41f7858cd1e052b72b1af550', '107905', '14576.62', '2', '2018-12-18 09:19:14', null);
INSERT INTO `member` VALUES ('107907', '用户15329368983', '15329368983', '0a3f0a929f6d3b035612b9a86510e646', '10429', '29.40', '2', '2018-12-18 20:52:58', null);
INSERT INTO `member` VALUES ('107908', '伊伊', '15073187692', 'fc3c34e94deb5ddbeda6fc62ee1e673c', '107905', '1622.92', '2', '2018-12-18 22:29:18', null);
INSERT INTO `member` VALUES ('107909', '用户13678619928', '13678619928', '7ca32fddf4921a511bf5e1c07bfa24c3', '1', '0.00', '2', '2018-12-19 06:30:39', null);
INSERT INTO `member` VALUES ('107910', '用户13589975995', '13589975995', 'c016f883ada1b6b0434b2fad6770ff01', '1', '0.00', '2', '2018-12-19 10:47:20', '2018-12-20 11:26:07');
INSERT INTO `member` VALUES ('107911', '用户18765467871', '18765467871', '114beac1e13817479cb0d04eb401c8bc', '1', '0.00', '2', '2018-12-19 11:07:17', '2018-12-20 11:25:39');
INSERT INTO `member` VALUES ('107912', '用户18254886634', '18254886634', '3ed19e5cf2d865844135d5a52f48dbf8', '1', '0.00', '2', '2018-12-19 17:27:57', null);
INSERT INTO `member` VALUES ('107913', '梁静', '13686731958', 'f2028b1ac7a8f9de40b18182e6a96c82', '107905', '773.66', '2', '2018-12-19 17:35:32', null);
INSERT INTO `member` VALUES ('107914', '用户18890901002', '18890901002', 'd4b2d230d8d56c25cd46da717026453b', '1', '0.00', '2', '2018-12-19 17:46:47', null);
INSERT INTO `member` VALUES ('107915', '君姐413', '13658772202', '647e6de38e6e1ebaa7b5254c3e38ae1b', '107905', '210.05', '2', '2018-12-19 17:48:53', null);
INSERT INTO `member` VALUES ('107916', '用户13888676134', '13888676134', '94f1949303b550931a06ea977ec9249c', '107915', '0.00', '2', '2018-12-19 18:50:37', null);
INSERT INTO `member` VALUES ('107917', '蕊蕊', '14787004646', 'cdae4bf840091a71afe80c31d8dec033', '107915', '2.98', '2', '2018-12-19 18:51:38', '2018-12-27 13:54:49');
INSERT INTO `member` VALUES ('107918', '用户18088242408', '18088242408', 'fc50ad3db3a9b6de893d97c7f6d5e5e3', '1', '0.00', '2', '2018-12-19 19:08:47', null);
INSERT INTO `member` VALUES ('107919', '用户13759100885', '13759100885', 'cb4cdf4039e82219453db5639fc1a792', '107905', '4.47', '2', '2018-12-19 19:10:33', null);
INSERT INTO `member` VALUES ('107920', '文文', '18214460595', '04b141a19005be7aea43aff307f94463', '107905', '0.00', '2', '2018-12-19 19:10:47', null);
INSERT INTO `member` VALUES ('107921', '随州总部蒋维维', '13597848565', 'd4743b6ab72f17efad97f445f4bbbe55', '10050', '213.63', '2', '2018-12-19 19:13:07', null);
INSERT INTO `member` VALUES ('107922', '吕新会', '13911163164', '0fafd8ff0e34444df254c5362a0cea3a', '10050', '116332.39', '2', '2018-12-19 19:13:34', null);
INSERT INTO `member` VALUES ('107923', '飞哥', '13383039818', 'b85cad0eb66f5c7c9aacd1fcfa60575a', '107906', '497.58', '2', '2018-12-19 19:14:24', null);
INSERT INTO `member` VALUES ('107924', '王苹', '13657665092', 'd3f11d793e394056281f4fe2c5511cc0', '107906', '0.00', '2', '2018-12-19 19:14:32', null);
INSERT INTO `member` VALUES ('107925', '晓晓', '13648812893', 'bba8b20fe718effadbd4d4a69b649fc6', '107906', '14.90', '2', '2018-12-19 19:14:34', null);
INSERT INTO `member` VALUES ('107926', '樱雪', '15807619889', '60c087a1bd829c46324fe22c4fa72f7b', '107906', '0.00', '2', '2018-12-19 19:14:36', null);
INSERT INTO `member` VALUES ('107927', '新儿的店', '18160226134', '195feef47af34095b2c0dbf424812154', '107906', '227.59', '2', '2018-12-19 19:15:55', '2018-12-22 14:49:40');
INSERT INTO `member` VALUES ('107928', '悠悠紫苏', '13768772905', '277aa3bb11e1a69814ed6d1b6448af77', '107906', '118.60', '2', '2018-12-19 19:16:15', null);
INSERT INTO `member` VALUES ('107929', '代海燕', '15036523330', '6b9020f32097d1a3a1bd259798e016be', '107906', '6.00', '2', '2018-12-19 19:16:38', null);
INSERT INTO `member` VALUES ('107930', '幸运草', '13759266932', '1799e855396f65eab32a57bb1e51c90c', '107906', '116.15', '2', '2018-12-19 19:17:22', '2018-12-30 10:23:47');
INSERT INTO `member` VALUES ('107931', '贵儿', '18288009957', 'b418a471c90b2901fda2ab9fc6cee2bc', '107906', '277.88', '2', '2018-12-19 19:17:26', null);
INSERT INTO `member` VALUES ('107932', '随州官方旗舰店', '13986445842', 'd3879bf98efe15a00ea7aa60ea2c919c', '107906', '83.41', '2', '2018-12-19 19:17:31', null);
INSERT INTO `member` VALUES ('107933', '紫珺', '18635339601', '1c1de17ffc5d4244d5c28cbf9890b78d', '107905', '7.44', '2', '2018-12-19 19:17:47', null);
INSERT INTO `member` VALUES ('107934', '冰雪', '13388631053', '9212b4eb3aa69f3eb0b5fb0c1b34a2b4', '107917', '1.49', '2', '2018-12-19 19:17:48', null);
INSERT INTO `member` VALUES ('107935', '小师妹', '13577141449', 'a52304a2ce4bf7be755da413a52fd656', '107906', '14.78', '2', '2018-12-19 19:18:07', '2018-12-20 19:52:47');
INSERT INTO `member` VALUES ('107936', '小绵羊', '13321679081', '3ee3031035883a0db2395f4e68fc78f8', '107906', '75.12', '2', '2018-12-19 19:18:46', null);
INSERT INTO `member` VALUES ('107937', '张洁', '18076333020', 'e4d122bb16a6b10602aa074d79567f7d', '107906', '424.20', '2', '2018-12-19 19:19:15', '2018-12-20 12:45:57');
INSERT INTO `member` VALUES ('107938', '健康天使苏小玲', '13734275812', '5d6bc31be5b0ea5666c7a98e8959544d', '107906', '9.92', '2', '2018-12-19 19:20:09', null);
INSERT INTO `member` VALUES ('107939', '心如止水', '13343368597', '507f513353702b50c145d5b7d138095c', '10050', '5276.42', '2', '2018-12-19 19:20:10', null);
INSERT INTO `member` VALUES ('107940', '英子', '18568756295', '9c860b45d31bbc0cb4335a14487431f6', '107906', '5.96', '2', '2018-12-19 19:20:52', null);
INSERT INTO `member` VALUES ('107941', '木子', '13922970825', '0e6404bdaff3ddab08ec3f44bf513021', '107913', '36.23', '2', '2018-12-19 19:20:59', null);
INSERT INTO `member` VALUES ('107942', '娟子', '13627937166', 'cb02c65622bbfc208e96f5c47c0ee5ce', '1', '0.00', '2', '2018-12-19 19:21:19', null);
INSERT INTO `member` VALUES ('107943', '王倩', '18270649982', '16b874be7f2a66b971c226d8456e1f2a', '107913', '22.15', '2', '2018-12-19 19:21:51', null);
INSERT INTO `member` VALUES ('107944', '小青', '13267331027', '6817a7d1724ba76894d20c637697d720', '107906', '4.47', '2', '2018-12-19 19:21:52', null);
INSERT INTO `member` VALUES ('107945', '孙丽丽', '15239209630', '626fea4e3a89b147fb127d9d4f5369e1', '107906', '32.08', '2', '2018-12-19 19:21:54', null);
INSERT INTO `member` VALUES ('107946', '小鱼儿', '13556071533', 'f18ca52cc68ecb00dee496bfe2423702', '107906', '8.94', '2', '2018-12-19 19:22:02', null);
INSERT INTO `member` VALUES ('107947', '王晓菊', '15099623069', '38de026fa3d363c7e64b37e9dd04e33e', '107927', '0.00', '2', '2018-12-19 19:22:49', null);
INSERT INTO `member` VALUES ('107948', '蒙蒙', '18307787348', '48e1db4436ce19b0141b0369ae05bf6b', '107913', '23.64', '2', '2018-12-19 19:22:59', null);
INSERT INTO `member` VALUES ('107949', '金玲', '15156586577', 'd49b356c0b2b0d365932c9e5f530a218', '107905', '0.00', '2', '2018-12-19 19:23:30', null);
INSERT INTO `member` VALUES ('107950', '宁缺毋滥', '15915702336', '03148177f7da30d3b7b400af4bd3cf14', '1', '0.00', '2', '2018-12-19 19:23:30', null);
INSERT INTO `member` VALUES ('107951', '霞', '15239725363', 'c8962b49af23bae68350607ab4bb6c2e', '107929', '0.00', '2', '2018-12-19 19:24:40', null);
INSERT INTO `member` VALUES ('107952', '赵敏', '15777113949', 'db3fc34dddcd4fb286f908ac76afd47d', '107906', '0.58', '2', '2018-12-19 19:24:50', null);
INSERT INTO `member` VALUES ('107953', '敏洁', '18878103983', 'de57cd1665ec0682a844178bdfd9ce9b', '107913', '181.25', '2', '2018-12-19 19:25:22', '2019-01-17 15:16:53');
INSERT INTO `member` VALUES ('107954', '冯洪琼', '18806691023', 'dc483e80a7a0bd9ef71d8cf973673924', '107913', '0.00', '2', '2018-12-19 19:25:51', null);
INSERT INTO `member` VALUES ('107955', '李情', '15660931026', '1ba5182d46dce31908afce8fffdd7796', '107915', '33.24', '2', '2018-12-19 19:25:59', null);
INSERT INTO `member` VALUES ('107956', '家娟', '18814937401', '56f61e2905412e0c478e531b1a21265c', '107905', '408.06', '2', '2018-12-19 19:25:59', null);
INSERT INTO `member` VALUES ('107957', '云朵', '18100802118', 'aa01ac7d67659e9a3556154a5d46cbad', '107908', '10.43', '2', '2018-12-19 19:26:19', null);
INSERT INTO `member` VALUES ('107958', '小马哥', '15987080790', 'e59b173004a31b276920eb9167c8a337', '107915', '13.40', '2', '2018-12-19 19:26:21', null);
INSERT INTO `member` VALUES ('107959', '刘红梅', '18747719269', '76ef7fa43da3cee53302b433c44620f4', '107906', '0.00', '2', '2018-12-19 19:26:43', null);
INSERT INTO `member` VALUES ('107960', '刘艳丽', '13647828976', 'dc483e80a7a0bd9ef71d8cf973673924', '107913', '579.77', '2', '2018-12-19 19:26:46', null);
INSERT INTO `member` VALUES ('107961', '姜丽丽', '13704559635', '4011fd927adc39bcc9bc09db0ffdb0e6', '107913', '36.30', '2', '2018-12-19 19:27:16', null);
INSERT INTO `member` VALUES ('107962', '欧月群', '15367577108', '927d049325b7a80d1d2653bcbff4ee1c', '107905', '0.00', '2', '2018-12-19 19:27:18', null);
INSERT INTO `member` VALUES ('107963', '莹儿', '18825863418', '58a18eeae119074bd8bb88637597e49f', '107906', '42.86', '2', '2018-12-19 19:27:30', null);
INSERT INTO `member` VALUES ('107964', '玲玲', '13822719505', '3d24b838770ee90773804e8599e549ff', '107908', '4.90', '2', '2018-12-19 19:27:44', null);
INSERT INTO `member` VALUES ('107965', '叶子', '17769392616', '2111a552272b8445b4e5d8a02ea9522b', '1', '0.00', '2', '2018-12-19 19:27:51', null);
INSERT INTO `member` VALUES ('107966', '沐家芬', '15974952390', '87b750fdfeb4468f58c3247b303704ab', '107930', '38.83', '2', '2018-12-19 19:27:58', null);
INSERT INTO `member` VALUES ('107967', '毛艳', '18772331336', '9ca4d77e7b6f9890190f807fa11fc755', '107906', '175.43', '2', '2018-12-19 19:28:39', null);
INSERT INTO `member` VALUES ('107968', '心雨', '15298489382', '2e2e19dcdb5f1684f3b2ddb43a3580e2', '107905', '408.09', '2', '2018-12-19 19:29:01', null);
INSERT INTO `member` VALUES ('107969', '关菊琳', '15739078615', 'c90d7aea83e4aac8608061d9ca4bf9c2', '107927', '0.00', '2', '2018-12-19 19:29:09', null);
INSERT INTO `member` VALUES ('107970', '巫利娜', '13750541356', '8f3bc06c6e35e2808e72fda99c93d55c', '107941', '22.49', '2', '2018-12-19 19:29:25', null);
INSERT INTO `member` VALUES ('107971', '车树萍', '18725230950', 'a05d7b639d4324af0329f67cd7619ee6', '107908', '0.00', '2', '2018-12-19 19:29:44', null);
INSERT INTO `member` VALUES ('107972', '兰兰', '13768222230', '030bd9318f136ee3b1e4c2afaff911c8', '107913', '0.00', '2', '2018-12-19 19:29:47', null);
INSERT INTO `member` VALUES ('107973', '伽伽', '13888810357', 'a2696571baddf822549b62c0c2309b3c', '107906', '71.84', '2', '2018-12-19 19:30:08', null);
INSERT INTO `member` VALUES ('107974', '糖糖妈', '15607710216', '705e728ab8fb31547450f183b5be0645', '107913', '1.49', '2', '2018-12-19 19:30:30', null);
INSERT INTO `member` VALUES ('107975', '张伟', '13507677771', 'dc483e80a7a0bd9ef71d8cf973673924', '107929', '14.90', '2', '2018-12-19 19:30:31', null);
INSERT INTO `member` VALUES ('107976', '高邂逅', '13321136813', 'd2d4ffcd227541d77a529c55b8086c91', '107939', '0.00', '2', '2018-12-19 19:30:32', null);
INSERT INTO `member` VALUES ('107977', '韦秋燕', '13679791625', 'af8f9dffa5d420fbc249141645b962ee', '107941', '18.15', '2', '2018-12-19 19:30:32', null);
INSERT INTO `member` VALUES ('107978', '符小连', '18276069029', 'df2263e7ce8c5a4f2b4f3b06d4af92b1', '107960', '5.95', '2', '2018-12-19 19:31:08', null);
INSERT INTO `member` VALUES ('107979', '司月华', '13738722850', '7f424deea69c9f7addb2aaba3ed0af15', '107906', '71.70', '2', '2018-12-19 19:31:13', null);
INSERT INTO `member` VALUES ('107980', '一雁', '13877765047', 'c434e0e0aa46e7ed4e04818f17473517', '107941', '51.08', '2', '2018-12-19 19:31:35', null);
INSERT INTO `member` VALUES ('107981', '谢华', '17773550265', '573062bdab4d84046c9c78c0a0ba4eb6', '10077', '51.08', '2', '2018-12-19 19:31:39', '2018-12-20 09:53:57');
INSERT INTO `member` VALUES ('107982', '张飘歌', '15237460206', 'e2522328e4420f5a6eb9deeaa6eeff48', '107960', '36.39', '2', '2018-12-19 19:31:41', null);
INSERT INTO `member` VALUES ('107983', '谭雪花', '13769141619', 'b087fddecb517597ba977bb8e6c8bd32', '107906', '113.45', '2', '2018-12-19 19:31:54', null);
INSERT INTO `member` VALUES ('107984', '黎小庆', '15987968008', '0691d71ba652dd02dabf8e40e6347898', '107906', '0.71', '2', '2018-12-19 19:32:06', null);
INSERT INTO `member` VALUES ('107985', '李林艳', '13204270960', 'f12be55bee925758dba79b6294d06640', '107905', '515.90', '2', '2018-12-19 19:32:16', null);
INSERT INTO `member` VALUES ('107986', '韩韩', '18093423910', 'b43ce67c7299636a7983eb04440da0af', '107906', '12.20', '2', '2018-12-19 19:32:17', '2018-12-26 10:48:31');
INSERT INTO `member` VALUES ('107987', '王童', '17661228501', '3b03ebae12da6a8d4c47ddcca17f9580', '10077', '0.40', '2', '2018-12-19 19:32:43', null);
INSERT INTO `member` VALUES ('107988', '王丹', '13922912523', '6191284f9e3a0ed6c1a013fa36301d15', '107955', '35.85', '2', '2018-12-19 19:33:05', null);
INSERT INTO `member` VALUES ('107989', '陈小燕', '13033857345', 'a6dbdecc5fd1c6aafd7fbb246680e04b', '107932', '0.00', '2', '2018-12-19 19:33:13', null);
INSERT INTO `member` VALUES ('107990', '彭姐', '13881735708', '515970b6c87645f59f3f5def589af2ce', '10050', '311.59', '2', '2018-12-19 19:33:31', null);
INSERT INTO `member` VALUES ('107991', '小逗逗', '13073097250', 'bee57f095a07ca96a12e76b237562e22', '107908', '51.99', '2', '2018-12-19 19:33:42', null);
INSERT INTO `member` VALUES ('107992', '邹信高', '18613173992', 'ec8ab40c94bc653ce83f73a47c7c90f2', '10077', '2.98', '2', '2018-12-19 19:33:44', null);
INSERT INTO `member` VALUES ('107993', '梁小玲', '13699872569', '1dc486bc2b4f03fc16819abad52c2b2f', '107913', '17.68', '2', '2018-12-19 19:33:50', null);
INSERT INTO `member` VALUES ('107994', '秀平', '13688417732', '22e76647b098891117c1af461cce5fbb', '107906', '40.32', '2', '2018-12-19 19:33:51', null);
INSERT INTO `member` VALUES ('107995', '叶慧瑜', '18038322852', '3088f5f165e840320b23dbadcc7d1e2c', '107944', '0.00', '2', '2018-12-19 19:34:21', null);
INSERT INTO `member` VALUES ('107996', '小情绪', '18878133077', '68ef139fba448c6b5b6628e0300717dd', '107936', '0.00', '2', '2018-12-19 19:34:50', null);
INSERT INTO `member` VALUES ('107997', '杨磊', '13966340799', '6dbb8ed1e2799ee931047d67d17b68e0', '107960', '18.60', '2', '2018-12-19 19:35:03', null);
INSERT INTO `member` VALUES ('107998', '杨杨', '15833195479', 'ae88ae2b9cc79519fe3901b4fa0e7786', '107927', '0.00', '2', '2018-12-19 19:35:36', null);
INSERT INTO `member` VALUES ('107999', '苏会霞', '18236786009', '666129c99e83741a0b20b09351aafa95', '107906', '0.00', '2', '2018-12-19 19:35:38', null);
INSERT INTO `member` VALUES ('108000', '何璐云', '15778436877', '08ef8aacfb2694be3f0248164e4818bd', '107953', '31.08', '2', '2018-12-19 19:35:39', null);
INSERT INTO `member` VALUES ('108001', '用户', '13707997898', 'c0644186be926cacfca99b8c99c377c9', '107905', '1.49', '2', '2018-12-19 19:35:42', null);
INSERT INTO `member` VALUES ('108002', '夏末', '18208793552', 'a46c568569ad99e21a31e95d16ba2a49', '107906', '38.83', '2', '2018-12-19 19:36:00', null);
INSERT INTO `member` VALUES ('108003', '王小贤', '15559655744', '4b29d0fcdb953e9f72b5c150af74e9f0', '1', '0.00', '2', '2018-12-19 19:36:06', null);
INSERT INTO `member` VALUES ('108004', '夏海燕', '15180359647', 'd979d2221989912f57f7d19bd3ee3388', '107956', '1.49', '2', '2018-12-19 19:36:06', null);
INSERT INTO `member` VALUES ('108005', '姜艳玲', '15008813959', '0d7ecc4634e0d9d6c903fc0f6f473992', '107984', '36.15', '2', '2018-12-19 19:36:24', null);
INSERT INTO `member` VALUES ('108006', '道心', '15208460990', 'b10bb05ea3fdfaa2890f6eb4c531f3e2', '107908', '81.43', '2', '2018-12-19 19:36:44', null);
INSERT INTO `member` VALUES ('108007', '杨志清', '13231628276', 'dc483e80a7a0bd9ef71d8cf973673924', '107939', '0.00', '2', '2018-12-19 19:36:44', null);
INSERT INTO `member` VALUES ('108008', '伍雪梅', '15777768571', '94fdba8a338cc8f6f159624470c6da5a', '107906', '1.49', '2', '2018-12-19 19:37:06', null);
INSERT INTO `member` VALUES ('108009', '玉纯', '13577962498', '0b9ac092f3a0e25f5e9124cb9d869003', '107905', '84.07', '2', '2018-12-19 19:37:08', null);
INSERT INTO `member` VALUES ('108010', '涛涛', '18896159212', '9797bb599a08cb8f0cccc678550646d7', '107953', '0.32', '2', '2018-12-19 19:37:19', null);
INSERT INTO `member` VALUES ('108011', '肖海丽', '13617359977', '1efbee3a698957b38a419b93dce1da0f', '107908', '0.00', '2', '2018-12-19 19:37:20', null);
INSERT INTO `member` VALUES ('108012', '纪艳', '15830370438', 'dc483e80a7a0bd9ef71d8cf973673924', '107985', '0.00', '2', '2018-12-19 19:37:28', null);
INSERT INTO `member` VALUES ('108013', '吴林容', '18376499628', 'a2eb3fe61d8608b547fa1f431faf7726', '107913', '0.00', '2', '2018-12-19 19:37:33', null);
INSERT INTO `member` VALUES ('108014', '甜心', '13868222581', 'a636f2f54e2f23cf6793ac65f276e7c4', '107968', '124.66', '2', '2018-12-19 19:38:00', null);
INSERT INTO `member` VALUES ('108015', '执着', '15987802152', 'fa6b00f5d8a965042551e9e2ae4e28ba', '107906', '0.34', '2', '2018-12-19 19:38:04', null);
INSERT INTO `member` VALUES ('108016', '韩月', '15833501162', 'bb3b5dd34ed52f860507d639e2d282b0', '107906', '1.49', '2', '2018-12-19 19:38:38', null);
INSERT INTO `member` VALUES ('108017', '朵儿', '18623751529', 'e899be8bc281e2ddc3de5bb5449f9459', '107983', '1.49', '2', '2018-12-19 19:38:53', null);
INSERT INTO `member` VALUES ('108018', '徐仙', '15559691777', '3c00b1d5d65ba7f3d17f90ad9a0bda5d', '107908', '0.00', '2', '2018-12-19 19:39:09', null);
INSERT INTO `member` VALUES ('108019', '乐乐妈咪', '15328637275', '90185e806f1626bbc2c0692b4bd7d25f', '107985', '0.70', '2', '2018-12-19 19:40:16', null);
INSERT INTO `member` VALUES ('108020', '张建琼', '15398783038', '07b68be905ddc7243d684007163d9fcd', '107925', '0.00', '2', '2018-12-19 19:40:24', null);
INSERT INTO `member` VALUES ('108021', '邹江玲', '15008776399', '19fd8ebf7c533fa47fea361e5b92c083', '107905', '0.00', '2', '2018-12-19 19:40:25', null);
INSERT INTO `member` VALUES ('108022', '黄文婷', '13680582229', '9216c4591cc44644a0aaa45a92f377ba', '1', '0.00', '2', '2018-12-19 19:40:32', null);
INSERT INTO `member` VALUES ('108023', '平淡生活出色彩', '15207001528', '9c86aebd0bdbbc47fce71f7521f96352', '10173', '0.00', '2', '2018-12-19 19:40:45', null);
INSERT INTO `member` VALUES ('108024', '张志良', '15999182133', 'fc8dbb48d9ea615341e26876eaa9221c', '107927', '108.38', '2', '2018-12-19 19:41:28', null);
INSERT INTO `member` VALUES ('108025', '李春荣', '15031541590', 'd5739c999611a0c61d1f98d4f13d5099', '107960', '0.27', '2', '2018-12-19 19:41:31', null);
INSERT INTO `member` VALUES ('108026', '莲子', '18975104800', '2a0b9146612dd65c470f75ea4978d5ec', '107908', '0.00', '2', '2018-12-19 19:41:35', null);
INSERT INTO `member` VALUES ('108027', '唐娈', '13319590994', '1036dd23327272ea523a1a11d977731b', '107908', '23.70', '2', '2018-12-19 19:41:39', null);
INSERT INTO `member` VALUES ('108028', '雷婷婷', '15826719258', '998229732ab8b3b1eb7a0297de5d7779', '107921', '0.40', '2', '2018-12-19 19:42:05', null);
INSERT INTO `member` VALUES ('108029', '林秋怡', '15977474525', '67d0d20751175881bd2f8ece381b5dfd', '107937', '0.00', '2', '2018-12-19 19:42:11', null);
INSERT INTO `member` VALUES ('108030', '梁柳杨', '18074111743', 'f32311ca8c5da891d9882e97bdb8b381', '107913', '8.83', '2', '2018-12-19 19:42:25', null);
INSERT INTO `member` VALUES ('108031', '拾叁', '15353598639', 'd166fdee002a8b520c38e1c74d34fb80', '107905', '0.00', '2', '2018-12-19 19:42:58', null);
INSERT INTO `member` VALUES ('108032', '王丽', '15885933802', 'e674f458e566554d8b08ca85adb10277', '1', '0.00', '2', '2018-12-19 19:42:59', null);
INSERT INTO `member` VALUES ('108033', '龙仙', '15868653920', '89c089588228215d2fd5a88d4d290469', '107956', '0.89', '2', '2018-12-19 19:43:01', null);
INSERT INTO `member` VALUES ('108034', '赵小萁', '13769188090', 'ba2d1624d191401e26b444351b57020f', '107973', '0.00', '2', '2018-12-19 19:43:09', null);
INSERT INTO `member` VALUES ('108035', '何萍', '15963992722', '30726cc476fac88dbb1b2b0f6cca06c1', '108004', '8.94', '2', '2018-12-19 19:43:13', null);
INSERT INTO `member` VALUES ('108036', '林泉玉', '18789221309', '5ed1faf6d9ec2e3bebc424903283bbd3', '1', '0.00', '2', '2018-12-19 19:43:27', null);
INSERT INTO `member` VALUES ('108037', '英姐', '15578240928', 'fd6e3123f6d29a2c4f65fe02c0a3a75e', '107913', '1.49', '2', '2018-12-19 19:43:36', null);
INSERT INTO `member` VALUES ('108038', '闫欣欣', '15234101068', '7c7e290c6d4d72d798d17e6b2b42a22d', '107905', '64.76', '2', '2018-12-19 19:43:36', null);
INSERT INTO `member` VALUES ('108039', '陈树琼', '13888411068', 'a6c2a4a217c990f8c6cdc4af9e358a72', '107973', '0.00', '2', '2018-12-19 19:43:39', null);
INSERT INTO `member` VALUES ('108040', '游槐云', '13979580810', '16b874be7f2a66b971c226d8456e1f2a', '107943', '0.00', '2', '2018-12-19 19:43:46', null);
INSERT INTO `member` VALUES ('108041', '尹艳红', '13292353059', 'cf6124f579b9fc65e1aa5ae6a451a5c8', '107902', '7.43', '2', '2018-12-19 19:43:49', null);
INSERT INTO `member` VALUES ('108042', '小语姑娘', '15375760901', '009d5eeed12f33b5a0857385fdc19d0f', '107968', '35.85', '2', '2018-12-19 19:44:12', null);
INSERT INTO `member` VALUES ('108043', '韩莉莉', '13874352103', 'a842dc7f1bbbd7c76db25d86b6baed3c', '107992', '0.00', '2', '2018-12-19 19:44:13', null);
INSERT INTO `member` VALUES ('108044', '张美丽', '15254680333', 'a4e23c639213772b35bec96da0107ba9', '10078', '0.00', '2', '2018-12-19 19:44:14', null);
INSERT INTO `member` VALUES ('108045', '记忆', '15842780176', '315bf109c254dc51d3dc6005671bb038', '107985', '4.49', '2', '2018-12-19 19:44:16', null);
INSERT INTO `member` VALUES ('108046', '小北', '15108248816', 'abc682ac1c8734e9cb86bca21ba7b897', '1', '0.00', '2', '2018-12-19 19:44:16', null);
INSERT INTO `member` VALUES ('108047', '刘占新', '15227593786', '6f706b897ae626de3a4dbec8d6cf224e', '107923', '0.27', '2', '2018-12-19 19:44:28', null);
INSERT INTO `member` VALUES ('108048', '李丽', '18692366627', 'adf00707a1c0154a9ad8edb57c8646f4', '107983', '2.98', '2', '2018-12-19 19:44:31', null);
INSERT INTO `member` VALUES ('108049', '谢彦', '13875567894', 'd6f4075287340042e5a3450f3326926b', '107981', '0.00', '2', '2018-12-19 19:44:44', null);
INSERT INTO `member` VALUES ('108050', '彭翠林', '13005915513', 'b3890af68fe7b31e97154eac3154a092', '107937', '0.00', '2', '2018-12-19 19:44:44', null);
INSERT INTO `member` VALUES ('108051', '素叶子', '18081670018', 'a72ad0378f08475d943f332eab705ba8', '1', '0.00', '2', '2018-12-19 19:44:50', null);
INSERT INTO `member` VALUES ('108052', '冯小丹', '15393785004', 'dbadc16acb69c6964757cd8873c61fe4', '107982', '0.00', '2', '2018-12-19 19:45:35', null);
INSERT INTO `member` VALUES ('108053', '杨瑛', '13437842829', 'c05efbbe8fb3358861590608352e33da', '107977', '28.84', '2', '2018-12-19 19:46:15', null);
INSERT INTO `member` VALUES ('108054', '崔广旭', '15233441631', 'ed38abc42f7352c9cf22d96b349c6f3a', '107902', '8.94', '2', '2018-12-19 19:46:35', null);
INSERT INTO `member` VALUES ('108055', '秦艳', '15398667952', '29b5485742cfcb364141b158f27d182d', '108005', '0.00', '2', '2018-12-19 19:46:39', null);
INSERT INTO `member` VALUES ('108056', '宋春晓', '17729789162', '8a78f8c68f9512c197191b0c6195812d', '107906', '182.80', '2', '2018-12-19 19:46:41', null);
INSERT INTO `member` VALUES ('108057', '吴金珍', '17721298038', '77478b1e2040d8bebfd1ece75eb12872', '107901', '0.00', '2', '2018-12-19 19:46:53', null);
INSERT INTO `member` VALUES ('108058', '黄福贵', '15200776186', 'c81b0bf5cea19dff90e60f635f6e222b', '107908', '14.70', '2', '2018-12-19 19:46:55', null);
INSERT INTO `member` VALUES ('108059', '知足常乐', '13622816601', 'cd986227c7435b9ac9173aa43b158fb3', '107991', '59.55', '2', '2018-12-19 19:46:56', null);
INSERT INTO `member` VALUES ('108060', '黄英超', '13450966878', '5f01501ea0147dbf2df98d05d0161008', '107913', '0.00', '2', '2018-12-19 19:47:03', null);
INSERT INTO `member` VALUES ('108061', '李慧', '15639132518', '408708979cc0cf58bda739a41a47e67a', '107929', '0.00', '2', '2018-12-19 19:47:07', null);
INSERT INTO `member` VALUES ('108062', '杨燕燕', '13924202624', 'b1dfeae804a52441b47888d7dd5420bb', '107908', '0.00', '2', '2018-12-19 19:47:18', null);
INSERT INTO `member` VALUES ('108063', '新彪', '15873876451', '41401359727890be7e2bd4dc795b8911', '107905', '0.00', '2', '2018-12-19 19:47:49', null);
INSERT INTO `member` VALUES ('108064', '君子兰', '13994123904', 'b3f4bf3da29759c21a367b749276e8c7', '107939', '37.34', '2', '2018-12-19 19:47:51', null);
INSERT INTO `member` VALUES ('108065', '李广丽', '13288675686', '1e4403840602bfafc53a7d5f9653bfc0', '107977', '0.89', '2', '2018-12-19 19:48:14', null);
INSERT INTO `member` VALUES ('108066', '赵花', '18725261917', 'dfc7f12a3282bc1d381d6e3c048d6f83', '107956', '0.00', '2', '2018-12-19 19:48:15', null);
INSERT INTO `member` VALUES ('108067', '余秋艳', '13878863816', '3a70e01da4ea8f8e287fb3437b01fdee', '107937', '86.96', '2', '2018-12-19 19:48:17', null);
INSERT INTO `member` VALUES ('108068', '蝴蝶兰', '13025910550', 'a738d24b1347f2e68145dc6702bea01f', '107953', '23.23', '2', '2018-12-19 19:48:20', null);
INSERT INTO `member` VALUES ('108069', '飞哥', '13933018970', 'b85cad0eb66f5c7c9aacd1fcfa60575a', '107923', '111.63', '2', '2018-12-19 19:48:24', null);
INSERT INTO `member` VALUES ('108070', '段思娟', '18108793989', '45c73e3e563aa0eea13bc817222dd71a', '1', '0.00', '2', '2018-12-19 19:48:40', null);
INSERT INTO `member` VALUES ('108071', '蓉蓉', '13535067792', '01b12a2fcbebd26ede5f5c935d9311f7', '107906', '214.07', '2', '2018-12-19 19:48:57', null);
INSERT INTO `member` VALUES ('108072', '刘良霜', '18320282895', '7003bfa6241ea0de0aec052e199defde', '107977', '66.74', '2', '2018-12-19 19:49:07', null);
INSERT INTO `member` VALUES ('108073', '李丹', '15616821689', '56b6025f0bf28a743124f38551817b94', '107981', '0.00', '2', '2018-12-19 19:49:20', null);
INSERT INTO `member` VALUES ('108074', '谢桂秋', '15924776113', 'bd5c7e8002bb645dac42947859fbf386', '107939', '1.49', '2', '2018-12-19 19:49:29', null);
INSERT INTO `member` VALUES ('108075', '刘明珠', '15887656601', '044972eb62f19a545bfde6139d64ba41', '107956', '14.70', '2', '2018-12-19 19:49:57', null);
INSERT INTO `member` VALUES ('108076', '李如兰', '13679321218', 'eaeb73f409cdd98b3767278462e9a9b4', '1', '0.00', '2', '2018-12-19 19:50:29', null);
INSERT INTO `member` VALUES ('108077', '陈苹', '15969589019', '28074e07a7351a849cdf7f5a4343262c', '10077', '0.48', '2', '2018-12-19 19:50:32', '2019-01-02 15:30:04');
INSERT INTO `member` VALUES ('108078', '小芳', '18670529526', '592797aca254f8c6f1db521cda8c5cb6', '107981', '35.85', '2', '2018-12-19 19:51:14', null);
INSERT INTO `member` VALUES ('108079', '张华', '13552802732', '49664b0ccfcb53e5072542be69554c1d', '1', '0.00', '2', '2018-12-19 19:51:21', null);
INSERT INTO `member` VALUES ('108080', '晶莹', '18336350147', '504f8284db98bd564263418144d6d457', '10109', '0.00', '2', '2018-12-19 19:51:27', null);
INSERT INTO `member` VALUES ('108081', '唐春艳', '18089537180', '0482b0f24dc8818032f3ca166fd85521', '10084', '0.00', '2', '2018-12-19 19:51:28', null);
INSERT INTO `member` VALUES ('108082', '小不点', '13068809048', 'f18ca52cc68ecb00dee496bfe2423702', '107906', '0.00', '2', '2018-12-19 19:51:36', null);
INSERT INTO `member` VALUES ('108083', '古丽', '17699053151', 'c79193472e2c5f3471351a248c71301f', '107927', '0.00', '2', '2018-12-19 19:51:41', null);
INSERT INTO `member` VALUES ('108084', '王原', '18031926320', '5366c73a0e460af12824cfef4668da35', '1', '0.00', '2', '2018-12-19 19:51:58', null);
INSERT INTO `member` VALUES ('108085', '陈利伟', '15133828767', 'd66b1f37f5e95e2ae8e354e753b77159', '107902', '0.00', '2', '2018-12-19 19:52:01', null);
INSERT INTO `member` VALUES ('108086', '王瑞敏', '13810933985', '1ee1d9603957760899011257f542ff00', '1', '0.00', '2', '2018-12-19 19:52:14', null);
INSERT INTO `member` VALUES ('108087', '三三', '13807350788', 'f2dc7a87f1a937f2d190c8b6ae9b65bb', '10077', '0.00', '2', '2018-12-19 19:52:25', null);
INSERT INTO `member` VALUES ('108088', '李晶', '18262477112', 'bca00956b43d73efc498c9d2bf442e92', '10109', '0.00', '2', '2018-12-19 19:52:25', null);
INSERT INTO `member` VALUES ('108089', '春', '15856042818', '1cffe65aba81dec9bb6d277d812e2416', '107908', '137.56', '2', '2018-12-19 19:52:44', '2019-01-03 10:18:50');
INSERT INTO `member` VALUES ('108090', '文琦', '13714337512', '6123d8a0ce9a445ade3ce9c6b72c9899', '107993', '0.00', '2', '2018-12-19 19:52:51', null);
INSERT INTO `member` VALUES ('108091', '安淑华', '13111284091', '0833fd9733050923fa03ff4b92bb0121', '10077', '7.43', '2', '2018-12-19 19:52:53', null);
INSERT INTO `member` VALUES ('108092', '侯淑莲', '18732458098', '8a1148a74ba479fcaca5e34f5de73d45', '107902', '0.00', '2', '2018-12-19 19:53:29', null);
INSERT INTO `member` VALUES ('108093', '曹迎迎', '15826788738', '0b061dd3dad209d952a977b6d1eba11c', '107921', '8.32', '2', '2018-12-19 19:54:02', null);
INSERT INTO `member` VALUES ('108094', '陈玲', '15288246200', 'c6b3b6f4edf13662b3d26e9f56f1a21d', '107908', '0.00', '2', '2018-12-19 19:54:08', null);
INSERT INTO `member` VALUES ('108095', '李春花', '13577955160', '325a3202336765fa7a3f33040db44972', '107984', '0.00', '2', '2018-12-19 19:54:11', null);
INSERT INTO `member` VALUES ('108096', '张凯婷', '15028551982', '17d6d144e99e14961a04dec21c8072fe', '1', '0.00', '2', '2018-12-19 19:54:31', null);
INSERT INTO `member` VALUES ('108097', '王正侠', '13636236223', '3db73b22f300893a51d5ba654848bbf5', '107932', '0.00', '2', '2018-12-19 19:54:34', null);
INSERT INTO `member` VALUES ('108098', '李玉芹', '15008840442', '8e0858e1f464273696f33bd9b9c088b9', '107958', '2.98', '2', '2018-12-19 19:54:46', null);
INSERT INTO `member` VALUES ('108099', '爱青', '15970382612', '6d4be821fa69df218cbccdb1cba472c7', '107956', '7.45', '2', '2018-12-19 19:54:55', null);
INSERT INTO `member` VALUES ('108100', '霞飞', '18997725709', '9ab593632d411c3bdab8b92d8123de40', '107905', '37.34', '2', '2018-12-19 19:55:01', null);
INSERT INTO `member` VALUES ('108101', '吕莉霞', '13721419795', 'bc22b39e787105376caa1796a4907f96', '108056', '120.60', '2', '2018-12-19 19:55:07', null);
INSERT INTO `member` VALUES ('108102', '林琴', '13645069814', '449cdb40a5f174c0e59135eb9921e063', '107906', '3.73', '2', '2018-12-19 19:55:17', null);
INSERT INTO `member` VALUES ('108103', '周艳', '15172763739', '982ee89146c2ac080c4841314332d3b4', '107932', '85.80', '2', '2018-12-19 19:55:18', null);
INSERT INTO `member` VALUES ('108104', '金亚新', '13513025183', 'b20277f8e9ba9b3b50c5b2014b8142d9', '107923', '0.00', '2', '2018-12-19 19:55:37', null);
INSERT INTO `member` VALUES ('108105', '张文艳', '13769288952', 'deb19b0bc6b3ff1d357f266b0e20da8e', '107905', '764.18', '2', '2018-12-19 19:55:49', null);
INSERT INTO `member` VALUES ('108106', '陈小平', '18728667884', '5ba838647f5f0318cd05509e0788018d', '10084', '0.00', '2', '2018-12-19 19:56:03', null);
INSERT INTO `member` VALUES ('108107', '王庆', '13618090577', '3d24b838770ee90773804e8599e549ff', '107990', '144.89', '2', '2018-12-19 19:56:12', null);
INSERT INTO `member` VALUES ('108108', '林春伶', '13481738048', '01c1741582466afd21199d53b111e4e7', '107980', '35.85', '2', '2018-12-19 19:56:12', null);
INSERT INTO `member` VALUES ('108109', '杨仙苹', '13769064278', '5909bba21acbad36b50b9b0dbffe9d28', '1', '0.00', '2', '2018-12-19 19:56:28', null);
INSERT INTO `member` VALUES ('108110', '侯虹君', '18909079987', '8a1148a74ba479fcaca5e34f5de73d45', '107906', '0.00', '2', '2018-12-19 19:56:52', null);
INSERT INTO `member` VALUES ('108111', '王丽娟', '13766262935', '2216e68a81dbbd87b3e41a7bc1763d7f', '108048', '0.00', '2', '2018-12-19 19:56:52', null);
INSERT INTO `member` VALUES ('108112', '徐丹', '13720352097', 'ec3b7babb9961ab3209c6ce3043dc105', '107983', '0.00', '2', '2018-12-19 19:56:54', null);
INSERT INTO `member` VALUES ('108113', '彝娜', '18487729462', 'c6ff958631b49b88506fa728653076e2', '1', '0.00', '2', '2018-12-19 19:57:01', null);
INSERT INTO `member` VALUES ('108114', '候晓燕', '15103743910', '7560881658ea6889d0707c34fc3f3cfd', '107982', '0.00', '2', '2018-12-19 19:57:05', null);
INSERT INTO `member` VALUES ('108115', '莉', '13835012660', '32875de4cc69fa228fc03a51fd48432a', '107939', '0.00', '2', '2018-12-19 19:57:16', null);
INSERT INTO `member` VALUES ('108116', '蒋海艳', '15172778687', 'f8b374559e6a3e046fb614cf8c147ca5', '107921', '0.00', '2', '2018-12-19 19:57:19', null);
INSERT INTO `member` VALUES ('108117', '兰瑞萍', '13017359814', '0625b66936d7969f3d40ede5756febff', '1', '0.00', '2', '2018-12-19 19:57:28', null);
INSERT INTO `member` VALUES ('108118', '阿清', '18579987521', '54072e86cdd234056848c9aa7173c920', '107960', '0.00', '2', '2018-12-19 19:57:28', null);
INSERT INTO `member` VALUES ('108119', '王美巧', '13569543024', 'ec365663f056d64a31fad23159cb331e', '1', '0.00', '2', '2018-12-19 19:57:38', null);
INSERT INTO `member` VALUES ('108120', '袁会英', '15315222987', '18e8229edb366397da42781d4e96920c', '107901', '0.00', '2', '2018-12-19 19:57:45', null);
INSERT INTO `member` VALUES ('108121', '李女士', '13776356808', '306c11aedf2907b656dba9fd9f8f8108', '1', '0.00', '2', '2018-12-19 19:58:03', null);
INSERT INTO `member` VALUES ('108122', '郝建明', '13838926679', 'a79444625e0382c417c7105cba15f82a', '107929', '0.00', '2', '2018-12-19 19:58:04', null);
INSERT INTO `member` VALUES ('108123', '王菊玲', '13546583035', '4e5ea6be1223419b08b2406edbd011d5', '107938', '0.00', '2', '2018-12-19 19:58:14', null);
INSERT INTO `member` VALUES ('108124', '李丽艳', '15642750716', '5f9a2bb3c4e12247d1087730768e25c8', '107985', '1.49', '2', '2018-12-19 19:58:56', null);
INSERT INTO `member` VALUES ('108125', '张霞', '13695385385', '097a0d81cde06a1d5122e28d1a0cbb10', '1', '0.00', '2', '2018-12-19 19:59:05', null);
INSERT INTO `member` VALUES ('108126', '张晓华', '13330734518', '493ee2ff82f3965eb56bb133b708f840', '107906', '147.20', '2', '2018-12-19 19:59:10', null);
INSERT INTO `member` VALUES ('108127', '李娟', '18188425665', '45a0bfedaf65c5dcd716a28d99442e54', '108030', '53.67', '2', '2018-12-19 19:59:32', null);
INSERT INTO `member` VALUES ('108128', '谢美存', '18878506783', 'a1118c3ed4d82e67b2fb253b6772f2c3', '107978', '0.00', '2', '2018-12-19 19:59:38', null);
INSERT INTO `member` VALUES ('108129', '陈佳勤', '13999193438', '40a39ca8a39878db60ce416739f1038c', '107927', '0.00', '2', '2018-12-19 19:59:40', null);
INSERT INTO `member` VALUES ('108130', '小英', '13989632224', '240d815ed7e2b8b8ac43c643d0254ab6', '107956', '0.00', '2', '2018-12-19 19:59:52', null);
INSERT INTO `member` VALUES ('108131', '田芹', '13153862270', 'a0b483bc07656541e6521268d4fac02e', '10084', '0.00', '2', '2018-12-19 19:59:54', null);
INSERT INTO `member` VALUES ('108132', '金雅娟', '13930737162', '93f7379604703c7b6f1aa635206c66ed', '107923', '0.00', '2', '2018-12-19 20:00:08', null);
INSERT INTO `member` VALUES ('108133', '苏洁', '13467205120', '2f5b32217c67acd80469e60a6e146192', '107938', '0.00', '2', '2018-12-19 20:00:21', null);
INSERT INTO `member` VALUES ('108134', '今夕是何年', '13718284695', '22bc27b141bbb3f4f9c0745f123d56ad', '107922', '2.08', '2', '2018-12-19 20:00:39', '2018-12-23 09:23:02');
INSERT INTO `member` VALUES ('108135', '静静', '13221787170', 'b66a39afceaa9a8e04486b7c01ed4583', '107905', '2.97', '2', '2018-12-19 20:01:07', null);
INSERT INTO `member` VALUES ('108136', '瞿滢芳', '18087878112', '9f0af1b4758fb43edd7542eff17f0c62', '1', '0.00', '2', '2018-12-19 20:01:15', null);
INSERT INTO `member` VALUES ('108137', '月婷', '17606911130', 'dc483e80a7a0bd9ef71d8cf973673924', '107930', '0.00', '2', '2018-12-19 20:01:19', null);
INSERT INTO `member` VALUES ('108138', '高玲', '18714983369', 'adc73b871a37894f3768a783aea7f385', '108025', '0.00', '2', '2018-12-19 20:01:23', null);
INSERT INTO `member` VALUES ('108139', '张卫荭', '15707033213', '47a95d2a589c26fb1c1ff6517f9f6808', '107906', '0.00', '2', '2018-12-19 20:01:37', null);
INSERT INTO `member` VALUES ('108140', '曹仕颐', '13145898220', '8e5b976d41205da9b7ce1194b1d31484', '1', '0.00', '2', '2018-12-19 20:01:41', null);
INSERT INTO `member` VALUES ('108141', '陈惠雪', '13927902505', 'df84d20aca8eae635639080905bc7d37', '107906', '1.49', '2', '2018-12-19 20:01:54', null);
INSERT INTO `member` VALUES ('108142', '蒯春红', '13277227342', 'e136a2cac97bcc1deaab3d81d17870bf', '107921', '0.00', '2', '2018-12-19 20:02:05', null);
INSERT INTO `member` VALUES ('108143', '王稳菊', '13608844826', 'b0a3661c0024ef0adbcd9d6e0d301113', '107984', '3.57', '2', '2018-12-19 20:02:06', null);
INSERT INTO `member` VALUES ('108144', '邓迎霞', '13699220982', '8a4771ded840207e930258a8a87848fe', '107985', '2.97', '2', '2018-12-19 20:02:10', null);
INSERT INTO `member` VALUES ('108145', '杨艳会', '13390418849', '7f314ede0efb491294bce154edae4d11', '107902', '0.00', '2', '2018-12-19 20:02:10', null);
INSERT INTO `member` VALUES ('108146', '张玲', '15842747590', '7af53544cebcde0b4a51975bf1e21d19', '107985', '0.00', '2', '2018-12-19 20:02:19', null);
INSERT INTO `member` VALUES ('108147', '龙哥', '13673284299', '6dfbeda16361f2262fca3bf1534be403', '107990', '17.87', '2', '2018-12-19 20:03:04', null);
INSERT INTO `member` VALUES ('108148', '小兰兰', '18376670247', '7365c327a45069456ca8de5525a6244d', '107974', '2.98', '2', '2018-12-19 20:03:16', null);
INSERT INTO `member` VALUES ('108149', '曹凯丰', '18890108590', '3f1215b4637d52a473e1014650d6f6c6', '107981', '0.00', '2', '2018-12-19 20:03:21', null);
INSERT INTO `member` VALUES ('108150', '王春萍', '13593106493', '933f0a8793788196647b6cb783d9c116', '10077', '33.22', '2', '2018-12-19 20:03:26', null);
INSERT INTO `member` VALUES ('108151', '罗献春', '15978083851', '056a5b8fb02292cd2ac51318c6061ca3', '107906', '87.89', '2', '2018-12-19 20:03:37', null);
INSERT INTO `member` VALUES ('108152', '蔡波', '18817143149', '7cb0b40b81e6ca24960a88b9d09ad167', '107952', '133.81', '2', '2018-12-19 20:03:38', null);
INSERT INTO `member` VALUES ('108153', '杨翠', '13308896840', '784bbf1db83122f8ac13c59e476f9e78', '107984', '0.00', '2', '2018-12-19 20:03:50', null);
INSERT INTO `member` VALUES ('108154', '吴应香', '13529273227', '3d29577a95cbf06c5d36defce2d1fda3', '108002', '0.00', '2', '2018-12-19 20:04:01', null);
INSERT INTO `member` VALUES ('108155', '庞康梅', '13420121245', 'f78a4f05ef070d6a4034e4014016b209', '107941', '0.00', '2', '2018-12-19 20:04:03', null);
INSERT INTO `member` VALUES ('108156', '丹丹', '13427508566', '06e24fa4046d4941c0bddce27df07aa6', '1', '0.00', '2', '2018-12-19 20:04:05', null);
INSERT INTO `member` VALUES ('108157', '小虾米妈妈', '18193362297', '407c3090deefd681c405a74bd91c7dfe', '10084', '0.00', '2', '2018-12-19 20:04:20', null);
INSERT INTO `member` VALUES ('108158', '吕传芬', '15964035062', '5122d2e3225140d608ecdf5e2fe05f4f', '107906', '50.90', '2', '2018-12-19 20:04:39', null);
INSERT INTO `member` VALUES ('108159', '崔妹儿', '13578053504', '11d6447f6e08b8434bc1b51dbdd362d9', '107906', '0.00', '2', '2018-12-19 20:04:45', null);
INSERT INTO `member` VALUES ('108160', '王盼盼', '15072943226', '72dba78bf5fc1c0bad20d516651307dc', '1', '0.00', '2', '2018-12-19 20:04:45', null);
INSERT INTO `member` VALUES ('108161', '梁毅', '13629659287', 'e7f41b34ef359d5ad3427e6bf63df8c4', '107973', '0.00', '2', '2018-12-19 20:04:56', null);
INSERT INTO `member` VALUES ('108162', '谨宝', '13963142041', '9e75f4211dede2c96e07710db001fd21', '10077', '279.50', '2', '2018-12-19 20:05:52', '2018-12-20 09:54:22');
INSERT INTO `member` VALUES ('108163', '西浠', '18283261531', '8934b6f61bbc3052ce222113ba08d877', '1', '0.00', '2', '2018-12-19 20:06:07', null);
INSERT INTO `member` VALUES ('108164', '闫红南', '15731422408', '46c8eabb5f782ab8aaf96c39f58af72d', '108054', '4.47', '2', '2018-12-19 20:06:10', null);
INSERT INTO `member` VALUES ('108165', '周珍', '15882491716', 'be406c9e0cc3cf25dbfa3f2ca7353fd1', '1', '0.00', '2', '2018-12-19 20:06:10', null);
INSERT INTO `member` VALUES ('108166', '赖赖', '13878155826', '8c998ac852127a5f5a5128709e6320f7', '107952', '0.00', '2', '2018-12-19 20:06:26', null);
INSERT INTO `member` VALUES ('108167', '王小含', '13942749838', '6ac594f3fe0430396842923dfe5e3ab0', '107985', '0.00', '2', '2018-12-19 20:06:34', null);
INSERT INTO `member` VALUES ('108168', '汪海霞', '13387290337', 'ed26c5e26c4a1d66dd48d95d6fd63326', '107932', '0.00', '2', '2018-12-19 20:06:45', null);
INSERT INTO `member` VALUES ('108169', '黄丽萍', '13471742513', 'dc483e80a7a0bd9ef71d8cf973673924', '107906', '6.35', '2', '2018-12-19 20:06:50', null);
INSERT INTO `member` VALUES ('108170', '张喜凤', '15391193423', '68eae91fa62111e0c562aa3e056d7c03', '108025', '0.43', '2', '2018-12-19 20:07:07', null);
INSERT INTO `member` VALUES ('108171', '红丫丫假发店', '15964718058', '350b780bfac602a1fecf2e853a0bd632', '10077', '1.49', '2', '2018-12-19 20:07:48', null);
INSERT INTO `member` VALUES ('108172', '自然', '18011360797', '637051ba8f60014a3aa0d3f25cc90a5d', '107990', '0.93', '2', '2018-12-19 20:08:08', null);
INSERT INTO `member` VALUES ('108173', '安琪拉', '13527107019', '9a8d5a75cf511c985f5623e1775dc910', '108148', '0.00', '2', '2018-12-19 20:08:10', null);
INSERT INTO `member` VALUES ('108174', '杨洁', '18021968655', '0d4414f8118c7e1ce9fd0e3cff4663bb', '107922', '0.00', '2', '2018-12-19 20:08:42', null);
INSERT INTO `member` VALUES ('108175', '刘颖', '13521025536', '257c42b3e772a6c8aea39e060ae1ddc3', '107922', '13704.54', '2', '2018-12-19 20:08:42', null);
INSERT INTO `member` VALUES ('108176', '张颖', '18654688683', 'edfebaa01fda74649cd93f46267fe7f7', '10078', '0.00', '2', '2018-12-19 20:08:48', null);
INSERT INTO `member` VALUES ('108177', '辣椒', '18475464454', 'eba97091bdccedf7d12cb3b5bf6d5843', '107905', '2.98', '2', '2018-12-19 20:09:21', null);
INSERT INTO `member` VALUES ('108178', '韦丹丹', '18161183699', '891f6c14b636f2067bb497ef1f3006c9', '10077', '0.00', '2', '2018-12-19 20:09:22', null);
INSERT INTO `member` VALUES ('108179', '周友艳', '18113400274', 'b75482f1392b6a6b7d78c5fb5bfce716', '108152', '23.70', '2', '2018-12-19 20:10:15', null);
INSERT INTO `member` VALUES ('108180', '李小梅', '15777200461', 'e6ce022cfb743c66afc48244e311facf', '1', '0.00', '2', '2018-12-19 20:10:16', null);
INSERT INTO `member` VALUES ('108181', '吴小芬', '13978194420', 'fa675b9116925807cbd38c4e96f34be3', '107906', '109.27', '2', '2018-12-19 20:10:48', null);
INSERT INTO `member` VALUES ('108182', '葛晶晶', '18839072268', '71480134507a64c425e94c6e6607c50e', '107929', '0.00', '2', '2018-12-19 20:11:10', null);
INSERT INTO `member` VALUES ('108183', '香港妹', '13330776692', 'a035fba418912e990c547829ca17ec53', '108127', '15.54', '2', '2018-12-19 20:11:24', null);
INSERT INTO `member` VALUES ('108184', '张丽华', '13513156856', 'b4cc8bfc3fd0c52f973a63a8fcb1229c', '1', '0.00', '2', '2018-12-19 20:11:30', null);
INSERT INTO `member` VALUES ('108185', '刘泉泉', '18823113016', 'dafca380e5a07530f7df8a4eb88c23c0', '107981', '0.00', '2', '2018-12-19 20:11:42', null);
INSERT INTO `member` VALUES ('108186', '梁雪雄', '18777219808', 'dc483e80a7a0bd9ef71d8cf973673924', '107906', '146.38', '2', '2018-12-19 20:11:58', null);
INSERT INTO `member` VALUES ('108187', '净安', '13393970050', 'cbc8c9ce9ce67bfa0be94f74d3e3975a', '1', '0.00', '2', '2018-12-19 20:12:07', null);
INSERT INTO `member` VALUES ('108188', '麦子', '13367627191', '3dfc1696f5b5064fb8aaad23c839f86a', '107906', '0.00', '2', '2018-12-19 20:12:10', null);
INSERT INTO `member` VALUES ('108189', '赵端阳', '15537482993', '888fa00a5724061add2896aefcd7009a', '10577', '0.00', '2', '2018-12-19 20:12:38', null);
INSERT INTO `member` VALUES ('108190', '丹丹', '18791664609', '2258bc474afd9b6bd2557334a3872b32', '107906', '90.74', '2', '2018-12-19 20:13:04', null);
INSERT INTO `member` VALUES ('108191', '彩虹', '13610611152', '005732e451e51a84da1531b0ce7298a7', '1', '0.00', '2', '2018-12-19 20:13:17', null);
INSERT INTO `member` VALUES ('108192', '女王', '13919256350', '948c953777bcdfc25265b45f6ce05f95', '107906', '25.56', '2', '2018-12-19 20:13:56', null);
INSERT INTO `member` VALUES ('108193', '聂静静', '18766736517', '24f5d194717ec5d684ecf3d7dd36747b', '1', '0.00', '2', '2018-12-19 20:14:12', null);
INSERT INTO `member` VALUES ('108194', '张燕', '18376696993', 'df3aafa9020aac6d7d0ca52e79f7c177', '107952', '0.00', '2', '2018-12-19 20:14:34', null);
INSERT INTO `member` VALUES ('108195', '清清', '13870394387', '10fb3eb5567367d7840806d273f34327', '107956', '69.72', '2', '2018-12-19 20:14:37', null);
INSERT INTO `member` VALUES ('108196', '张雯丹', '15275699978', '4ea1ad3bc43b550e0cda6f643cd83e8a', '1', '0.00', '2', '2018-12-19 20:14:38', null);
INSERT INTO `member` VALUES ('108197', '刘作敏', '15575757272', 'ecb2859e66f9713188185fd2ceb1e6c0', '107981', '0.00', '2', '2018-12-19 20:14:44', null);
INSERT INTO `member` VALUES ('108198', '张建梅', '13932650135', 'ef73781effc5774100f87fe2f437a435', '108064', '0.00', '2', '2018-12-19 20:14:59', null);
INSERT INTO `member` VALUES ('108199', '亚萍', '15343125871', '2b00765b4eaf69469d9432cf5bdf1817', '108147', '0.00', '2', '2018-12-19 20:15:20', null);
INSERT INTO `member` VALUES ('108200', '张姐', '18762271484', 'eea7ec98a478c23da138be0b18118014', '1', '0.00', '2', '2018-12-19 20:15:27', null);
INSERT INTO `member` VALUES ('108201', '舒银', '13888784960', '3909c56ea0282f09f25a0329881727b0', '107973', '0.00', '2', '2018-12-19 20:15:51', null);
INSERT INTO `member` VALUES ('108202', '黄小玲', '15078480912', '2af2eb4d33a723369874b4cdefffdf30', '107960', '0.00', '2', '2018-12-19 20:15:59', null);
INSERT INTO `member` VALUES ('108203', '杨洋', '15808836587', '154c633086a13681fcd14135a2927260', '107956', '0.00', '2', '2018-12-19 20:16:28', null);
INSERT INTO `member` VALUES ('108204', '惠子', '18296770918', '8d028733aa6807cb7ec82fab25cefafb', '107952', '0.00', '2', '2018-12-19 20:16:44', null);
INSERT INTO `member` VALUES ('108205', '夏琳', '17773501775', '1220c1a3c86171aa5038667db8fc7e19', '1', '0.00', '2', '2018-12-19 20:16:58', null);
INSERT INTO `member` VALUES ('108206', '琪小宝', '13888635492', 'd5e416b5fa8a0d5a94130f9759a4e37d', '107973', '0.00', '2', '2018-12-19 20:17:08', null);
INSERT INTO `member` VALUES ('108207', '吴翠连', '13299670068', 'c530504478b1f217c487c7eeeb38fa46', '107952', '1.49', '2', '2018-12-19 20:17:08', null);
INSERT INTO `member` VALUES ('108208', '海燕', '15882510371', '4c13c904fa7c6f4506d628d279c5f55c', '107906', '39.20', '2', '2018-12-19 20:17:23', null);
INSERT INTO `member` VALUES ('108209', '丽丽', '18779580060', 'f6d71e0ad0b3b129dc7b22c9c38b6085', '107913', '96.95', '2', '2018-12-19 20:17:39', null);
INSERT INTO `member` VALUES ('108210', '陶宝妈妈', '15021042980', '508ceb5a04b0de39a4896346325ed2da', '107953', '175.51', '2', '2018-12-19 20:18:08', null);
INSERT INTO `member` VALUES ('108211', '程秋芬', '13888914509', 'e33e2a3e6aea495ba55e3128a930b27e', '1', '0.00', '2', '2018-12-19 20:18:11', null);
INSERT INTO `member` VALUES ('108212', '李正芹', '15987512780', '82a5dd1efd82badb113b9fb61494d787', '108098', '0.00', '2', '2018-12-19 20:18:16', null);
INSERT INTO `member` VALUES ('108213', '肖生奎', '13628014716', '6db46d7dd5637d29560ef4a79249b60a', '107990', '280.08', '2', '2018-12-19 20:19:08', null);
INSERT INTO `member` VALUES ('108214', '李瑞', '18988192048', '54658b4621f1d3dd708241eca37f37a4', '107984', '1.49', '2', '2018-12-19 20:19:13', null);
INSERT INTO `member` VALUES ('108215', '韦珍', '13543080837', 'dd3f795bdd8c504b6836d650932eacb0', '107941', '73.65', '2', '2018-12-19 20:19:39', null);
INSERT INTO `member` VALUES ('108216', '林日龙', '18378059877', '523f9b71dd745e2f9d5846189cbf99f2', '107978', '0.00', '2', '2018-12-19 20:19:42', null);
INSERT INTO `member` VALUES ('108217', '谷孝月', '15130592549', '5f798f2a98e925307b40189a74d7210c', '108025', '0.00', '2', '2018-12-19 20:19:46', null);
INSERT INTO `member` VALUES ('108218', '杨晓鸿', '15287150585', '32289907d06faf45a3ce8be194409020', '107905', '0.00', '2', '2018-12-19 20:19:52', null);
INSERT INTO `member` VALUES ('108219', '杨浩', '14736430941', '993f0bff4e86db637a301da27ca4511e', '107956', '0.00', '2', '2018-12-19 20:19:57', null);
INSERT INTO `member` VALUES ('108220', '晓丽', '13932400331', '62a881c856c2e27cfbd22bbf96faeb31', '107902', '0.00', '2', '2018-12-19 20:20:11', null);
INSERT INTO `member` VALUES ('108221', '冯春芳', '15077787186', '8b3b2bf096788d4a50f5fd9e67258374', '107936', '17.68', '2', '2018-12-19 20:20:26', null);
INSERT INTO `member` VALUES ('108222', '赵祎明', '13795053957', '4914974e84b5fa6858888891d8b58a58', '108124', '0.00', '2', '2018-12-19 20:20:40', null);
INSERT INTO `member` VALUES ('108223', '岚岚', '15800537831', 'da3177cbd9f064004b6a0d59a3a484bb', '107906', '0.00', '2', '2018-12-19 20:20:44', null);
INSERT INTO `member` VALUES ('108224', '李勤', '18947057949', 'bb6683f16d6b2ac257ed00961572ab1b', '10109', '0.00', '2', '2018-12-19 20:21:23', null);
INSERT INTO `member` VALUES ('108225', '何海滩', '18973524585', 'cda873f94549947819b3d02d10aa42ec', '1', '0.00', '2', '2018-12-19 20:21:29', null);
INSERT INTO `member` VALUES ('108226', '王瑞芳', '15175248877', '0a7de0432ca766c8b5128c0c44741dd8', '108147', '0.00', '2', '2018-12-19 20:21:33', null);
INSERT INTO `member` VALUES ('108227', '李娜娜', '18586503732', '8502bb8b10c2f66749f3f6481858c9e0', '108152', '0.00', '2', '2018-12-19 20:21:55', null);
INSERT INTO `member` VALUES ('108228', '黄月娇', '13977786451', '59e7a4344d075ed810c64548f69f4436', '108169', '0.00', '2', '2018-12-19 20:22:11', null);
INSERT INTO `member` VALUES ('108229', '安然', '13839123281', '0a3c867d025e264c6ea4b17b048a0c99', '107952', '0.00', '2', '2018-12-19 20:22:43', null);
INSERT INTO `member` VALUES ('108230', '王效川', '13869620198', '46f94c8de14fb36680850768ff1b7f2a', '10103', '0.00', '2', '2018-12-19 20:22:51', null);
INSERT INTO `member` VALUES ('108231', '卢欣', '13407730829', '7cf7627ec1f6fbaab80d7c5e58464594', '107937', '0.00', '2', '2018-12-19 20:24:43', null);
INSERT INTO `member` VALUES ('108232', '罗妤菲', '13265076205', '00d0b9373b7a3f7c313210e7922cdd66', '108126', '0.00', '2', '2018-12-19 20:24:55', null);
INSERT INTO `member` VALUES ('108233', '桃子', '18265573817', '52f18e8c0cd57343d6a9ed48f76ece58', '10077', '0.00', '2', '2018-12-19 20:25:14', '2019-01-01 20:08:05');
INSERT INTO `member` VALUES ('108234', '方美娟', '13860354861', '1586d6a2deb2f1eb8b659ddc43ccaea9', '107952', '35.85', '2', '2018-12-19 20:25:17', null);
INSERT INTO `member` VALUES ('108235', '逯育涵', '13608847173', 'bec073fe9b47e38e3683709433a541ed', '1', '0.00', '2', '2018-12-19 20:26:10', null);
INSERT INTO `member` VALUES ('108236', '陈荣葵', '15177901770', '550f3ad36c7904478ea58b97d0d3c491', '108152', '1.49', '2', '2018-12-19 20:26:13', null);
INSERT INTO `member` VALUES ('108237', '陈婷婷', '13524378360', '9b700433958c0c97b06fcdb53ea15290', '108210', '0.00', '2', '2018-12-19 20:26:29', null);
INSERT INTO `member` VALUES ('108238', '张淑莉', '15233397027', '602eb329304a5b715c1bd282fb432b6b', '1', '0.00', '2', '2018-12-19 20:26:46', null);
INSERT INTO `member` VALUES ('108239', '张燕', '17375148150', '71de910e290c6737e96cc9e3755c8dab', '1', '0.00', '2', '2018-12-19 20:27:12', null);
INSERT INTO `member` VALUES ('108240', '陆美华', '15951308486', '7edd903dc8cbccebf2a257b50bc2596c', '10163', '1.49', '2', '2018-12-19 20:27:13', null);
INSERT INTO `member` VALUES ('108241', '镜花水月', '15894592097', '47788592b8dce94c7f91b1f60402c558', '108105', '50.29', '2', '2018-12-19 20:27:29', null);
INSERT INTO `member` VALUES ('108242', '张铃雁', '13577127555', '95d342dfcf4e9be99a508d2f3f205d52', '107905', '0.00', '2', '2018-12-19 20:27:37', null);
INSERT INTO `member` VALUES ('108243', '菠菜', '15388008836', '7cb0b40b81e6ca24960a88b9d09ad167', '108152', '4.87', '2', '2018-12-19 20:27:38', null);
INSERT INTO `member` VALUES ('108244', '陈小毛', '15110527070', '5fa067bca298945d256c1d79cd899e87', '107939', '1.49', '2', '2018-12-19 20:27:42', null);
INSERT INTO `member` VALUES ('108245', '熊亚连', '15172327131', 'fb1c2112c1cf1425a02df4e91eaa484a', '108177', '59.68', '2', '2018-12-19 20:27:46', null);
INSERT INTO `member` VALUES ('108246', '曾琴', '18178002926', '07b68be905ddc7243d684007163d9fcd', '107953', '2.98', '2', '2018-12-19 20:27:49', null);
INSERT INTO `member` VALUES ('108247', '岁月静好', '18570557381', '26ed3723cd2500bf437820f05219ad9c', '107981', '0.00', '2', '2018-12-19 20:28:02', null);
INSERT INTO `member` VALUES ('108248', '刘双清', '17670967109', '9f3a6e66a99096ba6c728f8049d232d2', '107908', '106.62', '2', '2018-12-19 20:28:12', '2018-12-23 09:23:25');
INSERT INTO `member` VALUES ('108249', '江庆美', '13543431800', '32c42ee572609580d44aa2324b193c91', '10077', '130.55', '2', '2018-12-19 20:28:13', null);
INSERT INTO `member` VALUES ('108250', '丫头', '15639139533', 'adf00707a1c0154a9ad8edb57c8646f4', '107929', '0.00', '2', '2018-12-19 20:28:24', null);
INSERT INTO `member` VALUES ('108251', '星语', '13769131120', '8e83122f65b1b44ba82787e2e442f67f', '107905', '0.00', '2', '2018-12-19 20:28:30', null);
INSERT INTO `member` VALUES ('108252', '王昱録沅', '13933294513', '5a42044ab0eb254e0d5f5de4bbd3d23d', '10285', '0.00', '2', '2018-12-19 20:28:31', null);
INSERT INTO `member` VALUES ('108253', '李小露', '13457580400', '8e8458ffc773b4511e3d8a4f90902b61', '107953', '0.00', '2', '2018-12-19 20:28:50', null);
INSERT INTO `member` VALUES ('108254', '冬天里的一抹香', '18931670585', 'd99421bd7d4a89005434342a44a65b41', '107939', '1.49', '2', '2018-12-19 20:29:08', null);
INSERT INTO `member` VALUES ('108255', '苏雪莲', '13888250101', 'a906449d5769fa7361d7ecc6aa3f6d28', '107973', '0.00', '2', '2018-12-19 20:29:51', null);
INSERT INTO `member` VALUES ('108256', '杨光琼', '18200353817', '43aa0b34f7398b14be4fa3e47b4cad6d', '108213', '20.66', '2', '2018-12-19 20:29:57', null);
INSERT INTO `member` VALUES ('108257', '李蕾', '15238716673', '10b0025e4fb95ee3dc17f8bff500c6ed', '107929', '0.00', '2', '2018-12-19 20:30:15', null);
INSERT INTO `member` VALUES ('108258', '丁婷婷', '13475256972', '87f7fcaf49b548a1ab22f08f62203188', '10050', '1.49', '2', '2018-12-19 20:30:21', null);
INSERT INTO `member` VALUES ('108259', '冯彩霞', '18299116895', '7ea474eec40c9d734a974bb8bbd9e56f', '107927', '0.00', '2', '2018-12-19 20:30:47', null);
INSERT INTO `member` VALUES ('108260', '吴茜', '13550127935', 'a73a56c5cb2a911f48cccbb8ccdebb00', '108172', '0.00', '2', '2018-12-19 20:30:51', null);
INSERT INTO `member` VALUES ('108261', '侯丽娜', '18715900858', '1488145e68a40f97d1c02ccb125f49d0', '1', '0.00', '2', '2018-12-19 20:31:05', null);
INSERT INTO `member` VALUES ('108262', '梁凤琼', '18476419699', '7c81bb114a3091e110fd2c0f6de56126', '107913', '0.00', '2', '2018-12-19 20:31:10', null);
INSERT INTO `member` VALUES ('108263', '胡丹丹', '15225288576', '450fe8634c9ac2b1f1e756e646b5b2a2', '107913', '4.47', '2', '2018-12-19 20:31:35', null);
INSERT INTO `member` VALUES ('108264', '李心忆', '15128558700', 'f66c46da6f9be739a563a7b614ffc8ce', '107902', '0.00', '2', '2018-12-19 20:31:45', null);
INSERT INTO `member` VALUES ('108265', '翠子', '15016259399', '4f83fa01e926f50a3a483d73484e4613', '107941', '0.16', '2', '2018-12-19 20:31:56', null);
INSERT INTO `member` VALUES ('108266', '于小敏', '18710068846', '2094996ad34921fd71ca3562b1ab8a23', '107902', '0.00', '2', '2018-12-19 20:32:21', null);
INSERT INTO `member` VALUES ('108267', '丁奎琳', '13329897405', '8e310df5da170452d5fe1bca11f1e5cc', '107921', '0.00', '2', '2018-12-19 20:33:11', null);
INSERT INTO `member` VALUES ('108268', '谢远芬', '17783159811', 'b1175fb0f72172c43ab09af0a8a764ee', '108089', '14.90', '2', '2018-12-19 20:35:17', null);
INSERT INTO `member` VALUES ('108269', '文静', '18972116716', '3941b7a12b2b62449e65bec1ca2e8375', '10084', '0.00', '2', '2018-12-19 20:35:18', null);
INSERT INTO `member` VALUES ('108270', '刘永丽', '18778809069', '2beb412506a7fbdfe3d179b5e028a4cd', '10077', '0.00', '2', '2018-12-19 20:35:57', null);
INSERT INTO `member` VALUES ('108271', '彭春枝', '13890667819', 'b30eb6cd4e5233bf8ecd2fb3efc01d85', '108147', '0.00', '2', '2018-12-19 20:36:28', null);
INSERT INTO `member` VALUES ('108272', '葛立伟', '18833316726', '0204551ce55739849b5996ef383a19f5', '108025', '22.66', '2', '2018-12-19 20:36:35', null);
INSERT INTO `member` VALUES ('108273', '白淑燕', '15930076028', 'c46d84b42fc50a7633cfa26c4e4310ba', '107927', '0.00', '2', '2018-12-19 20:37:10', null);
INSERT INTO `member` VALUES ('108274', '苏会霞', '13938251906', '666129c99e83741a0b20b09351aafa95', '108101', '7.45', '2', '2018-12-19 20:37:15', null);
INSERT INTO `member` VALUES ('108275', '王', '15198922325', '9ac0dcab7b7a8d23416afc63ffc29e25', '108143', '0.00', '2', '2018-12-19 20:37:22', null);
INSERT INTO `member` VALUES ('108276', '孙巾雲', '13913611706', '0ad07e9d86536322e1529d4d03ec9615', '10163', '0.00', '2', '2018-12-19 20:37:23', null);
INSERT INTO `member` VALUES ('108277', '陈晓玲', '15064218860', '7d0b0ddcc48d4d12742f6b5b4b41a2ec', '107982', '0.00', '2', '2018-12-19 20:37:49', null);
INSERT INTO `member` VALUES ('108278', '陆杏丽', '13501002665', '6e74cd5ac72075cd6dc3b7305efab2f3', '1', '0.00', '2', '2018-12-19 20:38:11', null);
INSERT INTO `member` VALUES ('108279', '仁和', '15877949354', '332471ca723bd4f8ff7837ec1649146e', '108241', '0.00', '2', '2018-12-19 20:38:38', null);
INSERT INTO `member` VALUES ('108280', '阿当', '15969271949', '7f587eca6386321f07073c00be7e1224', '1', '0.00', '2', '2018-12-19 20:38:42', null);
INSERT INTO `member` VALUES ('108281', '司远俊', '13952465373', '58a6f5c068927089bb3a71933ee8fe5c', '1', '0.00', '2', '2018-12-19 20:38:50', null);
INSERT INTO `member` VALUES ('108282', '仁和', '13906361024', '80a4014c15d8d27995f3861c71dcfaf0', '107908', '3.49', '2', '2018-12-19 20:39:26', null);
INSERT INTO `member` VALUES ('108283', '莲子', '13087959673', '7a5bfe07077f521cb62d6735ffd44c15', '107952', '10.43', '2', '2018-12-19 20:40:05', null);
INSERT INTO `member` VALUES ('108284', '向美燕', '15874384782', '393752e8cee82ccffabc3f61b86ab5f8', '107908', '23.70', '2', '2018-12-19 20:41:04', null);
INSERT INTO `member` VALUES ('108285', '惠儿', '18677662668', '1314f8360eec5420e6613b6884a2747c', '107913', '0.00', '2', '2018-12-19 20:41:09', null);
INSERT INTO `member` VALUES ('108286', '邓显红', '13688351884', 'd5e187a3ae64bf1ecaef0a346d66d7d8', '1', '0.00', '2', '2018-12-19 20:41:42', null);
INSERT INTO `member` VALUES ('108287', '婷婷', '13893721264', '97227e9486fe72d516107d84ef2c2a6f', '107913', '0.00', '2', '2018-12-19 20:42:03', null);
INSERT INTO `member` VALUES ('108288', '林雪梅', '13480156323', '8b5ba4a5204a3f29e968acbd375ffded', '107937', '0.00', '2', '2018-12-19 20:42:19', null);
INSERT INTO `member` VALUES ('108289', '张张', '13987706840', '8b056bfd17e0d5ea80baabf71b916fcd', '107905', '1.49', '2', '2018-12-19 20:42:32', null);
INSERT INTO `member` VALUES ('108290', '徐世恒', '15183246736', 'a7e48f17f198ef4f90bae59a81366667', '10084', '0.00', '2', '2018-12-19 20:42:35', null);
INSERT INTO `member` VALUES ('108291', '李玉红', '15096898395', '70c5ca1a43f524f6795c4af48e22b367', '1', '0.00', '2', '2018-12-19 20:42:37', null);
INSERT INTO `member` VALUES ('108292', '李红坤', '15128220446', '9d85cd8bf53996df44dddb9a988da293', '108147', '0.00', '2', '2018-12-19 20:42:53', null);
INSERT INTO `member` VALUES ('108293', '何曼', '17733227026', '83cff2c3d0bd4f197f3c8e3573594845', '108147', '0.00', '2', '2018-12-19 20:43:01', null);
INSERT INTO `member` VALUES ('108294', '蒲雪梅', '13882424200', '0c4765eb68f4195e1f561e97b9ef8fb1', '10084', '0.00', '2', '2018-12-19 20:43:17', null);
INSERT INTO `member` VALUES ('108295', '徐进邻', '18214006306', '0488f20aaacc9e854823172937868608', '108214', '0.00', '2', '2018-12-19 20:43:17', null);
INSERT INTO `member` VALUES ('108296', '郭星', '15332256539', '34472eb1a7f6218577ca4d76efd75d35', '107927', '23.93', '2', '2018-12-19 20:43:37', null);
INSERT INTO `member` VALUES ('108297', '乔建中', '18790038661', '6e8633276325611e9163672d8b79bc92', '107929', '0.00', '2', '2018-12-19 20:43:47', null);
INSERT INTO `member` VALUES ('108298', '珍珍', '15099711242', 'f3e3055b75ec50bf428b394ba7c962b8', '107953', '14.70', '2', '2018-12-19 20:44:24', null);
INSERT INTO `member` VALUES ('108299', '果果', '13668187427', 'e34f99481284a0f3f256b265b9fc8c2d', '107990', '15.26', '2', '2018-12-19 20:44:27', null);
INSERT INTO `member` VALUES ('108300', '梅子', '13888143595', 'c03deac0be860daffc0319982894f911', '107905', '19.17', '2', '2018-12-19 20:44:36', null);
INSERT INTO `member` VALUES ('108301', '查春艳', '13987928643', 'b0a7071782cd7e7c7fe15b7b0a4b6e59', '107908', '89.66', '2', '2018-12-19 20:44:40', null);
INSERT INTO `member` VALUES ('108302', '黎春英', '18076565315', '0659c7992e268962384eb17fafe88364', '1', '0.00', '2', '2018-12-19 20:44:52', null);
INSERT INTO `member` VALUES ('108303', '陈菲', '15035561655', '7a035f450fc3fd742a1e736176cb5537', '108244', '0.00', '2', '2018-12-19 20:45:14', null);
INSERT INTO `member` VALUES ('108304', '美丽', '15887795070', '0e4b5f47d4cc46792baf125cd7b688ef', '107930', '0.00', '2', '2018-12-19 20:45:19', null);
INSERT INTO `member` VALUES ('108305', '黑宁丽', '15125975035', '9b01e5eb990dc82250845295269e5fe4', '107906', '0.85', '2', '2018-12-19 20:45:22', null);
INSERT INTO `member` VALUES ('108306', '玉姐', '13926869506', '223456cdee9f072400f19efc449e509d', '107905', '0.85', '2', '2018-12-19 20:45:55', null);
INSERT INTO `member` VALUES ('108307', '鲁雪琴', '18175790206', '8af28783dc8bad7934f18e85e32ed670', '108048', '0.00', '2', '2018-12-19 20:46:00', null);
INSERT INTO `member` VALUES ('108308', '张娜', '18739214281', '65a0ec385ca6a0c1e20d1f8270c28303', '107945', '0.00', '2', '2018-12-19 20:46:02', null);
INSERT INTO `member` VALUES ('108309', '刘双', '13541297706', 'eefa2d8666cfd8ab7c0e92a0cb4b75f6', '10084', '0.00', '2', '2018-12-19 20:46:29', null);
INSERT INTO `member` VALUES ('108310', '黄晓红', '15016798947', 'fbd2ed2874707ddd429dfeeaceba29cb', '1', '0.00', '2', '2018-12-19 20:46:30', null);
INSERT INTO `member` VALUES ('108311', '乐乐', '15828043457', 'd7cf5d3d8bcdeddfcd4d396dbf46ef16', '1', '0.00', '2', '2018-12-19 20:46:55', null);
INSERT INTO `member` VALUES ('108312', '魏微', '13722757263', '76142fd2d8219ff14388e43b6040dfba', '108152', '0.00', '2', '2018-12-19 20:47:29', null);
INSERT INTO `member` VALUES ('108313', '王圆', '13214603202', '38a2ac50bd5de11f55ffba0309bdb42f', '107906', '54.67', '2', '2018-12-19 20:47:50', null);
INSERT INTO `member` VALUES ('108314', '魏婷婷', '18899529809', 'aa2583c6e306308998bdfe195570bce9', '107957', '1.49', '2', '2018-12-19 20:48:39', null);
INSERT INTO `member` VALUES ('108315', '糖糖', '15883988595', '91a3904f2a31240ef2b18d66a83cda23', '108126', '8.94', '2', '2018-12-19 20:48:43', '2018-12-29 09:34:01');
INSERT INTO `member` VALUES ('108316', '刘彩霞', '13723325612', '91bf4612966475123a3ac752f867e1da', '108025', '0.00', '2', '2018-12-19 20:49:00', null);
INSERT INTO `member` VALUES ('108317', '孟傲雪', '13703933679', '19ffe529fa0602cb3fbbd9315698442f', '1', '0.00', '2', '2018-12-19 20:49:27', null);
INSERT INTO `member` VALUES ('108318', '苏国妮', '13934895759', '43afa96e4cc95a353a2989d82acc6aa2', '107938', '0.00', '2', '2018-12-19 20:49:34', null);
INSERT INTO `member` VALUES ('108319', '周燕', '18623548273', 'fc838903cc1f6e6873bc47bc93c83e22', '108209', '0.00', '2', '2018-12-19 20:49:45', null);
INSERT INTO `member` VALUES ('108320', '于东红', '13620742498', '29ba47197f23a96113bff653b539cc8c', '108209', '4.69', '2', '2018-12-19 20:49:55', null);
INSERT INTO `member` VALUES ('108321', '自云春', '15750262218', 'dc483e80a7a0bd9ef71d8cf973673924', '108009', '0.00', '2', '2018-12-19 20:50:08', null);
INSERT INTO `member` VALUES ('108322', '刘春秀', '18623685306', '73415b3a32a1e66e89d053d38a9a0193', '1', '0.00', '2', '2018-12-19 20:50:13', null);
INSERT INTO `member` VALUES ('108323', '季丹', '13655155304', 'd656bd307d3fd4b55b91d90f715a8f4b', '1', '0.00', '2', '2018-12-19 20:50:37', null);
INSERT INTO `member` VALUES ('108324', '郑云霞', '13588989825', '1c6d6851bfe0d610746e8401c54de7d6', '10690', '0.00', '2', '2018-12-19 20:50:46', null);
INSERT INTO `member` VALUES ('108325', '李莲', '18353009335', '2137ac8387db74124fa459079f0622f5', '1', '0.00', '2', '2018-12-19 20:50:49', null);
INSERT INTO `member` VALUES ('108326', '小燕', '14786678406', 'f86365938724b6c45f05ea5f73b2fa90', '107906', '11.92', '2', '2018-12-19 20:51:44', null);
INSERT INTO `member` VALUES ('108327', '马莹', '13153690618', '56d73ca5876ca830c671dc1561f12459', '107913', '1.49', '2', '2018-12-19 20:51:58', null);
INSERT INTO `member` VALUES ('108328', '胡海燕', '13980157253', '37c5970b0bb60cefbe155c1353f33f7b', '108126', '0.00', '2', '2018-12-19 20:52:22', null);
INSERT INTO `member` VALUES ('108329', '刘琳', '18971589738', 'fa64951f830f38893e715e5cfef2623b', '107905', '78.88', '2', '2018-12-19 20:53:11', null);
INSERT INTO `member` VALUES ('108330', '美娜', '13876440853', 'e5c11cb54997f12e80e381c690afe287', '1', '0.00', '2', '2018-12-19 20:53:15', null);
INSERT INTO `member` VALUES ('108331', '梁传宇', '18672150862', '0a4211397bf6e1ca77b19a395f53c298', '107953', '0.00', '2', '2018-12-19 20:53:21', null);
INSERT INTO `member` VALUES ('108332', '洪婉晴', '15348895525', '3132b0fc6c050e61fb6206ffe05452be', '108209', '6.40', '2', '2018-12-19 20:53:30', null);
INSERT INTO `member` VALUES ('108333', '张玲玲', '15036119026', 'ad324eb585496327017b46f1b1d526de', '108101', '7.45', '2', '2018-12-19 20:53:30', null);
INSERT INTO `member` VALUES ('108334', '林博', '13581671096', '186c94a8ae53e9ce9b8bbb31a4a97b34', '10690', '0.00', '2', '2018-12-19 20:53:37', null);
INSERT INTO `member` VALUES ('108335', '李蓉', '15877426213', 'be4eda0158e1b7b0c7bb8ae1d1b2b665', '107905', '0.50', '2', '2018-12-19 20:53:41', null);
INSERT INTO `member` VALUES ('108336', '周兴嵘', '15587022923', 'e80669511660375dcd66641b35a3ae38', '107956', '0.70', '2', '2018-12-19 20:53:56', null);
INSERT INTO `member` VALUES ('108337', '李志明', '13920738980', '1c8a62baccba0a5bc5ac384e930fb35f', '107933', '0.00', '2', '2018-12-19 20:54:00', null);
INSERT INTO `member` VALUES ('108338', '陈春华', '18087916435', '293cd1681df218646b2e604362b4f758', '108005', '0.00', '2', '2018-12-19 20:54:05', null);
INSERT INTO `member` VALUES ('108339', '蔡小兰', '17750518820', '519975a0f8e4105378d7cf366b1bda88', '108209', '0.00', '2', '2018-12-19 20:54:32', null);
INSERT INTO `member` VALUES ('108340', '杨珺', '15882150285', '1fd88d3ff084883fade1f3eb845ea95a', '107990', '1.49', '2', '2018-12-19 20:55:26', null);
INSERT INTO `member` VALUES ('108341', '杜姐', '13878809779', 'fec4e58a1dcbda55c9b505113eae90d2', '107908', '0.00', '2', '2018-12-19 20:55:31', null);
INSERT INTO `member` VALUES ('108342', '冯娟', '13121512672', '6eaa9109eebbd514d7a3b4dc3d8830c2', '107922', '50.45', '2', '2018-12-19 20:55:50', '2018-12-23 09:21:20');
INSERT INTO `member` VALUES ('108343', '潘艳琼', '15878921848', '7a000cd760b033ce9c3459c04c4ab5e3', '1', '0.00', '2', '2018-12-19 20:56:05', null);
INSERT INTO `member` VALUES ('108344', '艾草膏', '17354126567', '80a4014c15d8d27995f3861c71dcfaf0', '108282', '7.45', '2', '2018-12-19 20:56:31', '2018-12-22 10:15:02');
INSERT INTO `member` VALUES ('108345', '如初', '15267133292', '84ec3e58b09d7b5a9b3e472d5705a8ca', '10416', '3.90', '2', '2018-12-19 20:56:45', null);
INSERT INTO `member` VALUES ('108346', '高玉霞', '13939032236', 'ec2d752ed0d0652254087040bced0247', '108274', '0.00', '2', '2018-12-19 20:56:53', null);
INSERT INTO `member` VALUES ('108347', '崔小龙', '18632262494', '6dfbeda16361f2262fca3bf1534be403', '108147', '0.00', '2', '2018-12-19 20:56:55', null);
INSERT INTO `member` VALUES ('108348', '邹信文', '13544185955', '15d5f0afbc2ab76b754cab82162f3496', '107992', '0.70', '2', '2018-12-19 20:57:57', null);
INSERT INTO `member` VALUES ('108349', '笋尖儿', '13811938657', '52001ddd9040be088f0a544deed65930', '10690', '25.19', '2', '2018-12-19 20:58:00', null);
INSERT INTO `member` VALUES ('108350', '邓芬', '15974802352', 'b5b341d6872b4cde1640e891d0c88891', '107973', '0.00', '2', '2018-12-19 20:58:45', null);
INSERT INTO `member` VALUES ('108351', '亲爱的丽丽', '18108533454', 'f4ff5ba893399b786eded6265dd21f77', '10086', '2.98', '2', '2018-12-19 20:59:11', null);
INSERT INTO `member` VALUES ('108352', '星夜', '13666715237', '46ee32e18cdf3b8bb425e5f7d00a5788', '1', '0.00', '2', '2018-12-19 20:59:13', null);
INSERT INTO `member` VALUES ('108353', '孙英立', '13343236988', '290610d49f1c9b549822a3ff339d3b60', '10077', '0.00', '2', '2018-12-19 20:59:28', null);
INSERT INTO `member` VALUES ('108354', '陈灵', '15759312957', '3e9c76eb8804d2025e0a93a9b6fcb619', '10109', '0.00', '2', '2018-12-19 20:59:48', null);
INSERT INTO `member` VALUES ('108355', '黄体坚', '18269513889', '8503fbd26101b1fb4e161ddc07d1191a', '1', '0.00', '2', '2018-12-19 20:59:53', null);
INSERT INTO `member` VALUES ('108356', '梅子', '18276177500', 'ccfb12e217058eeacc2f483fa29b6194', '107906', '8.94', '2', '2018-12-19 21:00:48', null);
INSERT INTO `member` VALUES ('108357', '彭光辉', '15933684321', 'dc483e80a7a0bd9ef71d8cf973673924', '1', '0.00', '2', '2018-12-19 21:00:49', null);
INSERT INTO `member` VALUES ('108358', '殷梅', '18920430227', 'ea92d32c88ba5b56e8722e78a0f954c9', '1', '0.00', '2', '2018-12-19 21:01:08', null);
INSERT INTO `member` VALUES ('108359', '吴鑫红', '18370723332', 'e1c569ac9191e309fe9800292e00984b', '108152', '0.00', '2', '2018-12-19 21:01:15', null);
INSERT INTO `member` VALUES ('108360', '红红', '15354242005', '8a1148a74ba479fcaca5e34f5de73d45', '10284', '0.00', '2', '2018-12-19 21:02:09', null);
INSERT INTO `member` VALUES ('108361', '雅琳', '15234060210', '3d667876c485b08332939c5da2502022', '107906', '125.69', '2', '2018-12-19 21:02:13', null);
INSERT INTO `member` VALUES ('108362', '健康与美', '13845936663', '0618631c8c9d8b3f83cd03a5292110ca', '107922', '0.40', '2', '2018-12-19 21:02:31', null);
INSERT INTO `member` VALUES ('108363', '芳芳', '13888284737', '175f75888347420b0b6e5351de7b4308', '108300', '0.00', '2', '2018-12-19 21:02:52', null);
INSERT INTO `member` VALUES ('108364', '李小艳', '18087808244', 'fb8175cbc13c447f4cba8a6cf31157af', '107984', '0.00', '2', '2018-12-19 21:03:22', null);
INSERT INTO `member` VALUES ('108365', '蓝春柳', '15577192385', '47f064a4da8211de4470bea2b51ba452', '1', '0.00', '2', '2018-12-19 21:04:04', null);
INSERT INTO `member` VALUES ('108366', '张扬', '15130558461', '38ec50553a426fe47efe7d66d1b1b373', '1', '0.00', '2', '2018-12-19 21:04:16', null);
INSERT INTO `member` VALUES ('108367', '刘乐', '13739647228', 'fe6d27740d4ea7eff079d8ae89c30783', '107983', '2.97', '2', '2018-12-19 21:04:17', null);
INSERT INTO `member` VALUES ('108368', '年小娟', '18009307740', '5c0fd01bf26c22966d16e6311954cbed', '109013', '0.00', '2', '2018-12-19 21:04:47', null);
INSERT INTO `member` VALUES ('108369', '刘思霞', '15664310921', 'bd05b66239db5f37cb83ae83d5c7a713', '1', '0.00', '2', '2018-12-19 21:05:21', null);
INSERT INTO `member` VALUES ('108370', '蒋春霞', '17760865566', 'bf9ac4f4bd7a9688036fa89bea31946c', '1', '0.00', '2', '2018-12-19 21:05:27', null);
INSERT INTO `member` VALUES ('108371', '江庆萍', '13977465131', '0d188e86b4a825142ca904bca699d153', '108209', '2.98', '2', '2018-12-19 21:05:29', null);
INSERT INTO `member` VALUES ('108372', '李文现', '13232570892', '663cb3579d1b4ce0701e315adc759eb7', '108030', '0.00', '2', '2018-12-19 21:05:43', null);
INSERT INTO `member` VALUES ('108373', '安然', '13540513487', '637051ba8f60014a3aa0d3f25cc90a5d', '108172', '0.00', '2', '2018-12-19 21:05:58', null);
INSERT INTO `member` VALUES ('108374', '桃花', '15193221780', 'e560bcd3a8a483089a3177d957aeef06', '107952', '0.00', '2', '2018-12-19 21:05:59', null);
INSERT INTO `member` VALUES ('108375', '江江', '17736679225', '390106b5a0812c74a561144037191b21', '108148', '0.00', '2', '2018-12-19 21:06:35', null);
INSERT INTO `member` VALUES ('108376', '刘辅祝', '15085290261', 'a0222d70fe09c2a5f6b3e917c691a186', '107905', '40.78', '2', '2018-12-19 21:06:36', null);
INSERT INTO `member` VALUES ('108377', '闫星', '18700983941', 'e6e7a261315425d1b5d1eb85e6459a6b', '108152', '0.19', '2', '2018-12-19 21:06:45', null);
INSERT INTO `member` VALUES ('108378', '张议之', '13934146415', 'c6c551e96d3b6047a2d454fa43f3c6a0', '109901', '1.49', '2', '2018-12-19 21:06:47', null);
INSERT INTO `member` VALUES ('108379', '永', '13381433676', 'e44ec0d93141fcb09df5affcd4098fc1', '10690', '0.00', '2', '2018-12-19 21:06:47', null);
INSERT INTO `member` VALUES ('108380', '王洪秀', '15230977202', 'bb846622271be07ddfc4c13c354231ea', '108025', '0.00', '2', '2018-12-19 21:07:29', null);
INSERT INTO `member` VALUES ('108381', '张燕红', '13410436802', '4843a3eeaf6d982e5fc27518c0c7a81c', '107968', '1.49', '2', '2018-12-19 21:07:47', null);
INSERT INTO `member` VALUES ('108382', '邓小春', '17702644779', '9d20d16f1f5a968bf892127785c66746', '108152', '8.91', '2', '2018-12-19 21:08:05', null);
INSERT INTO `member` VALUES ('108383', '王红英', '15982027658', '15f828bfbd41894790092154608ff0db', '1', '0.00', '2', '2018-12-19 21:08:52', null);
INSERT INTO `member` VALUES ('108384', '香水有毒', '15181251380', '1a5b6ebddd7d8d63bb2a04fc44280c9d', '107952', '0.00', '2', '2018-12-19 21:09:24', null);
INSERT INTO `member` VALUES ('108385', '余小芳', '18770378046', 'a5cf2a6b3037268de6dbc51a217340d8', '108099', '0.00', '2', '2018-12-19 21:10:33', null);
INSERT INTO `member` VALUES ('108386', '刘小渝', '13452473840', 'cd0228aabe904a95a190d156120fe40d', '107983', '0.00', '2', '2018-12-19 21:11:42', null);
INSERT INTO `member` VALUES ('108387', '沈莹', '13948291260', '2f67ff400b0e91f65a091a0c20aea4ca', '1', '0.00', '2', '2018-12-19 21:12:03', null);
INSERT INTO `member` VALUES ('108388', '贾益', '18386188921', 'b3dcf085785ce354ee8630ab8ae5f29b', '108152', '75.66', '2', '2018-12-19 21:12:22', null);
INSERT INTO `member` VALUES ('108389', '陈佳', '15067795275', '468bb8b33eabb60205b1d9ab2267530c', '107983', '0.00', '2', '2018-12-19 21:12:53', null);
INSERT INTO `member` VALUES ('108390', '赵燕霞', '18693224305', '8403669c982c39957c9241499ce7b72e', '107952', '4.48', '2', '2018-12-19 21:13:03', null);
INSERT INTO `member` VALUES ('108391', '高娓娓', '18638903477', '86bd1cf0cd7cf86d0a30829b24f7e287', '107929', '0.00', '2', '2018-12-19 21:13:38', null);
INSERT INTO `member` VALUES ('108392', '玉香恩', '18206937752', '3d69512e39324eaedbc8b924eb1c4343', '107905', '15.20', '2', '2018-12-19 21:13:38', null);
INSERT INTO `member` VALUES ('108393', '英', '13460634955', 'c8d3bbc5e9f2c4c275efc3203df2a1c4', '10109', '0.00', '2', '2018-12-19 21:13:53', null);
INSERT INTO `member` VALUES ('108394', '豌豆妈', '13579968867', '646aca1de9d92291a95544d7ffaa93d2', '1', '0.00', '2', '2018-12-19 21:13:59', null);
INSERT INTO `member` VALUES ('108395', '张金影', '13664479616', '9cbf8a4dcb8e30682b927f352d6559a0', '10109', '0.00', '2', '2018-12-19 21:14:09', null);
INSERT INTO `member` VALUES ('108396', '唐昌道', '13787522639', 'e0dcef47c8e8ce11db228e414b7624ec', '10086', '0.00', '2', '2018-12-19 21:14:18', null);
INSERT INTO `member` VALUES ('108397', '蝴蝶', '13241395667', '424d2334031dc9501405fd64df8f471d', '1', '0.00', '2', '2018-12-19 21:14:20', null);
INSERT INTO `member` VALUES ('108398', '黄运燕', '13667872562', 'dc483e80a7a0bd9ef71d8cf973673924', '108169', '0.00', '2', '2018-12-19 21:14:21', null);
INSERT INTO `member` VALUES ('108399', '杨忠志', '18287921806', '0d7ecc4634e0d9d6c903fc0f6f473992', '108005', '0.00', '2', '2018-12-19 21:14:48', null);
INSERT INTO `member` VALUES ('108400', '杨香', '18585524488', '2e9ce1cadd9906245d7797de9fd59a25', '1', '0.00', '2', '2018-12-19 21:16:53', null);
INSERT INTO `member` VALUES ('108401', '张爱华', '13228873008', '0a98de6f191c5dc99cc7613702bf9e01', '108240', '0.00', '2', '2018-12-19 21:17:08', null);
INSERT INTO `member` VALUES ('108402', '我', '17688480115', '6bd2b5193d47bab575647b8560edc39c', '107992', '0.00', '2', '2018-12-19 21:17:26', null);
INSERT INTO `member` VALUES ('108403', '李雪', '13614137073', '1f341f58bb449c7e12d254595c73debb', '107952', '0.00', '2', '2018-12-19 21:17:37', null);
INSERT INTO `member` VALUES ('108404', '王晓月', '15184257234', '0cbf5c6b6bde4e73426c195ce8049fb5', '107985', '0.00', '2', '2018-12-19 21:17:51', null);
INSERT INTO `member` VALUES ('108405', '黄英', '14786886858', '94d1163dffb1b02ae12b4ca06ffd33d9', '10416', '0.70', '2', '2018-12-19 21:18:26', null);
INSERT INTO `member` VALUES ('108406', '王妍', '13911438994', '8ebb980d1658713ac189944c93fee69d', '10690', '0.00', '2', '2018-12-19 21:18:53', null);
INSERT INTO `member` VALUES ('108407', '臭美的丫丫', '15924050044', 'b3f0243147103cff2bc4667547adf373', '107906', '12.48', '2', '2018-12-19 21:19:00', '2018-12-22 14:48:13');
INSERT INTO `member` VALUES ('108408', '陆起杏', '18894778560', 'd3853ebfd6f8538788af71e7332790c3', '108209', '0.00', '2', '2018-12-19 21:19:21', null);
INSERT INTO `member` VALUES ('108409', '谢多萍', '15773500715', '5bc5f4437029744ef569e551dac56259', '107981', '0.00', '2', '2018-12-19 21:19:33', null);
INSERT INTO `member` VALUES ('108410', '娄蒙蒙', '13393824818', 'a1935499bc5404b0ed153b42fb16d59d', '10109', '0.00', '2', '2018-12-19 21:19:59', null);
INSERT INTO `member` VALUES ('108411', '氵蒙花稳氵', '15777870231', '65a0ec385ca6a0c1e20d1f8270c28303', '107948', '19.17', '2', '2018-12-19 21:20:10', null);
INSERT INTO `member` VALUES ('108412', '王庆', '13678138767', '3d24b838770ee90773804e8599e549ff', '108107', '0.00', '2', '2018-12-19 21:21:43', null);
INSERT INTO `member` VALUES ('108413', '黄春华', '18070558689', '2bd76dc1ab3009465990bf73b90ec94a', '107952', '0.00', '2', '2018-12-19 21:21:49', null);
INSERT INTO `member` VALUES ('108414', '风信子', '13577770137', '2b51dd4224636da430c501dafa99625e', '107915', '2.98', '2', '2018-12-19 21:21:49', null);
INSERT INTO `member` VALUES ('108415', '石蒙蒙', '18639192733', 'bf87fe4ea1ec207fd52f5949a11b1468', '107929', '0.00', '2', '2018-12-19 21:21:53', null);
INSERT INTO `member` VALUES ('108416', '王彦', '13810827394', '9deea77c55e2617cb0ec89b1516e35c0', '108349', '0.00', '2', '2018-12-19 21:22:50', null);
INSERT INTO `member` VALUES ('108417', '钟荣芬', '18213278375', '08508e9d543bf8fb0c454dc4fa0fc846', '1', '0.00', '2', '2018-12-19 21:22:50', null);
INSERT INTO `member` VALUES ('108418', '刘晓梅', '13880094566', '3c7280e311b55015bf2ea8d0f4acdb64', '1', '0.00', '2', '2018-12-19 21:22:59', null);
INSERT INTO `member` VALUES ('108419', '姣英', '15911515928', '984cefd6d27eb0471fc401a493a4fdff', '108344', '0.00', '2', '2018-12-19 21:22:59', null);
INSERT INTO `member` VALUES ('108420', '瑶瑶', '13126116693', '3d819a1f69112883793f1c59240a50fb', '108025', '1.49', '2', '2018-12-19 21:23:09', null);
INSERT INTO `member` VALUES ('108421', '李春梅', '13560120780', '65f9d23a920ecfa7327c36338a53ee1c', '107913', '0.00', '2', '2018-12-19 21:23:10', null);
INSERT INTO `member` VALUES ('108422', '小玉', '18823411055', '64e13704b5476c1c92d6ce57fe89a771', '108382', '0.00', '2', '2018-12-19 21:23:20', null);
INSERT INTO `member` VALUES ('108423', '吴悦', '13971060761', '250f5f0c7d4bfa48dfe1e23c1cc56802', '107953', '0.00', '2', '2018-12-19 21:23:42', null);
INSERT INTO `member` VALUES ('108424', '蒙佳音', '15777875165', 'a1c15446dc7d2ed77bdaf7ebb53d3634', '107948', '0.00', '2', '2018-12-19 21:23:50', null);
INSERT INTO `member` VALUES ('108425', '史海连', '18235352458', 'c4a9f3b2c28e3da2c7006be2c6d74bd3', '107933', '0.00', '2', '2018-12-19 21:24:00', null);
INSERT INTO `member` VALUES ('108426', '刘武梅', '13283107622', 'd3af2eb5a625f229b229ef87e9e94221', '107905', '0.21', '2', '2018-12-19 21:24:03', null);
INSERT INTO `member` VALUES ('108427', '周伟', '13598444009', '666129c99e83741a0b20b09351aafa95', '108274', '0.00', '2', '2018-12-19 21:25:24', null);
INSERT INTO `member` VALUES ('108428', '李琳', '18633124356', 'fbe9166b1020e0dd03b074224460a227', '108025', '0.00', '2', '2018-12-19 21:25:53', null);
INSERT INTO `member` VALUES ('108429', '絮儿', '13060379343', '2c9d7abfd71c7c7006d00b6014ba6876', '10050', '2.98', '2', '2018-12-19 21:25:54', null);
INSERT INTO `member` VALUES ('108430', '韦晏', '15078608556', 'f3cf7a309d8e263158a191a22e16ed22', '1', '0.00', '2', '2018-12-19 21:26:30', null);
INSERT INTO `member` VALUES ('108431', '张建华', '15883966469', '8db31cc73aae87d2545e51b53799fda0', '108126', '0.00', '2', '2018-12-19 21:26:33', null);
INSERT INTO `member` VALUES ('108432', '黄榆芳', '18176875195', '75beecf75928012378f8562e158a3204', '107913', '0.00', '2', '2018-12-19 21:26:36', null);
INSERT INTO `member` VALUES ('108433', '锦娘', '18587568820', '79855eaae3aa1b53666df5b80220d28f', '107944', '0.00', '2', '2018-12-19 21:26:46', null);
INSERT INTO `member` VALUES ('108434', '付晓勤', '18029609035', 'f5e05a41724115d076bfb1fd2bd9613e', '10084', '102.73', '2', '2018-12-19 21:27:14', null);
INSERT INTO `member` VALUES ('108435', '邸立勋', '13473426031', 'ad5bcfff6743255df9588e9782293ae3', '108147', '0.00', '2', '2018-12-19 21:27:23', null);
INSERT INTO `member` VALUES ('108436', '付甜甜', '15981909351', '5c31ac5f6f0acd98254bf2f157da8448', '1', '0.00', '2', '2018-12-19 21:27:26', null);
INSERT INTO `member` VALUES ('108437', '希奇', '13367855191', 'f1f21e37a30001e4d7bd7dca99123da9', '108283', '0.00', '2', '2018-12-19 21:27:44', null);
INSERT INTO `member` VALUES ('108438', '李方莲', '15243582499', '6e67c1f0d73c42c1b12275b917404889', '1', '0.00', '2', '2018-12-19 21:28:11', null);
INSERT INTO `member` VALUES ('108439', '苏惠玲', '18937189136', 'dc8beeb2b80df4c5ff851d99bd2395ca', '1', '0.00', '2', '2018-12-19 21:29:11', null);
INSERT INTO `member` VALUES ('108440', '阿龍', '15837850474', '99183287f3b1b5d185356cf2b7bfc36d', '1', '0.00', '2', '2018-12-19 21:29:40', null);
INSERT INTO `member` VALUES ('108441', '珍珠', '13185863174', '37244685674d622ac9751a2ca44ceb43', '1', '0.00', '2', '2018-12-19 21:30:01', null);
INSERT INTO `member` VALUES ('108442', '许多多', '15874871907', '03746029226fa394033ecea21dc49ac9', '107908', '0.00', '2', '2018-12-19 21:30:46', null);
INSERT INTO `member` VALUES ('108443', '郭蓉华', '18195598615', 'b5d14894840ee781b62ddb397d01a023', '107906', '0.00', '2', '2018-12-19 21:30:53', null);
INSERT INTO `member` VALUES ('108444', '赵彦霞', '13462170519', '62bd77187e8a6f972a9ac67ed734efae', '10109', '0.00', '2', '2018-12-19 21:33:04', null);
INSERT INTO `member` VALUES ('108445', '张雁', '13808272293', '62bd77187e8a6f972a9ac67ed734efae', '108183', '3.84', '2', '2018-12-19 21:33:12', null);
INSERT INTO `member` VALUES ('108446', '王娅', '15095788663', 'f9df5a894f2bff7d035433bba18481b1', '108390', '1.49', '2', '2018-12-19 21:33:15', null);
INSERT INTO `member` VALUES ('108447', '徐双娟', '13429789780', '0b823c06fc643a88ad65d1ca6f38c97b', '108190', '0.00', '2', '2018-12-19 21:33:30', null);
INSERT INTO `member` VALUES ('108448', '媛媛', '15132539976', '7733a2dec221a1c65ccf9947b8f4b6a9', '108025', '0.00', '2', '2018-12-19 21:33:45', null);
INSERT INTO `member` VALUES ('108449', '赵江蒙', '15135930059', 'e6da73dd3cd32ecb081de3a387c5f53f', '107938', '0.00', '2', '2018-12-19 21:34:14', null);
INSERT INTO `member` VALUES ('108450', '詹晓瑞', '13268949162', 'cdbd25aea6541c1cd12a625aa0cc72dd', '107952', '59.55', '2', '2018-12-19 21:34:27', null);
INSERT INTO `member` VALUES ('108451', '何真红', '18685923098', 'aec60231d83fe6cf81444bc536596887', '10073', '0.00', '2', '2018-12-19 21:34:28', null);
INSERT INTO `member` VALUES ('108452', '李灵芝', '18175518874', 'c3c6c91d8232ae31c1c46f781856067e', '107981', '0.00', '2', '2018-12-19 21:35:47', null);
INSERT INTO `member` VALUES ('108453', '冯弟', '13471834959', '9a93b07ec081eed262c07367117173d8', '107948', '0.00', '2', '2018-12-19 21:36:27', null);
INSERT INTO `member` VALUES ('108454', '吴贤侠', '18807879851', '580ab7aa4d7101e45417eb09046effd1', '107906', '18.37', '2', '2018-12-19 21:37:02', '2019-01-03 10:19:36');
INSERT INTO `member` VALUES ('108455', '焦正春', '18975591761', '36c4fbc148b0513fa9e0b5877917f1a9', '107981', '0.00', '2', '2018-12-19 21:37:03', null);
INSERT INTO `member` VALUES ('108456', '张柏益', '13566600555', 'd83488e5a80fcdad009cc65e4b522618', '108135', '0.00', '2', '2018-12-19 21:37:29', null);
INSERT INTO `member` VALUES ('108457', '李林思', '17308812616', 'd8b054a7da423446a57656dfe87110c6', '107931', '0.00', '2', '2018-12-19 21:38:36', null);
INSERT INTO `member` VALUES ('108458', '陶恩彩', '13161549240', '1ac8ff5d04289264d8e32e6023dc3667', '1', '0.00', '2', '2018-12-19 21:38:50', null);
INSERT INTO `member` VALUES ('108459', '傅桂苹', '13866713090', 'fc24a2e1bc5f8c6ba617724d89408184', '108047', '0.00', '2', '2018-12-19 21:38:58', null);
INSERT INTO `member` VALUES ('108460', '何金菊', '15277934725', '8b83b205190d028913d27486709e6f5c', '107953', '1.49', '2', '2018-12-19 21:39:09', null);
INSERT INTO `member` VALUES ('108461', '姚先薇', '13018100965', '41fd1c837058de49fe069f9f3f24e700', '107952', '0.00', '2', '2018-12-19 21:39:34', null);
INSERT INTO `member` VALUES ('108462', '魏红红', '13893903576', '79134ce0f1a9e9bd9e36d9304a13aed9', '108314', '0.00', '2', '2018-12-19 21:39:34', null);
INSERT INTO `member` VALUES ('108463', '李美英', '13769901940', '31bca92e6bb5ce9ffd5e5adee527bc5c', '107984', '0.00', '2', '2018-12-19 21:39:58', null);
INSERT INTO `member` VALUES ('108464', '赵蕊', '13466052706', '3d4284cc12dc929d91dad6dcc0124671', '107973', '0.00', '2', '2018-12-19 21:40:23', null);
INSERT INTO `member` VALUES ('108465', '有玲', '13878717566', '268202076d43779d63f7e99a0ad78948', '107956', '0.00', '2', '2018-12-19 21:40:48', null);
INSERT INTO `member` VALUES ('108466', '章海燕', '15051232853', 'f2cc40c21c018613b983007bec4607c0', '10163', '0.00', '2', '2018-12-19 21:40:55', null);
INSERT INTO `member` VALUES ('108467', '王磊', '18295797627', '6a89af7ae399e8304c575ff3f5b0e3bc', '107938', '0.00', '2', '2018-12-19 21:41:07', null);
INSERT INTO `member` VALUES ('108468', '李文英', '18788564626', '62d82302d3e5e420a074a7d4798d114c', '107984', '0.00', '2', '2018-12-19 21:41:17', null);
INSERT INTO `member` VALUES ('108469', '小林', '18200251955', 'a267962c89fa70345908b2be32c8166c', '107990', '1.49', '2', '2018-12-19 21:41:22', null);
INSERT INTO `member` VALUES ('108470', '方丽', '13987743623', '2effd17dfdd108f5f60df15129f2ae54', '1', '0.00', '2', '2018-12-19 21:41:32', null);
INSERT INTO `member` VALUES ('108471', '胡华蓉', '13908201840', 'fafa7f15fccb638bc4388e4f61b0c64f', '107923', '0.00', '2', '2018-12-19 21:44:15', null);
INSERT INTO `member` VALUES ('108472', '徐春蕾', '18210268909', '241d995b87fbb6b3ba5c609938d8cd1c', '108175', '13.30', '2', '2018-12-19 21:44:28', '2018-12-23 09:19:28');
INSERT INTO `member` VALUES ('108473', '刘嘉敏', '15762635859', 'a3aa22bc3467e8eaee237f64a121275d', '108158', '48.48', '2', '2018-12-19 21:45:31', null);
INSERT INTO `member` VALUES ('108474', '邢秀民', '15811293249', '46ab902a1cae9fddbb3b1eb98c74617b', '108175', '4.18', '2', '2018-12-19 21:45:41', '2018-12-23 09:19:48');
INSERT INTO `member` VALUES ('108475', '李沙', '15073583137', '003be5f822f42160a97bcd0abfaa499d', '107981', '1.49', '2', '2018-12-19 21:45:43', null);
INSERT INTO `member` VALUES ('108476', '娟子', '18700705612', 'dc483e80a7a0bd9ef71d8cf973673924', '1', '0.00', '2', '2018-12-19 21:45:59', null);
INSERT INTO `member` VALUES ('108477', '红柑', '13975592728', '9d852a766a56de916f588caa6da4e9da', '107981', '0.00', '2', '2018-12-19 21:46:42', null);
INSERT INTO `member` VALUES ('108478', '汪小琴', '13508438798', '8e365396411dd0dd66cfc36a2bb1b8e8', '10417', '0.00', '2', '2018-12-19 21:47:21', '2019-01-18 13:45:52');
INSERT INTO `member` VALUES ('108479', '莲榕', '18376737392', '68caad6431f7d8e2e21eb05d9e2f6ed6', '107906', '1.49', '2', '2018-12-19 21:47:53', null);
INSERT INTO `member` VALUES ('108480', '婷婷', '18172466852', '9d79da3c7930008dfca7aee2250cf73b', '1', '0.00', '2', '2018-12-19 21:48:17', null);
INSERT INTO `member` VALUES ('108481', '于伊儿', '13521106511', 'd4678cd4dbeda45e0adc6d4b4da687d7', '1', '0.00', '2', '2018-12-19 21:48:22', null);
INSERT INTO `member` VALUES ('108482', '章玉进', '15896022570', '689a1664ffab12d368b374d7e2f9badc', '10163', '0.00', '2', '2018-12-19 21:49:08', null);
INSERT INTO `member` VALUES ('108483', '王斌', '18901168028', '3d24b838770ee90773804e8599e549ff', '108175', '63.84', '2', '2018-12-19 21:49:09', '2018-12-23 09:16:29');
INSERT INTO `member` VALUES ('108484', '禹禹', '18806950562', 'b0e79dce5fefc08561c3875ae749ee3a', '107919', '0.37', '2', '2018-12-19 21:49:17', null);
INSERT INTO `member` VALUES ('108485', '杨梅', '18314180390', '8215b9b6858e8142719419f948411292', '107919', '0.00', '2', '2018-12-19 21:49:37', null);
INSERT INTO `member` VALUES ('108486', '林敏', '18477462968', 'e833be3cd6b92e5f609ad9b4c32fad23', '107980', '0.00', '2', '2018-12-19 21:49:39', null);
INSERT INTO `member` VALUES ('108487', '何许梅子', '18287996781', 'd80c90cf74d855543074c5f861e70ee5', '107958', '1.49', '2', '2018-12-19 21:50:13', null);
INSERT INTO `member` VALUES ('108488', '冯珍', '13367605833', 'd3293cfbfa4258933b64fdfa739c5775', '107980', '0.00', '2', '2018-12-19 21:50:20', null);
INSERT INTO `member` VALUES ('108489', '孙桂桐', '13253228116', '7e5fd2db4567901ff636bcec05cfe5c7', '108175', '0.00', '2', '2018-12-19 21:50:21', null);
INSERT INTO `member` VALUES ('108490', '陈小华', '13421556859', 'a21c0359110ea36ea312a54bdc764fae', '108141', '0.00', '2', '2018-12-19 21:51:07', null);
INSERT INTO `member` VALUES ('108491', '晴妹儿', '15175538674', '689a019bdd1b84431429f6ca9b321be7', '1', '0.00', '2', '2018-12-19 21:51:26', null);
INSERT INTO `member` VALUES ('108492', '芳', '15087446654', 'a27cbe0ce0e49d1d9d7cac352cb36439', '107934', '0.00', '2', '2018-12-19 21:51:44', null);
INSERT INTO `member` VALUES ('108493', '明哥', '13712603037', '6e40eac2cfe0123a0a6b7ece9e00bd22', '10086', '0.00', '2', '2018-12-19 21:51:48', null);
INSERT INTO `member` VALUES ('108494', '杨茜', '13133905986', 'cf50f310a2e15e1f4bf131d9f3376505', '107968', '0.00', '2', '2018-12-19 21:51:53', null);
INSERT INTO `member` VALUES ('108495', '熊艳玲', '13505902385', 'a6f7e723d903f174be511332bc33b2c1', '108152', '1.49', '2', '2018-12-19 21:52:17', null);
INSERT INTO `member` VALUES ('108496', '刘春江', '13611026961', 'fa5f11a14fa17c6a5876a845a2bcee51', '108175', '16.64', '2', '2018-12-19 21:52:39', null);
INSERT INTO `member` VALUES ('108497', '周树琪', '13571647106', '45191a0e8cecf004326af73205d18326', '108190', '0.00', '2', '2018-12-19 21:52:58', null);
INSERT INTO `member` VALUES ('108498', '春天', '18782902115', '637051ba8f60014a3aa0d3f25cc90a5d', '108172', '0.00', '2', '2018-12-19 21:53:31', null);
INSERT INTO `member` VALUES ('108499', '施志平', '13987210937', '08285137b942e58d07229df1caf93668', '107905', '1.49', '2', '2018-12-19 21:53:46', null);
INSERT INTO `member` VALUES ('108500', '阮灵莺', '13559607274', '0702c8175b6cc3fcd2fce9e137aa328f', '1', '0.00', '2', '2018-12-19 21:53:49', null);
INSERT INTO `member` VALUES ('108501', '晨晨', '15118991998', '3fe04b293a601c0f48bce149cb4e95f3', '1', '0.00', '2', '2018-12-19 21:54:14', null);
INSERT INTO `member` VALUES ('108502', '汪海霞', '15897596642', 'ed26c5e26c4a1d66dd48d95d6fd63326', '1', '0.00', '2', '2018-12-19 21:55:14', null);
INSERT INTO `member` VALUES ('108503', '叁', '18213378359', 'c099d609f179e906becb8db041f08b23', '1', '0.00', '2', '2018-12-19 21:56:05', null);
INSERT INTO `member` VALUES ('108504', '李文', '13541788363', '0eae247ea1098d3f8d693636cfa083cc', '10084', '0.00', '2', '2018-12-19 21:56:12', null);
INSERT INTO `member` VALUES ('108505', '曹丽平', '15887411897', '0b7f90c4be91981262a3f3ddcc99c9d3', '1', '0.00', '2', '2018-12-19 21:56:41', null);
INSERT INTO `member` VALUES ('108506', '张一香', '13501100751', '5bf84485beb7048708c95f499757454c', '108175', '9.43', '2', '2018-12-19 21:59:35', '2018-12-23 09:12:21');
INSERT INTO `member` VALUES ('108507', '小曾', '18076248805', '11b1a1f48b114398756caad27126f86f', '1', '0.00', '2', '2018-12-19 21:59:36', null);
INSERT INTO `member` VALUES ('108508', '王有琼', '15912768786', 'b4675b8d0e6b18f6f4b3f70be4249f20', '107906', '72.85', '2', '2018-12-19 21:59:37', '2018-12-22 14:49:20');
INSERT INTO `member` VALUES ('108509', '刘招英', '18760355713', '0d771eab0fcac4a188072ad243d4ef8a', '107943', '0.55', '2', '2018-12-19 21:59:39', null);
INSERT INTO `member` VALUES ('108510', '健康天使', '13353592700', '63eb6a8255d83b9e94da8d6d225f950c', '107938', '5.96', '2', '2018-12-19 21:59:50', null);
INSERT INTO `member` VALUES ('108511', '王东静', '15175540577', 'ca4053b23b5143ce3b15319d2aafe75f', '1', '0.00', '2', '2018-12-19 22:00:08', null);
INSERT INTO `member` VALUES ('108512', '丁美丽', '18487312686', '70fdc50261277b93a3af786bcba3c49e', '107935', '0.00', '2', '2018-12-19 22:00:42', null);
INSERT INTO `member` VALUES ('108513', '李艳丽', '15890959433', '39ff801e78c1afca951868455e8801d7', '10109', '0.00', '2', '2018-12-19 22:00:46', null);
INSERT INTO `member` VALUES ('108514', '李韦碧', '15887072811', '79c1c306d6e626df2979b6cc7ceebb1c', '1', '0.00', '2', '2018-12-19 22:01:00', null);
INSERT INTO `member` VALUES ('108515', '陈娜', '15897651701', '3352e49e946fe5a61b19c6e22a6844ad', '107921', '4.47', '2', '2018-12-19 22:01:07', null);
INSERT INTO `member` VALUES ('108516', '付文娟', '15733612802', '115f8cfd3be63f7a627bd09f4e96b9ce', '10109', '0.00', '2', '2018-12-19 22:01:59', null);
INSERT INTO `member` VALUES ('108517', '磨磨', '18877150311', '8761c2faed719f7429931cff8538b6b4', '107906', '16.39', '2', '2018-12-19 22:03:01', null);
INSERT INTO `member` VALUES ('108518', '唐昌运', '18300006057', '3419908975ce13ab02b9b99bf664e60f', '107908', '12.38', '2', '2018-12-19 22:03:35', null);
INSERT INTO `member` VALUES ('108519', '唐汐儿', '15027808310', '975944bab0fef5d0f2c3f702fc84b633', '108025', '0.00', '2', '2018-12-19 22:04:28', null);
INSERT INTO `member` VALUES ('108520', '梁月荣', '18218592217', '10a1cb6b026d9d62b5633425324d6f4f', '107977', '0.00', '2', '2018-12-19 22:04:34', null);
INSERT INTO `member` VALUES ('108521', '桃子', '17307451455', '9611b54f88d4dd491840f088ee7cd8df', '107908', '10.43', '2', '2018-12-19 22:05:20', null);
INSERT INTO `member` VALUES ('108522', '孙玲芳', '15839226192', 'a3463fdb12d24b9152b77423c4c598a1', '1', '0.00', '2', '2018-12-19 22:05:33', null);
INSERT INTO `member` VALUES ('108523', '张丽婷', '13660610438', '1759ef95aa30723c2abe0c663c7939e5', '1', '0.00', '2', '2018-12-19 22:05:50', null);
INSERT INTO `member` VALUES ('108524', '李琳', '18708771973', 'dddc279e85128a4dc41f3b53b669f854', '1', '0.00', '2', '2018-12-19 22:06:30', null);
INSERT INTO `member` VALUES ('108525', '温海乐', '18275885540', 'e2864ec77287d3e9838343bc9e095ea8', '108181', '0.00', '2', '2018-12-19 22:08:27', null);
INSERT INTO `member` VALUES ('108526', '吴中义', '13525323575', 'e2c9dd9c6d7c8b87c6a61a15e0778ce8', '10285', '0.00', '2', '2018-12-19 22:09:28', null);
INSERT INTO `member` VALUES ('108527', '苏海健', '18589922298', '3d24b838770ee90773804e8599e549ff', '108181', '0.00', '2', '2018-12-19 22:10:22', null);
INSERT INTO `member` VALUES ('108528', '冯先芬', '17828012743', '7b7a4e3ef44b1beab68738bfade9999c', '10084', '0.00', '2', '2018-12-19 22:11:50', null);
INSERT INTO `member` VALUES ('108529', '陆慧红', '17687249456', 'f3bfbda0a34a915ecd70a2fe85ce5d1a', '108356', '0.00', '2', '2018-12-19 22:11:52', null);
INSERT INTO `member` VALUES ('108530', '魏娟', '15293658638', '2562b28ebc50da7d00f647a78e2cad63', '1', '0.00', '2', '2018-12-19 22:11:54', null);
INSERT INTO `member` VALUES ('108531', '肖谋冬', '15657991439', 'b1854f2c774c887929a3e37f9d6c7219', '108518', '0.00', '2', '2018-12-19 22:11:55', null);
INSERT INTO `member` VALUES ('108532', '姣姣', '15987581742', 'bf267179f51bb16f74b69f523bdde569', '1', '0.00', '2', '2018-12-19 22:14:03', null);
INSERT INTO `member` VALUES ('108533', '曹玲', '18987102985', '68a1952b66f609dbd16df8ade6a3d3c7', '107973', '0.00', '2', '2018-12-19 22:14:46', null);
INSERT INTO `member` VALUES ('108534', '席胜楠', '15090085619', '2a472f616b0a292e187bbfde65965734', '107945', '0.00', '2', '2018-12-19 22:15:36', null);
INSERT INTO `member` VALUES ('108535', '万峰', '18581503818', '4b13dc04dc5afffd9972a659b6a3eb88', '108006', '0.00', '2', '2018-12-19 22:16:53', null);
INSERT INTO `member` VALUES ('108536', '孙海燕', '18306275821', 'fc1cd78354f4565738ed34fc6f7cb7cb', '10163', '0.00', '2', '2018-12-19 22:17:18', null);
INSERT INTO `member` VALUES ('108537', '林琳', '13588282986', '257e78b87c49559071d5f33810794a30', '10077', '0.00', '2', '2018-12-19 22:17:22', null);
INSERT INTO `member` VALUES ('108538', '李帅', '15964314668', 'b1216e8beb0fba404640f7f8383784ea', '108320', '0.00', '2', '2018-12-19 22:17:58', null);
INSERT INTO `member` VALUES ('108539', '李婉青', '15278162050', 'f7d4752a95b82b32e41a402ac621813e', '107906', '0.00', '2', '2018-12-19 22:19:48', null);
INSERT INTO `member` VALUES ('108540', '黄双星', '13270381181', 'c849823b05233edd2c9549b92558a785', '108495', '0.00', '2', '2018-12-19 22:19:54', null);
INSERT INTO `member` VALUES ('108541', '贺心', '13611034387', '3fabe5db4ae7c9476274e6d905639dec', '108175', '24.74', '2', '2018-12-19 22:20:11', '2018-12-22 14:46:48');
INSERT INTO `member` VALUES ('108542', '黄海', '13687774426', 'd27da2369d1406fac377beac4fc422da', '108169', '0.00', '2', '2018-12-19 22:20:21', null);
INSERT INTO `member` VALUES ('108543', '成爱民', '18839030016', 'dc483e80a7a0bd9ef71d8cf973673924', '107929', '0.00', '2', '2018-12-19 22:20:41', null);
INSERT INTO `member` VALUES ('108544', '成新英', '18873218588', 'cd13d1bafa0a4d9a60d069d1164cc69a', '10763', '0.00', '2', '2018-12-19 22:20:42', null);
INSERT INTO `member` VALUES ('108545', '谭娜', '13402468435', 'a5a808d959490f17c3e6466a50532d7b', '108025', '0.00', '2', '2018-12-19 22:20:53', null);
INSERT INTO `member` VALUES ('108546', '段悦彤', '15935155436', 'deafc79b647052a9a973136a73c9306d', '1', '0.00', '2', '2018-12-19 22:22:12', null);
INSERT INTO `member` VALUES ('108547', '高静', '15175251383', '5b200a108605da9432b621f23921bead', '1', '0.00', '2', '2018-12-19 22:22:45', null);
INSERT INTO `member` VALUES ('108548', '旦措毛', '13997243237', '3b14b97811647ef3c9f11ce419ba3c71', '107952', '1.49', '2', '2018-12-19 22:22:54', null);
INSERT INTO `member` VALUES ('108549', '陈琳', '13778265200', 'ef8d6591a37530d5f76dad750e961c88', '108340', '0.00', '2', '2018-12-19 22:23:01', null);
INSERT INTO `member` VALUES ('108550', '辛蕾蕾', '13911342728', '10dddf048d8110a5c5b575326ec94801', '10690', '0.00', '2', '2018-12-19 22:23:38', null);
INSERT INTO `member` VALUES ('108551', '陈蔼卿', '13823833166', 'fd30f7800f8c131767bd65fbe033831d', '108265', '0.00', '2', '2018-12-19 22:23:39', null);
INSERT INTO `member` VALUES ('108552', '熊金燕', '18148786147', '60616b6927f72964d852845b0a09d79e', '108065', '0.00', '2', '2018-12-19 22:23:44', null);
INSERT INTO `member` VALUES ('108553', '苏振国', '13994885159', '2af746d4cebfdc8d5fe1e468fd6defa4', '107938', '0.00', '2', '2018-12-19 22:24:19', null);
INSERT INTO `member` VALUES ('108554', '詹志成', '13361553619', 'a3aa22bc3467e8eaee237f64a121275d', '108473', '73.19', '2', '2018-12-19 22:25:11', null);
INSERT INTO `member` VALUES ('108555', '刘新立', '15117996727', '8ede603ea41779da8374a83086b6bfaa', '108175', '0.00', '2', '2018-12-19 22:25:19', '2018-12-23 09:11:59');
INSERT INTO `member` VALUES ('108556', '柳霞', '13784485533', '752e14537e2afe50578fb4cd326031c6', '108175', '608.37', '2', '2018-12-19 22:25:21', '2018-12-31 10:00:00');
INSERT INTO `member` VALUES ('108557', '马云霞', '13779103329', '067b8026c215dac215c3f8fd63195327', '107985', '0.00', '2', '2018-12-19 22:26:24', null);
INSERT INTO `member` VALUES ('108558', '小艾', '15020596434', 'e889059c5109696a9082abd7e04afb17', '10064', '0.00', '2', '2018-12-19 22:26:45', null);
INSERT INTO `member` VALUES ('108559', '白芬', '15838908716', '2569d419bfea999ff13fd1f7f4498b89', '1', '0.00', '2', '2018-12-19 22:27:15', null);
INSERT INTO `member` VALUES ('108560', '李丽平', '15015152015', 'd2be9f073a7429300a2ee434d15e4052', '107988', '0.00', '2', '2018-12-19 22:29:02', null);
INSERT INTO `member` VALUES ('108561', '谭莹', '18874170517', '3b8d47c066cbc9d3dd0e9071d06df0cc', '1', '0.00', '2', '2018-12-19 22:30:11', null);
INSERT INTO `member` VALUES ('108562', '彭女士', '15719493686', '515970b6c87645f59f3f5def589af2ce', '107990', '0.00', '2', '2018-12-19 22:32:49', null);
INSERT INTO `member` VALUES ('108563', '蛋蛋', '13897690482', '3b14b97811647ef3c9f11ce419ba3c71', '108548', '0.00', '2', '2018-12-19 22:33:31', null);
INSERT INTO `member` VALUES ('108564', '张忠红', '15977999067', '65a0ec385ca6a0c1e20d1f8270c28303', '107953', '4.47', '2', '2018-12-19 22:34:24', null);
INSERT INTO `member` VALUES ('108565', '黄世周', '15878959225', '71b57dcc1e60d5d706fbedd4635df32e', '1', '0.00', '2', '2018-12-19 22:35:02', null);
INSERT INTO `member` VALUES ('108566', '彩虹', '18982371563', '308baf9908fd6cf4400d206d521628b0', '108172', '0.00', '2', '2018-12-19 22:35:34', null);
INSERT INTO `member` VALUES ('108567', '皇甫冬雪', '15027536455', 'db510325b1ff32e752dcd40002ad1267', '1', '0.00', '2', '2018-12-19 22:35:41', null);
INSERT INTO `member` VALUES ('108568', '梁增丽', '17776410460', 'e56472701a05d4517caed0522d2561a9', '1', '0.00', '2', '2018-12-19 22:36:40', null);
INSERT INTO `member` VALUES ('108569', '董志晓', '15987968838', '1866fa7c0e048cbb481806aacdce1c52', '107906', '2.78', '2', '2018-12-19 22:37:24', null);
INSERT INTO `member` VALUES ('108570', '戴丽', '13512555044', '9579e7e3d27e7c2cbd24452147d58c9f', '107905', '0.00', '2', '2018-12-19 22:38:12', null);
INSERT INTO `member` VALUES ('108571', '陈娟', '18008601055', '82d23bba34c0f12d9ed6687a0979c3dc', '108172', '0.00', '2', '2018-12-19 22:40:22', null);
INSERT INTO `member` VALUES ('108572', '张敏霞', '13713933601', 'ea999701e0378763cf9ba78db01183c1', '108177', '0.00', '2', '2018-12-19 22:40:30', null);
INSERT INTO `member` VALUES ('108573', '宋宝红', '13910684325', 'dad83d4d6195fc78519f0e5b9f76a187', '108175', '29.40', '2', '2018-12-19 22:40:39', null);
INSERT INTO `member` VALUES ('108574', '张丽萍', '13988926947', '7300944d8d6d56f2bd3616737f4ff18e', '107908', '0.00', '2', '2018-12-19 22:41:37', null);
INSERT INTO `member` VALUES ('108575', '易小妞', '17773852597', '32393a9bd110d0876d0bfe59c9a0a587', '1', '0.00', '2', '2018-12-19 22:41:58', null);
INSERT INTO `member` VALUES ('108576', '安娜', '13069319526', '9928d5a6b13b902889552c61f778c881', '10109', '0.00', '2', '2018-12-19 22:42:23', null);
INSERT INTO `member` VALUES ('108577', '小雪', '18977207818', '8202bfa0a2509f9a56d83b0512750333', '107906', '0.00', '2', '2018-12-19 22:43:40', null);
INSERT INTO `member` VALUES ('108578', '苏苏', '18710137837', '3c6e5a3b91b41b05e531f56100d4848d', '107952', '0.02', '2', '2018-12-19 22:44:50', null);
INSERT INTO `member` VALUES ('108579', '韦妹琳', '18577650212', '4d095c2d4050ace5094e5ca3a5b06be9', '107983', '0.00', '2', '2018-12-19 22:45:10', null);
INSERT INTO `member` VALUES ('108580', '向静怡', '15390413945', 'bdb8c154a8ac11b7c8ef128461c6a4d3', '107990', '0.00', '2', '2018-12-19 22:45:16', null);
INSERT INTO `member` VALUES ('108581', '何海燕', '18319808819', 'aec60231d83fe6cf81444bc536596887', '107993', '0.00', '2', '2018-12-19 22:46:13', null);
INSERT INTO `member` VALUES ('108582', '江汉花', '15997465616', '540a7f3a31442c6f772cf8c9dc2fcb7a', '107952', '2.18', '2', '2018-12-19 22:46:30', null);
INSERT INTO `member` VALUES ('108583', '李娇', '13305227888', '31075f9b84bd578d6731932d23f2f996', '108127', '0.00', '2', '2018-12-19 22:46:40', null);
INSERT INTO `member` VALUES ('108584', '李昌娟', '13978633628', 'bf0a998addbe7c3e26f5bc020a41979f', '1', '0.00', '2', '2018-12-19 22:47:11', null);
INSERT INTO `member` VALUES ('108585', '王小英', '15814408853', 'c65151e563bd57c2b78ca1674269fead', '108429', '0.00', '2', '2018-12-19 22:47:15', null);
INSERT INTO `member` VALUES ('108586', '李冰', '13760222302', '63d81169597fd8638eeecec1dfd18430', '107913', '0.85', '2', '2018-12-19 22:47:28', null);
INSERT INTO `member` VALUES ('108587', '洋洋', '15244687349', '0d1f4cbe8530bb19057493757f098573', '108313', '0.00', '2', '2018-12-19 22:48:02', null);
INSERT INTO `member` VALUES ('108588', '钟万英', '15049965657', '02b06cce760e5603fb0c4e4184bb6b2b', '107922', '49.92', '2', '2018-12-19 22:49:29', null);
INSERT INTO `member` VALUES ('108589', '师艳春', '15096719329', 'c5ebabd20dc8c7626377a4a7fc07f828', '108414', '35.85', '2', '2018-12-19 22:50:19', null);
INSERT INTO `member` VALUES ('108590', '大姐大', '15073582871', 'dd8a46f7f4291625767b6eac66849aa3', '1', '0.00', '2', '2018-12-19 22:52:40', null);
INSERT INTO `member` VALUES ('108591', '吴薇丹', '15016710731', 'ae9a95f84dff758dd4300e5bd1a76643', '1', '0.00', '2', '2018-12-19 22:52:46', null);
INSERT INTO `member` VALUES ('108592', '林华英', '13677896726', '67ee9da42a9e2b63cf83668622096086', '107952', '2.98', '2', '2018-12-19 22:53:37', null);
INSERT INTO `member` VALUES ('108593', '刘雅奸', '13317774439', 'd27da2369d1406fac377beac4fc422da', '1', '0.00', '2', '2018-12-19 22:55:22', null);
INSERT INTO `member` VALUES ('108594', '王东利', '18639198899', 'db2e22a9ddaebc6a4cd1e6ba341100b6', '107929', '0.00', '2', '2018-12-19 22:55:32', null);
INSERT INTO `member` VALUES ('108595', '韦旋', '13647808980', '2dad5b6a377ac6daa7ded5404a5fafcd', '1', '0.00', '2', '2018-12-19 23:00:12', null);
INSERT INTO `member` VALUES ('108596', '徐丽多', '18288465833', 'cf17ff51ba4547b38be89c0a90681ab6', '107906', '4.47', '2', '2018-12-19 23:00:50', null);
INSERT INTO `member` VALUES ('108597', '放飞梦想', '13469097599', '631396d3ac8491c772b4fab0d62d7b54', '10417', '54.35', '2', '2018-12-19 23:01:47', '2018-12-22 16:24:55');
INSERT INTO `member` VALUES ('108598', '思绪', '13987607676', '8a64f5618ac3e25438569eccf5f66213', '1', '0.00', '2', '2018-12-19 23:02:14', null);
INSERT INTO `member` VALUES ('108599', '房珊珊', '18690666601', '0cd6ac0582162e7d1ade97a0c0ab28f9', '107927', '0.00', '2', '2018-12-19 23:03:41', null);
INSERT INTO `member` VALUES ('108600', '梁楠楠', '18600688409', '0602a3dfc90dabcf91d0d21c9afac3c9', '108175', '0.00', '2', '2018-12-19 23:06:05', '2018-12-23 09:15:32');
INSERT INTO `member` VALUES ('108601', '罗明琴', '15283996069', '3110c37b8493895b9678bbf22ab22948', '108126', '2.98', '2', '2018-12-19 23:06:54', '2018-12-31 14:16:34');
INSERT INTO `member` VALUES ('108602', '蔡满湘', '13574564865', 'cf01042c59424c64c8f68aef5d3e8a6a', '107981', '5.96', '2', '2018-12-19 23:07:56', null);
INSERT INTO `member` VALUES ('108603', '芳芳', '13420768022', 'd9f0250f4ec36ac4b5698f5958091d84', '107977', '2.98', '2', '2018-12-19 23:09:02', null);
INSERT INTO `member` VALUES ('108604', '隆菲菲', '18933903658', '50b5115a4f3cd19c69a6ef25cebac93b', '107906', '0.00', '2', '2018-12-19 23:09:29', null);
INSERT INTO `member` VALUES ('108605', '吴鹏', '18086758786', '45a0bfedaf65c5dcd716a28d99442e54', '108127', '2.35', '2', '2018-12-19 23:09:49', null);
INSERT INTO `member` VALUES ('108606', '响响', '13307776197', '2b77954530f2d9bd85e22b7c3ebbe991', '10467', '0.00', '2', '2018-12-19 23:13:48', null);
INSERT INTO `member` VALUES ('108607', '阿莲', '17787700699', 'a97ccc150b76d66db47caa7a545991b8', '1', '0.00', '2', '2018-12-19 23:14:40', null);
INSERT INTO `member` VALUES ('108608', '龙瑜佳', '18374283630', '3d89d4b435ecc483089fbff98370d5c5', '1', '0.00', '2', '2018-12-19 23:14:58', null);
INSERT INTO `member` VALUES ('108609', '程晴', '18023815258', '1ee71d04415e3dc1388345acf2424872', '108186', '0.00', '2', '2018-12-19 23:15:21', null);
INSERT INTO `member` VALUES ('108610', '丽子', '13691410542', '2b746eb79913de58c99ff3ba23d13259', '1', '0.00', '2', '2018-12-19 23:16:12', null);
INSERT INTO `member` VALUES ('108611', '吴鹏', '15682185061', '0c94a8f89c8584b30962ca425418abdd', '108127', '0.00', '2', '2018-12-19 23:16:18', null);
INSERT INTO `member` VALUES ('108612', '王小军', '18031616676', '0e9688a55d51db936cb771b2b828f73e', '107939', '11.92', '2', '2018-12-19 23:16:21', null);
INSERT INTO `member` VALUES ('108613', '周益环', '17605851116', 'e855069728bb6c893b17e04d13b5790a', '108030', '4.33', '2', '2018-12-19 23:17:00', null);
INSERT INTO `member` VALUES ('108614', '张学昌', '15800645571', 'd094f005ef7e844453106b66d4010a08', '108098', '0.00', '2', '2018-12-19 23:17:06', null);
INSERT INTO `member` VALUES ('108615', '杨梅', '15198679935', '9852767a00ffcc461a2793530b9a33a1', '107966', '0.00', '2', '2018-12-19 23:17:23', null);
INSERT INTO `member` VALUES ('108616', '凤儿', '16620663313', '5caa3501602770d59ef1e7c4f28f6783', '10086', '1.49', '2', '2018-12-19 23:18:31', null);
INSERT INTO `member` VALUES ('108617', '许莲香', '15768697641', '6a943d84a8490627aae0af9eb55d852c', '1', '0.00', '2', '2018-12-19 23:20:10', null);
INSERT INTO `member` VALUES ('108618', '刘艳娟', '13532809094', '0fa69590677e5f7b4c2c5fca2e8ac73d', '107960', '0.00', '2', '2018-12-19 23:20:14', null);
INSERT INTO `member` VALUES ('108619', '戴琼书', '15008702914', 'cb1b1af4a115ad978638ae1277ccbeaf', '108005', '0.00', '2', '2018-12-19 23:22:24', null);
INSERT INTO `member` VALUES ('108620', '彭金红', '13551379428', '515970b6c87645f59f3f5def589af2ce', '107990', '0.00', '2', '2018-12-19 23:24:32', null);
INSERT INTO `member` VALUES ('108621', '寒筱', '13963121666', '235836054f3f712fb5d29472a311020f', '1', '0.00', '2', '2018-12-19 23:25:34', null);
INSERT INTO `member` VALUES ('108622', '苏俊丽', '13935342007', '46f94c8de14fb36680850768ff1b7f2a', '107933', '35.85', '2', '2018-12-19 23:27:05', null);
INSERT INTO `member` VALUES ('108623', '娟子', '17358505806', 'a7852910586c97d8cc3950107b493d8e', '107991', '0.00', '2', '2018-12-19 23:27:44', null);
INSERT INTO `member` VALUES ('108624', '谢晓春', '13959453386', 'd4a26c7e27d7886309ceb2d995c17963', '1', '0.00', '2', '2018-12-19 23:29:39', null);
INSERT INTO `member` VALUES ('108625', '劳咏红', '13543028212', '92ea37ce2d20320ec5c3dfff9742e95f', '107985', '0.00', '2', '2018-12-19 23:36:28', null);
INSERT INTO `member` VALUES ('108626', '林燕玲', '13713397523', '9413a1d8c66761b808f90200e71f21e3', '1', '0.00', '2', '2018-12-19 23:38:37', null);
INSERT INTO `member` VALUES ('108627', '王莉苹', '18608860080', 'eab0948122f4133e0477d19a6ed879da', '107983', '0.00', '2', '2018-12-19 23:41:13', null);
INSERT INTO `member` VALUES ('108628', '刘琴', '15528228783', 'eefa2d8666cfd8ab7c0e92a0cb4b75f6', '10203', '0.00', '2', '2018-12-19 23:41:30', null);
INSERT INTO `member` VALUES ('108629', '王保芬', '18388355103', '57058f3076d235883793df062f2a83cc', '107906', '0.00', '2', '2018-12-19 23:42:39', null);
INSERT INTO `member` VALUES ('108630', '廖历群', '17345178828', '64eb3908d964c9d887bc4749c0183eca', '107905', '0.00', '2', '2018-12-19 23:45:47', null);
INSERT INTO `member` VALUES ('108631', '房艳华', '18733018806', '17c9289e34e5991c2b5bf1175cd16384', '108047', '5.96', '2', '2018-12-19 23:50:37', null);
INSERT INTO `member` VALUES ('108632', '王莹', '13030281144', 'cbc2985a8dfaa5071aaedc7189a99755', '108147', '0.00', '2', '2018-12-19 23:52:50', null);
INSERT INTO `member` VALUES ('108633', '静', '13594666855', 'e63e93eca249e67e35bd3a154438c5ee', '1', '0.00', '2', '2018-12-19 23:53:23', null);
INSERT INTO `member` VALUES ('108634', '欧月煊', '15858000582', '1a5d6d5a460323e55caccf4991ba053a', '107906', '104.61', '2', '2018-12-19 23:53:59', null);
INSERT INTO `member` VALUES ('108635', '王珂', '13839289693', '11ad15ab6bb801194628aa5ff876c1ae', '1', '0.00', '2', '2018-12-19 23:55:30', null);
INSERT INTO `member` VALUES ('108636', '张丽花', '15777182608', 'fe21704c859e234851a9ecbb962d0204', '108209', '0.00', '2', '2018-12-19 23:56:05', null);
INSERT INTO `member` VALUES ('108637', '潘英子', '18677690052', '09298cbf5b2cec9260f9eb2470ec3232', '10467', '0.00', '2', '2018-12-19 23:56:12', null);
INSERT INTO `member` VALUES ('108638', '韦姐', '13829267503', 'd4bc9c37e4fdbf90b5ddfb6ad0aca900', '107953', '310.60', '2', '2018-12-19 23:59:26', null);
INSERT INTO `member` VALUES ('108639', '雪儿', '17377190020', 'f7d2800c6784ce43a9bfa733eddcaa41', '108634', '180.16', '2', '2018-12-19 23:59:58', null);
INSERT INTO `member` VALUES ('108640', '冉煜', '15282814553', 'e34f99481284a0f3f256b265b9fc8c2d', '108299', '0.00', '2', '2018-12-20 00:00:19', null);
INSERT INTO `member` VALUES ('108641', '郑聪', '15132937302', '7c2fd77fcf950dcc975b6fc53c2d1434', '107927', '0.00', '2', '2018-12-20 00:03:51', null);
INSERT INTO `member` VALUES ('108642', '黄小燕', '18267746031', '6f5f42acf4c541181d692acf905413ae', '1', '0.00', '2', '2018-12-20 00:05:12', null);
INSERT INTO `member` VALUES ('108643', '郑秀菊', '18783207251', '03363affbd084d2e6bfec06d96b90821', '107915', '0.00', '2', '2018-12-20 00:09:11', null);
INSERT INTO `member` VALUES ('108644', '刘春妙', '13277787276', 'dc483e80a7a0bd9ef71d8cf973673924', '108186', '0.00', '2', '2018-12-20 00:10:05', null);
INSERT INTO `member` VALUES ('108645', '邓颖', '18176899570', '8906933fe28fb89da1f6c446ec1a2f07', '1', '0.00', '2', '2018-12-20 00:16:56', null);
INSERT INTO `member` VALUES ('108646', '闫艳', '13562923962', 'e52409f31d1f67d749db96e2927e4d3f', '1', '0.00', '2', '2018-12-20 00:17:08', null);
INSERT INTO `member` VALUES ('108647', '赵燕霞', '15825883363', 'df13c48a1a708b23f6b7300a4314faff', '1', '0.00', '2', '2018-12-20 00:18:22', null);
INSERT INTO `member` VALUES ('108648', '梁金玉', '13463578606', '0e4d8442a4198b341a5b9338f75181aa', '10077', '2.98', '2', '2018-12-20 00:18:51', null);
INSERT INTO `member` VALUES ('108649', '文慧', '13629667359', '3f9c6e4ca4a4e55217a43aa75b84247e', '1', '0.00', '2', '2018-12-20 00:24:51', null);
INSERT INTO `member` VALUES ('108650', '张泽还', '18163770036', 'eca53cd84c05d836252bba3542cf5bb6', '108243', '0.00', '2', '2018-12-20 00:33:23', null);
INSERT INTO `member` VALUES ('108651', '覃桂言', '18278194107', '69eb855e0aa13e07c6a3390c71898a1d', '10173', '0.00', '2', '2018-12-20 00:33:52', null);
INSERT INTO `member` VALUES ('108652', '东方红', '13587061863', '8129b6014691d92964b1341f437be360', '1', '0.00', '2', '2018-12-20 00:34:05', null);
INSERT INTO `member` VALUES ('108653', '李东芳', '13990863450', 'cf969be4fee7231e6011be9c2e77ed2c', '108127', '0.00', '2', '2018-12-20 00:47:07', null);
INSERT INTO `member` VALUES ('108654', '家家', '15835163263', '2912ee97f8fb09c0624b7111fb9490bb', '1', '0.00', '2', '2018-12-20 00:47:38', null);
INSERT INTO `member` VALUES ('108655', '三生烟火一抹笑', '15187720450', 'eef02cfda280e8747bd50fb8696ee2b0', '1', '0.00', '2', '2018-12-20 01:09:45', null);
INSERT INTO `member` VALUES ('108656', '黄盈盈', '18007779973', '48daf27b006ebe788ec7cca1a7f14374', '1', '0.00', '2', '2018-12-20 01:17:16', null);
INSERT INTO `member` VALUES ('108657', '季永婷', '15202551120', 'd9090d643e7940845c95c9b7e74c9017', '1', '0.00', '2', '2018-12-20 01:21:33', null);
INSERT INTO `member` VALUES ('108658', '代华英', '15528192201', '39145a982ae082b44dbaf9b09e02d462', '1', '0.00', '2', '2018-12-20 01:39:07', null);
INSERT INTO `member` VALUES ('108659', '郝攀攀', '15660793131', '6d88f3b302f206fe0545e183df3cdb37', '1', '0.00', '2', '2018-12-20 03:25:18', null);
INSERT INTO `member` VALUES ('108660', '丽珠', '13988649642', 'b6fc5336c3a09660acb2f0f98c5df926', '1', '0.00', '2', '2018-12-20 03:32:44', null);
INSERT INTO `member` VALUES ('108661', '焦翠', '18089299778', '80d5bab9dfd0425a6931c76350c25c07', '1', '0.00', '2', '2018-12-20 03:40:36', null);
INSERT INTO `member` VALUES ('108662', '姜凤玉', '17674315505', '69a1f2984c486bb3e28d9d19ba49caec', '10417', '0.00', '2', '2018-12-20 03:43:35', null);
INSERT INTO `member` VALUES ('108663', '乔慧芬', '15635508577', 'c51cd8e64b0aeb778364765013df9ebe', '1', '0.00', '2', '2018-12-20 04:06:18', null);
INSERT INTO `member` VALUES ('108664', '叶子', '15007419999', '1e65faa1656d5c0ffde126c2750ab3eb', '10416', '4.16', '2', '2018-12-20 05:52:02', null);
INSERT INTO `member` VALUES ('108665', '路丽', '15184884748', '73626af1190caa5db6ced7782415dba8', '107906', '14.90', '2', '2018-12-20 05:52:09', null);
INSERT INTO `member` VALUES ('108666', '薛园', '15152870766', 'dcd8c30fde4c703ba57f286406bb845a', '107905', '0.00', '2', '2018-12-20 06:25:11', null);
INSERT INTO `member` VALUES ('108667', '美丽无痕', '18841004118', '9e62f281b1a58ca8fc92f98c1c0b3259', '107906', '0.00', '2', '2018-12-20 06:30:38', null);
INSERT INTO `member` VALUES ('108668', '阎朝敏', '17738565102', 'b3fa14ef9a327c7d7f60cff732bbe927', '107908', '1.49', '2', '2018-12-20 06:46:11', null);
INSERT INTO `member` VALUES ('108669', '谷玥萱', '15636650444', '93203328963b662482219041799b9886', '107915', '0.00', '2', '2018-12-20 06:50:50', null);
INSERT INTO `member` VALUES ('108670', '如意', '15368496599', '1c243a8194c4b1fcd683103c40bd78bc', '107915', '0.00', '2', '2018-12-20 06:58:56', null);
INSERT INTO `member` VALUES ('108671', '王宝珍', '15238028647', '3d24b838770ee90773804e8599e549ff', '1', '0.00', '2', '2018-12-20 06:59:29', null);
INSERT INTO `member` VALUES ('108672', '晓玉', '15093466281', 'dc483e80a7a0bd9ef71d8cf973673924', '108056', '37.44', '2', '2018-12-20 07:03:01', null);
INSERT INTO `member` VALUES ('108673', '苏金娣', '15185636161', '8afd4788c77d242b402556341b7f81eb', '1', '0.00', '2', '2018-12-20 07:09:40', null);
INSERT INTO `member` VALUES ('108674', '张菊', '13541826498', '0bc7f873746b9d8371e166c9b3b590d2', '108126', '0.00', '2', '2018-12-20 07:12:15', null);
INSERT INTO `member` VALUES ('108675', '王淑亚', '15037848979', 'aa382992e928d8cd6d7f22dc252d933e', '10109', '0.00', '2', '2018-12-20 07:14:18', null);
INSERT INTO `member` VALUES ('108676', '刘恬恬', '18530939130', '56a17c011fdffecff74a5090cd0009be', '108056', '0.00', '2', '2018-12-20 07:15:51', null);
INSERT INTO `member` VALUES ('108677', '李树青', '15238714648', '2a4a55c45a7d52bc3e09951635427831', '107929', '0.00', '2', '2018-12-20 07:17:04', null);
INSERT INTO `member` VALUES ('108678', '梁宝萍', '13783656325', 'c57562653c783faeb8b6cd917ef258c1', '108056', '0.00', '2', '2018-12-20 07:17:34', null);
INSERT INTO `member` VALUES ('108679', '宋宋', '18381412198', '640c1aa40dca52e7e3ad942f2ad05b0f', '107952', '0.00', '2', '2018-12-20 07:18:14', null);
INSERT INTO `member` VALUES ('108680', '刘利丛', '13811337814', '828c6a199232cf6cf4c0de0d94bc19d3', '10690', '0.00', '2', '2018-12-20 07:20:53', null);
INSERT INTO `member` VALUES ('108681', '刘晓丹', '16673508161', '015e20bdd093034e97363ef1439a72dc', '1', '0.00', '2', '2018-12-20 07:24:33', null);
INSERT INTO `member` VALUES ('108682', '王岩阔', '13810300685', 'a882399182bb5e0a2862ace1037dac49', '1', '0.00', '2', '2018-12-20 07:25:52', null);
INSERT INTO `member` VALUES ('108683', '滕召霞', '13739028760', '9b9895749b76e444d7de62548202bc6e', '10417', '58.80', '2', '2018-12-20 07:42:12', null);
INSERT INTO `member` VALUES ('108684', '蒋天丽', '13170660946', '7d6299af92a75efb1dde59e0ca875451', '1', '0.00', '2', '2018-12-20 07:44:09', null);
INSERT INTO `member` VALUES ('108685', '陈陈', '15689579398', '8d7ba5939bc2a3ecdc86c22477dc88db', '108344', '0.00', '2', '2018-12-20 07:47:55', null);
INSERT INTO `member` VALUES ('108686', '何春林', '18314163296', 'a380fed88c105c3b7cb9e66a9bb074fe', '1', '0.00', '2', '2018-12-20 07:57:19', null);
INSERT INTO `member` VALUES ('108687', '胡超', '18832646675', 'd9488c1ba8bcf15595178e2dd373439b', '1', '0.00', '2', '2018-12-20 07:58:33', null);
INSERT INTO `member` VALUES ('108688', '黄小娟', '13975708646', '5e0897661dd302cd19af3dd91dc600af', '107981', '1.49', '2', '2018-12-20 08:02:37', null);
INSERT INTO `member` VALUES ('108689', '张海英', '13939126782', '6672313e2cfe21eaf8f4f22db0246e86', '107929', '0.00', '2', '2018-12-20 08:02:56', null);
INSERT INTO `member` VALUES ('108690', '鱼儿', '18744417677', 'ea7a4cdb7909bfd0756d1fdab4817171', '108162', '23.70', '2', '2018-12-20 08:08:56', null);
INSERT INTO `member` VALUES ('108691', '明鲜', '13387787199', '328ee99e80da38b63357359823f4201e', '108209', '16.34', '2', '2018-12-20 08:10:13', null);
INSERT INTO `member` VALUES ('108692', '约书亚', '15890614013', 'd628e399958b899f3b7c5e6c237eb513', '10109', '0.00', '2', '2018-12-20 08:11:20', null);
INSERT INTO `member` VALUES ('108693', '史立伟', '15613598735', 'f25deeb6aa0850b8aa8b56acd85530a7', '108025', '0.00', '2', '2018-12-20 08:14:33', null);
INSERT INTO `member` VALUES ('108694', '影子陈', '15296573092', 'f97f9e56a9455b2421512eac33affdc3', '108236', '73.19', '2', '2018-12-20 08:15:34', null);
INSERT INTO `member` VALUES ('108695', '美时美刻体验馆', '13455605326', '8d95a39627ed01f334cc7daa84b8922a', '10077', '0.00', '2', '2018-12-20 08:18:15', null);
INSERT INTO `member` VALUES ('108696', '张赟英', '13751984686', '6d6fb81033112225f4465ff60f8f226e', '107960', '4.47', '2', '2018-12-20 08:18:27', null);
INSERT INTO `member` VALUES ('108697', '陈曼曼', '18785308608', '4aa86876acf40d74cc1d57259c9fda41', '10429', '0.00', '2', '2018-12-20 08:19:17', null);
INSERT INTO `member` VALUES ('108698', '谢晶', '13149108004', '3e7691a896fde6f5dbd4c5aea2c410fd', '1', '0.00', '2', '2018-12-20 08:20:21', null);
INSERT INTO `member` VALUES ('108699', '史尹丹', '13908801168', 'f0a44ca8d45297e0b3795c61b8289a8e', '1', '0.00', '2', '2018-12-20 08:20:50', null);
INSERT INTO `member` VALUES ('108700', '龙世珍', '13547447283', '964f51812b0aff885dbc91ae54d25157', '10416', '0.00', '2', '2018-12-20 08:20:58', null);
INSERT INTO `member` VALUES ('108702', '凤', '13695975077', '045effb22069a4e292850ff84faff015', '1', '0.00', '2', '2018-12-20 08:34:31', null);
INSERT INTO `member` VALUES ('108703', '王鲁阳', '18054653536', '814f4d1445b69fb45354d1620cc7c953', '1', '0.00', '2', '2018-12-20 08:34:54', null);
INSERT INTO `member` VALUES ('108704', '郭金梅', '15263859908', '475e1d8558c25995bf27f742e23ba7b7', '10078', '0.00', '2', '2018-12-20 08:36:31', null);
INSERT INTO `member` VALUES ('108705', '莎莎', '15987746442', '633e1dce6a8a610bb8c1202afe0e00c9', '1', '0.00', '2', '2018-12-20 08:37:21', null);
INSERT INTO `member` VALUES ('108706', '马淑兰', '15601364227', '77382e342627a4ed2e76e8c34166d8c2', '108175', '179.25', '2', '2018-12-20 08:39:44', null);
INSERT INTO `member` VALUES ('108707', '旭旭', '13810080754', '4c17b1febfebc66067f14579c59b70c4', '1', '0.00', '2', '2018-12-20 08:43:28', null);
INSERT INTO `member` VALUES ('108708', '闵美', '17774357083', '984cefd6d27eb0471fc401a493a4fdff', '10417', '0.00', '2', '2018-12-20 08:45:32', null);
INSERT INTO `member` VALUES ('108709', '胡云菲', '13529976834', '96e79218965eb72c92a549dd5a330112', '108708', '0.00', '2', '2019-02-22 13:21:14', null);
INSERT INTO `member` VALUES ('108721', 'summer', '18763895699', '96e79218965eb72c92a549dd5a330112', '1', '0.00', '2', '2019-02-22 15:13:34', null);

-- ----------------------------
-- Table structure for setting
-- ----------------------------
DROP TABLE IF EXISTS `setting`;
CREATE TABLE `setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `module` varchar(50) DEFAULT NULL COMMENT '模块',
  `code` varchar(30) DEFAULT NULL COMMENT '值',
  `val` mediumtext COMMENT '名称',
  `name` varchar(100) DEFAULT NULL COMMENT '名称',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uni` (`module`,`code`) USING BTREE,
  KEY `module` (`module`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='系统配置';

-- ----------------------------
-- Records of setting
-- ----------------------------
INSERT INTO `setting` VALUES ('1', 'site', 'siteName', '桥通天下', '系统名称');
INSERT INTO `setting` VALUES ('2', 'version', 'version', '1.0.1', '版本');
INSERT INTO `setting` VALUES ('3', 'sms', 'key', '903f256deba0d772a2adbc06265bcce8', '短信接口code');
INSERT INTO `setting` VALUES ('4', 'sms', 'tplId', '118804', '短信模板ID');
INSERT INTO `setting` VALUES ('5', 'sms', 'url', 'http://v.juhe.cn/sms/send', '短信url');
