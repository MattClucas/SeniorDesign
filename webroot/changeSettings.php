<?php
    $message = '';

    // appends $msg to $message which is then displayed at the bottom
    function addMsg($msg)
    {
        global $message;
        $message .= $msg . '</br>';
    }

    $settingsRoot = '/plant/settings/';
    if (!file_exists($settingsRoot) && !mkdir($settingsRoot, 0775))
    {
        die("Error: Unable to create /plant/settings/ directory.");
    }

    // set the numPlants.txt file if the post variable is set
    $numPlantsFile = $settingsRoot . 'num_plants.txt';
    $numPlants = $_POST['numPlants'];
    $numPlants = intval($numPlants);
    if (isset($numPlants) && !empty($numPlants) && is_int($numPlants))
    {
        if(!file_put_contents($numPlantsFile, ''.$numPlants))
        {
            addMsg('FAILED TO WRITE ' . $numPlants . ' TO ' . $waterContentFile);
        }
        else
        {
            addMsg('Set number of plants to ' . $numPlants);
        }
    }
    // the variable was not set, we need to read the numPlants.txt file to get numPlants
    else
    {
        addMsg("No value set for number of plants or number of plants was not a number, not writing number of plants.");
        $numPlants = file_get_contents($numPlantsFile);
        addMsg('Read number of plants from file.');
        $numPlants = intval($numPlants);
    }

    $waterContentFile = $settingsRoot . 'water_content.txt';
    if (is_int($numPlants))
    {
        $waterContentStr = '';
        // see if the waterContent array was set and try to write to file
        for($i = 0; $i < $numPlants; $i++)
        {
            // get the water content for this plant
            $content = $_POST['waterContent' . $i];
            // cast to an int
            $content = intval($content);

            // if this plants water content is not set or is not a number
            // there is an error, do not write to file
            if (!isset($content) || empty($content) || !is_int($content))
            {
                $waterContentStr = null;
                addMsg("Water Content not set or not a number for plant ".$i.". Not writing water content at all.");
                break;
            }

            // concatenate waterContent String to write to file
            // appending a ',' on all but the last entries
            if ($i < $numPlants - 1)
            {
                $waterContentStr .= $content . ',';
            }
            else
            {
                $waterContentStr .= $content;
            }
        }

        // write the string to the end of the water content file
        if (!empty($waterContentStr))
        {
            // append the new watercontent on the last line of the file
            if(!file_put_contents($waterContentFile, "\n" . $waterContentStr, FILE_APPEND))
            {
                addMsg('FAILED TO WRITE ' . $waterContentStr . ' TO ' . $waterContentFile);
            }
            else
            {
                addMsg('Wrote ' . $waterContentStr . ' to ' . $waterContentFile . '.');
            }

            // create an array of water contents from the string
            $waterContent = explode(',', $waterContentStr);
        }
        else
        {
            // we need to read from the water content file to get the water contents
            // read water contents file
            $waterContents = file_get_contents($waterContentFile);

            // separate into array of lines
            $waterContents = explode("\n", $waterContents);

            // get the latest good line in the file
            for ($i = count($waterContents) - 1; $i >= 0; $i--)
            {
                // get the last line
                $waterContent = $waterContents[$i];

                // break the line up by ',' delimiter character
                $waterContent = explode(',', $waterContent);

                // check first that there is an entry for each plant,
                // else there is an error and we need to try the next line
                if (count($waterContent) != $numPlants)
                {
                    continue;
                }

                // check that all entries are valid in this line
                $allEntriesAreNumber = true;
                for ($j = 0; $j < $numPlants; $j++)
                {
                    // if the entry is not a number it is invalid
                    $waterContent[$j] = intval($waterContent[$j]);
                    if (!is_int($waterContent[$j]))
                    {
                        $allEntriesAreNumber = false;
                        break;
                    }
                }
                if (!$allEntriesAreNumber)
                {
                    continue;
                }

                // at this point the line is valid so break
                addMsg('Using water content ' . $waterContents[$i] . ' from line ' . $i . ' of ' . $waterContentFile);
                break;
            }
        }
    }
    else
    {
        addMsg("Number of plants is not a number!");
    }

    // if there is no waterConent just intialize it to an empty array
    if (!isset($waterContent) || empty($waterContent))
    {
        addMsg('No water content set.');
        $waterContent = [];
    }
?>

<html>
    <head>
        <title>Change Settings</title>
    </head>
    <body>
        <h1>Change Plant Settings</h1>
        <a href="index.php">Home</a></br></br>
        <form action='changeSettings.php' method='post'>
            <label>Number of Plants: </label>
            <input type='text' name='numPlants' value="<?php echo $numPlants;?>"/></br>

            <?php
            $contentCount = count($waterContent);
            for($i=0;$i<$numPlants;$i++)
            {
                $content = $contentCount >= $i ? $waterContent[$i] : '';
                echo '<label>Threshold for plant' . $i . ' </label>';
                echo '<input type="text" name="waterContent'.$i.'" value="' . $content . '"/></br>';
            }
            ?>

            <input type='submit'/>
        </form>
        <div id="messageDiv"><?php echo $message;?></div>
    </body>
</html>
