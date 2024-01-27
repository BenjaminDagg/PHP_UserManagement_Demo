<?php
require("./model/User.php");
require("./Data/UserRepository.php");

$search = "";

$servername = "localhost";
$username = "sa";
$password = "Diamond1!";
$dbname = "test";

$dbService = new MyDatabaseService($servername,$username,$password,$dbname);
$userRepo = new UserRepository($dbService);

$users = $userRepo->get_users();

if ($_SERVER["REQUEST_METHOD"] == "GET") {

    if(isset($_GET['q'])){
        $search = $_GET['q'];

        foreach($users as $k=>$v) { 
            $found = false;
            foreach ($users[$k] as $key=>$value) { 
                
                //skip password,active,locked etc
                if($key == "active" || $key == "password" || $key == "locked" || $key == "incorrectLoginAttempts" || $key=="lockoutEnd"){
                    continue;
                }

                if(str_contains($value,$search) ){
                    $found = true;
                }
            }  
            if(!$found){
                unset($users[$k]); 
            }
        }
    }
}

//loop over users and create tr elements with data to return from GET request
foreach($users as $value){
    echo ''?>
        <tr class="row">
            <td class="data"><?php echo $value->id ?></td>
            <td class="data"><?php echo $value->userName ?></td>
            <td class="data"><?php echo $value->email ?></td>
            <td class="data"><?php echo $value->firstName ?></td>
            <td class="data"><?php echo $value->lastName ?></td>
            <td class="data"><?php echo ($value->active ? "Yes" : "No") ?></td>
            <td class="data"><?php echo ($value->locked ? "Yes" : "No") ?></td>
            <td class="data">
                <button type="button" onclick="navigateEditUser('<?php echo $value->id; ?>')">Edit</button>
                <button value="<?php echo $value->id; ?>" type="button" onclick="showModal('<?php echo $value->id; ?>')">Delete</button>
            </td>
        </tr>
    <?php 
}


exit();
?>