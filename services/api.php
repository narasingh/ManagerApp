<?php
 	require_once("Rest.inc.php");
	
	class API extends REST {
	
		public $data = "";
		
		const DB_SERVER = "localhost";
		const DB_USER = "root";
		const DB_PASSWORD = "";
		const DB = "angularcode_customer";

		private $db = NULL;
		private $mysqli = NULL;
		public function __construct(){
			parent::__construct();				// Init parent contructor
			$this->dbConnect();					// Initiate Database connection
		}
		
		/*
		 *  Connect to Database
		*/
		private function dbConnect(){
			$this->mysqli = new mysqli(self::DB_SERVER, self::DB_USER, self::DB_PASSWORD, self::DB);
		}
		
		/*
		 * Dynmically call the method based on the query string
		 */
		public function processApi(){
			$func = strtolower(trim(str_replace("/","",$_REQUEST['x'])));
			if((int)method_exists($this,$func) > 0)
				$this->$func();
			else
				$this->response('',404); // If the method not exist with in this class "Page not found".
		}
				
		private function login(){
			if($this->get_request_method() != "POST"){
				$this->response('',406);
			}
			$email = $this->_request['email'];		
			$password = $this->_request['pwd'];
			if(!empty($email) and !empty($password)){
				if(filter_var($email, FILTER_VALIDATE_EMAIL)){
					$query="SELECT uid, name, email FROM users WHERE email = '$email' AND password = '".md5($password)."' LIMIT 1";
					$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);

					if($r->num_rows > 0) {
						$result = $r->fetch_assoc();	
						// If success everythig is good send header as "OK" and user details
						$this->response($this->json($result), 200);
					}
					$this->response('', 204);	// If no records "No Content" status
				}
			}
			
			$error = array('status' => "Failed", "msg" => "Invalid Email address or Password");
			$this->response($this->json($error), 400);
		}
		
		private function customers(){	
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}
			$query="SELECT distinct c.customerNumber, c.customerName, c.email, c.address, c.city, c.state, c.postalCode, c.country FROM angularcode_customers c order by c.customerNumber desc";
			$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);

			if($r->num_rows > 0){
				$result = array();
				while($row = $r->fetch_assoc()){
					$result[] = $row;
				}
				$this->response($this->json($result), 200); // send user details
			}
			$this->response('',204);	// If no records "No Content" status
		}
		private function customer(){	
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}
			$id = (int)$this->_request['id'];
			if($id > 0){	
				$query="SELECT distinct c.customerNumber, c.customerName, c.email, c.address, c.city, c.state, c.postalCode, c.country FROM angularcode_customers c where c.customerNumber=$id";
				$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
				if($r->num_rows > 0) {
					$result = $r->fetch_assoc();	
					$this->response($this->json($result), 200); // send user details
				}
			}
			$this->response('',204);	// If no records "No Content" status
		}
		
		private function insertCustomer(){
			if($this->get_request_method() != "POST"){
				$this->response('',406);
			}

			$customer = json_decode(file_get_contents("php://input"),true);
			$column_names = array('customerName', 'email', 'city', 'address', 'country');
			$keys = array_keys($customer);
			$columns = '';
			$values = '';
			foreach($column_names as $desired_key){ // Check the customer received. If blank insert blank into the array.
			   if(!in_array($desired_key, $keys)) {
			   		$$desired_key = '';
				}else{
					$$desired_key = $customer[$desired_key];
				}
				$columns = $columns.$desired_key.',';
				$values = $values."'".$$desired_key."',";
			}
			$query = "INSERT INTO angularcode_customers(".trim($columns,',').") VALUES(".trim($values,',').")";
			if(!empty($customer)){
				$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
				$success = array('status' => "Success", "msg" => "Customer Created Successfully.", "data" => $customer);
				$this->response($this->json($success),200);
			}else
				$this->response('',204);	//"No Content" status
		}
		private function updateCustomer(){
			if($this->get_request_method() != "POST"){
				$this->response('',406);
			}
			$customer = json_decode(file_get_contents("php://input"),true);
			$id = (int)$customer['id'];
			$column_names = array('customerName', 'email', 'city', 'address', 'country');
			$keys = array_keys($customer['customer']);
			$columns = '';
			$values = '';
			foreach($column_names as $desired_key){ // Check the customer received. If key does not exist, insert blank into the array.
			   if(!in_array($desired_key, $keys)) {
			   		$$desired_key = '';
				}else{
					$$desired_key = $customer['customer'][$desired_key];
				}
				$columns = $columns.$desired_key."='".$$desired_key."',";
			}
			$query = "UPDATE angularcode_customers SET ".trim($columns,',')." WHERE customerNumber=$id";
			if(!empty($customer)){
				$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
				$success = array('status' => "Success", "msg" => "Customer ".$id." Updated Successfully.", "data" => $customer);
				$this->response($this->json($success),200);
			}else
				$this->response('',204);	// "No Content" status
		}
		
		private function deleteCustomer(){
			if($this->get_request_method() != "DELETE"){
				$this->response('',406);
			}
			$id = (int)$this->_request['id'];
			if($id > 0){				
				$query="DELETE FROM angularcode_customers WHERE customerNumber = $id";
				$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
				$success = array('status' => "Success", "msg" => "Successfully deleted one record.");
				$this->response($this->json($success),200);
			}else
				$this->response('',204);	// If no records "No Content" status
		}

		private function products(){

			$products_json = '{  
								   "products":[  
								      {  
								         "ID":0,
								         "Name":"Bread",
								         "Description":"Whole grain bread",
								         "ReleaseDate":"1992-01-01T00:00:00",
								         "DiscontinuedDate":null,
								         "Rating":4,
								         "Price":2.5
								      },
								      {  
								         "ID":1,
								         "Name":"Milk",
								         "Description":"Low fat milk",
								         "ReleaseDate":"1995-10-01T00:00:00",
								         "DiscontinuedDate":null,
								         "Rating":3,
								         "Price":3.5
								      },
								      {  
								         "ID":2,
								         "Name":"Vint soda",
								         "Description":"Americana Variety - Mix of 6 flavors",
								         "ReleaseDate":"2000-10-01T00:00:00",
								         "DiscontinuedDate":null,
								         "Rating":3,
								         "Price":20.9
								      },
								      {  
								         "ID":3,
								         "Name":"Havina Cola",
								         "Description":"The Original Key Lime Cola",
								         "ReleaseDate":"2005-10-01T00:00:00",
								         "DiscontinuedDate":"2006-10-01T00:00:00",
								         "Rating":3,
								         "Price":19.9
								      },
								      {  
								         "ID":4,
								         "Name":"Fruit Punch",
								         "Description":"Mango flavor, 8.3 Ounce Cans (Pack of 24)",
								         "ReleaseDate":"2003-01-05T00:00:00",
								         "DiscontinuedDate":null,
								         "Rating":3,
								         "Price":22.99
								      },
								      {  
								         "ID":5,
								         "Name":"Cranberry Juice",
								         "Description":"16-Ounce Plastic Bottles (Pack of 12)",
								         "ReleaseDate":"2006-08-04T00:00:00",
								         "DiscontinuedDate":null,
								         "Rating":3,
								         "Price":22.8
								      },
								      {  
								         "ID":6,
								         "Name":"Pink Lemonade",
								         "Description":"36 Ounce Cans (Pack of 3)",
								         "ReleaseDate":"2006-11-05T00:00:00",
								         "DiscontinuedDate":null,
								         "Rating":3,
								         "Price":18.8
								      },
								      {  
								         "ID":7,
								         "Name":"DVD Player",
								         "Description":"1080P Upconversion DVD Player",
								         "ReleaseDate":"2006-11-15T00:00:00",
								         "DiscontinuedDate":null,
								         "Rating":5,
								         "Price":35.88
								      },
								      {  
								         "ID":8,
								         "Name":"LCD HDTV",
								         "Description":"42 inch 1080p LCD with Built-in Blu-ray Disc Player",
								         "ReleaseDate":"2008-05-08T00:00:00",
								         "DiscontinuedDate":null,
								         "Rating":3,
								         "Price":1088.8
								      },
								      {  
								         "odata.type":"ODataDemo.FeaturedProduct",
								         "ID":9,
								         "Name":"Lemonade",
								         "Description":"Classic, refreshing lemonade (Single bottle)",
								         "ReleaseDate":"1970-01-01T00:00:00",
								         "DiscontinuedDate":null,
								         "Rating":7,
								         "Price":1.01
								      },
								      {  
								         "odata.type":"ODataDemo.FeaturedProduct",
								         "ID":10,
								         "Name":"Coffee",
								         "Description":"Bulk size can of instant coffee",
								         "ReleaseDate":"1982-12-31T00:00:00",
								         "DiscontinuedDate":null,
								         "Rating":1,
								         "Price":6.99
								      }
								   ]
								}';

			$this->response($products_json, 200);			

		}
		
		/*
		 *	Encode array into JSON
		*/
		private function json($data){
			if(is_array($data)){
				return json_encode($data);
			}
		}
	}
	
	// Initiiate Library
	
	$api = new API;
	$api->processApi();
?>