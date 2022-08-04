<?php
date_default_timezone_set('America/Sao_Paulo');
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Origin, Cache-Control, Pragma, Authorization, Accept, Accept-Encoding,SECRET,AdminAuthorization
");

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once 'configs.php';
require_once 'sqlMethods.php';


$post = $_POST;
$appName = "Vale a pipoca ?";


$resp = array();
if (isset($post["method"]))
{
	$method=$post["method"];
	if($method=="getMovies")
	{
		$connect= getDB();

		$pagenation= pagination();
		$titleFilter = "";
		if (isset($post["titleFilter"]) && $post["titleFilter"] != "") {
			$titleFilter = " and `title` LIKE '%" . $post["titleFilter"] . "%' ";
		}
		$yearFilter = "";
		if (isset($post["yearFilter"]) && $post["yearFilter"] != "") {
			$yearFilter = " and Year(release_date)=" . $post["yearFilter"] ." ";
		}
		$statusFilter = "";
		if (isset($post["statusFilter"]) && $post["statusFilter"] != "") {

			$statusFilter = " and status='" . $post["statusFilter"] . "' ";
		}


		$order=" order by popularity desc ";

		if(isset($post["order"])&& $post["order"]!="" )
		$order= " order by title ". getOrder()."  ";
		if (isset($post["order"]) && $post["order"] != "" && $post["order"]=="2" )
			$order = " order by release_date desc ";

		$sql = "SELECT `movie_id`, `imdb_id`, `title`, `original_title`, `poster_path` FROM `movie` where 1 $titleFilter $yearFilter $statusFilter ";

		$genderFilter = "";
		if (isset($post["genderFilter"]) && $post["genderFilter"] != "") {

			$genderFilter = " and genre_id='" . $post["genderFilter"] . "' ";
			$sql = str_replace("FROM `movie`", "FROM `movie` natural join movie_genre ",$sql );
			$sql.= $genderFilter;

		}

		$sql.=" $order ";

		$resp["movies"]= getSql($connect,$sql.$pagenation);

		$resp["total"]=countSql($connect,$sql);
		$resp["currentSql"] = $sql;

	}
	if ($method == "getActors") {
		$connect = getDB();

		$pagenation = pagination();


		$sql = "SELECT * FROM `person` WHERE person_id in (select person_id from movie_cast) ";



		$resp["actors"] = getSql($connect, $sql . $pagenation);

		$resp["total"] = countSql($connect, $sql);
		$resp["currentSql"] = $sql;
	}
	else if($method=="getMoviesStatus")
	{

		$connect= getDB();
		$sql = "SELECT status,count(*) as qnt FROM `movie` GROUP by status ";
		$resp["moviesStatus"]= getSql($connect,$sql);

	} else if ($method == "getGenders") {

		$connect = getDB();
		$sql = "SELECT `genre_id`, `name` FROM `genre`;";
		$resp["genders"] = getSql($connect, $sql);
	} else if ($method == "getMovieInfo") {

		if(isset($post["movie_id"]))
		{
			$connect = getDB();
			$movie_id= $post["movie_id"];
			if(countSql($connect,"SELECT movie_id from movie where movie_id='$movie_id'")==0 )
				error("Filme não encontrado");

			$sql = "SELECT * FROM `movie` WHERE movie_id='$movie_id'";
			$resp["movieInfos"] = getSql($connect, $sql);


			$castSql
			= "select movie_cast.character as movieCharacter, person.name, person.person_id as personID,movie.movie_id from person inner join movie_cast on movie_cast.person_id= person.person_id inner join movie on movie.movie_id = movie_cast.movie_id where movie.movie_id='$movie_id'";
			$resp["movieCast"]= getSql($connect, $castSql);
			$crewSql
			= "SELECT person.name,person.person_id, movie_crew.department, movie_crew.job FROM `movie_crew` inner join person on person.person_id= movie_crew.people_id WHERE movie_crew.movie_id='$movie_id' ";
			$resp["movieCrew"] = getSql($connect,$crewSql);

			//
			$reviewSql= "SELECT * FROM `review` WHERE movie_id='$movie_id' ";
			$resp["reviews"] = getSql($connect,$reviewSql );


			$resp["movieSql"]=$sql;
			$resp["castSql"]= $castSql;
			$resp["crewSql"] = $crewSql;
			$resp["reviewSql"]= $reviewSql;


		}else wrongParameters();

	}
	else if ($method == "getPersonInfo") {

		if(isset($post["person_id"]))
		{
			$connect = getDB();
			$person_id= $post["person_id"];

			$sql = "select * from person where person_id='$person_id'";
			$resp["personInfo"] = getOneRowSql($connect, $sql);




		}else wrongParameters();

	}

}
else wrongParameters();

$resp["status"]="OK";
$resp["serverTime"]= time();
echo json_encode($resp);


?>
