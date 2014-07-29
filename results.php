<?php
mysql_connect("188.121.44.166","endoproject","Koliki@20");
mysql_select_db("endoproject");

$file = fopen("data/conf.txt", "r") or exit("Unable to open config file!");
	while(!feof($file))
 		 {
		  $row  = fgets($file);
		  $data = explode(" ", $row);
		  if($data[0]=="ideal_hispeed"){
		  	$hs=$data[1];
		  	}
		  if($data[0]=="ideal_lowspeed"){
		  	$ls=$data[1];
		  	}
		  if($data[0]=="ideal_acchispeed"){
		  	$ahs=$data[1];
		  	}
		  }
fclose($file);

if(isset($_POST['compare'])){
	$tname = $_POST['table_name'];
	$ideal_hispeed = $hs; //ideal speed 
	$ideal_lowspeed = $ls;
	$ideal_acchispeed = $ahs;
	$score=0.0; // score (float)
	$better=0; // better score
	$feed_fast=0; // feedback if the insertion is fast
	$feed_slow=0; // feedback if the insertion is slow
	tophtml();
	googleapi($tname);
	bodymain();
	bodyview($tname);
	bottomhtml();
		
} else {
	tophtml();
	bodymain();
	bodyerror();
	bottomhtml();
}

function total_tables() {
	$sql = mysql_query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE =  'BASE TABLE' AND TABLE_SCHEMA =  'endoproject' ORDER BY CREATE_TIME DESC ");
	$total_tables = mysql_num_rows($sql);
	settype($total_tables, "integer");
	return $total_tables;
}

function total_time($table_name) {
	$sql = mysql_query("SELECT `time` FROM `$table_name` ORDER BY `time` ASC");
	$total_time = mysql_num_rows($sql);
	settype($total_time, "integer");
	return $total_time;
}
/*
function speedgraph(){
	global $tname;
	$totaltime = total_time($tname);
	for ($i=1;$i<=$totaltime; $i++) {
	echo "['".$i." sec', ";
	$sql = mysql_query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE =  'BASE TABLE' AND TABLE_SCHEMA =  'endoproject' ORDER BY CREATE_TIME ASC");
	$num = total_rows();
	while($getrow = mysql_fetch_array($sql))
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
	
} // end function */

function speed_comp_cal($tname) {
	global $ideal_hispeed;
	global $ideal_lowspeed;
	global $score;
	global $better;
	global $worse;
	global $improve;
	global $feed_slow;
	global $feed_fast;
	global $ideal_acchispeed;
	$totaltime = total_time($tname);
	$totaltables = total_tables();
	$total_var_value=0;
	$total_var_value2=0;
	$acc_total_var_value=0;
	$acc_total_var_value2=0;
	for ($i=1;$i<=$totaltime; $i++) {
		$sec_speed=0;
		$f_sec_speed=0;
		$high_speed=0;
		$low_speed=0;
		$ary_count=0;
		$sql = mysql_query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE =  'BASE TABLE' AND TABLE_SCHEMA =  'endoproject' ORDER BY CREATE_TIME ASC");
		while($getrow = mysql_fetch_array($sql)) {
			$table_name = $getrow['TABLE_NAME'];
			$f_i=$i+1; // for acceleration
			$sql2 = mysql_query("SELECT `speed` FROM `$table_name` WHERE `time`=$i");
			$row = mysql_fetch_array($sql2);
			$speed = $row['speed'];
			$sec_speed=$sec_speed+$speed;
			$array[$ary_count]=$speed;
			$ary_count=$ary_count+1;
			
			/* FOR FUTURE SPEED */
			$f_sql2 = mysql_query("SELECT `speed` FROM `$table_name` WHERE `time`=$f_i");
			$f_row = mysql_fetch_array($f_sql2);
			$f_speed = $f_row['speed'];
			$f_sec_speed=$f_sec_speed+$f_speed;
			
			
		}
		$high_speed = max($array);
		$low_speed = min($array);	
		$sql3 = mysql_query("SELECT `speed`  FROM `$tname` WHERE `time`=$i");
		$newrow = mysql_fetch_array($sql3);
		$comp_speed = $newrow['speed'];
		
		/* FOR FUTURE SPEED */
		$f_sql3 = mysql_query("SELECT `speed`  FROM `$tname` WHERE `time`=$f_i");
		$f_newrow = mysql_fetch_array($f_sql3);
		$f_comp_speed = $f_newrow['speed'];
		
		//These are the outputting values:
		$avg_speed=$sec_speed/$totaltables;
		$f_avg_speed=$f_sec_speed/$totaltables;
		
		$avg_acceleration=($f_avg_speed-$avg_speed)/$totaltables;
		$comp_acceleration=($f_comp_speed-$comp_speed)/$totaltables;
		
		/* ACCELERATION Variance */
		$acc_var_value=pow(($ideal_acchispeed - $avg_acceleration),2);
		$acc_total_var_value=$acc_total_var_value+$acc_var_value;
		
		$acc_var_value2=pow(($ideal_acchispeed - $comp_acceleration),2);
		$acc_total_var_value2=$acc_total_var_value2+$acc_var_value2;
		
		/* SPEED Variance */
		$var_value=pow(($ideal_hispeed - $avg_speed),2);
		$total_var_value=$total_var_value+$var_value;
		
		$var_value2=pow(($ideal_hispeed - $comp_speed),2);
		$total_var_value2=$total_var_value2+$var_value2;
		

		/*
		$high_speed;
		$low_speed;
		$comp_speed;
		
		$ideal_hispeed;
		$ideal_lowspeed;
		$score;
		$feed_fast;
		$feed_slow; */
		
		//good zone
		if($comp_speed>=$ideal_lowspeed) {
			$score=$score+0.5; }
		if ($comp_speed<=$ideal_hispeed) {
			$score=$score+0.5; }
		/*
		if ($comp_speed>$avg_speed && $comp_speed<$ideal_hispeed) {
			$better=$better+0.5; }
		if ($comp_speed<$avg_speed && $comp_speed>$ideal_lowspeed) {
			$better=$better+0.5; }
		*/
		
		//bad zone
		if ($comp_speed<$ideal_lowspeed) {
			$feed_slow=$feed_slow+1;
			$score=$score-0.5;}
		if ($comp_speed>$ideal_hispeed) {
			$feed_fast=$feed_fast+1;
			$score=$score-0.5; }	
		
		/*
		if ($comp_speed>=$avg_speed && $comp_speed>=$ideal_hispeed) {
			$better=$better-0.5; }
		if ($comp_speed<=$avg_speed && $comp_speed<=$ideal_lowspeed) {
			$better=$better-0.5; }	
		*/
		
	} // end for-loop

	$variance=$total_var_value/$totaltime-1;
	$comp_variance=$total_var_value2/$totaltime-1;
	
	// Acceleration
	$acc_variance=$acc_total_var_value/$totaltime-2;
	$acc_comp_variance=$acc_total_var_value2/$totaltime-2;
	$new_score=($score/$totaltime)*100;
	$new_score=round($new_score,2);
	
	
	
	// REF http://www.herkimershideaway.org/writings/pvarmn.htm
	$total_variance=$variance+$acc_variance;
	$total_comp_variance=$comp_variance+$acc_comp_variance;
	$total_variance=round($total_variance,5);
	$total_comp_variance=round($total_comp_variance,5);
	
	if ($total_comp_variance==$total_variance) {
		echo "<div class='alert alert-warning'>";
		echo "<b>You have made your scan same as the average scan. </b>"; 
		echo "</div>"; }
	else {
		if ($total_comp_variance<$total_variance) {
			$percent=(($total_variance-$total_comp_variance)/$variance)*100;
			$percent=round($percent,2);
			echo "<div class='alert alert-success'>";
			echo "<strong>Well done!</strong> You have made your scan <strong>".$percent."%</strong> better than the average scan.";
			echo "</div>"; }
		else {
			$percent=(($total_comp_variance-$total_variance)/$variance)*100;
			$percent=round($percent,2);
			echo "<div class='alert alert-danger'>";
			echo "<strong>Oh snap!</strong> You have made your scan <strong>".$percent."%</strong> worse than the average scan.";
			echo "</div>"; }
	}
	echo "<div class='well well-lg'>";
	if ($new_score<40) {
	echo "<h4>Your score is <span class='label label-danger'>".$new_score."/100 </span> </h4>";
	}
	if ($new_score>=40 && $new_score<=50) {
	echo "<h4>Your score is <span class='label label-warning'>".$new_score."/100 </span> </h4>";
	}
	if ($new_score>50) {
	echo "<h4>Your score is <span class='label label-success'>".$new_score."/100 </span> </h4>";
	}
	echo "<h4>Score : <span class='label label-info'>".$score."</span></h4> <br> Average Scan Variance : <b>".$total_variance."</b> <br> Current Scan Variance : <b>".$total_comp_variance."</b>";
	echo "</div>";
	
	echo "<div class='panel panel-info'>";
	echo "<div class='panel-heading'>";
	echo "<h3 class='panel-title'>Reviews</h3>";
	echo "</div>";
	echo "<div class='panel-body'>";
	if ($feed_slow<$feed_fast){
		echo "The scan was <b>fast</b>, inorder to make your scan better please slow down your speed "; }
	else { 
		echo "The scan was <b>slow</b>, inorder to make your scan better please increase your speed "; }
		$min_fast=$feed_fast/60;
		settype($min_fast, "integer");
		$sec_fast=$feed_fast-($min_fast*60);
		$min_slow=$feed_slow/60;
		settype($min_slow, "integer");
		$sec_slow=$feed_slow-($min_slow*60);
	echo "<br>Total time spent moving fast : <b>".$min_fast." minutes ".$sec_fast." seconds</b> ";
	echo "<br>Total time spent moving slow : <b>".$min_slow." minutes ".$sec_slow." seconds</b> ";
	echo "</div>";
	echo "</div>";

} // end function


function speed_comp($table_name) {
	global $tname;
	global $ideal_hispeed;
	global $ideal_lowspeed;
	$div=79; // divide the time in "div" parts for the display
	$totaltime = total_time($tname);
	$i=$totaltime/$div;
	$num = total_tables();
	
	while (round($i)<=$totaltime)
	{
	
	echo "['".$i." sec', ";
	$count=$i-(($totaltime/$div)-1);
	$sec_speed=0;
	$high_speed=0;
	$low_speed=0;
	$ary_count=0;
	$result = mysql_query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE =  'BASE TABLE' AND TABLE_SCHEMA =  'endoproject' ORDER BY CREATE_TIME ASC");
	while($getrow = mysql_fetch_array($result))
	{			
		$total_distance=0;
		$table_name = $getrow['TABLE_NAME'];
		for ($j=$count; $j<=$i; $j++) {
			$sql = mysql_query("SELECT `distance`  FROM `$table_name` WHERE `time`=$j");
			$row = mysql_fetch_array($sql);
			$distance = $row['distance'];
			$total_distance=$total_distance+$distance;
		}// end for-loop
		$speed = $total_distance/($totaltime/$div);
		$array[$ary_count]=$speed;
		$ary_count=$ary_count+1;		
		$sec_speed=$sec_speed+$speed;
		
		/*
		if($i==$totaltime && $table_name==$num){
		echo $speed."]]);"; }
		else {
			if($table_name<$num){
			echo $speed.", "; }
			else {
			echo $speed."], "; }
		} 
		*/
	} //end while reading
	
	$avg_speed=$sec_speed/$num;
	echo $avg_speed.", ";
	$total_distance2=0;
	for ($k=$count; $k<=$i; $k++){
		$sql3 = mysql_query("SELECT `distance`  FROM `$tname` WHERE `time`=$k");
		$newrow = mysql_fetch_array($sql3);
		$distance2 = $newrow['distance'];
		$total_distance2=$total_distance2+$distance2;
	}// end for-loop
	$comp_speed = $total_distance2/($totaltime/$div);
	$high_speed = max($array);
	$low_speed = min($array);
	
	echo $comp_speed.", ";
	echo $high_speed.", ";
	echo $low_speed.", ";
	echo $ideal_lowspeed.", ";
	if (round($i)!=$totaltime){
		echo $ideal_hispeed."], "; }
	else {
		echo $ideal_hispeed."]]);"; }
	
	$i=$i+($totaltime/$div);
	} //end while
} // end function

function acc_comp($table_name) {
	global $tname;
	global $ideal_hispeed;
	global $ideal_lowspeed;
	global $ideal_acchispeed;
	$div=79; // divide the time in "div" parts for the display
	$totaltime = total_time($tname);
	$i=$totaltime/$div;
	$num = total_tables();
	
	while (round($i)<=$totaltime)
	{
	
	echo "['".$i." sec', ";
	$count=$i-(($totaltime/$div)-1);
	$sec_speed=0;
	$f_sec_speed=0;
	$ary_count=0;

	$i_sec_speed=0;
	$result = mysql_query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE =  'BASE TABLE' AND TABLE_SCHEMA =  'endoproject' ORDER BY CREATE_TIME ASC");
	while($getrow = mysql_fetch_array($result))
	{			
		$total_distance=0;
		$i_total_distance=0;
		$table_name = $getrow['TABLE_NAME'];
		for ($j=$count; $j<=$i; $j++) {
			$sql = mysql_query("SELECT `distance`  FROM `$table_name` WHERE `time`=$j");
			$row = mysql_fetch_array($sql);
			$distance = $row['distance'];
			$total_distance=$total_distance+$distance;
			if ($j==$count){
				$i_total_distance=$distance;
			}
		}// end for-loop
		$speed = $total_distance/($totaltime/$div);
		
		$sec_speed=$sec_speed+$speed;
		$i_sec_speed=$i_sec_speed+$i_total_distance;
	} //end while reading

	$i_speed = $i_sec_speed/$num;	
	$f_speed=$sec_speed/$num;
	$acceleration=($f_speed-$i_speed)/($totaltime/$div);
	
	echo $acceleration.", ";
	$total_distance2=0;
	$f_total_distance2=0;
	for ($k=$count; $k<=$i; $k++){
		$sql3 = mysql_query("SELECT `distance`  FROM `$tname` WHERE `time`=$k");
		$newrow = mysql_fetch_array($sql3);
		$distance2 = $newrow['distance'];
		$total_distance2=$total_distance2+$distance2;
	}// end for-loop
			
	$i_sql3 = mysql_query("SELECT `speed`  FROM `$tname` WHERE `time`=$count");
	$i_newrow = mysql_fetch_array($i_sql3);
	$i_speed2 = $i_newrow['speed'];
	
	$f_speed2 = $total_distance2/($totaltime/$div);
	$comp_acceleration=($f_speed2-$i_speed2)/($totaltime/$div);
	
	
	
	echo $comp_acceleration.", ";
	if (round($i)!=$totaltime){
		echo $ideal_acchispeed."], "; }
	else {
		echo $ideal_acchispeed."]]);"; }
	
	$i=$i+($totaltime/$div);
	} //end while
} // end of function

function tophtml(){
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
<?php
}
function googleapi($table_name) {
?>
		<!-- GOOGLE GRAPH API -->
		<script type="text/javascript" src="https://www.google.com/jsapi"></script>
		<script type="text/javascript">
		  google.load("visualization", "1", {packages:["corechart"]});
		  google.setOnLoadCallback(drawChart);
	  
		  function drawChart() {
			var data = google.visualization.arrayToDataTable([
			<?php
			echo "['Time', 'Rows', 'Columns'],";
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
		
		<script type="text/javascript">
		google.setOnLoadCallback(drawChart);
		function drawChart() {
			var data = new google.visualization.DataTable();
			data.addColumn('string', 'x');
			data.addColumn('number', 'values');
			data.addColumn({id:'i0', type:'number', role:'interval'});
			data.addColumn({id:'i1', type:'number', role:'interval'});
			data.addColumn({id:'i1', type:'number', role:'interval'});
			data.addColumn({id:'i2', type:'number', role:'interval'});
			data.addColumn({id:'i3', type:'number', role:'interval'});

			data.addRows([
				<?php
				
				speed_comp($table_name);
				?>
	
			// The intervals data as narrow lines (useful for showing raw source data)
			var options_lines = {
				title: 'Speed Intervals, in mm/sec',
				curveType:'function',
				lineWidth: 2,
				interval: {
					'i0': { 'style':'line', 'color':'#2ecc71', 'lineWidth': 2 }, //Current GREEN
					'i1': { 'style':'area', 'color':'#F1CA3A', 'fillOpacity':0.3},					 // High and Low GOLD
					'i2': { 'style':'line', 'color':'#e67e22', 'lineWidth': 2 }, // ideal Low ORANGE
					'i3': { 'style':'line', 'color':'#e74c3c', 'lineWidth': 2 }, // ideal High RED
				},
				legend: 'none',
			};

			var chart_lines = new google.visualization.LineChart(document.getElementById('chart_lines'));
			chart_lines.draw(data, options_lines);
		}
		</script>
		
		<script type="text/javascript">
		google.setOnLoadCallback(drawChart);
		function drawChart() {
			var data = new google.visualization.DataTable();
			data.addColumn('string', 'x');
			data.addColumn('number', 'values');
			data.addColumn({id:'i0', type:'number', role:'interval'});
			data.addColumn({id:'i1', type:'number', role:'interval'});

			data.addRows([
				<?php
				acc_comp($table_name);
				?>
	
			// The intervals data as narrow lines (useful for showing raw source data)
			var options_lines = {
				title: 'Acceleration Intervals, in mm/sec2',
				curveType:'function',
				lineWidth: 2,
				interval: {
					'i0': { 'style':'line', 'color':'#2ecc71', 'lineWidth': 2 }, //Current GREEN
					'i1': { 'style':'line', 'color':'#e74c3c', 'lineWidth': 2 }, // ideal High RED
				},
				legend: 'none',
			};

			var chart_lines = new google.visualization.LineChart(document.getElementById('chart_lines2'));
			chart_lines.draw(data, options_lines);
		}
		</script>	
<?php
}
function bodymain() {
?>
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
}
function bodyview($table_name) {
?>
	<div class="container">
	  <div class="starter-template">
		<h1>Your Results</h1>
		<?php
		echo "<p class='lead'>Here is the result for the scan no. <span class='label label-primary'>".$table_name."</span></br> Your results are depicted in graphs and review boxes.</p>";
		?>
	  </div>
  
	  
	
	<?php
	echo "<center>";
	speed_comp_cal($table_name);
	echo "<div id='chart_lines' style='width: 1000px; height: 1000px;'></div>"; 
	echo "<div id='chart_lines2' style='width: 1000px; height: 600px;'></div>";
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
	
	echo "</center>";
	?>	
<?php
}	
function bodyerror() {
?>
		<br><br><br><br><br><br><br><br>
		<div class="container">
		  <div class="starter-template">
			<h1>Sorry!</h1>
			<p class="lead">you have not selected the test to be compared.</br> please go <a href="lms.php">this page</a> to select the test.</p>
		  </div>
<?php
}
function bottomhtml() {
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
<?php
}
?>
