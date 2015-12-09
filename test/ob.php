<?php

class OneWork extends Worker
{
  public function __construct()
  {
  }
  
  public function run()
  {
    ob_start();
    
    // make some notice, $b does not exists
    
    $a = $b;
    
    $output = ob_get_clean();
    
    print "End Run in OneWork with output: {$output}\n";
  }
}

$pool = new Pool(1);

$pool->submit(new OneWork());

// Now lets try the same notice in normal environment

ob_start();

// make some notice, $b does not exists

$a = $b;

$output = ob_get_clean();

print "Output Notice: {$output}\n";

?>