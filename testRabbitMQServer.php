#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require_once('login.php.inc');
include_once('sessions_handler.php');

function doLogin($username,$password)
{
    // lookup username in databas
    // check password
    $login = new loginDB();
    return $login->validateLogin($username,$password);
    //return false if not valid
}

function doCreateUser($username, $password, $email, $twoFactor)
{
  $login = new loginDB();
  return $login->createUser($username, $password, $email, $twoFactor);
}

function doValidate($sessionID)
{
  return validate_session($sessionID);
}

function doLogout($sessionID)
{
  return destroy_session($sessionID);
}
function doRate($mealID, $accountID, $rating){
	return rateRecipe($mealID, $accountID, $rating);
}
function doSave($query){
  return saveRecipe($query);
}

function doSearchMeals($query){
  return searchMeals($query);
}

function doMealDetails($query){
  return mealDetails($query);
}

function doRank(){
	return getRank();
}

function getRating($query){
  return getMealRating($query);
}

function validate2step($username, $authCode){
  return validateAuthentication($username, $authCode);
}

function doValidateAuthLife($userId){
  return validateAuthLife($userId);
}

function requestProcessor($request)
{
  echo "received request".PHP_EOL;
  var_dump($request);
  if(!isset($request['type']))
  {
    return "ERROR: unsupported message type";
  }
  switch ($request['type'])
  {
    case "login":
      return doLogin($request['username'],$request['password']);
    case "validate_session":
      return doValidate($request['sessionID']);
    case "register":
      return doCreateUser($request['username'], $request['password'], $request['email'], $request['twoFactor']);
    case "logout":
      return doLogout($request['sessionID']);
    case "rate":
      return doRate($request['mealID'], $request['accountID'], $request['rating']);
    case "save_recipe":
      return doSave($request['sessionID']);
    case "searchMeals":
      return doSearchMeals($request['sessionID']);
    case "mealDetails":
      return doMealDetails($request['sessionID']);
    case "share":
      return doShare($request['mealID'], $request['accountID']);
    case "searchUser":
      return doSearchUser($request['accountID']);
    case "rank":
      return doRank();
    case "getMealRating":
      return getRating($request['sessionID']);
    case "checkAuthLife":
      return doValidateAuthLife($request['userId']);
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("testRabbitMQ.ini","testServer");

$server->process_requests('requestProcessor');
exit();
?>