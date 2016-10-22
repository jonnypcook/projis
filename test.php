<?php 
$db = new PDO('mysql:host=localhost;dbname=8p3crm;charset=utf8', 'root', '');
try {
    //connect as appropriate as above
    $stmt = $db->query('SELECT * FROM `BuyingType`'); //invalid query!
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    die('ok');
} catch(PDOException $ex) {
    echo "An Error occured!"; //user friendly message
}


 ?>
