<? ob_start(); ?>
<?php
mysql_connect("188.121.44.166","endoproject","Koliki@20");
mysql_select_db("endoproject");

// TO CHECK FOR ANY TABLES IN DATABASE 
$result = mysql_query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE =  'BASE TABLE' AND TABLE_SCHEMA =  'endoproject' ORDER BY CREATE_TIME DESC ");
$num = mysql_num_rows($result);
settype($num, "integer");
// gives total no. of tables in database
if($num==0){
$view_table=0; // this will not view the chart in the page
}
else {
$view_table=1;
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="Girish Kumar">
    <link rel="shortcut icon" href="assets/ico/favicon.ico">

    <title>LMS Prototype</title>

    <!-- Bootstrap core CSS -->
    <link href="bootstrap-3.1.1/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for page -->
    <link href="custom-css/starter-template.css" rel="stylesheet">
    <link href="custom-css/file.css" rel="stylesheet">
    
	<!-- jQuery -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>

    <!-- Just for debugging purposes. Don't actually copy this line! -->
    <!--[if lt IE 9]><script src="assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
       
    <!-- FILE UPLOAD SCRIPT REF#1 -->
    <script>
    // Script for whipping file into shape with bootstrap 3
		$(document)
			.on('change', '.btn-file :file', function() {
				var input = $(this),
				numFiles = input.get(0).files ? input.get(0).files.length : 1,
				label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
				input.trigger('fileselect', [numFiles, label]);
		});
		
		$(document).ready( function() {
			$('.btn-file :file').on('fileselect', function(event, numFiles, label) {
				
				var input = $(this).parents('.input-group').find(':text'),
					log = numFiles > 1 ? numFiles + ' files selected' : label;
				
				if( input.length ) {
					input.val(log);
				} else {
					if( log ) alert(log);
				}
				
			});
		});		
	</script>
  
  </head>

  <body>

    <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="index.html">LMS Project</a>
        </div>
        <div class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            <li><a href="index.html">Home</a></li>
            <li class="active"><a href="lms.php">Prototype</a></li>
            <li><a href="aims.html">Project Aims</a></li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </div>

    <div class="container">

      <div class="starter-template">
        <h1>Endoscope Result Management</h1>
        <?php
        echo "<p class='lead'>These is where all the data processing happens.</br> There are <font color='#c0392b'>".$num."</font> Tests uploaded so far</p>";
        ?>
      </div>
      <?php
      if ($view_table==0) {
      ?>
	  <div class="alert alert-danger">
	  <button type="button" class="close" data-dismiss="alert">&times;</button>
        There are <strong>no test results</strong> uploaded so far.
      </div>
      <?php
      }
      ?>
    
    <!-- FUNCTION to store the test data into database -->
    
    <?php
    
    function calculate($table_name)
	{
	mysql_query("ALTER TABLE  `$table_name` ADD  `distance` FLOAT( 11 ) NOT NULL , ADD  `speed` FLOAT( 11 ) NOT NULL ;");
	$totaldistance=0;
	$totalspeed=0;
	$sql = mysql_query("SELECT * FROM `$table_name` ORDER BY `time` ASC");
	    while($row = mysql_fetch_array($sql)) 
	    {
	    $time = $row['time'];
	    $rotation = $row['rotation'];
	    $angle = $row['angle'];
	    $x = $row['x'];
	    $y =$row['y'];
	    
	    //Fetching Future Row 
	    $ftime=$time+1;
	    $sql2 = mysql_query("SELECT * FROM `$table_name` WHERE `time`=$ftime");
	    $frow = mysql_fetch_array($sql2);
	    $ftime = $frow['time'];
	    $fx = $frow['x'];
	    $fy =$frow['y'];
	    
	    
		if($x>0 && $y>0) { // assuming that point of insersison will not be at 0,0
			$distancex= pow(($fx-$x),2);
			$distancey= pow(($fy-$y),2);
			$distance=sqrt($distancex+$distancey);
				if($ftime!=0){
					$totaldistance= $totaldistance+$distance;
					$speed=$distance/($ftime-$time);
					mysql_query("UPDATE  `endoproject`.`$table_name` SET  `distance` =  '$distance',`speed` =  '$speed' WHERE  `$table_name`.`time` =$time");
					}
				else {
					//echo "No more future values";
				}
				}
		else
			echo $time." Second(s) passed But there is no insertion yet <br>";
		}
		echo "The Distance and speed is added to database <br>";
		//$totalspeed=$totaldistance/$time;
	    //echo "<br> <br> Total Distance : ".$totaldistance;
	    //echo "<br> <br> Total Avg Speed : ".$totalspeed; 
	}
    
    function storedb($textfile)
    {
		$file = fopen($textfile, "r") or exit("Unable to open file!");
		//Output a line of the file until the end is reached
		$result = mysql_query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE =  'BASE TABLE' AND TABLE_SCHEMA =  'endoproject' ORDER BY CREATE_TIME DESC ");
		$num = mysql_num_rows($result);
        if($num==0){
        $table_name=1;
        }
        else {
        $getrow = mysql_fetch_array($result);
		$table_name = $getrow['TABLE_NAME'];
		$table_name = $table_name+1;
		}
		mysql_query("CREATE TABLE  `endoproject`.`$table_name` (`time` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,`rotation` INT( 11 ) NOT NULL ,`angle` INT( 11 ) NOT NULL ,`x` INT( 11 ) NOT NULL ,`y` INT( 11 ) NOT NULL)");
		while(!feof($file))
 		 {
		  $row  = fgets($file). "<br>";
		  $data = explode("	", $row);
		  if($data[0]== "" || $data[0]== " " || $data[0]==NULL || $data[0]=="/n" || $data[0]==0 ) {
	  			break;
	  			}
	  		else {
	  			if($data[1]!=0 && $data[2]!=0 && $data[2]!=0 && $data[4]!=0){
	  			mysql_query("INSERT INTO `endoproject`.`$table_name` (`rotation`, `angle`, `x`, `y`) VALUES ('$data[1]','$data[2]','$data[3]','$data[4]')"); }
	  			}
		  }
  		echo " Your data is stored in the database!! <br>";
		fclose($file);
		unlink($textfile);
		echo " Your file is deleted from the server <br>";
		calculate($table_name);
		echo "<center>";
		echo "<form action='results.php' method='post'>";
		echo "<input type='hidden' name='table_name' value='".$table_name."' >";
		echo "Click here to compare your Results.<br><br>";
		echo "<a href='lms.php' class='btn btn-default btn-file'>Back</a>";
		echo "<input class='btn btn-default btn-file' type='submit' name='compare' value='Compare'>";
		echo "</form>";
		echo "</center>";
	}
	
	function createdb()
    {
    	$textfile = "data/DataEndo.txt";
		$file = fopen($textfile, "r") or exit("Unable to open file!");
		//Output a line of the file until the end is reached
		$result = mysql_query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE =  'BASE TABLE' AND TABLE_SCHEMA =  'endoproject' ORDER BY CREATE_TIME DESC ");
		$num = mysql_num_rows($result);
        if($num==0){
        $table_name=1;
        }
        else {
        $getrow = mysql_fetch_array($result);
		$table_name = $getrow['TABLE_NAME'];
		$table_name = $table_name+1;
		}
		mysql_query("CREATE TABLE  `endoproject`.`$table_name` (`time` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,`rotation` INT( 11 ) NOT NULL ,`angle` INT( 11 ) NOT NULL ,`x` INT( 11 ) NOT NULL ,`y` INT( 11 ) NOT NULL)");
		while(!feof($file))
 		 {
		  $row  = fgets($file). "<br>";
		  $data = explode("	", $row);
		  if($data[0]== "" || $data[0]== " " || $data[0]==NULL || $data[0]=="/n" || $data[0]==0 ) {
	  			break;
	  			}
	  		else {
	  			if($data[1]!=0 && $data[2]!=0 && $data[2]!=0 && $data[4]!=0){
	  			$rotation = $data[1]+mt_rand(-10,5);
				$angle = $data[2]+mt_rand(-10,5);
				$x = $data[3]+mt_rand(-10,5);
				$y = $data[4]+mt_rand(-10,5);
	  			mysql_query("INSERT INTO `endoproject`.`$table_name` (`rotation`, `angle`, `x`, `y`) VALUES ('$rotation','$angle','$x','$y')"); }
	  			}
		  }
  		echo " Your data is stored in the database!! <br>";
  		fclose($file);
		calculate($table_name);
		echo "<center>";
		echo "<form action='results.php' method='post'>";
		echo "<input type='hidden' name='table_name' value='".$table_name."' >";
		echo "Click here to compare your Results.<br><br>";
		echo "<a href='lms.php' class='btn btn-default btn-file'>Back</a>";
		echo "<input class='btn btn-default btn-file' type='submit' name='compare' value='Compare'>";
		echo "</form>";
		echo "</center>";
	}
	
	function enterdb()
    {
    	$textfile = "data/DataEndo".mt_rand(0,11).".txt";
		$file = fopen($textfile, "r") or exit("Unable to open file!");
		//Output a line of the file until the end is reached
		$result = mysql_query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE =  'BASE TABLE' AND TABLE_SCHEMA =  'endoproject' ORDER BY CREATE_TIME DESC ");
		$num = mysql_num_rows($result);
        if($num==0){
        $table_name=1;
        }
        else {
        $getrow = mysql_fetch_array($result);
		$table_name = $getrow['TABLE_NAME'];
		$table_name = $table_name+1;
		}
		mysql_query("CREATE TABLE  `endoproject`.`$table_name` (`time` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,`rotation` INT( 11 ) NOT NULL ,`angle` INT( 11 ) NOT NULL ,`x` INT( 11 ) NOT NULL ,`y` INT( 11 ) NOT NULL)");
		while(!feof($file))
 		 {
		  $row  = fgets($file). "<br>";
		  $data = explode("	", $row);
		  if($data[0]== "" || $data[0]== " " || $data[0]==NULL || $data[0]=="/n" || $data[0]==0 ) {
	  			break;
	  			}
	  		else {
	  			if($data[1]!=0 && $data[2]!=0 && $data[2]!=0 && $data[4]!=0){
	  			mysql_query("INSERT INTO `endoproject`.`$table_name` (`rotation`, `angle`, `x`, `y`) VALUES ('$data[1]','$data[2]','$data[3]','$data[4]')"); }
	  			}
		  }
  		echo " Your data is stored in the database!! <br>";
  		fclose($file);
		calculate($table_name);
		echo "<center>";
		echo "<form action='results.php' method='post'>";
		echo "<input type='hidden' name='table_name' value='".$table_name."' >";
		echo "Click here to compare your Results.<br><br>";
		echo "<a href='lms.php' class='btn btn-default btn-file'>Back</a>";
		echo "<input class='btn btn-default btn-file' type='submit' name='compare' value='Compare'>";
		echo "</form>";
		echo "</center>";
	}
	
	function view_ideal_value()
	{
	echo "<br><br><br>
	<div class='row'>
	<div class='col-md-4'></div>
	<div class='col-md-4'><h3 class='sub-header'>Ideal Values</h3> <form action='lms.php' method='post'>";
	$file = fopen("data/conf.txt", "r") or exit("Unable to open config file!");
	while(!feof($file))
 		 {
		  $row  = fgets($file);
		  $data = explode(" ", $row);
		  if($data[0]=="ideal_hispeed"){
		  	$hispeed=$data[1];
		  	}
		  if($data[0]=="ideal_lowspeed"){
		  	$lowspeed=$data[1];
		  	}
		  if($data[0]=="ideal_acchispeed"){
		  	$acchispeed=$data[1];
		  	}
		  }
	fclose($file);
	echo "<div class='input-group'>";
  	echo "<span class='input-group-addon'>Ideal high speed</span>";
  	echo "<input type='text' class='form-control' name='hispeed' value='".$hispeed."'>";
  	echo "<span class='input-group-addon'>mm/sec</span>";
	echo "</div>";
	echo "<br>";
	echo "<div class='input-group'>";
  	echo "<span class='input-group-addon'>Ideal low speed</span>";
  	echo "<input type='text' class='form-control' name='lowspeed' value='".$lowspeed."'>";
  	echo "<span class='input-group-addon'>mm/sec</span>";
	echo "</div>";
	echo "<br>";
	echo "<div class='input-group'>";
  	echo "<span class='input-group-addon'>Ideal high acceleration</span>";
  	echo "<input type='text' class='form-control' name='acchispeed' value='".$acchispeed."'>";
  	echo "<span class='input-group-addon'>mm/sec<sup>2</sup></span>";
	echo "</div>";
	echo "<br>";
	echo "<br><input class='btn btn-primary btn-file' type='submit' name='ideal' value='Change Values'>";
	echo "<br></form></div><div class='col-md-4'></div></div>";
	
	} //end of the function
	
	function change_ideal_value($hispeed, $lowspeed, $acchispeed)
	{
	$file = fopen("data/conf.txt","w");
	echo fwrite($file,"ideal_hispeed ".$hispeed."\n");
	echo fwrite($file,"ideal_lowspeed ".$lowspeed."\n");
	echo fwrite($file,"ideal_acchispeed ".$acchispeed."\n");
	fclose($file);
	}//end of the function
    ?>
    
    
    <!-- UPLOAD TEXT FILE REF#2 -->
    <center>
	<?php
	
	if(isset($_POST['delete'])){
		$tno = $_POST['table_name'];
		mysql_query("DROP TABLE `$tno`");
		echo "Your table is deleted!";
		header("Location: lms.php");
	}
	if(isset($_POST['submit'])){
		$allowedExts = array("txt");
		$temp = explode(".", $_FILES["file"]["name"]);
		$extension = end($temp);
		if (($_FILES["file"]["type"] == "text/plain")
		&& ($_FILES["file"]["size"] < 5242880)
		&& in_array($extension, $allowedExts))
		  {
		  if ($_FILES["file"]["error"] > 0)
			{
			echo "Return Code: ".$_FILES["file"]["error"]."<br>";
			}
		  else
			{
			echo "Upload: ".$_FILES["file"]["name"]."<br>";
			echo "Type: ".$_FILES["file"]["type"]."<br>";
			echo "Size: ".($_FILES["file"]["size"] / 1024)." kB<br>";
			echo "Temp file: ".$_FILES["file"]["tmp_name"]."<br>";
			// If the file exists on the server
			if (file_exists("upload/" . $_FILES["file"]["name"]))
			  {
			  echo $_FILES["file"]["name"]." already exists,<br>Please try<a href='lms.php'> Again</a>.";
			  }
			else
			  { 
			  move_uploaded_file($_FILES["file"]["tmp_name"],
			  "upload/" . $_FILES["file"]["name"]);
			  echo "Stored in: "."upload/".$_FILES["file"]["name"];
			  storedb("upload/".$_FILES["file"]["name"]); // calling store db function
			  }
			}
		  }
		else
		  {
		  echo "Invalid file!! <br>Please try<a href='lms.php'> Again</a>.";
		  }
	}
	elseif(isset($_POST['generate'])) {
		createdb(); }
	elseif(isset($_POST['create'])) {
		enterdb(); }
	elseif(isset($_POST['ideal'])) {
		$hispeed = $_POST['hispeed'];
		$lowspeed = $_POST['lowspeed'];
		$acchispeed = $_POST['acchispeed'];
		change_ideal_value($hispeed, $lowspeed, $acchispeed); 
		echo "Your ideal values are changed!";
		header("Location: lms.php");
		}
	else {
	?>
		
	<h4>Upload</h4>
	<form action="lms.php" method="post" enctype="multipart/form-data">
		<div class="input-group">
			<span class="input-group-btn">
				<span class="btn btn-primary btn-file">
					Browse&hellip; <input type="file" name="file" id="file" multiple>
				</span>
			</span>
			<input type="text" class="form-control" readonly>
		</div>
		<span class="help-block">
			Please upload the endoscope's data (*.txt) file here or use the <b>generate</b> button to randomise and save sample test.
		</span>
	<input class="btn btn-primary btn-file" type="submit" name="submit" value="Submit">
	<input class="btn btn-warning btn-file" type="submit" name="generate" value="Submit Random Generated Scan">
	<input class="btn btn-warning btn-file" type="submit" name="create" value="Submit Random Default Scan">
	</form>
	<!-- <a class="btn btn-default btn-file" href="results.php">Results</a> -->
	<?php
	view_ideal_value();
	?>
	</br></br></br></br>
		<?php
		if($view_table==1){
		?>
		<h2 class="sub-header">Scans list</h2>
		<div class="table-responsive">
		<table class="table table-striped">
		  <thead>
			<tr>
			  <th>Date Created</th>
			  <th>Scan no.</th>
			  <th>Duration</th>
			  <th>Delete</th>
			  <th>Results</th>
			</tr>
		  </thead>
		  <tbody>
		  
	<?php
	$sql = mysql_query("SELECT CREATE_TIME, TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE =  'BASE TABLE' AND TABLE_SCHEMA =  'endoproject' ORDER BY CREATE_TIME DESC ");
	while($row = mysql_fetch_array($sql)) 
	{
	$date = $row['CREATE_TIME'];
	$table_name = $row['TABLE_NAME'];
	$sql2 = mysql_query("SELECT `time` FROM `$table_name` ORDER BY `time` ASC");
	$total_time = mysql_num_rows($sql2);
	settype($total_time, "integer");
	$min_tt=$total_time/60;
	settype($min_tt, "integer");
	$sec_tt=$total_time-($min_tt*60);
	echo "<tr>";
	echo "<td>".$date."</td>";
	echo "<td><span class='badge'>".$table_name."</span></td>";
	echo "<td>".$min_tt." min(s) ".$sec_tt." sec(s)</td>";
	echo "<form action='lms.php' method='post'>";
	echo "<input type='hidden' name='table_name' value='".$table_name."' >";
	echo "<td><input class='btn btn-danger btn-file' type='submit' name='delete' value='Delete'></td>";
	echo "</form>";
	echo "<form action='results.php' method='post'>";
	echo "<input type='hidden' name='table_name' value='".$table_name."' >";
	echo "<td><input class='btn btn-primary btn-file' type='submit' name='compare' value='Compare'></td>";
	echo "</form>";
	echo "</tr>";
	}
	?>
		</table>
	</div>

	
	
	<?php
	}// end if statement
	} // end main else
	?>
	</br></br></br>
    <div class="mastfoot">
      	<div class="inner">
        	<p>Created by <a href="http://girishkumar.co">Girish Kumar</a>, using <a href="http://getbootstrap.com">Bootstrap</a> and <a href="https://developers.google.com/chart/">Google Chart API</a>.</p>
      	</div>
	</div>
	
	</center>
	
    </div><!-- /.container -->


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="bootstrap-3.1.1/js/bootstrap.min.js"></script>
  </body>
</html>
<? ob_flush(); ?>