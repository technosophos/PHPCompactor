<?php
/**
 *
 * Compactor.php -- Main class
 *
 * (c) 2010 Jurriaan Pruis (email@jurriaanpruis.nl)
 *
 **/
require_once "compactfile.php";
class Compactor {
  public static $safechar = array('?',';',':','}','{','(',')',',','=','|','&','>','<','.','-','+','*','/');
  public static $semisafe = array('"','\'');
  public static $removable = array(T_COMMENT,T_COMMENT,T_DOC_COMMENT,T_OPEN_TAG,T_CLOSE_TAG);
  public static $requires = array(T_REQUIRE_ONCE,T_INCLUDE_ONCE); // use require_once and include_once for including of static files
  public static $aftertoken = array(T_BOOLEAN_OR, T_BOOLEAN_AND, T_IS_EQUAL, T_IS_GREATER_OR_EQUAL, 
                           T_IS_IDENTICAL, T_IS_NOT_EQUAL, T_IS_NOT_IDENTICAL, T_IS_SMALLER_OR_EQUAL, 
                           T_PLUS_EQUAL, T_MINUS_EQUAL, T_OR_EQUAL, T_DEC, T_DOUBLE_ARROW, 
                           T_ENCAPSED_AND_WHITESPACE, T_CURLY_OPEN, T_INC, T_IF, T_CONCAT_EQUAL,T_WHITESPACE);
  public static $beforetoken = array(T_BOOLEAN_OR, T_BOOLEAN_AND, T_IS_EQUAL, T_IS_GREATER_OR_EQUAL, 
                           T_IS_IDENTICAL, T_IS_NOT_EQUAL, T_IS_NOT_IDENTICAL, T_IS_SMALLER_OR_EQUAL, 
                           T_PLUS_EQUAL, T_MINUS_EQUAL, T_OR_EQUAL, T_DEC, T_DOUBLE_ARROW, 
                           T_ENCAPSED_AND_WHITESPACE, T_CURLY_OPEN, T_INC, T_IF, T_CONCAT_EQUAL,T_WHITESPACE,
                           T_VARIABLE,  T_CONSTANT_ENCAPSED_STRING);
  public static $keyword = array(T_ECHO,T_PRINT,T_CASE);
  
  private $compacted = array();
  private $handle;
  
  /** The files excluded during a {@link compactAll()}. */
  protected $excludes = array();
  
  public function __construct($outfile) {
    $this->handle = fopen($outfile, 'w');
    fwrite($this->handle, '<?php' . PHP_EOL);
  }
  
  public function compact($file) {
    $compact = new CompactFile($file,$this->handle);
    $compact->compact();
    $this->compacted[] = $compact;
  }
  
  /**
   * Exclude these files from compacting.
   *
   * @param array $files
   *  An array of files to exclude.
   */
  public function exclude($files) {
    $expanded = array();
    // Expand wildcards:
    foreach ($files as $pattern) {
      $expanded += glob($pattern);
    }
    
    // Probably should do something to remove duplicates.
    
    $this->excludes = $expanded;
  }
  
  /**
   * Returns the expanded exclusion list.
   */
  public function getExcludedFiles() {
    return $this->excludes;
  }
  
  /**
   * Compact the given file and all included files.
   *
   * Files specified in the 'excludes' list will not be compacted here.
   *
   * @param string $baseFile
   *  The base file to exclude.
   */
  public function compactAll($baseFile) {
    $before = get_included_files();
    include $baseFile;
    $files = array_diff(get_included_files(),$before);

    foreach($files as $file) $this->compact($file);
  }
  
  public function report() {
    $lenbefore = 0;
    $lenafter = 0;
    foreach($this->compacted as $compact) {
      $lenbefore += $compact->filesize;
    }
    $filecount = count($this->compacted);
    $lenafter= ftell($this->handle);
    $percent = sprintf('%.2f%%', (($lenafter/$lenbefore) - 1)*100);
    echo "Compacted $filecount files into one \n";
    echo "Filesize report: $lenbefore bytes to $lenafter bytes ($percent)\n";
    echo "Done.\n";
  }
  public function close() {
    fclose($this->handle);
  }
  
  
}
