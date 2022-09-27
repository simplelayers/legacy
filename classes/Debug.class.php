<?php
/**
  * A class for timing things.
  * @package ClassHierarchy
  */

/**
 * A class for timing things.
 *
 * @package ClassHierarchy
 */
class Debug {
   /**
     * @var float The Unix time (with microseconds) when the timer was started.
     */
   private $time_start;
   /**
     * @var float The Unix time (with microseconds) when the last event was triggered.
     */
   private $time_previous;
   /**
    * @ignore
    */
   function __construct() {
        $this->reset();
   }

    /**
     * Reset the timer to 0.
     * This is called automatically when the Debug timer is instantiated.
     */
    function reset() {
        $this->time_previous = $this->time_start = microtime(TRUE);
    }

    /**
     * Log an event and the elapsed time since reset() was called or the Debug timer was created.
     * @param string comment An optional comment or note to associate with the event in the log.
     */
    function tick($comment='Timer event') {
        $message = sprintf("%s, Tick %f, Total %f",
                           $comment,
                           microtime(TRUE) - $this->time_previous,
                           microtime(TRUE) - $this->time_start
                           );
        
        $this->time_previous = microtime(TRUE);
    }


}
?>
