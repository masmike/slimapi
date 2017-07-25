<?php

require 'vendor/autoload.php';
require 'libs/NotORM.php'; 
require 'include/db_conn.php';
require 'include/functions.php';

$dsn = $dbmethod.$dbname;
$pdo = new PDO($dsn, $dbuser, $dbpass);
$db  = new NotORM($pdo);

$app = new \Slim\app;

//testing route params noauth
$app-> get('/coba/{key}', function($request, $response, $args){
    return $response->write("Hello " . $args['key']);
});

$app-> get('/allusers', function($request, $response) use($app, $db){
	foreach($db->users() as $data){
        $users['allusers'][] = array(
            'id' => $data['id'],
            'username' => $data['username'],
            'email' => $data['email'],
            'token' => $data['token']
            );
    }
    return $response->withJson($users, 200, JSON_PRETTY_PRINT);
});

$app-> post('/register', function($request, $response) use($app, $db) {

	$result = array();

	$username = $request->getParsedBody()['username'];
	$email =  $request->getParsedBody()['email'];
	$phone = $request->getParsedBody()['phone'];
	$pass = $request->getParsedBody()['pass'];
	$hash = hashSHA($pass);
	$enc_pass = $hash["encrypted"];
	$salt = $hash["salt"];

	//validate user is exist
	$checkUserExist = $db->users->where("username", $username);
	if ($checkUserExist->fetch()) {
		$result["message"] = "Sorry, this username already existed";
		$result["time"] = date('Y-m-d H:i:s');
		return $response->withJson($result, 200, JSON_PRETTY_PRINT);
	} else {
		//insert
		$apikey = md5(uniqid(rand(), true));
		
		$data = array();
		$data["username"] = $username;
		$data["email"] = $email;
		$data["phone"] = $phone;
		$data["password"] = $enc_pass;
		$data["salt"] = $salt;
		$data["token"] = $apikey;
		$data["created_at"] = date('Y-m-d');

		$res = $db->users->insert($data);
		if ($res) {
			$result["message"] = "success";
			$result["time"] = date('Y-m-d H:i:s');
			return $response->withJson($result, 200, JSON_PRETTY_PRINT);
		} else {
			$result["message"] = "Undefined error";
			$result["time"] = date('Y-m-d H:i:s');
			return $response->withJson($result, 200, JSON_PRETTY_PRINT);
		}
	}
});

$app-> post('/logMeIn', function($request, $response) use($app, $db) {
	$result = array();

	$username = $request->getParsedBody()['email'];
	$password = $request->getParsedBody()['password'];

	// echo $password;

	if(isset($username, $password)) {
	 	$cekUser = $db->users()->where('username', $username);

	 	if($data = $cekUser->fetch()) {
	 		$salt = $data['salt'];
	 		$enc_pass = $data['password'];
	 		$hash = checkHashSHA($salt, $password);
	 		if($enc_pass == $hash) {
	 			$result["error"] = "false";
	 			$result["uid"] = "eweaw12213";
	 			$result["time"] = date('Y-m-d H:i:s');
	 			$result["user"] = array(
	 				"name" => $data["fullname"],
	 				"email" => $data["email"],
	 				"created_at" => $data["created_at"]);
	 			return $response->withJson($result, 200, JSON_PRETTY_PRINT);
	 		} else {
	 			return NULL;
	 		}
	 	} else {
	 		$result["tag"] = "login";
	 		$result["success"] = 0;
	 		$result["error"] = true;
	 		$result["message"] = "Login credentials are wrong. Please try again!";
	 	 	$result["time"] = date('Y-m-d H:i:s');
	 	 	return $response->withJson($result, 200, JSON_PRETTY_PRINT);
	 	}

	} else {
	 	$result["message"] = "Required parameters is missing!";
	 	$result["time"] = date('Y-m-d H:i:s');
	 	return $response->withJson($result, 200, JSON_PRETTY_PRINT);
	};
});

//run App
$app->run();