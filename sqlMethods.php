<?php

function getDB()
{
	$connect = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);
	mysqli_set_charset($connect, "utf8") or die(mysqli_error($connect));
	$sql = "SET time_zone = '-3:00';";
	$result = mysqli_query($connect, $sql) or die(mysqlError(mysqli_error($connect)));

	return $connect;
}


function executeSQL($connect, $sql)
{

	$result = mysqli_query($connect, $sql) or die(mysqli_error($connect));
	return $result;
}
function countSql($connect, $sql)
{
	$result = mysqli_query($connect, $sql) or die(mysqli_error($connect));
	return mysqli_num_rows($result);
}
function getSql($connect, $sql)
{
	$respx = array();

	$result = mysqli_query($connect, $sql) or die(mysqli_error($connect));
	if (mysqli_num_rows($result) >= 1) {

		$rows = array();
		while ($r = mysqli_fetch_assoc($result)) {
			$rows[] = $r;
		}
		$respx = $rows;
	}
	return $respx;
}
function getOneRowSql($connect, $sql)
{;
	$result = mysqli_query($connect, $sql) or die(mysqlError(mysqli_error($connect)));
	if (mysqli_num_rows($result) >= 1) {
		$r = mysqli_fetch_assoc($result);
		return $r;
	}
	return  (object)[];
}


function wrongParameters()
{
	$resp = array();
	$resp["status"] = "error";
	$resp["message"] = "Parametros inválidos";
	die(json_encode($resp));
}

function mysqlError($error)
{
	$resp = array();
	$resp["message"] = "Mysql erro";
	$resp["error"] = $error;
	$resp["status"] = "error";
	return (json_encode($resp));
}


//Utils
function generateRandomCode($length = 5)
{
	$characters = '0123456789';
	$charactersLength = strlen($characters);
	$randomString = '';
	for ($i = 0;
		$i < $length;
		$i++
	) {
		$randomString .= $characters[rand(0, $charactersLength - 1)];
	}
	return $randomString;
}

function generateRandomString($length = 5)
{
	$characters = '0123456789abcdefghijklmnopqrstuvwxyz';
	$charactersLength = strlen($characters);
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, $charactersLength - 1)];
	}
	return $randomString;
}
function writelog($text,$file)
{
	file_put_contents($file, $text . PHP_EOL, FILE_APPEND | LOCK_EX);
}
function requestLog($text)
{
	file_put_contents('requestslog.txt', $text . PHP_EOL, FILE_APPEND | LOCK_EX);
}
function md6($pass)
{
	$hash = password_hash($pass, PASSWORD_DEFAULT, ['cost' => 12]);
	return $hash;
}
function uploadImage($path, $postname)
{
	$uploaddir = $path;
	$imgcomprovante = "";
	if (isset($_FILES[$postname])) {
		$ext = pathinfo($_FILES[$postname]['name'], PATHINFO_EXTENSION);
		if (!is_image($_FILES[$postname]['tmp_name'])) {
			error("Arquivo $postname não é uma imagem");
		}
		$frontImage = $uploaddir . (generateRandomString(32) . "." . $ext);
		if (move_uploaded_file($_FILES[$postname]['tmp_name'], $frontImage)) {
			$imgcomprovante = $frontImage;
			return $imgcomprovante;
		} else {
			error("Falha ao carregar imagem - $postname");
		}
	} else error("Imagem $postname não enviada");
}
function uploadDocument($path, $postname)
{
	$uploaddir = $path;
	$imgcomprovante = "";
	if (isset($_FILES[$postname])) {
		$ext = pathinfo($_FILES[$postname]['name'], PATHINFO_EXTENSION);

		$frontImage = $uploaddir . (generateRandomString(16) . "." . $ext);
		if (move_uploaded_file($_FILES[$postname]['tmp_name'], $frontImage)) {
			$imgcomprovante = $frontImage;
			return $imgcomprovante;
		} else {
			error("Falha ao carregar imagem - $postname");
		}
	} else error("Documento $postname não enviada");
}
function is_image($path)
{
	$a = getimagesize($path);
	$image_type = $a[2];

	if (in_array($image_type, array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_BMP))) {
		return true;
	}
	return false;
}

function dateRangeFilter($paramName)
{
	$datefilter = "";
	$post = $_POST;

	if (isset($post["dataInicial"]) && isset($post["dataFinal"]) && $post["dataInicial"]!="" && $post["dataFinal"]!="" ) {
		try {
			$dataInicio =	date_create_from_format("Y-m-d", $post["dataInicial"]);
			$dataFim =	date_create_from_format("Y-m-d", $post["dataFinal"]);


			$dataInicio = $post["dataInicial"];
			$dataFim = $post["dataFinal"];
			$datefilter = " and $paramName>='$dataInicio' and $paramName<='$dataFim' ";

			//	$resp["datas"]=$datas;

		} catch (Exception $e) {
			error("Por favor digite as datas com valor correto");
		}
	}
	return $datefilter;
}
function pagination()
{
	$pagecount = 12;
	if (isset($_POST["page"]) && $_POST['page'] != "") {
		try {
			$page = intval($_POST["page"]);
			if ($page <= 0) $page = 1;

			$offset = ($page - 1) * $pagecount;
			return " LIMIT " . $offset . "," . $pagecount;
		} catch (Exception $e) {
			return "";
		}
	} else return "";
}

function deleteFile($path)
{
	if (file_exists($path)) {
		unlink($path);
	}
}
function getOrder()
{
	$order = "desc";
	if (isset($_POST["order"])) {
		$o = $_POST["order"];
		if ($o == "0") {
			$order = "desc";
		}
		if ($o == "1") {
			$order = "asc";
		}
	}
	return $order;
}
function error($message)
{
	$resp = array();
	$resp["message"] = $message;
	$resp["status"] = "error";
	die(json_encode($resp));
}



?>