<?php
require 'Views/Sections.php';
require_once 'mail/mailHelper.php';
require_once 'configSXC.php';

// the errors array
$errors = array();
// allocate the faculty and post first
$faculties = \sxc\Models\DbHelper::getDepartmentLists();
$posts = \sxc\Models\DbHelper::getPostListFromDatabase();

if (isset($_GET['action'])) {
    \sxc\Views\Sections::generateHeader("CDS " . $_GET['action']);
} else {
    \sxc\Views\Sections::generateHeader("CDS Operation");
}

if (! isset($_SESSION['userLoggedIn'])) {
    die("Please Login to complete this request");
} else {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['cdsAddUser'])) {
            $defaults = $_POST;
            if ($errors = validateUserInfo()) {
                showErrors($errors);
                displayAddUserForm($defaults);
            } else {
                storeUserToDatabase();
            }
        } elseif (isset($_POST['cdsReport'])) {
            updateComplainStatus($_POST['remarks'], $_POST['cdsReport']);
            $complainDetails = \sxc\Models\DbHelper::getComplainerUserNameById($_POST['cdsReport']);
            $person = \sxc\Models\DbHelper::viewUser($complainDetails['complainBy'], 'username');
            $hodEmail = \sxc\Models\DbHelper::getHodEmail($person->getDepartment());
            $messageBody = "
                In response to the complain lodged by the staff of <b>{$person->getDepartment()}</b> Department <b> Mr/Ms. {$person->getName()}</b>. ,The 
                following action has been taken:<br />
                <div style='background:#9c9;border-bottom:1px solid grey; padding: 15px;'>
                Room No : $complainDetails[roomNo]<br />
                Room Name : $complainDetails[roomName]<br />
                Computer Number : $complainDetails[compNo]<br />
                Details : <br />
                $complainDetails[details]<br />
                </div> 
                <hr />
                The action taken by our staff of <b>Department of Computer Science</b> is <br />:
                <div style='background-color:#f00;color: #fff;padding: 10px;'>
                    <b>Status :</b> $complainDetails[status]<br /> 
                    <b>Details : </b> $_POST[remarks]
                </div> 
            ";
            sendEmail($hodEmail, "Respected HOD", 'Complain Action', $messageBody);
            viewComplains();
        } elseif (isset($_POST['cdsUpdateUser'])) {
            $defaults = $_POST;
            if ($errors = validateUpdateInfo()) {
                showErrors($errors);
                displayUpdateUserForm($defaults, $_POST['cdsUpdateUser']);
            } else {
                updateUser();
            }
        }
    } else {
        $defaults = [
            'fullName' => '',
            'officeID' => '',
            'department' => '',
            'post' => '',
            'email' => '',
            'username' => '',
            'pass' => '',
            'rePass' => ''
        ];
        if (isset($_GET['action'])) {
            if ($_GET['action'] == 'addUser') {
                displayAddUserForm($defaults);
            } elseif ($_GET['action'] == 'viewUsers') {
                viewUsers();
            } elseif ($_GET['action'] == 'complains') {
                viewComplains();
            } elseif ($_GET['action'] == 'report' && isset($_GET['id'])) {
                displayReportForm($_GET['id']);
            } elseif ($_GET['action'] == 'viewPendingComplains') {
                viewComplains('pending');
            } elseif ($_GET['action'] == 'viewProfile' && isset($_GET['username'])) {
                $person = \sxc\Models\DbHelper::viewUser($_GET['username'], "username");
                displayData($person);
            } elseif ($_GET['action'] == 'edit' && isset($_GET['id'])) {
                $person = \sxc\Models\DbHelper::viewUser($_GET['id'], "id");
                $defaults = [
                    'fullName' => $person->getName(),
                    'officeID' => $person->getId(),
                    'department' => $person->getDepartment(),
                    'post' => $person->getPost(),
                    'email' => $person->getEmail(),
                    'username' => $person->getUsername(),
                    'pass' => $person->getPassword(),
                    'rePass' => $person->getPassword()
                ];
                displayUpdateUserForm($defaults, $_GET['id']);
            } elseif ($_GET['action'] == 'delete' && isset($_GET['id'])) {
                \sxc\Models\DbHelper::deleteUser($_GET['id']);
                viewUsers();
            } elseif ($_GET['action'] == 'viewComplainByNumber' && isset($_GET['for'])) {
                viewAllComplains($_GET['for']);
            }
        }
    }
}

?>


<?php \sxc\Views\Sections::generateFooter();?>

<?php

function displayAddUserForm($defaults)
{
    // allocate the faculty and post first
    global $faculties;
    global $posts;
    echo <<<HEREDOC
	<div class='alert alert-info' role='alert' style='text-align:center'>
      <h3>Add New User</h3>
    </div>
		<form method='post' name='addUserForm' action='$_SERVER[PHP_SELF]' class='loginForm'>
			<table>
				<tr>
					<td>Full Name : </td>
					<td><input type='text' name='fullName' value = '$defaults[fullName]' required/></td>
				</tr>
				<tr>
					<td>Office ID : </td>
					<td><input type='text' name='officeID' value = '$defaults[officeID]' required/></td>
				</tr>
HEREDOC;
    echo "<tr><td>Department : </td><td><select name='department' style='width: 100%;'>";
    \sxc\Views\Sections::generateSelectMenuSameValue($faculties, $defaults['department']);
    echo "</td></tr>";
    echo "<tr><td>Post : </td><td><select name='post' style='width: 100%;'>";
    \sxc\Views\Sections::generateSelectMenuSameValue($posts, $defaults['post']);
    echo "</td></tr>";
    echo <<<HEREDOC2
		<tr>
			<td>Email </td>
			<td><input type='email' name='email' value = '$defaults[email]' required/></td>
		</tr>
		<tr>
			<td>Username : </td>
			<td><input type='text' name='username' value = '$defaults[username]' required/></td>
		</tr>
		<tr>
			<td>Password </td>
			<td><input type='password' name='pass' value='$defaults[pass]' required/></td>
		</tr>
		<tr>
			<td>Confirm Password </td>
			<td><input type='password' name='rePass' value = '$defaults[rePass]' required/></td>
		</tr>
		<tr>
	        <td><input type="hidden" name="cdsAddUser" value='1'></td>
	        <td><button type="submit" class="btn btn-info" style="width:100%;">Add User</button></td>
        </tr>
	</table>
	</form>
HEREDOC2;
}

function displayUpdateUserForm($defaults, $target)
{
    // allocate the faculty and post first
    global $faculties;
    global $posts;
    echo <<<HEREDOC
	<div class='alert alert-info' role='alert' style='text-align:center'>
      <h3>Add New User</h3>
    </div>
		<form method='post' name='addUserForm' action='$_SERVER[PHP_SELF]' class='loginForm'>
			<table>
				<tr>
					<td>Full Name : </td>
					<td><input type='text' name='fullName' value = '$defaults[fullName]' required/></td>
				</tr>
				<tr>
					<td>Office ID : </td>
					<td><input type='text' name='officeID' value = '$defaults[officeID]' required/></td>
				</tr>
HEREDOC;
    echo "<tr><td>Department : </td><td><select name='department' style='width: 100%;'>";
    \sxc\Views\Sections::generateSelectMenuSameValue($faculties, $defaults['department']);
    echo "</td></tr>";
    echo "<tr><td>Post : </td><td><select name='post' style='width: 100%;'>";
    \sxc\Views\Sections::generateSelectMenuSameValue($posts, $defaults['post']);
    echo "</td></tr>";
    echo <<<HEREDOC2
		<tr>
			<td>Email </td>
			<td><input type='email' name='email' value = '$defaults[email]' required/></td>
		</tr>
		<tr>
			<td>Username : </td>
			<td><input type='text' name='username' value = '$defaults[username]' required/></td>
		</tr>
		<tr>
			<td>Password </td>
			<td><input type='password' name='pass' value='$defaults[pass]' required/></td>
		</tr>
		<tr>
			<td>Confirm Password </td>
			<td><input type='password' name='rePass' value = '$defaults[rePass]' required/></td>
		</tr>
		<tr>
	        <td><input type="hidden" name="cdsUpdateUser" value='$target'></td>
	        <td><button type="submit" class="btn btn-info" style="width:100%;">Update User</button></td>
        </tr>
	</table>
	</form>
HEREDOC2;
}

function sanitizeInput()
{
    // sanitize the input first
    $_POST['fullName'] = strip_tags(trim($_POST['fullName']));
    $_POST['officeID'] = strip_tags(trim($_POST['officeID']));
    $_POST['department'] = strip_tags(trim($_POST['department']));
    $_POST['post'] = strip_tags(trim($_POST['post']));
    $_POST['email'] = strip_tags(trim($_POST['email']));
    $_POST['username'] = strip_tags(trim($_POST['username']));
    $_POST['pass'] = strip_tags(trim($_POST['pass']));
    $_POST['rePass'] = strip_tags(trim($_POST['rePass']));
}

function validateUserInfo()
{
    // preparation for validation of email
    global $errors;
    $input['email'] = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    if (strlen($_POST['fullName']) == 0 || strlen($_POST['officeID']) == 0 || strlen($_POST['username']) == 0 || strlen($_POST['pass']) == 0 || strlen($_POST['rePass']) == 0) {
        $errors[] = "Some fields are left empty";
    }
    if ($_POST['pass'] != $_POST['rePass']) {
        $errors[] = "Passwords doesn't match";
    }
    if (\sxc\Models\DbHelper::isNotAUniqueUser($_POST['username'])) {
        $errors[] = "Username $_POST[username] already used";
    }
    if (strlen($_POST['pass']) < 8) {
        $errors[] = 'Passwords should be at least 8 characters long';
    }
    if (! $input['email']) {
        $errors[] = 'Please enter a valid email address';
    }
    if (! in_array($_POST['department'], $GLOBALS['faculties'])) {
        $errors[] = 'Invalid Department Input';
    }
    if (! in_array($_POST['post'], $GLOBALS['posts'])) {
        $errors[] = 'Invalid Post Input';
    }
    return $errors;
}

function validateUpdateInfo()
{
    // preparation for validation of email
    global $errors;
    $input['email'] = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    if (strlen($_POST['fullName']) == 0 || strlen($_POST['officeID']) == 0 || strlen($_POST['username']) == 0 || strlen($_POST['pass']) == 0 || strlen($_POST['rePass']) == 0) {
        $errors[] = "Some fields are left empty";
    }
    if ($_POST['pass'] != $_POST['rePass']) {
        $errors[] = "Passwords doesn't match";
    }
    if (strlen($_POST['pass']) < 8) {
        $errors[] = 'Passwords should be at least 8 characters long';
    }
    if (! $input['email']) {
        $errors[] = 'Please enter a valid email address';
    }
    if (! in_array($_POST['department'], $GLOBALS['faculties'])) {
        $errors[] = 'Invalid Department Input';
    }
    if (! in_array($_POST['post'], $GLOBALS['posts'])) {
        $errors[] = 'Invalid Post Input';
    }
    return $errors;
}

function showErrors($errors)
{
    echo "<table id='errorTable'>";
    echo "<tr><td id='errorHeading' class='alert alert-info' role='alert'>Please Correct These errors : </td></tr>";
    $index = 0;
    $colors = [
        "#9c9",
        "#ddd"
    ];
    foreach ($errors as $error) {
        echo "<tr><td style='background: $colors[$index];'>$error</td></tr>";
        $index = 1 - $index;
    }
    echo "</table>";
}

function storeUserToDatabase()
{
    $person = new \sxc\Models\Person();
    // filling the data to object person
    $person->setName($_POST['fullName']);
    $person->setId($_POST['officeID']);
    $person->setUserName($_POST['username']);
    $person->setPassword($_POST['pass']);
    $person->setEmail($_POST['email']);
    $person->setDepartment($_POST['department']);
    $person->setPost($_POST['post']);
    $person->setAuthority('user');
    
    // store to the database
    \sxc\Models\DbHelper::addUserToDatabase($person);
    viewUsers();
}

function updateUser()
{
    $person = new \sxc\Models\Person();
    // filling the data to object person
    $person->setName($_POST['fullName']);
    $person->setId($_POST['officeID']);
    $person->setUserName($_POST['username']);
    $person->setPassword($_POST['pass']);
    $person->setEmail($_POST['email']);
    $person->setDepartment($_POST['department']);
    $person->setPost($_POST['post']);
    $person->setAuthority('user');
    
    // store to the database
    \sxc\Models\DbHelper::updateUser($person);
    viewUsers();
}

function viewUsers()
{
    $i = 1;
    echo "<table id='viewData'>";
    echo "<tr>";
    echo "<th>S.N</th>";
    echo "<th>Office ID</th>";
    echo "<th>Name</th>";
    echo "<th>Department</th>";
    echo "<th>Post</th>";
    echo "<th>Authority</th>";
    echo "<th>Email</th>";
    echo "<th>Operation</th>";
    echo "</tr>";
    $person = new \sxc\Models\Person();
    try {
        $db = new \PDO('mysql:host=' . hostname . ';dbname=' . dbName, dbUsername, dbPassword);
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $stmt = $db->query("select * from users order by id desc");
        while ($row = $stmt->fetch()) {
            if ($row['authority'] != 'admin' && $row['authority'] != 'cds') {
                echo "<tr>";
                echo "<td>$i</td>";
                echo "<td>$row[officeId]</td>";
                echo "<td>$row[name]</td>";
                echo "<td>$row[department]</td>";
                echo "<td>$row[post]</td>";
                echo "<td>$row[authority]</td>";
                echo "<td>$row[email]</td>";
                echo "<td><a href='$_SERVER[PHP_SELF]?action=delete&id=$row[id]'>Delete</a><br />
				  <a href='$_SERVER[PHP_SELF]?action=edit&id=$row[id]' >Update</a></td>";
                echo "</tr>";
                ++ $i;
            }
        }
        echo "</table>";
        $db = null;
    } catch (\PDOException $e) {
        echo "Cannot make connection to the database " . $e->getMessage();
    }
}

function viewComplains($type = "default")
{
    $i = 1;
    echo date("Y-m-d H:i:s");
    echo "<table id='viewData'>";
    echo "<tr>";
    echo "<th>S.N</th>";
    echo "<th>Complain By</th>";
    echo "<th>Details</th>";
    echo "<th>Status</th>";
    echo "<th>Handle By</th>";
    echo "<th>Complain Date</th>";
    echo "<th>Handled Date</th>";
    echo "<th>Remarks</th>";
    echo "<th>option</th>";
    echo "</tr>";
    try {
        $db = new \PDO('mysql:host=' . hostname . ';dbname=' . dbName, dbUsername, dbPassword);
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $stmt = $db->query("select * from complains order by id desc limit 100");
        while ($row = $stmt->fetch()) {
            $complainDate = date("F j, Y, g:i a", intval($row['complainDate']));
            $handleDate = date("F j, Y, g:i a", intval($row['handledDate']));
            if ($type == "default") {
                echo "<tr>";
                echo "<td>$i</td>";
                echo "<td><a href='$_SERVER[PHP_SELF]?action=viewProfile&username=$row[complainBy]'>$row[complainBy]</a></td>";
                echo "<td><div class='detail'>Room No : $row[roomNo]<br />Room Name: $row[roomName]<br />Computer No : $row[compNo]</div>$row[Details]</td>";
                echo "<td>$row[status]</td>";
                echo "<td><a href='$_SERVER[PHP_SELF]?action=viewProfile&username=$row[handledBy]'>$row[handledBy]</a></td>";
                echo "<td>$complainDate</td>";
                echo "<td>";
                if ($row['handledDate'] == null) {
                    echo "</td>";
                } else {
                    echo "$handleDate</td>";
                }
                echo "<td>$row[Remarks]</td>";
                echo "<td>";
                if ($row['status'] != 'acted') {
                    echo "<a href='$_SERVER[PHP_SELF]?action=report&id=$row[id]'>Report</a>";
                }
                echo "</td>";
                echo "</tr>";
                ++ $i;
            } elseif ($type = "pending") {
                if ($row['status'] == 'pending') {
                    echo "<tr>";
                    echo "<td>$i</td>";
                    echo "<td><a href='$_SERVER[PHP_SELF]?action=viewProfile&username=$row[complainBy]'>$row[complainBy]</a></td>";
                    echo "<td><div class='detail'>Room No : $row[roomNo]<br />Room Name: $row[roomName]<br />Computer No : $row[compNo]</div>$row[Details]</td>";
                    echo "<td>$row[status]</td>";
                    echo "<td><a href='$_SERVER[PHP_SELF]?action=viewProfile&username=$row[handledBy]'>$row[handledBy]</a></td>";
                    echo "<td>$complainDate</td>";
                    echo "<td>";
                    if ($row['handledDate'] == null) {
                        echo "</td>";
                    } else {
                        echo "$handleDate</td>";
                    }
                    echo "<td>$row[Remarks]</td>";
                    echo "<td><a href='$_SERVER[PHP_SELF]?action=report&id=$row[id]'>Report</a>";
                    echo "</tr>";
                    ++ $i;
                }
            }
        }
        echo "</table>";
        $db = null;
    } catch (\PDOException $e) {
        echo "Cannot make connection to the database " . $e->getMessage();
    }
}

function displayReportForm($id)
{
    try {
        $db = new \PDO('mysql:host=' . hostname . ';dbname=' . dbName, dbUsername, dbPassword);
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $stmt = $db->query("select * from complains where id=$id");
        while ($row = $stmt->fetch()) {
            echo <<< DISP
            <div class = 'reportForm'>
                <form name='report' method='post' action= '$_SERVER[PHP_SELF]'>
                    <table>
                        <tr>
                            <td colspan='2'>
                                <h5>$row[complainBy]</h5>
                                <p>$row[Details]</p>
                            </td>
                        </tr>
                        <tr>
                            <td>Remarks</td>
                            <td><textarea name='remarks' rows='5' cols='30'></textarea></td>
                        </tr>
                        <tr>
                        	<td>Action</td>
                        	<td>
                        		<input type='radio' value = 'acted' name='action' checked/>Acted
                        		<input type='radio' value = 'pending' name='action' />Pending
                        	</td>
                        </tr>
                        <tr>
                	        <td><input type="hidden" name="cdsReport" value='$id'></td>
                	        <td><button type="submit" class="btn btn-info" style="width:400px%;">Report This Problem</button></td>
                        </tr>
                    </table>
                </form>
            </div>       
DISP;
        }
        $db = null;
    } catch (\PDOException $e) {
        echo "Cannot make connection to the database " . $e->getMessage();
    }
}

function updateComplainStatus($remarks, $id)
{
    try {
        $currentDate = time();
        $status = $_POST['action'];
        $user = $_SESSION['userLoggedIn']->getUsername();
        $db = new \PDO('mysql:host=' . hostname . ';dbname=' . dbName, dbUsername, dbPassword);
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $stmt = $db->prepare("update complains set status='$status', handledBy='$user', handledDate='$currentDate', Remarks=? where id = ? ");
        $stmt->execute(array(
            $remarks,
            $id
        ));
        $db = null;
    } catch (\PDOException $e) {
        echo "Cannot make connection to the database " . $e->getMessage();
    }
}

function displayData($person)
{
    if ($person->getUsername() != null) {
        $fullName = ucwords($person->getName());
        $department = ucwords($person->getDepartment());
        $officeId = ucwords($person->getId());
        $post = ucwords($person->getPost());
        $authority = ucwords($person->getAuthority());
        $email = ucwords($person->getEmail());
        $totalComplains = \sxc\Models\DbHelper::getTotalComplains($person->getUsername());
        $username = $person->getUsername();
        echo <<<DISP
    <table id='viewData'>
            <tr>
                <td>Name : </td>
                <td>$fullName</td>
            </tr>
            <tr>
                <td>Department : </td>
                <td>$department</td>
            </tr>
            <tr>
                <td>Office ID : </td>
                <td>$officeId</td>
            </tr>
            <tr>
                <td>Post : </td>
                <td>$post</td>
            </td>
            <tr>
                <td>Authority : </td>
                <td>$authority</td>
            </td>
            <tr>
                <td>Email : </td>
                <td>$email</td>
            </td>
            <tr>
                <td>Total Complains : </td>
                <td><a href='$_SERVER[PHP_SELF]?action=viewComplainByNumber&for=$username'>$totalComplains</a></td>
            </td>
    </table>
DISP;
    } else {
        echo "<div class='alert alert-danger' role='alert'>
               Request Profile not found. It is deleted from database!!!!
              </div>";
    }
}

function viewAllComplains($identity)
{
    // only my complains should be viewed
    $person = new \sxc\Models\Person();
    try {
        $db = new \PDO('mysql:host=' . hostname . ';dbname=' . dbName, dbUsername, dbPassword);
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $stmt = $db->prepare("select * from complains where complainBy=? order by id desc");
        $stmt->execute(array(
            $identity
        ));
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
        while ($row = $stmt->fetch()) {
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
    } catch (\PDOException $e) {
        echo "Cannot make connection to the database " . $e->getMessage();
    }
}
?>