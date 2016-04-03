SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


CREATE TABLE IF NOT EXISTS `audience` (
  `audience_id` int(11) NOT NULL AUTO_INCREMENT,
  `audience_username` varchar(100) NOT NULL,
  `audience_pwd` varchar(100) NOT NULL,
  `audience_name` varchar(100) NOT NULL,
  `audience_tel` varchar(20) NOT NULL,
  `audience_email` varchar(50) NOT NULL,
  `audience_department` tinyint(4) NOT NULL DEFAULT '1',
  `audience_position` varchar(255) NOT NULL,
  `audience_portrait` varchar(255) NOT NULL,
  `audience_state` tinyint(1) NOT NULL,
  `audience_nick` varchar(1) NOT NULL,
  `audience_sex` tinyint(1) NOT NULL,
  `audience_age` tinyint(3) NOT NULL,
  `audience_qq` int(11) NOT NULL,
  `audience_weixin` varchar(20) NOT NULL,
  `audience_imUsername` varchar(255) NOT NULL,
  `audience_imPassword` varchar(255) NOT NULL,
  `leader_id` int(11) NOT NULL DEFAULT '1',
  `audience_rights` tinyint(1) NOT NULL DEFAULT '1',
  `time_insert` int(11) NOT NULL,
  `time_update` int(11) NOT NULL DEFAULT '0',
  `time_delete` int(11) NOT NULL,
  `audience_sort` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`audience_id`),
  UNIQUE KEY `audience_username` (`audience_username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `audience_department` (
  `department_id` int(11) NOT NULL AUTO_INCREMENT,
  `department_name` varchar(50) NOT NULL,
  `department_pid` int(11) NOT NULL DEFAULT '0',
  `audience_num` int(11) NOT NULL,
  `have_child` tinyint(1) NOT NULL DEFAULT '0',
  `floor_id` int(11) NOT NULL,
  `leader_id` int(11) NOT NULL,
  `time_insert` int(11) NOT NULL,
  `time_update` int(11) NOT NULL,
  `time_delete` int(11) NOT NULL,
  PRIMARY KEY (`department_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `audience_department_rds` (
  `rds_id` int(11) NOT NULL AUTO_INCREMENT,
  `audience_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `time_insert` int(11) NOT NULL DEFAULT '0',
  `time_update` int(11) NOT NULL DEFAULT '0',
  `time_delete` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`rds_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `audience_meeting_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `audience_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


INSERT INTO `audience_meeting_category` (`id`, `audience_id`, `category_id`) VALUES(1, 1, 1);

CREATE TABLE IF NOT EXISTS `department_personnel` (
  `dpid` int(11) NOT NULL AUTO_INCREMENT,
  `department_id` int(11) NOT NULL,
  `personnel_id` int(11) NOT NULL,
  PRIMARY KEY (`dpid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


INSERT INTO `department_personnel` (`dpid`, `department_id`, `personnel_id`) VALUES
(1, 1, 4);


CREATE TABLE IF NOT EXISTS `event` (
  `event_id` int(11) NOT NULL AUTO_INCREMENT,
  `event_name` varchar(255) NOT NULL,
  `event_description` text NOT NULL,
  `event_start_time` int(11) NOT NULL,
  `event_end_time` int(11) NOT NULL,
  `event_launch_id` int(11) NOT NULL,
  `event_execution_id` int(11) NOT NULL,
  `event_examination_id` int(11) NOT NULL,
  `event_acceptance_id` int(11) NOT NULL,
  `event_current_audience` int(11) NOT NULL,
  `event_current_date` date NOT NULL,
  `event_current_state` int(11) NOT NULL,
  `event_current_responds` text NOT NULL,
  `event_image` varchar(20) NOT NULL,
  `event_state` tinyint(4) NOT NULL,
  `project_id` int(11) NOT NULL,
  `time_insert` int(11) NOT NULL,
  `time_update` int(11) NOT NULL,
  `time_delete` int(11) NOT NULL,
  PRIMARY KEY (`event_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `event_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `log_type` tinyint(1) NOT NULL,
  `audience_id` int(11) NOT NULL,
  `event_state` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `event_responds` text NOT NULL,
  `time_insert` int(11) NOT NULL,
  `time_update` int(11) NOT NULL,
  `time_delete` int(11) NOT NULL,
  `time_date` varchar(20) NOT NULL,
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `event_notice` (
  `notice_id` int(11) NOT NULL AUTO_INCREMENT,
  `audience_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `event_state` int(11) NOT NULL,
  `time_insert` int(11) NOT NULL,
  `time_update` int(11) NOT NULL,
  `time_delete` int(11) NOT NULL,
  PRIMARY KEY (`notice_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `information` (
  `information_id` int(11) NOT NULL AUTO_INCREMENT,
  `information_title` varchar(100) NOT NULL,
  `information_subtitle` varchar(100) NOT NULL,
  `information_desc` text NOT NULL,
  `information_type` varchar(50) NOT NULL,
  `information_size` varchar(50) NOT NULL,
  `information_section` varchar(100) NOT NULL,
  `information_category` tinyint(4) NOT NULL,
  `time_insert` int(11) NOT NULL,
  `time_update` int(11) NOT NULL,
  `time_delete` int(11) NOT NULL,
  `information_url_type` tinyint(4) NOT NULL,
  `information_url_upload` varchar(255) NOT NULL,
  `information_url_editor` varchar(255) NOT NULL,
  `information_image` varchar(255) NOT NULL,
  `information_channel` int(11) NOT NULL,
  PRIMARY KEY (`information_id`),
  UNIQUE KEY `information_id` (`information_id`),
  KEY `information_id_2` (`information_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `information_channel` (
  `channel_id` int(11) NOT NULL AUTO_INCREMENT,
  `channel_name` varchar(100) NOT NULL,
  `channel_sort` int(11) NOT NULL,
  `time_insert` int(11) NOT NULL,
  `time_update` int(11) NOT NULL,
  `time_delete` int(11) NOT NULL,
  PRIMARY KEY (`channel_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `information_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `information_id` int(11) NOT NULL,
  `time_insert` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `information_suggest` (
  `suggest_id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `information_category` tinyint(4) NOT NULL,
  `suggest_content` text NOT NULL,
  `suggest_state` int(11) NOT NULL DEFAULT '0',
  `suggest_resolve` tinyint(4) NOT NULL,
  `time_insert` int(11) NOT NULL,
  PRIMARY KEY (`suggest_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `information_suggest_votes` (
  `votes_id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `suggest_id` int(11) NOT NULL,
  `time_insert` int(11) NOT NULL,
  PRIMARY KEY (`votes_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `information` (`information_id`, `information_title`, `information_subtitle`, `information_desc`, `information_type`, `information_size`, `information_section`, `information_category`, `time_insert`, `time_update`, `time_delete`, `information_url_type`, `information_url_upload`, `information_url_editor`, `information_image`, `information_channel`) VALUES
(2, '企业管理技巧', '一些感悟', '11', 'doc', '0.02', '', 1, 1433832835, 0, -1, 1, '', 'http://huiyi.qeebu.cn/teamin/Public/Uploads/information/55768d8324819.doc', '', 2),
(3, '企业管理技巧浅析', '', '', 'doc', '0.02', '', 1, 1433833705, 0, -1, 1, '', 'http://huiyi.qeebu.cn/teamin/Public/Uploads/information/557690e98a4b3.doc', '', 2),
(4, '如何提高电话销售技巧', '', '', 'doc', '0.01', '', 1, 1433834437, 0, -1, 1, '', 'http://huiyi.qeebu.cn/teamin/Public/Uploads/information/557693c5c2e89.doc', '', 3),
(5, '服装销售心理学', '', '', 'doc', '0.03', '', 1, 1433834471, 0, -1, 1, '', 'http://huiyi.qeebu.cn/teamin/Public/Uploads/information/557693e747407.doc', '', 3),
(6, '组织架构形态', '', '', 'mp4', '13.58', '', 1, 1433843763, 0, -1, 1, '', 'http://huiyi.qeebu.cn/teamin/Public/Uploads/information/5576b833ac235.mp4', '', 9);

INSERT INTO `information_channel` (`channel_id`, `channel_name`, `channel_sort`, `time_insert`, `time_update`, `time_delete`) VALUES
(2, '企业管理', 1, 1433832480, 0, -1),
(3, '销售技巧', 2, 1433834413, 0, -1),
(9, '组织架构', 3, 1433843545, 0, -1);

CREATE TABLE IF NOT EXISTS `leave` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `audience_id` int(11) NOT NULL,
  `approval_uid` int(11) NOT NULL,
  `start_time` int(11) NOT NULL,
  `end_time` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `reason` text NOT NULL,
  `approval_reason` text NOT NULL,
  `state` tinyint(1) NOT NULL,
  `time_insert` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `leave_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `leave_state` int(11) NOT NULL,
  `leave_id` int(11) NOT NULL,
  `back_time` int(11) NOT NULL DEFAULT '0',
  `leave_content` text NOT NULL,
  `time_update` int(11) NOT NULL,
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `leave_personnel_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `personnel_id` int(11) NOT NULL,
  `leave_state` int(11) NOT NULL,
  `leave_id` int(11) NOT NULL,
  `back_time` int(11) NOT NULL DEFAULT '0',
  `leave_content` text NOT NULL,
  `time_update` int(11) NOT NULL,
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `leave_prove` (
  `prove_id` int(11) NOT NULL AUTO_INCREMENT,
  `leave_id` int(11) NOT NULL,
  `prove_image_path` varchar(255) NOT NULL,
  PRIMARY KEY (`prove_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `leave_type` (
  `type_id` int(11) NOT NULL AUTO_INCREMENT,
  `type_name` varchar(50) NOT NULL,
  PRIMARY KEY (`type_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

INSERT INTO `leave_type` (`type_id`, `type_name`) VALUES
(1, '事假'),
(2, '病假'),
(3, '产假'),
(4, '年假'),
(5, '婚假'),
(6, '丧假'),
(7, '护理假');

CREATE TABLE IF NOT EXISTS `meeting` (
  `meeting_id` int(11) NOT NULL AUTO_INCREMENT,
  `meeting_title` varchar(255) NOT NULL,
  `meeting_sponsorsId` int(11) NOT NULL,
  `meeting_description` text NOT NULL,
  `meeting_startTime` int(11) NOT NULL,
  `meeting_endTime` int(11) NOT NULL,
  `meeting_local` varchar(255) NOT NULL,
  `meeting_local_type` tinyint(1) NOT NULL DEFAULT '0',

  `meeting_room_id` int(11) NOT NULL,
  `meeting_state` tinyint(1) NOT NULL DEFAULT '1',
  `meeting_type` tinyint(1) NOT NULL DEFAULT '0',
  `meeting_date` date NOT NULL,
  `time_insert` int(11) NOT NULL,
  `time_update` int(11) NOT NULL DEFAULT '0',
  `meeting_category` int(11) NOT NULL,
  `meeting_join_type` tinyint(4) NOT NULL DEFAULT '2',
  `group_id` int(11) NOT NULL,
  PRIMARY KEY (`meeting_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `meeting_group` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(200) NOT NULL COMMENT '组名',
  PRIMARY KEY (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
insert into `meeting_group`(`group_id`,`group_name`) values('1','default');


CREATE TABLE IF NOT EXISTS `meeting_category` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  `category_pid` int(11) NOT NULL,
  `category_sort` tinyint(4) NOT NULL,
  `time_insert` int(11) NOT NULL,
  `time_update` int(11) NOT NULL,
  `time_delete` int(11) NOT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

INSERT INTO `meeting_category` (`category_id`, `category_name`, `category_pid`, `category_sort`, `time_insert`, `time_update`, `time_delete`) VALUES
(1, 'default', 0, 1, 0, 0, 1);


CREATE TABLE IF NOT EXISTS `meeting_join` (
  `join_id` int(11) NOT NULL AUTO_INCREMENT,
  `meeting_id` int(11) NOT NULL,
  `audience_id` int(11) NOT NULL,
  `join_state` tinyint(1) NOT NULL,
  `join_content` text NOT NULL,
  `is_secret` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`join_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `meeting_room` (
  `room_id` int(11) NOT NULL AUTO_INCREMENT,
  `floor_id` int(11) NOT NULL,
  `room_name` varchar(100) NOT NULL,
  `room_allSeats` int(11) NOT NULL,
  `room_haveWifi` tinyint(1) NOT NULL DEFAULT '0',
  `room_haveProjector` tinyint(1) NOT NULL DEFAULT '0',
  `room_description` text NOT NULL,
  PRIMARY KEY (`room_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


INSERT INTO `meeting_room` (`room_id`, `floor_id`, `room_name`, `room_allSeats`, `room_haveWifi`, `room_haveProjector`, `room_description`) VALUES
(1, 1, 'room_name', 0, 0, 0, 'room_desc');


CREATE TABLE IF NOT EXISTS `meeting_roombuilding` (
  `building_id` int(11) NOT NULL AUTO_INCREMENT,
  `local_id` int(11) NOT NULL,
  `building_name` varchar(100) NOT NULL,
  PRIMARY KEY (`building_id`,`local_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;



INSERT INTO `meeting_roombuilding` (`building_id`, `local_id`, `building_name`) VALUES
(1, 1, 'building_name');


CREATE TABLE IF NOT EXISTS `meeting_roomfloor` (
  `floor_id` int(11) NOT NULL AUTO_INCREMENT,
  `building_id` int(11) NOT NULL,
  `floor_name` varchar(100) NOT NULL,
  PRIMARY KEY (`floor_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;



INSERT INTO `meeting_roomfloor` (`floor_id`, `building_id`, `floor_name`) VALUES
(1, 1, 'floor_name');


CREATE TABLE IF NOT EXISTS `meeting_roomlocal` (
  `local_id` int(11) NOT NULL AUTO_INCREMENT,
  `local_name` varchar(100) NOT NULL,
  PRIMARY KEY (`local_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;



INSERT INTO `meeting_roomlocal` (`local_id`, `local_name`) VALUES
(1, 'name');



CREATE TABLE IF NOT EXISTS `meeting_roomschedule` (
  `schedule_id` int(11) NOT NULL AUTO_INCREMENT,
  `room_id` int(11) NOT NULL,
  `schedule_date` date NOT NULL,
  `schedule_maxstep` tinyint(2) NOT NULL,
  `schedule_0` tinyint(1) NOT NULL,
  `schedule_1` tinyint(1) NOT NULL,
  `schedule_2` tinyint(1) NOT NULL,
  `schedule_3` tinyint(1) NOT NULL,
  `schedule_4` tinyint(1) NOT NULL,
  `schedule_5` tinyint(1) NOT NULL,
  `schedule_6` tinyint(1) NOT NULL,
  `schedule_7` tinyint(1) NOT NULL,
  `schedule_8` tinyint(1) NOT NULL,
  `schedule_9` tinyint(1) NOT NULL,
  `schedule_10` tinyint(1) NOT NULL,
  `schedule_11` tinyint(1) NOT NULL,
  `schedule_12` tinyint(1) NOT NULL,
  `schedule_13` tinyint(1) NOT NULL,
  `schedule_14` tinyint(1) NOT NULL,
  `schedule_15` tinyint(1) NOT NULL,
  `schedule_16` tinyint(1) NOT NULL,
  `schedule_17` tinyint(1) NOT NULL,
  `schedule_18` tinyint(1) NOT NULL,
  `schedule_19` tinyint(1) NOT NULL,
  `schedule_20` tinyint(1) NOT NULL,
  `schedule_21` tinyint(1) NOT NULL,
  `schedule_22` tinyint(1) NOT NULL,
  `schedule_23` tinyint(1) NOT NULL,
  `schedule_24` tinyint(1) NOT NULL,
  PRIMARY KEY (`schedule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `news` (
  `news_id` int(11) NOT NULL AUTO_INCREMENT,
  `news_title` varchar(100) NOT NULL,
  `news_image` varchar(255) NOT NULL,
  `news_image_s` varchar(255) NOT NULL,
  `news_url` varchar(255) NOT NULL,
  `news_author` varchar(100) NOT NULL,
  `news_intro` varchar(100) NOT NULL,
  `news_content` text NOT NULL,
  `news_time` int(11) NOT NULL,
  `news_mark` tinyint(1) NOT NULL,
  `news_sort` int(11) NOT NULL,
  `news_display` tinyint(4) NOT NULL,
  `news_readNum` int(11) NOT NULL,
  `news_category` tinyint(4) NOT NULL DEFAULT '1',
  `news_channel` int(11) NOT NULL DEFAULT '1',
  `news_publish_time` int(11) NOT NULL,
  PRIMARY KEY (`news_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

INSERT INTO `news` (`news_id`, `news_title`, `news_image`, `news_image_s`, `news_url`, `news_author`, `news_intro`, `news_content`, `news_time`, `news_mark`, `news_sort`, `news_display`, `news_readNum`, `news_category`, `news_channel`, `news_publish_time`) VALUES
(1, '中国成为新兴市场贸易下滑主因', '', '', 'Public/Uploads/news/1161/14338318718873.html', '英国《金融时报》 金奇', 'nbsp; nbsp; nbsp; 今年新兴市场贸易额下滑已', '<p class="dropcap">\r\n	&nbsp; &nbsp; &nbsp; 今年新兴市场贸易额下滑已经成为一种主要由中国造成的趋势。当然，中国本身的下滑数据压低了整个新兴市场的平均水平，但影响还不止于此。中国对大宗商品的需求日益降低，而同时其制造业进口也开始直线下滑。\r\n</p>\r\n<p>\r\n	&nbsp; &nbsp; &nbsp; 研究公司牛津经济(Oxford Economics)\r\n</p>\r\n<p>\r\n	&nbsp; &nbsp; &nbsp;&nbsp;计算得出，2012年至2014年间中国每年对全球商品贸易增长的提升约为0.5个百分点，而今年第一季度则拉低了大概0.7个百分点（见图表）。\r\n</p>\r\n<p>\r\n	<img align="right" src="http://i.ftimg.net/picture/2/000052332_piclink.jpg" width="1" height="1" />\r\n</p>\r\n<p>\r\n	这标志着，自2008/09金融危机以来（除了2012年的偶然情况），中国对全球贸易增长首次产生拉低影响。中国也与美国形成对比，后者在今年第一季度将全球贸易增长提升了0.7个百分点。\r\n</p>\r\n<p>\r\n	&nbsp; &nbsp; &nbsp; 分析人士称，中国因素将对可预见的未来产生重要影响。“主要由于中国贸易疲软，我们对全球贸易增长的预测也大幅降低，”牛津经济的经济学家亚当•斯莱特(Adam \r\nSlater)称。\r\n</p>\r\n<p>\r\n	&nbsp; &nbsp; &nbsp;&nbsp;“如今我们预计今年全球贸易将仅增长2.6%，较2014年的4%有所下滑，也仅是长期平均增速的一半左右。的确，这样的全球贸易预测增速，似乎更像是全球衰退时期、而非复苏时期的预测增速，”他补充道。\r\n</p>\r\n<p>\r\n	&nbsp; &nbsp; &nbsp; 本周公布的5月韩国出口数据是中国负面影响的最新体现。数据表明，韩国（出口导向型经济）的出口额下滑了10.9%，这是6年来首次出现两位数降幅。韩国贸易部在声明中称，随着中国公司将其中间产品供应本地化，韩国对华出口连续第4个月下滑。\r\n</p>\r\n<p>\r\n	尽管中国对铁矿石、铜等大宗商品的需求低迷已是既成事实，但制造业进口下滑则是最新出现的趋势。牛津经济计算的中国制造业进口额3个月移动平均值显示，3月数值同比下滑24%（见图表）。\r\n</p>\r\n<p>\r\n	<img align="right" src="http://i.ftimg.net/picture/3/000052333_piclink.jpg" width="1" height="1" />\r\n</p>\r\n<p>\r\n	&nbsp; &nbsp; &nbsp; 这不仅导致了德国和美国投资品的订单下降，也加剧了最近几个月新兴市场出口下滑的趋势。瑞银(UBS)策略师巴努•巴韦贾(Bhanu \r\nBaweja)用瑞银最新的3个月移动平均数据展示了，在挑选出来的34个新兴市场中，所有经济体均出现进口下滑，同时只有2个地区（中国内地和香港）呈现出口增长。\r\n</p>\r\n<p>\r\n	&nbsp; &nbsp; &nbsp;&nbsp;总的来说，官方数据显示，今年3月新兴市场出口额同比下滑13.5%，与2月的同比下滑1.7%相比，降幅大幅加大，创下自2008/09金融危机以来的最大降幅。据凯投宏观(Capital \r\nEconomics)收集的初始数据显示，新兴市场出口疲软的情况一直持续至4月（如果韩国的表现也是某种预兆的话，也许这种情况还会持续到5月）。\r\n</p>\r\n<p>\r\n	&nbsp; &nbsp; &nbsp; 这一趋势很大一部分可以归咎于价值效应：大宗商品和石油价格降低，压低了新兴市场的出口商品价值，同时美元走强对那些货币相对美元贬值的新兴国家的出口商品价值形成下压。不过，还有更多的结构性不利因素也在发挥作用。\r\n</p>\r\n<p>\r\n	简单来说，推动新兴市场国家发展了十几年的活力正在消失。巴韦贾称，基于瑞银66个国家样本的数据，新兴市场的国内生产总值(GDP)增长率中值接近3%，与2001-2002年衰退时期的水平类似。巴韦贾称，以加权平均值来看，新兴市场GDP增长率于2014年中期降至4%以下，并于今年第一季度进一步下滑至3.5%。\r\n</p>\r\n<p>\r\n	&nbsp; &nbsp; &nbsp;&nbsp;斯莱特也表达了对新兴市场下滑可能会对全球经济产生影响的担忧。“全球经济过去成功摆脱过新兴市场危机和制造业增长疲软时期的影响，但是这一次做到这一点可能没那么容易，这是因为如今通过提高私营部门杠杆来刺激需求的空间较小，”他称。\r\n</p>\r\n<p>\r\n	该警告颇为中肯，因为上一次新兴市场对全球经济造成冲击时——上世纪90年代末的亚洲金融危机——以市场汇率计算，新兴市场占全球GDP的比重仅为23%。如今，这一比例为35%。\r\n</p>', 1433831871, 0, 0, 1, 0, 1, 2, 1433824200),
(2, '金融世界过度膨胀的先兆', 'Public/Uploads/news/1161/m_55768a94a039c.jpg', 'Public/Uploads/news/1161/s_55768a94a039c.jpg', 'Public/Uploads/news/1161/14338320848367.html', '英国《金融时报》 吉莲•邰蒂', '美国企业上周跨越了一座里程碑。Dealogic的数据显示，美', '<p class="dropcap">\r\n	美国企业上周跨越了一座里程碑。Dealogic的数据显示，美国并购交易价值在今年5月达到2430亿美元，创下单月最高纪录。上两次的单月最高纪录分别出现在2007年5月和2000年1月，签署的协议价值分别为2260亿美元和2130亿美元。\r\n</p>\r\n<p>\r\n	与此同时，今年以来全球签署的并购协议价值达到1.85万亿美元，如果持续这种趋势的话，2015年全年并购交易价值将超过2007年创造的4.6万亿美元的最高年度纪录。\r\n</p>\r\n<p>\r\n	政策制定者和投资者应该据此得出什么结论？如果你想乐观一点，你可以将此视为动物精神值得欢迎的回归。2008年金融危机爆发后最初那段时间，企业高管受到了如此大创伤，也感到如此恐慌，因此他们把削减企业债务和成本放在第一位。但现在那些头衔里挂着“首席”两个字的企业高管(C-suite)更乐于进取了。尤其引人瞩目的是，当前的并购热潮不是像科技热时期那样，仅席卷了一个行业，而是席卷了包括零售、石油、制药和科技在内的众多行业。\r\n</p>\r\n<p>\r\n	但对这种趋势的另一种较为悲观的解读是：西方金融体系正淹没在过多的现金和信贷当中。历史经验显示，此类境况很少会有好的结果。毕竟，关于前两次并购交易高潮的一个关键事实是，它们正好与股票和信贷出现泡沫的时间重合。在前两次并购交易创出最高纪录后不久，那些泡沫都破裂了，并带来了令人痛苦的后果。\r\n</p>\r\n<p>\r\n	此外，当前并购热潮的背景令人不安。自2008年金融危机以来，美国企业利润飙升至创纪录的水平：例如，高盛(Goldman \r\nSachs)估计，（不包括金融和公用事业企业在内的）标普500成分股企业的利润率大约为9%。在正常情况下，这理应引发企业投资热潮，实际上，这也是政策制定者一直渴望看到的。\r\n</p>\r\n<p>\r\n	但投资热潮并未出现。相反，高盛表示，投资占营业现金流的比例在过去5年里实际上已从29%下降至23%，这显然是因为企业对增长前景感到悲观。\r\n</p>\r\n<p>\r\n	这意味着他们需要为资金找到其他用途。许多公司拿出大量资金用于股份回购：贝莱德(BlackRock)表示，今年4月，标普500成分股公司用于股份回购上的资金达到创纪录的1330亿美元。他们也派发了丰厚的股息。实际上，分析师预计，今年美国企业用于红利派息和股份回购的资金总额将会超过1万亿美元，高于预计的营运和研发支出。\r\n</p>\r\n<p>\r\n	但不出所料的是，资金也在流入并购交易，因为企业高管寻求通过引人瞩目的方法来表明他们不白拿薪水。\r\n</p>\r\n<p>\r\n	一些银行家辩称，这种策略并不坏，至少不比其他一些糟糕选择更坏。高盛的戴维•科斯京(David \r\nKostin)在一份写给客户的报告中表示：“在我们看来，鉴于美国股市当前估值过高，把资金用于收购——尤其是以股票协议的形式收购——比用于回购更具战略说服力。”\r\n</p>\r\n<p>\r\n	在政策制定者看来，问题在于，这股突然迸发的并购热潮不太可能大举推动经济增长。从2000年和2007年的情况来看，它也不会为股东带来多少长远价值。更令人不安的是，尽管并购最初是由多余的现金和股票提供的融资，但现在它似乎更多地也是通过举债融资。例如，上周Dealogic还披露称，2015年企业债券发行量达到了创纪录的5434亿美元。这让投资级企业的债务股本率达到了85%，而2010年的这一比例是72%。\r\n</p>\r\n<p>\r\n	<p class="dropcap">\r\n		这一并购热潮的策略者、从事并购业务的银行业人士坚称，2015年将不同于2000年或者2007年，因为首席执行官们从过去发生的事情中汲取了应有的教训，只追逐那些具有战略价值的并购。至少，他们给出的理由是这样的。另外，一些政策观察人士辩称，随着美国加息那一天日趋临近，这种并购热潮将在今年晚些时候自然而然地冷却下来。谁知道呢？如果经济继续增长，公司可能最终拿出他们当前用于并购或者回购的劲头来投资。\r\n	</p>\r\n	<p>\r\n		但是别抱指望。更可能的结果是，当未来的历史学家回首2015年5月的时候，这个2430亿美元的最高纪录将会被视为金融世界过度膨胀的先兆。这是一个量化宽松让资产所有者变得富裕（更别说让并购咨询顾问发大财）的时代。但它没有让公司相信经济前景欣欣向荣，也不足以说服它们投资。这确实令人担忧。\r\n	</p>\r\n</p>', 1433832084, 0, 0, 1, 0, 1, 2, 1433826000),
(3, '希拉里不再唱独角戏', 'Public/Uploads/news/1161/m_55768b634592d.png', 'Public/Uploads/news/1161/s_55768b634592d.png', 'Public/Uploads/news/1161/14338322914129.html', '英国《金融时报》专栏作家 爱德华•卢斯', '突然之间，希拉里•克林顿(Hillary Clinton)的', '<p class="dropcap">\r\n	突然之间，希拉里•克林顿(Hillary \r\nClinton)的选战路上不再孤单。一个月前，角逐民主党总统候选人提名的只有她一个——多数观察人士揣测她将直登总统宝座。如今，民主党内参与角逐候选人资格的已经有四位了，也许很快就会有第五人加入战团。很难想象希拉里的哪一个对手能打败她。民调显示，与希拉里最接近的伯尼•桑德斯(Bernie \r\nSanders)，支持率与她也有50个百分点的差距。然而，形势每一刻都在变。周二，《华盛顿邮报》(Washington \r\nPost)和美国广播公司(ABC)公布的民意测验结果显示，希拉里的支持率自2008年以来首次跌破50%。她的支持率仍然高于包括杰布•布什(Jeb \r\nBush)在内的潜在共和党对手。不过这样的发展势头却令人烦恼。\r\n</p>\r\n<p>\r\n	那么，希拉里是不是有麻烦了？如果以竞争对手们的获胜几率来看，这个问题的答案是否定的。最新宣布参选的民主党人林肯•查菲(Lincoln \r\nChafee)是最不可能获胜的一位。查菲曾是东北部“洛克菲勒共和党人”(Rockefeller \r\nRepublican)最后的少数成员之一。2007年竞选罗得岛州长时，查菲转为独立候选人。在做了一任州长之后，他于2013年加入民主党。他的成名源于他是唯一反对美国2003年入侵伊拉克的共和党参议员，而希拉里则支持这一战争。不过后来希拉里转变了立场。查菲最独树一帜的观点是他希望将美国度量衡单位从英制转为公制。他因这一主张而受到了嘲笑，然而他表示不会退让半步。他的支持率令他差一点无法被统计到民调中。\r\n</p>\r\n<p>\r\n	理论上，曾任马里兰州长和巴尔的摩市长的马丁•奥马利(Martin \r\nO''Malley)应该会给希拉里造成更大威胁。对于那些不信任希拉里或认为希拉里代表着倒退的民主党选民来说，相对年轻（现年52岁）、精力充沛、奉行自由派观点的奥马利拥有的资历很有吸引力。不过，上个月巴尔的摩发生的骚乱，令奥马利的履历蒙上了污点。骚乱的起因是一名年轻黑人男子死在了警察手中。虽然奥马利在近十年前就已卸任巴尔的摩市长一职，但这座城市如今会成为“城市分裂”(urban \r\nbreakdown)的代名词，奥马利很难摆脱干系。\r\n</p>\r\n<p>\r\n	弗吉尼亚州前参议员、作家吉姆•韦布(Jim Webb)同样没胜算。周三，他说自己“尚未宣布”参选，但在乔治梅森大学(George Mason \r\nUniversity)的一次演讲中，他已经明确表示计划参选。他可能会吸引大量蓝领“里根民主党人”(Reagan \r\nDemocrats)，像往常一样，这些人已对政治丧失兴趣。韦布在民调中的支持率也低到几乎可以忽略不计。只有桑德斯的支持率上了两位数，他是当世人记忆中第一位真正要参与角逐总统宝座的美国社会主义者。然而，很难想象民主党人会选择一位70多岁的社会主义者作为他们的总统候选人。那么希拉里还有什么可担心的呢？\r\n</p>\r\n<p>\r\n	<p>\r\n		她面临三个问题。首先，美国媒体渴望扳倒她。“希拉里阵营”与媒体记者的关系非常糟糕，自今年4月宣布竞选总统以来，她几乎回避了与媒体的所有互动。周三，南卡罗莱纳州共和党参议员、已经宣布参选的林塞•格雷厄姆(Lindsey \r\nGraham)将希拉里与金正恩(Kim Jong Un)相提并论。他在福克斯新闻频道(Fox \r\nNews)讥讽地说：“好吧，现在与那个朝鲜人对话要比跟她对话还容易。”\r\n	</p>\r\n	<p>\r\n		其次，超过一半的美国人表示希拉里“不值得信任”。但多数人也认为她是一位“强有力的领导者”。第一点是否会破坏第二点还不得而知。最后一个问题是，民主党的竞选阵营仍可能有新加入者。如果副总统乔•拜登(Joe \r\nBiden)加入竞选，可能会大大改变选情。美国有线电视新闻网(CNN)最近的一项调查显示在他未宣布参选的情况下，他的支持率为14%。希拉里获得提名目前看起来仍毫无悬念，但现在开始有点比赛的感觉了。\r\n	</p>\r\n</p>', 1433832291, 0, 0, 1, 0, 1, 2, 1433831400),
(4, '万达电商做开放式O2O，布局有妙招', '', '', 'Public/Uploads/news/1161/14338374274220.html', '天下金融网', '互联网发展到今天可以说是到了一个比较成熟的阶段。对于新进的互', '互联网发展到今天可以说是到了一个比较成熟的阶段。对于新进的互联网企业来说，如果没有一个良好的布局，将难以在行业内长久的发展下去。最近备受关注的万达电商，其在O2O开放式平台的建立上可谓是开创了先路。<br />\r\n　　自总理提出“互联网+”后，O2O领域的战争进一步升级。万达电商从创立以来一直致力于打造一个开放式的O2O平台，为用户提供便捷的消费体验。现如今，越来越多的企业正在开始涉足电商O2O领域，出现了众多的O2O平台，O2O前路漫漫，万达电商上下求索找到的这条能否引领该行业呢?<br />\r\n　　相比其他O2O平台而言，万达电商的布局就更显智慧和人性化，基于万达自身线下资源优势—丰富的线下业态资源、109座万达广场商业中心、每年超过16亿人次的客流量。通过线上线下完全融合的立体方式，全面采用智能设备和技术，为广大消费人群提供以体验式服务为主的一站式O2O开放式电商平台。<br />\r\n　　万达电商打通的不只平台入口，还要做消费者的大会员管理。对于消费者而言，万达要做的大会员就是通过一张会员卡来承载用户从身份到积分到支付的各个环节，进而实现无缝衔接的顺畅体验。此外，无论在万达的哪个业态版块消费，都可以通过万达电商的支付体系完成支付，其价值都将累计，并且转化为统一的身份标识和跨行业的、开放式的积分，可谓最大化的放大了消费的价值。<br />\r\n　　而这些智慧服务正切合了消费者日益升级的消费需求，他们需要更有体验感、更能提供便捷服务的消费新模式，可以说万达电商触及到了O2O真正的核心—消费的互动体验，这无疑给了消费者足够的参与理由。<br />\r\n　　万达电商的布局是凭借自身独特的优势而作出的，其他企业即使模仿恐怕也难以复制其成功之路。作为新兴的电商企业，万达电商在带给用户惊喜的同时也承担着用户所寄予的更大期望。从现有的情况可以了解到，万达电商对自身的长久发展充满了信心，业界对其未来的成长也表示了认可。', 1433837427, 0, 0, 1, 0, 1, 3, 1433923200),
(5, '苹果等巨头们纷纷搞起了“新闻”，又如何？内容商很难因此而致富', 'Public/Uploads/news/1161/m_55769fea2842e.png', 'Public/Uploads/news/1161/s_55769fea2842e.png', 'Public/Uploads/news/1161/14338375463704.html', '匿名', '苹果在刚刚结束的发布会上推出了一款类似Flipboard 式', '<div>\r\n	<p>\r\n		苹果在刚刚结束的发布会上推出了一款类似Flipboard \r\n式的新闻聚合类应用——News（新闻），这款应用的诞生同时也意味着另一款原生应用将退下历史舞台，它就是苹果的报刊杂志（Newsstand）。苹果公司2011年推出了报摊性质的软件Newsstand，这一工具并不提供新闻内容，而是让用户购买各种数字新闻产品，下载到本地在进行阅读。\r\n	</p>\r\n	<p>\r\n		<br />\r\n	</p>\r\n	<p>\r\n		而全新的News应用将会从全世界的新闻出版商中收集内容，用户则可以对文章等显示方式进行自定义。第一步，News \r\napp将在美国、澳大利亚和英国推出。\r\n	</p>\r\n	<p>\r\n		<br />\r\n	</p>\r\n	<p>\r\n		苹果高管埃迪·库伊在发布会上表示：“News应用将会通过一种美观和简洁的方式向用户传达文章等内容，同时尊重你的隐私，因为苹果不会分享你的个人数据。”\r\n	</p>\r\n	<p>\r\n		<br />\r\n	</p>\r\n	<p>\r\n		苹果表示，虽然新闻内容存放于苹果，但是媒体机构将拥有新闻内容、掌握文章显示的控制权。外部媒体也可以该客户端中的广告位，从而获取收入。如果外部媒体独立销售广告位，媒体将获得全部广告收入。如果广告位由苹果系统来销售，苹果将进行提成，不过具体提成的比例还不得而知。\r\n	</p>\r\n	<p>\r\n		<br />\r\n	</p>\r\n	<p>\r\n		苹果这一步给我们带来什么样的启示呢？\r\n	</p>\r\n	<p>\r\n		<br />\r\n	</p>\r\n	<p>\r\n		Medium作者<a href="https://medium.com/the-backlight/quick-thoughts-on-apple-s-announcements-at-wwdc-b51c9e2cf53d" target="_self">Tim Carmody这么说</a>：\r\n	</p>\r\n	<p>\r\n		<br />\r\n	</p>\r\n	<blockquote>\r\n		<p>\r\n			2015年越看越像是一个内容分发之年。不单像Facebook与Snapchat这样的社交平台在对内容感兴趣，设备平台们也在侧身其中。这倒不是说Apple \r\nNews一定能成功，之前跟它有类似的Google \r\nCurrents也没做起来（虎嗅注：Google官方正式宣布关闭Android平台的Currents新闻阅读应用，现有应用用户将转移到Google最新推出Google \r\nPlay \r\nNewsstand应用上。这款应用最早亮相于2011年的12月份）。但是这个趋势很明显，独立的新闻应用与新闻品牌正在融入这个那个更加大的平台。而出版商丧失权力后换回来的也很明显：大量的利益亟待开发。\r\n		</p>\r\n		<p>\r\n			<br />\r\n		</p>\r\n		<p>\r\n			我认为这个市场才刚刚打开，今后会有各种不同可能的内容解决方案。现在可以说，新闻与音乐、地图一样，正在成为移动设备公司操作系统级别的基础内容。\r\n		</p>\r\n		<p>\r\n			<br />\r\n		</p>\r\n		<p>\r\n			需要指出的是，新闻阅读自RSS以来最大的创新是在社会化阅读推荐方面，比如Flipboard、Facebook \r\nPaper等，它们都是基于他们自己的社会化推荐系统与分享链接来做。目前，还看不出苹果打算从社交这个维度去服务它的新闻读者。我认为这会是一个问题。人们不想再读更多的东西了，他们希望看到一些新的、关联性强的新闻然后他们可以分享给其他人并进一步产生讨论。\r\n		</p>\r\n		<p>\r\n			<br />\r\n		</p>\r\n		<p>\r\n			这是2015年，社交网络已成为新闻中不可分割的一部分。\r\n		</p>\r\n	</blockquote>\r\n	<p>\r\n		<br />\r\n	</p>\r\n	<p>\r\n		苹果此举，直接侵入Flipboard的腹地，是否对后者的商业利益产生直接影响还不得而知。而苹果也只是追随Facebook的步伐。今年5月，Facebook发布了新的新闻服务。出版商们可以通过Facebook \r\nInstant \r\nArticles服务，将它们的文章直接发布到Facebook的iOS应用中，这就意味着它们将这些内容提供给用户们的速度将比通过自家网站提供的速度更快。如果外部媒体独立销售广告位，其可以拿走百分之百收入，如果由Facebook来帮助销售广告位，媒体可获得七成。\r\n	</p>\r\n	<p>\r\n		<br />\r\n	</p>\r\n	<p>\r\n		Facebook、苹果相继深度整合媒体内容，并利用其流量与广告资源与媒体内容商分享利益。这看上去已是一股非常明显的趋势，想来会很快影响到国内各大平台商对内容的思考。内容因此有救了吗？悬。国内的今日头条APP或一度劲头很足的搜狐新闻客户端都声称对内容商有类似的政策，但你看哪家媒体或自媒体因此而过上了衣食无忧的生活？\r\n	</p>\r\n</div>', 1433837546, 0, 0, 1, 0, 1, 3, 1433898660);




CREATE TABLE IF NOT EXISTS `news_channel` (
  `channel_id` int(11) NOT NULL AUTO_INCREMENT,
  `channel_name` varchar(100) NOT NULL,
  `channel_sort` int(11) NOT NULL,
  `time_insert` int(11) NOT NULL,
  `time_update` int(11) NOT NULL,
  `time_delete` int(11) NOT NULL,
  PRIMARY KEY (`channel_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

INSERT INTO `news_channel` (`channel_id`, `channel_name`, `channel_sort`, `time_insert`, `time_update`, `time_delete`) VALUES
(2, '奇步新闻', 1, 1433844853, 1433844853, 0),
(3, '互联网新闻', 2, 1433844853, 1433844853, 0);


CREATE TABLE IF NOT EXISTS `news_comment` (
  `comment_id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `news_id` int(11) NOT NULL,
  `time_insert` int(11) NOT NULL,
  `content` text CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`comment_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `news_votes` (
  `votes_id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `news_id` int(11) NOT NULL,
  `time_insert` int(11) NOT NULL,
  PRIMARY KEY (`votes_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `notice` (
  `notice_id` int(11) NOT NULL AUTO_INCREMENT,
  `notice_title` varchar(255) NOT NULL,
  `notice_detail` text NOT NULL,
  `timestamp` int(11) NOT NULL,
  PRIMARY KEY (`notice_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `project` (
  `project_id` int(11) NOT NULL AUTO_INCREMENT,
  `project_name` varchar(255) NOT NULL,
  `project_description` text NOT NULL,
  `project_startTime` int(11) NOT NULL,
  `project_endTime` int(11) NOT NULL,
  `project_leader` int(11) NOT NULL,
  `time_insert` int(11) NOT NULL,
  `time_update` int(11) NOT NULL,
  `time_delete` int(11) NOT NULL,
  PRIMARY KEY (`project_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;



INSERT INTO `project` (`project_id`, `project_name`, `project_description`, `project_startTime`, `project_endTime`, `project_leader`, `time_insert`, `time_update`, `time_delete`) VALUES
(1, 'name', 'desc', 0, 0, 1, 0, 0, 0);


CREATE TABLE IF NOT EXISTS `push` (
  `push_id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` tinyint(4) NOT NULL,
  `log_id` int(11) NOT NULL,
  `time_insert` int(11) NOT NULL,
  PRIMARY KEY (`push_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `questionnaire` (
  `questionnaire_id` int(11) NOT NULL AUTO_INCREMENT,
  `questionnaire_title` varchar(255) NOT NULL,
  `questionnaire_publish` int(11) NOT NULL DEFAULT '1',
  `questionnaire_detail` text NOT NULL,
  `questionnaire_state` tinyint(4) NOT NULL,
  `questionnaire_end_time` int(11) NOT NULL,
  PRIMARY KEY (`questionnaire_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `questionnaire_audience_answer` (
  `as_id` int(11) NOT NULL AUTO_INCREMENT,
  `audience_id` int(11) NOT NULL DEFAULT '0',
  `questionnaire_id` int(11) NOT NULL DEFAULT '0',
  `question_id` int(11) NOT NULL DEFAULT '0',
  `answer` varchar(255) NOT NULL,
  `other` varchar(255) NOT NULL,
  PRIMARY KEY (`as_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `questionnaire_question` (
  `question_id` int(11) NOT NULL AUTO_INCREMENT,
  `questionnaire_id` int(11) NOT NULL,
  `question_title` varchar(100) NOT NULL,
  `question_type` int(11) NOT NULL,
  PRIMARY KEY (`question_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `questionnaire_question_opt` (
  `opt_id` int(11) NOT NULL AUTO_INCREMENT,
  `question_id` int(11) NOT NULL,
  `opt` varchar(2) NOT NULL,
  `answer` varchar(255) NOT NULL,
  PRIMARY KEY (`opt_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `question_theme` (
  `theme_id` int(11) NOT NULL AUTO_INCREMENT,
  `theme_title` varchar(255) NOT NULL,
  `theme_intro` text NOT NULL,
  `end_time` int(11) NOT NULL,
  `sponsor_id` int(11) NOT NULL,
  PRIMARY KEY (`theme_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `questionnaire` (`questionnaire_id`, `questionnaire_title`, `questionnaire_publish`, `questionnaire_detail`, `questionnaire_state`, `questionnaire_end_time`) VALUES
(2, '工作环境与工作内容调查', 1, '针对企业员工的调查', 1, 1451491200),
(3, '员工工作满意度调查', 1, '调查企业员工对企业的满意度', 1, 1451491200),
(4, '企业管理者调查', 1, '调查企业管理者是否关心企业员工', 1, 1451491200),
(5, '员工企业文化认知度调查', 1, '调查员工对企业的文化认知度', 1, 1451491200);

INSERT INTO `questionnaire_question` (`question_id`, `questionnaire_id`, `question_title`, `question_type`) VALUES
(1, 1, 'title', 1),
(2, 2, '您对本公司的工作环境与室内设施是否满意', 1),
(3, 2, '您认为本公司的作息时间安排是否合理', 1),
(5, 2, '您认为本公司的规章制度是否合理或有其他建议', 4),
(6, 2, '您认为公司是否为您的工作配备了充足的资源，或您希望配备哪些资源', 4),
(7, 2, '您认为本公司的薪酬水平相对于行业平均水平', 1),
(8, 2, '您对本公司的福利是否满意', 1),
(9, 2, '您对自己的工作内容与兴趣所在关联度评价是', 1),
(10, 2, '您觉得公司内部是否给您提供了较好的工作机会', 1),
(11, 2, '您认为您的工作给你带来的压力', 1),
(12, 3, '您对目前公司与员工沟通的渠道或方式是否满意', 1),
(13, 3, '您认为公司是否给予员工足够的信任', 1),
(14, 3, '您认为您的工作是否受人尊敬', 1),
(15, 3, '您对所在部门的团队合作情况是否满意', 1),
(16, 3, '您认为自己及周围的同事是否能够符合制度和工作流程规定进行工作', 1),
(17, 3, '您认为您的工作能否充分发挥您的个人能力', 1),
(18, 3, '您认为自己在公司内是否得到公平的对待', 1),
(19, 4, '公司是否有清晰的目标给员工以振奋和激励', 1),
(20, 4, '为了获得长期的成功，公司员工都了解自己应该做什么', 1),
(21, 4, '您对公司未来发展的目标是否了解及认可', 1),
(22, 4, '您认为公司是否关心员工个人的成长和前途', 1),
(23, 4, '您觉得公司对员工职业发展的设计是否明确', 4),
(24, 4, '有不同意见或创新思维，您是否愿意主动提出', 1),
(25, 4, '您认为员工对公司提出意见和建议时，公司的反应是', 1),
(26, 4, '您认为公司需要提高的方面主要是', 1),
(27, 5, '您认为塑造企业文化是否与您有直接关系', 1),
(28, 5, '您认为企业文化应该是', 1),
(29, 5, '您认为企业文化应该达到的效果是', 1),
(30, 5, '您认为以下哪一项对企业文化最重要', 4),
(31, 5, '您认为优秀的企业文化是基于', 4);

INSERT INTO `questionnaire_question_opt` (`opt_id`, `question_id`, `opt`, `answer`) VALUES
(1, 1, 'op', 'answer'),
(2, 2, 'A', '非常满意'),
(3, 2, 'B', '比较满意'),
(4, 2, 'C', '一般'),
(5, 2, 'D', '不太满意'),
(6, 2, 'E', '非常不满意'),
(7, 3, 'A', '非常合理'),
(8, 3, 'B', '比较合理'),
(9, 3, 'C', '一般'),
(10, 3, 'D', '不太合理'),
(11, 3, 'E', '非常不合理'),
(12, 4, 'A', '非常合理'),
(13, 4, 'B', '比较合理'),
(14, 4, 'C', '一般'),
(15, 4, 'D', '不太合理'),
(16, 4, 'E', '非常不合理'),
(17, 5, 'A', '非常合理'),
(18, 5, 'B', '比较合理'),
(19, 5, 'C', '一般'),
(20, 5, 'D', '不太合理'),
(21, 5, 'E', '非常不合理'),
(22, 6, 'A', '充足'),
(23, 6, 'B', '一般'),
(24, 6, 'C', '不充足'),
(25, 6, 'D', '难说'),
(26, 6, 'E', '没考虑过'),
(27, 7, 'A', '高于平均水平'),
(28, 7, 'B', '一般'),
(29, 7, 'C', '低于平均水平'),
(30, 7, 'D', '不清楚'),
(31, 8, 'A', '非常满意'),
(32, 8, 'B', '比较满意'),
(33, 8, 'C', '一般'),
(34, 8, 'D', '不太满意'),
(35, 8, 'E', '非常不满意'),
(36, 9, 'A', '非常一致'),
(37, 9, 'B', '比较一致'),
(38, 9, 'C', '一般'),
(39, 9, 'D', '不太一致'),
(40, 9, 'E', '非常不一致'),
(41, 10, 'A', '有'),
(42, 10, 'B', '一般'),
(43, 10, 'C', '没有'),
(44, 10, 'D', '难说'),
(45, 11, 'A', '很大'),
(46, 11, 'B', '较大'),
(47, 11, 'C', '一般'),
(48, 11, 'D', '不大'),
(49, 11, 'E', '难说'),
(50, 12, 'A', '非常满意'),
(51, 12, 'B', '比较满意'),
(52, 12, 'C', '一般'),
(53, 12, 'D', '不满意'),
(54, 12, 'E', '非常不满意'),
(55, 13, 'A', '是'),
(56, 13, 'B', '一般'),
(57, 13, 'C', '否'),
(58, 13, 'D', '难说'),
(59, 14, 'A', '受尊重'),
(60, 14, 'B', '一般'),
(61, 14, 'C', '不受尊重'),
(62, 14, 'D', '不知道'),
(63, 14, 'E', '从来没想过'),
(64, 15, 'A', '非常满意'),
(65, 15, 'B', '比较满意'),
(66, 15, 'C', '一般'),
(67, 15, 'D', '不太满意'),
(68, 15, 'E', '非常不满意'),
(69, 16, 'A', '完全符合'),
(70, 16, 'B', '比较符合'),
(71, 16, 'C', '一般'),
(72, 16, 'D', '不太符合'),
(73, 16, 'E', '非常不符合'),
(74, 17, 'A', '能'),
(75, 17, 'B', '一般'),
(76, 17, 'C', '不能'),
(77, 17, 'D', '难说'),
(78, 17, 'E', '没考虑过'),
(79, 18, 'A', '能'),
(80, 18, 'B', '一般'),
(81, 18, 'C', '否'),
(82, 18, 'D', '难说'),
(83, 18, 'E', '不在乎'),
(84, 19, 'A', '有'),
(85, 19, 'B', '一般'),
(86, 19, 'C', '没有'),
(87, 19, 'D', '难说'),
(88, 19, 'E', '没考虑过'),
(89, 20, 'A', '是'),
(90, 20, 'B', '一般'),
(91, 20, 'C', '否'),
(92, 20, 'D', '难说'),
(93, 21, 'A', '是'),
(94, 21, 'B', '一般'),
(95, 21, 'C', '没有'),
(96, 21, 'D', '难说'),
(97, 22, 'A', '关心'),
(98, 22, 'B', '一般'),
(99, 22, 'C', '不关心'),
(100, 22, 'D', '难说'),
(101, 23, 'A', '明确'),
(102, 23, 'B', '一般'),
(103, 23, 'C', '不明确'),
(104, 23, 'D', '难说'),
(105, 24, 'A', '非常愿意'),
(106, 24, 'B', '比较愿意'),
(107, 24, 'C', '视情况而定'),
(108, 24, 'D', '不太愿意'),
(109, 24, 'E', '没考虑过'),
(110, 25, 'A', '有善接受'),
(111, 25, 'B', '有所反应'),
(112, 25, 'C', '毫无反应'),
(113, 25, 'D', '不接受'),
(114, 25, 'E', '难说'),
(115, 26, 'A', '行政人事工作'),
(116, 26, 'B', '财务管理'),
(117, 26, 'C', '销售工作'),
(118, 26, 'D', '公司管理'),
(119, 26, 'E', '公司发展方向'),
(120, 26, 'F', '企业文化及企业宣传'),
(121, 27, 'A', '有'),
(122, 27, 'B', '没有'),
(123, 27, 'C', '不知道'),
(124, 27, 'D', '无所谓'),
(125, 28, 'A', '主观打造的'),
(126, 28, 'B', '客观形成的'),
(127, 28, 'C', '员工主动地适应、融入企业文化'),
(128, 28, 'D', '企业文化顺应员工，因员工而变化'),
(129, 28, 'E', '企业文化顺应企业家、高层管理者'),
(130, 28, 'F', '企业与员工互动关系，相互影响、相互改造'),
(131, 29, 'A', '使企业人心凝聚、目标一致'),
(132, 29, 'B', '使员工服从企业的目标和决定，全力效力于企业'),
(133, 29, 'C', '使企业更富文化力、人性力'),
(134, 29, 'D', '实现企业、员工、社会的共赢'),
(135, 30, 'A', '培植企业文化'),
(136, 30, 'B', '企业文化的形成'),
(137, 30, 'C', '企业文化的发展'),
(138, 30, 'D', '企业文化的传播'),
(139, 30, 'E', '企业文化的变革'),
(140, 31, 'A', '企业悠久历史的沉淀和凝结'),
(141, 31, 'B', '企业家精神、企业创业之道与理念'),
(142, 31, 'C', '企业员工的共识、信仰与行为准则'),
(143, 31, 'D', '企业领导的理想');




CREATE TABLE IF NOT EXISTS `sqlite` (
  `sqlite_id` int(11) NOT NULL AUTO_INCREMENT,
  `sqlite_category` tinyint(4) NOT NULL,
  `sqlite_path` varchar(255) NOT NULL,
  `time_insert` int(11) NOT NULL,
  PRIMARY KEY (`sqlite_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `supplydemand` (
  `sd_id` int(11) NOT NULL AUTO_INCREMENT,
  `sd_publish` int(11) NOT NULL,
  `sd_type` tinyint(1) NOT NULL,
  `sd_title` varchar(50) CHARACTER SET utf8mb4 NOT NULL,
  `sd_detail` text NOT NULL,
  `sd_state` tinyint(4) NOT NULL,
  `sd_local` varchar(100) NOT NULL,
  `sd_attachment` varchar(255) NOT NULL,
  `type_id` int(11) NOT NULL DEFAULT '1',
  `time_insert` int(11) NOT NULL,
  `time_update` int(11) NOT NULL,
  PRIMARY KEY (`sd_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `supplydemand_attachment` (
  `attachment_id` int(11) NOT NULL AUTO_INCREMENT,
  `attachment_path` varchar(255) NOT NULL,
  `supplydemand_id` int(11) NOT NULL,
  PRIMARY KEY (`attachment_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `supplydemand_audience` (
  `sda_id` int(11) NOT NULL AUTO_INCREMENT,
  `sd_id` int(11) NOT NULL,
  `audience_id` int(11) NOT NULL,
  `time_insert` int(11) NOT NULL,
  PRIMARY KEY (`sda_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `supplydemand_type` (
  `type_id` int(11) NOT NULL AUTO_INCREMENT,
  `type_name` varchar(100) NOT NULL,
  `timestamp` int(11) NOT NULL,
  PRIMARY KEY (`type_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
INSERT INTO `supplydemand_type` (`type_id`, `type_name`, `timestamp`) VALUES
(1, '中药类', 0),
(2, '化药类', 0),
(3, '保健品类', 0),
(4, '医疗器械类', 0),
(5, '金融服务类', 0);


CREATE TABLE IF NOT EXISTS `uri` (
  `devicetoken` varchar(128) NOT NULL COMMENT '手机令牌',
  `app_for` varchar(100) NOT NULL COMMENT '暂不使用',
  `mobiletype` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'ios=1,android=2',
  `uid` int(11) NOT NULL DEFAULT '1' COMMENT '用户',
  `timestamp` int(11) NOT NULL COMMENT '时间',
  `version` varchar(20) NOT NULL COMMENT '当前app版本,检测谁没升级',
  `last_open_time` int(11) NOT NULL COMMENT '最后一次打开时间',
  `badge` int(11) NOT NULL COMMENT '小红点',
  PRIMARY KEY (`devicetoken`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `notice_push` (
  `push_id` int(11) NOT NULL AUTO_INCREMENT,
  `notice_id` int(11) NOT NULL,
  `audience_id` int(11) NOT NULL,
  PRIMARY KEY (`push_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `task` (
  `task_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `task_content` text NOT NULL COMMENT '内容',
  `task_creater_id` int(11) NOT NULL COMMENT '创建者id',
  `task_project_id` int(11) NOT NULL COMMENT '项目id',
  `task_time_begin` int(11) NOT NULL COMMENT '开始时间',
  `task_time_done` int(11) NOT NULL COMMENT '完成时间',
  `task_time_end` int(11) NOT NULL COMMENT '截止时间',
  `task_notice_time` int(11) NOT NULL,
  `task_notice_repeat` int(11) NOT NULL,
  `task_desc` text NOT NULL,
  `task_importance` int(11) NOT NULL,
  `task_urgency` int(11) NOT NULL,
  `time_insert` int(11) NOT NULL COMMENT '插入时间',
  `time_update` int(11) NOT NULL COMMENT '修改时间',
  `time_delete` int(11) NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `task_project` (
  `project_id` int(11) NOT NULL AUTO_INCREMENT,
  `project_name` varchar(255) NOT NULL,
  `project_detail` text NOT NULL,
  `project_time_begin` int(11) NOT NULL,
  `project_time_done` int(11) NOT NULL,
  `project_time_end` int(11) NOT NULL,
  `project_color` varchar(10) NOT NULL,
  `time_insert` int(11) NOT NULL,
  `time_update` int(11) NOT NULL,
  `time_delete` int(11) NOT NULL,
  PRIMARY KEY (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `task_project_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `part` tinyint(4) NOT NULL COMMENT '0创建,1管理员,2成员',
  `time_insert` int(11) NOT NULL,
  `time_update` int(11) NOT NULL,
  `time_delete` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `task_task_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `part` tinyint(4) NOT NULL DEFAULT '2' COMMENT '角色 0创建 2成员',
  `uid` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `time_read` int(11) NOT NULL,
  `time_insert` int(11) NOT NULL,
  `time_update` int(11) NOT NULL,
  `time_delete` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
INSERT INTO `task_project` (`project_id`, `project_name`, `project_detail`, `project_time_begin`, `project_time_done`, `project_time_end`, `project_color`, `time_insert`, `time_update`, `time_delete`) VALUES
(1, '个人项目', ' ', 1433919889, 0, 0, '18c3c0', 1433919889, 1433920000, 0);

CREATE TABLE IF NOT EXISTS `daily_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `log_create_uid` int(11) NOT NULL,
  `log_content` text NOT NULL,
  `log_local` varchar(255) NOT NULL DEFAULT ' ',
  `log_long` varchar(20) NOT NULL DEFAULT ' ',
  `log_lat` varchar(20) NOT NULL DEFAULT ' ',
  `time_insert` int(11) NOT NULL,
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `daily_log_attr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `log_id` int(11) NOT NULL,
  `path` varchar(255) NOT NULL,
  `time_insert` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `daily_log_praise` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `log_id` int(11) NOT NULL DEFAULT '0',
  `reply_id` int(11) NOT NULL DEFAULT '0',
  `praise_for` int(11) NOT NULL,
  `group_cows` varchar(100) NOT NULL,
  `time_insert` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `time_insert` (`time_insert`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `daily_log_remind` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `log_id` int(11) NOT NULL DEFAULT '0',
  `reply_id` int(11) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL,
  `at_local` int(11) NOT NULL,
  `at_length` int(11) NOT NULL,
  `time_insert` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `daily_log_reply` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `log_id` int(11) NOT NULL,
  `pid` int(11) NOT NULL DEFAULT '0',
  `content` text NOT NULL,
  `time_insert` int(11) NOT NULL,
  `reply_for_uid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `daily_log_share_range` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `log_id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `daily_log_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `log_id` int(11) NOT NULL,
  `tag_name` varchar(100) CHARACTER SET utf8mb4 NOT NULL,
  `tag_real_name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tag_name` (`tag_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `daily_log_tags_private` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag_name` varchar(100) CHARACTER SET utf8 NOT NULL,
  `tag_real_name` varchar(100) CHARACTER SET utf8 NOT NULL,
  `uid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `push_notice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` text CHARACTER SET utf8 NOT NULL,
  `module` varchar(20) CHARACTER SET utf8 NOT NULL,
  `module_id` int(11) NOT NULL,
  `time_insert` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `time_insert` (`time_insert`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `push_notice_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `push_notice_id` int(11) NOT NULL,
  `audience_id` int(11) NOT NULL,
  `time_insert` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `audience_id` (`audience_id`,`time_insert`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `meeting_push` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `meeting_id` int(11) NOT NULL,
  `audience_id` int(11) NOT NULL,
  `push_time` int(11) NOT NULL,
  `befor_time` int(11) NOT NULL,
  `time_update` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `task_share_log` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`task_id` int(11) NOT NULL,
  	`task_share_name` varchar(100) NOT NULL,
	`task_share_members_id` text NOT NULL,
	`task_is_department` tinyint(4) NOT NULL,
	`task_at_members_id` text NOT NULL,
	`task_members_department_id` int(11) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `task_execution` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `time_insert` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `task_uid_id` (`task_id`,`uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `sign_in` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `local` varchar(200) CHARACTER SET utf8 NOT NULL,
  `long` varchar(100) CHARACTER SET utf8 NOT NULL,
  `lat` varchar(100) CHARACTER SET utf8 NOT NULL,
  `range` int(11) NOT NULL,
  `start_time` int(11) NOT NULL,
  `end_time` int(11) NOT NULL,
  `date` date NOT NULL,
  `open_time` int(11) NOT NULL,
  `time_insert` int(11) NOT NULL,
  `time_update` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `sign_in_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sign_in_id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `long` varchar(100) CHARACTER SET utf8 NOT NULL,
  `lat` varchar(100) CHARACTER SET utf8 NOT NULL,
  `local` varchar(100) CHARACTER SET utf8 NOT NULL,
  `start_time` int(11) NOT NULL,
  `date` date NOT NULL,
  `state` int(11) NOT NULL,
  `time_insert` int(11) NOT NULL,
  `time_update` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
