<?php
/*
	This is a sample game that demos the Playnomics API and what you can do with it.
	In this use-case we're assuming that this a game you are running in the Facebook canvas.
*/
require 'config.php';
require 'fb-php-sdk/facebook.php';

$app_url = 'https://apps.facebook.com/' . $config["fb"]["namespace"] . '/';

//to get an exact birthday we need to ask for that permission
$scope = 'email,user_birthday';

// Init the Facebook SDK
$facebook = new Facebook(array(
	'appId'  => $config["fb"]["appId"],
    'secret' => $config["fb"]["secret"],
));

// Get the current user
$user_id = $facebook->getUser();

// If the user has not installed the app, redirect them to the Login URL
if (!$user_id) {
  	$login_url = $facebook->getLoginUrl(array(
			'scope' => $scope,
			'redirect_uri' => $app_url,
	));
	print('<script> top.location.href=\'' . $login_url . '\'</script>');
	return;
}
//for this basic example we're assuming that this player is always new to game,
//however in real life you should check if the user has logged in previously,
//so that the install time is more accurate
$is_new_user = true;
$user_info = array();

if($is_new_user) {
	//epoch install time
	$install_time = time();
}

$fb_user_profile = $facebook->api("/".$user_id."?fields=gender,third_party_id,birthday");
$user_info["gender"] = $fb_user_profile["gender"];

//this is the anonymized user ID from facebook
$user_info["user_id"] = $fb_user_profile["third_party_id"];

if($fb_user_profile["birthday"] != null){
	$birth_parts = explode("/", $fb_user_profile["birthday"]);
	$user_info["birth_year"] = $birth_parts[2];
}

//there many ways to attribute the source of a new user
if($is_new_user && $_GET["fb_source"]){
	$user_info["source"] = $_GET["fb_source"];

	//checking to see if this person was invited
	if($_GET["request_ids"]){
    	$request_ids =  explode(",", $_GET["request_ids"]);

    	if(count($request_ids) > 0){
    		$last_request_id = $request_ids[count($request_ids) - 1];
    		$user_info["invitation_id"] = $last_request_id;

    		$get_apprequest = "https://graph.facebook.com/". $last_request_id ."?access_token=" . $facebook->getAccessToken();

    		$get_apprequest_result = file_get_contents($get_apprequest);
    		$get_apprequest_result = json_decode($get_apprequest_result, true);

    		$sender_user_id = $get_apprequest_result["from"]["id"];

    		$sender_profile = $facebook->api("/".$sender_user_id."?fields=third_party_id");
    		$user_info["invitation_sender_id"] = $sender_profile["third_party_id"];
    	}

		$fql_query_result = file_get_contents($fql_query_url);
	}
}
?>
<!DOCTYPE html>
<html>
    <head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
		<!-- styles -->
		<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet"/>
		<link href="css/styles.css" rel="stylesheet"/>
		<title>Playnomics Marketplace World!</title>
    </head>
    <body>
		<div class="container" style="width:760px">
		    <!-- messaging frame-->
		    <div id="messageDiv"></div>
		    <!-- Facebook controls -->
		    <div class="navbar">
					<div class="navbar-inner">
					    <a class="brand" id="btnInvitations" href="#">Invite Friends</a>
					    <ul class="nav pull-right">
								<li class="dropdown">
								    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
									Get more stuff from the store!
								    </a>
								    <ul class="dropdown-menu">
<?
foreach($storeBundles as $bundleId => $bundle)
{
?>
									<li>
									    <a href="#" class="btnPurchase" data-bundle="<? echo $bundleId ?>"><? echo $bundle["title"] ?></a>
									</li>
<?
}
?>
								    </ul>
								</li>
					    </ul>
					</div>
		    </div>
		    <div class="row">
					<div class="span12">
						<!-- the game goes here -->
					    <img alt="Game Image" src="img/PlaynomicsMarketplaceWorld.png"/>
					</div>
		    </div>
		</div>
		<!-- jquery -->
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js" type="text/javascript"></script>
		<!-- bootstrap -->
		<script src="bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
		<script type="text/javascript">
	    	//this need to be declared globally
			var _pnConfig={};
	    	var PlaynomicsSample = {
				onGameCurrencyPurchasedWithFBC : function(transId, currencyName, currencyAmount, facebookCredits) {
				    pnTransaction(transId, null, null, "CurrencyConvert", null, [currencyName, "FBC"], [currencyAmount, facebookCredits * -1],  ["v", "r"]);
				},
				onItemPurchasedWithFBC : function(transId, itemId, quantity, facebookCredits) {
				    pnTransaction(transId, itemId, quantity, "BuyItem", null, "FBC", facebookCredits, "r");
				},
				onInvitationSent : function(invitationId, recipientUserId) {
				    pnInvitationSent(invitationId, recipientUserId, null, null);
				},
				onInvitationReceived : function(invitationId, recipientUserId) {
				    pnInvitationResponse(invitationId, recipientUserId, "accepted");
				},
				initApi : function() {
					//this frame is specific to this sample game only
					_pnConfig["b0_barDivId"] ="messageDiv";
					_pnConfig["b0_frameId"] = "QTOXMQGSSATNPJRE";
					_pnConfig["b0_width"] = "760";
					_pnConfig["b0_height"] = "90";

					_pnConfig.userId = "<? echo $user_info["user_id"] ?>";
					_pnConfig.onLoadComplete = PlaynomicsSample.onLoadComplete;

					var _pnAPIURL=document.location.protocol+"//js.a.playnomics.net/v1/api?a=<?echo $config["playnomics"]["appId"]?>",
					_pnAPI=document.createElement("script");
					_pnAPI.type="text/javascript";
					_pnAPI.async=true;_pnAPI.src=_pnAPIURL;document.body.appendChild(_pnAPI);
				},
				onLoadComplete : function() {
<?
/*
 * In the most common usecase games care about reporting userInfo when the player is brand new to the game,
 * to track how they got to you game and to describe who they are. There may have other scenarios where you want
 * to provide this information for other games.
 */
if($is_new_user) {
?>
				    var gender = "<? echo $user_info["gender"] ?>";
				    var birthYear = parseInt("<? echo $user_info["birth_year"] ?>");
    <?
    if($user_info["source"]) {
    ?>
				    var source = "<? echo $user_info["source"] ?>";
    <?
    } else {
    ?>
				    var source = null;
    <?
    } if($user_info["invitation_id"]) {
    ?>
				    var invitationId = <? echo $user_info["invitation_id"]?>;
				    var recipientUserId = "<? echo $user_info["user_id"] ?>";
				    var sourceUser = "<? echo $user_info["invitation_sender_id"] ?>";
				    //attribute the response to the last invitation only
				    PlaynomicsSample.onInvitationReceived(invitationId, recipientUserId);
    <?
    } else {
    ?>
				    var sourceUser = null;
    <?
    }
    ?>
				    var sourceCampaign = null;
				    var installTime = <?echo $install_time?>;
					pnUserInfo("update", null, null, gender, birthYear, source, sourceCampaign, installTime, sourceUser);
<?
}
?>
				}, 
				onAdClicked : function(){
					alert("Wow! You clicked this in-game ad!");
				}
	    	};
		</script>
		<!-- Start FB JS API -->
		<div id="fb-root"></div>
		<script type="text/javascript">
		    window.fbAsyncInit = function() {
					// init the FB JS SDK
				FB.init({
					appId : '<? echo $config["fb"]["appId"] ?>', // App ID from the app dashboard
					frictionlessRequests: true
				});

				FB.getLoginStatus(function(response) {
				    if(response.status == "connected"){
							//this is a redundant check, but we're doing this
							//because we know that the Facebook JavaScript API has been loaded,
							//before we actually make this call
							FB.Canvas.setAutoGrow();
							PlaynomicsSample.initApi();
				    }
				});
		    };
		    // Load the SDK asynchronously
		    (function(d, s, id) {
					var js, fjs = d.getElementsByTagName(s)[0];
					if (d.getElementById(id)){return;}
					js = d.createElement(s); js.id = id;
					js.src = "//connect.facebook.net/en_US/all.js";
					fjs.parentNode.insertBefore(js, fjs);
		    }(document, 'script', 'facebook-jssdk'));

	  		FbGame = {
				purchase : function(bundleId) {
				    //for the purpose of this sample code, we provide a client side
				    //copy of our store object so that we can get some important meta-data about
				    //the bundled items we are selling. This is purely for reporting purposes.

				    var storeBundles = <? echo json_encode($storeBundles);?>;

				    var itemOrder = {
							method : 'pay',
							action : 'buy_item',
							// You can pass any string, but your payments_get_items must
							// be able to process and respond to this data.
							order_info : { 'item_id' : bundleId },
							dev_purchase_params : { 'oscif' : true }
					};

				    FB.ui(itemOrder, function(data){
						if(data['order_id'] && data['status'] && data['status'] == "settled"){
					    	//the transaction has been completed
					    	if(storeBundles[bundleId]){
								var transId = data['order_id'];
								var storeBundle = storeBundles[bundleId];

								var quantity = storeBundle.data.quantity;
								var type = storeBundle.data.type;
								var totalPriceFbc = storeBundle.price;

								var singularName = storeBundle.data.singular_name;

								if(type === "currency"){
								    //in the currency context, itemsIncluded is just the currency name.
								    //our current example, only handles the purchase of 1 in-game currency per bundle
								    PlaynomicsSample.onGameCurrencyPurchasedWithFBC(transId, singularName, quantity, totalPriceFbc);
								} else if(type === "items") {
								    //In this context, itemsIncluded helps us to undestand what combination of items is offered
								    //in the bundle. For items, we calculate the per-unit price, and include the quantity.

								    var pricePerUnit = totalPriceFbc / quantity;

								    PlaynomicsSample.onItemPurchasedWithFBC(transId, singularName, quantity, pricePerUnit);

								    //However, you can also report the quantity as 1, and just use the totalPriceFbc, like so:
								    //PlaynomicsSample.onItemPurchasedWithFBC(transId, bundleId, 1, totalPriceFbc);
								}
					    	}
						}
				    });
				},
				inviteDialog : function() {
			    	FB.ui(
						{
						   	method: 'apprequests',
					    	//only invite people who have not already joined the game
					    	filter: 'app_non_users',
					    	message: 'Invite your friends to join the Playnomics Marketplace!'
						},
						function(response){
					    	if(response && response.request && response.to){
								var requestId = response.request;

								$.each(response.to, function(index, userId){
					    			//get the repicients third party id so that we
					    			//report the anonymized third party invite information
					    			FB.api('/'+userId+"?fields=third_party_id", function(response){
										var recipientId = response.third_party_id;
										PlaynomicsSample.onInvitationSent(requestId, recipientId);
					    			});
								});
					    	}
						}
					);
				}
			};
		</script>
		<script type="text/javascript">
		  	$(document).ready(function() {
				$('.btnPurchase').on('click', function(){
			    	var bundleId = $(this).data('bundle');
			    	FbGame.purchase(bundleId);
				});

				$('#btnInvitations').on('click', function(){
			    	FbGame.inviteDialog();
				});
		  	});
		</script>
		<!-- End FB JS API -->
 	</body>
</html>