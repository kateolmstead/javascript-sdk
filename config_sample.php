<?php
/*
* These configuration settings would be better stored in an external file or database table.
* We kept this sample very simple so that you can get a general idea of how to make things work.
*/

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

$storeBundles = array(
    1 => array(
        "title"         => "50 Coins",
        "description"   => "50 Coins to spend in Marketplace World!",
        "image_url"     => "http://imageUrl",
        //this is the price in Facebook Credits, equivalent to $5.99 USD
        "price"         => 599,
        "data"          => array(
            "quantity"                  => 50,
            "type"                      => "currency",
            //describes what items we are actually selling in the bundle
            "singular_name"             => "Coin"
        )
    ),
    2 => array(
        "title"         => "100 Widgets",
        "description"   => "100 widgets to help you build things in Marketplace World!",
        "image_url"     => "http://imageUrl",
        //this is the price in Facebook Credits, equivalent to $5.99 USD
        "price"         => 599,
        "data"          => array(
            "quantity"          => 100,
            "type"              => "items",
            //describes what items we are actually selling in the bundle
            "singular_name"     => "Widget"
        )
    ),
);
?>
