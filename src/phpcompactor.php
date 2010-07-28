<?php
/**
 * Compact PHP code.
 *
 * Strip comments, combine entire library into one file.
 *
 * Modified by Jurriaan Pruis - Better 'compression'
 *
 **/
 
if ($argc < 3) {
  print "Strip unnecessary data from PHP source files.\n\n\tUsage: php phpcompactor.php DESTINATION.php SOURCE.php\n";
  exit;
}
 
$source = $argv[2];
$target = $argv[1];
print "Compacting $source into $target.\n";
 
include $source;
 
$files = get_included_files();
$before = 0;
$out = fopen($target, 'w');
fwrite($out, '<?php' . PHP_EOL);
$next = false;
foreach ($files as $f) {
  if ($f !== __FILE__) {
    echo '+ compacting \''.$f. "'\n";
    $before += filesize($f);
    $contents = trim(file_get_contents($f));
    $tokens = token_get_all($contents);
    $previous = false;
    $removearr = array();
    $count = 0;
    
    foreach ($tokens as $token) {
      if($previous) {
        if(!is_string($token)) {
          switch ($token[0]) {
            case T_BOOLEAN_OR:
            case T_BOOLEAN_AND:
            case T_IS_EQUAL:
            case T_IS_GREATER_OR_EQUAL:
            case T_IS_IDENTICAL:
            case T_IS_NOT_EQUAL:
            case T_IS_NOT_IDENTICAL:
            case T_IS_SMALLER_OR_EQUAL:
            case T_PLUS_EQUAL:
            case T_MINUS_EQUAL:
            case T_OR_EQUAL:
            case T_DEC:
            case T_CURLY_OPEN:
            case T_INC:
            case T_ENCAPSED_AND_WHITESPACE:
            case T_DOUBLE_ARROW:
            case T_CONCAT_EQUAL:
            case T_PRINT:
            case T_IF:
            case T_VARIABLE:
            case T_WHITESPACE:
                        $removearr[] = $previous;
          }
        } else {
          if(in_array($token,array('?',';',':','}','{','(',')',',','=','|','&','>','<','.','-','+','*','/'))) {
                             
            $removearr[] = $previous;
          }
        }
      }
      if($token && !is_string($token) && $token[0] == T_WHITESPACE) {
        $previous = $count;
      } else {
        $previous = false;
      }
      $count++;
    }
    $count = 0;

    foreach ($tokens as $token) {
      if (is_string($token)) {
        fwrite($out, $token);
        if(in_array($token,array('?',';',':','}','{','(',')',',','=','|','&','>','<','.','-','+','*','/'))) $next = true;
      }
      else {
        switch ($token[0]) {
          case T_COMMENT:
          case T_DOC_COMMENT:
          case T_OPEN_TAG:
          case T_CLOSE_TAG:
          case T_REQUIRE:
          case T_REQUIRE_ONCE:
          case T_INCLUDE_ONCE:
            break;
        
          case T_WHITESPACE:
            if(!$next && !in_array($count,$removearr)) {
              fwrite($out, ' ');
            }           
            break;
          case T_BOOLEAN_OR:
          case T_BOOLEAN_AND:
          case T_IS_EQUAL:
          case T_IS_GREATER_OR_EQUAL:
          case T_IS_IDENTICAL:
          case T_IS_NOT_EQUAL:
          case T_IS_NOT_IDENTICAL:
          case T_IS_SMALLER_OR_EQUAL:
          case T_PLUS_EQUAL:
          case T_MINUS_EQUAL:
          case T_OR_EQUAL:
          case T_DEC:
          case T_DOUBLE_ARROW:
          case T_ENCAPSED_AND_WHITESPACE:
          case T_CURLY_OPEN:
          case T_INC:
          case T_IF:
          case T_CONCAT_EQUAL:
            $next = true;
            fwrite($out, $token[1]);
            break;
          default:
            $next = false;
            fwrite($out, $token[1]);

        }
 
      }
      $count++;
    }
  }
}

fclose($out);
$after = filesize($target);
$percent = sprintf('%.2f%%', (($after/$before) - 1)*100);
echo 'Compacted '.count($files) . " files \n";
echo "Filesize report: $before bytes to $after bytes ($percent)\n";
echo "Done.\n";

?>
