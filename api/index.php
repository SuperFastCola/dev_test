<?php
header('Content-type: application/json');

$message = new stdClass();
$message->results = array("No Results Found");

$request = explode("/",$_SERVER["REQUEST_URI"]);
$request = $request[sizeof($request)-1];

error_log(json_encode($_REQUEST));


if(isset($request)){
	switch($request){
		case 'jobs':
			//if request uri matchs jobs
			define('ROOT_DIR', preg_replace("/\/api/","", dirname(realpath(__FILE__))) . "/"); // need to add trailing slash
			require(ROOT_DIR . "config.php");
			require(ROOT_DIR  . "includes/connection.php");	

			$search_statement = "where title is not null and company is not null";

			//get search string match
			if(isset($_REQUEST["search"]) && preg_match("/\w/",$_REQUEST["search"])){

				//clean evil characters
				$search_criteria = $myDB->clean($_REQUEST["search"]);
				$search_statement .= " and (id like '%" . $search_criteria .  "%'" .
									" or code like '%" . $search_criteria .  "%'" .
									" or company like '%" . $search_criteria .  "%'" .
									" or title like '%" . $search_criteria .  "%') ";		
			}



			if(isset($_REQUEST["ids"]) && gettype($_REQUEST["ids"])=="array" && sizeof($_REQUEST["ids"])>0 ){
				$ids_part = " and id in (" . implode(",",$_REQUEST["ids"]) . ")";
			}
			else{
				$ids_part = "";
			}

			//get direction of results
			$direction = (isset($_REQUEST["dir"]) && preg_match("/^(asc|desc)$/i", $_REQUEST["dir"]) )?$_REQUEST["dir"]:'asc';


			//get sort by order
			$order = (isset($_REQUEST["order"]) && preg_match("/^(title|code|company|created)$/i",$_REQUEST["order"]) )?' order by ' . $_REQUEST["order"] . ' ' . $direction:' order by title ' . $direction;

			//build query
			$query = "select *, DATE_FORMAT(created,'%c/%e/%Y') as niceDate, LOWER(DATE_FORMAT(created,'%l:%i%p')) as niceTime from job_listings " . $search_statement . $ids_part . $order . " limit 20";
			$myDB->execute($query);

			error_log($query);

			//if no results returned.
			if($myDB->dataRows()>0){
				$message->results = $myDB->fetchObject(true);	
			}

		break;
	}
}

echo json_encode($message);
?>