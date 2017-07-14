<?php
require_once('wxwidget.php');
?><!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Environment Canada WX Widget - Custom Display of RSS Weather Data</title>
    </head>
    <body>
        <style type="text/css">
            .wxwidget { 
                font-family: helvetica;
                text-align: center;
                width: 300px;
                border: thin silver solid;
                font-weight: lighter;
                margin: 0.5em;
                padding: 0.5em;
                line-height: 150%;
            }
        </style>
        <div class="weather"><?php
            // RSS feeds of local forecasts can be found at http://weather.gc.ca/mainmenu/weather_menu_e.html, in most cases the city number can be replaced.
            // URL of RSS feed for Vancouver, BC
            $url = "http://www.weatheroffice.gc.ca/rss/city/bc-74_e.xml"; //vancouver
            //$url = "http://www.weatheroffice.gc.ca/rss/city/bc-81_e.xml"; //abbotsford

            $wxwidget = new WXWidget($url);
            echo $wxwidget->displayDiv();
        ?></div>
    </body>
</html>
