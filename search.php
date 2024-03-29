<?php

session_start();

require("./model/User.php");
require("./Data/UserRepository.php");

$search = "";

$dbService = new DatabaseService();
$userRepo = new UserRepository($dbService);

$users = $userRepo->get_users();

if ($_SERVER["REQUEST_METHOD"] == "GET") {

    if(isset($_GET['q'])){
        $search = strtolower($_GET['q']);

        foreach($users as $k=>$v) { 
            $found = false;
            foreach ($users[$k] as $key=>$value) { 

                //skip password,active,locked etc
                if($key == "active" || $key == "password" || $key == "locked" || $key == "incorrectLoginAttempts" || $key=="lockoutEnd"){
                    continue;
                }

                $v = strtolower($value);

                if(str_contains($v,$search) ){
                    $found = true;
                }
            }  
            if(!$found){
                unset($users[$k]); 
            }
        }
    }
}

if(count($users) > 0){
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
                <button class="action-button" value="<?php echo $value->id; ?>" name="edit" type="submit" >Edit</button>
                        <?php if(isset($_SESSION['username']) && $_SESSION['username'] == $value->userName) : ?>
                            <button disabled class="action-button readonly" value="<?php echo $value->userName; ?>" type="button" onclick="showModal('<?php echo $value->userName; ?>')">Delete</button>
                        <?php else : ?>
                            <button class="action-button" value="<?php echo $value->userName; ?>" type="button" onclick="showModal('<?php echo $value->userName; ?>')">Delete</button>
                        <?php endif; ?>
                </td>
            </tr>
        <?php 
    }
}
else{
    echo "<tr class=\"row-empty\">";
    echo "<td class=\"data\" align=\"center\" colspan=\"8\">No users found.</td>";
    echo "</tr>";
}


exit();
?>