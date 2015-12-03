<?php
// Xamboo v.1
// index.php / Main Site index
// WRAPPER TO MAIN SITE DISPATCHER
// Ing. Philippe Thomassigny (c) 2012
// Xamboo is free software

// Cambios:
// 24/06/2014, Phil: Reverse IP protección y redirects hacia www automaticamente
// 02/11/2014, Phil: Simplificacion directorios a solamente BASE y REPOSITORY, los demas vienen de config
// 02/11/2014, Phil: Agregar CACHE1DIR y CACHE2DIR
// 02/11/2014, Phil: Remover variables de config obsoletas wwwsite cdnsite etc.
// 20/11/2014, Phil: Agregar la variable forceserver para poder ver la página en un esclavo en particular

error_reporting(E_ALL);
ini_set('display_errors', true);

// redirect si no estamos en el sitio principal de kiwi
if (!isset($_GET['forceserver']) && (!isset($_SERVER["HTTP_HOST"]) || $_SERVER["HTTP_HOST"] != 'devel4.kiwilimon.com'))
{
  header("HTTP/1.1 301 Moved Permanently");
  header('Location: http://www.kiwilimon.com' . $_SERVER['REQUEST_URI']);
  print <<<EOF
<html>
<header>
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-11441155-1']);
  _gaq.push(['_setDomainName', 'kiwilimon.com']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
</header>
<body>
</body>
</html>
EOF;
  return;
}

// We take at first the microtime to evaluate calculation time of page.
$BASE_MIB = microtime();

$SKIN = 'kiwi';
// The wrapper config variables are set by the installation system
// THE DRIVE SYSTEM is done to assure windows-unix compatibility of transporting application
if (isset($_SERVER['WINDIR']))
{
  $BASEDRIVE = $REPOSITORYDRIVE = 'C:';
}
else
{
  $BASEDRIVE = $REPOSITORYDRIVE = '';
}

global $BASEDIR, $REPOSITORYDIR;

$BASEDIR       = $BASEDRIVE       . '/home/sites/kiwi4.kiwilimon.com/';
$REPOSITORYDIR = $REPOSITORYDRIVE . '/home/sites/kiwi4.kiwilimon.com/repository/';

// Just in case for multiple site and php apache config
set_include_path('.'.PATH_SEPARATOR.$BASEDIR.'/include');

$INDEXPHP = 'index.php';
$JSPHP = 'index.js.php';

// implements __autoload
include_once $BASEDIR.'/include/__autoload.lib';

// When arriving here, we enter in the 'normal' HTML wrapper.
// Each page is wrapped through the index.
// The index gives the basic functions and includes to the included libraries.

$SHOW_EXCEPTION = $SHOW_OB = 2; // log exceptions by default
$TIME_LIMIT = 30; // 30 secs by default
$SHM = false;
$SHMID = $SHMSIZE = 0;

$config = null;
if (@file_exists($REPOSITORYDIR.'site.afo'))
{
  $fconfig = fopen($REPOSITORYDIR.'site.afo', 'rb');
  $config = unserialize(fread($fconfig, filesize($REPOSITORYDIR.'site.afo')));
  fclose($fconfig);

  // we first check maintenance flag !
  if ($config['maintenance'] == 1 && !isset($_GET['takeover']))
  {
    require $config['SITEDIR'].$config['maintenancelink'];
    die();
  }

  $SHOW_EXCEPTION = $config['exception'];
  $SHOW_OB = $config['ob'];
  if ($config['debug'])
  {
    \core\WADebug::setDebug($config['debug']);
    \core\WADebug::setLevel($config['level']);
    \core\WADebug::setRedirect($config['redirect'], $config['file']);
  }

  setlocale(LC_ALL, $config['locale']);
  date_default_timezone_set($config['timezone']);
  \core\WAFile::setDirMask($config['defdirmask']);
  \core\WAFile::setFileMask($config['deffilemask']);
  $TIME_LIMIT = $config['timelimit'];

  $SHM = $config['shmuse'];
  $SHMID = $config['shmid'];
  $SHMSIZE = $config['shmsize'];
}
else
{
  print "Error 505 - Falta la configuración del sistema";
  die();
}
set_time_limit($TIME_LIMIT);

//    define('WADEBUG', true);
//    WADebug::setDebug(true);
//    WADebug::setLevel(1);
//    WADebug::setRedirect($config['redirect'], $config['file']);
// $SHOW_EXCEPTION = $SHOW_OB = 3; // show nothing by default, only log

$config['BASEDIR'] = $BASEDIR;
$config['REPOSITORYDIR'] = $REPOSITORYDIR;
$config['SKIN'] = $SKIN;

// we setup some variables we need
$URI = $QUERY = $BASE_P = $base = null;
try
{
  $errors = null;
  if ($SHOW_OB != 10)
    ob_start();

  $BASE_MIE = microtime();

  // we create the base object
  $base = new \common\Base($config);
  \core\WAMessage::setMessagesFile($BASEDIR.'/application/static/'.$base->Language.'.xml');
  
  // check and register client !
  $base->checkChef();

  if (isset($_SERVER['REQUEST_URI']))
    $URI = strtolower($_SERVER['REQUEST_URI']);
  if ($URI)
  {
    // Remove query part (already managed by PHP)
    if (strpos($URI, '?'))
    {
      $QUERY = substr($URI, strpos($URI, '?'));
      $URI = substr($URI, 0, strpos($URI, '?'));
    }
    if (substr($URI, -1) == '/' && strlen($URI) > 1)
    {
      // NO ACEPTAMOS URLS QUE TERMINAN CON /, REDIRECCIONAMOS !!
      $URI = substr($URI, 0, -1);
      header('HTTP/1.1 301 Moved Permanently');
      header('Location: ' . $URI . $QUERY);
      return;
    }
    if (strlen($URI) > 1)
      $BASE_P = $URI;
  }

  // Call the engine with the page
  $engine = new \xamboo\engine($URI);
  $text = $engine->run($BASE_P, null, null);

  if ($SHOW_OB != 10)
  {
    $ob = ob_get_contents();
    ob_end_clean();
  }
  else
    $ob = null;

  $text = str_replace(array('__CDN__', '__GRAPH__', '__WWW__', '__ADMIN__', '__HOSTNAME__'), array($config['CDNDOMAIN'], $config['GRAPHDOMAIN'], $config['SITEDOMAIN'], $config['ADMINDOMAIN'], $base->HOSTNAME), $text);
  print $text;
  
  if ($ob && $SHOW_OB > 0)
  {
    showlog_string($base, $BASE_P, $SHOW_OB, $ob);
  }
  
  $BASE_ME = microtime();

  try
  {
    // Calc stats if activated and if all correct
    $base->insertStat(
      $base->calculatePureTime($BASE_MIB,$BASE_ME),
      $BASE_P, $URI);
  }
  catch(Exception $e)
  { // ignoramos los eventuales errores de MySQL -- too many connections 
  }
}
catch (Exception $exception)
{
  $ob = ob_get_contents();
  ob_end_clean();

  if (($SHOW_OB >= 2 || $SHOW_EXCEPTION >= 2))
  {
    print <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta name="Generator" content="Xamboo v4 - (c) 2012-2014 Philippe Thomassigny - Xamboo is free software" />
  <meta name="Component" content="Engine - Exception Manager" />
  <meta http-equiv="PRAGMA" content="NO-CACHE" />
  <meta http-equiv="Expires" content="-1" />
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body>
EOF;
  }

  if ($ob && $SHOW_OB > 0)
    print showlog_string($base, $BASE_P, $SHOW_EXCEPTION, $ob);

  if ($SHOW_EXCEPTION > 0)
    showlog_string($base, $BASE_P, $SHOW_EXCEPTION, $exception->__toString());

  if (($SHOW_OB >= 2 || $SHOW_EXCEPTION >= 2))
  {
    print <<<EOF
</body>
</html>
EOF;
  }

  if ($base)
  {
    // add Stats
//    $base->insertStat(
//      $base->calculatePureTime(0,0),
//      $BASE_P, $URI);
  }
}

function showlog_string($base, $P, $SHOW, $str)
{
  if ($SHOW >= 2)
    print $str;
  if ($SHOW == 1 || $SHOW == 3)
  {
    if ($base)
    {
      // log exception !
      $base->insertSysLog('site', $str, $_SERVER['REQUEST_URI']);
    }
  }
}

?>