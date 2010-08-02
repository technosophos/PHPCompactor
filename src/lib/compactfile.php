<?php
/**
 *
 * Compactor.php -- File class
 *
 * (c) 2010 Jurriaan Pruis (email@jurriaanpruis.nl)
 *
 **/
 
class CompactFile {
  private $filename,$out;
  public $filesize,$compressedsize = 0;
  public function __construct($filename,$out) {
    $this->filename = $filename;
    $this->filesize = filesize($filename);
    $this->out = $out;
  }
  public function compact() {
    printf("Compacting %s -- filesize: %dB", basename($this->filename), $this->filesize);
    $tokens = $this->getTokens();
    $next = false;
    $start = ftell($this->out);
    $len = count($tokens);
    for ($i=0;$len > $i;$i++) {
      $token = $tokens[$i];
			if($i+1 < $len) {
			  $nexttoken = $tokens[$i+1];
			} else {
			  $nexttoken = '';
			}
			if($i-1 > -1) {
			  $prevtoken = $tokens[$i-1];
			} else {
			  $prevtoken = '';
			}
			if (is_string($token)) {
	      $this->write($token);
	      if(in_array($token,Compactor::$safechar)) $next = true;
	    } else if($token[0] == T_WHITESPACE) {
	      
	      if(!$next) {
	        if(is_string($nexttoken)) {
            if(!in_array($nexttoken,Compactor::$safechar) && !(in_array($nexttoken,Compactor::$semisafe) && in_array($prevtoken[0],Compactor::$keyword))) {
              $this->write(' ');
            }
          } else if(!in_array($nexttoken[0],Compactor::$beforetoken)) {
            $this->write(' ');
          } 
          
        }
      } else if(in_array($token[0],Compactor::$aftertoken)) {
        $next = true;
        $this->write($token[1]);
      } else if(in_array($token[0],Compactor::$removable)) {
        $next = true;
      } else if(in_array($token[0],Compactor::$requires)) {

        for ($i2 = $i;$len > $i2;$i2++) {
          $rtoken = &$tokens[$i2];
          if($rtoken == ';') {
            $rtoken = '';
            break;
          } else {
            $rtoken = '';
          }
        }
      } else {
        $next = false;
        
        $this->write($token[1]);
      }
    }
    $this->compressedsize = ftell($this->out) - $start;
    printf(", compressed size: %dB, %.2f%%\n",$this->compressedsize,(($this->compressedsize/$this->filesize) - 1)*100);
  }
  private function write($string) {
    fwrite($this->out, $string);
  }
  private function getTokens() {
    return token_get_all(trim(file_get_contents($this->filename)));
  }
}