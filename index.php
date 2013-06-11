<?php 
/*
* @Purpose: display a leader board for the game
*
* @How it works: 

	1- get the parameters passed (userID, access token)
	2- fetch the user's friend list from FB graph
	3- save result in array
	4- get those friends who have records in game mysql DB
	5- get the friends score as well as the user's score and display them in a descent order according to score

* @Author: Ala
*
* @Date created: 6/4/2013
*
* @Version: 1 
*/



// function to reoreder the result descendently according to score
/*
* @ purpose: to reorder the array according to user's score
*
* @ param array $array: array of users name and their score
*
* @ return array $reference:  the reorder array
*/

function array_sort_by_column(&$arr, $col, $dir = SORT_DESC) {
    $sort_col = array();
    foreach ($arr as $key=> $row) {
        $sort_col[$key] = $row[$col];
    }

    array_multisort($sort_col, $dir, $arr);
}








//print_r($_SERVER['REQUEST_URI']);

//include the DB needed
include "libgame_db_connect.php";


if (isset($_GET['id']) && isset($_GET['user_access_token'])) {

$userID=$_GET['id'];
$userAccessToken=$_GET['user_access_token'];
$FACEBOOK_BASE_API="https://graph.facebook.com/";

//https://graph.facebook.com/alasl31/friends?access_token=CAACEdEose0cBAMeiyfaEndHKrf9eCkzTn4hojYUKWNEsdk6XXZCraVkf4ZBYYxqmQZAISntDjZBsVT0JXjNSJcxJUSxidwIcZBWCoeUHMDyJfhbZAfH8osCYftY2j5QQoa4ZAJxpjWPv6BfXJpdfbpkQSIgT3TRAt4ZD

$friendsURL=$FACEBOOK_BASE_API.$userID."/friends?access_token=".$userAccessToken;
$friendsJsonObject=file_get_contents($friendsURL);

$friendsList=json_decode($friendsJsonObject);
$list=get_object_vars($friendsList);
$arrayList=$list['data'];


// get list of friends
foreach($arrayList as $friend) {


	$friendObject=get_object_vars($friend);
	$id=$friendObject['id'];
	$friendsArray[]=array('id'=>$id);





}
// don't forget to add our user
$friendsArray[]=array('id'=>$userID);

// now get the users that exist in friend list and our DB







// array to save the users and their score
$leaderBoard=array();
foreach ($friendsArray as $friend) {  //loop through users

	// query to get their scores
	$query= " SELECT user_id,user_name as name,SUM(POINTS) as score FROM quests, quest_progresses, users WHERE 
	user_id={$friend['id']} and completed=1 and quests.id=quest_progresses.quest_id AND users.id=user_id";

	$result=$con->query($query) or die("database error" . $con->error);

	$records=$result->fetch_assoc();
	// if there is obe exists in our DB then save it to $leaderBoard
	if ($records['name'] !="") {
		
		
		$leaderBoard=array('id'=>$records['user_id'],'name'=>$records['name'],'score'=>$records['score']);

	}

}


// now we need to sort the array in descendent order

array_sort_by_column($leaderBoard, 'score');


?>




<!DOCTYPE html>
<html>
  <head>
   <title>LeaderBoard </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap -->
    <!--<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">-->

   <link rel="stylesheet" type="text/css" href="http://gvsu.edu/cms3/assets/741ECAAE-BD54-A816-71DAF591D1D7955C/libui.css" />
   <link rel="stylesheet" type="text/css" href="style.css" />
  </head>
  <body>
    <div style="text-align:center"> <h1>Welcome to The Library Game Leaderboard!</h1></div>

<table>

 <thead>
	<?php 

// if there is no friends then display none
	if (!empty($leaderBoard[0])) {

		echo " <thead>";
		echo "<TD><B>#</B></TD><TD><B>Name</B></TD><TD><B>Score</B></TD>";
		echo " </thead>";

		// variable to display the numbers 1,2,3 next to the row
		$order=1;

		echo "<tbody>";
			foreach($leaderBoard as $list) {
			
				

				echo "<TR><TD>".$order."</TD><TD>".$list['name']."</TD><TD>".$list['score']."</TD><TR>";
				
			}
		echo "</tbody>";

			$order++;
		

	} else {

			echo '<div class="lib-alert center" >
					<p><B>Sorry! We have not found you in our list :( </B></p>
				</div>';

	}

	echo '</table>';	
?>


  <script src="http://code.jquery.com/jquery.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>
  </body>
</html>


<?php } else {

		echo 
					'Sorry! The information is not recognized !';
				
}
?>

