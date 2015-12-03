<?php

/* @DESCR -- Do not edit

XConfig.lib
Contains the basic class to build a config object
(c) 2015 Philippe Thomassigny

XConfig is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

XConfig is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Xamboo.  If not, see <http://www.gnu.org/licenses/>.

Creation: 2012-09-23
Changes:
  2015-07-23 Phil: First release
  2015-08-13 Phil: Added reserved words interpretation (yes, true, no, none, null, etc)
  2015-08-13 Phil: Added ';' as comment

@End_DESCR */

namespace xconfig;

class XConfig implements \ArrayAccess, \Iterator, \Countable
{
  const VERSION = '2.0.0';
  protected $entries = array();

  /* The constructor receive a data, that may be a string (to be compiled) or an array of param => value
     The string of a configuration file has the format:
     parameter=value
     one per line. If a parameter is repeated, it will be inserted as an array of values
     The default array may contains expected values for each parameter, if they are not present.
       a null, 0 or false value in the parameters "is" a value and the default will not be used.
  */
  public function __construct($data, $default = null)
  {
    if (is_string($data))
    { // data buffer
      $this->entries = XConfig::compile($data);
    }
    else if (is_array($data))
    {
      if (isset($data['entries']))
        $this->entries = $data['entries'];
    }
    if (is_array($default))
    {
      foreach($default as $p => $v)
        if (!isset($this->entries[$p]))
          $this->entries[$p] = $v;
    }
  }

  /* This function permits to merge another config into this one. 
     The parameters with same names will be replaced, others appended.
  */
  public function merge($data)
  {
    $entries = array();
    if (is_string($data))
    { // data buffer
      $entries = XConfig::compile($data);
    }
    else if (is_array($data))
    {
      if (isset($data['entries']))
        $entries = $data['entries'];
    }
    else if ($data instanceof XConfig)
      $entries = $data;
    foreach($entries as $entry => $value)
      $this->entries[$entry] = $value;
  }
  
  // magic functions implements
  public function __get($name)
  {
    if (isset($this->entries[$name]))
      return $this->entries[$name];
    return null;
  }

  public function __set($name, $val)
  {
    $this->entries[$name] = $val;
    return $this;
  }

  public function __isset($name)
  {
    return isset($this->entries[$name]);
  }

  public function __unset($name)
  {
    unset($this->entries[$name]);
  }

  // ArrayAccess implemented
  public function offsetSet($offset, $value)
  {
    if ($offset)
      $this->entries[$offset] = $value;
  }

  public function offsetExists($offset)
  {
    return isset($this->entries[$offset]);
  }

  public function offsetUnset($offset)
  {
    unset($this->entries[$offset]);
  }

  public function offsetGet($offset)
  {
    return isset($this->entries[$offset]) ? $this->entries[$offset] : null;
  }

  // Iterator implemented
  public function rewind()
  {
    reset($this->entries);
  }

  public function current()
  {
    return current($this->entries);
  }

  public function key()
  {
    return key($this->entries);
  }

  public function next()
  {
    return next($this->entries);
  }

  public function valid()
  {
    return current($this->entries) !== false;
  }

  // Countable implemented
  public function count()
  {
    return count($this->entries);
  }

  // Own array get/set
  public function getArray()
  {
    return $this->entries;
  }

  public function setArray($array)
  {
    foreach($array as $k => $v)
      $this->entries[$k] = $v;
    return $this;
  }

  // is serializable
  protected function serial(&$data)
  {
    $data['entries'] = $this->entries;
  }

  protected function unserial($data)
  {
    $this->entries = $data['entries'];
  }

  // Build a beautifull string with parameters
  public function __toString()
  {
    return XConfig::create($this->entries);
  }

  // Compiler of the configuration string. May be used without creating an instance 
  static function compile($text)
  {
    $text = str_replace(array("\n\r", "\r\n", "\r"), "\n", $text);
    $xtext = explode("\n", $text);
    $lines = array();
    foreach($xtext as $line)
    {
      $line = trim($line);
      if (!$line || substr($line, 0, 1) == '#' || substr($line, 0, 1) == ';')
        continue;
      if (($p = strpos($line, '=')) !== false)
      {
        $param = substr($line, 0, $p);
        $value = substr($line, $p+1);
        if ($value === false)
          $value = null;
      }
      else
      {
        $param = $line;
        $value = null;
      }
      if (in_array($value, array('true', 'yes', 'on'), true))
        $value = true;
      if (in_array($value, array('false', 'no', 'off', 'none'), true))
        $value = false;
      if (!isset($lines[$param]))
        $lines[$param] = $value;
      else
      {
        if (!is_array($lines[$param]))
          $lines[$param] = array($lines[$param], $value);
        else
          $lines[$param][] = $value;
      }
    }
    return $lines;
  }
  
  // Creates a string from an array, may be used without creating an instance
  static function create($data)
  {
    $text = '';
    foreach($data as $k => $v)
    {
      $text .= $k . '=';
      if (is_array($v))
      {
        $text .= '[';
        $first = 0;
        foreach($v as $vx)
          $text .= (($first++)?',':'') . (is_bool($vx)?($vx?'true':'false'):$vx);
        $text .= ']' . "<br />\n";
      }
      else
        $text .= (is_bool($v)?($v?'true':'false'):$v) . "<br />\n";
    }
    return $text;
  }

}

?>