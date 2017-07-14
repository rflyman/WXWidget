<?php
require_once('magpierss-0.72/rss_fetch.inc');

class WXWidget {

    var $rss_url;
    var $rss;
    var $condition;
    var $observed_at;
    var $temperature;
    var $pressure;
    var $humidity;
    var $dewpoint;
    var $wind;
    var $air_quality_health_index;
    var $url;

    //Constructor for WXWidget Param: $url is the URL to the RSS feed from Environment Canada.
    public function __construct($url) {
        $this->rss_url = $url;
        //Fetch the RSS feed for the URL
        $this->rss = fetch_rss($url);

        if (!$this->initializeFromRSS()) {
            //Failed to initialize
            throw new Exception("Weather Data Currently Unavailable");
        }
    }

    /* Assign provided data from RSS feed to WXWidget object. RSS provides details such as the following:
     */

    private function initializeFromRSS() {

        foreach ($this->rss->items as $item) {

            $title = $item['title'];

            if (strstr($title, "Current Conditions:")) {
                /* echo "<hr/>";
                  print_r($item);
                  echo "<br/>"; */
                $this->url = $item['link_'];

                //Parse out each value from the summary, also remove all bold tags
                $description = str_replace("<b>", "", str_replace("</b>", "", $item['summary']));
                //Separate each line
                $lines = explode("<br/>", $description);

                //Examine each line
                $broken_lines = $this->breakLines($lines);

                //Trim all keys and values, Some lines were separated that require a : and should be rejoined.
                $repaired_lines = $this->rejoinTrimmed($broken_lines);

                //Assign all the parsed variables, variables are not always available. Check must be performed to see if data exists for each variable.
                $this->setCondition($repaired_lines);
                $this->setObservedAt($repaired_lines);
                $this->setTemperature($repaired_lines);
                $this->setPressure($repaired_lines);
                $this->setHumidity($repaired_lines);
                $this->setDewpoint($repaired_lines);
                $this->setWind($repaired_lines);
                $this->setAirQualityHealthIndex($repaired_lines);
            }
        }
        return true;
    }

    //Set the condition from array
    private function setCondition($repaired_lines) {
        if (array_key_exists('Condition', $repaired_lines)) {
            $this->condition = $repaired_lines['Condition'];
        } else {
            $this->condition = "";
        }
    }

    //Set the observed_at from array
    private function setObservedAt($repaired_lines) {
        if (array_key_exists('Observed at', $repaired_lines)) {
            $this->observed_at = $repaired_lines['Observed at'];
        } else {
            $this->observed_at = "";
        }
    }

    //Set the Temperature from array
    private function setTemperature($repaired_lines) {
        if (array_key_exists('Temperature', $repaired_lines)) {
            $this->temperature = $repaired_lines['Temperature'];
        } else {
            $this->temperature = "";
        }
    }

    //Set the Pressure from array
    private function setPressure($repaired_lines) {
        if (array_key_exists('Pressure', $repaired_lines)) {
            $this->pressure = $repaired_lines['Pressure'];
        } else {
            $this->pressure = "";
        }
    }

    //Set the Humidity from array
    private function setHumidity($repaired_lines) {
        if (array_key_exists('Humidity', $repaired_lines)) {
            $this->humidity = $repaired_lines['Humidity'];
        } else {
            $this->humidity = "";
        }
    }

    //Set the Dewpoint from array
    private function setDewpoint($repaired_lines) {
        if (array_key_exists('Dewpoint', $repaired_lines)) {
            $this->dewpoint = $repaired_lines['Dewpoint'];
        } else {
            $this->dewpoint = "";
        }
    }

    //Set the Wind from array
    private function setWind($repaired_lines) {
        if (array_key_exists('Wind', $repaired_lines)) {
            $this->wind = $repaired_lines['Wind'];
        } else {
            $this->wind = "";
        }
    }

    //Set the Air Quality Health Index from array
    private function setAirQualityHealthIndex($repaired_lines) {
        if (array_key_exists('Air Quality Health Index', $repaired_lines)) {
            $this->air_quality_health_index = $repaired_lines['Air Quality Health Index'];
        } else {
            $this->air_quality_health_index = "";
        }
    }

    //Break up lines and return array of each line
    private function breakLines($lines) {
        $broken_lines = array();
        foreach ($lines as $line) {
            //Separate attribute and value pair
            $broken_lines[] = explode(":", $line);
        }
        return $broken_lines;
    }

    //Trim all keys and values, Some lines were separated that require a : and should be rejoined.
    private function rejoinTrimmed($broken_lines) {
        $repaired_lines = array();
        foreach ($broken_lines as $tmp_line) {
            //Check if the line is split into a further array
            if (is_array($tmp_line) && count($tmp_line) > 2) {
                //Trim each and rejoin. Only ever encountered a single split per line.
                //Could be redone iterating through all items and joining accordingly
                $repaired_lines[trim($tmp_line[0])] = trim($tmp_line[1]) . ":" . trim($tmp_line[2]);
            } else if (count($tmp_line) > 1) {
                $repaired_lines[trim($tmp_line[0])] = trim($tmp_line[1]);
            }
        }
        return $repaired_lines;
    }

    //Determine if it is night or day
    private function isNight() {
        date_default_timezone_set('America/Los_Angeles');
        $localtime_assoc = localtime(time(), true);
        $hour = $localtime_assoc['tm_hour'];
        if ($hour > 21 || $hour < 5) {
            return true;
        }
        return false;
    }

    //Determine which image to load based on current condition, returns as a string
    //Good example of a switch statement use case
    //Could use more icon images
    private function getConditionImage() {
        //Is it night or day
        $night = $this->isNight();
        switch ($this->condition) {
            case "Sunny":
            case "Clear":
            case "Mainly Clear":

                if ($night) {
                    //It is night time so a sun doesn't make sense...
                    //Assumes server time is local at this point.
                    return "<img src=\"Image/weatherIcons/nightimages/clearnight.png\" alt=\"Clear\" border=\"0\"/>"; //also breaks so no need to add breaks.
                } else {
                    return "<img src=\"Image/weatherIcons/images/clear.png\" alt=\"Clear\" border=\"0\"/>"; //also breaks so no need to add breaks.
                }
            case "Partly Cloudy":
                if ($night) {
                    //It is night time so a sun doesn't make sense...
                    //Assumes server time is local at this point.
                    return "<img src=\"Image/weatherIcons/nightimages/fewclouds.png\" alt=\"Partly Cloudy\" border=\"0\"/>"; //also breaks so no need to add breaks.
                } else {
                    return "<img src=\"Image/weatherIcons/images/sunny-with-cloudy-periods.png\" alt=\"Partly Cloudy\" border=\"0\"/>"; //also breaks so no need to add breaks.
                }
            case "Sunny Periods":
            case "Mainly Sunny":
                return "<img src=\"Image/weatherIcons/images/sunny-with-cloudy-periods.png\" alt=\"" . $this->condition . "\" border=\"0\"/>"; //also breaks so no need to add breaks.
            case "Overcast":
            case "Cloudy":
            case "Fog":
            case "Shallow Fog":
            case "Fog Depositing Ice":
            case "Fog Patches":
            case "Mostly Cloudy":
            case "Distant Precipitation":
                return "<img src=\"Image/weatherIcons/images/overcast.png\" alt=\"" . $this->condition . "\" border=\"0\"/>"; //also breaks so no need to add breaks.
            case "Light Rain":
            case "Rain":
            case "Light Rain Showers":
            case "Rain Showers":
            case "Rainshower":
            case "Showers of Rain":
            case "Light Rainshower":
            case "Light Drizzle":
            case "Light Rainshowers":
            case "Heavy Rainshowers":
            case "Heavy Rain Showers":
            case "Heavy Rain":
                return "<img src=\"Image/weatherIcons/images/shra.png\" alt=\"" . $this->condition . "\" border=\"0\"/>"; //also breaks so no need to add breaks.
            case "Light Snow":
            case "Snow Grains":
            case "Light Snow Pellets":
            case "Snow":
            case "Light Freezing Drizzle":
                return "<img src=\"Image/weatherIcons/images/snow.png\" alt=\"" . $this->condition . "\" border=\"0\"/>"; //also breaks so no need to add breaks.
            case "Light Snow Showers":
                if ($night) {
                    return "<img src=\"Image/weatherIcons/nightimages/snowshowersnight.png\" alt=\"" . $this->condition . "\" border=\"0\"/>"; //also breaks so no need to add breaks.
                } else {
                    return "<img src=\"Image/weatherIcons/images/snowshowers.png\" alt=\"" . $this->condition . "\" border=\"0\"/>"; //also breaks so no need to add breaks.
                }
                return ""; //also breaks so no need to add breaks.
            case "Mixed Rain and Snow":
            case "Rain and Snow":
            case "Rain and Snow Mixed":
                return "<img src=\"Image/weatherIcons/images/rasn.png\" alt=\"" . $this->condition . "\" border=\"0\"/>"; //also breaks so no need to add breaks.
            case "Thunderstorm with Rain":
            case "Thunderstorm":
            case "Thunderstorm with Hail":
                if ($night) {
                    return "<img src=\"Image/weatherIcons/nightimages/thundershowersnight.png\" alt=\"" . $this->condition . "\" border=\"0\"/>"; //also breaks so no need to add breaks.
                } else {
                    return "<img src=\"Image/weatherIcons/images/periods-of-lightning.png\" alt=\"" . $this->condition . "\" border=\"0\"/>"; //also breaks so no need to add breaks.
                }
            case "Recent Thunderstorm":
                return "<img src=\"Image/weatherIcons/images/lightning.png\" alt=\"" . $this->condition . "\" border=\"0\"/>"; //also breaks so no need to add breaks.
            default:
                return ""; //also breaks so no need to add breaks.
        }
    }

    //Returns HTML of div to display a simple example weather widget
    public function displayDiv() {
        ?>
        <div class="wxwidget">
            <strong><?= $this->observed_at; ?></strong><br/> <?php
            //calculate the correct image and display for the weather condition
            echo $this->getConditionImage();
            ?><br/><strong>Condition:</strong> <?= $this->condition; ?><br/>
            <strong>Temperature:</strong> <?= $this->temperature; ?><br/></strong>
        <strong>Wind:</strong> <?= $this->wind; ?><br/>
        <strong>Humidity:</strong> <?= $this->humidity; ?><br/>
        <strong>Pressure:</strong> <?= $this->pressure; ?><br/>
        <strong>Air Quality Health Index:</strong> <?= $this->air_quality_health_index; ?><br/>
        <p>Click <a href="<?= $this->url; ?>" target="_blank" title="More Weather">here</a> for more weather</p>
        <p style="font-size: 80%;">*based on Environment Canada data.</p>
        </div>
        <?php
    }

}
?>