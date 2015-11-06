<?php
	require_once("sqlConnection.php");
?>

<!doctype html>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <title>Plant Monitor</title>

        <!-- Style sheets -->
        <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="css/style.css">
        <link rel="stylesheet" type="text/css" href="css/bootstrap-theme.min.css">
        
        <!-- Javascript includes -->
        <script type="text/javascript" src="js/Chart.Core.js"></script>
        <script type="text/javascript" src="js/Chart.Bar.js"></script>
        <script type="text/javascript" src="js/Chart.Line.js"></script>
        <script type="text/javascript" src="js/jquery-latest.js"></script>
        <script type="text/javascript" src="js/jquery.tablesorter.min.js"></script>
        <script type="text/javascript" src="js/jquery-2.1.3.min.js"></script>
        <script type="text/javascript" src="js/bootstrap.min.js"></script>
        <script type="text/javascript" src="js/jquery.metadata.js"></script>
        <style>
            .graphs {
                width: 75%;
                margin-left: 12.5%;
            }
        </style>
    </head>
    <body>
        <div id="HTMLBlock" class="graphs">
            <?php 
                $NUM_LABELS = 20;

                // query the database for all totalled/averaged sensor data from every plant
                $result = $db->query("SELECT PLANT_ID, " .
                                            "SUM(WATER_USED_MILLILITERS) AS water, " .
                                            "AVG(MOISTURE_PERCENTAGE) AS moisture, " .
                                            "AVG(LEAF_THICKNESS) AS thickness " .
                                     "FROM PlantMonitor_Data " .
                                     "GROUP BY PLANT_ID");

                // put every column into an array
                $plantIds = [];
                $waters = [];
                $moisture = [];
                $thickness = [];
                foreach($result as $key => $val)
                {
                    $plantIds[] = $val['PLANT_ID'];
                    $waters[] = $val['water'];
                    $moisture[] = $val['moisture'];
                    $thickness[] = $val['thickness'];
                }

                // use plant ids as keys with sensor data as values
                $waterUsage = array_combine($plantIds, $waters);
                $moistureAverage = array_combine($plantIds, $moisture);
                $thicknessAverage = array_combine($plantIds, $thickness);

                // sort each array to properly order the graph
                asort($waterUsage);
                asort($moistureAverage);
                asort($thicknessAverage);
            ?>
            <script>
                var moistureDataSet = 
                {
                    labels : 
                    <?php echo json_encode(array_keys($moistureAverage));?>,
                    datasets : 
                    [
                        {
                            fillColor : "rgba(63,127,191,1)",
                            strokeColor : "rgba(220,220,220,0.8)",
                            highlightFill: "rgba(95,179,86,1)",
                            highlightStroke: "rgba(220,220,220,1)",
                            data : <?php echo json_encode(array_values($moistureAverage));?>
                        }
                    ]
                };
                var thicknessDataSet = 
                {
                    labels : 
                    <?php echo json_encode(array_keys($thicknessAverage));?>,
                    datasets : 
                    [
                        {
                            fillColor : "rgba(244,182,34,1)",
                            strokeColor : "rgba(220,220,220,0.8)",
                            highlightFill: "rgba(95,179,86,1)",
                            highlightStroke: "rgba(220,220,220,1)",
                            data : <?php echo json_encode(array_values($thicknessAverage));?>
                        }
                    ]
                };
                var waterUsageDataSet = 
                {
                    labels : 
                    <?php echo json_encode(array_keys($waterUsage));?>,
                    datasets : 
                    [
                        {
                            fillColor : "rgba(63,127,191,1)",
                            strokeColor : "rgba(220,220,220,0.8)",
                            highlightFill: "rgba(95,179,86,1)",
                            highlightStroke: "rgba(220,220,220,1)",
                            data : <?php echo json_encode(array_values($waterUsage));?>
                        }
                    ]
                };
                window.onload = function(){
                    var canvasbar = document.getElementById("moistureCanvas").getContext("2d");
                    window.myBar = new Chart(canvasbar).Bar(moistureDataSet, {
                        responsive : true
                    });
                    var canvasbar = document.getElementById("thicknessCanvas").getContext("2d");
                    window.myBar = new Chart(canvasbar).Bar(thicknessDataSet, {
                        responsive : true
                    });
                    var canvasbar = document.getElementById("waterUsageCanvas").getContext("2d");
                    window.myBar = new Chart(canvasbar).Bar(waterUsageDataSet, {
                        responsive : true
                    });
                    $("#LatestUpdatesTable").tablesorter(); 
                };
            </script>
            <h2>Moisture Content</h2>
            <canvas id="moistureCanvas"></canvas>
            <h2>Thickness Millimeters</h2>
            <canvas id="thicknessCanvas"></canvas>
            <h2>Water Used (mL)</h2>
            <canvas id="waterUsageCanvas"></canvas>
            <div class="container">
                <h2>Latest Readings</h2>
                <table id="LatestUpdatesTable" class="table table-hover tablesorter">
                    <thead>
                        <tr>
                            <th>Plant ID</th>
                            <th>Moisture Percentage</th>
                            <th>Thickness</th>
                            <th>Water Added</th>
                            <th>Time</th>
                        </tr>
                    </thead>   
                    <tbody>
                    <?php
                        // select the latest readings from each plant
                        $result = $db->query("SELECT PLANT_ID, " .
                                                    "MOISTURE_PERCENTAGE, " .
                                                    "LEAF_THICKNESS, " .
                                                    "WATER_USED_MILLILITERS, " .
                                                    "TIME " .
                                             "FROM PlantMonitor_Data " .
                                             "WHERE TIME IN ( " .
                                                    "SELECT MAX(TIME) " .
                                                    "FROM PlantMonitor_Data " .
                                                    "GROUP BY PLANT_ID " .
                                             ") " .
                                             "GROUP BY PLANT_ID");
                        
                        // display each reading as a row in the table
                        foreach($result as $key => $val)
                        {
                    ?>
                        <tr>
                            <td>
                                <a href="/singlePlant.php?id=<?php echo $val['PLANT_ID'] ?>"><?php echo $val['PLANT_ID'] ?></a>
                            </td>
                            <td><?php echo  $val['MOISTURE_PERCENTAGE'] ?></td>
                            <td><?php echo  $val['LEAF_THICKNESS'] ?></td>
                            <td><?php echo  $val['WATER_USED_MILLILITERS'] ?></td>
                            <td><?php echo  date("F j, Y, g:i a", strtotime($val['TIME'])) ?></td>
                        </tr>
                    <?php 
                    }
                    ?>
                    </tbody>
                </table>
            </div>
            <div id="copyright">
                <div class="repository">
                    <div class="left">
                        <p id="copy">This site © 2015 Iowa State University. All Rights Reserved.</p>
                    </div>
                    <div class="right">
                        <a target="_blank" href="http://dec1514.sd.ece.iastate.edu/">Senior Design Group DEC15-14</a>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
