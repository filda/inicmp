<?php
/**
* PHP INI Comparator
* Filip Šubr <fsubr@centrum.cz>
* 
*/
define('INICMP_VERSION', '1.0');

$local_ini['name'] = $_SERVER['SERVER_NAME'];
$local_ini['time'] = time();
// TODO string phpversion ([ string $extension ] )
$local_ini['phpversion'] = phpversion();
$local_ini['ini'] = ini_get_all();
$filename = 'php_ini_'.$_SERVER['SERVER_NAME'].'_'.date('YnjHis',time());
$content = serialize($local_ini);

if(isset($_GET['download'])){
        header("Content-type: application/force-download"); 
        Header( "Content-Disposition: attachment; filename=".$filename);        
        echo $content;
        die();
}

foreach (glob('php_ini_'.$_SERVER['SERVER_NAME'].'*') as $inifile) {
        $older_local[] = $inifile;
}

$write_new = true;
if(isset($older_local)){
        sort($older_local);
        $older_local_ini = unserialize(file_get_contents($older_local[count($older_local)-1]));
        if($local_ini['ini'] === $older_local_ini['ini']){
                $write_new = false;
        }
}

if($write_new){
        if($handle = @fopen($filename, 'w')) {                
                if (fwrite($handle, $content) === FALSE) {
                        echo "Cannot write to file ($filename)";
                }                
                fclose($handle);
        }else{
                echo "Cannot write to file ($filename)";
                $ini[$filename] = $local_ini;
        }        
}

foreach (glob("php_ini_*") as $inifile) {
        $ini[$inifile] = unserialize(file_get_contents($inifile));

}
$inifiles = array_keys($ini);
$inikeys = array();
foreach($ini as $inifile){
        // this doesn't work as expected
        //$inikeys = array_merge($inikeys, array_keys($inifile['ini'])); 
        foreach(array_keys($inifile['ini']) as $inikey){
//echo $inikey;        
                if(!array_search($inikey, $inikeys)){
                        $inikeys[] = $inikey;
                }
        }        
}
//print_r($inikeys);
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>PHP INI Comparatör</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="description" content="Compares PHP settings">
<meta name="keywords" content="php ini setting compare tool">
<meta name="author" content="filips <fsubr@centrum.cz>">
<style type="text/css">
body{background-color: #ffffff; color: #000000; }
body, td, th, h1, h2{font-family: sans-serif;}
h1{font-size: 150%;}
h2{font-size: 125%;}
table{border-collapse: collapse;}
td, th{border: 1px solid #000000; font-size: 75%; vertical-align: baseline;}
th {background-color: #9999cc; color: #000000;}
td{background-color: #cccccc; color: #000000; padding: 5px;}
.row{background-color: #eeeeee;}
table h1{text-align: left;}
.e{background-color: #ccccff;}
img {float: right; border: 0px;}
a:link{color: #000099; text-decoration: none; background-color: #ffffff;}
a:hover{text-decoration: underline;}
</style>
</head>
<body>
<table border="0" cellpadding="3" width="600">
<tr><th>
<a href="http://www.php.net/"><img border="0" src="/info.php?=PHPE9568F34-D428-11d2-A769-00AA001ACF42" alt="PHP Logo" /></a><h1 class="p">PHP INI Comparatör</h1>
</th></tr>
</table>

<ul>
<li><a href="?download">Download config</a></li>
<li><a href="#help">Help</a></li>
<li><a href="#about">About</a></li>
</ul>

<h2 id="compare">Comparation</h2>
<table><tr><td>&nbsp;</td>
<?php
foreach($ini as $single_ini){
        echo '<th>Server name: '.$single_ini['name'];
        echo '<br>Snapshot time: '.date('H:i:s j.n.Y',$single_ini['time']);
        echo '<br>PHP Version: '.$single_ini['phpversion'];
        echo '</th>';
}
echo '</tr>';

$row = '';
foreach($inikeys as $directive){
        $val = 'no value';
        $show = false;
        foreach($inifiles as $inifile){
                if($val == 'no value'){
                        if(isset($ini[$inifile]['ini'][$directive]['local_value'])){
                                $val = $ini[$inifile]['ini'][$directive]['local_value'];
                        }
                }else{
                        if(!isset($ini[$inifile]['ini'][$directive]['local_value'])){
                                $ini[$inifile]['ini'][$directive]['local_value'] = 'no value';
                        }
                        if($val != $ini[$inifile]['ini'][$directive]['local_value']){
                                $show = true;
                        }
                }
        }
        if($show){
                if($row == ''){
                        $row = ' class="row"';
                }else{
                        $row = '';
                }
                echo '<tr><th class="e">'.$directive.'</th>';
                foreach($inifiles as $inifile){
                        if(isset($ini[$inifile]['ini'][$directive]['local_value'])){
                                echo '<td'.$row.'>'.htmlentities($ini[$inifile]['ini'][$directive]['local_value']).'&nbsp;</td>';
                        }
                        // TODO jeste je tam global_value a nejak access
                }
        }
        echo '</tr>';
}
?>
</table>

<h2 id="help">Help</h2>
<p>Chytrému napověz.</p>
<h3>PHP INI Comparator</h3>
<p>Nahraj to na svůj oblíbenej server s php podporou, vygeneruj si soubor s nastavením a porovnávej s dalšíma.</p>
<p>
Zjistí a uloží nastavení PHP. Název souboru s nastavením má konvenci php_ini_<i>servername</i>_<i>yyyymmddhhmmss</i>. Pokud z néjakého důvodu nemáte možnost zápisu na filesystém můžete použít funkci <a href="?download">Download config</a>. Potom můžete nastavení porovnat na jiném serveru.
</p>

<h2 id="about">About</h2>
<table border="0" cellpadding="3" width="600">
<tr><td>
<p>This program is software.</p>
<p>Napsal Filda <a href="mailto:fsubr@centrum.cz">fsubr@centrum.cz</a> &copy; 2008</p>
<p>Choose your license, for example GPL, LGPL may be better. Or MIT? What about BSD?</p>
</td></tr>
</table>
</body>
</html>