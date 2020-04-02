<?php

namespace sxc\Models;
require_once 'configSXC.php';

require 'Person.php';
require 'Department.php';
class DbHelper {
	public static function getUser($userName, $password) {
		$person = new \sxc\Models\Person ();
		try {
			$db = new \PDO ( 'mysql:host='.hostname.';dbname='.dbName, dbUsername, dbPassword );
			$db->setAttribute ( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
			$stmt = $db->prepare ( "select * from users where username=? and password=?" );
			$stmt->execute ( array (
					$userName,
					$password 
			) );
			while ( $row = $stmt->fetch () ) {
				$person->setName ( $row ['name'] );
				$person->setDepartment ( $row ['department'] );
				$person->setId ( $row ['officeId'] );
				$person->setPost ( $row ['post'] );
				$person->setAuthority ( $row ['authority'] );
				$person->setUserName ( $row ['username'] );
				$person->setPassword ( $row ['password'] );
				$person->setEmail ( $row ['email'] );
			}
			$db = null;
			return $person;
		} catch ( \PDOException $e ) {
			echo "Cannot make connection to the database " . $e->getMessage ();
		}
	}
	public static function isNotAUniqueUser($userName) {
		try {
			$db = new \PDO ( 'mysql:host='.hostname.';dbname='.dbName, dbUsername, dbPassword );
			$db->setAttribute ( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
			$stmt = $db->prepare ( "select * from users where username=?" );
			$stmt->execute ( array (
					$userName 
			) );
			if ($stmt->rowCount () == 0) {
				return false;
			} else
				return true;
			$db = null;
		} catch ( \PDOException $e ) {
			echo "Cannot make connection to the database " . $e->getMessage ();
		}
	}
	public static function addUserToDatabase($person) {
		try {
			$db = new \PDO ( 'mysql:host='.hostname.';dbname='.dbName, dbUsername, dbPassword );
			$db->setAttribute ( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
			$stmt = $db->prepare ( "insert into users (name,department,officeId,authority,post,username,password,email) values(?,?,?,?,?,?,?,?)" );
			$stmt->execute ( array (
					$person->getName (),
					$person->getDepartment (),
					$person->getId (),
					$person->getAuthority (),
					$person->getPost (),
					$person->getUsername (),
					$person->getPassword (),
					$person->getEmail () 
			) );
			$db = null;
		} catch ( \PDOException $e ) {
			echo "Cannot make connection to the database " . $e->getMessage ();
		}
	}
	public static function storeComplainToDatabase($person, $complain) {
		try {
			$currentTime = time ();
			$db = new \PDO ( 'mysql:host='.hostname.';dbname='.dbName, dbUsername, dbPassword );
			$db->setAttribute ( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
			$stmt = $db->prepare ( "insert into complains (complainBy,Details,status,complainDate,roomNo,roomName,compNo) values(?,?,?,'$currentTime',?,?,?)" );
			$stmt->execute ( array (
					$person->getUsername (),
					$complain ['details'],
					'pending',
					$complain ['roomNo'],
					$complain ['roomName'],
					$complain ['compNo'] 
			) );
			$db = null;
		} catch ( \PDOException $e ) {
			echo "Cannot make connection to the database " . $e->getMessage ();
		}
	}
	public static function viewUser($identity, $category) {
		$person = new \sxc\Models\Person ();
		try {
			$stmt;
			$db = new \PDO ( 'mysql:host='.hostname.';dbname='.dbName, dbUsername, dbPassword );
			$db->setAttribute ( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
			if ($category == 'username') {
				$stmt = $db->prepare ( "select * from users where username=?" );
			} elseif ($category == 'id') {
				$stmt = $db->prepare ( "select * from users where id=?" );
			}
			$stmt->execute ( array (
					$identity 
			) );
			while ( $row = $stmt->fetch () ) {
				$person->setName ( $row ['name'] );
				$person->setDepartment ( $row ['department'] );
				$person->setId ( $row ['officeId'] );
				$person->setPost ( $row ['post'] );
				$person->setAuthority ( $row ['authority'] );
				$person->setUserName ( $row ['username'] );
				$person->setPassword ( $row ['password'] );
				$person->setEmail ( $row ['email'] );
			}
			$db = null;
			return $person;
		} catch ( \PDOException $e ) {
			echo "Cannot make connection to the database " . $e->getMessage ();
		}
	}
	public static function updateUser($person) {
		try {
			$db = new \PDO ( 'mysql:host='.hostname.';dbname='.dbName, dbUsername, dbPassword );
			$db->setAttribute ( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
			$stmt = $db->prepare ( "update users set name=?,department=?,officeId=?,authority=?,post=?,username=?,password=?,email=? where username = ?" );
			$stmt->execute ( array (
					$person->getName (),
					$person->getDepartment (),
					$person->getId (),
					$person->getAuthority (),
					$person->getPost (),
					$person->getUsername (),
					$person->getPassword (),
					$person->getEmail (),
					$person->getUsername () 
			) );
			$db = null;
		} catch ( \PDOException $e ) {
			echo "Cannot make connection to the database " . $e->getMessage ();
		}
	}
	public static function deleteUser($id) {
		try {
			$db = new \PDO ( 'mysql:host='.hostname.';dbname='.dbName, dbUsername, dbPassword );
			$db->setAttribute ( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
			$stmt = $db->prepare ( "delete from users where id = ?" );
			$stmt->execute ( array (
					$id 
			) );
			$db = null;
		} catch ( \PDOException $e ) {
			echo "Cannot make connection to the database " . $e->getMessage ();
		}
	}
	public static function getTotalComplains($username) {
		try {
			$db = new \PDO ( 'mysql:host='.hostname.';dbname='.dbName, dbUsername, dbPassword );
			$db->setAttribute ( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
			$stmt = $db->prepare ( "select count(*) from complains where complainBy=?" );
			$stmt->execute ( array (
					$username 
			) );
			$db = null;
			return $stmt->fetchColumn ();
		} catch ( \PDOException $e ) {
			echo "Cannot make connection to the database " . $e->getMessage ();
		}
	}
	public static function insertDepartmentInformation($department) {
		try {
			$db = new \PDO ( 'mysql:host='.hostname.';dbname='.dbName, dbUsername, dbPassword );
			$db->setAttribute ( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
			$stmt = $db->prepare ( "insert into hods (name,department,email) values(?,?,?)" );
			$stmt->execute ( array (
					$department->getHodName (),
					$department->getDepartmentName (),
					$department->getHodEmail () 
			) );
			$db = null;
		} catch ( \PDOException $e ) {
			echo "Cannot make connection to the database " . $e->getMessage ();
		}
	}
	public static function getDepartmentObject($id) {
		$department = new \sxc\Models\Department ();
		try {
			$db = new \PDO ( 'mysql:host='.hostname.';dbname='.dbName, dbUsername, dbPassword );
			$db->setAttribute ( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
			$stmt = $db->prepare ( "select * from hods where id=?" );
			$stmt->execute ( array (
					$id 
			) );
			while ( $row = $stmt->fetch () ) {
				$department->setDepartmentName ( $row ['department'] );
				$department->setHodName ( $row ['name'] );
				$department->setHodEmail ( $row ['email'] );
			}
		} catch ( \PDOException $e ) {
			echo "Cannot make connection to the database " . $e->getMessage ();
		}
		return $department;
	}
	public static function updateDpeartmentInformation($department, $identity) {
		try {
			$db = new \PDO ( 'mysql:host='.hostname.';dbname='.dbName, dbUsername, dbPassword );
			$db->setAttribute ( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
			$stmt = $db->prepare ( "update hods set department=?,name=?,email=? where id=?" );
			$stmt->execute ( array (
					$department->getDepartmentName (),
					$department->getHodName (),
					$department->getHodEmail (),
					$identity 
			) );
			$db = null;
		} catch ( \PDOException $e ) {
			echo "Cannot make connection to the database " . $e->getMessage ();
		}
	}
	public static function DeleteDepartment($id) {
		try {
			$db = new \PDO ( 'mysql:host='.hostname.';dbname='.dbName, dbUsername, dbPassword );
			$db->setAttribute ( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
			$stmt = $db->prepare ( "delete from hods where id = ?" );
			$stmt->execute ( array (
					$id 
			) );
			$db = null;
		} catch ( \PDOException $e ) {
			echo "Cannot make connection to the database " . $e->getMessage ();
		}
	}
	public static function getDepartmentLists() {
		$departments = array ();
		try {
			$db = new \PDO ( 'mysql:host='.hostname.';dbname='.dbName, dbUsername, dbPassword );
			$db->setAttribute ( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
			$stmt = $db->query ( "select * from hods order by id desc" );
			while ( $row = $stmt->fetch () ) {
				$departments [] = $row ['department'];
			}
			$db = null;
		} catch ( \PDOException $e ) {
			echo "Cannot make connection to the database " . $e->getMessage ();
		}
		return $departments;
	}
	public static function getAllUSersFromDepartment($department) {
		$users = array ();
		try {
			$db = new \PDO ( 'mysql:host='.hostname.';dbname='.dbName, dbUsername, dbPassword );
			$db->setAttribute ( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
			$stmt = $db->prepare ( "select * from users where department = ?" );
			$stmt->execute ( array (
					$department 
			) );
			while ( $row = $stmt->fetch () ) {
				$users [$row ['name']] = $row ['username'];
			}
			$db = null;
		} catch ( \PDOException $e ) {
			echo "Cannot make connection to the database " . $e->getMessage ();
		}
		return $users;
	}
	public static function insertPost($post) {
		try {
			$db = new \PDO ( 'mysql:host='.hostname.';dbname='.dbName, dbUsername, dbPassword );
			$db->setAttribute ( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
			$stmt = $db->prepare ( "insert into posts (postName) values(?)" );
			$stmt->execute ( array (
					$post 
			) );
			$db = null;
		} catch ( \PDOException $e ) {
			echo "Cannot make connection to the database " . $e->getMessage ();
		}
	}
	public static function isUniquePost($postName) {
		try {
			$db = new \PDO ( 'mysql:host='.hostname.';dbname='.dbName, dbUsername, dbPassword );
			$db->setAttribute ( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
			$stmt = $db->prepare ( "select * from posts where postName=?" );
			$stmt->execute ( array (
					$postName 
			) );
			if ($stmt->rowCount () == 0) {
				return true;
			} else
				return false;
			$db = null;
		} catch ( \PDOException $e ) {
			echo "Cannot make connection to the database " . $e->getMessage ();
		}
	}
	public static function getPostNameById($id) {
		try {
			$postName;
			$db = new \PDO ( 'mysql:host='.hostname.';dbname='.dbName, dbUsername, dbPassword );
			$db->setAttribute ( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
			$stmt = $db->prepare ( "select * from posts where id=?" );
			$stmt->execute ( array (
					$id 
			) );
			while($row = $stmt->fetch()){
				$postName = $row['postName'];
			}
			$db = null;
		} catch ( \PDOException $e ) {
			echo "Cannot make connection to the database " . $e->getMessage ();
		}
		return $postName;
	}
	public static function updatePostNameFromDatabase($postName, $id){
		try {
			$postName;
			$db = new \PDO ( 'mysql:host='.hostname.';dbname='.dbName, dbUsername, dbPassword );
			$db->setAttribute ( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
			$stmt = $db->prepare ( "update posts set postName = ? where id = ?" );
			$stmt->execute ( array (
					$postName,
					$id
			) );
			$db = null;
		} catch ( \PDOException $e ) {
			echo "Cannot make connection to the database " . $e->getMessage ();
		}
	}
	public static function deletePostFromDatabase($id){
		try {
			$postName;
			$db = new \PDO ( 'mysql:host='.hostname.';dbname='.dbName, dbUsername, dbPassword );
			$db->setAttribute ( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
			$stmt = $db->prepare ( "delete from posts where id=?" );
			$stmt->execute ( array (
					$id
			) );
			$db = null;
		} catch ( \PDOException $e ) {
			echo "Cannot make connection to the database " . $e->getMessage ();
		}
	}
	public static function getPostListFromDatabase(){
		$posts = array ();
		try {
			$db = new \PDO ( 'mysql:host='.hostname.';dbname='.dbName, dbUsername, dbPassword );
			$db->setAttribute ( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
			$stmt = $db->query ( "select * from posts order by id desc" );
			while ( $row = $stmt->fetch () ) {
				$posts [] = $row ['postName'];
			}
			$db = null;
		} catch ( \PDOException $e ) {
			echo "Cannot make connection to the database " . $e->getMessage ();
		}
		return $posts;
	}
	public static function getComplainerUserNameById($id){
	    try {
	        $complainDetail;
	        $db = new \PDO ( 'mysql:host='.hostname.';dbname='.dbName, dbUsername, dbPassword );
	        $db->setAttribute ( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
	        $stmt = $db->prepare ( "select * from complains where id=?" );
	        $stmt->execute ( array (
	            $id
	        ) );
	        while($row = $stmt->fetch()){
	            $complainDetail['complainBy'] = $row['complainBy'];
	            $complainDetail['details'] = $row['Details'];
	            $complainDetail['status'] = $row['status'];
	            $complainDetail['roomNo'] = $row['roomNo'];
	            $complainDetail['roomName'] = $row['roomName'];
	            $complainDetail['compNo'] = $row['compNo'];
	        }
	        $db = null;
	    } catch ( \PDOException $e ) {
	        echo "Cannot make connection to the database " . $e->getMessage ();
	    }
	    return $complainDetail;
	}
	
	public static function getHodEmail($department){
	    try {
	        $email;
	        $db = new \PDO ( 'mysql:host='.hostname.';dbname='.dbName, dbUsername, dbPassword );
	        $db->setAttribute ( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
	        $stmt = $db->prepare ( "select email from hods where department=?" );
	        $stmt->execute ( array (
	            $department
	        ) );
	        while($row = $stmt->fetch()){
	            $email = $row['email'];
	        }
	        $db = null;
	    } catch ( \PDOException $e ) {
	        echo "Cannot make connection to the database " . $e->getMessage ();
	    }
	    return $email;
	}
	public static function getCDSList(){
	    try {
	        $cdsList=array();
	        $db = new \PDO ( 'mysql:host='.hostname.';dbname='.dbName, dbUsername, dbPassword );
	        $db->setAttribute ( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
	        $stmt = $db->prepare ( "select * from users where authority=?" );
	        $stmt->execute ( array (
	            "cds"
	        ) );
	        while($row = $stmt->fetch()){
	           $person = new \sxc\Models\Person();
	           $person->setName($row['name']);
	           $person->setDepartment($row['department']);
	           $person->setId($row['officeId']);
	           $person->setAuthority($row['authority']);
	           $person->setPost($row['post']);
	           $person->setUserName($row['username']);
	           $person->setPassword($row['password']);
	           $person->setEmail($row['email']);
	           $cdsList[] = $person;
	        }
	        $db = null;
	    } catch ( \PDOException $e ) {
	        echo "Cannot make connection to the database " . $e->getMessage ();
	    }
	    return $cdsList;
	}
}
    
?>
