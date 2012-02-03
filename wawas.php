<?php

// The WebAbility(r) Application Server, version beta
// (c) 2011, 2012 Philippe Thomassigny C.
// info@webability.info
// Please read GPL joined licence
//
// Work ONLY on php >= 5.3

// ========================================================
// We are forever
// ========================================================
error_reporting(E_ALL);
set_time_limit(0);
declare(ticks = 1);
define('WADEBUG', true);

include_once 'include/__autoload.lib';

// ========================================================
// MAIN APPLICATION CLASS
// ========================================================
final class WAWAS extends WAObject
{
  const VERSION = '1.00.01';

  // command lines orders
  private $fileconfig = 'conf/wawas.conf';

  // type of server
  private $asdaemon = false;

  // Shutdown indicator
  private $pleasekill = false;  // true to indicate KILL THE DAEMON from arguments
  private $shutdown = false;    // true to indicate we already caught a shutdown order (TERM signal or so)

  // config instance
  public $config = null;

  // server instance
  public $server = null;

  // loggers
//  public $mainlogger = null;
//  public $errorlogger = null;

  // Protocols
  public $protocols = array();

  function __construct()
  {
    parent::__construct();
    if (self::$debug || $this->localdebug)
      $this->doDebug('WAWAS->__construct()', WADebug::SYSTEM);

    WAObject::setBase($this);
  }

  // ========================================================
  // MESSAGES HANDLER
  // ========================================================

  function insertMain($message)
  {
//    if ($this->mainlogger)
//      $this->mainlogger->insertLog(1, $message);
//    else
      print '===== M:'.date('Y-m-d H:i:s', time()).": ".$message."\n";
  }

  function insertError($errormessage)
  {
//    if ($this->errorlogger)
//      $this->errorlogger->insertLog(1, $errormessage);
//    else
      print '===== E:'.date('Y-m-d H:i:s', time()).": ".$errormessage."\n";
  }

  // ========================================================
  // MAIN RUN FUNCTION
  // ========================================================

  public function run()
  {
    if (self::$debug || $this->localdebug)
    {
      $this->doDebug('WAWAS->__run()', WADebug::SYSTEM);
      $line = "";
      for ($item = 0; $item < $_SERVER["argc"]; $item++)
        $line .= " ".$_SERVER["argv"][$item];
      $this->doDebug("Arguments: ".$line, WADebug::INFO);
    }

    // ========================================================
    // Step 0, We are controller
    // ========================================================
    if (function_exists('setproctitle'))
    {
      // we clean the title
      setproctitle(str_repeat(' ', 50).chr(0));
      setproctitle('WAWAS Controller'.chr(0));
    }


    // ========================================================
    // Step 1, Arguments
    // ========================================================
    if ($_SERVER["argc"] > 1)
    {
      for ($item = 1; $item < $_SERVER["argc"]; $item++)
      {
        $arg = $_SERVER["argv"][$item];

        switch($arg)
        {
          case "--help":
          case "-h":
          case "-?":
            $v = WAWAS::VERSION;
            echo <<<EOF
The WebAbility(r) Web and Application Server (WAWAS) v$v
Use: php wawas.php [options]
options are:
-h -? or --help :                  print this help
-k or --kill    :                  kill the daemon
-v or --version :                  print the version
-f [file] or --fileconfig [file] : use an alternate config file
--debug [level] :                  enter in debug mode, 1 = system, 2 = info, 3 = user


EOF;
            die(0);
            break;

          case "--version":
          case "-v":
            echo "The WebAbility(r) Web and Application Server (WAWAS) v".WAWAS::VERSION."\n";
            die(0);
            break;

          case "--fileconfig":
          case "-f":
            $this->fileconfig = $_SERVER["argv"][$item+1];
            $item++;
            break;

          case "--kill":
          case "-k":
            $this->pleasekill = true;
            break;

          case "--debug": // ignore the debug, already parsed before objects
            $item++;
            break;

          default:
            throw new WAWASError("Unknown argument: ".$arg);
            break;
        }
      }
    }

    // ========================================================
    // Step 2, Config
    // ========================================================
    $this->config = new Config($this->fileconfig);

    // ========================================================
    // Step 3, Load defined libraries by Conf (protocols and modules)
    // ========================================================
    // we create protocols
    $protocols = $this->config->getEntry('protocol');
    if (!$protocols)
      throw new WAWASError("Error: there is no protocol to listen to. Please check your config file.");

    foreach($protocols as $protocol)
    {
      $this->protocols[$protocol['name']] = new $protocol['lib']($protocol['name']);

      // we create modules
      $modules = $protocol['module'];
      if (!$modules)
        throw new WAWASError("Error: there is no modules to run in the protocol [{$protocol['name']}]. Please check your config file.");
      foreach($modules as $module)
      {
        $this->protocols[$protocol['name']]->registerModule($module['name'], new $module['lib']($module['name']) );
      }
    }

    // we parse full config file with protocols and modules def
    $this->config->parseConfig();

    // ========================================================
    // Step 4, Are we killing daemon ?
    // ========================================================
    if ($this->pleasekill)
    {
      $pidfile = $this->config->getEntry('daemon/pidfile');
      if ($pidfile && file_exists($pidfile))
      {
        $fp = fopen($pidfile, 'rb');
        $pid = fread($fp, filesize($pidfile) );
        fclose($fp);
        if (posix_kill($pid, 9))
        {
          print 'Server with PID: ' . $pid . ' killed.' . PHP_EOL;
          exit(0);
        }
        print 'There is no server with PID: ' . $pid . PHP_EOL;
      }
      print 'There is no PID lock file.' . PHP_EOL;
      exit(1);
    }

    // ========================================================
    // Step 5, Daemonize
    // ========================================================
    if ($this->config->getEntry('daemon/status'))
    {
      // Check if OK
      $pidfile = $this->config->getEntry('daemon/pidfile');
      if (file_exists($pidfile))
      {
        if (!is_callable('pcntl_fork') || !is_callable('posix_setsid'))
          throw new WAWASError('Could not daemonize WAWAS without fork and setsid available.');

        $fp = fopen($pidfile, 'rb');
        $pid = fread($fp, filesize($pidfile) );
        fclose($fp);
        if (posix_kill($pid, 0))
        {
          print 'WAWAS is already running with PID: ' . $pid . PHP_EOL;
          exit(1);
        }
        if (!unlink($pidfile))
        {
          throw new WAWASError('WAWAS Cannot unlink PID file '.$pidfile);
        }
        $this->insertMain('PID file for defunct WAWAS server process '.$pid.' has been removed');
      }

      $pid=pcntl_fork();
      if ($pid==-1)
      {
        throw new WAWASError('Error on FORK. Could not create daemon of WAWAS.');
      }
      else if ($pid)
      {
        print 'WAWAS Daemon running in background, PID = ' . $pid . PHP_EOL;
        exit(0);
      }
      $this->asdaemon = true;
      posix_setsid();
      $pid = posix_getpid();

      $fp = fopen($pidfile, 'wb');
      fwrite($fp, $pid);
      fclose($fp);
    }

    // ========================================================
    // Step 6, Start SERVER
    // ========================================================
    $this->server = new Server();
    $this->server->run();

    // ========================================================
    // On gracefull shutdown
    // ========================================================
    if ($this->asdaemon)
      unlink($this->config->getEntry('daemon/pidfile'));
  }

  // ========================================================
  // GENERAL TOOLS
  // ========================================================

/*
  public function createLogger($filename)
  {
    if (self::$debug || $this->localdebug)
      $this->doDebug("WAWAS->createLogger($filename)", WADebug::SYSTEM);

    if (!$filename)
      return null;
    $fullfile = realpath($filename);
    if (!isset($this->loggers[$fullfile]))
    {
      $l = new Logger($fullfile);
      $this->loggers[$fullfile] = $l;
    }
    else
    {
      $l = $this->loggers[$fullfile];
    }
    return $l;
  }
*/

  public function sigTERM()
  {
    if (self::$debug || $this->localdebug)
      $this->doDebug("WAWAS->sigTERM() in ".$this->server->getType(), WADebug::SYSTEM);

    if ($this->shutdown)
    {
      // kill all children and forked processes too

      print "forced manual shutdown on BREAK.\n";
      exit(0);
    }
    $this->shutdown = true;
    $type = $this->server->getType();
    if ( $type == 'Master' || $type == 'Standalone')
    {
      // message only for master, we dont need to say it for each worker or thread
      print "Starting gracefull shutdown. Please wait. hit CONTROL-C again to force immediate shutdown.\n";
    }
    // send order to Server to shutdown, does not matter which type it is
    $this->server->shutdown();
  }

}

function signals_handler($sign)
{
  global $WAWAS;
  // for now we just exit.
  switch($sign)
  {
    case SIGTERM:
    case SIGINT:
    case SIGHUP:
      $WAWAS->sigTERM();
      break;
  }
}

try
{
  // 1. check Debug mode
  if ($_SERVER["argc"] > 1)
  {
    for ($item = 1; $item < $_SERVER["argc"]; $item++)
    {
      $arg = $_SERVER["argv"][$item];
      if ($arg == "--debug")
      {
        $debuglevel = isset($_SERVER["argv"][$item+1])?$_SERVER["argv"][$item+1]:null;
        if (is_numeric($debuglevel) && $debuglevel >= 1 and $debuglevel <= 3)
        {
          WADebug::setDebug(true);
          WADebug::setLevel($debuglevel);
          WADebug::setRedirect(WADebug::ASCIIREDIR);
        }
      }
    }
  }

  $os = WADebug::getOSType();
  if (($os == WADebug::UNIX || $os == WADebug::MAC) && is_callable("pcntl_signal"))
  {
    pcntl_signal(SIGTERM, "signals_handler");
    pcntl_signal(SIGHUP, "signals_handler");
    pcntl_signal(SIGINT, "signals_handler");

    // autoclean children zombies
    pcntl_signal(SIGCHLD, SIG_IGN);
  }

  $WAWAS = new WAWAS();
  $WAWAS->run();
}
catch (Exception $exception)
{
  // ========================================================
  // ERROR AND LOGS FUNCTIONS
  // ========================================================
  if ($WAWAS)
    $WAWAS->insertError($exception->__toString());
  else
    print "===== ".date("Y-m-d H:i:s", time()).": ".$exception->__toString();
  exit(1);
}

?>