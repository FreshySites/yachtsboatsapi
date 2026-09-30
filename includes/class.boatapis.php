<?php
class BoatsAPI {
    
    
    
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

	public static function get_ip_address() {
		return isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : null;
	}

	private static function get_user_agent() {
		return isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : null;
	}

	private static function get_referer() {
		return isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : null;
	}
	
	public static function inject_ak_js( $post_id ) {
		echo '<input type="hidden" id="ak_js" name="ak_js" value="' . mt_rand( 0, 250 ) . '"/>';
		echo '<textarea name="ak_hp_textarea" cols="45" rows="8" maxlength="100" style="display: none !important;"></textarea>';
	}

	public static function saveBoatsAPIData( $apiData ) {
		$save_obj = new SaveAllBoatsAPIData();
		foreach ($apiData as $key => $value) {
			
			// Detail description array into strin						
			if (is_array($value->AdditionalDetailDescription ?? '')){
				
				$AdditionalDetailDescription = $value->AdditionalDetailDescription;
				
			}else{
				$AdditionalDetailDescription = array();
				$AdditionalDetailDescription[] = $value->AdditionalDetailDescription ?? '';
			}
			
			$AdditionalDetailDescription = implode( " ", $AdditionalDetailDescription );
			
			
			//for all use this vlues
			$boatid = $value->DocumentID;
			$Office = $value->Office;
			$BoatLocation = $value->BoatLocation;

			$NominalLength = $value->NominalLength;
			$NominalLength = explode(" ",$NominalLength ?? '');
			$NominalLength = $NominalLength[0];

			$LengthOverall = isset($value->LengthOverall) ? $value->LengthOverall : Null;
			$LengthOverall = explode(" ",$LengthOverall ?? '');
			$LengthOverall = $LengthOverall[0];

			$FuelTankCountNumeric = isset($value->FuelTankCountNumeric) ? $value->FuelTankCountNumeric : Null;
			$FuelTankCountNumeric = explode(" ",$FuelTankCountNumeric ?? '');
			$FuelTankCountNumeric = $FuelTankCountNumeric[0];

			$HoldingTankCapacityMeasure = isset($value->HoldingTankCapacityMeasure) ? $value->HoldingTankCapacityMeasure : Null;
			$HoldingTankCapacityMeasure = explode(" ",$HoldingTankCapacityMeasure ?? '');
			$HoldingTankCapacityMeasure = $HoldingTankCapacityMeasure[0];

			$MaximumSpeedMeasure = isset($value->MaximumSpeedMeasure) ? $value->MaximumSpeedMeasure : Null;
			$MaximumSpeedMeasure = explode(" ",$MaximumSpeedMeasure ?? '');
			$MaximumSpeedMeasure = $MaximumSpeedMeasure[0];

			$watertankcapacitymeasure = isset($value->watertankcapacitymeasure) ?  $value->watertankcapacitymeasure : Null;
			$watertankcapacitymeasure = explode(" ",$watertankcapacitymeasure ?? '');
			$watertankcapacitymeasure = $watertankcapacitymeasure[0];

			$TotalEnginePowerQuantity = isset($value->TotalEnginePowerQuantity) ? $value->TotalEnginePowerQuantity : Null;
			$TotalEnginePowerQuantity = explode(" ",$TotalEnginePowerQuantity ?? '');
			$TotalEnginePowerQuantity = $TotalEnginePowerQuantity[0];

			$FuelTankCapacityMeasure = isset($value->FuelTankCapacityMeasure) ? $value->FuelTankCapacityMeasure: Null;
			$FuelTankCapacityMeasure = explode(" ",$FuelTankCapacityMeasure ?? '');
			$FuelTankCapacityMeasure = $FuelTankCapacityMeasure[0];
			//end

			// For categories
			
			global $wpdb;

			$BoatCategories = array('code' => isset($value->BoatCategoryCode) ? $value->BoatCategoryCode : Null);
			$categoryid = $save_obj->InsertBoatCategories($BoatCategories,$wpdb);
			if(isset($categoryid['last_id'])){
				$categoryids  = $categoryid['last_id']; 
			}else{
				$save_obj->ErrorMessage($categoryid['message']); 
			}

			//end

			// For Agents 
			$SalesRep = $value->SalesRep;
			$message = ''; 
			
			if(isset($SalesRep->Message)){
				$message = $SalesRep->Message;
			}

			$Agents = ['partid' => $SalesRep->PartyId,'name' =>$SalesRep->Name,'message' => $message, 'email' => $Office->Email, 'phone' => $Office->Phone];
			$save_obj->InsertUpdateAgents($Agents,$wpdb);
			//end

			//For Images
			$Images = $value->Images;
			// print_r(json_decode(json_encode($Images)));
			$save_obj->InsertUpdateImage($Images,$boatid);
			//end

			//For Videos
			if(isset($value->Videos)){
				$Videos = $value->Videos;
				$save_obj->InsertUpdateVideo($Videos,$boatid,$wpdb);
			}
			//end

			//Engines
			if(isset($value->Engines)){
				
				
				/*
				$Engines=$value->Engines;
				
				error_log(print_r($Engines, true));
				
				
				$engines_data = array(
					'boatid' => isset($boatid) ? $boatid : Null,
					'make' => isset($Engines[0]->Make) ? $Engines[0]->Make : Null,
					'model' => isset($Engines[0]->Model) ? $Engines[0]->Model : Null,
					'fuel' => isset($Engines[0]->Fuel) ? $Engines[0]->Fuel : Null,
					'enginepower' => isset($Engines[0]->EnginePower) ? $Engines[0]->EnginePower : Null,
					'type' => isset($Engines[0]->Type) ? $Engines[0]->Type : Null,
					'year' => isset($Engines[0]->Year) ? $Engines[0]->Year : Null,
					'hours' => isset($Engines[0]->Hours) ? $Engines[0]->Hours : Null,
					'BoatEngineLocationCode' => isset($Engines[0]->BoatEngineLocationCode) ? $Engines[0]->BoatEngineLocationCode : Null,
					'DriveTransmissionDescription' => isset($Engines[0]->DriveTransmissionDescription) ? $Engines[0]->DriveTransmissionDescription : Null,
					'PropellerType' => isset($Engines[0]->PropellerType) ? $Engines[0]->PropellerType : Null
				);
				*/
				
				
				$Engines = (array) $value->Engines;

                $engine0 = isset($Engines[0]) && is_object($Engines[0]) ? $Engines[0] : null;
                
                $engines_data = array(
                    'boatid' => isset($boatid) ? $boatid : null,
                    'make' => isset($engine0->Make) ? $engine0->Make : null,
                    'model' => isset($engine0->Model) ? $engine0->Model : null,
                    'fuel' => isset($engine0->Fuel) ? $engine0->Fuel : null,
                    'enginepower' => isset($engine0->EnginePower) ? $engine0->EnginePower : null,
                    'type' => isset($engine0->Type) ? $engine0->Type : null,
                    'year' => isset($engine0->Year) ? $engine0->Year : null,
                    'hours' => isset($engine0->Hours) ? $engine0->Hours : null,
                    'BoatEngineLocationCode' => isset($engine0->BoatEngineLocationCode) ? $engine0->BoatEngineLocationCode : null,
                    'DriveTransmissionDescription' => isset($engine0->DriveTransmissionDescription) ? $engine0->DriveTransmissionDescription : null,
                    'PropellerType' => isset($engine0->PropellerType) ? $engine0->PropellerType : null
                );
				
				
				
				
				
				// print_r((object)$engines_data);
				// $engi = (object)$engines_data;
				$save_obj->InsertUpdateEngines(json_encode($engines_data));

				//Fuel
				$fuelid = $save_obj->InsertUpdateFuel($engines_data,$wpdb);
				if(isset($fuelid['last_id'])){
					$fuelids = $fuelid['last_id']; 
				}else{
					$save_obj->ErrorMessage($fuelid['message']); 
				}
				//end
			}
			//end

			//Price
			$boatprice = array('boatid' =>$boatid, 'old' => isset($value->Price) ? $value->Price : 0.00 , 'new' =>  isset($value->NormPrice) ? $value->NormPrice : 0.00);
			$save_obj->InsertUpdateBoatPrice($boatprice,$wpdb);

			//HullMaterials
			$HullMaterials = array('code' => isset($value->BoatHullMaterialCode) ? $value->BoatHullMaterialCode : Null);
			// print_r($HullMaterials); die();
			$hullid = $save_obj->InsertUpdateHullMaterials($HullMaterials, $wpdb);
			if(isset($hullid['last_id'])){
				$hullids = $hullid['last_id']; 
			}else{
				$save_obj->ErrorMessage($hullid['message']); 
			}
			//end

			//Condition
			$condition = array('code' => isset($value->SaleClassCode) ? $value->SaleClassCode : Null);
			$conditionid = $save_obj->InsertUpdateConditions($condition, $wpdb);
			if(isset($conditionid['last_id'])){
				$conditionids = $conditionid['last_id']; 
			}else{
				$save_obj->ErrorMessage($conditionid['message']); 
			}
			//end

			//For Boats
			$data = array('id' => isset($boatid) ? $boatid : Null,
				'yachtworldid' => isset($value->YachtWorldID) ? $value->YachtWorldID : Null, 
				'agentid' => isset($value->SalesRep->PartyId) ? $value->SalesRep->PartyId: Null, 
				'status' => isset($value->SalesStatus) ? $value->SalesStatus : Null, 
				'city' => isset($BoatLocation->BoatCityName) ? $BoatLocation->BoatCityName : Null,
				'countrycode' => isset($BoatLocation->BoatCountryID) ? $BoatLocation->BoatCountryID : Null, 
				'statecode' => isset($BoatLocation->BoatStateCode) ? $BoatLocation->BoatStateCode : Null,
				'price' => isset($value->Price) ? $value->Price : Null,
				'make' => isset($value->MakeString) ? $value->MakeString : Null, 
				'model'=> isset($value->Model) ? $value->Model : Null, 
				'nominallength' => isset($NominalLength) ? $NominalLength : Null,
				'normnominallength'=> isset($value->NormNominalLength) ? $value->NormNominalLength : Null,
				'lengthoverall'=> isset($LengthOverall) ? $LengthOverall : Null,
				'year' => isset($value->ModelYear) ? $value->ModelYear : Null,
				'boatname' => isset($value->BoatName) ? $value->BoatName : Null,
				'brokername' => isset($value->SalesRep->Name) ? $value->SalesRep->Name : Null,
				'companyname' => isset($value->CompanyName) ? $value->CompanyName : Null, 
				'buildername' => isset($value->BuilderName) ? $value->BuilderName : Null,
				'designername' => isset($value->DesignerName) ? $value->DesignerName : Null,
				'fullserachtx' => (isset($value->MakeString) ? esc_attr($value->MakeString) : Null).' '.(isset($value->Model) ? esc_attr($value->Model) : Null).' '.(isset($value->ModelYear) ? esc_attr($value->ModelYear): Null).' '.(isset($Office->City) ? esc_attr($Office->City) : Null).' '.(isset($Office->State) ? esc_attr($Office->State) : Null).' '.(isset($value->Country) ? esc_attr($Office->State) : Null).' '.(isset($value->SalesRep->Name) ? esc_attr($value->SalesRep->Name) : Null).' '.(isset($value->BoatCategoryCode) ? esc_attr($value->SalesRep->Name) : Null),
				'maxdraft' => isset($value->MaxDraft) ? $value->MaxDraft : Null,
				'displacementmeasure' => isset($value->DisplacementMeasure) ? $value->DisplacementMeasure : Null,
				'ballastweightmeasure' => isset($value->BallastWeightMeasure) ? $value->BallastWeightMeasure : Null,
				'bridgeclearancemeasure' => isset($value->BridgeClearanceMeasure) ? $value->BridgeClearanceMeasure : Null,
				'cabinheadroommeasure' => isset($value->CabinHeadroomMeasure) ? $value->CabinHeadroomMeasure : Null,
				'beammeasure' => isset($value->BeamMeasure) ? $value->BeamMeasure : Null,
				'deadrisemeasure' => isset($value->DeadriseMeasure) ? $value->DeadriseMeasure : Null,
				'electricalcircuitmeasure' => isset($value->ElectricalCircuitMeasure) ? $value->ElectricalCircuitMeasure : Null,
				'freeboardmeasure' => isset($value->FreeBoardMeasure) ? $value->FreeBoardMeasure : Null,
				'fueltankcapacitymeasure' => isset($FuelTankCapacityMeasure) ? $FuelTankCapacityMeasure : Null,
				'fueltankcountnumeric' => isset($FuelTankCountNumeric) ? $FuelTankCountNumeric : Null,
				'holdingtankcapacitymeasure' => isset($HoldingTankCapacityMeasure) ? $HoldingTankCapacityMeasure: Null,
				'holdingtankcountnumeric' => isset($value->HoldingTankCountNumeric) ? $value->HoldingTankCountNumeric : Null,
				'maximumspeedmeasure' => isset($MaximumSpeedMeasure) ? $MaximumSpeedMeasure : Null,
				'rangemeasure' => isset($value->RangeMeasure) ? $value->RangeMeasure : Null,
				'watertankcapacitymeasure' => isset($value->WaterTankCapacityMeasure) ? $value->WaterTankCapacityMeasure : Null,
				'watertankcountnumeric' => isset($value->WaterTankCountNumeric) ? $value->WaterTankCountNumeric : Null, //if
				'numberofengines' => isset($value->NumberOfEngines) ? $value->NumberOfEngines : Null, 
				'totalenginehoursnumeric' => isset($value->TotalEngineHoursNumeric) ? $value->TotalEngineHoursNumeric : Null, //if
				'totalenginepowerquantity' => isset($TotalEnginePowerQuantity) ? $TotalEnginePowerQuantity : Null,
				'registrationcountrycode' => isset($value->RegistrationCountryCode) ? $value->RegistrationCountryCode : Null,
				'generalboatdescription' => isset($value->GeneralBoatDescription[0]) ? str_replace("'","\'",$value->GeneralBoatDescription[0]) : Null,
				'additionaldetaildescription' => isset($value->AdditionalDetailDescription[0]) ? str_replace("'","\'",$AdditionalDetailDescription) : Null,
				'lat' => Null,
				'lng' => Null,
				'viewed' => Null,
				'isavailableforpls' => isset($value->IsAvailableForPls) ? $value->IsAvailableForPls : Null, 
				'ispricereduced' => isset($value->NormPrice) ? $value->NormPrice : Null,
				'ishot' => Null,
				'isdisplayedaftersold' => Null,
				'ispricehidden' => Null,
				'hascoop' => Null,
				'itemreceiveddate' => isset($value->ItemReceivedDate) ? $value->ItemReceivedDate : Null,
				'modifieddate' => isset($value->LastModificationDate) ? $value->LastModificationDate : Null,
				'conditionid' => isset($conditionids) ? $conditionids : Null,
				'hullid' => isset($hullids) ? $hullids : Null,
				'categoryid' => isset($categoryids) ? $categoryids : Null,
				'fuelid' => isset($fuelids) ? $fuelids : Null,
			);
			$save_obj->InsertUpdateBoat($data, $wpdb);
			//end
		}
		echo 'All the yachts boats has been imported successfully!';
	}

	private static function bail_on_activation( $message, $deactivate = true ) {
		?>
		<!doctype html>
		<html>
			<head>
				<meta charset="<?php bloginfo( 'charset' ); ?>" />
				<style>
				* {
					text-align: center;
					margin: 0;
					padding: 0;
					font-family: "Lucida Grande",Verdana,Arial,"Bitstream Vera Sans",sans-serif;
				}
				p {
					margin-top: 1em;
					font-size: 18px;
				}
				</style>
			</head>
			<body>
				<p><?php echo esc_html( $message ); ?></p>
			</body>
		</html>
		<?php
		if ( $deactivate ) {
			$plugins = get_option( 'active_plugins' );
			$boatsapi = plugin_basename( BOATS__PLUGIN_DIR . 'boatsapi.php' );
			$update  = false;
			foreach ( $plugins as $i => $plugin ) {
				if ( $plugin === $boatsapi ) {
					$plugins[$i] = false;
					$update = true;
				}
			}

			if ( $update ) {
				update_option( 'active_plugins', array_filter( $plugins ) );
			}
		}
		exit();
	}

	/**
	 * Attached to activate_{ plugin_basename( __FILES__ ) } by register_activation_hook()
	 * @static
	 */
	public static function plugin_activation() {
		if ( version_compare( $GLOBALS['wp_version'], BOATS__MINIMUM_WP_VERSION, '<' ) ) {
			load_plugin_textdomain( 'boatsapi' );
			
			$message = '<strong>'.sprintf(esc_html__( 'BoatsAPI %s requires WordPress %s or higher.' , 'boatsapi'), BOATS_VERSION, BOATS__MINIMUM_WP_VERSION ).'</strong> '.sprintf(__('Please <a href="%1$s">upgrade WordPress</a> to a current version, or <a href="%2$s">downgrade to version 2.4 of the BoatsAPI plugin</a>.', 'boatsapi'), 'https://techleadz.com/', 'https://techleadz.com/');

			self::bail_on_activation( $message );
		} elseif ( ! empty( $_SERVER['SCRIPT_NAME'] ) && false !== strpos( $_SERVER['SCRIPT_NAME'], '/wp-admin/plugins.php' ) ) {
			add_option( 'Activated_BoatsAPI', true );
			update_option( 'Activated_BoatsAPI', true );

			add_option('do_activation_redirect', true);

			if (! wp_next_scheduled ( 'boatsCroneSchedule' )) {
				wp_schedule_event(time(), 'every_six_h', 'boatsCroneSchedule');
			}
		}
	}

	/**
	 * Removes all wpdbection options
	 * @static
	 */
	public static function plugin_deactivation( ) {
		update_option('Activated_BoatsAPI',false);
		update_option('boats_api_option_name', ''); 
		update_option('boats_api_key', ''); 
		update_option('boats_api_key_url', ''); 
		delete_option('yacht_plugin_activation_key'); 
		delete_option('yacht_plugin_last_checked');
		delete_option('yacht_plugin_to_be_checked');
		delete_option('yacht_plugin_to_be_checked_again');
		/* 
			Do your custom stuff here... 
		*/

		global $wpdb;

		$table1 = $wpdb->prefix.'boats';
		$table2 = $wpdb->prefix.'agents';
		$table3 = $wpdb->prefix.'boat_categories';
		$table4 = $wpdb->prefix.'boat_conditions';
		$table5 = $wpdb->prefix.'boat_fuel';
		$table6 = $wpdb->prefix.'boat_hull_materials';
		$table7 = $wpdb->prefix.'boat_prices';
		$table8 = $wpdb->prefix.'boat_properties';
		$table9 = $wpdb->prefix.'engines';
		$table10 = $wpdb->prefix.'events';
		$table11 = $wpdb->prefix.'images';
		$table12 = $wpdb->prefix.'videos';
		$table13 = $wpdb->prefix.'offices';

        $wpdb->query("DROP TABLE IF EXISTS `$table1`");
        $wpdb->query("DROP TABLE IF EXISTS `$table2`");
        $wpdb->query("DROP TABLE IF EXISTS `$table3`");
        $wpdb->query("DROP TABLE IF EXISTS `$table4`");
        $wpdb->query("DROP TABLE IF EXISTS `$table5`");
        $wpdb->query("DROP TABLE IF EXISTS `$table6`");
        $wpdb->query("DROP TABLE IF EXISTS `$table7`");
        $wpdb->query("DROP TABLE IF EXISTS `$table8`");
        $wpdb->query("DROP TABLE IF EXISTS `$table9`");
        $wpdb->query("DROP TABLE IF EXISTS `$table10`");
        $wpdb->query("DROP TABLE IF EXISTS `$table11`");
        $wpdb->query("DROP TABLE IF EXISTS `$table12`");
        $wpdb->query("DROP TABLE IF EXISTS `$table13`");
	}
	
	/**
	 * Essentially a copy of WP's build_query but one that doesn't expect pre-urlencoded values.
	 *
	 * @param array $args An array of key => value pairs
	 * @return string A string ready for use as a URL query string.
	 */
	public static function build_query( $args ) {
		return _http_build_query( $args, '', '&' );
	}

}
?>