<?php

namespace sxc\Models;

class Person {
	private $name, $department, $officeId, $authority, $post, $username, $password, $email;
	
	// Getters and setters
	public function getEmail() {
		return $this->email;
	}
	public function getUsername() {
		return $this->username;
	}
	public function getPassword() {
		return $this->password;
	}
	public function getName() {
		return $this->name;
	}
	public function getDepartment() {
		return $this->department;
	}
	public function getId() {
		return $this->officeId;
	}
	public function getPost() {
		return $this->post;
	}
	public function getAuthority() {
		return $this->authority;
	}
	public function setEmail($email){
		$this->email = $email;
	}
	public function setUserName($username) {
		$this->username = $username;
	}
	public function setPassword($password) {
		$this->password = $password;
	}
	public function setName($name) {
		$this->name = $name;
	}
	public function setDepartment($department) {
		$this->department = $department;
	}
	public function setId($id) {
		$this->officeId = $id;
	}
	public function setPost($post) {
		$this->post = $post;
	}
	public function setAuthority($authority) {
		$this->authority = $authority;
	}
}
