<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

$path = str_replace('/www/web', '', dirname(__FILE__));

$lastUpdate     = explode('|', file_get_contents($path .'/var/lastTemp.dat'));
$lastUpdate = $lastUpdate[4];

if ( urldecode($_REQUEST['tstamp']) === $lastUpdate )
{
  echo json_encode(array( 'newData'    => 0 ));
}
else
{
  $db = new SQLite3($path .'/var/heating.db');
  $results = $db->query("SELECT * FROM tempLog
                          WHERE timestamp > datetime(datetime('now', 'localtime'), '-12 hours')
                          ORDER BY timestamp DESC
                          LIMIT 200");
  while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
      $temps[] = array(
                        'timestamp'  => $row['timestamp'],
                        'tempSet'    => $row['tempSet'],
                        'tempIn'     => $row['tempIn'],
                        'tempOut'    => $row['tempOut'],
                        'heatingOn'  => $row['heatingOn']*$row['tempSet'],
                        'humidIn'    => $row['humidIn']
                        );
  }

  echo json_encode(array( 'chart' => $temps,
                            'timestamp'  => date('H:i:s', strtotime($temps[0]['timestamp'])),
                            'tempSet'    => $temps[0]['tempSet'],
                            'tempIn'     => $temps[0]['tempIn'],
                            'tempOut'    => $temps[0]['tempOut'],
                            'heatingOn'  => $temps[0]['heatingOn'],
                            'humidIn'    => $temps[0]['humidIn'],
                            'newData'    => 1
                        ));
}

?>