<?php
namespace sxc;
require_once 'mail/mailHelper.php';
require_once 'configSXC.php';


require 'Views/Sections.php';
require_once 'configSXC.php';
\sxc\Views\Sections::generateHeader("Complain");

if (isset($_GET['action'])&&$_GET ['action'] == 'viewMyComplains') {
	// only my complains should be viewed
	$person = new \sxc\Models\Person ();
	try {
		$db = new \PDO ( 'mysql:host='.hostname.';dbname='.dbName, dbUsername, dbPassword );
		$db->setAttribute ( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
		$stmt = $db->prepare ( "select * from complains where complainBy=? order by id desc" );
		$stmt->execute ( array (
				$_SESSION ['userLoggedIn']->getUsername ()
		) );
		echo <<<DisplayTable
						<table id='viewData'>
							<tr>
								<th>complain Date</th>
								<th>Details</th>
								<th>status</th>
								<th>Handled By</th>
								<th>Handled Date</th>
								<th>Remarks</th>
							</tr>
DisplayTable;
		while ( $row = $stmt->fetch () ) {
			$complainDate = date("F j, Y, g:i a", intval($row['complainDate']));
			$handleDate = date("F j, Y, g:i a", intval($row['handledDate']));
			echo "<tr>";
			echo "<td>$complainDate</td>";
			echo "<td><div class='detail'>Room No : $row[roomNo]<br />Room Name: $row[roomName]<br />Computer No : $row[compNo]</div>$row[Details]</td>";
			echo "<td>$row[status]</td>";
			echo "<td>$row[handledBy]</td>";
			echo "<td>$handleDate</td>";
			echo "<td>$row[Remarks]</td>";
			echo "</tr>";
		}
		echo "</table>";
		$db = null;
	} catch ( \PDOException $e ) {
		echo "Cannot make connection to the database " . $e->getMessage ();
	}
}

if (isset($_SESSION['userLoggedIn']) && !isset($_POST['complainFormSUbCheck'])) {
    displayComplainForm();
} else {
    // check for submission of the form
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if(isset($_POST['complainFormSUbCheck'])&&$_POST['complainFormSUbCheck']==2){
            //storeComplainToDatabase
            $complain = [
                            "roomNo"=>"$_POST[roomNo]",
                            "roomName"=>"$_POST[roomName]",
                            "compNo"=>"$_POST[compNo]",
                            "details"=>"$_POST[details]",
            ];
            \sxc\Models\DbHelper::storeComplainToDatabase($_SESSION['userLoggedIn'], $complain);
            displayComplainForm();
            $complainer = $_SESSION["userLoggedIn"]->getUsername();
            $messageBody = "<div style='background:#ff5c33;border-bottom:1px solid grey; padding: 15px;'>
            Complain By: $complainer<br />
            Room No : $_POST[roomNo]<br />
            Room Name : $_POST[roomName]<br />
            Computer Number : $_POST[compNo]<br />
            Details : <br />
            $_POST[details]<br />
            </div> ";
            sendEmailToCDS("Complain Alert", $messageBody);
        }else{
            // sanitize the input first
            $_POST['username'] = trim($_POST['username']);
            $_POST['password'] = trim($_POST['password']);
            $operator = \sxc\Models\DbHelper::getUser($_POST['username'], $_POST['password']);
            if ($operator->getId() != null) {
                $_SESSION['userLoggedIn'] = $operator;
                displayComplainForm();
                header("Refresh:0");
            } else {
                echo "<div class='alert alert-danger' role='alert'>
               Invalid Login Information !!!
              </div>";
                \sxc\Views\Sections::displayLoginForm('login');
            }
        }
    } else {
        \sxc\Views\Sections::displayLoginForm('login');
    }
}

\sxc\Views\Sections::generateFooter();
?>
<?php

function displayComplainForm()
{
    if(isset($_GET['action']) && $_GET['action']=='viewMyComplains'){
    	
    }else{
    	echo <<<COMPLAIN
	<form name='f1' method='post' action='$_SERVER[PHP_SELF]' class='loginForm'>
		<table id='complainTable'>
			<tr>
				<td>Room Number : </td>
				<td><input type='number' maxlength='3' name='roomNo' style='width:100%;'/></td>
			</tr>
            <tr>
				<td>Room Name :<br /> (If no room Number) </td>
				<td><input type='text' name='roomName' style='width:100%;'/></td>
			</tr>
			<tr>
				<td>Computer Number : </td>
				<td><input type='text' name='compNo' style='width:100%;'/></td>
			</tr>
			<tr>
				<td>Problem Details : </td>
				<td>
					<textarea name='details' rows="5" cols="30" style='width:100%;'></textarea>
				</td>
			</tr>
            <tr>
	        <td><input type="hidden" name="complainFormSUbCheck" value='2'></td>
	        <td><button type="submit" class="btn btn-info" style="width:100%;">Submit Complain</button></td>
        </tr>
		</table>
	</form>
COMPLAIN;
    }
}
?>
