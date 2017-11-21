<html>
<h2>IP Information System</h2>
</html>

<?php
//IP-API.com
class myProject {
    static $fields = 65535;     // refer to http://ip-api.com/docs/api:returned_values#field_generator
    static $use_xcache = false;  // set this to false unless you have XCache installed (http://xcache.lighttpd.net/)
    static $api = "http://ip-api.com/php/";

    public $status, $country, $countryCode, $region, $regionName, $city, $zip, $lat, $lon, $timezone, $isp, $org, $as, $reverse, $query, $message;

    public static function query($q) {
        $data = self::communicate($q);
        $result = new static;
        foreach($data as $key => $val) {
            $result->$key = $val;
        }
        return $result;
    }

    private function communicate($q) {
        $q_hash = md5('ipapi'.$q);
        if(self::$use_xcache && xcache_isset($q_hash)) {
            return xcache_get($q_hash);
        }
        if(is_callable('curl_init')) {
            $c = curl_init();
            curl_setopt($c, CURLOPT_URL, self::$api.$q.'?fields='.self::$fields);
            curl_setopt($c, CURLOPT_HEADER, false);
            curl_setopt($c, CURLOPT_TIMEOUT, 30);
            curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
            $result_array = unserialize(curl_exec($c));
            curl_close($c);
        } else {
            $result_array = unserialize(file_get_contents(self::$api.$q.'?fields='.self::$fields));
        }
        if(self::$use_xcache) {
            xcache_set($q_hash, $result_array, 86400);
        }
        return $result_array;
    }
}
//Get user IP Address
$ippr = @$query->query;
$query = myProject::query($ippr);
//Define variables from IP-API
$status  = $query->status;
$country = $query->country;
$countryCode = $query->countryCode;
$region = $query->region;
$regionName = $query->regionName;
$city = $query->city;
$zip = $query->zip;
$timezone = $query->timezone;
$isp = $query->isp;
$org = $query->org;
$provider = $query->as;
$reverse = $query->reverse;
$ip = $query->query;
$message = $query->message;
$lon = $query->lon;
$lat = $query->lat;

//Connect to database and store values
$DB_Conn = mysql_connect("localhost", "admin", "password");
if ($DB_Conn === false)
  echo"<p>Unable to conenct to the database server.</p>"
  . "<p>Error code " . mysql_errno()
  . ": " . mysql_error() . "</p>";
else {
  $DB_Name = "ipsys";
  mysql_select_db($DB_Name, $DB_Conn);
  $Table_Name = "colec";
  $SQL_String = "insert into $Table_Name(status, country, countryCode, region, regionName, city, zip, lat, lon, timezone, isp, org, provider, reverse, ip, message)
  values('$status', '$country', '$countryCode', '$region', '$regionName', '$city', '$zip', '$lat', '$lon', '$timezone', '$isp', '$org', '$provider', '$reverse', '$ip', '$message')";
  $Query_Result = @mysql_query($SQL_String, $DB_Conn);

if($Query_Result === false)
  echo"<p>Unable to execute the query.</p>"
  . "<p>Error code " . mysql_errno($DB_Conn)
  . ": " . mysql_error($DB_Conn) . "</p>";
else
  echo"<p>Your Information Has Been Submited To The Database!</p>";
}

echo"<p> Your Browser Information: ";
echo $_SERVER['HTTP_USER_AGENT'] . "\n\n </p>";
$browser = get_browser(null, true);
?>

<fieldset>
 <form>
  <p>Status : <?php echo $status ?> </p>
  <p>Country : <?php echo $country ?> </p>
  <p>Country Code: <?php echo $countryCode ?> </p>
  <p>Region : <?php echo $region?> </p>
  <p>Region Name : <?php echo $regionName?> </p>
  <p>City : <?php echo $city?> </p>
  <p>Zip Code : <?php echo $zip?> </p>
  <p>Timezone : <?php echo $timezone?> </p>
  <p>Internet Service Provider : <?php echo $isp?> </p>
  <p>Organization : <?php echo $org?> </p>
  <p>Provider : <?php echo $provider?> </p>
  <p>Reverse : <?php echo $reverse?> </p>
  <p>IP Address : <?php echo $ip?> </p>
  <p>Message : <?php echo $message?> </p>
  <p>Latitude : <?php echo $lat ?> </p>
  <p>Longitude : <?php echo $lon ?> </p>
 </form>
</fieldset>

<!--Google Maps API -->
<fieldset>
<!DOCTYPE html >
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
    <title>IP Information System</title>
    <style>
      /* Always set the map height explicitly to define the size of the div
       * element that contains the map. */
      #map {
        height: 100%;
      }
      /* Optional: Makes the sample page fill the window. */
      html, body {
        height: 100%;
        margin: 0;
        padding: 0;
      }
    </style>
  </head>

  <body>
    <div id="map"></div>

    <script>
      var customLabel = {
        restaurant: {
          label: 'R'
        },
        bar: {
          label: 'B'
        }
      };

      function initMap() {

        var lat = <?php echo $lat ?>;
        var lon = <?php echo $lon ?>;

        var map = new google.maps.Map(document.getElementById('map'), {
          center: new google.maps.LatLng(lat, lon),
          zoom: 12,
        });
        var infoWindow = new google.maps.InfoWindow;
          downloadUrl('https://storage.googleapis.com/mapsdevsite/json/mapmarkers2.xml', function(data) {
            var xml = data.responseXML;
            var markers = xml.documentElement.getElementsByTagName('marker');
            Array.prototype.forEach.call(markers, function(markerElem) {
              var id = markerElem.getAttribute('id');
              var name = markerElem.getAttribute('name');
              var address = markerElem.getAttribute('address');
              var type = markerElem.getAttribute('type');
              var point = new google.maps.LatLng(
                  parseFloat(markerElem.getAttribute('lat')),
                  parseFloat(markerElem.getAttribute('lng')));

              var infowincontent = document.createElement('div');
              var strong = document.createElement('strong');
              strong.textContent = name
              infowincontent.appendChild(strong);
              infowincontent.appendChild(document.createElement('br'));

              var text = document.createElement('text');
              text.textContent = address
              infowincontent.appendChild(text);
              var icon = customLabel[type] || {};
              var marker = new google.maps.Marker({
                map: map,
                position: new google.maps.LatLng([lat], [lon]),
              });
              marker.addListener('click', function() {
                infoWindow.setContent(infowincontent);
                infoWindow.open(map, marker);
              });
            });
          });
        }

      function downloadUrl(url, callback) {
        var request = window.ActiveXObject ?
            new ActiveXObject('Microsoft.XMLHTTP') :
            new XMLHttpRequest;

        request.onreadystatechange = function() {
          if (request.readyState == 4) {
            request.onreadystatechange = doNothing;
            callback(request, request.status);
          }
        };

        request.open('GET', url, true);
        request.send(null);
      }

      function doNothing() {}
    </script>
    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB3x7mRn3mq9NlzHvZetHhgvYWfwIbO9YM&callback=initMap">
    </script>
  </body>
</html>
</fieldset>
