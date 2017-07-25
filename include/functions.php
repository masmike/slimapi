<?php
/* Create by masmike
*/

function hashSHA($password) {
	$salt = sha1(rand());
    $salt = substr($salt, 0, 10);
    $encrypted = base64_encode(sha1($password . $salt, true) . $salt);
    $hash = array("salt" => $salt, "encrypted" => $encrypted);
    
    return $hash;
}

function checkHashSHA($salt, $password) {
    $hash = base64_encode(sha1($password . $salt, true) . $salt);
 
    return $hash;
}

