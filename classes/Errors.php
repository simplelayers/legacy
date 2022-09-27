<?php

namespace errors;

class NoProject
extends \Exception {

	protected $message = 'The specified Map does not exist';	

}

class NoLayer
extends \Exception {

	protected $message = 'The requested Layer is not in the specified Map, or is not the correct data type';

}

class NotOwner
extends \Exception {

	protected $message = 'Only the owner can do that';

}

class NeedRead
extends \Exception {

	protected $message = 'This operation requires at least read permission';

}

class NeedEdit
extends \Exception {

	protected $message = 'This operation requires at least edit permission';

}

class WrongGeom
extends \Exception {

	protected $message = 'The Layer geometry or data type is not appropriate for this operation';

}

class ColorschemeTooLarge
extends \Exception {

	protected $message = 'The resulting color scheme would be too large';

}


class UserLevel
extends \Exception {

	protected $message = "This Map is restricted according to its owner's preferences.";

}

class PGConnect
extends \Exception {
	
	protected $message = "There was a problem connecting got the database";
}

?>