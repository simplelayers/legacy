<?php
use utils\UserIconUtil;
use utils\PageUtil;
use model\Seats;
use model\SeatAssignments;
use model\Permissions;

/**
 * Print info about a person.
 * 
 * @package Dispatchers
 */
/**
 */
function _config_info()
{
    $config = Array();
    // Start config
    // Stop config
    return $config;
}

function _dispatch_info($template, $args, $org, $pageArgs)
{
    $user = $args['user'];
    $world = $args['world'];
    
    $contactId = RequestUtil::Get('contactId', RequestUtil::Get('id', $user->id));
    $person = ($contactId == $user->id) ? $user : $world->getPersonById($contactId);
    $pageArgs['pageSubnav'] = 'community';
    $pageArgs['contactId'] = $contactId;
    $pageArgs['pageTitle'] = ($user->id == $pageArgs['userId']) ? 'Community - Your Contact Info' : 'Community - Contact Info for ' . $user->realname;
    $pageArgs['contactName'] = $person->realname;
    
    $contactOrg = Organization::GetOrgByUserId($contactId);
    
    $pageArgs['contactOrgName'] = $contactOrg->name;
    
    $seat = SeatAssignments::GetUserSeat($contactId);
    $contactSeat = $seat['data']['seatname'];
    $pageArgs['contactSeat'] = $seat['data']['seatName'];
    
    $contactOrgOwner = $contactOrg->owner->id;
    
    $pageArgs['isContact'] = $user->buddylist->isOnListById($contactId);
    if ($contactId == $user->id)
        unset($pageArgs['isContact']);
        
        // fetch the person
    if (! $person or (! $person->canBeSeenById($user->id) and $user->id != $person->id)) {
        if(!Permissions::HasPerm($pageArgs['permissions'],':SysAdmin:General:',Permissions::VIEW)) {
            print javascriptalert('This person has elected to make their profile private.');
            return print redirect('contact.list');
        }
    }
    $template->assign('person', $person);
    
    // translate their list of tags into hyperlinks
    $template->assign('taglinks', activate_tags($person->tags, './?do=contact.list&tag='));
    
    $canEditContact = ($contactId == $user->id);
    
    if (! $canEditContact) {
        if ($pageArgs['pageActor'] == 'admin') {
            $canEditContact = true;
        } elseif ($contactOrgOwner == $user->id) {
            $canEditContact = true;
        }
    }
    $pageArgs['canEditContact'] = $canEditContact;
    
    PageUtil::SetPageArgs($pageArgs, $template);
    
    /*
     * if($person == $user) {
     * SubnavFactory::UseNav(SubnavFactory::SUBNAV_ACCOUNT,$template);
     * } else {
     *
     * $subnav = SubnavFactory::GetNav(SubnavFactory::SUBNAV_CONTACT);
     * $subnav->makeDefault($person, $user);
     *
     * SubnavFactory::SetNav($subnav, $template);
     * }
     */
    
    $iconURI = UserIconUtil::GetIconURL($person->id);
    
    $hasIcon = ($iconURI !== false);
    $template->assign('hasIcon', $hasIcon);
    $template->assign('selector', 'true');
    
    $template->assign('canEditContact', $canEditContact);	
	$template->display('contact/info.tpl');
    }
?>