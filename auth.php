<?php

include('config.php');
include('./automatic-php-api/PHP-OAuth2/Client.php');
include('automatic-php-api/PHP-OAuth2/GrantType/IGrantType.php');
include('automatic-php-api/PHP-OAuth2/GrantType/AuthorizationCode.php');
include('automatic-php-api/Automatic/class.Automatic.php');


$client = new OAuth2\Client(AUTOMATIC_CLIENT_ID, AUTOMATIC_CLIENT_SECRET);
$automatic = new Automatic(AUTOMATIC_CLIENT_ID, AUTOMATIC_CLIENT_SECRET);

session_start();

if(isset($_SESSION["automatic_token"])){
	$automatic->setOAuthToken($_SESSION["automatic_token"]);
}

if(!$automatic->isLoggedIn()){
	if (!isset($_GET['code'])){
		$scopes = array("scope:location", "scope:vehicle", "scope:trip:summary");
		$auth_url = $automatic->authenticationURLForScopes($scopes);
	    header('Location: ' . $auth_url);
	    die('Redirect');
	}
	else{
	    $response_token = $automatic->getTokenForCode($_GET["code"]);
	    $_SESSION["automatic_token"] = $response_token; // keep user logged in w/ session
	    $automatic->setOAuthToken($_SESSION["automatic_token"]);

	    $qr = $response_token->access_token;
	    var_dump($response_token);
	    echo "<img src='http://chart.apis.google.com/chart?cht=qr&chs=500x500&chl=$qr&chld=H|0'/>";
	}
}
else if(isset($_REQUEST["logout"])){
	session_destroy();
	header("Location: " . AUTOMATIC_REDIRECT_URI);
	exit;
}

// we only get here after we've logged into Automatic

echo "<a href='?logout'>Log Out</a>";
echo "<br><br>";

$response = $automatic->getTrips(1, 5);
print_r($response);

if(count($response["result"])){
	// show that we can also fetch a single trip
	$trip = $response["result"][0];
	$response = $automatic->getTrip($trip["id"]);
	
	echo "\n\n\n";
	print_r($response);
}

?>