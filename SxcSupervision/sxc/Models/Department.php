<?php
namespace sxc\Models;
class Department{
	private $departmentName;
	private $hodName;
	private $hodEmail;
	
	
	public function setDepartmentName($departmentName){
		$this->departmentName = $departmentName;
	}
	public function setHodName($hodName){
		$this->hodName = $hodName;
	}
	public function setHodEmail($hodEmail){
		$this->hodEmail = $hodEmail;
	}
	
	public function getDepartmentName(){
		return $this->departmentName;
	}
	public function getHodName(){
		return $this->hodName;
	}
	public function getHodEmail(){
		return $this->hodEmail;
	}
}

?>