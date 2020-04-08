<?php

namespace sxc;

require 'Views/Sections.php';

\sxc\Views\Sections::generateHeader ( "CDS Page" );

function displayCDSMenu(){
	echo <<<HTMLBLOCK
<h2 id="index">SXC Multimedia resource monitoring system</h2>
<nav id='navigation'>
	<ul>
		<li><a href='cdsoperation.php?action=addUser'>Add User</a></li>
		<li><a href='cdsoperation.php?action=viewUsers'>View Users</a></li>
		<li><a href='cdsoperation.php?action=complains'>Complains</a></li>
	</ul>
</nav>
HTMLBLOCK;
}

if(isset($_SESSION['userLoggedIn']) && ($_SESSION['userLoggedIn']->getAuthority() == 'cds' || $_SESSION['userLoggedIn']->getAuthority() == 'admin' )){
	displayCDSMenu();
}else{
	if ($_SERVER ['REQUEST_METHOD'] == 'POST') {
		// sanitize input first
		$_POST ['username'] = trim ( $_POST ['username'] );
		$_POST ['password'] = trim ( $_POST ['password'] );
		$operator = \sxc\Models\DbHelper::getUser ( $_POST ['username'], $_POST ['password'] );
		if ($operator->getId () != null && ($operator->getAuthority () == 'admin' || $operator->getAuthority () == 'cds' || $operator->getAuthority () == 'superUser')) {
			$_SESSION ['userLoggedIn'] = $operator;
			header("Refresh:0");
			displayCDSMenu();
		} else {
			echo "<div class='alert alert-danger' role='alert'>
           Invalid Login Information !!!
          </div>";
			\sxc\Views\Sections::displayLoginForm ( 'login',"CDS Login" );
		}
	} else {
		\sxc\Views\Sections::displayLoginForm ( 'login', "CDS Login" );
	}
	
}
?>


<?php
\sxc\Views\Sections::generateFooter ();
?>
