<?php

namespace sxc;

require 'Views/Sections.php';
require_once 'configSXC.php';

\sxc\Views\Sections::generateHeader ( "CDS Page" );
function displayAdminMenu() {
	echo <<<HTMLBLOCK
<h2 id="index">SXC Multimedia resource monitoring system</h2>
<nav id='navigation'>
	<ul>
		<li><a href='adminOperation.php?action=addCDS'>Add CDS</a></li>
		<li><a href='adminOperation.php?action=viewCDS'>View CDS</a></li>
		<li><a href='adminOperation.php?action=addDepartment'>Add Department</a></li>
		<li><a href='adminOperation.php?action=viewDepartments'>View Departments</a></li>
		<li><a href='adminOperation.php?action=departmentComplains'>Department Complains</a></li>
		<li><a href='adminOperation.php?action=addPosts'>Add Post</a></li>
		<li><a href='adminOperation.php?action=viewPosts'>View Posts</a></li>
	</ul>
</nav>
HTMLBLOCK;
}

if (isset ( $_SESSION ['userLoggedIn'] ) && $_SESSION['userLoggedIn']->getAuthority()=='admin') {
	displayAdminMenu ();
} else {
	if ($_SERVER ['REQUEST_METHOD'] == 'POST') {
		// sanitize input first
		$_POST ['username'] = trim ( $_POST ['username'] );
		$_POST ['password'] = trim ( $_POST ['password'] );
		$operator = \sxc\Models\DbHelper::getUser ( $_POST ['username'], $_POST ['password'] );
		if ($operator->getId () != null && ($operator->getAuthority () == 'admin' || $operator->getAuthority () == 'superUser')) {
			$_SESSION ['userLoggedIn'] = $operator;
			header("Refresh:0");
			displayAdminMenu ();
		} else {
			echo "<div class='alert alert-danger' role='alert'>
           Invalid Login Information !!!
          </div>";
			\sxc\Views\Sections::displayLoginForm ( 'login', "Admin Login" );
		}
	} else {
		\sxc\Views\Sections::displayLoginForm ( 'login', "Admin Login" );
	}
}
?>


<?php
\sxc\Views\Sections::generateFooter ();
?>
