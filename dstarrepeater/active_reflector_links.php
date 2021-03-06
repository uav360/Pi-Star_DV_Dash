<?php include_once $_SERVER['DOCUMENT_ROOT'].'/config/ircddblocal.php';
$configs = array();

if ($configfile = fopen($gatewayConfigPath,'r')) {
        while ($line = fgets($configfile)) {
                list($key,$value) = split("=",$line);
                $value = trim(str_replace('"','',$value));
                if ($key != 'ircddbPassword' && strlen($value) > 0)
                $configs[$key] = $value;
        }

}
$progname = basename($_SERVER['SCRIPT_FILENAME'],".php");
$rev="20141101";
$MYCALL=strtoupper($callsign);
?>
    <b>D-Star Link Information</b>
    <table>
    <tr>
    <th><a class="tooltip" href="#">Radio<span><b>Radio Module</b></span></a></th>
    <th><a class="tooltip" href="#">Default<span><b>Default Link Destination</b></span></a></th>
    <th><a class="tooltip" href="#">Auto<span><b>AutoLink</b>- green: enabled<br />- red: disabled</span></a></th>
    <th><a class="tooltip" href="#">Timer<span><b>Reset/Restart Timer</b></span></a></th>
    <th><a class="tooltip" href="#">Link<span><b>Link-Status</b>- green: enabled<br />- red: disabled</span></a></th>
    <th><a class="tooltip" href="#">Linked to<span><b>linked destination</b></span></a></th>
    <th><a class="tooltip" href="#">Mode<span><b>Mode or Protocol used</b></span></a></th>
    <th><a class="tooltip" href="#">Direction<span><b>Direction</b>incoming or outgoing</span></a></th>
    <th><a class="tooltip" href="#">Last Change (<?php echo date('T')?>)<span><b>Timestamp of last change</b><?php echo date('T')?></span></a></th>
    </tr>

<?php
    $tot = array(0=>"Never",1=>"Fixed",2=>"5min",3=>"10min",4=>"15min",5=>"20min",6=>"25min",7=>"30min",8=>"60min",9=>"90min",10=>"120min",11=>"180min",12=>"&nbsp;");
    $ci = 0;
    $tr = 0;
    for($i = 1;$i < 5; $i++){
	$param="repeaterBand" . $i;
	if((isset($configs[$param])) && strlen($configs[$param]) == 1) {
	    $ci++;
	    if($ci > 1) { $ci = 0; }
	    print "<tr>";
	    $tr = 1;
	    $module = $configs[$param];
	    $rcall = sprintf("%-7.7s%-1.1s",$MYCALL,$module);
	    $param="repeaterCall" . $i;
	    if(isset($configs[$param])) { $rptrcall=sprintf("%-7.7s%-1.1s",$configs[$param],$module); } else { $rptrcall = $rcall;}
	    print "<td>".str_replace(' ', '&nbsp;', substr($rptrcall,0,8))."</td>";
	    $param="reflector" . $i;
	    if(isset($configs[$param])) { print "<td>".str_replace(' ', '&nbsp;', substr($configs[$param],0,8))."</td>"; } else { print "<td>&nbsp;</td>";}
	    $param="atStartup" . $i;
	    //if($configs[$param] == 1){print "<td><img src=\"images/20green.png\" /></td>"; } else { print "<td><img src=\"images/20red.png\" /></td>"; }
	    if($configs[$param] == 1){print "<td>Auto</td>"; } else { print "<td>No</td>"; }
	    $param="reconnect" . $i;
	    if(isset($configs[$param])) { $t = $configs[$param]; } else { $t = 0; }
	    if($t > 12){ $t = 12; }
	    print "<td>$tot[$t]</td>";
	    $j=0;
	    if ($linkLog = fopen($linkLogPath,'r')) {
		while ($linkLine = fgets($linkLog)) {
		    //$statimg = "<img src=\"images/20red.png\">";
		    $statimg = "Down";
                    $linkDate = "&nbsp;";
                    $protocol = "&nbsp;";
                    $linkType = "&nbsp;";
                    $linkRptr = "&nbsp;";
                    $linkRefl = "&nbsp;";
// Reflector-Link, sample:
// 2011-09-22 02:15:06: DExtra link - Type: Repeater Rptr: DB0LJ  B Refl: XRF023 A Dir: Outgoing
// 2012-10-12 17:15:45: DCS link - Type: Repeater Rptr: DB0LJ  B Refl: DCS001 L Dir: Outgoing
// 2012-10-12 17:56:10: DCS link - Type: Repeater Rptr: DB0RPL B Refl: DCS015 B Dir: Outgoing
                    if(preg_match_all('/^(.{19}).*(D[A-Za-z]*).*Type: ([A-Za-z]*).*Rptr: (.{8}).*Refl: (.{8}).*Dir: Outgoing$/',$linkLine,$linx) > 0){
			//$statimg = "<img src=\"images/20green.png\" />";
			$statimg = "Up";
			$linkDate = date("d-M-Y H:i:s", strtotime(substr($linx[1][0],0,19)));
                        $protocol = $linx[2][0];
                        $linkType = $linx[3][0];
                        $linkRptr = $linx[4][0];
                        $linkRefl = $linx[5][0];
			if($linkRptr == $rptrcall){
			    print "<td>$statimg</td>";
			    print "<td>".str_replace(' ', '&nbsp;', substr($linkRefl,0,8))."</td>";
			    print "<td>$protocol</td>";
			    print "<td>Outgoing</td>";
				$utc_time = $linkDate;
                        	$utc_tz =  new DateTimeZone('UTC');
                        	$local_tz = new DateTimeZone(date_default_timezone_get ());
                        	$dt = new DateTime($utc_time, $utc_tz);
                        	$dt->setTimeZone($local_tz);
                        	$local_time = $dt->format('H:i:s M jS');
			    print "<td>$local_time</td>";
			    print "</tr>\n";
                    	    $tr = 0;
			}
		    }
		}
		fclose($linkLog);
	    }

// 2012-05-08 21:16:31: DExtra link - Type: Repeater Rptr: DB0LJ  A Refl: DB0MYK B Dir: Incoming

	    if ($linkLog = fopen($linkLogPath,'r')) {
		while ($linkLine = fgets($linkLog)) {
		    //$statimg = "<img src=\"images/20red.png\" />";
		    $statimg = "Down";
                    $linkDate = "&nbsp;";
                    $protocol = "&nbsp;";
                    $linkType = "&nbsp;";
                    $linkRptr = "&nbsp;";
                    $linkRefl = "&nbsp;";
                    if(preg_match_all('/^(.{19}).*(D[A-Za-z]*).*Type: ([A-Za-z]*).*Rptr: (.{8}).*Refl: (.{8}).*Dir: Incoming$/',$linkLine,$linx) > 0){
			//$statimg = "<img src=\"images/20green.png\" />";
			$statimg = "Up";
			$linkDate = date("d-M-Y H:i:s", strtotime(substr($linx[1][0],0,19)));
                        $protocol = $linx[2][0];
                        $linkType = $linx[3][0];
                        $linkRptr = $linx[4][0];
                        $linkRefl = $linx[5][0];
			if($linkRptr == $rptrcall){
			    $ci++;
			    if($ci > 1) { $ci = 0; }
			    print "<tr>";
			    print "<td>".str_replace(' ', '&nbsp;', substr($rptrcall,0,8))."</td>";
			    print "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
			    print "<td>$statimg</td>";
			    print "<td>".str_replace(' ', '&nbsp;', substr($linkRefl,0,8))."</td>";
			    print "<td>$protocol</td>";
			    print "<td>Incoming</td>";
				$utc_time = $linkDate;
                        	$utc_tz =  new DateTimeZone('UTC');
                        	$local_tz = new DateTimeZone(date_default_timezone_get ());
                        	$dt = new DateTime($utc_time, $utc_tz);
                        	$dt->setTimeZone($local_tz);
                        	$local_time = $dt->format('H:i:s M jS');
			    print "<td>$local_time</td>";
			    print "</tr>\n";
                    	    $tr = 0;
            		}
            	    }
		}
		fclose($linkLog);
	    }
	    if ($tr == 1){
		print"<td>$statimg</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
	    }
	}
    }
?>

    </table>
