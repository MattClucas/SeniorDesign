<?php
	defined('C5_EXECUTE') or die("Access Denied.");
	$db = Loader::db();
?>

<div id="HTMLBlock<?php echo intval($bID)?>" class="HTMLBlock">
<script>
<?php 
	$NUM_LABELS = 20;
	echo $content;
	
	$labels = "";
	$moistureData = "";
	$temperatureData = "";
	$waterUsageData = "";
	$chartType = "";
	if (isset($_GET['id']) && $_GET['id'] > 0)
	{
		$chartType = "Line";
		$result = $db->Execute("SELECT * FROM `PlantMonitor_Data` WHERE `PLANT_ID` = '" . $_GET['id'] . "' ORDER BY TIME");
		$i=0;
		foreach($result as $key => $val)
		{
			$i++;
		}
		$resultLen = $i;
		
		$i=0;
		foreach($result as $key => $val)
		{
			if($i%round($resultLen/$NUM_LABELS)==0)
			{
				$labels .= $val['TIME'];
			}
			else
			{
				$labels .= "";
			}
			$moistureData .= $val['MOISTURE_PERCENTAGE'];
			$temperatureData .= $val['TEMPERATURE_DEGREES_FAHRENHEIT'];
			$waterUsageData .= $val['WATER_USED_MILLILITERS'];
			if($i<$resultLen-1)
			{
				$labels .= "\",\"";
				$moistureData .= ",";
				$temperatureData .= ",";
				$waterUsageData .= ",";
			}
			$i++;
		}
	?>
	var moistureDataSet = 
	{
		labels : 
		["<?php echo $labels;?>"],
		datasets : 
		[
			{
				label: "My First dataset",
				fillColor : "rgba(63,127,191,1)",
				strokeColor : "rgba(220,220,220,1)",
				pointColor : "rgba(220,220,220,0)",
				pointStrokeColor : "#fff",
				pointHighlightFill : "#fff",
				pointHighlightStroke : "rgba(220,220,220,1)",
				data : [<?php echo $moistureData;?>]
			}
		]
	};
	var temperatureDataSet = 
	{
		labels : 
		["<?php echo $labels;?>"],
		datasets : 
		[
			{
				label: "My First dataset",
				fillColor : "rgba(244,182,34,1)",
				strokeColor : "rgba(220,220,220,1)",
				pointColor : "rgba(220,220,220,0)",
				pointStrokeColor : "#fff",
				pointHighlightFill : "#fff",
				pointHighlightStroke : "rgba(220,220,220,1)",
				data : [<?php echo $temperatureData;?>]
			}
		]
	};
	var waterUsageDataSet = 
	{
		labels : 
		["<?php echo $labels;?>"],
		datasets : 
		[
			{
				label: "My First dataset",
				fillColor : "rgba(63,127,191,1)",
				strokeColor : "rgba(220,220,220,1)",
				pointColor : "rgba(220,220,220,0)",
				pointStrokeColor : "#fff",
				pointHighlightFill : "#fff",
				pointHighlightStroke : "rgba(220,220,220,1)",
				data : [<?php echo $waterUsageData;?>]
			}
		]
	};
	<?php
	}
	else
	{
		$chartType = "Bar";
		$result = $db->Execute("SELECT PLANT_ID, SUM(WATER_USED_MILLILITERS) AS water, AVG(MOISTURE_PERCENTAGE) AS moisture, AVG(TEMPERATURE_DEGREES_FAHRENHEIT) AS temperature FROM PlantMonitor_Data GROUP BY PLANT_ID");
		$plantIds = [];
		$waters = [];
		$moisture = [];
		$temperature = [];
		foreach($result as $key => $val)
		{
			$plantIds[] = $val['PLANT_ID'];
			$waters[] = $val['water'];
			$moisture[] = $val['moisture'];
			$temperature[] = $val['temperature'];
		}
		
		$waterUsage = array_combine($plantIds, $waters);
		$moistureAverage = array_combine($plantIds, $moisture);
		$temperatureAverage = array_combine($plantIds, $temperature);
		asort($waterUsage);
		asort($moistureAverage);
		asort($temperatureAverage);
		
		$i=0;
		foreach($waterUsage as $key => $val)
		{
			$labels .= $key;
			$waterUsageData .= $val;
			if($i<count($waterUsage)-1)
			{
				$labels .= "\",\"";
				$waterUsageData .= ",";
			}
			$i++;
		}
		
		$i=0;
		foreach($moistureAverage as $key => $val)
		{
			$moistureData .= $val;
			if($i<count($moistureAverage)-1)
			{
				$moistureData .= ",";
			}
			$i++;
		}
		
		$i=0;
		foreach($temperatureAverage as $key => $val)
		{
			$temperatureData .= $val;
			if($i<count($temperatureAverage)-1)
			{
				$temperatureData .= ",";
			}
			$i++;
		}
	?>
	var moistureDataSet = 
	{
		labels : 
		["<?php echo $labels;?>"],
		datasets : 
		[
			{
				label: "water",
				fillColor : "rgba(63,127,191,1)",
				strokeColor : "rgba(220,220,220,0.8)",
				highlightFill: "rgba(95,179,86,1)",
				highlightStroke: "rgba(220,220,220,1)",
				data : [<?php echo $moistureData;?>]
			}
		]
	};
	var temperatureDataSet = 
	{
		labels : 
		["<?php echo $labels;?>"],
		datasets : 
		[
			{
				label: "water",
				fillColor : "rgba(244,182,34,1)",
				strokeColor : "rgba(220,220,220,0.8)",
				highlightFill: "rgba(95,179,86,1)",
				highlightStroke: "rgba(220,220,220,1)",
				data : [<?php echo $temperatureData;?>]
			}
		]
	};
	var waterUsageDataSet = 
	{
		labels : 
		["<?php echo $labels;?>"],
		datasets : 
		[
			{
				label: "water",
				fillColor : "rgba(63,127,191,1)",
				strokeColor : "rgba(220,220,220,0.8)",
				highlightFill: "rgba(95,179,86,1)",
				highlightStroke: "rgba(220,220,220,1)",
				data : [<?php echo $waterUsageData;?>]
			}
		]
	};
	<?php
	}
	?>
	window.onload = function(){
		var canvasbar = document.getElementById("moistureCanvas").getContext("2d");
		window.myBar = new Chart(canvasbar).<?php echo $chartType;?>(moistureDataSet, {
			responsive : true
		});
		var canvasbar = document.getElementById("temperatureCanvas").getContext("2d");
		window.myBar = new Chart(canvasbar).<?php echo $chartType;?>(temperatureDataSet, {
			responsive : true
		});
		var canvasbar = document.getElementById("waterUsageCanvas").getContext("2d");
		window.myBar = new Chart(canvasbar).<?php echo $chartType;?>(waterUsageDataSet, {
			responsive : true
		});
		$("#LatestUpdatesTable").tablesorter(); 
	}
	</script>
	<h2>Moisture Content</h2>
	<canvas id="moistureCanvas" height="450" width="600"></canvas>
	<h2>Temperature Degress Fahrenheit</h2>
	<canvas id="temperatureCanvas" height="450" width="600"></canvas>
	<h2>Water Used (mL)</h2>
	<canvas id="waterUsageCanvas" height="450" width="600"></canvas>
	<div class="container">
	  <h2>Plant Data</h2>    
		<table id="LatestUpdatesTable" class="table table-hover tablesorter">
				<thead>
				  <tr>
					<th>Plant ID</th>
					<th>Moisture Percentage</th>
					<th>Temperature</th>
					<th>Water Added</th>
					<th>Time</th>
				  </tr>
				</thead>   
		<tbody>
		<?php
			$result = $db->Execute("SELECT PLANT_ID, MOISTURE_PERCENTAGE, TEMPERATURE_DEGREES_FAHRENHEIT, WATER_USED_MILLILITERS, TIME
									FROM PlantMonitor_Data 
									WHERE TIME IN (
										SELECT MAX(TIME)
										FROM PlantMonitor_Data
										GROUP BY PLANT_ID
									)
									GROUP BY PLANT_ID");
			foreach($result as $key => $val){
		?><tr>
			<td><a href="?id=<?php echo $val['PLANT_ID'] ?>"><?php echo $val['PLANT_ID'] ?></a></td>
			<td><?php echo  $val['MOISTURE_PERCENTAGE'] ?></td>
			<td><?php echo  $val['TEMPERATURE_DEGREES_FAHRENHEIT'] ?></td>
			<td><?php echo  $val['WATER_USED_MILLILITERS'] ?></td>
			<td><?php echo  date("F j, Y, g:i a", strtotime($val['TIME'])) ?></td>
	    </tr>
		<?php }?>
		</tbody>
	  </table>
	</div>
</div>  