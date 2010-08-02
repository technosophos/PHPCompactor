<?php
/**
 * Compact PHP code.
 *
 * Strip comments, combine entire library into one file.
 *
 * Modified by Jurriaan Pruis - Better 'compression'
 *
 **/
 
require_once('lib/compactor.php');

if ($argc < 3) {
  print "Strip unnecessary data from PHP source files.\n\n\tUsage: php phpcompactor.php DESTINATION.php SOURCE.php\n";
  exit;
}
 
$source = $argv[2];
$target = $argv[1];
print "Compacting $source into $target.\n";
$compactor = new Compactor($target);

$before = get_included_files();
include $source;
$files = array_diff(get_included_files(),$before);

foreach($files as $file) $compactor->compact($file);

$compactor->report();
$compactor->close();