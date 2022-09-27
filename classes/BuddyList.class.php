<?php
/**
  * See the BuddyList class documentation.
  * @package ClassHierarchy
  */

/**
  * @ignore
  */

/**
 * A BuddyList instance is automagically present inside a Person, and is fetched via $user->buddylist 
 * 
 * Public Attributes:
 * - owner -- The BuddyList's link to the Person who owns it. Read-only. Example: $person->buddylist->owner->username
 * 
 * @package ClassHierarchy
 */

class BuddyList {
   /**
     * @ignore
     */
   private $world;
   /**
     * @ignore
     */
   public $owner;
   /**
     * @ignore
     */
   function __construct(&$world,&$owner) {
      $this->world  = $world;
      $this->owner  = $owner;
   }

   /////
   ///// adding and removing people, and querying the list
   /////
   /**
     * @return array An array of integers, being ID#s for the people who are on this Person's buddy list.
     * Example: foreach ($user->buddylist->getFlatListOfIds() as $userid) print $userid;
     */
   function getFlatListOfIds() {
      $ids = $this->world->db->Execute('SELECT buddy FROM  buddylist WHERE owner=?', array($this->owner->id) )->getRows();
      $ids = array_map( create_function('$a','return $a["buddy"];') , $ids);
      return $ids;
   }
   /**
     * @return array An array of Person objects, being the people who are on this Person's buddy list.
     * Example: $people = $user->buddylist->getFlatListOfPeople();
     */
   function getFlatListOfPeople() {
      return array_map( array($this->world,'getPersonById') , $this->getFlatListOfIds() );
   }
   /**
     * @param string $username A username.
     * @return boolean True/false indicating whether the specified person is on the buddy list.
     * Example: if ($user->buddylist->isOnListByUsername('bob')) print "Yes, he's a buddy.";
     */
   function isOnListByUsername($username) {
      $id = $this->world->getUserIdFromUsername($username);
      return $this->isOnListById($id);
   }
   /**
     * @param integer $id A user's unique ID#.
     * @return boolean Same as isOnListByUsername() except that it takes an ID# instead of a username.
     * Example: if ($user->buddylist->isOnListById(12)) print "Yes, he's a buddy.";
     */
   function isOnListById($id) {
      if ($id===false) return false;
      $id = $this->world->db->Execute('SELECT * FROM buddylist WHERE owner=? AND buddy=?', array($this->owner->id,$id) );
      return (bool) !$id->EOF;
   }
   /**
     * Find the specified user in the World and add them to this person's buddy list. If they are already on the
     * list, then their category will be updated. As such, this is also the method for updating a buddy's category.
     * Example: $user->buddylist->addPersonByUsername('bob');
     * @param string $username A username.
     * @param integer $category Optional; the category for this newly-added friend.
     */
   function addPersonByUsername($username) {
      $person = System::Get()->getPersonByUsername($username);
      return $this->addPerson($person);
   }
   
   function addPerson($person) {
       if (!$person || ($person->id==$this->owner->id)) return false;
       // go ahead and add them
       $id = $person->id;
       if(!$this->isOnListById($id)){
           $this->world->db->Execute('INSERT INTO buddylist (owner,buddy) VALUES (?,?)', array($this->owner->id,$id) );
           $person->notify($this->owner->id, "added you as a contact.", "", $this->owner->id, "./?do=contact.info&id=".$this->owner->id, 7);
       }
   }
   
   /**
     * Same as addPersonByUsername() except that it takes an ID# instead of a username.
     * Example: $user->buddylist->addPersonById(12);
     * @param integer $id A user's unique ID#.
     * @param integer $category Optional; the category for this newly-added friend.
     * @return Same as addPersonByUsername() except that it takes an ID# instead of a username.
     */
   function addPersonById($id) {
   	
      // load the person, then check sanity: trhey must exist, cannot be admin, and cannot be oneself
      $person = $this->world->getPersonById($id);
      return $this->addPerson($person);
    }
   /**
     * Remove the specified person from the person's buddy list.
     * Example: $user->buddylist->removePersonByUsername('bob');
     * @param string $username A username.
     */
   function removePersonByUsername($username) {
      $id = $this->world->getUserIdFromUsername($username);
      return $this->removePersonById($p->id);
   }
   /**
     * Same as removePersonByUsername() except that it accepts a user's ID# instead of their username.
     * Example: $user->buddylist->removePersonById(12);
     * @param string $id A user's unique ID#.
     */
   function removePersonById($id) {
      if ($id===false) return false;
      $this->world->db->Execute('DELETE FROM buddylist WHERE owner=? AND buddy=?', array($this->owner->id,$id) );
      // also, delete any special accesslevels they may have been assigned
      $this->world->db->Execute('DELETE FROM layersharing WHERE who=? AND layer in (SELECT id FROM layers WHERE owner=?)', array($id,$this->owner->id) );
      $this->world->db->Execute('DELETE FROM projectsharing WHERE who=? AND project in (SELECT id FROM projects WHERE owner=?)', array($id,$this->owner->id) );
   }

}

?>
