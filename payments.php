<?php
/*
 * This sample code is based on the PHP Payments Callback available from Facebook:
 * https://developers.facebook.com/docs/payments/callback/
 */
require "config.php";
require "fb-php-sdk/facebook.php";

header('Content-Type: application/json; charset=UTF-8');

$facebook = new Facebook(array(
    'appId' => $config["fb"]["appId"],
    'secret' => $config["fb"]["secret"]
));

//init the response value
$response = "";

//Validate request is from Facebook and parse contents for use.
$request = $facebook->getSignedRequest();

if ($request) {
    // Get request type
    $request_type = $_POST["method"];
    if ($request_type == "payments_get_items") {
        $response = getItemsResponse($request, $request_type);
    } else if ($request_type == "payments_status_update") {
        $response = getStatusUpdateResponse($request, $request_type);
    }
}
echo json_encode($response);
return;

function getItemsResponse($request, $request_type)
{
    global $storeBundles, $config;

    $response = "";

    //we're displaying items in FB dialog for the player to confirm purchase
    $order_info = json_decode($request['credits']['order_info'], true);
    // Get item id.
    $item_id    = $order_info['item_id'];
    //look to see if we have this bundle in our "store"
    $item       = isset($storeBundles[$item_id]) ? $storeBundles[$item_id] : null;

    if ($item) {
        $response = array(
            'content' => array(
                0 => array(
                    'title' => $item['title'],
                    'description' => $item['description'],
                    'image_url' => $item['image_url'],
                    'product_url' => $item['product_url'],
                    'price' => $item['price']
                )
            ),
            'method' => $request_type
        );
    } else {
        error_Log("Could not find item " . $item_id . " in store.");
    }
    return $response;
}

function getStatusUpdateResponse($request, $request_type)
{
    global $storeBundles, $config;
    $response = "";

    //FB is sending us a status indicating the status of player's order

    //Get order details.
    $order_details = json_decode($request['credits']['order_details'], true);

    // Determine if this is an earned currency order.
    $item_data             = json_decode($order_details['items'][0]['data'], true);
    $earned_currency_order = (isset($item_data['modified'])) ? $item_data['modified'] : null;

    // Get order status.
    $current_order_status = $order_details['status'];

    if ($current_order_status == 'placed') {
        // Fulfill order based on $order_details unless...

        if ($earned_currency_order) {
            // Fulfill order based on the information below...
            // URL to the application's currency webpage.
            $product        = $earned_currency_order['product'];
            // Title of the application currency webpage.
            $product_title  = $earned_currency_order['product_title'];
            // Amount of application currency to deposit.
            $product_amount = $earned_currency_order['product_amount'];
            // If the order is settled, the developer will receive this
            // amount of credits as payment.
            $credits_amount = $earned_currency_order['credits_amount'];
        }

        $next_order_status = 'settled';

        // Construct response.
        $response = array(
            'content' => array(
                'status' => $next_order_status,
                'order_id' => $order_details['order_id']
            ),
            'method' => $request_type
        );
    } else if ($current_order_status == 'disputed') {
        // 1. Track disputed item orders.
        // 2. Investigate user's dispute and resolve by settling or refunding the order.
        // 3. Update the order status asychronously using Graph API.
    } else if ($current_order_status == 'refunded') {
        // Track refunded item orders initiated by Facebook. No need to respond.

    } else if ($current_order_status == 'settled') {

        // Verify that the order ID corresponds to a purchase you've fulfilled, thenÉ
        // Get order details.
        $order_details = json_decode($request['credits']['order_details'], true);

        // Construct response.
        $response = array(
            'content' => array(
                'status' => 'settled',
                'order_id' => $order_details['order_id']
            ),
            'method' => $request_type
        );
    } else {
        // Track other order statuses.
    }
    return $response;
}
?>