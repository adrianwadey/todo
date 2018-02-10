<?php

// my database class
	include 'db_ID.php';


class Db {

	public $mysql;

	function __construct() {
		$dbid = new ID();
		$this->mysql = new mysqli($dbid->url, $dbid->user, $dbid->password, $dbid->database) or die("problem");
	}

//	function delete_by_id($id) {
//		$query = "DELETE from todo WHERE id = $id";
//		$result = $this->mysql->query($query) or die("There was a problem");
//
//		if($result) return 'yay!';
//	}

	function update_by_id($id, $description) {
		$query = "UPDATE todo
		         SET description = ?
				 WHERE id = ?
				 LIMIT 1";

		 if($stmt = $this->mysql->prepare($query)) {
		 	$stmt->bind_param('si', $description, $id);
			$stmt->execute();
			return "good job!";
		 }
	}

	function mark_complete_by_id($id) {
		$query = "UPDATE todo
				SET completed = TRUE, whencompleted = NOW()
				WHERE id = ?
				LIMIT 1";

		 if($stmt = $this->mysql->prepare($query)) {
		 	$stmt->bind_param('i', $id);
			$stmt->execute();
			return "completed";
		 }
	}
}
 // end class


