<?php
	$db = new mysqli("localhost", "michaeldmead", "LmXRrV6wVEwhszCd", "michaeldmead");
    if($db->connection_error)
    {
        die("connection failed: " . $db->connection_error);
    }
?>

<!doctype html>
<html>
    <head>
        <meta charset="utf-8">	
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <title>Plant Monitor</title>
        
        <!-- Style sheets -->
        <link rel="stylesheet" type="text/css" href="/grapher/css/bootstrap.css">
        <link rel="stylesheet" type="text/css" href="/grapher/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="/grapher/css/style.css">
        <link rel="stylesheet" type="text/css" href="/grapher/css/bootstrap-theme.min.css">
        <link rel="stylesheet" type="text/css" href="/grapher/css/bootstrap-theme.css">
        
        <!-- Javascript includes -->
        <script type="text/javascript" src="/grapher/js/view.js"></script>
        <script type="text/javascript" src="/grapher/js/Chart.Radar.js"></script>
        <script type="text/javascript" src="/grapher/js/Chart.Doughnut.js"></script>
        <script type="text/javascript" src="/grapher/js/Chart.Core.js"></script>
        <script type="text/javascript" src="/grapher/js/Chart.Bar.js"></script>
        <script type="text/javascript" src="/grapher/js/Chart.Line.js"></script>
        <script type="text/javascript" src="/grapher/js/jquery-latest.js"></script>
        <script type="text/javascript" src="/grapher/js/jquery.tablesorter.min.js"></script>
        <script type="text/javascript" src="/grapher/js/Chart.PolarArea.js"></script>
        <script type="text/javascript" src="/grapher/js/jquery-2.1.3.min.js"></script>
        <script type="text/javascript" src="/grapher/js/npm.js"></script>
        <script type="text/javascript" src="/grapher/js/bootstrap.js"></script>
        <script type="text/javascript" src="/grapher/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="/grapher/js/jquery.metadata.js"></script>
        <script type="text/javascript" src="/grapher/js/jquery.tablesorter.js"></script>
        <link rel="stylesheet" type="text/css" media="screen" href="/themes/nashuarpc_theme/style.css">
    </head>
    <body>
        <!-- hello from jacob -->
        <div id="package">
            <div id="header">
                <div id="navigation">
                    <div class="repository">
                        <ul class="nav">
                            <li class="">
                                <a href="/" target="_self" class="">Home</a>
                            </li>
                            <li class="">
                                <a href="/remote/" target="_self" class="">Remote</a>
                            </li>
                            <li class="nav-selected nav-path-selected">
                                <a href="/graphing/" target="_self" class="nav-selected nav-path-selected">Graphing</a>
                            </li>
                        </ul>				
                    </div>
                </div>
            </div>
            <div id="container">
                <div class="repository">
                    <div id="content" class="wide">
                        <div id="HTMLBlock" class="HTMLBlock">
                            <script>
                            <?php 
                                $NUM_LABELS = 20;
                                echo $content;
                                
                                $labels = "";
                                $moistureData = "";
                                $thicknessData = "";
                                $waterUsageData = "";
                                $chartType = "";
                                if (isset($_GET['id']) && $_GET['id'] > 0)
                                {
                                    $chartType = "Line";
                                    $id = $db->real_escape_string($_GET['id']);
                                    $result = $db->query("SELECT * FROM `PlantMonitor_Data` WHERE `PLANT_ID` = '" . $id . "' ORDER BY TIME");
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
                                        $thicknessData .= $val['LEAF_THICKNESS'];
                                        $waterUsageData .= $val['WATER_USED_MILLILITERS'];
                                        if($i<$resultLen-1)
                                        {
                                            $labels .= "\",\"";
                                            $moistureData .= ",";
                                            $thicknessData .= ",";
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
                                var thicknessDataSet = 
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
                                            data : [<?php echo $thicknessData;?>]
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
                                    $result = $db->query("SELECT PLANT_ID, SUM(WATER_USED_MILLILITERS) AS water, AVG(MOISTURE_PERCENTAGE) AS moisture, AVG(LEAF_THICKNESS) AS thickness FROM PlantMonitor_Data GROUP BY PLANT_ID");
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
                                    
                                    $waterUsage = array_combine($plantIds, $waters);
                                    $moistureAverage = array_combine($plantIds, $moisture);
                                    $temperatureAverage = array_combine($plantIds, $thickness);
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
                                        $thicknessData .= $val;
                                        if($i<count($temperatureAverage)-1)
                                        {
                                            $thicknessData .= ",";
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
                                var thicknessDataSet = 
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
                                            data : [<?php echo $thicknessData;?>]
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
                                    window.myBar = new Chart(canvasbar).<?php echo $chartType;?>(thicknessDataSet, {
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
                                <h2>Thickness Millimeters</h2>
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
                                                <th>Thickness</th>
                                                <th>Water Added</th>
                                                <th>Time</th>
                                              </tr>
                                            </thead>   
                                    <tbody>
                                    <?php
                                        $result = $db->query("SELECT PLANT_ID, MOISTURE_PERCENTAGE, LEAF_THICKNESS, WATER_USED_MILLILITERS, TIME
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
                                        <td><?php echo  $val['LEAF_THICKNESS'] ?></td>
                                        <td><?php echo  $val['WATER_USED_MILLILITERS'] ?></td>
                                        <td><?php echo  date("F j, Y, g:i a", strtotime($val['TIME'])) ?></td>
                                    </tr>
                                    <?php }?>
                                    </tbody>
                                  </table>
                                </div>
                                <div id="copyright">
                                <div class="repository">
                                    <div class="left">
                                        <p id="copy">This site © 2015 Iowa State University. All Rights Reserved.</p>
                                        <div class="clear"></div>
                                    </div>
                                    <div class="right">
                                        <a target="_blank" href="http://dec1514.sd.ece.iastate.edu/">Senior Design Group DEC15-14</a>
                                        <div class="clear"></div>
                                    </div>
                                    <div class="clear"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>

</html>
