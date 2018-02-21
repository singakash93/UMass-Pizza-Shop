
<?php
require('../../util/main.php');
require('../../model/database.php');
require('../../model/day_db.php');
require('../../model/initial.php');
require('../../model/inventory_db.php');
require('web_services.php');
require ('../../vendor/autoload.php');


// Note that you don't have to put all your code in this file.
// You can use another file day_helpers.php to hold helper functions
// and call them from here.

$spot = strpos($app_path, 'pizza2');
$part = substr($app_path, 0, $spot);
$base_url = $_SERVER['SERVER_NAME'] . $part . 'proj2_server/rest';

$httpClient = new \GuzzleHttp\Client();
//$url = 'http://' . $base_url . '/day/';


$action = filter_input(INPUT_POST, 'action');
if ($action == NULL) {
    $action = filter_input(INPUT_GET, 'action');
    if ($action == NULL) {
        $action = 'list';
    }
}
 $current_day = get_current_day($db);
if ($action == 'list') {
    try {
        $todays_orders = get_todays_orders($db, $current_day);
         $inventory_details = get_inventory_lists($db);
         $provision= get_undelivr($db);
    } catch (Exception $e) {
        include('../errors/error.php');
        exit();
    }
    include('day_list.php');
} else if ($action == 'next_day') {
    try {
        finish_orders_for_day($db, $current_day);
        increment_day($db);
        $inventory_details = get_inventory_lists($db);
        $nextday = get_current_day($db);
        
        try{
            $response = post_day($httpClient, $base_url, $nextday);
            //(client OBJ,server URL,data)//
            $status = $response->getStatusCode();
        } catch (GuzzleHttp\Exception $e) {
            $status = 'POST failed, error = ' . $e;
            error_log($status);
            include '../errors/error.php';  // Note new error.php code that handles Exceptions
        }
        $previous_supplies = get_undelivr($db);
        foreach ($previous_supplies as $orderIDs) {
            try {
                $order = get_order($httpClient, $base_url, $orderIDs['order_id']);
              if ($order[0]['delivered'])
                { 
                  $flr_qy = $orderIDs['flour_qty'];
                  $chs_qy = $orderIDs['cheese_qty'];
                  $id = $orderIDs['order_id'];
                  refill_flour($db, $flr_qy); 
                  refill_cheese($db, $chs_qy); 
                  erase_undelivr($db,$id); 
                } 
            }catch (Exception $e) 
            {
                $error = $e->getMessage();
                include('../../errors/error.php');
                exit();
            }
            }
        $presentinventory = get_inventory_lists($db);
        $provision = get_undelivr($db);
        $quantity_flour = $presentinventory[0]['quantity'];
        $quantity_cheese = $presentinventory[1]['quantity'];
        
        foreach ($provision as $certain_value)
        {
             $quantity_flour  =  $quantity_flour + $certain_value['flour_qty'];
            
            $quantity_cheese = $quantity_cheese + $certain_value['cheese_qty'];
        }
        $flr_qy = 0;
        $chs_qy = 0;
        error_log($quantity_flour . 'floor qty');
        error_log($quantity_cheese . 'cheese _ qty');
        
        if($quantity_flour  < 150)
        {
            if((150-$quantity_flour ) % 40 == 0)
            {
                $flr_qy = 150 - $quantity_flour ;
            }
            else
            {
                $flr_qy = (floor((150 - $quantity_flour ) / 40) + 1) * 40;
            }
        }
        if($quantity_cheese < 150)
        {
            if((150 - $quantity_cheese) % 20 == 0){
                $chs_qy = 150 - $quantity_cheese;
            }
            else{
                $chs_qy = (floor((150 - $quantity_cheese) / 20) + 1) * 20;
            }
        }
        if($flr_qy > 0 || $chs_qy > 0) 
        {
           
            $item1['productID'] = 11;
            $item1['quantity'] = $flr_qy;
            $item2['productID'] = 12;
            $item2['quantity'] = $chs_qy;
            $order['customerID'] = rand(1, 3);
            $order['items'][0] = $item1;
            $order['items'][1] = $item2;
     
        try{
            $orderid_path = post_order($httpClient, $base_url, $order);
            $path_string = $orderid_path[0];
            $orderid_part = explode('/', $path_string);
            $orderid1 = end($orderid_part);
            add_undelivr_ord($db, $orderid1, $flr_qy, $chs_qy);
            
        } catch (GuzzleHttp\Exception $e) {
            $status = 'POST failed, error = ' . $e;
            error_log($status);
            include '../errors/error.php';  // Note new error.php code that handles Exceptions
        
       
    
        }}
    
 }  catch (Exception $e) {
        include ('../errors/error.php');
        exit();
    } 
 header("Location: .");
        }
else if ($action == 'initial_db') {
    try {
        initial_db($db);
        $nextday1 = 0;
        try{
            $response = post_day($httpClient, $base_url, $nextday1);
            $status = $response->getStatusCode();
        } catch (GuzzleHttp\Exception $e) {
            $status = 'POST failed, error = ' . $e;
            error_log($status);
            include '../errors/error.php';  // Note new error.php code that handles Exceptions
        }
        header("Location: .");
        
    } catch (Exception $e) {
        include ('../../errors/error.php');
        exit();
    } 
}
?>
