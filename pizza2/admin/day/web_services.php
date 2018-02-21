<?php
// Functions to do the base web services needed
// Note that all needed web services are sent from this day directory
// The functions here should throw up to their callers, just like
// the functions in model.
//
// Post day number to server
// Returns if successful, or throws if not



function post_day($httpClient, $base_url, $day) {
    error_log('post_day to server: ' . $day);
    $url = $base_url . '/day/';
    $response = $httpClient->request('POST', $url,['json' => $day]); //guzzale part
//                 $request = $httpClient->createRequest('POST', $url);
//            $request->setBody(Stream::factory('9'));
//         $response =  $request->send();
    return $response;
   //echo $response; die();
 error_log('post_day to server: ' . $day);
 
//  $url =  'http://' . $base_url . '/day/';
//    $day1['day'] = $day;
//    //$response = $httpClient->request('POST', $url, ['json' => $day1]);
//    return $response;
    
}

function post_order($httpClient, $base_url, $new_order){
    error_log('post_order to server with order_id ');
    $url =  'http://' . $base_url . '/orders/';
    $response2 = $httpClient->request('POST', $url, ['json' => $new_order]);
    $location2 = $response2->getHeader('Location');
    //$status2 = $response2->getStatusCode();
    //print_r($location2); die;
    return $location2;
}

function get_order($httpClient, $base_url, $oid){
    error_log('get_order to server: ' . $oid);
    $url = 'http://' . $base_url . '/orders/'.$oid;
    
    $response = $httpClient->get($url);
    $order_Json = $response->getBody()->getContents();  // as StreamInterface, then string
    $order = json_decode($order_Json, true);
    return $order;
}

// TODO: POST order and get back location (i.e., get new id), get all orders 
// in server and/or get a specific order by orderid