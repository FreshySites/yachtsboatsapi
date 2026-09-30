<?php

class BoatsAPITableList {

	private static $initiated = false;

	/**

	 * Is the comment check happening in the context of an API call? Of if false, then it's during the POST that happens after filling out a comment form.

	 *

	 * @var type bool

	 */

	private static $is_api_call = false;



	public static function init() {

		if ( ! self::$initiated ) {

			self::init_hooks();

		}

	}



	/**

	 * Initializes WordPress hooks

	 */

	private static function init_hooks() {

		self::$initiated = true;

	}

	

	public static function getBoatsTbl(){

		global $wpdb;

		$table = $wpdb->prefix.'boats';

		$boats = "CREATE TABLE `$table` (

		`id` int(10) unsigned NOT NULL AUTO_INCREMENT,

		`categoryid` int(10) unsigned NOT NULL,

		`conditionid` int(10) unsigned NOT NULL,

		`yachtworldid` int(10) unsigned DEFAULT NULL,

		`officeid` int(10) NOT NULL,

		`agentid` int(10) DEFAULT NULL,

		`hullid` int(10) DEFAULT NULL,

		`fuelid` int(10) DEFAULT NULL,

		`status` varchar(32) NOT NULL,

		`city` varchar(50) DEFAULT NULL,

		`countrycode` varchar(50) DEFAULT NULL,

		`statecode` varchar(10) DEFAULT 'SC',

		`price` decimal(11,2) DEFAULT '0.00',

		`make` varchar(100) DEFAULT NULL,

		`model` varchar(100) DEFAULT NULL,

		`nominallength` decimal(6,2) DEFAULT NULL,

		`normnominallength` decimal(6,2) DEFAULT NULL,

		`lengthoverall` decimal(6,2) DEFAULT NULL,

		`lengthatwaterline` decimal(6,2) DEFAULT NULL,

		`lengthofdeck` decimal(6,2) DEFAULT NULL,

		`year` int(10) DEFAULT NULL,

		`boatname` varchar(100) DEFAULT NULL,

		`brokername` varchar(100) DEFAULT NULL,

		`companyname` varchar(100) DEFAULT NULL,

		`buildername` varchar(100) DEFAULT NULL,

		`designername` varchar(100) DEFAULT NULL,

		`listingtitle` varchar(100) DEFAULT NULL,

		`fulltextsearch` mediumtext,

		`maxdraft` decimal(6,2) DEFAULT NULL,

		`displacementmeasure` decimal(10,2) DEFAULT NULL,

		`ballastweightmeasure` decimal(10,2) DEFAULT NULL,

		`bridgeclearancemeasure` decimal(10,2) DEFAULT NULL,

		`cabinheadroommeasure` decimal(10,2) DEFAULT NULL,

		`beammeasure` decimal(10,2) DEFAULT NULL,

		`deadrisemeasure` decimal(10,2) DEFAULT NULL,

		`electricalcircuitmeasure` decimal(10,2) DEFAULT NULL,

		`freeboardmeasure` decimal(10,2) DEFAULT NULL,

		`fueltankcapacitymeasure` decimal(10,2) DEFAULT NULL,

		`fueltankcountnumeric` int(3) DEFAULT NULL,

		`holdingtankcapacitymeasure` decimal(10,2) DEFAULT NULL,

		`holdingtankcountnumeric` int(3) DEFAULT NULL,

		`maximumspeedmeasure` decimal(10,2) DEFAULT NULL,

		`rangemeasure` decimal(10,2) DEFAULT NULL,

		`watertankcapacitymeasure` decimal(10,2) DEFAULT NULL,

		`watertankcountnumeric` int(3) DEFAULT NULL,

		`numberofengines` int(2) DEFAULT NULL,

		`totalenginehoursnumeric` int(10) DEFAULT NULL,

		`totalenginepowerquantity` decimal(10,2) DEFAULT NULL,

		`registrationcountrycode` varchar(10) DEFAULT NULL,

		`generalboatdescription` longtext,

		`additionaldetaildescription` longtext,

		`lat` float(10,6) DEFAULT NULL,

		`lng` float(10,6) DEFAULT NULL,

		`viewed` int(10) NOT NULL DEFAULT '0',

		`isavailableforpls` bit(1) NOT NULL DEFAULT b'0',

		`ispricereduced` bit(1) NOT NULL DEFAULT b'0',

		`ishot` bit(1) NOT NULL DEFAULT b'0',

		`isdisplayedaftersold` bit(1) NOT NULL DEFAULT b'0',

		`ispricehidden` bit(1) NOT NULL DEFAULT b'0',

		`hascoop` bit(1) NOT NULL DEFAULT b'0',

		`itemreceiveddate` datetime DEFAULT NULL,

		`modifieddate` datetime DEFAULT NULL,

		`createdat` datetime DEFAULT NULL,

		`updatedat` datetime DEFAULT NULL,

		`source` tinyint(1) NOT NULL DEFAULT '1',

		PRIMARY KEY (`id`),

		KEY `fk_boats_categoryid` (`categoryid`),

		KEY `fk_boats_conditionid` (`conditionid`),

		KEY `fk_boats_fuelid` (`fuelid`),

		KEY `fk_boats_hullid` (`hullid`),

		KEY `fk_boats_agentid` (`agentid`),

		FULLTEXT KEY `fk_fulltextsearch` (`fulltextsearch`)

		) ENGINE=InnoDB DEFAULT CHARSET=utf8";

		return $boats;



	}



	public static function getAgentsTbl(){

		global $wpdb;

		$table = $wpdb->prefix.'agents';

		$agents = "CREATE TABLE `$table` (

		`partid` int(6) NOT NULL,

		`name` varchar(100) NOT NULL DEFAULT '',

		`message` mediumtext,

		`email` VARCHAR(100) NOT NULL DEFAULT '',

		`phone` VARCHAR(100) NOT NULL DEFAULT '',
 
		PRIMARY KEY (`partid`)

		) ENGINE=InnoDB DEFAULT CHARSET=utf8";

		return $agents;

	}



	public static function getBoatCategoriesTbl(){

		global $wpdb;

		$table = $wpdb->prefix.'boat_categories';

		$boat_categories = "CREATE TABLE `$table` (

		`id` int(10) unsigned NOT NULL AUTO_INCREMENT,

		`code` varchar(20) NOT NULL,

		`title` varchar(50) NOT NULL,

		`description` varchar(512) DEFAULT NULL,

		`isactive` bit(1) NOT NULL DEFAULT b'1',

		PRIMARY KEY (`id`)

		) ENGINE=InnoDB DEFAULT CHARSET=utf8";

		return $boat_categories;

	}



	public static function getBoatConditionsTbl(){

		global $wpdb;

		$table = $wpdb->prefix.'boat_conditions';

		$boat_conditions = "CREATE TABLE `$table` (

		`id` int(10) unsigned NOT NULL AUTO_INCREMENT,

		`code` varchar(20) NOT NULL,

		`title` varchar(50) NOT NULL,

		`description` varchar(512) DEFAULT NULL,

		`isactive` bit(1) NOT NULL DEFAULT b'1',

		PRIMARY KEY (`id`)

		) ENGINE=InnoDB DEFAULT CHARSET=utf8";

		return $boat_conditions;

	}



	public static function getBoatFuelTbl(){

		global $wpdb;

		$table = $wpdb->prefix.'boat_fuel';

		$boat_fuel = "CREATE TABLE `$table` (

		`id` int(10) unsigned NOT NULL AUTO_INCREMENT,

		`boatid` varchar(50) NOT NULL,

		`code` varchar(50) NOT NULL,

		`title` varchar(50) NOT NULL,

		`description` varchar(512) DEFAULT NULL,

		`isactive` bit(1) NOT NULL DEFAULT b'1',

		PRIMARY KEY (`id`)

		) ENGINE=InnoDB DEFAULT CHARSET=utf8";

		return $boat_fuel;

	} 



	public static function getBoatHullMaterialsTbl(){

		global $wpdb;

		$table = $wpdb->prefix.'boat_hull_materials';

		$boat_hull_materials = "CREATE TABLE `$table` (

		`id` int(10) unsigned NOT NULL AUTO_INCREMENT,

		`code` varchar(20) NOT NULL,

		`title` varchar(50) NOT NULL,

		`description` varchar(512) DEFAULT NULL,

		`isactive` bit(1) NOT NULL DEFAULT b'1',

		`position` int(3) NOT NULL,

		PRIMARY KEY (`id`)

		) ENGINE=InnoDB DEFAULT CHARSET=utf8";

		return $boat_hull_materials;

	}



	public static function getBoatPricesTbl(){

		global $wpdb;

		$table = $wpdb->prefix.'boat_prices';

		$boat_prices = "CREATE TABLE `$table` (

		`id` int(10) unsigned NOT NULL AUTO_INCREMENT,

		`boatid` int(10) unsigned NOT NULL,

		`new` decimal(11,2) NOT NULL DEFAULT '0.00',

		`old` decimal(11,2) NOT NULL DEFAULT '0.00',

		`createdat` datetime DEFAULT NULL,

		PRIMARY KEY (`id`)

		) ENGINE=InnoDB DEFAULT CHARSET=utf8";

		return $boat_prices;

	}



	public static function getBoatPropertiesTbl(){

		global $wpdb;

		$table = $wpdb->prefix.'boat_properties';

		$boat_properties = "CREATE TABLE `$table` (

		`id` int(10) unsigned NOT NULL AUTO_INCREMENT,

		`boatid` int(10) unsigned NOT NULL,

		`new` decimal(11,2) NOT NULL DEFAULT '0.00',

		`old` decimal(11,2) NOT NULL DEFAULT '0.00',

		`createdat` datetime DEFAULT NULL,

		PRIMARY KEY (`id`)

		) ENGINE=InnoDB DEFAULT CHARSET=utf8";

		return $boat_properties;

	}



	public static function getEnginesTbl(){

		global $wpdb;

		$table = $wpdb->prefix.'engines';

		$engines = "CREATE TABLE `$table` (

		`id` int(10) unsigned NOT NULL AUTO_INCREMENT,

		`boatid` int(10) unsigned NOT NULL,

		`make` varchar(100) DEFAULT NULL,

		`model` varchar(100) DEFAULT NULL,

		`fuel` varchar(50) DEFAULT NULL,

		`enginepower` varchar(100) DEFAULT NULL,

		`type` varchar(100) DEFAULT NULL,

		`year` int(5) DEFAULT NULL,

		`hours` int(8) DEFAULT NULL,

		`BoatEngineLocationCode` varchar(100) DEFAULT NULL,

		`DriveTransmissionDescription` varchar(256) DEFAULT NULL,

		`PropellerType` varchar(256) DEFAULT NULL,

		PRIMARY KEY (`id`),

		KEY `ix_engines` (`boatid`)

		) ENGINE=InnoDB DEFAULT CHARSET=utf8";

		return $engines;

	}



	public static function getEventsTbl(){

		global $wpdb;

		$table = $wpdb->prefix.'events';

		$events = "CREATE TABLE `$table` (

		`id` int(10) unsigned NOT NULL AUTO_INCREMENT,

		`action` varchar(100) DEFAULT NULL,

		`type` varchar(1) NOT NULL DEFAULT 'I' COMMENT 'I,W,E',

		`message` varchar(512) DEFAULT NULL,

		`detail` mediumtext,

		`remoteip` varchar(15) NOT NULL,

		`createdat` datetime NOT NULL,

		PRIMARY KEY (`id`)

		) ENGINE=InnoDB DEFAULT CHARSET=utf8";

		return $events;

	}



	public static function getImagesTbl(){

		global $wpdb;

		$table = $wpdb->prefix.'images';

		$images = "CREATE TABLE `$table` (

		`id` int(10) unsigned NOT NULL AUTO_INCREMENT,

		`boatid` int(10) unsigned NOT NULL,

		`url` mediumtext NOT NULL,

		`caption` varchar(512) DEFAULT NULL,

		`priority` int(5) NOT NULL,

		PRIMARY KEY (`id`),

		KEY `uix_boats_yachtworldid` (`boatid`)

		) ENGINE=InnoDB DEFAULT CHARSET=utf8";

		return $images;

	}



	public static function getVideosTbl(){

		global $wpdb;

		$table = $wpdb->prefix.'videos';

		$videos = "CREATE TABLE `$table` (

		`id` int(10) unsigned NOT NULL AUTO_INCREMENT,

		`boatid` int(10) unsigned NOT NULL,

		`url` mediumtext NOT NULL,

		`thumbnailUrl` mediumtext NOT NULL,

		`title` varchar(512) DEFAULT NULL,

		`desc` varchar(256) DEFAULT NULL,

		PRIMARY KEY (`id`),

		KEY `uix_boats_yachtworldid` (`boatid`)

		) ENGINE=InnoDB DEFAULT CHARSET=utf8";

		return $videos;

	}



	public static function getOfficesTbl(){

		global $wpdb;

		$table = $wpdb->prefix.'offices';

		$offices = "CREATE TABLE `$table` (

		`id` int(10) unsigned NOT NULL AUTO_INCREMENT,

		`boatid` int(10) unsigned NOT NULL,

		`url` mediumtext NOT NULL,

		`thumbnailUrl` mediumtext NOT NULL,

		`title` varchar(512) DEFAULT NULL,

		`desc` varchar(256) DEFAULT NULL,

		PRIMARY KEY (`id`),

		KEY `uix_boats_yachtworldid` (`boatid`)

		) ENGINE=InnoDB DEFAULT CHARSET=utf8";

		return $offices;

	}



	public static function getMoreQueriesTbl(){

		global $wpdb;

		$table = $wpdb->prefix.'yacht_queries';

		$queries = "CREATE TABLE `$table` (

		`id` int(10) unsigned NOT NULL AUTO_INCREMENT,

		`boatid` int(10) unsigned NOT NULL,

		`customer_info` mediumtext NOT NULL,

		PRIMARY KEY (`id`),

		KEY `uix_boats_yachtworldid` (`boatid`)

		) ENGINE=InnoDB DEFAULT CHARSET=utf8";

		return $queries;

	}



}

?>