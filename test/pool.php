<?php

/*
declare(ticks=1);

// A function called on each tick event
function tick_handler()
{
  $name = 'log.txt';
  $memory = memory_get_usage();
  file_put_contents ($name,"$memory\n",FILE_APPEND);
}

register_tick_function('tick_handler');
*/

class WebWork extends Worker
{
  private $num;
  
  public function __construct($num)
  {
    $this->num = $num;
  }
  
  public function run()
  {
    print "Run en webwork {$this->num}\n";

    for ($a = 1; $a < 5; $a++)
    {
      $x = @file_get_contents('http://127.0.0.1:99');
      $this->wait();
    }

    print "End Run en webwork {$this->num}\n";
  }
}

$pool = new Pool(250);



for ($a = 1; $a < 1000000; $a++)
{
  $pool->submit(new WebWork($a));
}


?>
