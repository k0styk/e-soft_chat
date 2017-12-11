<html>
 <head>
	<meta charset="utf-8">
	<title>Главная</title>
 </head>
 <body>
	Trying connect to postGRE;<BR><BR>
<?php	
///		
///		
///		
///		ФАЙЛ ПРЕДНАЗНАЧЕН ДЛЯ ОТЛАДКИ
///		
///		
///		
if(isset($_POST['date'])) {
	
$expiration = date('Y-m-d H:i:s', strtotime($_POST['date']));
echo $expiration;
	exit;
include "php\lib\dbConnector.php";
$db = new dbConnector();
echo "hi";
echo "<BR><BR>";
$result1 = $db->blockRoomMember("1","user1",$expiration);
var_dump($result1);
echo "<BR><BR>";
exit;
}



// include "lib\dbConnector.php";
// $db = new dbConnector();
// echo "<BR><BR>";
// $result1 = $db->putRoomMember("1","user1");
// var_dump($result1);

?>
	
 </body>
</html>