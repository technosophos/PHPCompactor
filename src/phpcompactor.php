<?php
/**
 * Compact PHP code.
 *
 * Strip comments, combine entire library into one file.
 *
 * Modified by Jurriaan Pruis - Better 'compression'
 *
 * TODO: merge the two foreach structures
 * TODO: more tokens.. for example T_PRINT & T_ECHO (Problems when printing constants) echo'test'; and echo$test; are allowed, echoENT_QUOTES; not (of course)
 * TODO: strip require/include (_once) paths from source
 *
 */
 
if ($argc < 3) {
  print "Strip unecessary data from PHP source files.\n\n\tUsage: php phpcompactor.php DESTINATION.php SOURCE.php";
  exit;
}
 
 
$source = $argv[2];
$target = $argv[1];
//print "Compacting $source into $target.\n";
 
include $source;
 
$files = get_included_files();
 
$out = fopen($target, 'w');
fwrite($out, '<?php' . PHP_EOL);
$next = false;
foreach ($files as $f) {
  echo $f. "\n";
  if ($f !== __FILE__) {
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
                          $token[0] = token_name($token[0]);
  
            break;
          default:
            $next = false;
            fwrite($out, $token[1]);
                      $token[0] = token_name($token[0]);

        }
 
      }
      $count++;
    }
  }
}

fclose($out);
?>
