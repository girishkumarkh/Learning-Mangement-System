<?php
mysql_connect("188.121.44.166","endoproject","Koliki@20");
mysql_select_db("endoproject");


// TO CHECK FOR ANY TABLES IN DATABASE  
$result = mysql_query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE =  'BASE TABLE' AND TABLE_SCHEMA =  'endoproject' ORDER BY CREATE_TIME DESC ");
$num = mysql_num_rows($result);
// gives total no. of tables in database
if($num==0){
$viewchart=0; // this will not view the chart in the page
}
else {
$viewchart=1;
}


function total_time($table_name) {
	$totaltimesql = mysql_query("SELECT `time` FROM `$table_name` ORDER BY `time` ASC");
	$totaltime = mysql_num_rows($totaltimesql);
	return $totaltime;
}

function total_rows() {
	$result1 = mysql_query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE =  'BASE TABLE' AND TABLE_SCHEMA =  'endoproject' ORDER BY CREATE_TIME DESC ");
	$checkrow = mysql_fetch_array($result1);
	$table_name = $checkrow['TABLE_NAME'];
	return $table_name;
}

function speedgraph(){
	$totaltime = total_time();
	for ($i=1;$i<=$totaltime; $i++) {
	echo "['".$i." sec', ";
	$result2 = mysql_query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE =  'BASE TABLE' AND TABLE_SCHEMA =  'endoproject' ORDER BY CREATE_TIME ASC");
	$num = total_rows();
	while($getrow = mysql_fetch_array($result2))
	{			
	$table_name = $getrow['TABLE_NAME'];
	$sql = mysql_query("SELECT `speed`  FROM `$table_name` WHERE `time`=$i");
	$row = mysql_fetch_array($sql);
	$speed = $row['speed'];
	if($i==$totaltime && $table_name==$num){
	echo $speed."]]);"; }
	else {
		if($table_name<$num){
		echo $speed.", "; }
		else {
		echo $speed."], ";
		}
	}
	} //end while
	} //end for
	
	} // end function
	
function speed_comp() {

	$totaltime = total_time($tname);
	$i=237;
	$num = total_rows();
	
	while ($i<=$totaltime)
	{
	
	
	echo "['".$i." sec', ";
	$count=$i-236;
	
	$result2 = mysql_query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE =  'BASE TABLE' AND TABLE_SCHEMA =  'endoproject' ORDER BY CREATE_TIME ASC");
	while($getrow = mysql_fetch_array($result2))
	{			
		$total_distance=0;
		$table_name = $getrow['TABLE_NAME'];
		for ($j=$count; $j<=$i; $j++)
		{
		$sql = mysql_query("SELECT `distance`  FROM `$table_name` WHERE `time`=$j");
		$row = mysql_fetch_array($sql);
		$distance = $row['distance'];
		$total_distance=$total_distance+$distance;
		}// end for loop
		$speed = $total_distance/237;
		if($i==$totaltime && $table_name==$num){
		echo $speed."]]);"; }
		else {
			if($table_name<$num){
			echo $speed.", "; }
			else {
			echo $speed."], ";
			}
		}
	} //end while reading
	
	
	$i=$i+237;
	} //end while

} // end function
			
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="Girish Kumar">
    <link rel="shortcut icon" href="assets/ico/favicon.ico">

    <title>Results - LMS Prototype</title>

    <!-- Bootstrap core CSS -->
    <link href="bootstrap-3.1.1/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for page -->
    <link href="custom-css/starter-template.css" rel="stylesheet">
    <link href="custom-css/file.css" rel="stylesheet">
	

    <!-- Just for debugging purposes. Don't actually copy this line! -->
    <!--[if lt IE 9]><script src="assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    
    <!-- GOOGLE GRAPH API -->
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
		<?php
		echo "['Time', 'Rows', 'Columns'],";
		$table_name = total_rows();
		$sql = mysql_query("SELECT `time` ,  `x` ,  `y`  FROM `$table_name` ORDER BY `time` ASC");
		$max = mysql_num_rows($sql);
	    while($row = mysql_fetch_array($sql)) 
	    {
	    $time = $row['time'];
	    $x = $row['x'];
	    $y = $row['y'];
		if($sno!=$max) {
			echo "['".$time."',".$x.", ".$y."],"; }
		else {
			echo "['".$time."',".$x.", ".$y."]"; 
			break;}
		}
		?>
        ]);

        var options = {
          title: 'Graph of Rows and columns'
        };

        var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
        chart.draw(data, options);
      }
      </script>
      <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
		<?php
		echo "['Time', 'Rotation', 'Angle'],";
		$table_name = total_rows();
		$sql = mysql_query("SELECT `time` ,  `rotation` ,  `angle`  FROM `$table_name` ORDER BY `time` ASC");
		$max = mysql_num_rows($sql);
	    while($row = mysql_fetch_array($sql)) 
	    {
	    $time = $row['time'];
	    $rotation = $row['rotation'];
	    $angle = $row['angle'];
		if($sno!=$max) {
			echo "['".$time."',".$rotation.", ".$angle."],"; }
		else {
			echo "['".$time."',".$rotation.", ".$angle."]"; 
			break;}
		}
		?>
        ]);

        var options = {
          title: 'Graph of Rotation and angle'
        };

        var chart = new google.visualization.LineChart(document.getElementById('chart_div2'));
        chart.draw(data, options);
      }
      </script>
      
      <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
        <?php
		echo "['x', 'y'],";
		$table_name = total_rows();
		$sql = mysql_query("SELECT `x` ,  `y`  FROM `$table_name` ORDER BY `time` ASC");
		$max = mysql_num_rows($sql);
	    while($row = mysql_fetch_array($sql)) 
	    {
	    $x = $row['x'];
	    $y = $row['y'];
		if($sno!=$max) {
			echo "[".$x.", ".$y."],"; }
		else {
			echo "[".$x.", ".$y."]"; 
			break;}
		}
		?>
        ]);
        var options = {
          title: 'X vs. Y comparison',
          hAxis: {title: 'x', minValue: 0, maxValue: 1000},
          vAxis: {title: 'y', minValue: 0, maxValue: 400},
          legend: 'none'
        };

        var chart = new google.visualization.ScatterChart(document.getElementById('chart_div3'));
        chart.draw(data, options);
      }
    </script>
    
    
    <script>
	google.setOnLoadCallback(drawChart);
	function drawChart() {
		var data = new google.visualization.DataTable();
		data.addColumn('string', 'x');
		data.addColumn('number', 'values');
		data.addColumn({id:'i0', type:'number', role:'interval'});
		data.addColumn({id:'i1', type:'number', role:'interval'});

		data.addRows([
			<?php
			speed_comp();
			?>
			
			

		// The intervals data as narrow lines (useful for showing raw source
		// data)
		var options_lines = {
			title: 'Speed Intervals, every 237 seconds',
			curveType:'function',
			lineWidth: 2,
			intervals: { 'style':'line' }, // Use line intervals.
			legend: 'none',
		};

		var chart_lines = new google.visualization.LineChart(document.getElementById('chart_lines'));
		chart_lines.draw(data, options_lines);
	}
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

	<?php
   	if(isset($_POST['compare'])){
		$tname = $_POST['table_name'];
		
		
	?>
	
	<div class="container">
	  <div class="starter-template">
		<h1>Haaa!</h1>
		<p class="lead">These is where all the data processing happens.</br> Below here is a sample graph from the API (Visual Analytics).</p>
	  </div>
  
	  <div class="alert alert-success">
	  <button type="button" class="close" data-dismiss="alert">&times;</button>
		<strong>Well done!</strong> You made your scan 80% better.
	  </div>
	
	<?php
	echo "<center>";
	if($viewchart==1){
	echo "<div id='chart_lines' style='width: 1000px; height: 600px;'></div>"; 
	echo "<div id='chart_div' style='width: 1000px; height: 600px;'></div>";
	echo "<div id='chart_div2' style='width: 1000px; height: 600px;'></div>";
	echo "<div id='chart_div3' style='width: 1000px; height: 600px;'></div>";
	echo"<br>";
	/*
	$totaldistance=0;
	$totalspeed=0;
	$sql = mysql_query("SELECT * FROM `1` ORDER BY `time` ASC");
	    while($row = mysql_fetch_array($sql)) 
	    {
	    $time = $row['time'];
	    $rotation = $row['rotation'];
	    $angle = $row['angle'];
	    $x = $row['x'];
	    $y =$row['y'];
	    
	    //Fetching Future Row 
	    $sql2 = mysql_query("SELECT * FROM `1` WHERE `time`=$time+1");
	    $frow = mysql_fetch_array($sql2);
	    $ftime = $frow['time'];
	    $fx = $frow['x'];
	    $fy =$frow['y'];
	    
	    
		if($x>0 && $y>0) { // assuming that point of insersison will not be at 0,0
			$distancex= pow(($fx-$x),2);
			$distancey= pow(($fx-$x),2);
			$distance=sqrt($distancex+$distancey);

			echo "Current ".$time." Seconds, ".$x." X, ".$y."Y , ";
				if($ftime!=0){
					echo "Future ".$ftime." Seconds, ".$fx." X, ".$fy."Y , ";
					echo " The distance = ".$distance." , ";
					$totaldistance= $totaldistance+$distance;
					$speed=$distance/($ftime-$time);
					echo " The Speed = ".$speed." <br>";
					}
				else
					echo "No more future values";
				}
		else
			echo $time." Second(s) passed But there is no insertion yet <br>";
		}
		$totalspeed=$totaldistance/$time;
	    echo "<br> <br> Total Distance : ".$totaldistance;
	    echo "<br> <br> Total Avg Speed : ".$totalspeed; */
	
	}else {
	echo "There are no tests results uploaded so far!. <br> Please upload the endoscope's data file,<br> and <a href='lms.php'>Try Again</a>";
	}
	echo "</center>";
	}else {
	?>
	<br><br><br><br><br><br><br><br>
	<div class="container">
	  <div class="starter-template">
		<h1>Sorry!</h1>
		<p class="lead">you have not selected the test to be compared.</br> please go <a href="lms.php">this page</a> to select the test.</p>
	  </div>
	
	<?php
	}
	?>
	
	<center>	
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
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script src="bootstrap-3.1.1/js/bootstrap.min.js"></script>
  </body>
</html>