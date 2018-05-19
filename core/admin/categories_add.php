<?php
############################################################
# PODCAST GENERATOR
#
# Created by Alberto Betella
# http://www.podcastgenerator.net
# 
# This is Free Software released under the GNU/GPL License.
############################################################

########### Security code, avoids cross-site scripting (Register Globals ON)
if (isset($_REQUEST['GLOBALS']) OR isset($_REQUEST['absoluteurl']) OR isset($_REQUEST['amilogged']) OR isset($_REQUEST['theme_path'])) { exit; } 
########### End

### Check if user is logged ###
	if (!isUserLogged()) { exit; }
###

$PG_mainbody .= '<h3>'._("Add a category").'</h3>';

//include ("$absoluteurl"."components/xmlparser/loadparser.php");
//include ("$absoluteurl"."core/admin/readXMLcategories.php");

// define variables
$arrdesc = NULL;
$arrid = NULL;
$isduplicated = NULL;

$n = 0; // counter

$add = $_POST['addcategory']; // variable
$desc = $_POST['catdesc'];

// Depurate input
function cleanup($content){
	$content = stripslashes($content);
	$content = htmlspecialchars($content);
	$content = depurateContent($content);
	return $content;
}

$add = cleanup($add);
$desc = cleanup($desc);

if ($add != NULL and $add != "all") { /// 000


	// create unique and depurated id from the description (using the function renamefilestrict)
	
	$id = avoidXSS(renamefilestrict($add)); //deletes also accents	
	$id = ampersandRemove($id); //ampersand create issues
	
	if (strlen($id) < 3) {
	$suffix = random_str(5);
	$id = $id.$suffix;
	}

	$parser = simplexml_load_file($absoluteurl."categories.xml",'SimpleXMLElement',LIBXML_NOCDATA);

	//parse
//	if (isset($parser->document->category)) {
		foreach($parser->category as $singlecategory)
		{
			// echo $singlecategory->id[0]->tagData."<br>";
			// echo $singlecategory->description[0]->tagData;
			// echo "<br><br>";

			if ($id != $singlecategory->id[0] AND $add !=$singlecategory->name[0]) { // if the id of the new category is different from the ids already present in the XML file and if the description is different (e.g. the description is compared cause the id is generated with random characters in case of conversion from japanese, corean etc...

			// put into the array 
				$arrdesc[$n] = array(
					'name' => htmlspecialchars($singlecategory->name[0]),
					'desc' => htmlspecialchars($singlecategory->description[0])
				); // Encode special characters
				$arrid[$n] = $singlecategory->id[0];

			}
			else { // if ID already present in XML

				$isduplicated = TRUE; // assign duplicated label

			}


			$n++; //increment count
		}
//	}


	if ($isduplicated != TRUE) { // 001 if new category doesn't exist yet
	$arrdesc[$id] = array(
		'name' => $add, // Name
		'desc' => $desc //Description
	);

	$arrid[$id] = $id; // create Id

	//echo "<br>tot elementi $n<BR>";



	$xmlfiletocreate = '<?xml version="1.0" encoding="'.$feed_encoding.'"?>
	<PodcastGenerator>';

	foreach ($arrdesc as $key => $val) {
		// echo "cat[" . $key . "] = " . $val . "<br>";
		// echo $key."<br>";

		$xmlfiletocreate .= '
			<category>
				<id>'.$arrid[$key].'</id>
				<name>'.$val['name'].'</name>
				<description>'.$val['desc'].'</description>
			</category>';
	}


	$xmlfiletocreate .= '
	</PodcastGenerator>';

	/////////////////////
	// WRITE THE XML FILE
	$fp = fopen("categories.xml",'w+'); //open desc file or create it

	fwrite($fp,$xmlfiletocreate);

	fclose($fp);

	$PG_mainbody .= '<p>'._("New category:").' <i>'.$val['name'].'</i></p>';

	$PG_mainbody .= '<p><b>'._("Category added!").'</b></p><p><a href="?p=admin&do=categories">'._("Back to category management").'</a>';

} // 001 end 

else { //if new category already exists

	$PG_mainbody .= _("The category you are trying to add already exists...").'<br /><br />
		<form>
		<input type="button" value="&laquo; '._("Back").'" onClick="history.back()" class="btn btn-danger btn-small" />
		</form>';

}

} // 000
else { // if POST is empty or is = to the word "all", which is already taken to show all podcasts


$PG_mainbody .= _("Please write a category name...").'
	<br /><br />
	<form>
	<input type="button" value="&laquo; '._("Back").'" onClick="history.back()" class="btn btn-danger btn-small" />
	</form>';


}


?>