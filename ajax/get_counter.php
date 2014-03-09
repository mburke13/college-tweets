<?php
require_once(__DIR__ . '/../config.php');
header('Content-type: application/json');
$db = new SQLite3(DATABASE_NAME);
$statement = $db->prepare('SELECT * FROM counter');
$result = $statement->execute()->fetchArray(SQLITE3_ASSOC);
if($result){
	echo(json_encode(array("success"=>true, "count" => $result['count'])));
}
else{
	http_response_code(404);
}
?>