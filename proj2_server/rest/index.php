<?php
$request_uri = $_SERVER['REQUEST_URI'];
$doc_root = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT');
$dirs = explode(DIRECTORY_SEPARATOR, __DIR__);
array_pop($dirs); // remove last element
$project_root = implode('/', $dirs) . '/';
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', '0'); // would mess up response
ini_set('log_errors', 1);
// the following file needs to exist, be accessible to apache
// and writable (chmod 777 php-server-errors.log)
ini_set('error_log', $project_root . 'php-server-errors.log');
set_include_path($project_root);
// app_path is the part of $project_root past $doc_root
$app_path = substr($project_root, strlen($doc_root));
// project uri is the part of $request_uri past $app_path, not counting its last /
$project_uri = substr($request_uri, strlen($app_path) - 1);
$parts = explode('/', $project_uri);
// like  /rest/product/1 ;
//     0    1     2    3    

require_once('model/database.php');
require_once('model/product_db.php');
require_once('model/order_db.php');
require_once('model/day.php');

$server = $_SERVER['HTTP_HOST'];
$method = $_SERVER['REQUEST_METHOD'];
$proto = isset($_SERVER['HTTPS']) ? 'https:' : 'http:';
$url = $proto . '//' . $server . $request_uri;
$resource = trim($parts[2]);
$id = $parts[3];
error_log('starting REST server request, method=' . $method . ', uri = ...'. $project_uri);

switch ($resource) {
    // Access the specified product
    case 'products':
        error_log('request at case product');
        switch ($method) {
            case 'GET':
                handle_get_product($id);
                break;
            case 'POST':
                handle_post_product($url);
                break;
            default:
                $error_message = 'bad HTTP method : ' . $method;
                include_once('errors/server_error.php');
                server_error(405, $error_message);
                break;
        }
        break;
    
    case 'day':
        error_log('request at case day');
        switch ($method) {
            case 'GET':
                $day = get_server_presentday($db);
                handle_get_day($day);
                break;
            case 'POST':
                $new_day = handle_post_day();
                error_log('To see day on server '.$new_day);
                if($new_day == '0'){
                    reinitialize_orders_1();
                    set_server_day($db, 1);
                }
                else{
                     set_server_day($db, $new_day);
                }
                break;
            default:
                $error_message = 'bad HTTP method : ' . $method;
                include_once('errors/server_error.php');
                server_error(405, $error_message);
                break;
        }
        break;
        
    case 'orders':
        error_log('request at case orders');
        switch ($method) {
            case 'GET':
                $presentday = get_server_presentday($db);
                if(isset($id)){
                 handle_get_order($id,$presentday);
                 
                }
                else{
                    handle_get_every_orders();
                    }
                break;
            case 'POST':
                $presentday1 = get_server_presentday($db);
                handle_post_orders($url,$presentday1);
                //handle_post_orders($url);
                break;
          default:
                $error_message = 'bad HTTP method : ' . $method;
                include_once('errors/server_error.php');
                server_error(405, $error_message);
                break;
        }
        break;
        
     default:
        $error_message = 'Unknown REST resource: ' . $resource;
        include_once('errors/server_error.php');
        server_error(400, $error_message);
        break;

}

function handle_get_product($product_id) {
    $product = get_product($product_id);
    $data = json_encode($product);
    error_log('hi from handle_get_product');
    echo $data;
}

function handle_get_every_orders() 
{
    $everyorders = get_every_orders();
    $data = json_encode($everyorders);
    error_log('handle_every_orders');
    echo $data;
}


function handle_post_product($url) {
    $bodyJson = file_get_contents('php://input');
    error_log('Server saw post data' . $bodyJson);
    $body = json_decode($bodyJson, true);
    try {
        $product_id = add_product($body['categoryID'], $body['productCode'], 
                $body['productName'], $body['description'], $body['listPrice'],
                $body['discountPercent']);
        // return new URI in Location header
        $locHeader = 'Location: ' . $url . $product_id;
        header($locHeader, true, 201);  // needs 3 args to set code 201 
        error_log('hi from handle_post_product, header = ' . $locHeader);
    } catch (PDOException $e) {
        $error_message = 'Insert failed: ' . $e->getMessage();
        include_once('errors/server_error.php');
        server_error(400, $error_message);
    }
}

function handle_get_order($order_id,$presentday)
{
    $today = $presentday;
    $order = get_order($order_id);
    $orderitem = get_order_items($order_id);
    if($order['deliveryDay'] <= $today)
       {
        $dlvr = true;
        
       } 
    else{
           $dlvr = false;
           
       }

    $body1 = array('customerID'=>$order['customerID'],
        'orderID' => $order['orderID'], 'delivered' => $dlvr);
    $items = array();
    foreach ($orderitem as $item){
        $item = array('productID' => $item['productID'], 'quantity' => $item['quantity']);
        array_push($items, $item);
}
    $orderdata = array($body1,$items);
    $data = json_encode($orderdata);
    error_log('hi from handle_get_order');
    echo $data;
}

function handle_post_orders($url,$presentday1) {
//function handle_post_orders($url) {
   $today = $presentday1;
    //$today = get_server_presentday($db);
    try {
        $bodyJson = file_get_contents('php://input');
        error_log('Server saw post data' . $bodyJson);
        $body = json_decode($bodyJson, true);
         
           error_log('today '.$today);
           $date = date("y-m-d H:i:s");
           $deliveryday = rand(($today+1),($today+2));
           $orderID = add_order($body['customerID'], $date, $deliveryday);
           foreach ($body['items'] as $value){
               $product = get_product($value['productID']);
               add_order_item($orderID, $value['productID'], $product['listPrice'], $product['discountPercent'], $value['quantity']);
            }
           
        // return new URI in Location header
        $locHeader = 'Location: ' . $url . $orderID;
        header($locHeader, true, 201);  // needs 3 args to set code 201 
        error_log('hi from handle_post_product, header = ' . $locHeader);
    } catch (PDOException $e) {
        $error_message = 'Insert failed: ' . $e->getMessage();
        include_once('errors/server_error.php');
        server_error(400, $error_message);
    }
}

function handle_get_day($day) {
    error_log('rest server in handle_get_day, day = ' . $day);
    echo $day;
}

function handle_post_day() {
    
//    error_log('rest server in handle_post_day');
//    $day = file_get_contents('php://input');  // just a digit string
//    error_log('Server saw POSTed day = ' . $day);  
//    $body_day = json_decode($day, true);
//    return $body_day['day'];
    
     error_log('rest server in handle_post_day');
    $day = file_get_contents('php://input');  // just a digit string
    error_log('Server saw POSTed day = ' . $day);
    return $day;
}

// define this error function for server use (used in product_db.php, etc.)
// The error message is put in the server log
function display_db_error($error_message) {
    include_once('errors/server_error.php');
    
    server_error(500, $error_message);
    
    exit;
}

?>