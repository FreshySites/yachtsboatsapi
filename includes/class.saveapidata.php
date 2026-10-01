<?php

class SaveAllBoatsAPIData {

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



	public static function getBoatsFromAPI(){



		$key 			= get_option('boats_api_key');

		$api_url 		= get_option('boats_api_key_url');



		if(!$key && !$api_url){

			return false;

		}

		

		$url = $api_url.$key."&salesstatus=active,on-order,sale%20pending&rows=10000";



		$curl = curl_init();

		  curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
        'Accept: application/json',
        'User-Agent: Mozilla/5.0 (compatible; YachtImporter/1.5.4)'
    ),
));

		$response = curl_exec($curl);
		
		error_log("YACHT DEBUG: first 200 chars = " . substr($response, 0, 200));
        error_log("YACHT DEBUG: json_last_error = " . json_last_error() . " msg = " . json_last_error_msg());

        // DEBUG - add these 3 lines temporarily
        $curl_error = curl_error($curl);
        $curl_errno = curl_errno($curl);
        error_log("YACHT DEBUG: curl_errno=$curl_errno, curl_error=$curl_error, response_length=" . strlen($response));
        
        curl_close($curl);

		$response = json_decode($response);

		return $response->results;

	}



	public static function InsertQuery($query, $wpdb){
		global $wpdb;

		$res = $wpdb->query($query);

		if ($res === false) {
			error_log('Boats API SQL Error: ' . $wpdb->last_error);
			error_log('Boats API SQL Query: ' . $query);
			return array(
				'message' => false,
				'error'   => $wpdb->last_error
			);
		}

		return array(
			'last_id' => $wpdb->insert_id,
			'message' => true
		);
	}




	public static function IsCheckDataQuery($whereConditon, $nameTbl, $wpdb){

		global $wpdb;



		$result = $wpdb->get_results("SELECT * from `$nameTbl` where $whereConditon");



		if(isset($wpdb->num_rows)){

			if ($wpdb->num_rows == 0) {

				$message = array('isCheck' => true,'data' => "");	

			}else{

				$data = $result[0];

				$message = array('isCheck' => false,'data' => $data);

			}

		}else{

			if (empty($result)) {

				$message = array('isCheck' => true, 'data' => "");	

			}else{

				$data = $result[0];

				$message = array('isCheck' => false,'data' => $data);

			}

		}



		return $message;

	}



	public static function ErrorMessage($message){

		

		if(!empty($message)){

			echo $message;

		}



	}



	public static function InsertBoatCategories($data, $wpdb){

		global $wpdb;



		$code = $data['code'];

		$nameTbl = $wpdb->prefix.'boat_categories';

		$whereConditon = " code = '$code' ";

		$returnQuery = self::IsCheckDataQuery($whereConditon,$nameTbl,$wpdb);

		// print_r($returnQuery); die();

		if($returnQuery['isCheck'] == 1){

			

		  	$query = "insert into $nameTbl (code,title) values ('$code','$code')";

		 	$message = self::InsertQuery($query,$wpdb);

			if($message['message'] != true){

				$message = array('message' =>$message,'error' => true); 

			}else{

				// $message = array('last_id' => $message['last_id'],'error' => true); 
				
				$message = array('last_id' => $code,'error' => true); 
				
			}

			

		}

		else{

			//$message = array('last_id' => $returnQuery['data']->id);
			
			$message = array('last_id' => $code);

		}

		return $message;

	}



	public static function SelectBoatCategories($data, $wpdb){

		global $wpdb;

		

		$code = $data['code'];

		$table = $wpdb->prefix.'boat_categories';

		$query = "select code from `$table` where code = '$code'";

		

		$result = $wpdb->query($query);

		

		if (trim($result)==0) {

			return true;

		}else{

			return false;

		}

	}



	public static function InsertUpdateAgents($data,$wpdb){

		global $wpdb;

		

		$partid = $data['partid'];
		
		if (empty($partid)){
			$partid = 0;
		}

		$name = $data['name'];

		$message = esc_html($data['message']);
		$email = $data['email'];
		$phone = $data['phone'];

		$whereConditon = "partid = '$partid'";

		$nameTbl = $wpdb->prefix."agents"; 



		$returnQuery = self::IsCheckDataQuery($whereConditon, $nameTbl, $wpdb);

		

		if($returnQuery['isCheck'] == 1){

			

			$query = "insert into `$nameTbl` (partid,name,message,email,phone) values('$partid','$name','$message','$email','$phone')";

		}else{

			$query = "update `$nameTbl` set name = '$name', message = '$message' where partid = $partid ";

		}

		$message = self::InsertQuery($query,$wpdb);

		if($message['message'] != true){

			ErrorMessage($message);

		}



		return $message;

	}



	public static function InsertUpdateImage($data,$boatid){

		global $wpdb;

		$nameTbl = $wpdb->prefix."images";

		

		foreach ($data as $key => $value) {



			$whereConditon = " boatid = '$boatid' and url = '$value->Uri' ";

		    $returnQuery = self::IsCheckDataQuery($whereConditon, $nameTbl, $wpdb);

			

			if($returnQuery['isCheck'] == 1){

				
				$Uri     = $value->Uri ?? '';
				$Caption = $value->Caption ?? '';

				$query = $wpdb->prepare(
					"INSERT INTO `$nameTbl` (boatid, url, caption, priority)
					 VALUES (%d, %s, %s, %d)",
					$boatid, $Uri, $Caption, $value->Priority
				);

				
			}else{

				$id = $returnQuery['data'];

				$id = $id->id;

				$query = "update `$nameTbl` set url = '$value->Uri', caption = '$value->Caption', priority = '$value->Priority' where id = $id ";

			}

			

			$message = self::InsertQuery($query, $wpdb);

			if($message['message'] != true){

				ErrorMessage($message); 

			}

		}

	}



	public static function InsertUpdateVideo($data, $boatid, $wpdb){
		global $wpdb;

		$table = $wpdb->prefix . "videos";

		foreach ($data->url as $key => $url) {

			$thumbnail = $data->thumbnailUrl[$key] ?? '';
			$title     = $data->title[$key] ?? '';

			// check existing
			$existing = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT id FROM `$table`
					 WHERE boatid = %d AND url = %s AND thumbnailUrl = %s",
					$boatid, $url, $thumbnail
				)
			);

			if (!$existing) {
				$query = $wpdb->prepare(
					"INSERT INTO `$table` (boatid, url, thumbnailUrl, title)
					 VALUES (%d, %s, %s, %s)",
					$boatid, $url, $thumbnail, $title
				);
			} else {
				$query = $wpdb->prepare(
					"UPDATE `$table`
					 SET url = %s, thumbnailUrl = %s, title = %s
					 WHERE id = %d",
					$url, $thumbnail, $title, $existing->id
				);
			}

			self::InsertQuery($query, $wpdb);
		}
	}




	public static function InsertUpdateBoat($data,$wpdb){

		global $wpdb;

		$value = (object)$data;

		$nameTbl = $wpdb->prefix."boats"; 

		$whereConditon = " id = '$value->id' ";

		$returnQuery = self::IsCheckDataQuery($whereConditon, $nameTbl, $wpdb);

		// print_r($returnQuery); die();

	   if($returnQuery['isCheck'] == 1){

	  	 //$query = "INSERT INTO `$nameTbl` (id,yachtworldid,agentid,status,city,countrycode,statecode,price,make,model,nominallength,normnominallength,lengthoverall,year,boatname,brokername,companyname,buildername,designername,fulltextsearch,maxdraft,displacementmeasure,ballastweightmeasure,bridgeclearancemeasure,cabinheadroommeasure,beammeasure,deadrisemeasure,electricalcircuitmeasure,freeboardmeasure,fueltankcapacitymeasure,fueltankcountnumeric,holdingtankcapacitymeasure,holdingtankcountnumeric,maximumspeedmeasure,rangemeasure,watertankcapacitymeasure,watertankcountnumeric,numberofengines,totalenginehoursnumeric,totalenginepowerquantity,registrationcountrycode,generalboatdescription,additionaldetaildescription,lat,lng,viewed,isavailableforpls,ispricereduced,ishot,isdisplayedaftersold,ispricehidden,hascoop,itemreceiveddate,modifieddate,categoryid,conditionid,fuelid,hullid) VALUES('$value->id','$value->yachtworldid','$value->agentid','$value->status','$value->city','$value->countrycode','$value->statecode','$value->price','$value->make','$value->model','$value->nominallength','$value->normnominallength','$value->lengthoverall','$value->year','$value->boatname','$value->brokername','$value->companyname','$value->buildername','$value->designername','$value->fullserachtx','$value->maxdraft','$value->displacementmeasure','$value->ballastweightmeasure','$value->bridgeclearancemeasure','$value->cabinheadroommeasure','$value->beammeasure','$value->deadrisemeasure','$value->electricalcircuitmeasure','$value->freeboardmeasure','$value->fueltankcapacitymeasure','$value->fueltankcountnumeric','$value->holdingtankcapacitymeasure','$value->holdingtankcountnumeric','$value->maximumspeedmeasure','$value->rangemeasure','$value->watertankcapacitymeasure','$value->watertankcountnumeric','$value->numberofengines','$value->totalenginehoursnumeric','$value->totalenginepowerquantity','$value->registrationcountrycode','$value->generalboatdescription','$value->additionaldetaildescription','$value->lat','$value->lng','$value->viewed','$value->isavailableforpls','$value->ispricereduced','$value->ishot','$value->isdisplayedaftersold','$value->ispricehidden','$value->hascoop','$value->itemreceiveddate','$value->modifieddate','$value->categoryid','$value->conditionid','$value->fuelid','$value->hullid')";								
	  	 
	  	 /*
	  	    $query = "INSERT INTO `$nameTbl` (
			id, yachtworldid, agentid, status, city, countrycode, statecode, price, make, model, nominallength, 
			normnominallength, lengthoverall, year, boatname, brokername, companyname, buildername, designername, 
			fulltextsearch, maxdraft, displacementmeasure, ballastweightmeasure, bridgeclearancemeasure, 
			cabinheadroommeasure, beammeasure, deadrisemeasure, electricalcircuitmeasure, freeboardmeasure, 
			fueltankcapacitymeasure, fueltankcountnumeric, holdingtankcapacitymeasure, holdingtankcountnumeric, 
			maximumspeedmeasure, rangemeasure, watertankcapacitymeasure, watertankcountnumeric, numberofengines, 
			totalenginehoursnumeric, totalenginepowerquantity, registrationcountrycode, generalboatdescription, 
			additionaldetaildescription, lat, lng, viewed, isavailableforpls, ispricereduced, ishot, 
			isdisplayedaftersold, ispricehidden, hascoop, itemreceiveddate, modifieddate, categoryid, 
			conditionid, fuelid, hullid
		) VALUES (
			'".mysqli_real_escape_string($wpdb->dbh, $value->id)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->yachtworldid)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->agentid)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->status)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->city)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->countrycode)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->statecode)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->price)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->make)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->model)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->nominallength)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->normnominallength)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->lengthoverall)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->year)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->boatname)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->brokername)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->companyname)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->buildername)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->designername)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->fullserachtx)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->maxdraft)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->displacementmeasure)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->ballastweightmeasure)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->bridgeclearancemeasure)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->cabinheadroommeasure)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->beammeasure)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->deadrisemeasure)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->electricalcircuitmeasure)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->freeboardmeasure)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->fueltankcapacitymeasure)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->fueltankcountnumeric)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->holdingtankcapacitymeasure)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->holdingtankcountnumeric)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->maximumspeedmeasure)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->rangemeasure)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->watertankcapacitymeasure)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->watertankcountnumeric)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->numberofengines)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->totalenginehoursnumeric)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->totalenginepowerquantity)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->registrationcountrycode)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->generalboatdescription)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->additionaldetaildescription)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->lat)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->lng)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->viewed)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->isavailableforpls)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->ispricereduced)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->ishot)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->isdisplayedaftersold)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->ispricehidden)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->hascoop)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->itemreceiveddate)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->modifieddate)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->categoryid)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->conditionid)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->fuelid)."',
			'".mysqli_real_escape_string($wpdb->dbh, $value->hullid)."'
		)";
		
		*/
		
		
		$query = "INSERT INTO `$nameTbl` (
    id, yachtworldid, agentid, status, city, countrycode, statecode, price, make, model, nominallength, 
    normnominallength, lengthoverall, year, boatname, brokername, companyname, buildername, designername, 
    fulltextsearch, maxdraft, displacementmeasure, ballastweightmeasure, bridgeclearancemeasure, 
    cabinheadroommeasure, beammeasure, deadrisemeasure, electricalcircuitmeasure, freeboardmeasure, 
    fueltankcapacitymeasure, fueltankcountnumeric, holdingtankcapacitymeasure, holdingtankcountnumeric, 
    maximumspeedmeasure, rangemeasure, watertankcapacitymeasure, watertankcountnumeric, numberofengines, 
    totalenginehoursnumeric, totalenginepowerquantity, registrationcountrycode, generalboatdescription, 
    additionaldetaildescription, lat, lng, viewed, isavailableforpls, ispricereduced, ishot, 
    isdisplayedaftersold, ispricehidden, hascoop, itemreceiveddate, modifieddate, categoryid, 
    conditionid, fuelid, hullid
) VALUES (
    '".mysqli_real_escape_string($wpdb->dbh, $value->id ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->yachtworldid ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->agentid ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->status ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->city ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->countrycode ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->statecode ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->price ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->make ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->model ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->nominallength ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->normnominallength ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->lengthoverall ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->year ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->boatname ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->brokername ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->companyname ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->buildername ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->designername ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->fullserachtx ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->maxdraft ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->displacementmeasure ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->ballastweightmeasure ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->bridgeclearancemeasure ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->cabinheadroommeasure ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->beammeasure ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->deadrisemeasure ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->electricalcircuitmeasure ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->freeboardmeasure ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->fueltankcapacitymeasure ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->fueltankcountnumeric ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->holdingtankcapacitymeasure ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->holdingtankcountnumeric ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->maximumspeedmeasure ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->rangemeasure ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->watertankcapacitymeasure ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->watertankcountnumeric ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->numberofengines ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->totalenginehoursnumeric ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->totalenginepowerquantity ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->registrationcountrycode ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->generalboatdescription ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->additionaldetaildescription ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->lat ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->lng ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->viewed ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->isavailableforpls ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->ispricereduced ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->ishot ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->isdisplayedaftersold ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->ispricehidden ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->hascoop ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->itemreceiveddate ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->modifieddate ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->categoryid ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->conditionid ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->fuelid ?? '')."',
    '".mysqli_real_escape_string($wpdb->dbh, $value->hullid ?? '')."'
)";

	   	}



	   	else{



		    //$query = "UPDATE `$nameTbl` SET yachtworldid = '$value->yachtworldid', agentid = '$value->agentid', status = '$value->status', city = '$value->city', countrycode = '$value->countrycode',statecode = '$value->statecode',price = '$value->price',make = '$value->make',model = '$value->model',nominallength = '$value->nominallength',normnominallength = '$value->normnominallength',lengthoverall = '$value->lengthoverall',year = '$value->year',boatname = '$value->boatname',brokername = '$value->brokername',companyname = '$value->companyname',buildername = '$value->buildername',designername = '$value->designername',fulltextsearch = '$value->fullserachtx',maxdraft = '$value->maxdraft',displacementmeasure = '$value->displacementmeasure',ballastweightmeasure = '$value->ballastweightmeasure',bridgeclearancemeasure = '$value->bridgeclearancemeasure',cabinheadroommeasure= '$value->cabinheadroommeasure',beammeasure = '$value->beammeasure',deadrisemeasure = '$value->deadrisemeasure',electricalcircuitmeasure = '$value->electricalcircuitmeasure',freeboardmeasure = '$value->freeboardmeasure',fueltankcapacitymeasure = '$value->fueltankcapacitymeasure',fueltankcountnumeric  = '$value->fueltankcountnumeric',holdingtankcapacitymeasure  = '$value->holdingtankcapacitymeasure',holdingtankcountnumeric  = '$value->holdingtankcountnumeric',maximumspeedmeasure  = '$value->maximumspeedmeasure',rangemeasure  = '$value->rangemeasure',watertankcapacitymeasure  = '$value->watertankcapacitymeasure',watertankcountnumeric  = '$value->watertankcountnumeric',numberofengines  = '$value->numberofengines',totalenginehoursnumeric  = '$value->totalenginehoursnumeric',totalenginepowerquantity  = '$value->totalenginepowerquantity',registrationcountrycode  = '$value->registrationcountrycode',generalboatdescription = '$value->generalboatdescription',additionaldetaildescription = '$value->additionaldetaildescription',lat = '$value->lat',lng  = '$value->lng',viewed  = '$value->viewed',isavailableforpls  = '$value->isavailableforpls',ispricereduced  = '$value->ispricereduced',ishot  = '$value->ishot',isdisplayedaftersold  = '$value->isdisplayedaftersold',ispricehidden  = '$value->ispricehidden',hascoop  = '$value->hascoop',itemreceiveddate  = '$value->itemreceiveddate',modifieddate = '$value->modifieddate' WHERE id = '$value->id'";

				
$query = $wpdb->prepare(
    "UPDATE `$nameTbl` SET
        yachtworldid = %s,
        agentid = %s,
        status = %s,
        city = %s,
        countrycode = %s,
        statecode = %s,
        price = %s,
        make = %s,
        model = %s,
        nominallength = %s,
        normnominallength = %s,
        lengthoverall = %s,
        year = %s,
        boatname = %s,
        brokername = %s,
        companyname = %s,
        buildername = %s,
        designername = %s,
        fulltextsearch = %s,
        maxdraft = %s,
        displacementmeasure = %s,
        ballastweightmeasure = %s,
        bridgeclearancemeasure = %s,
        cabinheadroommeasure = %s,
        beammeasure = %s,
        deadrisemeasure = %s,
        electricalcircuitmeasure = %s,
        freeboardmeasure = %s,
        fueltankcapacitymeasure = %s,
        fueltankcountnumeric = %s,
        holdingtankcapacitymeasure = %s,
        holdingtankcountnumeric = %s,
        maximumspeedmeasure = %s,
        rangemeasure = %s,
        watertankcapacitymeasure = %s,
        watertankcountnumeric = %s,
        numberofengines = %s,
        totalenginehoursnumeric = %s,
        totalenginepowerquantity = %s,
        registrationcountrycode = %s,
        generalboatdescription = %s,
        additionaldetaildescription = %s,
        lat = %s,
        lng = %s,
        viewed = %s,
        isavailableforpls = %s,
        ispricereduced = %s,
        ishot = %s,
        isdisplayedaftersold = %s,
        ispricehidden = %s,
        hascoop = %s,
        itemreceiveddate = %s,
        modifieddate = %s
     WHERE id = %d",
    $value->yachtworldid,
    $value->agentid,
    $value->status,
    $value->city,
    $value->countrycode,
    $value->statecode,
    $value->price,
    $value->make,
    $value->model,
    $value->nominallength,
    $value->normnominallength,
    $value->lengthoverall,
    $value->year,
    $value->boatname,
    $value->brokername,
    $value->companyname,
    $value->buildername,
    $value->designername,
    $value->fullserachtx,
    $value->maxdraft,
    $value->displacementmeasure,
    $value->ballastweightmeasure,
    $value->bridgeclearancemeasure,
    $value->cabinheadroommeasure,
    $value->beammeasure,
    $value->deadrisemeasure,
    $value->electricalcircuitmeasure,
    $value->freeboardmeasure,
    $value->fueltankcapacitymeasure,
    $value->fueltankcountnumeric,
    $value->holdingtankcapacitymeasure,
    $value->holdingtankcountnumeric,
    $value->maximumspeedmeasure,
    $value->rangemeasure,
    $value->watertankcapacitymeasure,
    $value->watertankcountnumeric,
    $value->numberofengines,
    $value->totalenginehoursnumeric,
    $value->totalenginepowerquantity,
    $value->registrationcountrycode,
    $value->generalboatdescription,
    $value->additionaldetaildescription,
    $value->lat,
    $value->lng,
    $value->viewed,
    $value->isavailableforpls,
    $value->ispricereduced,
    $value->ishot,
    $value->isdisplayedaftersold,
    $value->ispricehidden,
    $value->hascoop,
    $value->itemreceiveddate,
    $value->modifieddate,
    $value->id
);


	   		}

			

			$message = self::InsertQuery($query, $wpdb);

			 //print_r($message['message']);die();

			if($message['message'] != true){

				ErrorMessage($message); 

			}



	}



	public static function InsertUpdateEngines($data){

		global $wpdb;

		$data = json_decode($data, true);

		// print_r($data);

		$boatid = $data['boatid'];

		$make = $data['make'];

		$model = $data['model'];

		$fuel = $data['fuel'];

		$enginepower = $data['enginepower'];

		$type = $data['type'];

		$year = $data['year'];

		$hours = $data['hours'];

		$BoatEngineLocationCode = $data['BoatEngineLocationCode'];

		$DriveTransmissionDescription = $data['DriveTransmissionDescription'];

		$PropellerType = $data['PropellerType'];

		

		$nameTbl = $wpdb->prefix."engines"; 

		$whereConditon = " boatid = '$boatid' ";

		$returnQuery = self::IsCheckDataQuery($whereConditon,$nameTbl,$wpdb);



		if($returnQuery['isCheck'] == 1){

			

		  $query = "insert into `$nameTbl` (make,model,fuel,enginepower,type,year,hours,BoatEngineLocationCode,DriveTransmissionDescription,PropellerType,boatid) values ('$make','$model','$fuel','$enginepower','$type','$year','$hours','$BoatEngineLocationCode','$DriveTransmissionDescription','$PropellerType','$boatid')";

		}else{

			$message = array('last_id' => $returnQuery['data']->id);



			$id = $returnQuery['data'];

			$id = $id->id;

			$query = "update `$nameTbl` set make = '$make', model = '$model', fuel = '$fuel', enginepower = '$enginepower', type = '$type', year = '$year', hours = '$hours', BoatEngineLocationCode = '$BoatEngineLocationCode', DriveTransmissionDescription = '$DriveTransmissionDescription', PropellerType = '$PropellerType' where id = $id ";

		}

		

		$message = self::InsertQuery($query, $wpdb);

		if($message['message'] != true){

			ErrorMessage($message);

		}

	}



	public static function InsertUpdateFuel($data,$wpdb){

		global $wpdb;

		$boatid = $data['boatid'];

		$code = $data['fuel'];

		

		$nameTbl = $wpdb->prefix."boat_fuel"; 

		$whereConditon = " code = '$code' ";

		$returnQuery = self::IsCheckDataQuery($whereConditon,$nameTbl,$wpdb);

		// print_r($returnQuery); die();



		if($returnQuery['isCheck'] == 1){

			

		  	$query = "insert into `$nameTbl` (boatid,code,title) values ('$boatid','$code','$code')";

		 	$message = self::InsertQuery($query,$wpdb);

			if($message['message'] != true){

				$message = array('message' =>$message,'error' => true); 

			}else{

				// $message = array('last_id' => $message['last_id'],'error' => true); 
				
				$message = array('last_id' => $code,'error' => true); 

			}



		}

		else{

			// $message = array('last_id' => $returnQuery['data']->id);
			
			$message = array('last_id' => $code);

		}

		return $message;

	}



	public static function InsertUpdateHullMaterials($data,$wpdb){

		global $wpdb;

		$code = $data['code'];

		$nameTbl = $wpdb->prefix."boat_hull_materials"; 

		$whereConditon = " code = '$code' ";

		$returnQuery = self::IsCheckDataQuery($whereConditon, $nameTbl, $wpdb);

		if($returnQuery['isCheck'] == 1){

			

		  	$query = "insert into `$nameTbl` (code,title) values ('$code','$code')";

		 	$message = self::InsertQuery($query,$wpdb);



			if($message['message'] != true){

				$message = array('message' =>$message, 'error' => true); 

			}else{

				//$message = array('last_id' => $message['last_id'],'error' => true); 
				
				$message = array('last_id' => $code,'error' => true); 

			}



		}

		else{

			//$message = array('last_id' => $returnQuery['data']->id);
			
			$message = array('last_id' => $code);

		}

		return $message;



	}



	public static function InsertUpdateConditions($data, $wpdb){

		global $wpdb;

		$code = $data['code'];

		$nameTbl = $wpdb->prefix."boat_conditions"; 

		$whereConditon = " code = '$code' ";

		$returnQuery = self::IsCheckDataQuery($whereConditon, $nameTbl, $wpdb);

		if($returnQuery['isCheck'] == 1){

			

		  	$query = "insert into `$nameTbl` (code, title) values ('$code', '$code')";

		 	$message = self::InsertQuery($query, $wpdb);

			if($message['message'] != true){

				$message = array('message' =>$message,'error' => true); 

			}else{

				//$message = array('last_id' => $message['last_id'],'error' => true); 
				
				$message = array('last_id' => $code,'error' => true); 

			}



		}

		else{

			//$message = array('last_id' => $returnQuery['data']->id);
			
			$message = array('last_id' => $code);

		}

		return $message;

	}



	public static function InsertUpdateBoatPrice($data,$wpdb){

		global $wpdb;

		$boatid = $data['boatid'];

		$old = $data['old'];

		$new = $data['new'];



		$nameTbl = $wpdb->prefix."boat_prices"; 

		$whereConditon = " boatid = '$boatid' ";

		$returnQuery = self::IsCheckDataQuery($whereConditon,$nameTbl,$wpdb);

			

		if($returnQuery['isCheck'] == true){

			

			$query = "insert into `$nameTbl` (boatid,old,new) values ('$boatid','$old','$new')";

		

		}else{

			

			$id = $returnQuery['data'];

			$id = $id->id;

			$query = "update `$nameTbl` set old = '$old', new = '$new' where id = '$id' ";

		}

		$message = self::InsertQuery($query,$wpdb);



		if($message['message'] != true){

			ErrorMessage($message); 

		}



	}

}

?>
