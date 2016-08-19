<?php
//------------Forms created by Nikolai Vitsyn 2004/01/23
require_once(dirname(__FILE__).'/../../globals.php');
include_once($GLOBALS["srcdir"]."/api.inc");
function bronchitis_report( $pid, $encounter, $cols, $id) {
$count = 0;
$data = formFetch("form_bronchitis", $id);
if ($data) {
print "<table style='display:block;width:100%;'><tr>";
foreach($data as $key => $value) {
if ($key == "id" || $key == "pid" || $key == "user" || $key == "groupname" || $key == "authorized" || $key == "activity" || $key == "date" || $value == "" || $value == "0000-00-00 00:00:00") {
	continue;
}
if ($value == "on") {
$value = "yes";
}
$key=ucwords(str_replace("_"," ",$key));
print "<td style='width:".(100/($cols>1?$cols:2))."%'><span class=bold>$key: </span><span class=text>$value</span></td>";
$count++;
if ($count == $cols) {
$count = 0;
print "</tr><tr>\n";
}
}
}
print "</tr></table>";
}
?> 
