<?php

require_once('dbconfig.php');

class USER{	
	private $conn;
	private $inactive = 1200; 
	private $bindParamArr = array();
	/** @var bool|null */
	private $userTableAdminExtrasCache = null;
	
	public function getConnection() {
        return $this->conn;
    }
	

	public function __construct(){
		$database = new Database();
		$this->conn = $database->dbConnection();
    }

	public function runQuery($sql){
		$stmt = $this->conn->prepare($sql);
		return $stmt;
	}

	public function getdatetime(){ //R69
		date_default_timezone_set('Asia/Colombo');
		$datewithtime = date("Y-m-d H:i:s");
		$dateonly = date("Y-m-d");
		$textdate = date('Y F d l');
		return array("0"=>$datewithtime,"1"=>$dateonly,"2"=>$textdate);
	}

	public function userTableAdminExtrasAvailable() {
		if ($this->userTableAdminExtrasCache !== null) {
			return $this->userTableAdminExtrasCache;
		}
		try {
			$st = $this->conn->query("SHOW COLUMNS FROM `user_table` LIKE 'admin_role'");
			$this->userTableAdminExtrasCache = ($st && $st->rowCount() > 0);
		} catch (PDOException $e) {
			$this->userTableAdminExtrasCache = false;
		}
		return $this->userTableAdminExtrasCache;
	}

	/**
	 * @param array $opts Optional keys: role (administrator|editor), city_country, admin_status (0|1), mobile (overrides $uMobile when extras exist)
	 */
	public function adminRegister($uFirstName, $uLastName, $uMail, $uPass, $uMobile, $uID = 0, array $opts = array()) {
		try {
			$extras = $this->userTableAdminExtrasAvailable();
			$role = strtolower(trim((string) (isset($opts['role']) ? $opts['role'] : 'administrator')));
			if ($role !== 'editor') {
				$role = 'administrator';
			}
			$city = substr(trim((string) (isset($opts['city_country']) ? $opts['city_country'] : '')), 0, 100);
			$adminStatus = array_key_exists('admin_status', $opts) ? ((int) (bool) $opts['admin_status']) : 1;
			$profilePic = substr(trim((string) (isset($opts['profile_pic']) ? $opts['profile_pic'] : 'default.jpg')), 0, 255);
			if ($profilePic === '') {
				$profilePic = 'default.jpg';
			}
			$mob = substr(trim((string) (isset($opts['mobile']) ? $opts['mobile'] : $uMobile)), 0, 20);

			$uPass = (string) $uPass;
			$hashPass = null;
			if ($uPass !== '') {
				$hashPass = "pass" . password_hash($uPass, PASSWORD_DEFAULT);
			}

			if ($uID == 0 && $hashPass === null) {
				return "Password is required for a new admin.";
			}

			if (!$extras) {
				if ($uID == 0) {
					$stmt = $this->conn->prepare("INSERT INTO user_table (first_name,last_name,login_email,`password`,mobile_number) 
						VALUES(:uFirstName, :uLastName, :uMail, :uPass, :uMobile)");
					$returnString = "Successfully Added a new Admin";
				} else {
					if ($uPass === '') {
						$stmt = $this->conn->prepare("UPDATE user_table SET first_name=:uFirstName, last_name=:uLastName, login_email=:uMail, mobile_number=:uMobile 
							WHERE `id`=:uID");
						$stmt->bindparam(":uID", $uID);
						$stmt->bindparam(":uFirstName", $uFirstName);
						$stmt->bindparam(":uLastName", $uLastName);
						$stmt->bindparam(":uMail", $uMail);
						$stmt->bindparam(":uMobile", $mob);
						$stmt->execute();
						return "Successfully Edited Admin";
					}
					$stmt = $this->conn->prepare("UPDATE user_table SET first_name=:uFirstName, last_name=:uLastName, login_email=:uMail, `password`=:uPass, mobile_number=:uMobile 
						WHERE `id`=:uID");
					$stmt->bindparam(":uID", $uID);
					$returnString = "Successfully Edited Admin";
				}
				if ($uID == 0 || $uPass !== '') {
					$stmt->bindparam(":uFirstName", $uFirstName);
					$stmt->bindparam(":uLastName", $uLastName);
					$stmt->bindparam(":uMail", $uMail);
					$stmt->bindparam(":uPass", $hashPass);
					$stmt->bindparam(":uMobile", $mob);
					$stmt->execute();
				}
				return isset($returnString) ? $returnString : "Successfully Edited Admin";
			}

			if ($uID == 0) {
				$stmt = $this->conn->prepare("INSERT INTO user_table (first_name,last_name,login_email,`password`,mobile_number,admin_role,city_country,admin_status,profile_pic) 
					VALUES(:uFirstName, :uLastName, :uMail, :uPass, :uMobile, :role, :city, :ast, :pic)");
				$stmt->bindparam(":role", $role);
				$stmt->bindparam(":city", $city);
				$stmt->bindparam(":ast", $adminStatus, PDO::PARAM_INT);
				$stmt->bindparam(":pic", $profilePic);
				$returnString = "Successfully Added a new Admin";
			} else {
				if ($hashPass !== null) {
					$stmt = $this->conn->prepare("UPDATE user_table SET first_name=:uFirstName, last_name=:uLastName, login_email=:uMail, `password`=:uPass, mobile_number=:uMobile, admin_role=:role, city_country=:city, admin_status=:ast WHERE `id`=:uID");
					$stmt->bindparam(":uPass", $hashPass);
				} else {
					$stmt = $this->conn->prepare("UPDATE user_table SET first_name=:uFirstName, last_name=:uLastName, login_email=:uMail, mobile_number=:uMobile, admin_role=:role, city_country=:city, admin_status=:ast WHERE `id`=:uID");
				}
				$stmt->bindparam(":uID", $uID);
				$stmt->bindparam(":role", $role);
				$stmt->bindparam(":city", $city);
				$stmt->bindparam(":ast", $adminStatus, PDO::PARAM_INT);
				$returnString = "Successfully Edited Admin";
			}
			$stmt->bindparam(":uFirstName", $uFirstName);
			$stmt->bindparam(":uLastName", $uLastName);
			$stmt->bindparam(":uMail", $uMail);
			$stmt->bindparam(":uMobile", $mob);
			$stmt->execute();
			return $returnString;
		} catch (PDOException $e) {
			return "Database error: " . $e->getMessage();
		}
	}

	public function doLogin($umail,$upass){
	    
		try{
			$sql = "SELECT `id`,`password` FROM `user_table` WHERE `delete_status` = '0' AND `login_email`=:umail ";
			if ($this->userTableAdminExtrasAvailable()) {
				$sql .= " AND `admin_status` = 1 ";
			}
			$stmt = $this->conn->prepare($sql);
			$stmt->execute(array(':umail'=>$umail));
			$userRow=$stmt->fetch(PDO::FETCH_ASSOC);
			if($stmt->rowCount() == 1){
				$storedHash = $userRow['password'];
				// Some installs prefix the hash with "pass". Support both formats.
				if (strpos($storedHash, 'pass') === 0) {
					$storedHash = substr($storedHash, 4);
				}
				if(password_verify($upass, $storedHash)){
					$_SESSION['session_tourism'] = $userRow['id'];
					$_SESSION['timeout']=time();
					 session_write_close();
					return true;
				}else{
					return false;
				}
			}
		}catch(PDOException $e){
			echo $e->getMessage();
		}
	}

	public function checkTimeout(){
	    if (defined('PUBLIC_PAGE') && PUBLIC_PAGE === true) {
        return true;
    }
		if (!isset($_SESSION['timeout'])) {
			$_SESSION['timeout'] = time();
			return true;
		}
		$session_life = time() - $_SESSION['timeout'];
		if($session_life < $this->inactive) {
			$_SESSION['timeout']=time();
			return true;
		} else {
			return false;
		}
	}
	
	public function is_loggedin($session="session_tourism"){
	    if (defined('PUBLIC_PAGE') && PUBLIC_PAGE === true) {
        return true;
    }

		if(isset($_SESSION[$session])){
			return true;
		} else {
			return false;
		}
	}

	public function sessionUser($session="session_tourism"){
		if(isset($_SESSION[$session])){
			$session_tourism = $_SESSION[$session];
			return $session_tourism;
		}
	}

	public function redirect($url){
		header("Location: $url");
		exit;
	}

	public function doLogout($returnPage="", $session="session_tourism"){
		if (session_status() === PHP_SESSION_ACTIVE) {
			session_destroy();
		}
		if (isset($_SESSION) && is_array($_SESSION)) {
			unset($_SESSION[$session]);
		}

		
		if (trim($returnPage) === "") {
        $returnPage = "index.php"; 
        }
        echo "<script>
        localStorage.removeItem('admin_session');
        localStorage.removeItem('session_time');
        window.location.href = '$returnPage';
       </script>";
       exit;
    
		$this->redirect($returnPage);
	}

	public function doLogout2($returnPage="", $session="session_tourism"){
		if (session_status() === PHP_SESSION_ACTIVE) {
			session_destroy();
		}
		if (isset($_SESSION) && is_array($_SESSION)) {
			unset($_SESSION[$session]);
		}
		$this->redirect($returnPage);
	}

	function generateRandomString($length = 10) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, ($charactersLength-1))];
		}
		return $randomString;
	}

	public function fetchAll($colsArr, $tablesArr, $condsArr, $orderBy="", $other="") {
		$this->bindParamArr = array();
		$query = $this->createSqlFields($colsArr);
		$query = $this->createSqlFrom($query, $tablesArr);
		$query = $this->createWhereClause($query, $condsArr);
		$query .= ($other=="") ? "" : " AND $other";
		$query .= ($orderBy=="") ? "" : " ORDER BY $orderBy";
		$stmt = $this->conn->prepare($query);
		$stmt->execute($this->bindParamArr);
		$dataRow = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return($dataRow);
	}

	public function insertTable($tableName, $colArr, $lastInsertID=false) {
		$this->bindParamArr = array();
		$query = "INSERT INTO $tableName (";
		foreach ( $colArr as $key=>$value ) {
			$query .= "$key,";
		}
		$query = substr($query, 0, -1) . ") VALUES(";
		foreach ( $colArr as $key=>$value ) {
			$query .= ":$key,";
			$this->bindParamArr[":$key"] = $value;
		}
		$query = substr($query, 0, -1) . ")";
		$stmt = $this->conn->prepare($query);
		if ( $stmt->execute($this->bindParamArr) ) {
			return ($lastInsertID==false) ? true : $this->conn->lastInsertId();
		} else {
			return false;
		}
	}

	public function updateTable($tableName, $colArr, $whArr) {
		$this->bindParamArr = array();
		$query = "UPDATE `$tableName` SET ";
		foreach ($colArr as $key=>$value) {
			$query .= "`$key`=:$key,"; 
			$this->bindParamArr[":$key"] = $value;
		}
		$query = substr($query, 0, -1);
		$stmt = $this->conn->prepare($this->createWhereClause($query, $whArr));
		if ( $stmt->execute($this->bindParamArr) ) {
			return true;
		} else {
			return false;
		}
	}

	public function deleteTableRow($tableName, $whArr) {
		$this->bindParamArr = array();
		$query = "DELETE FROM $tableName";
		$query = $this->createWhereClause($query, $whArr);
		$stmt = $this->conn->prepare($query);
		$stmt->execute($this->bindParamArr);
		return true;
	}

	public function confirmDeleteModal($ID, $Name, $Email, $header, $url) {
		$html = "
			<script>
                $(function(){
                    $('#confirmDeleteModal').modal('show');
                });
            </script>
			<div class='modal fade' id='confirmDeleteModal' data-backdrop='static' tabindex='-1' role='dialog' aria-labelledby='staticBackdropLabel' aria-hidden='true' style='margin-top:200px'>
				<div class='modal-dialog' role='document'>
					<div class='modal-content'>
						<div class='modal-header'>
							<h5 class='modal-title' id='staticBackdropLabel'>$header</h5>
						</div>
						<div class='modal-body'>
						<form action='' class='text-center' method='post'>
								Name : $Name<br>
								Email : $Email<br>
								<input type='hidden' name='deleteNameID' value='$ID'>
							<br>
							<input type='submit' class='btn btn-danger btn-sm' name='confirmDeleteSubmit' value='Delete'>
							<button class='btn btn-sm btn-secondary' type='button' onclick=location.href='./$url'>Cancel</button>
						</form>
						</div>
					</div>
				</div>
			</div>
		";
		return $html;
	}

	public function CountRows($tablename,$whArr="") {
		$this->bindParamArr = array();
		$query = "SELECT * FROM $tablename";
		if($whArr != "")	{
			$query = $this->createWhereClause($query, $whArr);
		} 
		$stmt = $this->conn->prepare($query);
		$stmt->execute($this->bindParamArr);
		$count = $stmt->rowCount();
		return($count);
	}

	public function GetLastRecord($tblname,$whcol,$whval,$whcol2,$whval2,$orderby) {
		$stmt = $this->conn->prepare("SELECT * FROM $tblname WHERE $whcol='$whval' AND $whcol2 = '$whval2' ORDER BY $orderby DESC LIMIT 1");
		$stmt->execute();
		$userRowpro = $stmt->fetchAll(PDO::FETCH_ASSOC);
		foreach ($userRowpro as $pn) {
			$last_purid = $pn['purchase_id'];//0
			$last_prid = $pn['purchase_billno'];//1
			$ts_uq_id = $pn['ts_uq_id'];//2
		}
		return array($last_purid,$last_prid,$ts_uq_id);
	}	

	public function IsExist($tablename,$col,$value) {
		$sql = "SELECT COUNT(*) AS num FROM $tablename WHERE $col = :value";
		$stmt = $this->conn->prepare($sql);
		$stmt->bindValue(':value', $value);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($row['num'] > 0){
			return true;
		} else {
			return false;
		}
	}

	public function MaxValue($tblname, $FldName, $whArr=""){
		$this->bindParamArr = array();
		$query = "SELECT MAX($FldName) AS FldName FROM $tblname";
		$stmt = $this->conn->prepare($this->createWhereClause($query, $whArr));
		$stmt->execute($this->bindParamArr);
		$rsn=$stmt->fetchall(PDO::FETCH_ASSOC);
		foreach ($rsn as $rowde){
			$Field   = $rowde['FldName'];
		}
		return $Field;
	}

	public function MinValue($tblname, $FldName, $whArr=""){
		$this->bindParamArr = array();
		$query = "SELECT MIN($FldName) AS FldName FROM $tblname";
		$stmt = $this->conn->prepare($this->createWhereClause($query, $whArr));
		$stmt->execute($this->bindParamArr);
		$rsn=$stmt->fetchall(PDO::FETCH_ASSOC);
		foreach ($rsn as $rowde){
			$Field   = $rowde['FldName'];
		}
		return $Field;
	}

	public function GetLastID($table){
		$stmt = $this->conn->prepare("SELECT * FROM $table ");
		$stmt->execute();
		$count = $stmt->rowCount();
		return($count);
	}

	private function createSqlFields($colsArr) {
		if ( $colsArr == "" ) {
			$query = "SELECT *";
		} else {
			$query = "SELECT ";
			foreach ( $colsArr as $value ) {
				$query .= "$value,";
			}
			$query = substr($query, 0, -1);
		}
		return $query;
	}

	private function createSqlFrom($query, $tablesArr) {
		$query .= " FROM ";
		foreach ( $tablesArr as $value ) {
			$query .= "$value,";
		}
		$query = substr($query, 0, -1);
		return $query;
	}

	private function createWhereClause($query, $condsArr) {
		if ( $condsArr != "" ) {
			$query .= " WHERE";
			foreach ( $condsArr as $key=>$value ) {
				$query .= " `$key`=:$key"."x"." AND";
				$this->bindParamArr[":$key"."x"] = $value;
			}
			$query = substr($query, 0, -3);
		} 
		return $query;
	}

	//sub Sring
	function Before($specialChar, $inthat) {
        return substr($inthat, 0, strpos($inthat, $specialChar));
	}

	function After($specialChar, $inthat){
        if (!is_bool(strpos($inthat, $specialChar)))
        return substr($inthat, strpos($inthat,$specialChar)+strlen($specialChar));
	}

}