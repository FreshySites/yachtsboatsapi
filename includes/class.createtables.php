<?php
class BoatsAPICreateTables {
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

	public static function CheckTbl($wpdb, $tbl_name){
		global $wpdb;

		$table_name = $tbl_name;
		$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );

		if ( ! $wpdb->get_var( $query ) == $table_name ) {
		    return true;
		}else{
			return false;
		}
	}

	public static function RunQuery($query, $wpdb){
		global $wpdb;
		if($wpdb->query($query) === TRUE) {
			$message = true;
		}else{
			$message = "Error creating table: Error";
		}

		return $message;
	}

	public static function CheckAndCreateBoatsTbl($wpdb){
		global $wpdb;
		$tbl_name = $wpdb->prefix.'boats';

		if(self::CheckTbl($wpdb, $tbl_name) == true){
			$obj = new BoatsAPITableList();
			$boats = $obj->getBoatsTbl();
			// echo $boats; die();
			$message = self::RunQuery($boats, $wpdb);
			if($message != true){
				echo $message;
			}
		}
	}

	public static function CheckAndCreateAgentsTbl($wpdb){
		global $wpdb;
		$tbl_name = $wpdb->prefix.'agents';
		$obj = new BoatsAPITableList();
		if(self::CheckTbl($wpdb, $tbl_name) == true){
			$agents = $obj->getAgentsTbl();
			$message = self::RunQuery($agents, $wpdb);
			if($message != true){
				echo $message;
			}
		}
	}

	public static function CheckAndCreateBoatCategoriesTbl($wpdb){
		global $wpdb;
		$tbl_name = $wpdb->prefix.'boat_categories';

		if(self::CheckTbl($wpdb, $tbl_name) == true){
			$obj = new BoatsAPITableList();
			$boat_categories = $obj->getBoatCategoriesTbl();
			$message = self::RunQuery($boat_categories, $wpdb);
			if($message != true){
				echo $message;
			}
		}
	}

	public static function CheckAndCreateBoatConditionsTbl($wpdb){
		global $wpdb;
		$tbl_name = $wpdb->prefix.'boat_conditions';

		if(self::CheckTbl($wpdb, $tbl_name) == true){
			$obj = new BoatsAPITableList();
			$boat_conditions = $obj->getBoatConditionsTbl();
			$message = self::RunQuery($boat_conditions, $wpdb);
			if($message != true){
				echo $message;
			}
		}
	}

	public static function CheckAndCreateBoatFuelTbl($wpdb){
		global $wpdb;
		$tbl_name = $wpdb->prefix.'boat_fuel';

		if(self::CheckTbl($wpdb, $tbl_name) == true){
			$obj = new BoatsAPITableList();
			$boat_fuel = $obj->getBoatFuelTbl();
			$message = self::RunQuery($boat_fuel, $wpdb);
			if($message != true){
				echo $message;
			}
		}
	}

	public static function CheckAndCreateBoatHullMaterialsTbl($wpdb){
		global $wpdb;
		$tbl_name = $wpdb->prefix.'boat_hull_materials';

		if(self::CheckTbl($wpdb, $tbl_name) == true){
			$obj = new BoatsAPITableList();
			$boat_hull_materials = $obj->getBoatHullMaterialsTbl();
			$message = self::RunQuery($boat_hull_materials, $wpdb);
			if($message != true){
				echo $message;
			}
		}
	}

	public static function CheckAndCreateBoatPricesTbl($wpdb){
		global $wpdb;
		$tbl_name = $wpdb->prefix.'boat_prices';

		if(self::CheckTbl($wpdb, $tbl_name) == true){
			$obj = new BoatsAPITableList();
			$boat_prices = $obj->getBoatPricesTbl();
			$message = self::RunQuery($boat_prices, $wpdb);
			if($message != true){
				echo $message;
			}
		}
	}

	public static function CheckAndCreateBoatPropertiesTbl($wpdb){
		global $wpdb;
		$tbl_name = $wpdb->prefix.'boat_properties';

		if(self::CheckTbl($wpdb, $tbl_name) == true){
			$obj = new BoatsAPITableList();
			$boat_properties = $obj->getBoatPropertiesTbl();
			$message = self::RunQuery($boat_properties, $wpdb);
			if($message != true){
				echo $message;
			}
		}
	}

	public static function CheckAndCreateEnginesTbl($wpdb){
		global $wpdb;
		$tbl_name = $wpdb->prefix.'engines';

		if(self::CheckTbl($wpdb, $tbl_name) == true){
			$obj = new BoatsAPITableList();
			$engines = $obj->getEnginesTbl();
			$message = self::RunQuery($engines, $wpdb);
			if($message != true){
				echo $message;
			}
		}
	}

	public static function CheckAndCreateEventsTbl($wpdb){
		global $wpdb;
		$tbl_name = $wpdb->prefix.'events';

		if(self::CheckTbl($wpdb, $tbl_name) == true){
			$obj = new BoatsAPITableList();
			$events = $obj->getEventsTbl();
			$message = self::RunQuery($events, $wpdb);
			if($message != true){
				echo $message;
			}
		}
	}

	public static function CheckAndCreateImagesTbl($wpdb){
		global $wpdb;
		$tbl_name = $wpdb->prefix.'images';

		if(self::CheckTbl($wpdb, $tbl_name) == true){
			$obj = new BoatsAPITableList();
			$images = $obj->getImagesTbl();
			$message = self::RunQuery($images, $wpdb);
			if($message != true){
				echo $message;
			}
		}
	}

	public static function CheckAndCreateVideosTbl($wpdb){
		global $wpdb;
		$tbl_name = $wpdb->prefix.'videos';

		if(self::CheckTbl($wpdb, $tbl_name) == true){
			$obj = new BoatsAPITableList();
			$videos = $obj->getVideosTbl();
			$message = self::RunQuery($videos, $wpdb);
			if($message != true){
				echo $message;
			}
		}
	}

	public static function CheckAndCreateOfficesTbl($wpdb){
		global $wpdb;
		$tbl_name = $wpdb->prefix.'offices';

		if(self::CheckTbl($wpdb, $tbl_name) == true){
			$obj = new BoatsAPITableList();
			$offices = $obj->getOfficesTbl();
			$message = self::RunQuery($offices, $wpdb);
			if($message != true){
				echo $message;
			}
		}
	}

	public static function CheckAndCreateMoreQueriesTbl($wpdb){
		global $wpdb;
		$tbl_name = $wpdb->prefix.'yacht_queries';

		if(self::CheckTbl($wpdb, $tbl_name) == true){
			$obj = new BoatsAPITableList();
			$queries = $obj->getMoreQueriesTbl();
			$message = self::RunQuery($queries, $wpdb);
			if($message != true){
				echo $message;
			}
		}
	}

}
?>