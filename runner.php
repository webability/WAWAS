<?php

// Servidor multiprotócolo
// (c) 2015 Philippe Thomassigny C.
// info@webability.info
// Please read GPL joined licence
//
// Necesita PHP 5.3+ CLI

error_reporting(E_ALL);
set_time_limit(0);

setlocale(LC_ALL, 'es_mx.UTF8');
setlocale(LC_NUMERIC, 'C');
date_default_timezone_set('America/Mexico_City');

// ========================================================
// Incluir las librerias necesarias -- global AUTOLOAD
// ========================================================
function autoload($classname)
{
//  print "buscando $classname \n";

  if (is_file('include/'.str_replace('\\', '/', $classname).".lib"))
    include_once 'include/'.str_replace('\\', '/', $classname).".lib";
  elseif (is_file('application/'.str_replace('\\', '/', $classname).".lib"))
    include_once 'application/'.str_replace('\\', '/', $classname).".lib";
}

spl_autoload_register('autoload');

function crash()
{
  echo 'Crash ?', PHP_EOL;
}

register_shutdown_function('crash');

// Duerme unos microsegundos usando un monitor MTS
function _sleep($microseconds)
{
  $monitor = new Threaded();
  $monitor->synchronized(function() use($microseconds, $monitor) {
      $monitor->wait($microseconds);
  });
}

try
{
  // Verificamos que tengamos lo que necesitamos para funcionar
  if (!is_callable("posix_kill"))
    throw new Error('Error: Server necesita la función posix_kill en le módulo pcntl para funcionar.');
  if (!is_callable("pcntl_signal"))
    throw new Error('Error: Server necesita la función pcntl_signal en le módulo pcntl para funcionar.');
  if (!extension_loaded("pthreads"))
    throw new Error('Error: Server necesita la extensión pthreads para funcionar.');
  if (!is_callable("pcntl_fork"))
    throw new Error('Error: Server necesita la función pcntl_fork en el módulo pcntl para funcionar.');

  // Verificamos si estamos en modo Debug antes de cualquier cosa
  if ($_SERVER["argc"] > 1)
  {
    for ($item = 1; $item < $_SERVER["argc"]; $item++)
    {
      $arg = $_SERVER["argv"][$item];
      if ($arg == "--debug")
      {
        $debuglevel = isset($_SERVER["argv"][$item+1])?$_SERVER["argv"][$item+1]:null;
/*        
        if (is_numeric($debuglevel) && $debuglevel >= 1 and $debuglevel <= 3)
        {
          WAWASDebug::setDebug(true);
          WAWASDebug::setLevel($debuglevel);
          WAWASDebug::setRedirect(WADebug::ASCIIREDIR);
        }
*/
      }
    }
  }
  
  $Runner = new \server\Runner();

  pcntl_signal(SIGTERM, array($Runner, "signals_handler"));
  pcntl_signal(SIGHUP, array($Runner, "signals_handler"));
  pcntl_signal(SIGINT, array($Runner, "signals_handler"));

  $Runner->run();
  exit(0);
}
catch (Throwable $exception)
{
  // Notifica el error
  print "===== ".date("Y-m-d H:i:s", time()).": ".$exception->__toString()."=====\n";
  exit(1);
}

?>