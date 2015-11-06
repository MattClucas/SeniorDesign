<?php
	$settingsRoot = '/home/mike/settings/';
	if (isset($_POST['numPlants']) && $_POST['numPlants'] != null && $_POST['numPlants'] != '')
	{
		$filename = $settingsRoot . 'num_plants.txt';
		if(!file_put_contents($filename, $_POST['numPlants']))
		{
			echo 'FAILED TO WRITE ' . $_POST['numPlants'] . ' TO ' . $filename;
		}
	}
	
	if (isset($_POST['waterContent']) && $_POST['waterContent'] != null && $_POST['waterContent'] != '')
	{
		$filename = $settingsRoot . 'water_content.txt';
		if(!file_put_contents($filename, $_POST['waterContent'] . '
', FILE_APPEND))
		{
			echo 'FAILED TO WRITE ' . $_POST['waterContent'] . ' TO ' . $filename;
		}
	}
?>

<html>
	<head>
		<title>Change Settings</title>
	</head>
	<body>
		<h1>Change Plant Settings</h1>
		<form action='changeSettings.php' method='post'>
			Number of Plants: <input type='text' name='numPlants'/></br>
			Comma Separated Water Content List: <input type='text' name='waterContent'/></br>
			<input type='submit'/>
		</form>
	</body>
</html>