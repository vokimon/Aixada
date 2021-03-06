<?php

//require_once('FirePHPCore/lib/FirePHPCore/FirePHP.class.php');
ob_start(); // Starts FirePHP output buffering

require_once("local_config/config.php");
require_once("inc/database.php");
require_once("utilities.php");
require_once("utilities_dates.php");
require_once("utilities_shop_and_order.php");



//$firephp = FirePHP::getInstance(true);

// This controls if the table_manager objects are stored in $_SESSION or not.
// It looks like doing it cuts down considerably on execution time.
$use_session_cache = configuration_vars::get_instance()->use_session_cache;



if (!isset($_SESSION)) {
    session_start();
 }

DBWrap::get_instance()->debug = true;

try{
	
  switch($_REQUEST['oper']) {
  	
	  case 'listAllOrderableProviders':
	  	printXML(stored_query_XML('get_orderable_providers', 'providers', 'name'));
	    exit;
	      
	  case 'getOrderableProducts4DateRange':
	  	printXML(stored_query_XML_fields('get_orderable_products_for_dates', get_param('fromDate'), get_param('toDate'),get_param('provider_id')));
	  	exit; 	
	  		
	  case 'toggleOrderableProduct':
	  	echo do_stored_query('toggle_orderable_product', get_param('product_id'), get_param('date'));
	    exit;
	        
	  case 'getTypeOrderableProducts':
	  	printXML(stored_query_XML_fields('get_type_orderable_products', get_param('provider_id') ));
	  	exit;
	  		
	  case 'activateProduct':
	  	echo do_stored_query('change_active_status_product', 1, get_param('product_id'));
	  	exit;
	  		
	  case 'deactivateProduct':
	  	echo do_stored_query('change_active_status_product', 0, get_param('product_id'));
	  	exit;
	  	
	  case 'generateDatePattern':
	  	echo generate_date_product_pattern(get_param('provider_id'), get_param('date'), get_param('weeklyFreq'),  get_param('nrMonth') );
	  	exit;

	  case 'modifyOrderClosingDate':
	  	echo do_stored_query('modify_order_closing_date', get_param('provider_id'), get_param('order_date'), get_param('closing_date'));
	    exit;    

  default:
    throw new Exception("ctrlActivateProducts: variable oper not set in query");
        
  }
} 

catch(Exception $e) {
  header('HTTP/1.0 401 ' . $e->getMessage());
  die ($e->getMessage());
}  


?>