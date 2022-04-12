<?php
use model\SeatAssignments;
use auth\Context;
use utils\PageUtil;
use utils\ParamUtil;
/**
 * Administration: Form to create a new user.
 * 
 * @package Dispatchers
 */
/**
 */
function _config_report() {
	$config = Array ();
	// Start config
	$config ["sendUser"] = true;
	$config ["sendWorld"] = true;
	$config ["admin"] = false;
	// Stop config
	return $config;
}
function _dispatch_report($template, $args, $org, $pageArgs) {
	$pageArgs ['pageSubnav'] = 'org';
	$pageArgs['orgId'] = $org->id;	
	$world = $args ["world"];
	$user = $args ["user"];
	$id = ParamUtil::GetOne($args,'id','orgId');
	if (! is_null( $id ) && $user->organization !== false)
		$_REQUEST ['id'] = $user->organization->id;
	if ((!is_null( $id ) && $id != $user->organization->id) && ! $user->admin) {
		print javascriptalert ( 'You are not in an organization.' );
		return print redirect ( 'mainmenu' );
	}
	
	$org = $world->getOrganizationById ( $id );
	$pageArgs['pageTitle'] = $org->name .' - Usage Report';
	if (! $org) {
		print javascriptalert ( 'That organization was not found, or you are not a member.' );
		return print redirect ( 'organization.info' );
	}
	if ($org->owner->id != $args ['user']->id && ! Context::Get ()->IsSysAdmin ()) {
		print javascriptalert ( 'You are not the owner of this organization.' );
		return print redirect ( 'organization.info&id=' . $org->id );
	}
	
	$template->assign ( 'org', $org );
	
	$source = Array ();
	
	if (! isset ( $_REQUEST ["view"] ) || $_REQUEST ["view"] == "layerdisk" || $_REQUEST ["view"] == "mediadisk") {
		$table = Array (
				"name" => "Disk Usage",
				"type" => "data",
				"id" => "disk",
				"head" => Array (
						"Source",
						"Size" 
				),
				"data" => Array () 
		);
		$layerUsage = $org->owner->diskUsageDB () + $org->owner->diskUsageFiles ();
		foreach ( $org->group->getMembers () as $person ) {
			$layerUsage += $person->diskUsageDB () + $person->diskUsageFiles ();
		}
		$mediaUsage = $org->diskusage;
		$table ["data"] [] = Array (
				'<a href="./?do=organization.report&view=layerdisk&id='.$org->id.'" >Layers</a>',
				Units::bytesToString ( $layerUsage ) 
		);
		$table ["data"] [] = Array (
				'<a href="./?do=organization.report&view=mediadisk&id='.$org->id.'" >Media</a>',
				Units::bytesToString ( $mediaUsage ) 
		);
		
		$table ["data"] [] = Array (
				"Total",
				Units::bytesToString ( $mediaUsage + $layerUsage ) 
		);
		$source [] = $table;
	}
	if (! isset ( $_REQUEST ["view"] )) {
		$assignments = new SeatAssignments ();
		/*
		 * $table = Array("name" => "Staff Usage", "type"=>"data", "id" => "staff", "head" => Array("Seat", "Used", "Limit", "Available"), "data" => Array()); $table["data"][] = Array("Staff", $org->st_seats-$org->seatsLeft(1), $org->st_seats, $org->seatsLeft(1)); $table["data"][] = Array("Executive", $org->ex_seats - $org->seatsLeft(2), $org->ex_seats, $org->seatsLeft(2)); $table["data"][] = Array("Power Users", $org->po_seats - $org->seatsLeft(3), $org->po_seats, $org->seatsLeft(3)); $table["data"][] = Array("Total Members", ($org->st_seats+$org->ex_seats+$org->po_seats)-($org->seatsLeft(1)+$org->seatsLeft(2)+$org->seatsLeft(3)), ($org->st_seats+$org->ex_seats+$org->po_seats), ($org->seatsLeft(1)+$org->seatsLeft(2)+$org->seatsLeft(3)));
		 */
		// $source[] = $table;
	}
	function by_size($a, $b) {
		return $a->diskusage < $b->diskusage;
	}
	function by_size_media($a, $b) {
		return $a ["diskusage"] < $b ["diskusage"];
	}
	if (isset ( $_REQUEST ["view"] )) {
		if ($_REQUEST ["view"] == "layerdisk") {
			$table = Array (
					"name" => "Layer Disk Usage",
					"type" => "data",
					"id" => "layerdisk",
					"head" => Array (
							"Layer",
							"Owner",
							"Size" 
					),
					"data" => Array () 
			);
			$members = $org->group->getMembers ();
			$members [] = $org->owner;
			foreach ( $members as $person ) {
				$layers = $person->listLayers ();
				usort ( $layers, 'by_size' );
				foreach ( $layers as $layer ) {
					$size = $layer->diskusage;
					
					$ctr = 0;
					
					while ( $size >= 1000 ) {
						$ctr ++;
						$size = $size / 1024;
					}
					
					$table ["data"] [] = Array (
							'<a href="./?do=layer.info&id=' . $layer->id . '">' . $layer->name . '</a>',
							'<a href="./?do=contact.info&id=' . $person->id . '">' . $person->realname . " (" . $person->username . ")</a>",
							Units::bytesToString ( $size ) 
					);
				}
			}
			$source [] = $table;
		}
		if ($_REQUEST ["view"] == "mediadisk") {
			$table = Array (
					"name" => "Media Disk Usage",
					"type" => "data",
					"id" => "mediadisk",
					"head" => Array (
							"Name",
							"Media",
							"Size" 
					),
					"data" => Array () 
			);
			$media = $org->getMedia ();
			usort ( $media, 'by_size_media' );
			foreach ( $media as $file ) {
				$table ["data"] [] = Array (
						$file ["name"],
						$file ["name"],
						number_format ( $file ["diskusage"] / 1024, 2 ) . " kB" 
				);
			}
			$source [] = $table;
		}
	}
	$template->assign ( 'source', $source );
	PageUtil::SetPageArgs($pageArgs, $template);
	/*
	 * $subnav = new OrganizationSubnav(); $subnav->makeDefault($user,'',$org); $template->assign('subnav',$subnav->fetch());
	 */
	$template->display ( 'organization/report.tpl' );
}
?>
