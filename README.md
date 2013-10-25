Playnomics PlayRM JavaScript SDK Integration Guide
==================================================
If you're new to PlayRM and/or don't have a PlayRM account and would like to get started using PlayRM please visit   <a href="https://controlpanel.playnomics.com/signup">https://controlpanel.playnomics.com/signup</a> to sign up. Soon after creating an account you will receive a registration confirmation email permitting you access to your PlayRM control panel.

Within the control panel, click the <strong>applications</strong> tab and add your app. Upon doing so, you will receive an <strong>Application ID</strong> and an <strong>API KEY</strong>. These two components will enable you to begin the integration process.

Our integration has been optimized to be as straight forward and user friendly as possible. If you're feeling unsure or would like better understand the order the process before beginning integration, please take a moment to check out the <a href="http://integration.playnomics.com/technical/#integration-getting-started">getting started</a> page. Here you can find an overview of our integration process, and platform specific features, to help you better understand the PlayRM integration process.

To help contextualize our integration, you can refer to our <strong>sample Facebook app</strong> located in this document. The easiest way to get there is to use the "Sample Facebook App" link in the left navigation index. This example illustrates how you can integrate PlayRM with Facebook's PHP and JavaScript SDKs. You can view the <a href="https://apps.facebook.com/playnomicstest/" target="_blank">live example</a>. Keep in mind, the PlayRM Javascript SDK is not Facebook exclusive and will work in any Javascript application context.


## Considerations for Cross-Platform Applications

If you want to deploy your app to multiple platforms (eg: JavaScript for Facebook and the Unity Web player), you'll need to create separate applications in the control panel. Each application must incorporate a separate `<APPID>` particular to that application. In addition, placements and their respective creative uploads will be particular to that app in order to ensure that they are sized appropriately - proportionate to your app screen size.


# Basic Integration
Integration is as simple as adding the code snippet below into your application. Upon initial integration, the SDK will be running in **test mode**. Be sure to switch to [production mode](#switch-sdk-to-production-mode) before deploying your app.

 This code needs to incorporate your `<APPID>`, found on your control panel, and it must provide a `<USER-ID>`. The `<USER-ID>` helps PlayRM consistently identify each user over his or her lifetime in an application.

Once loaded, the SDK will automatically begin collecting basic user information (including geography) and engagement data, and send it to the PlayRM test servers.

```javascript
<!-- Start Playnomics API -->
<script type="text/javascript">
_pnConfig={};
_pnConfig.userId="<USER-ID>";

_pnConfig.onLoadComplete = function() {
    //optionally provide a callback function that is fired when the SDK has been loaded
};

var _pnAPIURL=document.location.protocol+"//js.b.playnomics.net/v1/api?a=<APPID>",
_pnAPI=document.createElement("script");
_pnAPI.type="text/javascript";_pnAPI.async=true;_pnAPI.src=_pnAPIURL;document.body.appendChild(_pnAPI);
</script>
<!-- End Playnomics API -->
```

The `<USER-ID>` should be a persistent, anonymized, and unique to each user. This is typically discerned dynamically when a user starts the application. Some potential implementations:

* If your app is a Facebook canvas application, select the user’s Third Party ID. The sample application demonstrates how you can do this:

```php
<?php
//...
$facebook = new Facebook(array(
    'appId'  => $config["fb"]["appId"],
    'secret' => $config["fb"]["secret"],
));

// Get the current user
$user_id = $facebook->getUser();
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
* A hash of the user’s email address.

**You cannot use the user’s Facebook ID or any personally identifiable information (plain-text email, name, etc) for the `<USER-ID>`.**

`_pnConfig.onLoadComplete` allows you to optionally pass a callback that will be fired when the SDK has finished initialization. This a common place to call the [user info module](#demographics-and-install-attribution).


Congratulations! You've completed our basic integration. You will now be able to track engagement behaviors (having incorporated the Engagement Module) from the PlayRM dashboard. At this point we recommend that you use our integration validation tool to test your integration of our SDK in order insure that it has been properly incorporated in your app. 


PlayRM is currently operating in test mode. Be sure you switch to [production mode](#switch-sdk-to-production-mode), by implementing the code call outlined in our Basic Integration before deploying your app in the web or in an app store.

# Full Integration

<div class="outline">
    <ul>
        <li>
            <a href="#full-integration">Full Integration</a>
            <ul>
                <li><a href="#demographics-and-install-attribution">Demographics and Install Attribution</a></li>
                <li>
                    <a href="#monetization">Monetization</a>
                    <ul>
                        <li>
                            <a href="#purchases-of-in-app-currency-with-real-currency">Purchases of In-App Currency with Real Currency</a>
                        </li>
                        <li>
                            <a href="#purchases-of-items-with-real-currency">Purchases of Items with Real Currency</a>
                        </li>
                        <li>
                            <a href="#purchases-of-items-with-premium-currency">Purchases of Items with Premium Currency</a>
                        </li>
                    </ul>
                </li>
   <li><a href="#custom-event-tracking">Custom Event Tracking</a></li>
                <li><a href="#validate-integration">Validate Integration</a></li>
                <li><a href="#switch-sdk-to-production-mode">Switch SDK to Production Mode</a></li>
            </ul>
        </li>
        <li>
            <a href="#messaging-integration">Messaging Integration</a>
            <ul>
                <li><a href="#sdk-integration">SDK Integration</a></li>
                <li><a href="#using-rich-code-callbacks">Using Rich Data Callbacks</a></li>
            </ul>
        </li>
        <li>
            <a href="#sample-facebook-app">Sample Facebook App</a>
            <ul>
                <li><a href="#getting-started">Getting Started</a></li>
                <li><a href="#dependencies">Dependencies</a></li>
                <li><a href="#disclaimers">Disclaimers</a></li>
                <li><a href="#assumptions">Assumptions</a></li>
            </ul>
        </li>
        <li><a href="#support-issues">Support Issues</a></li>
    </ul>
</div>


If you're reading this it's likely that you've integrated our SDK and are interested in tailoring PlayRM to suit your particular segmentation needs.

The index on the right provides a holistic overview of the <strong>full integration</strong> process. From it, you can jump to specific points in this document depending on what you're looking to learn and do.

To clarify where you are in the timeline of our integration process, you've completed our basic integration. Doing so will enable you to track engagement behaviors from the PlayRM dashboard (having incorporated the Engagement Module). The following documentation provides succinct information on how to incorporate additional and more in-depth segmentation functionality by integrating any, or all of the following into your application:

<ul>
    <li><strong>User Info Module:</strong> - provides basic user information</li>
    <li><strong>Monetization Module:</strong> - tracks various monetization events and transactions</li>
   <li><strong>Custom Events Module:</strong> - tracks significant user events customized to your app</li>
</ul>

Along with integration instructions for our various modules, you will also find integration information pertaining to messaging frame setup, as well as a sample Facebook application with code examples to contextualize how you might incorporate our SDK. 


## Demographics and Install Attribution

After the SDK is loaded, the User Info Module may be called to collect basic demographic and acquisition information. This data is used to segment users based on how/where they were acquired and enables improved targeting with basic demographics, in addition to the behavioral data collected using other events.

Provide each user’s information using this call:

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
            Source of the user, such as "FacebookAds", "UserReferral", "Playnomics", etc. These are only suggestions; any 16-character or shorter string is acceptable
        </td>
    </tr>
    <tr>
        <td><code>sourceCampaign</code></td>
        <td>Any 16-character or shorter string to help identify specific campaigns.</td>
    </tr>
    <tr>
        <td><code>sourceUser</code></td>
        <td>
            If the user was acquired via a UserReferral (i.e., a viral message), the userId of the person who initially brought this user into the app.
        </td>
    </tr>
    <tr>
        <td><code>installTime</code></td>
        <td>
            Unix epoch time in seconds when the user originally installed the app.
        </td>
    </tr>
</table>

You can extract a lot of this information from the Facebook PHP SDK, particularly the <a href="https://developers.facebook.com/docs/reference/api/" target="_blank">Graph API</a>, provided that your canvas application has the right permissions. The Sample App covers this in detail.

## Monetization

PlayRM provides a flexible interface for tracking monetization events. This module should be called every time a user triggers a monetization event. 

This event tracks users that have monetized and the amount they have spent in total, *real* currency:
* FBC (Facebook Credits)
* USD (US Dollars)
* OFD (offer valued in USD)

or an in-app, *virtual* currency.

```javascript
pnTransaction(transactionId, itemId, quantity, type, otherUserId, currencyTypes, 
    currencyValues, currencyCategories);
```
<table>
    <tr>
        <td><code>transactionId</code></td>
        <td>A unique identifier for this transaction. If this is Facebook, you can use the order ID provided to you by Facebook. You can also use an internal ID. If nothing else is available, you can generate large random number.</td>
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
                <li>BuyItem: A purchase of virtual item. The <code>quantity</code> is added to the user’s inventory</li>
                <li>
                    SellItem: A sale of a virtual item to another user. The item is removed from the user’s inventory. Note: a sale of an item will result in two events with the same <code>transactionId</code>, one for the sale with type SellItem, and one for the receipt of that sale, with type BuyItem
                </li>
                <li>
                    ReturnItem: A return of a virtual item to the store. The item is removed from the user’s inventory
                </li>
                <li>BuyService: A purchase of a service, e.g., VIP membership </li>
                <li>SellService: The sale of a service to another user</li>
                <li>ReturnService: The return of a service</li>
                <li>
                    CurrencyConvert: A conversion of currency from one form to another, usually in the form of real currency (e.g., US dollars) to virtual currency.  If the type of a transaction is CurrencyConvert, then there should be at least 2 elements in the <code>currencyTypes</code>, <code>currencyValues</code>, and <code>currencyCategoriess</code> arrays.
                </li>
                <li>Initial: An initial allocation of currency and/or virtual items to a new user</li>
                <li>Free: Free currency or item given to a user by the application</li>
                <li>
                    Reward: Currency or virtual item given by the application as a reward for some action by the user
                </li>
                <li>
                   GiftSend: A virtual item sent from one user to another.

                   Note: a virtual gift should result in two transaction events with the same <code>transactionId</code>, one with the type GiftSend, and another with the type GiftReceive
                </li>
                <li>GiftReceive: A virtual good received by a user. See note for GiftSend type</li>
            </ul>
        </td>
    </tr>
    <tr>
        <td><code>otherUserId</code></td>
        <td>
            If applicable, the other user involved in the transaction. A contextual example is a user sending a gift to another user.
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
                    A <em>virtual</em> app currency, limited to 16 characters.
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
* [Purchases of In-App Currency with Real Currency](#purchases-of-in-app-currency-with-real-currency)
* [Purchases of Items with Real Currency](#purchases-of-items-with-real-currency)
* [Purchases of Items with In-App Currency](#purchases-of-items-with-in-app-currency)

### Purchases of In-App Currency with Real Currency

A very common monetization strategy is to incentivize users to purchase premium, in-app currency with real currency. PlayRM treats this like a currency exchange. This is one of the few cases where currency metadata: `currencyTypes`, `currencyValues`, `currencyCategories` are expressed in an array form. `itemId`, `quantity`, and `otherUserId` are left `null`.

An example of this working in concert with Facebook's Payments Dialog is provided in the Sample App, however, here is a more simplified example:

```javascript
//user purchases 500 MonsterBucks for 10 USD

var quantityBucks = 500;
var gameCurrency = "MonsterBucks";

var priceInUSD = 10;
var transType = "CurrencyConvert";

//it's important that corresponding currency metadata is consistently positioned in each array
pnTransaction(transactionId, null, null, transType, null, [gameCurrency, "USD"], [quantityBucks, priceInUSD * -1],  ["v", "r"]);
```

### Purchases of Items with Real Currency

An example of this working in concert with Facebook's Payments Dialog is provided in the Sample App, however, here is a more simplified example:

```javascript
//user purchases a "Monster Trap" for $.99 USD
var trapItemId = "Monster Trap"
var quantity = 1;
var price = .99;
var transType = "BuyItem";

pnTransaction(tranId, trapItemId, quantity, transType, null, "USD", price, "r");
```

### Purchases of Items with Premium Currency

This event is used to segment monetized users (and potential future monetizers) by collecting information about how and when they spend their premium currency (an in-app currency that is primarily acquired using a *real* currency). This is one level of information deeper than the previous use-cases.

#### Currency Exchanges

This is a continuation on the first currency exchange example. It showcases how to track each purchase of in-app *attention* currency (non-premium virtual currency) paid for with a *premium*:

```javascript

//In this hypothetical, Mana is an attention currency that is earned over the lifetime of the app. 
//They can also be purchased with the premium MonsterBucks that the user may have purchased earlier.

//user buys 100 Mana with 10 MonsterBucks
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
//user buys 20 light armor, for 5 MonsterBucks

var itemQuantity = 20;
var item = "Light Armor";

var transType = "BuyItem";

var premimumCurrency = "MonsterBucks";
var premiumCost = 5;

pnTransaction(transactionId, item, itemQuantity, transType, null, premimumCurrency, premiumCost, "v");
```

## Custom Event Tracking

Custom Events may be used in a number of ways.  They can be used to track certain key in-app events such as finishing a tutorial or receiving a high score. They may also be used to track other important lifecycle events such as level up, zone unlocked, etc.  PlayRM, by default, supports up to five custom events.  You can then use these custom events to create more targeted custom segments.

Each time a user completes a certain event, track it with this call:

```javascript
pnMilestone(milestoneId, milestoneName);
```

These parameters should be replaced:
<table>
    <tr>
        <td><code>milestoneId</code></td>
        <td>A unique 64-bit numeric identifier for this custom event occurrence</td>
    </tr>
    <tr>
        <td><code>milestoneName</code></td>
        <td>
            The name of the custom event which should be "CUSTOMn", where n is 1 through 5.
            The name is case-sensitive.
        </td>
    </tr>
</table>

Example client-side calls for a user reaching a custom event, with generated IDs:

```javascript
function generateLargeId(){
  //generates a 64-bit integer
  return Math.floor(Math.random() * (Math.pow(2,63)- 1));  
}
 

// when custom event CUSTOM1 is completed 
var milestoneCustom1Id = generateLargeId(); 
pnMilestone(milestoneCustom1Id, "CUSTOM1"); 
```
## Validate Integration
After configuring your selected PlayRM modules, you should verify your application's correct integration with the self-check validation service.

Simply visit the self-check page for your application: **`https://controlpanel.playnomics.com/applications/<APPID>`**

You can now see the most recent event data sent by the SDK, with any errors flagged. Visit the  <a href="http://integration.playnomics.com/technical/#self-check">self-check validation guide</a> for more information.

We strongly recommend running the self-check validator before deploying your newly integrated application to production.

## Switch SDK to Production Mode
Once you have [validated](#validate-integration) your integration, you can switch the SDK from **test** to **production** mode, simply change the domain of the PlayRM API URL in the [Basic Integration](#basic-integration) step from **js.b.playnomics.net** to **js.a.playnomics.net**:

```javascript
<!-- Start Playnomics API -->
//...

var _pnAPIURL=document.location.protocol+"//js.a.playnomics.net/v1/api?a=<APPID>",

//...
<!-- End Playnomics API -->
```
If you ever wish to test or troubleshoot your integration later on, simply switch the domain back to **js.b.playnomics.net** and revisit the self-check validation tool for your application:

**`https://controlpanel.playnomics.com/applications/<APPID>`**


Messaging Integration
=====================

This guide assumes you're already familiar with the concept of placements and messaging, and that you have all of the relevant `placements` setup for your application.

If you are new to PlayRM's messaging feature, please refer to <a href="http://integration.playnomics.com/support/#core-concepts" target="_blank">Core Concepts for Messaging</a>.

Once you have all of your placements created with their associated `<PLAYRM-FRAME-ID>`s, you can start the integration process.

## SDK Integration

To tell PlayRM where to place the placement, you first create an empty `div` element in the appropriate location (the id of the `div` is arbitrary):

```html
<div id="messageDiv"></div>
```

Then modify the PlayRM SDK config `_pnConfig` to let PlayRM know about your placement:

```javascript
//this frame is specific to this sample app only
_pnConfig["b0_barDivId"] ="messageDiv";
_pnConfig["b0_frameId"] = "<PLAYRM-FRAME-ID>";
//the height and width of your frame are configurable and based
//on what you set up in the control panel
_pnConfig["b0_width"] = "760";
_pnConfig["b0_height"] = "90";


_pnConfig.userId = "<USER-ID>";
_pnConfig.onLoadComplete = PlaynomicsSample.onLoadComplete;

var _pnAPIURL=document.location.protocol+"//js.b.playnomics.net/v1/api?a=<APPID>",
_pnAPI=document.createElement("script");
_pnAPI.type="text/javascript";
_pnAPI.async=true;_pnAPI.src=_pnAPIURL;document.body.appendChild(_pnAPI);
```

After the SDK has initialized, PlayRM will automatically find the `<div>` tag and replace it with an `iframe` of the appropriate width and height.

Note: This means that the `<div>` tag must exist in the HTML DOM when the SDK initialized.  To achieve greater control over when the placement appears, you could dynamically control its visibility using CSS property as show in this example:

```html
<div style="display:none" id="GameEndInScreen">
   <div id="GameEndInScreenFrame"></div>
</div>
   <input type="button" onclick="document.getElementById('GameEndInScreen').style.display='block'" value="Show Frame">
</body></html>
```

### Using Rich Data Callbacks
Rich Data is a JSON message that you associate with your message creative. When the user presses the message, the PlayRM SDK bubbles-up the associated JSON object to a function that you assign to your frame.

You attach a function to the placement to process JSON data related to a placement. 

```javascript
_pnConfig["b0_barDivId"] ="messageDiv";
_pnConfig["b0_frameId"] = "<PLAYRM-FRAME-ID>";
_pnConfig["b0_width"] = "760";
_pnConfig["b0_height"] = "90";

_pnConfig["b0_onClick"] = function(data){
    //data is a deserialized JSON object, most likely an associative array
}
```

The actual contents of your message can be delayed until the time of the messaging campaign configuration. However, the structure of your message needs to be decided before you can process it in your application.

In this example, the message shown to the user changes based on the desired segments:

<table>
    <thead>
        <tr>
            <th>
                Segment
            </th>
            <th>
                Priority
            </th>
            <th>
                Callback Behavior
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                At-Risk
            </td>
            <td>1st</td>
            <td>
                In this case, we're worried once-active users are now in danger of leaving the app. We might offer them <strong>50 MonsterBucks</strong> to bring them back.
            </td>
        </tr>
        <tr>
            <td>
                Lapsed 7 or more days
            </td>
            <td>2nd</td>
            <td>
                In this case, we want to thank the user for coming back and incentivize these lapsed users to continue doing so. We might offer them <strong>10 MonsterBucks</strong> to increase their engagement and loyalty.
            </td>
        </tr>
        <tr>
            <td>
                Default - users who don't fall into either segment.
            </td>
            <td>3rd</td>
            <td>
                In this case, we can offer a special item to them for returning to the app.
            </td>
        </tr>
    </tbody>
</table>

```javascript
_pnConfig["b0_onClick"] = function(data){
    if(data.type && data.type === "award"){
        if(data.award){
            var item = data.award.item;
            var quantity = data.award.quantity;

            Inventory.addItem(item, quantity);
        }
    }
}
```

The related messages would be configured in the Control Panel to use this callback by placing this in the **Target Data** for each message:

Grant 10 Monster Bucks
```json
{
    "type" : "award",
    "award" : 
    {
        "item" : "MonsterBucks",
        "quantity" : 10
    }
}
```

Grant 50 Monster Bucks
```json
{
    "type" : "award",
    "award" : 
    {
        "item" : "MonsterBucks",
        "quantity" : 50
    }
}
```

Grant Bazooka
```json
{
    "type" : "award",
    "award" :
    {
        "item" : "Bazooka",
        "quantity" : 1
    }
}
```

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

This sample code is a very simple use-case; in reality, your application integration might work a lot differently. Again, the goal of this sample app is to convey how you can integrate some of the major features of Facebook's SDKs with the PlayRM JavaScript SDK.

## Assumptions

* We treat every user like a new user. In reality, **your app server should keep track of each user that joins your application and report attribution appropriately.**
* The app store just has two items and everything is hard-coded. Yuck. We also share the store information with the client (JSON object dump) so that we can better understand the transaction that is taking place on the client side. Your implementation will likely be more data-driven, and should be more selective about what information is available to the web browser.

Contact Support
===============

If you have any questions or issues, please contact <a href="mailto:support@playnomics.com">support@playnomics.com</a>.


