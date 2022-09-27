<?php
/**
  * See the Changes class documentation.
  * @package ClassHierarchy
  */

/**
 * The class representing changes to objects, per ticker 535.
 * 
 * @package ClassHierarchy
 */

class Changes {
   /**
     * @ignore
     */
   private $world;		// a link to the World we live in
   /**
    * @ignore
    */
   function __construct(&$world) {
      $this->world     = $world;

      // verify that we exist. If not, commit noisy suicide
      if (!$this->world) throw new Exception("No world");
   }


    /**
     * Create a log or something? This can't work since it violates null constraints, since it hasn't been defined.
     * @param string $who I have no idea.
     * @param string $type The type of object being queried, e.g. Project.
     * @param string $id The ID# of the object being queried.
     */
    function LogCreate($who,$type,$id) {
        $this->world->db->Execute('INSERT INTO changes (who,target_type,target_id) VALUES (?,?,?)', array($who,$type,$id) );
    }

    /**
     * Retrieve a log or something?
     * @param string $who I have no idea.
     * @param string $type The type of object being queried, e.g. Project.
     * @param string $id The ID# of the object being queried.
     */
    function LogRetrieve($who,$type,$id) {
        $this->world->db->Execute('SELECT * FROM changes WHERE target_type=? AND target_id=? AND who=?', array($type,$id,$who) );
    }

    /**
     * Update a log or something?
     * @param string $who I have no idea.
     * @param string $type The type of object being queried, e.g. Project.
     * @param string $id The ID# of the object being queried.
     */
    function LogUpdate($who,$type,$id) {
        $this->world->db->Execute('UPDATE changes SET true WHERE who=? AND target_type=? AND target_id=?', array($who,$type,$id) );
    }

    /**
     * Delete a log or something?
     * @param string $who I have no idea.
     * @param string $type The type of object being queried, e.g. Project.
     * @param string $id The ID# of the object being queried.
     */
    function LogDelete($who,$type,$id) {
        $this->world->db->Execute('DELETE FROM changes WHERE target_type=? AND target_id=? AND who=?', array($type,$id,$who) );
    }

    /**
     * Add a log or something?
     * @param string $who I have no idea.
     * @param string $type The type of object being queried, e.g. Project.
     * @param string $id The ID# of the object being queried.
     */
    function LogAdd($who,$type,$id) {
        $this->world->db->Execute('INSERT INTO changes (who,target_type,target_id) VALUES (?,?,?)', array($who,$type,$id) );
    }

    /**
     * Query the log or something?
     * @param string $since The earliest date for which logs will be retrieved, YYYY-MM-DD format.
     * @param string $type The type of object being queried, e.g. Project.
     * @param string $id The ID# of the object being queried.
     * @return array Something?
     */
    function QueryLog($since,$type,$id) {
        $this->world->db->Execute('SELECT * FROM changes WHERE timestamp>=? AND target_type=? AND target_id=?', array($since,$type,$id) );
    }

}
?>