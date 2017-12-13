<?php 
/*
 * Trying to create a pay page
 */

require_once './serviceapi.php';

define('MERCHANT', 'mymail@gmail.com');
define('SECRET_KEY', 'AnzdEQcJMd1PWpfCMrGjPupE8315ixbSUL5y');

/******** Before payment **************/
if(!empty($_post)){
	$args = array();

	$args["email"] = $_post['email_address'];
	$args["firstname"] = $_post['firstname'];
    $args["lastname"] = $_post['last_name'];

	$args["amount"] = $_post['amount'];
	$args["product"] = $_post['amount'].' Pricing plan';

	// call paytabs
	payment_api_process($args);
}

/************ call paytabs api *************/
function payment_api_process($args){
	if(empty($args)){return false;}

	$amount = $args['amount'];
	$title = $args["firstname"].' '.$args["lastname"];
	$product = 'Upgrade Pricing Plan';

	$b_firstname = $args["firstname"];
	$b_lastname = $args["lastname"];
	$b_address = 'D-012,Media City';
	$b_state = 'Dubai';
	$b_email = $args["email"] ;
	$b_telephone =  00000000000;

	$s_firstname = $args["firstname"];
	$s_lastname = $args["lastname"];
	$s_address = 'D-012,Media City';
	$s_state = 'Dubai';
	$s_email = $args["email"] ;
	$s_telephone = 00000000000;


	$pt = new paytabs(MERCHANT, SECRET_KEY);

	$result = $pt->create_pay_page(array(
    
	//Customer's Personal Information
	'title' => $title , 			// Customer's Name on the invoice
	'cc_first_name' => $b_firstname, 		//This will be prefilled as Credit Card First Name
    'cc_last_name' => $b_lastname, 		//This will be prefilled as Credit Card Last Name
	'email' => $b_email,
    'cc_phone_number' => "971",
	'phone_number' => $b_telephone,
    
	//Customer's Billing Address (All fields are mandatory)
	//When the country is selected as USA or CANADA, the state field should contain a String of 2 characters containing the ISO state code otherwise the payments may be rejected. 
	//For other countries, the state can be a string of up to 32 characters.
	'billing_address' => $b_address,
    'city' => 'DSO',
    'state' => $b_state,
    'postal_code' => "97300",
    'country' => "ARE",
   
    //Customer's Shipping Address (All fields are mandatory)
	
	'address_shipping' => $s_address,
    'city_shipping' => 'DSO',
    'state_shipping' => $s_state,
    'postal_code_shipping' => "97300",
    'country_shipping' => "ARE",
   
    //Product Information
    "products_per_title"=> $product, //Product title of the product. If multiple products then add “||” separator
    'currency' => "AED",				//Currency of the amount stated. 3 character ISO currency code 
	"unit_price"=>$amount,					//Unit price of the product. If multiple products then add “||” separator.
    'quantity' => "1",					//Quantity of products. If multiple products then add “||” separator
	'other_charges' => "0",				//Additional charges. e.g.: shipping charges, taxes, VAT, etc.
	'amount' => $amount,  				//Amount of the products and other charges, it should be equal to: amount = (sum of all products’ (unit_price * quantity)) + other_charges
										//This field will be displayed in the invoice as the sub total field

	'discount'=>"0",					//Discount of the transaction. The Total amount of the invoice will be= amount - discount

	
	"msg_lang" => "english",			//Language of the PayPage to be created. Invalid or blank entries will default to English.(Englsh/Arabic)
    
    
    "reference_no" => $plan_id,		//Invoice reference number in your system
    "site_url" => "http://example.com", //The requesting website be exactly the same as the website/URL associated with your PayTabs Merchant Account
    'return_url' => "http://example.com/index.php?paid=true",
    "cms_with_version" => "CMS 4.8.2"
	));

	//echo "FOLLOWING IS THE RESPONSE: <br />";
	//print_r ($result);

	if($result->response_code == 4012){ // success to create payment page

		$_SESSION['payment_reference'] = $result->p_id;

		echo '<script type="text/javascript">
	           window.location = "'.$result->payment_url.'"
	      </script>';
	}else{

		echo $result->response_code;
		die('Failed to create pay page');
	}

}

/********* After Payment ******************/
if(isset($_REQUEST['paid']) && $_REQUEST['paid']){ 

	$payment_reference = isset($_SESSION['payment_reference'])? $_SESSION['payment_reference']: 0;

	$pt = new paytabs(MERCHANT, SECRET_KEY);
	$response = $pt->verify_payment($payment_reference);

	if($response->response_code == 100){ // verified payment success
		$_SESSION['reference_no'] =  $response->reference_no;

		header("Location: http://example.com/successful-payment/");
		die();

	}else{
		// payment transaction failed
		echo ($response->response_code);
		die;
	}
}
?>