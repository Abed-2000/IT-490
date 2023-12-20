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

function doCreateUser($username, $password, $email)
{
  $login = new loginDB();
  return $login->createUser($username, $password, $email);
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

function doReccomend($query){
  return getRandomUnratedMeal($query);
}

function doShare($mealID, $accountID){
   return profileShare($mealID, $accountID);
}
function doUnshare($mealID, $accountID){
	return profileUnshare($mealID, $accountID);
}
function returnShares($accountID){
	return getShares($accountID);
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
      return doCreateUser($request['username'], $request['password'], $request['email']);
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
    case "unshare":
      return doUnshare($request['mealID'], $request['accountID']);  
     case "searchUser":
      return doSearchUser($request['accountID']);
     case "rank":
      return doRank();
     case "recommend":
      return doReccomend($request['sessionID']);
    case "returnShares":
      return doGetShares($request['accountID']);
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("testRabbitMQ.ini","testServer");

$server->process_requests('requestProcessor');
exit();
?>
