<?php
include '../header.php';
//include "db.php";
echo "<body>";
require '../db/db.php';
$db = new Db();
$current = 0;

//$query = "SELECT * FROM versions ORDER BY ts asc";
$query = "SELECT version FROM versions WHERE ts = (SELECT MAX(ts) FROM versions)";
//$query = "SELECT * FROM versions WHERE version = (SELECT MIN(version) FROM versions)";
$results = $db->mysql->query($query);

if($results->num_rows) {
	$current = $results->fetch_object()->version;
	//while($row = $results->fetch_object()) {
	//	$ts = $row->ts;
	//	$version = $row->version;
	//	$id = $row->id;
	//	echo "<p>$id  $ts  $version</p>";
	//}
}

libxml_use_internal_errors(true);

$xml=simplexml_load_file("db.xml") or die("Error: Cannot create object");

if ($xml === false) {
    echo "Failed loading XML: ";
    foreach(libxml_get_errors() as $error) {
        echo "<br>", $error->message;
    }
    echo "</body>";
    include 'footer.php';
    die();
}

$versions = array();
foreach($xml->version as $d) {
  array_push($versions, (int)$d->version);
}

$highest = max($versions);

if (!isset($_GET["version"])) {
  $desired_version = $highest;
} else {
  $desired_version=(int)htmlspecialchars($_GET["version"]);
}
echo "current version is " . $current . "<br/>";
echo "desired version is " . $desired_version . "<br/>";

if ($current == $desired_version) {
  echo "no changes required to database<br/>";
  echo "</body>";
  include '../footer.php';
  die();
} elseif ($current > $desired_version) {
  rsort($versions);
  echo "downgrading database is not currently supported<br/>";
  echo "</body>";
  include '../footer.php';
  die();
}

sort($versions);
echo "upgrading database from version " . $current . " to " . $desired_version . "<br/>";
$versions = array_unique($versions);


foreach($versions as $v){
  echo '<br/>';
  if ($v <= $current){
    echo "ignoring version $v<br/>";
  } else {
    $res = $xml->xpath("version/version[.=$v]/parent::*");
    //should be exactly one of each version so use [0]
    Upgrade($res[0]);
  }
}

function Upgrade($v){
  global $db;
  echo 'Upgrading to version '; print((int)$v->version); echo'<br/>';
  foreach($v->addtable as $table){
    $sqlstring  = "CREATE TABLE ";
    $sqlstring .= $table->name;
    $sqlstring .= " (";
    $comma = " ";
    foreach($table->addcolumn as $ac){
      $sqlstring .= $comma . $ac->name . " " . $ac->column_def;
      $comma = ", ";
    }
    $sqlstring .= " )";
    //$sqlstring .= ";";
    echo $sqlstring; echo '<br/>';
    $db->mysql->query($sqlstring);
  }
  foreach($v->table as $table){
    UpdateTable($table);
  }
  $sqlstring = "INSERT INTO versions (version) VALUES ($v->version)";
  echo $sqlstring; echo '<br/>';
  $db->mysql->query($sqlstring);
}

function UpdateTable($tableops){
  global $db;
  foreach($tableops->addcolumn as $ac){
    $sqlstring  = "ALTER TABLE ";
    $sqlstring .= $tableops->name . " ";
    $sqlstring .= "ADD COLUMN ";
    $sqlstring .= $ac->name . " " . $ac->column_def;
    //$sqlstring .= ";";
    echo $sqlstring; echo '<br/>';
    $db->mysql->query($sqlstring);
  }
}

echo "</body>";
include '../footer.php';
?>

