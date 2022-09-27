<?php

require_once( dirname(__FILE__). "/interfaces/IFormatter.class.php");

class LayerFormatter implements IFormatter
{
		const BASIC= "basic";
	const OWNER = "owner";
	const LABELS = "labels";
	const DESCRIPTION = "description";
	const TAGS = "tags";
	const CLASSIFICATION = "classification";
	const SUBLAYERS = "sublayers";
	const ATTRIBUTES = "attributes";
	
	public $options;
	protected $user;
	protected $world;
	protected $userId;
	public function __construct($world , $user ) {
		$this->world = $world;	
		$this->user = $user;
		$userId = (is_null($this->user)) ? 0 : $user->id;
		$this->options = new FlagEnum(self::BASIC,
										self::OWNER,
										self::LABELS,
										self::DESCRIPTION,
										self::TAGS,
										self::CLASSIFICATION,
										self::ATTRIBUTES);
	}
	
	
	public function __get( $what ) {
		if( $what == "max" ) return $this->options->GetMaxValue();
		return $this->options[$what];
	}
	public function WriteJSON( $layer, $preferences=null ) {
		echo json_encode($this->MakeJSON($layer, $preferences));
	}
	private function MakeJSON( $layer, $preferences=null ) {
		$alevels = AccessLevels::GetEnum();
		$ltypes = LayerTypes::GetEnum();
		
		if( is_null($preferences) ) {
			$preferences = $this->options[self::BASIC] | $this->options[self::OWNER] | $this->options[self::SUBLAYERS];
		}
		
		if( !($layer instanceof Layer) ) {
			$layer = $this->world->getLayerById($layer,$preferences);
		}
		
		$layerArray = Array();
		$layerArray['id'] = $layer->id;
		$layerArray['type'] = $layer->type;
		$layerArray['type_label'] = $ltypes[$layer->type];
		
		$layerArray['name'] = $layer->name;
		$access = $this->world->auth->isAnonymous ? 1 :  $layer->getPermissionById($this->userId);
		$layerArray['access'] = $access;
		$layerArray['access_label'] = $alevels[$access];
		
		$layerArray['geom'] = $layer->geomtypestring;

		if(  $this->options->isFlagged( self::OWNER, $preferences) ) {
			$owner = $layer->owner;
			$layerArray['owner'] = $owner->id;
			$layerArray['owner_name'] = $owner->username; 
		}
		
		if( $this->options->isFlagged( self::LABELS, $preferences) ) {
			$layerArray['attribute'] = $layer->labelitem;
			$layerArray['label_style'] = $layer->label_style;
		}
		
		if( $this->options->isFlagged( self::DESCRIPTION, $preferences) ){
			$layerArray['description>'] = $this->toCDATA($layer->description);
		}
		
		if( $this->options->isFlagged( self::TAGS, $preferences) ) {
			$layerArray['tags>'] = $this->toCDATA($layer->tags);
		}
		
		if( $this->options->isFlagged( self::ATTRIBUTES, $preferences ) ) {
			foreach( $layer->getAttributesVerbose(true) as $attribute=>$value ) {
				$layerArray['layerAttributes'][] = Array('name' => $attribute, 'requirement' => $value->requires, 'type' => $value->type, 'maxlength' => $value->maxlength,'display'=>$value->display,'z'=>$value->z);
			}
		}	$layerArray['layerAttributes'] = Array();
		
		
		if( $this->options->isFlagged( self::CLASSIFICATION, $preferences) ) {
			$colorscheme= $layer->colorscheme;
			if( $colorscheme ) {
				$symbol = $colorscheme->getAllEntries();
				$symbol = $symbol[0];
				if( !is_null($symbol) ) {
					$symbol = $layer->colorscheme->getAllEntries();
					$symbol = $symbol[0];	
					$layerArray['classification']['type'] = $layer->colorschemetype;
					$layerArray['classification']['stroke'] = $symbol->stroke_color;
					$layerArray['classification']['fill'] = $symbol->fill_color;
					$layerArray['classification']['symbol_size'] = $symbol->symbol_size;
					$layerArray['classification']['attribute'] = $symbol->criteria1;
					$layerArray['classification']['symbol'] = $symbol->symbol;
						
					
				}
			}
		}
		
		
		if($this->options->isFlagged( self::SUBLAYERS, $preferences ) ) {
		    
			if( $layer->type == LayerTypes::COLLECTION) {
			    
				$layerArray['sublayers'] = Array();
				$subs = LayerCollection::GetSubs($this->world,$layer->id,true) ;
				$layerArray['sublayers'] = array();
				foreach( LayerCollection::GetSubs($this->world,$layer->id) as $sub ) {
					$layerArray['sublayers'][] = $this->MakeJSON($sub, $preferences);
				}
			}
		
		}
		return $layerArray;
	}
	
	public function WriteXML( $layer, $preferences=null ) {
		$alevels = AccessLevels::GetEnum();
		$ltypes = LayerTypes::GetEnum();
		
		if( is_null($preferences) ) {
			$preferences = $this->options[self::BASIC] | $this->options[self::OWNER] | $this->options[self::SUBLAYERS];
		}
		
		if( !($layer instanceof Layer) ) {
			$layer = $this->world->getLayerById($layer,$preferences);
		}
		
		echo '<layer ';
		$this->toAttribute( 'id', $layer->id );
		$this->toAttribute( 'type',$layer->type);
	
		$this->toAttribute( 'type_label',$ltypes[$layer->type] );
		$this->toAttribute( 'name', $layer->name);
	
		$access = $layer->getPermissionById($this->userId);
		$this->toAttribute( 'access',$layer->getPermissionById($this->userId) );
		
		$this->toAttribute( 'access_label',$alevels[$access] );
		$geomtypestring = ( $layer->type == LayerTypes::COLLECTION ) ? "collection" : $layer->geomtypestring;
		$this->toAttribute('geom', $geomtypestring);
		
		if(  $this->options->isFlagged( self::OWNER, $preferences) ) {
			$owner = $layer->owner;
			$this->toAttribute( "owner",$owner->id);
			$this->toAttribute( "owner_name",$owner->username); 
		}
		
		echo " >";
		echo "<searchtip>";
		$this->toCDATA( $layer->searchtip);
		echo "</searchtip>";
		if( $this->options->isFlagged( self::LABELS, $preferences) ) {
			echo '<labels ';
			$this->toAttribute('attribute',$layer->labelitem);
			echo ' >';
			echo '</labels>';
		}
		
		if( $this->options->isFlagged( self::DESCRIPTION, $preferences) ){
			echo "<description>";
			$this->toCDATA($layer->description);
			echo "</description>";
		}
		
		if($this->options->isFlagged( self::TAGS, $preferences) ) {
			echo "<tags>";
			$this->toCDATA($layer->tags);
			echo "</tags>";
		}
		
		if( $this->options->isFlagged( self::ATTRIBUTES, $preferences ) ) {
			echo "<layerAttributes>";
				foreach( $layer->getAttributesVerbose(true) as $attribute=>$value ) {
					echo "<attribute";
					$this->toAttribute('name',$attribute);
					$this->toAttribute('requirement',$value->requires);
					$this->toAttribute('type',$value->type);
					$this->toAttribute('maxlength',$value->maxlength);
					$this->toAttribute('display',$value->display);
					$this->toAttribute('z',$value->z);
					echo " />";
				}
			echo "</layerAttributes>";
		}
		
		if( $this->options->isFlagged( self::CLASSIFICATION, $preferences) ) {
			$colorscheme= $layer->colorscheme;
			if( $colorscheme ) {
				$symbol = $colorscheme->getAllEntries();
				$symbol = $symbol[0];
				
				echo "<classification";
					if( !is_null($symbol) ) {
						$symbol = $layer->colorscheme->getAllEntries();
						$symbol = $symbol[0];	
						$this->toAttribute('type',$layer->colorschemetype);
						$this->toAttribute('stroke',$symbol->stroke_color);
						$this->toAttribute('fill',$symbol->fill_color);
						$this->toAttribute('symbol_size',$symbol->symbol_size);
						$this->toAttribute('attribute',$symbol->criteria1 );
						$this->toAttribute('label_style',htmlentities($layer->label_style_string));
							
					}
				echo "/>";
			}
		}
		
		if($this->options->isFlagged( self::SUBLAYERS, $preferences ) ) {
			if( $layer->type == LayerTypes::COLLECTION) {
				echo '<sublayers>';
				$subs = LayerCollection::GetSubs($this->world,$layer->id,true) ;
				
				foreach( LayerCollection::GetSubs($this->world,$layer->id) as $sub ) {
					$this->WriteXML($sub, $preferences);
				}
				echo '</sublayers>';
			}
		
		}
		echo '</layer>';
	}
	
	protected function toAttribute( $name,$val) {
		echo " ".$name."=\"$val\"";
	}
	
	protected function toCDATA( $data ) {
		$data = str_replace("]]>","]] >",$data);
		echo "<![CDATA[".$data."]]>";
	}



}

?>