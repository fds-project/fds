SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for api_log
-- ----------------------------
DROP TABLE IF EXISTS `api_log`;
CREATE TABLE `api_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `auth_user` int(11) DEFAULT NULL,
  `parameters` text,
  `added` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4371 DEFAULT CHARSET=latin1;


-- ----------------------------
-- Table structure for auth
-- ----------------------------
DROP TABLE IF EXISTS `auth`;
CREATE TABLE `auth` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `authkey` varchar(40) DEFAULT NULL,
  `last_used` datetime DEFAULT NULL,
  `comment` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of auth
-- ----------------------------
INSERT INTO `auth` VALUES ('1', '8308651804facb7b9af8ffc53a33a22d6a1c8ac2', '2018-01-14 12:13:33', 'Global API token used by various applications');
INSERT INTO `auth` VALUES ('2', 'b53f4f92fdd37ed8425f629863a8dbdcb9118163', '2018-01-13 12:09:43', 'Local Passtrough CE-JS engine');

-- ----------------------------
-- Table structure for condition_groups
-- ----------------------------
DROP TABLE IF EXISTS `condition_groups`;
CREATE TABLE `condition_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `comment` text,
  `added` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of condition_groups
-- ----------------------------
INSERT INTO `condition_groups` VALUES ('1', 'Demo condition list #1', '<b>Demo condition list :D</b>', '2017-11-29 22:47:00');

-- ----------------------------
-- Table structure for condition_types
-- ----------------------------
DROP TABLE IF EXISTS `condition_types`;
CREATE TABLE `condition_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `internalValue` varchar(255) DEFAULT NULL,
  `visibleValue` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of condition_types
-- ----------------------------
INSERT INTO `condition_types` VALUES ('1', 'equals', 'Equals');
INSERT INTO `condition_types` VALUES ('2', 'notEquals', 'Not Equals');
INSERT INTO `condition_types` VALUES ('3', 'contains', 'Contains');
INSERT INTO `condition_types` VALUES ('4', 'checkRange', 'In integer range');
INSERT INTO `condition_types` VALUES ('5', 'lessThan', 'Below');
INSERT INTO `condition_types` VALUES ('6', 'lessThanOrEquals', 'Below or Equals');
INSERT INTO `condition_types` VALUES ('7', 'greaterThan', 'Greater');
INSERT INTO `condition_types` VALUES ('8', 'greaterThanOrEquals', 'Greater or Equals');
INSERT INTO `condition_types` VALUES ('9', 'regex', 'Regex match (Python)');
INSERT INTO `condition_types` VALUES ('10', 'empty', 'Is Empty / Not set');
INSERT INTO `condition_types` VALUES ('11', 'notEmpty', 'Not Empty');
INSERT INTO `condition_types` VALUES ('12', 'length', 'String length is');
INSERT INTO `condition_types` VALUES ('13', 'minLength', 'Minimal Length');
INSERT INTO `condition_types` VALUES ('14', 'maxLength', 'Max Length');
INSERT INTO `condition_types` VALUES ('15', 'isTrue', 'Is True / Set');
INSERT INTO `condition_types` VALUES ('16', 'isFalse', 'Is False / Not set');

-- ----------------------------
-- Table structure for conditions
-- ----------------------------
DROP TABLE IF EXISTS `conditions`;
CREATE TABLE `conditions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupid` int(11) DEFAULT NULL,
  `sort_order` int(11) DEFAULT NULL,
  `fieldName` varchar(125) DEFAULT NULL,
  `condition` text,
  `operation` varchar(50) DEFAULT NULL,
  `conditionType` varchar(255) DEFAULT NULL,
  `multiplier_value` int(11) DEFAULT NULL,
  `multiplier_type` varchar(25) DEFAULT NULL,
  `warning` int(1) DEFAULT NULL,
  `added` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=170 DEFAULT CHARSET=latin1;


-- ----------------------------
-- Table structure for datasources
-- ----------------------------
DROP TABLE IF EXISTS `datasources`;
CREATE TABLE `datasources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `inputtype` varchar(25) DEFAULT NULL,
  `testdata` text,
  `added` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for datasources_fields
-- ----------------------------
DROP TABLE IF EXISTS `datasources_fields`;
CREATE TABLE `datasources_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fieldid` varchar(255) DEFAULT NULL,
  `datasourceid` int(255) DEFAULT NULL,
  `fielddata` varchar(255) DEFAULT NULL,
  `datatype` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=latin1;


-- ----------------------------
-- Table structure for fields
-- ----------------------------
DROP TABLE IF EXISTS `fields`;
CREATE TABLE `fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `visibleValue` varchar(255) DEFAULT NULL,
  `internalValue` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of fields
-- ----------------------------
INSERT INTO `fields` VALUES ('1', 'Country', 'country');
INSERT INTO `fields` VALUES ('2', 'Full Name', 'fullName');
INSERT INTO `fields` VALUES ('3', 'City', 'city');
INSERT INTO `fields` VALUES ('4', 'BSN / SSN', 'bsn');
INSERT INTO `fields` VALUES ('5', 'Year of Birth', 'yearOfBirth');
INSERT INTO `fields` VALUES ('6', 'Country of Origin', 'countryOrigin');
INSERT INTO `fields` VALUES ('7', 'First Name', 'firstName');
INSERT INTO `fields` VALUES ('8', 'Last Name', 'lastName');
INSERT INTO `fields` VALUES ('9', 'Naam (NLD)', 'naam');

-- ----------------------------
-- Table structure for jobs
-- ----------------------------
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `text` text,
  `added` datetime DEFAULT NULL,
  `datasourceid` int(11) DEFAULT NULL,
  `conditiongroupid` int(11) DEFAULT NULL,
  `addedby` int(11) DEFAULT NULL,
  `status` int(1) DEFAULT NULL,
  `type` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for logs
-- ----------------------------
DROP TABLE IF EXISTS `logs`;
CREATE TABLE `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data` text,
  `loginuser` int(11) DEFAULT NULL,
  `added` datetime DEFAULT NULL,
  `ip` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=155 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for mldata
-- ----------------------------
DROP TABLE IF EXISTS `mldata`;
CREATE TABLE `mldata` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupid` int(11) DEFAULT NULL,
  `binaryresultstring` text,
  `ylabel` int(11) DEFAULT NULL,
  `added` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of mldata
-- ----------------------------
INSERT INTO `mldata` VALUES ('1', '1', '1010111000101010', '1', '2017-12-08 23:06:12');
INSERT INTO `mldata` VALUES ('2', '1', '1010111000101111', '1', '2017-12-08 23:06:42');
INSERT INTO `mldata` VALUES ('3', '1', '1010111000000000', '0', '2017-12-08 23:06:39');

-- ----------------------------
-- Table structure for profiles
-- ----------------------------
DROP TABLE IF EXISTS `profiles`;
CREATE TABLE `profiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `publicname` varchar(255) DEFAULT NULL,
  `data` text,
  `added` datetime DEFAULT NULL,
  `key_iv` blob,
  `key_data` varchar(255) DEFAULT NULL,
  `encryption` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=344 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(125) DEFAULT NULL,
  `password` varchar(40) DEFAULT NULL,
  `password_salt` varchar(25) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `country` varchar(2) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `privileges` int(1) DEFAULT '0',
  `personal_use` int(1) DEFAULT NULL,
  `name_person` varchar(255) DEFAULT NULL,
  `custom_email` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES ('1', 'admin@fds.local', 'c94260908f9bfc3dc4ff6d6fb7199f7d0d6859f7', 'ladsfkjsfadkjsfadkjsfadi', '2017-11-28 13:04:38', 'NL', '1', '1', '1', 'Default User', 'admin@fds.local');

