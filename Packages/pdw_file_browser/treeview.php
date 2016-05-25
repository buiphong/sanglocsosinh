<?php

require_once('functions.php');

// Rootname is name of uploadfolder
$rootname = array_pop((explode("/", trim($uploadpath,"/"))));

// Get folders from uploadpath and create a list
$dirs = getDirTree(STARTINGPATH, false);
if (isset($_SESSION['pwd_las_part']) && STARTINGPATH == $_SESSION['pwd_last_part']) {
	$class = 'selected';
	$selected = $_SESSION['pwd_las_part'];
}
else
{
	$class = '';
	$selected = '';
}
//Print treeview to screen
echo '<ul class="treeview">
            <li class="'.$class.'"><a class="root" href="'.$uploadpath.'">'.$rootname."</a>\n";
echo 		renderTree($dirs, $uploadpath, $selected);
echo "            </li>
       </ul>\n";

?>