<?php
	#error_reporting(E_ALL);

	$user = 'root';
	$password = 'root';
	$db = 'esensorics_dev';
	$host = 'localhost';
	$port = 8889;

	$conn = mysqli_init();
	$success = mysqli_real_connect(
	   $conn, 
	   $host, 
	   $user, 
	   $password, 
	   $db,
	   $port
	);


	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	} 


	function yt_exists($videoID) {
	    $theURL = "http://www.youtube.com/oembed?url=http://www.youtube.com/watch?v=$videoID&format=json";
	    $headers = get_headers($theURL);

	    if (substr($headers[0], 9, 3) !== "404") {
	        return true;
	    } else {
	        return false;
	    }
	}


	if ($_POST["addnew"]==1)
	{
		$error="";
		if (strlen($_POST["name"])<1) $error.="<p>Name has to be at least 2 characters.</p>";
		if (strlen($_POST["description"])<1) $error.="<p>Description has to be at least 2 characters.</p>";
		if (strlen($_POST["videolink"])!=11) $error.="<p>Youtube video ID has to be 11 characters.</p>";
			elseif (!yt_exists($_POST["videolink"])) $error.="<p>Youtube video does not exist.</p>";


		if (strlen($error)==0)
		{
			$conn->query("INSERT into surveys (name, description, videolink) VALUES (\"".mysqli_real_escape_string($conn,$_POST["name"])."\", \"".mysqli_real_escape_string($conn,$_POST["description"])."\", \"".mysqli_real_escape_string($conn,$_POST["videolink"])."\")");
			header('Location: surveys.php?success=1');
		}	
	}

	if ($_GET["delete"]>0)
	{
		$id=(int)$_GET["delete"];
		$conn->query("DELETE FROM surveys WHERE id=".$id);
		header('Location: surveys.php?deleted=1');	
	}
?><!DOCTYPE html>
<html>
<head>
	<title>Esensorics food survey database</title>
 	<link rel="stylesheet" href="style.css">
 	<script src="jquery-1.11.2.min.js"></script>
</head>

<body>
  <h1>Esensorics food survey database</h1>
  <p>This is a page to test the food survey database.</p>

<?php
	    if ($_GET["success"]==1)
	    {
	    	echo "
	        	<div class=\"table_row_nostyle\">
	        		<div class=\"success\"><p><strong>You have successfully added a new survey!</strong></p></div>
	        	</div>
	    	";
	    }
	    if ($_GET["deleted"]==1)
	    {
	    	echo "
	        	<div class=\"table_row_nostyle\">
	        		<div class=\"success\"><p><strong>You have successfully deleted a survey!</strong></p></div>
	        	</div>
	    	";
	    }
?>

<p><input type="button" id="addnew" value="Add new survey" /></p>
<div id="addnew_form" class="hiddendiv">
	<h2>Add new survey</h2>
	<div class="tablewithform">

<?php
	    if (strlen($error)>0)
	    {
	    	echo "
	        	<div class=\"table_row_nostyle\">
	        		<div class=\"error\"><p><strong>We could not add new survey, because:</strong></p>".$error."</div>
	        	</div>
	        	<script>
					  $( \"#addnew_form\" ).toggle( \"fast\", function() {});
				</script>
	    	";
	    }
?>

		<form action="surveys.php" method="post">
			<input type="hidden" name="addnew" value="1" />
			<div class="table_row_nostyle">
				<div class="table_field" style="width:150px;">Name</div>
				<div class="table_field" style="width:250px;"><input name="name" type="text" value="<?=htmlspecialchars($_POST["name"])?>" /></div>
			</div>

			<div class="table_row_nostyle">
				<div class="table_field" style="width:150px;">Description</div>
				<div class="table_field" style="width:250px;"><textarea name="description"><?=htmlspecialchars($_POST["description"])?></textarea></div>
			</div>

			<div class="table_row_nostyle">
				<div class="table_field" style="width:150px;">Youtube video ID</div>
				<div class="table_field" style="width:250px;"><input name="videolink" type="text" value="<?=htmlspecialchars($_POST["videolink"])?>" maxlength="11" /></div>
			</div>

			<div class="table_row_nostyle"><input type="submit" id="addnew_submit" value="Save" /></div>
		</form>
	</div>
	<div class="table_row_nostyle" style="height:20px;"></div>

</div>
<script>
	$( "#addnew" ).click(function() {
	  $( "#addnew_form" ).toggle( "fast", function() {});
	});
</script>


<?php

	$sql = "SELECT * FROM surveys";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
	    // output data of each row
	    echo "<div class=\"table\" style=\"width:800px;\">";

	        echo "
	        	<div class=\"table_row_header\">
	        		<div class=\"table_field\" style=\"width:30px;\">ID</div>
	        		<div class=\"table_field\" style=\"width:100px;\">Name</div>
	        		<div class=\"table_field\" style=\"width:250px;\">Description</div>
	        		<div class=\"table_field\" style=\"width:100px;\">Youtube video</div>
	        		<div class=\"table_field\" style=\"width:50px;\">Delete</div>
	        	</div>
	        ";

	    while($row = $result->fetch_assoc()) {


	        echo "
	        	<div class=\"table_row\">
	        		<div class=\"table_field\" style=\"width:30px;\">".$row["id"]."</div>
	        		<div class=\"table_field\" style=\"width:100px;\">".$row["name"]."</div>
	        		<div class=\"table_field\" style=\"width:250px;\">".$row["description"]."</div>
	        		<div class=\"table_field\" style=\"width:100px;cursor:pointer;text-decoration:underline;font-weight:bold;\" id=\"videolink_".$row["id"]."\">".(strlen($row["videolink"])>0?"Check video":"")."</div>
	        		<div class=\"table_field\" style=\"width:50px;\"><a href=\"surveys.php?delete=".$row["id"]."\"><img src=\"img/icon-delete.png\" title=\"Delete\" alt=\"Delete\" width=\"15\" /></a></div>
	        	</div>
	        ";

	        if (strlen($row["videolink"])>10) {
	        	echo "
	        	<div class=\"hiddenvideo\" id=\"videodiv_".$row["id"]."\">
	        		<iframe id=\"ytplayer_".$row["id"]."\" type=\"text/html\" width=\"640\" height=\"390\" src=\"http://www.youtube.com/embed/".$row["videolink"]."\" frameborder=\"0\"></iframe>
	   			</div>
	        	";
	        	
	        	echo "
	        	<script>
					$( \"#videolink_".$row["id"]."\" ).click(function() {
					  $( \"#videodiv_".$row["id"]."\" ).toggle( \"slow\", function() {});
					});
	        	</script>
	        	";
	        }
	    }
 	    echo "</div>";

	} else {
	    echo "<p>There were no results</p>";
	}
	$conn->close();


	

?>

</body>

</html>