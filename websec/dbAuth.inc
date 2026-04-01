
     <?php
     //database authentication
     $hostDB="127.0.0.1";
     $userDB ="root";
     $passwordDB="";
     $databaseDB ="websec";
     //connect to database
     $connect = mysqli_connect($hostDB, $userDB,$passwordDB,$databaseDB);
     if (mysqli_connect_errno()){
     die(" cannot connect to database ". mysqli_connect_error());
     }
     $query ="select * from users " ;
     $result= mysqli_query($connect,$query);
     if (!$result){
     die(' error while running query');
     }
     $userInfo=array();
     $loginInUser=null;
 
     mysqli_free_result($result);
     mysqli_close($connect);
     ?>
    