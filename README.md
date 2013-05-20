Playnomics PlayRM JavaScript SDK Integration Guide
==================================================
If you're new to PlayRM or don't yet have an account with <a href="http://www.playnomics.com">Playnomics</a>, please take a moment to <a href="http://integration.playnomics.com/technical/#integration-getting-started">get acquainted with PlayRM</a>.

A sample Facebook app is provided to illustrate how you can integrate PlayRM with Facebook's PHP and JavaScript SDKs. You can view the <a href="https://apps.facebook.com/playnomicstest/" target="_blank">live example</a>. However, the PlayRM SDK is not designed to work exclusively with Facebook.

Outline
=======
* [Prerequisites](#prerequisites)
    * [Signing Up for the PlayRM Service](#signing-up-for-the-playrm-service)
    * [Register Your Game](#register-your-game)
* [Basic Integration](#basic-integration)
    * [Installing the SDK](#installing-the-sdk)
    * [Demographics and Install Attribution](#demographics-and-install-attribution)
    * [Monetization](#monetization)
        * [Purchases of In-Game Currency with Real Currency](#purchases-of-in-game-currency-with-real-currency)
        * [Purchases of Items with Real Currency](#purchases-of-items-with-real-currency)
        * [Purchases of Items with Premium Currency](#purchases-of-items-with-premium-currency)
    * [Invitations and Virality](#invitations-and-virality)
    * [Custom Event Tracking](#custom-event-tracking)
* [Messaging Integration](#messaging-integration)
    * [Setting up a Frame](#setting-up-a-frame)
    * [SDK Integration](#sdk-integration)
    * [Enabling Click-to-JS](#enabling-click-to-js)
* [Sample Facebook App](#sample-facebook-app)
    * [Getting Started](#getting-started)
    * [Dependencies](#dependencies)
    * [Disclaimers](#disclaimers)
    * [Assumptions](#assumptions)
* [Support Issues](#support-issues)

Basic Integration
=================
The following snippet of code asynchronously loads the SDK into your game canvas; it needs to be configured with your `<APPID>` from the dashboard and it needs to provide a `<USER-ID>`. The `<USER-ID>` helps PlayRM to consistently identify each player over their lifetime in a game.

Once loaded, the SDK will automatically begin collecting basic user information (including geography) and engagement data.

```javascript
<!-- Start Playnomics API -->
<script type="text/javascript">
_pnConfig={};
_pnConfig.userId="<USER-ID>";

_pnConfig.onLoadComplete = function() {
    //optionally provide a callback function that is fired when the SDK has been loaded
};

var _pnAPIURL=document.location.protocol+"//js.a.playnomics.net/v1/api?a=<APPID>",
_pnAPI=document.createElement("script");
_pnAPI.type="text/javascript";_pnAPI.async=true;_pnAPI.src=_pnAPIURL;document.body.appendChild(_pnAPI);
</script>
<!-- End Playnomics API -->
```

The `<USER-ID>` should be a persistent, anonymized, and unique to each player. This is typically discerned dynamically when a player starts the game. Some potential implementations:

* If your game is a Facebook canvas game, select the player's Third Party ID. The sample application demonstrates how you can do this:

```php
<?php

//...
//...

$facebook = new Facebook(array(
    'appId'  => $config["fb"]["appId"],
    'secret' => $config["fb"]["secret"],
));

// Get the current user
$user_id = $facebook->getUser();

//...
//...

$fb_user_profile = $facebook->api("/".$user_id."?fields=gender,third_party_id,birthday");
$user_info["user_id"] = $fb_user_profile["third_party_id"];
```

```javascript 
<!-- Start Playnomics API -->
<script type="text/javascript">
_pnConfig={};
_pnConfig.userId="<?echo $user_info["user_id"]?>";
```

* An internal ID (such as a database auto-generated number).
* A hash of the player’s email address.

**You cannot use the player's Facebook ID or any personally identifiable information (plain-text email, name, etc) for the `<USER-ID>`.**

`_pnConfig.onLoadComplete` allows you to optionally pass a callback that will be fired when the SDK has finished initialization. This a common place to call the [user info module](#demographics-and-install-attribution).

## Demographics and Install Attribution

After the SDK is loaded, the user info module may be called to collect basic demographic and acquisition information. This data is used to segment users based on how/where they were acquired and enables improved targeting with basic demographics, in addition to the behavioral data collected using other events.

Provide each player's information using this call:

```javascript
pnUserInfo("update", null, null, sex, birthYear, source, sourceCampaign, installTime, sourceUser);
```
If any of the parameters are not available, you should pass `null`.
<table>
    <tr>
        <td><code>sex</code></td>
        <td>M or F</td>
    </tr>
    <tr>
        <td><code>birthYear</code></td>
        <td>4-digit year, such as 1980</td>
    </tr>
    <tr>
        <td><code>source</code></td>
        <td>
            Source of the player, such as "FacebookAds", "UserReferral", "Playnomics", etc. These are only suggestions, any 16-character or shorter string is acceptable
        </td>
    </tr>
    <tr>
        <td><code>sourceCampaign</code></td>
        <td>Any 16-character or shorter string to help identify specific campaigns.</td>
    </tr>
    <tr>
        <td><code>sourceUser</code></td>
        <td>
            If the player was acquired via a UserReferral (i.e., a viral message), the userId of the person who initially brought this user into the game.
        </td>
    </tr>
    <tr>
        <td><code>installTime</code></td>
        <td>
            Unix epoch time in seconds when the player originally installed the game.
        </td>
    </tr>
</table>

You can extract a lot of this information from the Facebook PHP SDK, particularly the <a href="https://developers.facebook.com/docs/reference/api/" target="_blank">Graph API</a>, provided that your canvas application has the right permissions. The Sample App covers this in detail.

## Monetization

PlayRM provides a flexible interface for tracking monetization events. This module should be called every time a player triggers a monetization event. 

This event tracks players that have monetized and the amount they have spent in total, *real* currency:
* FBC (Facebook Credits)
* USD (US Dollars)
* OFD (offer valued in USD)

or an in-game, *virtual* currency.

```javascript
pnTransaction(transactionId, itemId, quantity, type, otherUserId, currencyTypes, 
    currencyValues, currencyCategories);
```
<table>
    <tr>
        <td><code>transactionId</code></td>
        <td>A unique identifier for this transaction. If this is Facebook, you can use the order ID provided to you by Facebook. You can also use an internal ID. If nothing else is available, you can genenate large random number.</td>
    </tr>
    <tr>
        <td><code>itemId</code></td>
        <td>If applicable, an identifier for the item. The identifier should be consistent.</td>
    </tr>
    <tr>
        <td><code>quantity</code></td>
        <td>If applicable, the number of items being purchased.</td>
    </tr>
    <tr>
        <td><code>type</code></td>
        <td>
            The type of transaction occurring:
            <ul>
                <li>BuyItem: A purchase of virtual item. The <code>quantity</code> is added to the player's inventory</li>
                <li>
                    SellItem: A sale of a virtual item to another player. The item is removed from the player's inventory. Note: a sale of an item will result in two events with the same <code>transactionId</code>, one for the sale with type SellItem, and one for the receipt of that sale, with type BuyItem
                </li>
                <li>
                    ReturnItem: A return of a virtual item to the store. The item is removed from the player's inventory
                </li>
                <li>BuyService: A purchase of a service, e.g., VIP membership </li>
                <li>SellService: The sale of a service to another player</li>
                <li>ReturnService: The return of a service</li>
                <li>
                    CurrencyConvert: An conversion of currency from one form to another, usually in the form of real currency (e.g., US dollars) to virtual currency.  If the type of a transaction is CurrencyConvert, then there should be at least 2 elements in the <code>currencyTypes</code>, <code>currencyValues</code>, and <code>currencyCategoriess</code> arrays.
                </li>
                <li>Initial: An initial allocation of currency and/or virtual items to a new player</li>
                <li>Free: Free currency or item given to a player by the application</li>
                <li>
                    Reward: Currency or virtual item given by the application as a reward for some action by the player
                </li>
                <li>
                   GiftSend: A virtual item sent from one player to another.

                   Note: a virtual gift should result in two transaction events with the same <code>transactionId</code>, one with the type GiftSend, and another with the type GiftReceive
                </li>
                <li>GiftReceive: A virtual good received by a player. See note for GiftSend type</li>
            </ul>
        </td>
    </tr>
    <tr>
        <td><code>otherUserId</code></td>
        <td>
            If applicable, the other player involved in the transaction. A contextual example is a player sending a gift to another player.
        </td>
    </tr>
    <tr>
        <td><code>currencyTypes</code></td>
        <td>
            A string or array of strings, indicating the type of currency being used in the transaction.
            <ul>
                <li>
                    <em>Real</em> currencies:
                    <ul>
                        <li>FBC (Facebook Credits)</li>
                        <li>USD (US Dollars)</li>
                    </ul>
                </li>
                <li>
                    A <em>virtual</em> game currency, limited to 16 characters.
                </li>
            </ul>
        </td>
    </tr>
    <tr>
        <td><code>currencyValues</code></td>
        <td>
            A single numeric value or an array values, indicating the value being spent.
        </td>
    </tr>
    <tr>
        <td><code>currencyCategories</code></td>
        <td>
            An string or an of strings, indicating whether the currency is <em>virtual</em> "v" or <em>real</em> "r".
        </td>
    </tr>
</table>

We highlight three common use-cases below. The Sample App covers the first two.
* [Purchases of In-Game Currency with Real Currency](#purchases-of-in-game-currency-with-real-currency)
* [Purchases of Items with Real Currency](#purchases-of-items-with-real-currency)
* [Purchases of Items with In-Game Currency](#purchases-of-items-with-in-game-currency)

### Purchases of In-Game Currency with Real Currency

A very common monetization strategy is to incentivize players to purchase premium, in-game currency with real currency. PlayRM treats this like a currency exchange. This is one of the few cases where currency metadata: `currencyTypes`, `currencyValues`, `currencyCategories` are expressed in an array form. `itemId`, `quantity`, and `otherUserId` are left `null`.

An example of this working in concert with Facebook's Payments Dialog is provided in the Sample App, however, here is a more simplified example:

```javascript
//player purchases 500 MonsterBucks for 10 USD

var quantityBucks = 500;
var gameCurrency = "MonsterBucks";

var priceInUSD = 10;
var transType = "CurrencyConvert";

//it's important that cooresponding currency metadata is consistently positioned in each array
pnTransaction(transactionId, null, null, transType, null, [gameCurrency, "USD"], [quantityBucks, priceInUSD * -1],  ["v", "r"]);
```

### Purchases of Items with Real Currency

An example of this working in concert with Facebook's Payments Dialog is provided in the Sample App, however, here is a more simplified example:

```javascript
//player purchases a "Monster Trap" for $.99 USD
var trapItemId = "Monster Trap"
var quantity = 1;
var price = .99;
var transType = "BuyItem";

pnTransaction(tranId, trapItemId, quantity, transType, null, "USD", price, "r");
```

### Purchases of Items with Premium Currency

This event is used to segment monetized players (and potential future monetizers) by collecting information about how and when they spend their premium currency (an in-game currency that is primarily acquired using a *real* currency). This is one level of information deeper than the previous use-cases.

#### Currency Exchanges

This is a continuation on the first currency exchange example. It showcases how to track each purchase of in-game *attention* currency (non-premium virtual currency) paid for with a *premium*:

```javascript

//In this hypothetical, Mana is an attention currency that is earned over the lifetime of the game. 
//They can also be purchased with the premium MonsterBucks that the player may have purchased earlier.

//player buys 100 Mana with 10 MonsterBucks
var attentionCurrency = "Mana";
var attentionAmount = 100;

var premimumCurrency = "MonsterBucks";
var premiumCost = -10;

var transType = "CurrencyConvert";

//notice that both currencies are virtual
pnTransaction(transactionId, null, null, transType, null, [premimumCurrency, attentionCurrency], [premiumCost, attentionAmount],["v","v"]);
```
#### Item Purchases

This is a continuation on the first item purchase example, except with premium currency.

```javascript
//player buys 20 light armor, for 5 MonsterBucks

var itemQuantity = 20;
var item = "Light Armor";

var transType = "BuyItem";

var premimumCurrency = "MonsterBucks";
var premiumCost = 5;

pnTransaction(transactionId, item, itemQuantity, transType, null, premimumCurrency, premiumCost, "v");
```

## Invitations and Virality

The virality module allows you to track a single invitation from one player to another (e.g., inviting friends to join a game on Facebook).

If multiple requests can be sent at the same time, such as through the Facebook Friend selector, a separate function call should be made for each recipient. The Sample App details how to work with the Facebook Requests dialog.

```javascript
pnInvitationSent(invitationId, recipientUserId, recipientAddress, method);
```
<table>
    <tr>
        <td><code>invitationId</code></td>
        <td>
            A unique 64-bit integer identifier for this invitation. 

            If this is a Facebook application, you can use the appRequestId.

            If no identifier is available, this could be a hash/MD5/SHA1 of the sender's and neighbor's IDs concatenated. <strong>The resulting identifier can not be personally identifiable.</strong>
        </td>
    </tr>
    <tr>
        <td><code>recipientUserId</code></td>
        <td>This can be a hash/MD5/SHA1 of the recipient's Facebook ID, their Facebook 3rd Party ID or an internal ID. It cannot be a personally identifiable ID.</td>
    </tr>
    <tr>
        <td><code>recipientAddress</code></td>
        <td>
            An optional way to identify the recipient, for example the <strong>hashed email address</strong>. When using <code>recipientUserId</code> this can be <code>null</code>.
        </td>
    </tr>
    <tr>
        <td><code>method</code></td>
        <td>
            The method of the invitation request will include one of the following:
            <ul>
                <li>facebookRequest</li>
                <li>email</li>
                <li>twitter</li>
            </ul>
        </td>
    </tr>
</table>

You can then track each invitation response. **IMPORTANT:** you will need to pass the invitationId through the invitation link. Facebook exposes the appRequestIDs through their query string and we illustrate how you can consume them in the Sample App.

```javascript
pnInvitationResponse(invitationId, recipientUserId, response);
```
<table>
    <tr>
        <td><code>invitationId</code></td>
        <td>the ID of the corresponding invitation sent event.</td>
    </tr>
    <tr>
        <td><code>recipientUserId</code></td>
        <td>the recipient ID used in the corresponding invitation sent event</td>
    </tr>
    <tr>
        <td><code>response</code></td>
        <td>Currently this only supports "accepted"</td>
    </tr>
</table>

Example calls for a player’s invitation and the recipient’s acceptance:

```javascript
var invitationId = 112345675;
var recipientUserId = 10000013;

pnInvitationSent(invitationId, recipientUserId, null, null); 

//later on the recipient accepts the invitation

pnInvitationResponse(invitationId, recipientUserId, "accepted"); 
```

## Custom Event Tracking

Milestones may be defined in a number of ways.  They may be defined at certain key gameplay points like, finishing a tutorial, or may they refer to other important milestones in a player's lifecycle. PlayRM, by default, supports up to five custom milestones.  Players can be segmented based on when and how many times they have achieved a particular milestone.

Each time a player reaches a milestone, track the milestone using the JavaScript call:

```javascript
pnMilestone(milestoneId, milestoneName);
```

These parameters should be replaced:
<table>
    <tr>
        <td><code>milestoneId</code></td>
        <td>A unique 64-bit numeric identifier for this milestone occurrence</td>
    </tr>
    <tr>
        <td><code>milestoneName</code></td>
        <td>
            The name of the milestone which should be "TUTORIAL" or "CUSTOMn", where n is 1 through 5.
            The name is case-sensitive.
        </td>
    </tr>
</table>

Example client-side calls for a player’s reaching a milestone, with generated IDs:

```javascript
function generateLargeId(){
  //generates a 64-bit integer
  return Math.floor(Math.random() * (Math.pow(2,63)- 1));  
}
 
//when the player completes the tutorial
var milestoneTutorialId = generateLargeId(); 
pnMilestone(milestoneTutorialId, "TUTORIAL");
 
//when milestone CUSTOM2 is reached
var milestoneCustom2Id = generateLargeId(); 
pnMilestone(milestoneCustom2Id, "CUSTOM2"); 
```
Messaging Integration
=====================

This guide assumes you're already familiar with the concept of frames and messaging, and that you have all of the relevant `frames` setup for your application.

If you are new to PlayRM's messaging feature, please refer to <a href="http://integration.playnomics.com" target="_blank">integration documentation</a>.

Once you have all of your frames created with their associated `<PLAYRM-FRAME-ID>`s, you can start the integration process.

## SDK Integration

To tell PlayRM where to place the frame, you first create an empty `div` element in the appropriate location (the id of the `div` is arbitrary):

```html
<div id="messageDiv"></div>
```

Then modify the PlayRM SDK config `_pnConfig` to let PlayRM know about your frame:

```javascript
//this frame is specific to this sample game only
_pnConfig["b0_barDivId"] ="messageDiv";
_pnConfig["b0_frameId"] = "<PLAYRM-FRAME-ID>";
//the height and width of your frame are configuarable and based
//on what you set up in the control panel
_pnConfig["b0_width"] = "760";
_pnConfig["b0_height"] = "90";

_pnConfig.enableAdJS=true;

_pnConfig.userId = "<USER-ID>";
_pnConfig.onLoadComplete = PlaynomicsSample.onLoadComplete;

var _pnAPIURL=document.location.protocol+"//js.a.playnomics.net/v1/api?a=<APPID>",
_pnAPI=document.createElement("script");
_pnAPI.type="text/javascript";
_pnAPI.async=true;_pnAPI.src=_pnAPIURL;document.body.appendChild(_pnAPI);
```

After the SDK has initialized, PlayRM will automatically find the `<div>` tag and replace it with an `iframe` of the appropriate width and height.

### Enabling Code Callbacks
Code Callbacks is a feature that allows you to target JavaScript code in your game canvas from a message. You can think of like a dynamic click callback, because the JavaScript to be executed when the player clicks is entirely via the Playnomics control panel. You must, however, explicitally enable this feature in your integration.

To enable any JavaScript function, add the setting:

```javascript
_pnConfig.enableAdJS=true;
```

To enable a specific set of JavaScript functions, add the following setting for EACH function that may be triggered:

```javascript
_pnConfig.adJS_<NAME>="<JS-FUNCTION>";
```
Replace `<NAME>` with any name. This name will be provided in the Playnomics control panel when creating a messaging creative.

Replace `<JS-FUNCTION>` with the JavaScript function to be triggered when the player clicks the message.

Sample Facebook App
===================
## Getting Started
To use this sample app, create a config.php file based on the config-sample.php and create Facebook Canvas App. You can retrieve these settings from your Facebook app management screen. 

Your PlayRM AppId is available from the <a href="https://controlpanel.playnomics.com" target="_blank">control panel</a>.

```php
$config = array(
    "fb" => array(
        "appId"     => "APP_ID",
        "secret"    => "APP_SECRET",
        "namespace" => "APP_NAMESPACE"
    ),
    "playnomics"  => array(
        "appId" => "APP_ID"
    ),
);
```
## Dependencies

This sample app is written in PHP and JavaScript. It utilizes Facebook's API for PHP and JavaScript, in tandem.

In Q3 of 2013, Facebook will be deprecating Facebook credits and will be rolling out a transaction system that is based in <a href="https://developers.facebook.com/docs/payments/" target="_blank">localized, real currency</a>.

While jQuery and Twitter Bootstrap are both used, they aren't necessary for working with the PlayRM SDK. For jQuery we're using 1.9.1, but <a href="http://www.jquery.com/browser-support/" target="_blank">jQuery 2.x only supports IE 9 and above</a>.

## Disclaimers

This sample code is a very simple use-case; in reality, your game integration might work a lot differently. Again, the goal of this sample app is to convey how you can integrate some of the major features of Facebook's SDKs with the PlayRM JavaScript SDK.

## Assumptions

* We treat every player like a new player. In reality, **your game server should keep track of each player that joins your game and report attribution appropriately.**
* The game store just has two items and everything is hard-coded. Yuck. We also share the store information with the client (JSON object dump) so that we can better understand the transaction taking place on the client side. Your implementation will likely be more data-driven, and should be more selective about what information is available to the web browser.

Support Issues
==============
If you have any questions or issues, please contact <a href="mailto:support@playnomics.com">support@playnomics.com</a>.
