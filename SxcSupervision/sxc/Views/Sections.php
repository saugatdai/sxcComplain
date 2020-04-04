<?php

namespace sxc\Views;

require 'Models/DbHelper.php';
class Sections {
	public static function generateHeader(string $pageTitle) {
		session_start ();
		date_default_timezone_set ( 'Asia/Kathmandu' );
		echo <<<DISP
        <!DOCTYPE html>
        <html>
            <head>
                <meta charset = 'utf-8'>
                <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
                <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
                <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
                <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
                <link rel='stylesheet' type='text/css' href='styles/style.css' />
                <title>$pageTitle</title>
            </head>
            <body>
                		
DISP;
		echo "<div class='container-fluid'>";
		if (isset ( $_GET ['action'] )) {
			if ($_GET ['action'] == 'logout') {
				unset ( $_SESSION ['userLoggedIn'] );
				header ( "Refresh:0; url=index.php" );
			}
		}
		if (isset ( $_SESSION ['userLoggedIn'] )) {
			echo <<<DISP
					<nav id='saugatNav'>
						<ul>
	                        <li><a href='index.php'>Home</a></li>
                        	<li><a href="complain.php?action=viewMyComplains">My Complains</a></li>
DISP;
			if ($_SESSION ['userLoggedIn']->getAuthority () == 'admin' || $_SESSION ['userLoggedIn']->getAuthority () == 'cds') {
				echo <<< navBar
						<li><a href='cdsoperation.php?action=addUser'>Add User</a></li>
						<li><a href='cdsoperation.php?action=viewUsers'>View Users</a></li>
						<li><a href='cdsoperation.php?action=complains'>All Complains</a></li>
                        <li><a href='cdsoperation.php?action=viewPendingComplains'>Pending Complains</a></li>
navBar;
			}
			echo "<li><a href='$_SERVER[PHP_SELF]?action=logout'>Logout</a> </li></ul></nav>";
		}
	}
	public static function generateFooter() {
		echo <<<DISP
            </div>
            <footer class="fixed-bottom">
	           <p> Powered by department of computer science </p>
DISP;
		$status = \sxc\Views\Sections::watchManStatus ();
		if (isset ( $_SESSION ['userLoggedIn'] ) && ($_SESSION ['userLoggedIn']->getAuthority () == 'admin' || $_SESSION ['userLoggedIn']->getAuthority () == 'cds')) {
			echo " Server Status : ";
			if ($status == 'running') {
				echo "<span style='color:#fff'>running</span>";
			} elseif ($status = "notRunning") {
				echo "<span style='color:#f00'>not running </span><span> please run for receiving popup email notifications</span>";
			}
		}
		echo <<<DISP2
				</footer>
			</body>
		</html>
DISP2;
	}
	public static function displayLoginForm($formName, $topic = "Please Login") {
		echo <<<DISP
          <section class='loginForm'>
              <form name='$formName' method='post' action="$_SERVER[PHP_SELF]">
                  <table>
                      <tr>
                          <td colspan='2' align='center' style="font-weight:bold;font-size:20px;">$topic</td>
                      </tr>
                      <tr>
                          <td><label for="user">Username : </label></td>
                          <td><input type='text' name='username' id='user' required/></td>
                      </tr>
                      <tr>
                          <td><label for="pass">Password : </label></td>
                          <td><input type='password' name='password' id="pass" required/></td>
                      </tr>
                      <tr>
                          <td></td>
                          <td><button type="submit" class="btn btn-info" style="width:100%;">Login</button></td>
                      </tr>
                  </table>
              </form>
          </section>
DISP;
	}
	public static function generateSelectMenuSameValue($arrays, $default) {
		foreach ( $arrays as $item ) {
			echo "<Option value ='$item' ";
			if ($item == $default) {
				echo "selected='selected'";
			}
			echo ">$item</option>";
		}
	}
	public function watchManStatus() {
		$address = "localhost";
		$port = 4309;
		$socket = @socket_create ( AF_INET, SOCK_STREAM, getprotobyname ( 'tcp' ) );
		if (! $socket) {
			$message = "notRunning";
		}
		
		if (! @socket_connect ( $socket, $address, $port )) {
			$message = "notRunning";
		} else {
			$message = "status\n\r";
			$len = strlen ( $message );
			$status = socket_sendto ( $socket, $message, $len, MSG_EOF, $address, $port );
			if ($status !== FALSE) {
				$message = '';
				$next = '';
				while ( $next = socket_read ( $socket, 4096 ) ) {
					$message .= $next;
				}
			} else {
				echo "Failed";
			}
		}
		
		socket_close ( $socket );
		return $message;
	}
}

	
