<!doctype html>
<html lang="hr">
	<head>
		<meta charset="utf-8">
		<title>HomeBrain</title>
		<link href="css/jquery-ui.min.css" rel="stylesheet">
		<link href="css/c3.css" rel="stylesheet" type="text/css">

		<script src="js/jquery.min.js"></script>
		<script src="js/jquery-ui.min.js"></script>

		<script src="js/d3.min.js" charset="utf-8"></script>
		<script src="js/c3.min.js"></script>


		<script>		
			 $(function() {
				$( "#tabs" ).tabs();
			 });

			$(document).ready(function(){
			});
		</script>

		<style>
			table.center {
			  margin-left: auto;
			  margin-right: auto;
			  width: 95%
			}
		</style>
	</head>
   <body style="background-color: #E0E0E0;">
      <div id="tabs">
         <ul>
            <li><a href="#tab-heating">Grijanje</a></li>
            <li><a href="#tab-lan">LAN</a></li>
         </ul>

<!- ----- LAN ---------------------------------------------------------------------------------------- ->
         <div id="tab-lan">
            <?php
            error_reporting(E_ERROR | E_WARNING | E_PARSE);

            $path = str_replace('/www/web', '', dirname(__FILE__));

            $db = new SQLite3($path .'/var/lan.db'); 

            $sql = "SELECT mac, name, heating, allwaysOn FROM lanDevices 
                      WHERE (allwaysOn IS NOT NULL AND heating IS NOT NULL)
                        ORDER BY heating DESC;";
            $result = $db->query($sql);
            while ($row = $result->fetchArray(SQLITE3_ASSOC))
            {
              $devices[$row['mac']]['name']      = $row['name'];
              $devices[$row['mac']]['live']      = false;
              $devices[$row['mac']]['allwaysOn'] = $row['allwaysOn'];
              
            }

            $sql = "SELECT DISTINCT lanDevices.mac, lanLog.ip FROM lanDevices, lanLog 
                      WHERE lanDevices.mac = lanLog.mac
                        AND (lanDevices.allwaysOn IS NOT NULL AND lanDevices.heating IS NOT NULL)
                        AND (lanLog.stop IS NULL OR stop > datetime(datetime('now', 'localtime'), '-10 minutes'));";
            $result = $db->query($sql);
            while ($row = $result->fetchArray(SQLITE3_ASSOC))
            {
              $devices[$row['mac']]['ip'] = $row['ip'];
              $devices[$row['mac']]['live'] = true;
            }

            echo "\n<table class=\"center\">";
            foreach ( $devices as $mac => $device ) {
              if ( $device['live'] === false ) $class ="ui-state-error";
              else $class ="";

              echo "\n<tr class=\"$class\">";
              echo "<td style=\"padding: 0 .7em;\">";
              echo $device['name'];
              echo "</td>";
              echo "<td style=\"padding: 0 .7em;\">";
              echo $mac;
              echo "</td>";
              echo "<td style=\"padding: 0 .7em;\">";
              echo $device['ip'];
              echo "</td>";
              echo "\n</tr>";
              
            }
            echo "\n</table>";

            ?>
         </div>
<!- -------------------------------------------------------------------------------------------------- ->


<!- ----- Heating ------------------------------------------------------------------------------------ ->
         <div id="tab-heating">        
            <table class="center" border=0>
              <tr><td>
                    <span id="boost">&nbsp;</span>
                    <span id="heatStat1">&nbsp;</span>
                    <span id="heatStat2">&nbsp;</span>
                    <span id="heatStat3">&nbsp;</span>
                  </td>
               <td style="width: 100px; text-align: center;"><span id="timestamp" style="font-size: medium;">&nbsp;</span></td>
              </tr>
              <tr>
               <td rowspan="4">  
               <div id='chart'></div>
             </div>
            </td>
            <td><div id='gaugeIn'></div>
                <div style="font-size: xx-small; text-align: center;">Unutarnja temp.</div></td>
           </tr>
           <tr>
            <td><div id='gaugeHumid'></div>
                <div style="font-size: xx-small; text-align: center;">Vlaga zraka</div></td>
          </tr>
           <tr>
            <td><div id='gaugeOut'></div>
                <div style="font-size: xx-small; text-align: center;">Vanjska temp.</div></td>
          </tr>
           <tr>
            <td><div style="height: 60px; width: 100px;"></div></td>
          </tr>
                <script>                  
                  var chartData = {
                          json: [{
                             timestamp: "",
                             tempSet: 0,
                             tempIn: 0,
                             tempOut: 0,
                             heatingOn: 0,
                             humidIn: 0
                          }],
                          x: 'timestamp',
                          xFormat: '%Y-%m-%d %H:%M:%S',
                          axes: {
                              humidIn: 'y2'
                          },
                          keys: {
                              x: 'timestamp',
                              value: ['tempIn', 'tempSet', 'heatingOn', 'humidIn', 'tempOut'],
                          },
                          names: {
                            tempIn: 'Unutarnja temp.',
                            tempSet: 'Temp. grijanja',
                            heatingOn: 'Grijanje',
                            humidIn: 'Vlaga zraka',
                            tempOut: 'Vanjska temp.'
                          },
                          type: 'spline',
                          types: {
                              tempIn: 'spline',
                              tempSet: 'area-step',
                              heatingOn: 'area-step'
                          },
                          colors: {
                              tempIn: '#CC6633',
                              tempSet: '#5555aa',
                              heatingOn: '#ee4444',
                              humidIn: '#6699CC',
                              tempOut: '#99CC66'
                          },
                      }

                  var chart = c3.generate({
                      bindto: '#chart',
                      data: chartData,
                      point: {
                          show: false
                      },
                      axis: {
                            x: {
                                type: 'timeseries',
                                tick: {
                                      format: "%d.%m. %H:%M",
                                      rotate: 20,
                                      multiline: true
                                }
                            },
                            y: {
                              show: true,
                              label: 'temp °C',
                            },
                            y2: {
                              show: true,
                              min: 55,
                              max: 80,
                              label: 'vlažnost %'
                            }
                      },
                      zoom: {
                          enabled: true
                      }
                  });

                  var gaugeData = {
                          columns: [['temp', 0]],
                          type: 'gauge'
                      };
                  
                  var gaugeIn = c3.generate({
                      bindto: '#gaugeIn',
                      data: gaugeData,
                      tooltip: {
                        show: false
                      },
                      size: {
                          width: 100,
                          height: 80
                      },
                      gauge: {
                        min: -10,
                        max: 40,
                        label: {
                          format: function (value, ratio) {
                            return value + '°C';
                          },
                          show: false
                        }
                      },
                      color: {
                          pattern: ['#ddf6ff', '#ffbf00', '#ff8000'],
                          threshold: {
                              unit: 'value',
                              values: [10, 20, 30]
                          }
                      }
                  });
                  
                  var gaugeHumid = c3.generate({
                      bindto: '#gaugeHumid',
                      data: gaugeData,
                      tooltip: {
                        show: false
                      },
                      size: {
                          width: 100,
                          height: 80
                      },
                      gauge: {
                        min: 20,
                        max: 100,
                        label: {
                          format: function (value, ratio) {
                            return value + '%';
                          },
                          show: false
                        }
                      },
                      color: {
                          pattern: ['#00bfff', '#007fff', '#0040ff'],
                          threshold: {
                              unit: 'value',
                              values: [60, 70, 80]
                          }
                      }
                  });

                  var gaugeOut = c3.generate({
                      bindto: '#gaugeOut',
                      data: gaugeData,
                      tooltip: {
                        show: false
                      },
                      size: {
                        width: 100,
                        height: 80
                      },
                      gauge: {
                        min: -10,
                        max: 40,
                        label: {
                          format: function (value, ratio) {
                            return value + '°C';
                          },
                          show: false
                        }
                      },
                      color: {
                          pattern: ['#ddf6ff', '#ffbf00', '#ff8000'],
                          threshold: {
                              unit: 'value',
                              values: [5, 15, 25]
                          }
                      }
                  });

                function refresh () {
                    $.ajax({
                        type: "GET",
                        contentType: "application/json; charset=utf-8",
                        url: "getTemp.php",
                        data: "tstamp=" + $("#timestamp").html() + "",
                        dataType: "json",
                        async: true,
                        cache: false,
                        success: function (data) {
                            if ( data.newData > 0 ) {
                              chartData.json = data.chart;
                              chart.load(chartData);

                              gaugeIn.load({columns: [['temp', data.tempIn]]});
                              gaugeOut.load({columns: [['temp', data.tempOut]]});
                              gaugeHumid.load({columns: [['temp', data.humidIn]]});

                              $("#timestamp").html(data.timestamp.substr(0,5));

                              if ( data.heatingOn > 0 )
                                $("#heatStat1").html("Grijanje je upaljeno na "+data.tempSet+"°C");
                              else
                                $("#heatStat1").html("Grijanje ugašeno ("+data.tempSet+"°C)");
                            }
                        },
                        error: function (result) {
                        }
                    });
                };

                refresh();
                setInterval(refresh, 60000);

                </script>
         </table>
         
<!- -------------------------------------------------------------------------------------------------- ->

      </div>
   </body>
</html>