<form id='login' action='transactions.php' method='post' acceptcharset='UTF-8'>
 <div class="row">
 <div class="col-sm">
 <label for='ToUserName' >To UserName*:</label>
 </div>
 <div class="col-sm">
 <input type='text' class="form-control mb-2 mr-sm-2"
name='ToUserName' id='ToUserName' maxlength="50" required />
 </div>
 <div class="col-sm">
 <label for='Amount' >Amount:</label>
 </div>
 <div class="col-sm">
 <input type='text'class="form-control mb-2 mr-sm-2"
name='Amount' id='Amount' maxlength="50" required />
 <input type="hidden" name="fromUserName" value="<?=
$_COOKIE['userName'];?>" />
<input type="hidden" name="csrf" value="<?=
$_SESSION['token'];?>" />
 </div>
 <div class="col-sm">
 <label for='Notes' >Notes:</label>
 </div>
 <div class="col-sm">
 <input type='text'class="form-control mb-2 mr-sm-2"
name='Notes' id='Notes' />
 <div class="col-sm">
 <input type='submit' class="btn btn-primary mb-2" name='Submit'
id='submit' value='Send' />
 </div>
 </div>

 <h2> List of transactions</h2>
 <table class="table table-bordered table-striped">
 <thead>
 <tr>
 <th> transaction id </td>
 <th> from </td>
 <th> to</td>
 <th> amount</td>
 <th> notes</td>
 </tr>
 </thead>
 <tbody>
<?php
// Security check for directory traversal and other special characters
foreach (array($_GET, $_POST, $_COOKIE) as $superglobal) {
    foreach ($superglobal as $key => $value) {
        if (is_string($value)) {
            $decoded_value = urldecode($value);
            if (
                // Check for "pont pont", "slash", "backlash" in decoded value
                strpos($decoded_value, '..') !== false ||
                strpos($decoded_value, '/') !== false ||
                strpos($decoded_value, '\\') !== false ||

                // Check for specific encoded patterns in raw value
                stripos($value, '%2e%2e%2f') !== false ||
                stripos($value, '%2e%2e/') !== false ||
                stripos($value, '..%2f') !== false ||
                stripos($value, '%2e%2e%5c') !== false ||
                stripos($value, '%c1%1c') !== false ||
                stripos($value, '%c0%af') !== false
            ) {
                die('Invalid characters in input detected.');
            }
        }
    }
}
//Database Authentication
require(__DIR__ . "/dbAuth.inc");
require(__DIR__ . "/session.php");
//connect to database
$connect = mysqli_connect($hostDB, $userDB,$passwordDB,$databaseDB);
if(mysqli_connect_errno()) {
 die(" cannot connect to database ". mysqli_connect_error());
}

//Add new transaction
if(!empty($_POST['fromUserName']) and !empty($_POST['ToUserName'])) {
    $query ="insert into transactions(fromUser, toUser, amount, notes)
    values ('". $_POST['fromUserName'] ."','". $_POST['ToUserName'] ."',".
    $_POST['Amount'] .",'". $_POST['Notes'] . "')";
    $result= mysqli_query($connect,$query);
    if (!$result){
    die(' error while running query');
    }
   }

$userToken = isset($_GET['userToken']) ? $_GET['userToken'] : '';
// get user name from token
$query ="select * from users where userToken='" . $userToken ."'";
$result= mysqli_query($connect,$query);
if (!$result) {
die(' error while running query');
}
$userName=null;
while ($row= mysqli_fetch_assoc($result)) {
 $userName= $row["userName"];
 break; // to be save
}
// get user transactions
if( !empty($userName)) {
 $query ="select * from transactions where fromUser='". $userName ."' or
toUser='". $userName ."'" ;
 $result= mysqli_query($connect,$query);
 if (!$result){
 die(' Error cannot run query');
 }
 $userInfo=array();
 $loginInUser=null;
 while ($row= mysqli_fetch_assoc($result)) {
 $rowColor ="class='success'";
 if($row["fromUser"]==$userName){
 $rowColor ="class='danger'";
 }
 echo " <tr ". $rowColor .">";
 echo " <td>". $row["id"] ." </td>";
 echo " <td>". $row["fromUser"] ." </td>";
 echo " <td>". $row["toUser"]."</td>";
 echo " <td>". $row["amount"]."</td>";
 echo " <td>". htmlentities($row["notes"])."</td>";
 echo " </tr>";
 }
 mysqli_free_result($result);
}

mysqli_close($connect);
?>
 </tbody>
 </table>