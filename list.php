<?php 
    require("header.php");
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

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if($_POST['action'] == 'Delete' && $_POST['id']){
            $id = $_POST['id'];
            $userRepo->delete_user($id);

            $users = $userRepo->get_users();
        }

        
    }

    if ($_SERVER["REQUEST_METHOD"] == "GET") {

        if(isset($_GET['search'])){
            $users = $userRepo->get_users();
            $search = strtolower($_GET['search']);

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
?>
<!DOCTYPE html>
<script>
    function navigateAddUser(){
        window.location.href = "add_user.php";
    }

    function navigateEditUser(value){
        window.location.href = "edit_user.php?id=" + value;
    }

    //show delete user confirmation
    function showModal(userId){
        //show modal
        var modal = document.getElementById("modal");
        modal.style.display = "block";

        //set value of hidden ID field in the modal to the user ID that was clicked on
        var idInput = document.getElementById("userToDelete");
        idInput.value = userId;
    }

    //hide delete user confirmation
    function hideModal(){
        var modal = document.getElementById("modal");
        modal.style.display = "none";
    }
    

    function onFilterChanged(){
        
        var items = document.getElementsByClassName("row");
        var search = document.getElementById("search").value;
        var count = Array.from(items).length;
        var filteredItems = [];

        //delete 'no results' row if it exists
        if(document.getElementsByClassName('row-empty').length > 0){
            var deleteRow = document.getElementsByClassName('row-empty')[0];
            deleteRow.remove();
        }
        
        //loop through rows of table. if contains search text then keep row. if not then set display:none
        Array.from(items).forEach(function(el) {
            var text = el.innerText;
            
            if(text.toUpperCase().indexOf(search.toUpperCase()) > -1){
                el.style.display = "";
            }
            else{
                el.style.display = "none";
                filteredItems.push(el);
            }
        });


        //if no results then add a row with text 'No users found'
        if(filteredItems.length == count){

            var body = document.querySelector('tbody');
            body.innerHTML += "<tr class=\"row-empty\"><td class=\"data\" align=\"center\" colspan=\"8\">No users found.</td></tr>"
        }
        
    }
    var timer = null;
    function onSearch(){
        clearTimeout(timer);
        timer = setTimeout(() => {
            doneTyping();
        },1000);
    }

    function doneTyping(str){
        var form = document.getElementById("phpsearchform");
        form.submit();
    }

    function ajaxOnSearchEntered(str){
        clearTimeout(timer);
        setTimeout(function() {
            ajaxDoneTyping(str);
        },1000);
    }

    async function ajaxDoneTyping(str){

        var body = document.querySelector('tbody');

        try{
            var response = await fetch("search.php?q=" + str);

            if(!response.ok){
                throw await response.status;
            }

            var text = await response.text();
            body.innerHTML = text; 
        }
        catch(error){
            body.innerHTML = "<tr class=\"row-empty\"><td class=\"data\" align=\"center\" colspan=\"8\">An error ocurred when trying to query users.</td></tr>"
        }
}

</script>
<style>
    .data {
        border: solid 1px #a6a6a6;
        padding: 5px;
        text-align: center;
    }

    .header {
        border: none;
        text-align: center;
        font-weight: bold;
        padding: 5px;
        background-color: #2e507c;
        color: white;
        border-right: solid 1px white;;
    }

    table {
        border-collapse: collapse;
        font-family: Roboto,sans-serif;
    }

    #modal {
        position: absolute;
        margin:0 auto;
        width: 200px;
        height: 150px;
        border: 1px solid black;
        text-align: center;
        display: none;
        background-color: white;
        box-shadow: 5px 10px 18px #888888;
    }

    table tr:nth-child(even) {
        background-color: #ccc;
    }

    .row-hidden {
        display: none;
    }
</style>
<html>
    <head>
        <title>Users</title>
        
    </head>
    <h3>User Management</h3>
    <body>
            <div id="modal" style="display:<?php if($isValid){echo "block";}else{echo "none";} ?>">
                <h3>Delete User</h3>
                <span>Are you sure you want to delete this user?</span><br/>
                <form  method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                    <input hidden="hidden" id="userToDelete" name="id" value="0" />
                    <button type="button" onclick="hideModal()">Cancel</button>
                    <input type="submit" name="action" value="Delete"/>
                </form>
            </div>
        <button onclick="navigateAddUser()">Add User</button>
        <br/>
        JavaScript Search:<input id="search" placeholder="Search" onkeyup="onFilterChanged()" value=""/>
        <form id="phpsearchform" method="GET" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            PHP Search: <input id="searchphp" name="search" onkeyup="onSearch()" placeholder="Search" value="<?php echo $search ?>"/>
        </form>
        
        PHP/AJAX Search: <input onkeyup="ajaxOnSearchEntered(this.value)" placeholder="Search"/>
        
        <table id="user-table">
            <thead>
                <tr>
                    <td class="header">Id</td>
                    <td class="header">User Name</td>
                    <td class="header">Email</td>
                    <td class="header">First Name</td>
                    <td class="header">Last Name</td>
                    <td class="header">Active</td>
                    <td class="header">Locked</td>
                    <td class="header">Actions</td>
                </tr>
            </thead>
            <tbody>
            <?php if(count($users) == 0) : ?>
                <tr class="row">
                    <td class="data" align="center" colspan="8">No users found.</td>
                </tr>
            <?php endif; ?>

            <?php foreach($users as $value) : ?>
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
            <?php endforeach; ?>
            </tbody>
        </table>
        
    </body>
</html>