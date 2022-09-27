<?php
use model\mapping\PixoSpatial;
require_once (dirname ( __FILE__ ) . "/interfaces/IFormatter.class.php");
class ProjectFormatter implements IFormatter {
	protected $preferences;
	const BASIC = "basic";
	const OWNER = "owner";
	const ADDER = "adder";
	const PROJECTION = "projection";
	const TAGS = "tags";
	const DESCRIPTION = "description";
	const BOUNDS = "bounds";
	const BOUNDS_ADJUSTED = "bounds_adjusted";
	const viewSize = "viewSize";
	const LAYERS = "layers";
	const LAYERS_AS_Ps = 'layers_as_ps';
	const MAPCONFIG = 'map_config';
	public $options;
	protected $world;
	protected $user;
	protected $viewSize = array (
			null,
			null 
	);
	protected $projection;
	protected $noScale = false;
	protected $layerPreferences = null;
	public function __construct($world, $user) {
		$this->world = $world;
		$this->user = $user;
		$this->options = new FlagEnum ( self::BASIC, self::OWNER, self::ADDER, self::PROJECTION, self::TAGS, self::DESCRIPTION, self::BOUNDS, self::viewSize, self::LAYERS, self::LAYERS_AS_Ps,self::MAPCONFIG );
	}
	public function __get($what) {
		if ($what == "max")
			return $this->options->GetMaxValue ();
		return $this->options [$what];
	}
	public function SetProjectContext($permission, $width = null, $height = null, $projection = null, $noScale = false, $layerPreferences = null) {
		$this->viewSize [0] = $width;
		$this->viewSize [1] = $height;
		$this->projection = $projection;
		$this->permission = $permission;
		$this->noScale = true;
		$this->layerPreferences = $layerPreferences;
	}
	public function WriteXML($project, $preferences = null, $opt_bbox = null) {
		$bbox = is_null ( $opt_bbox ) ? $project->bbox : $opt_bbox;
		$pixo = new PixoSpatial($bbox, $this->viewSize[0], $this->viewSize[1]);
		$pixo->MoveToROI($bbox,0);
		//$pixo->SetInitialLevel();
		$bbox = $pixo->GetBBox();
		// error_log($bbox);
		$oldsize = explode ( ",", $project->windowsize );
		$defaultProj4 = $this->world->projections->defaultProj4;
		$targetSRID = isset ( $this->projection ) ? $this->projection : $project->projectionSRID;
		$targetProj4 = $this->world->projections->getProj4BySRID ( $targetSRID );
		
		//$this->viewSize [0] = is_null ( $this->viewSize [0] ) ? $this->viewSize [0] : $oldsize [0];
		//$this->viewSize [1] = is_null ( $this->viewSize [1] ) ? $this->viewSize [1] : $oldsize [1];
		
		echo "<project";
		
		$this->toAttribute ( "id", $project->id );
		$this->toAttribute ( "name", $project->name );
		$this->toAttribute ( "projection", "4326" );
		$this->toAttribute ( "access", $this->permission );
		$this->toAttribute ( "lpa", ($project->allowlpa ? "1" : "0") );
		$this->toAttribute ( "private", ($project->private ? "1" : "0") );
		
		if ($this->options->IsFlagged ( self::OWNER, $preferences )) {
			$this->toAttribute ( "owner", $project->owner->id );
			$this->toAttribute ( "owner_name", $project->owner->username );
			$this->toAttribute ("owner_realname",$project->owner->realname);
		}
		
		if ($this->options->IsFlagged ( self::ADDER, $preferences )) {
			$this->toAttribute ( "viewer", $this->user->id );
			$this->toAttribute ( "viewer_name", $this->user->username );
		}
		echo ">";
		if($this->options->IsFlagged(self::MAPCONFIG, $preferences)) {
		  $this->toTextElement('mapConfig', $project->config);		
		}
		if ($this->options->IsFlagged ( self::TAGS, $preferences )) {
		    $this->toTextElement('tags',$project->tags );
		}
		
		if ($this->options->isFlagged ( self::DESCRIPTION, $preferences )) {
		    
			$this->toTextElement( 'description', htmlspecialchars ( $project->description ) );
		}
		
		if ($this->options->isFlagged ( self::BOUNDS, $preferences )) {
			/*$projector = new Projector_MapScript ();
			$projector->SetViewExtents ( $project->bbox );
			
			$projector->SetViewSize ( $oldsize [0], $oldsize [1] );
			
			// error_log($bbox);
			list ( $x1, $y1, $x2, $y2 ) = explode ( ',', $bbox );
			$projector->SetViewExtents ( $bbox );
			$projector->CropToView ( $oldsize [0], $oldsize [1], $this->viewSize [0], $this->viewSize [1] );
			
			$exts = $projector->GetROIExtents ( 'string' );
			*/
			$exts = $bbox;
			// error_log($exts);
			echo "<extents>";
			echo "<projected ";
			$this->toAttribute ( "bbox", $exts );
			echo " />";
			echo "<unprojected";
			$this->toAttribute ( "bbox", $exts );
			echo "/>";
			
			/*
			 * if( $this->options->isFlagged( self::BOUNDS_ADJUSTED, $preferences) ) { $projector = new Projector_MapScript(); if( !is_null($request['noscale']) ) { $projector->CropToView( $oldsize[0], $oldsize[1], $this->viewSize[0], $this->viewSize[1],0,0); } else { $projector->FitToView( $oldsize,$this->viewSize); //$projector->SetviewSize($viewSize[0],$viewSize[1]); } $exts = $projector->GetROIExtents('string'); $extents = $projector->ProjectExtents( $defaultProj4 , $targetProj4 , $oldsize[0], $oldsize[1] , $bbox ); $this->toAttribute("bbox", $exts['to']); echo " />"; echo "<unprojected"; $this->toAttribute("unbbox",$exts['from']); echo "/>"; } else {
			 */
			/*$this->toAttribute("bbox",$bbox);
				echo "/>";
				echo"<unprojected bbox=\"$bbox\" />";
			//}
			 *
			 */
			echo "</extents>";
		}
		
		if ($this->options->isFlagged ( self::viewSize, $preferences )) {
			echo ("<viewSize width=\"{$this->viewSize[0]}\" height=\"{$this->viewSize[1]}\" />");
		}
		
		if ($this->options->isFlagged ( self::LAYERS, $preferences )) {
			
			$formatter = new ProjectLayerFormatter ( $this->world, $this->user );
			echo "<layers>";
			$projectLayers = $project->getLayers ( true, 'asc' );
			foreach ( $projectLayers as $projectLayer ) {
				if ($projectLayer instanceof ProjectLayer) {
					$formatter->WriteXML ( $projectLayer );
				}
			}
			echo "</layers>";
		}
		echo "</project>";
	}
	public function WriteJSON($project, $preferences = null, $opt_bbox = null) {
		
		echo json_encode ( $this->MakeJSON ( $project, $preferences ) );
	}
	public function MakeJSON($project, $preferences = null, $opt_bbox = null) {
		
		$bbox = is_null ( $opt_bbox ) ? $project->bbox : $opt_bbox;
		// error_log($bbox);
		$oldsize = explode ( ",", $project->windowsize );
		$defaultProj4 = $this->world->projections->defaultProj4;
		$targetSRID = isset ( $this->projection ) ? $this->projection : $project->projectionSRID;
		$targetProj4 = $this->world->projections->getProj4BySRID ( $targetSRID );
		
		$this->viewSize [0] = is_null ( $this->viewSize [0] ) ? $this->viewSize [0] : $oldsize [0];
		$this->viewSize [1] = is_null ( $this->viewSize [1] ) ? $this->viewSize [1] : $oldsize [1];
		$json = array ();
		$projectInfo = array ();
		$projectInfo ['id'] = $project->id;
		$projectInfo ['name'] = $project->name;
		$projectInfo ['projection'] = '4326';
		$projectInfo ['access'] = $this->permission;
		;
		$projectInfo ['lpa'] = $project->allowlpa ? "1" : "0";
		$projectInfo ["private"] = $project->private ? "1" : "0";
		
		if ($this->options->IsFlagged ( self::OWNER, $preferences )) {
			$projectInfo ["owner"] = $project->owner->id;
			$projectInfo ["owner_name"] = $project->owner->username;
		}
		
		if ($this->options->IsFlagged ( self::ADDER, $preferences )) {
			$projectInfo ["viewer"] = $this->user->id;
			$projectInfo ["viewer_name"] = $this->user->username;
		}
		
		if ($this->options->IsFlagged ( self::TAGS, $preferences )) {
			$projectInfo ['tags'] = $project->tags;
		}
		
		if ($this->options->isFlagged ( self::DESCRIPTION, $preferences )) {
			$projectInfo ['description'] = $project->description;
		}
		
		if ($this->options->isFlagged ( self::BOUNDS, $preferences )) {
			$projector = new Projector_MapScript ();
			$projector->SetViewExtents ( $project->bbox );
			$projector->SetViewSize ( $oldsize [0], $oldsize [1] );
			// error_log($bbox);
			list ( $x1, $y1, $x2, $y2 ) = explode ( ',', $bbox );
			$projector->SetViewExtents ( $bbox );
			$projector->CropToView ( $oldsize [0], $oldsize [1], $this->viewSize [0], $this->viewSize [1] );
			$exts = $projector->GetROIExtents ( 'string' );
			// error_log($exts);
			
			$extents = array ();
			$extents ['projected'] = $exts;
			$etents ['unprojected'] =$exts;
			
			$projectInfo ['extents'] = $extents;
			
			/*
			 * if( $this->options->isFlagged( self::BOUNDS_ADJUSTED, $preferences) ) { $projector = new Projector_MapScript(); if( !is_null($request['noscale']) ) { $projector->CropToView( $oldsize[0], $oldsize[1], $this->viewSize[0], $this->viewSize[1],0,0); } else { $projector->FitToView( $oldsize,$this->viewSize); //$projector->SetviewSize($viewSize[0],$viewSize[1]); } $exts = $projector->GetROIExtents('string'); $extents = $projector->ProjectExtents( $defaultProj4 , $targetProj4 , $oldsize[0], $oldsize[1] , $bbox ); $this->toAttribute("bbox", $exts['to']); echo " />"; echo "<unprojected"; $this->toAttribute("unbbox",$exts['from']); echo "/>"; } else {
			 */
			/*$this->toAttribute("bbox",$bbox);
				echo "/>";
				echo"<unprojected bbox=\"$bbox\" />";
			//}
			 *
			 */
		
		}
		
		if ($this->options->isFlagged ( self::viewSize, $preferences )) {
			$projectInfo ['viewSize'] = array (
					'width' => $this->viewSize [0],
					'height' => $this->viewSize [1] 
			);
		}
		
		if ($this->options->isFlagged ( self::LAYERS, $preferences )) {
			$formatter = new ProjectLayerFormatter ( $this->world, $this->user );
			$projectLayers = $project->getLayers ();
			$projectInfo ['layers'] = array ();
			foreach ( $projectLayers as $projectLayer ) {
				$player = $formatter->MakeJSON ( $projectLayer );
				$projectInfo ['layers'] [$player['id']] = $player;
			}
		}
		
		$json ['project'] = $projectInfo;
		return $json;
	}
	protected function toAttribute($name, $val) {
		echo " " . $name . "=\"$val\"";
	}
	protected function toTextElement($name,$data) {
	    echo "<$name>";
	    $this->toCDATA($data);
	    echo "</$name>";
	}
	protected function toCDATA($data) {
		$data = str_replace ( "]]>", "]] >", $data );
		echo "<![CDATA[" . $data . "]]>";
	}
}

?>