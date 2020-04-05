<?php
require 'Views/Sections.php';

// the errors array
$errors = array ();
// allocate the faculty and post first
$faculties = \sxc\Models\DbHelper::getDepartmentLists ();
$posts = \sxc\Models\DbHelper::getPostListFromDatabase();

if (isset ( $_GET ['action'] )) {
	\sxc\Views\Sections::generateHeader ( "Admin " . $_GET ['action'] );
} else {
	\sxc\Views\Sections::generateHeader ( "Admin Operation" );
}

if (! isset ( $_SESSION ['userLoggedIn'] )) {
	die ( "Please Login to complete this request" );
} else {
	if ($_SERVER ['REQUEST_METHOD'] == 'POST') {
		if (isset ( $_POST ['addCDS'] )) {
			$defaults = $_POST;
			if ($errors = validateUserInfo ()) {
				showErrors ( $errors );
				displayAddCDSForm ( $defaults );
			} else {
				storeUserToDatabase ();
			}
		} elseif (isset ( $_POST ['cdsAddDepartment'] )) {
			$departmentDefaults = $_POST;
			if ($errors = validateDeparmentInformation ()) {
				showErrors ( $errors );
				displayAddDepartmentForm ( $departmentDefaults );
			} else {
				insertIntoDepartmentTable ();
			}
		} elseif (isset ( $_POST ['updateCDS'] )) {
			$defaults = $_POST;
			if ($errors = validateUpdateInfo ()) {
				showErrors ( $errors );
				displayUpdateUserForm ( $defaults, $_POST ['updateCDS'] );
			} else {
				updateUser ();
			}
		} elseIf (isset ( $_POST ['adminUpdateHOD'] )) {
			$departmentDefaults = $_POST;
			if ($errors = validateDeparmentInformation ()) {
				showErrors ( $errors );
				displayUpdateDepartmentForm ( $departmentDefaults, $_POST ['adminUpdateHOD'] );
			} else {
				updateDepartment ();
				viewDepartments ();
			}
		}elseif(isset($_POST['updatePostFromDatabase'])){
			$postName = $_POST['postName'];
			\sxc\Models\DbHelper::updatePostNameFromDatabase($_POST['postName'], $_POST['updatePostFromDatabase']);
			viewPosts();
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
		$departmentDefaults = [ 
				'dName' => '',
				'hName' => '',
				'dEmail' => '' 
		];
		if (isset ( $_GET ['action'] )) {
			if ($_GET ['action'] == 'addCDS') {
				displayAddCDSForm ( $defaults );
			} elseif ($_GET ['action'] == 'viewCDS') {
				viewCDS ();
			} elseif ($_GET ['action'] == 'addDepartment') {
				displayAddDepartmentForm ( $departmentDefaults );
			} elseif ($_GET ['action'] == 'viewDepartments') {
				viewDepartments ();
			} elseif ($_GET ['action'] == 'viewPendingComplains') {
				viewComplains ( 'pending' );
			} elseif ($_GET ['action'] == 'viewProfile' && isset ( $_GET ['username'] )) {
				$person = \sxc\Models\DbHelper::viewUser ( $_GET ['username'], "username" );
				displayData ( $person );
			} elseif ($_GET ['action'] == 'edit' && isset ( $_GET ['id'] )) {
				$department = \sxc\Models\DbHelper::getDepartmentObject ( $_GET ['id'] );
				$departmentDefaults = [ 
						'dName' => $department->getDepartmentName (),
						'hName' => $department->getHodName (),
						'dEmail' => $department->getHodEmail () 
				];
				displayUpdateDepartmentForm ( $departmentDefaults, $_GET ['id'] );
			} elseif ($_GET ['action'] == 'delete' && isset ( $_GET ['id'] )) {
				\sxc\Models\DbHelper::DeleteDepartment ( $_GET ['id'] );
				viewDepartments ();
			} elseif ($_GET ['action'] == 'editCDS' && isset ( $_GET ['id'] )) {
				$user = \sxc\Models\DbHelper::viewUser ( $_GET ['id'], 'id' );
				$defaults = [ 
						'fullName' => $user->getName (),
						'officeID' => $user->getId (),
						'department' => $user->getDepartment (),
						'post' => $user->getPost (),
						'email' => $user->getEmail (),
						'username' => $user->getUsername (),
						'pass' => $user->getPassword (),
						'rePass' => $user->getPassword () 
				];
				displayUpdateUserForm ( $defaults, $_GET ['id'] );
			}elseif($_GET['action']=='deleteCDS' && isset($_GET['id'])){
				\sxc\Models\DbHelper::deleteUser($_GET['id']);	
				viewCDS();
			}elseif ($_GET ['action'] == 'departmentComplains') {
				displayDepartmentChoice ();
			} elseif ($_GET ['action'] == 'viewDepartmentComplains') {
				$department = $_GET ['department'];
				// TODO get all users from department
				$users = \sxc\Models\DbHelper::getAllUSersFromDepartment ( $department );
				
				// TODO display Complains of all users
				
				foreach ( $users as $key=>$temp ) {
					echo <<<HEREDOC
					<div class='alert alert-info' role='alert' style='text-align:center'>
				      <h3>Complain by $key</h3>
				    </div>
HEREDOC;
					$person = new \sxc\Models\Person ();
					try {
						$db = new \PDO ( 'mysql:host='.hostname.';dbname='.dbName, dbUsername, dbPassword );
						$db->setAttribute ( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
						$stmt = $db->prepare ( "select * from complains where complainBy=? order by id desc" );
						$stmt->execute ( array (
								$temp 
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
							$complainDate = date ( "F j, Y, g:i a", intval ( $row ['complainDate'] ) );
							$handleDate = date ( "F j, Y, g:i a", intval ( $row ['handledDate'] ) );
							echo "<tr>";
							echo "<td>$complainDate</td>";
							echo "<td><div class='detail'>Room No : $row[roomNo]<br />Room Name: $row[roomName]<br />Computer No : $row[compNo]</div>$row[Details]</td>";
							echo "<td>$row[status]</td>";
							echo "<td><a href='$_SERVER[PHP_SELF]?action=viewProfile&username=$row[handledBy]'>$row[handledBy]</a></td>";
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
			}elseif($_GET['action']=='viewComplainByNumber' && isset($_GET['for'])){
				viewAllComplains($_GET['for']);
			}elseif($_GET['action']=='addPosts'){
				viewAddPostForm();
			}elseif($_GET['action']=='addPostToDatabase'){
				addPostToDatabase($_GET['postName']);
				viewPosts();
			}elseif($_GET['action']=='deletePost' && isset($_GET['id'])){
				\sxc\Models\DbHelper::deletePostFromDatabase($_GET['id']);
				viewPosts();
			}elseif($_GET['action']=='updatePost' && isset($_GET['id'])){
				$postName = \sxc\Models\DbHelper::getPostNameById($_GET['id']);
				echo $postName;
				displayUpdatePostForm($postName,$_GET['id']);
			}if($_GET['action']=='viewPosts'){
				viewPosts();
			}
		}
	}
}

?>


<?php \sxc\Views\Sections::generateFooter();?>

<?php
function displayUpdatePostForm($postName,$id){
	echo <<<HTML
	<form name='f2' method='post' action='$_SERVER[PHP_SELF]' class='loginForm'>
		Post Name : <input type = 'text' name='postName' value = '$postName' required/><br />
		<input type='hidden' name='updatePostFromDatabase' value='$id'/>
		<button type='submit' class='btn btn-info' style='width:100%;'>Update Post</button>
	</form>
HTML;
}
function viewPosts(){
	$i = 1;
	echo "<table id='viewData'>";
	echo "<tr>";
	echo "<th>S.N</th>";
	echo "<th>Post Name</th>";
	echo "<th>Operations </th>";
	echo "</tr>";
	$person = new \sxc\Models\Person ();
	try {
		$db = new \PDO ( 'mysql:host='.hostname.';dbname='.dbName, dbUsername, dbPassword );
		$db->setAttribute ( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
		$stmt = $db->query ( "select * from posts order by id desc" );
		while ( $row = $stmt->fetch () ) {
			echo "<tr>";
			echo "<td>$i</td>";
			echo "<td>$row[postName]</td>";
			echo "<td><a href='$_SERVER[PHP_SELF]?action=deletePost&id=$row[id]'>Delete</a><br />
			<a href='$_SERVER[PHP_SELF]?action=updatePost&id=$row[id]'>Update</a></td>";
			echo "</tr>";
			++ $i;
		}
		echo "</table>";
		$db = null;
	} catch ( \PDOException $e ) {
		echo "Cannot make connection to the database " . $e->getMessage ();
	}
}
function addPostToDatabase($postName){
	if(\sxc\Models\DbHelper::isUniquePost($postName)){
		\sxc\Models\DbHelper::insertPost($postName);
	}else{
		$errors[] = "Post named $postName was already added in database";
		showErrors($errors);
	}
}
function viewAddPostForm(){
	echo <<<HTML
	<form name='f2' method='get' action='$_SERVER[PHP_SELF]' class='loginForm'>
		Post Name : <input type = 'text' name='postName' required/><br />
		<input type='hidden' name='action' value='addPostToDatabase'/>
		<button type='submit' class='btn btn-info' style='width:100%;'>Add Post</button>
	</form>
HTML;
}

function displayDepartmentChoice() {
	$departments = \sxc\Models\DbHelper::getDepartmentLists ();
	echo "<form name='f1' class='loginForm' method='get' action='$_SERVER[PHP_SELF]'>";
	echo "Select Department : ";
	echo "<select name='department'>";
	foreach ( $departments as $temp ) {
		echo "<option value='$temp'>$temp</option>";
	}
	echo "</select><br />";
	echo "<input type='hidden' name='action' value='viewDepartmentComplains'/>";
	echo "<button type='submit' class='btn btn-info' style='width:100%;'>View Complains</button>";
}
function displayAddCDSForm($defaults) {
	// allocate the faculty and post first
	global $faculties;
	global $posts;
	echo <<<HEREDOC
	<div class='alert alert-info' role='alert' style='text-align:center'>
      <h3>Add CDS USER</h3>
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
	\sxc\Views\Sections::generateSelectMenuSameValue ( $faculties, $defaults ['department'] );
	echo "</td></tr>";
	echo "<tr><td>Post : </td><td><select name='post' style='width: 100%;'>";
	\sxc\Views\Sections::generateSelectMenuSameValue ( $posts, $defaults ['post'] );
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
	        <td><input type="hidden" name="addCDS" value='1'></td>
	        <td><button type="submit" class="btn btn-info" style="width:100%;">Add User</button></td>
        </tr>
	</table>
	</form>
HEREDOC2;
}
function displayUpdateUserForm($defaults, $target) {
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
	\sxc\Views\Sections::generateSelectMenuSameValue ( $faculties, $defaults ['department'] );
	echo "</td></tr>";
	echo "<tr><td>Post : </td><td><select name='post' style='width: 100%;'>";
	\sxc\Views\Sections::generateSelectMenuSameValue ( $posts, $defaults ['post'] );
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
	        <td><input type="hidden" name="updateCDS" value='$target'></td>
	        <td><button type="submit" class="btn btn-info" style="width:100%;">Update CDS</button></td>
        </tr>
	</table>
	</form>
HEREDOC2;
}
function sanitizeInput() {
	// sanitize the input first
	$_POST ['fullName'] = strip_tags ( trim ( $_POST ['fullName'] ) );
	$_POST ['officeID'] = strip_tags ( trim ( $_POST ['officeID'] ) );
	$_POST ['department'] = strip_tags ( trim ( $_POST ['department'] ) );
	$_POST ['post'] = strip_tags ( trim ( $_POST ['post'] ) );
	$_POST ['email'] = strip_tags ( trim ( $_POST ['email'] ) );
	$_POST ['username'] = strip_tags ( trim ( $_POST ['username'] ) );
	$_POST ['pass'] = strip_tags ( trim ( $_POST ['pass'] ) );
	$_POST ['rePass'] = strip_tags ( trim ( $_POST ['rePass'] ) );
}
function validateUserInfo() {
	// preparation for validation of email
	global $errors;
	$input ['email'] = filter_input ( INPUT_POST, 'email', FILTER_VALIDATE_EMAIL );
	if (strlen ( $_POST ['fullName'] ) == 0 || strlen ( $_POST ['officeID'] ) == 0 || strlen ( $_POST ['username'] ) == 0 || strlen ( $_POST ['pass'] ) == 0 || strlen ( $_POST ['rePass'] ) == 0) {
		$errors [] = "Some fields are left empty";
	}
	if ($_POST ['pass'] != $_POST ['rePass']) {
		$errors [] = "Passwords doesn't match";
	}
	if (\sxc\Models\DbHelper::isNotAUniqueUser ( $_POST ['username'] )) {
		$errors [] = "Username $_POST[username] already used";
	}
	if (strlen ( $_POST ['pass'] ) < 8) {
		$errors [] = 'Passwords should be at least 8 characters long';
	}
	if (! $input ['email']) {
		$errors [] = 'Please enter a valid email address';
	}
	if (! in_array ( $_POST ['department'], $GLOBALS ['faculties'] )) {
		$errors [] = 'Invalid Department Input';
	}
	if (! in_array ( $_POST ['post'], $GLOBALS ['posts'] )) {
		$errors [] = 'Invalid Post Input';
	}
	return $errors;
}
function validateUpdateInfo() {
	// preparation for validation of email
	global $errors;
	$input ['email'] = filter_input ( INPUT_POST, 'email', FILTER_VALIDATE_EMAIL );
	if (strlen ( $_POST ['fullName'] ) == 0 || strlen ( $_POST ['officeID'] ) == 0 || strlen ( $_POST ['username'] ) == 0 || strlen ( $_POST ['pass'] ) == 0 || strlen ( $_POST ['rePass'] ) == 0) {
		$errors [] = "Some fields are left empty";
	}
	if ($_POST ['pass'] != $_POST ['rePass']) {
		$errors [] = "Passwords doesn't match";
	}
	if (strlen ( $_POST ['pass'] ) < 8) {
		$errors [] = 'Passwords should be at least 8 characters long';
	}
	if (! $input ['email']) {
		$errors [] = 'Please enter a valid email address';
	}
	if (! in_array ( $_POST ['department'], $GLOBALS ['faculties'] )) {
		$errors [] = 'Invalid Department Input';
	}
	if (! in_array ( $_POST ['post'], $GLOBALS ['posts'] )) {
		$errors [] = 'Invalid Post Input';
	}
	return $errors;
}
function showErrors($errors) {
	echo "<table id='errorTable'>";
	echo "<tr><td id='errorHeading' class='alert alert-info' role='alert'>Please Correct These errors : </td></tr>";
	$index = 0;
	$colors = [ 
			"#9c9",
			"#ddd" 
	];
	foreach ( $errors as $error ) {
		echo "<tr><td style='background: $colors[$index];'>$error</td></tr>";
		$index = 1 - $index;
	}
	echo "</table>";
}
function storeUserToDatabase() {
	$person = new \sxc\Models\Person ();
	// filling the data to object person
	$person->setName ( $_POST ['fullName'] );
	$person->setId ( $_POST ['officeID'] );
	$person->setUserName ( $_POST ['username'] );
	$person->setPassword ( $_POST ['pass'] );
	$person->setEmail ( $_POST ['email'] );
	$person->setDepartment ( $_POST ['department'] );
	$person->setPost ( $_POST ['post'] );
	$person->setAuthority ( 'cds' );
	
	// store to the database
	\sxc\Models\DbHelper::addUserToDatabase ( $person );
	viewCDS ();
}
function updateUser() {
	$person = new \sxc\Models\Person ();
	// filling the data to object person
	$person->setName ( $_POST ['fullName'] );
	$person->setId ( $_POST ['officeID'] );
	$person->setUserName ( $_POST ['username'] );
	$person->setPassword ( $_POST ['pass'] );
	$person->setEmail ( $_POST ['email'] );
	$person->setDepartment ( $_POST ['department'] );
	$person->setPost ( $_POST ['post'] );
	$person->setAuthority ( 'cds' );
	
	// store to the database
	\sxc\Models\DbHelper::updateUser ( $person );
	viewCDS ();
}
function viewCDS() {
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
	$person = new \sxc\Models\Person ();
	try {
		$db = new \PDO ( 'mysql:host='.hostname.';dbname='.dbName, dbUsername, dbPassword );
		$db->setAttribute ( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
		$stmt = $db->query ( "select * from users order by id desc" );
		while ( $row = $stmt->fetch () ) {
			if ($row ['authority'] == 'cds') {
				echo "<tr>";
				echo "<td>$i</td>";
				echo "<td>$row[officeId]</td>";
				echo "<td>$row[name]</td>";
				echo "<td>$row[department]</td>";
				echo "<td>$row[post]</td>";
				echo "<td>$row[authority]</td>";
				echo "<td>$row[email]</td>";
				echo "<td><a href='$_SERVER[PHP_SELF]?action=deleteCDS&id=$row[id]'>Delete</a><br />
				  <a href='$_SERVER[PHP_SELF]?action=editCDS&id=$row[id]' >Update</a></td>";
				echo "</tr>";
				++ $i;
			}
		}
		echo "</table>";
		$db = null;
	} catch ( \PDOException $e ) {
		echo "Cannot make connection to the database " . $e->getMessage ();
	}
}
function displayAddDepartmentForm($defaults) {
	echo <<<DISP
	<div class='alert alert-info' role='alert' style='text-align:center'>
      <h3>Add Department Information</h3>
    </div>
	<form name='f1' class = 'loginForm' method='post' actoin='$_SERVER[PHP_SELF]'>
		<table>
			<tr>
				<td>Department Name : </td>
				<td><input type='text' name='dName' value='$defaults[dName]' /></td>
			</tr>
			<tr>
				<td>HOD Name : </td>
				<td><input type='text' name='hName' value='$defaults[hName]' /></td>
			</tr>
			<tr>
				<td>Department Email : </td>
				<td><input type='email' name='dEmail' value='$defaults[dEmail]'/></td>
			</tr>
			<tr>
	        	<td><input type="hidden" name="cdsAddDepartment" value='1'></td>
       	 		<td><button type="submit" class="btn btn-info" style="width:100%;">Add Department</button></td>
        	</tr>			
		</table>
	</form>
DISP;
}
function validateDeparmentInformation() {
	global $errors;
	// sanitize inputs first of all
	$_POST ['dName'] = trim ( $_POST ['dName'] );
	$_POST ['hName'] = trim ( $_POST ['hName'] );
	$_POST ['dEmail'] = trim ( $_POST ['dEmail'] );
	$input ['email'] = filter_input ( INPUT_POST, 'dEmail', FILTER_VALIDATE_EMAIL );
	
	if (strlen ( $_POST ['dName'] ) == 0 || strlen ( $_POST ['hName'] ) == 0 || strlen ( $_POST ['dEmail'] ) == 0) {
		$errors [] = 'None of the fields should be left empty';
	} elseif (! $input ['email']) {
		$errors [] = 'Please Enter a valid Email';
	}
	return $errors;
}
function insertIntoDepartmentTable() {
	$department = new \sxc\Models\Department ();
	$department->setDepartmentName ( $_POST ['dName'] );
	$department->setHodEmail ( $_POST ['dEmail'] );
	$department->setHodName ( $_POST ['hName'] );
	\sxc\Models\DbHelper::insertDepartmentInformation ( $department );
	viewDepartments ();
}
function viewDepartments() {
	$i = 1;
	echo "<table id='viewData'>";
	echo "<tr>";
	echo "<th>S.N</th>";
	echo "<th>Department</th>";
	echo "<th>Head Of Department</th>";
	echo "<th>Email of HOD</th>";
	echo "<th>Operations </th>";
	echo "</tr>";
	$person = new \sxc\Models\Person ();
	try {
		$db = new \PDO ( 'mysql:host='.hostname.';dbname='.dbName, dbUsername, dbPassword );
		$db->setAttribute ( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
		$stmt = $db->query ( "select * from hods order by id desc" );
		while ( $row = $stmt->fetch () ) {
			echo "<tr>";
			echo "<td>$i</td>";
			echo "<td>$row[department]</td>";
			echo "<td>$row[name]</td>";
			echo "<td>$row[email]</td>";
			echo "<td><a href='$_SERVER[PHP_SELF]?action=delete&id=$row[id]'>Delete</a><br />
				<a href='$_SERVER[PHP_SELF]?action=edit&id=$row[id]' >Update</a></td>";
			echo "</tr>";
			++ $i;
		}
		echo "</table>";
		$db = null;
	} catch ( \PDOException $e ) {
		echo "Cannot make connection to the database " . $e->getMessage ();
	}
}
function displayUpdateDepartmentForm($defaults, $identity) {
	echo <<<DISP
	<div class='alert alert-info' role='alert' style='text-align:center'>
      <h3>Update Department Information</h3>
    </div>
	<form name='f1' class = 'loginForm' method='post' actoin='$_SERVER[PHP_SELF]'>
		<table>
			<tr>
				<td>Department Name : </td>
				<td><input type='text' name='dName' value='$defaults[dName]' /></td>
			</tr>
			<tr>
				<td>HOD Name : </td>
				<td><input type='text' name='hName' value='$defaults[hName]' /></td>
			</tr>
			<tr>
				<td>Department Email : </td>
				<td><input type='email' name='dEmail' value='$defaults[dEmail]'/></td>
			</tr>
			<tr>
	        	<td><input type="hidden" name="adminUpdateHOD" value='$identity'></td>
       	 		<td><button type="submit" class="btn btn-info" style="width:100%;">Update CDS</button></td>
        	</tr>
		</table>
	</form>
DISP;
}
function updateDepartment() {
	$department = new \sxc\Models\Department ();
	$department->setDepartmentName ( $_POST ['dName'] );
	$department->setHodName ( $_POST ['hName'] );
	$department->setHodEmail ( $_POST ['dEmail'] );
	$identity = $_POST ['adminUpdateHOD'];
	\sxc\Models\DbHelper::updateDpeartmentInformation ( $department, $identity );
}
function displayData($person) {
	if ($person->getUsername () != null) {
		$fullName = ucwords ( $person->getName () );
		$department = ucwords ( $person->getDepartment () );
		$officeId = ucwords ( $person->getId () );
		$post = ucwords ( $person->getPost () );
		$authority = ucwords ( $person->getAuthority () );
		$email = ucwords ( $person->getEmail () );
		$totalComplains = \sxc\Models\DbHelper::getTotalComplains ( $person->getUsername () );
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

function viewAllComplains($identity){
	$person = new \sxc\Models\Person ();
	try {
		$db = new \PDO ( 'mysql:host='.hostname.';dbname='.dbName, dbUsername, dbPassword );
		$db->setAttribute ( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
		$stmt = $db->prepare ( "select * from complains where complainBy=? order by id desc" );
		$stmt->execute ( array (
				$identity
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
?>