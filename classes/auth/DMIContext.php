<?php

namespace auth;

use \SimpleSession;
use \System;
use utils\DOUtil;
use utils\ParamUtil;
use subnav\SubnavFactory;
use model\Seats;
use model\Permissions;
use model\SeatAssignments;
use utils\PageUtil;

class DMIContext extends Context {

    private $isRetrievingAccount = false;
    private $isLoggingIn = false;
    private $loginOptions = array(
        'wapi.auth.authenticate',
        '/account.login',
        'account/login'
    );

    public function __construct(Creds $creds) {
        parent::__construct($creds);
        $ini = System::GetIni();
        $params = \WAPI::GetParams();

        $sl_path = ParamUtil::Get($params, 'sl_path', $this->GetStart($params));
        if ($sl_path == "")
            $sl_path = ParamUtil::Get($params, 'context') . '/' . ParamUtil::Get($params, 'do');
        if (in_array(ParamUtil::Get($_GET, 'do'), $this->loginOptions))
            $this->isLoggingIn = true;
        $this->isLoggingIn = in_array($sl_path, $this->loginOptions);
        // if(!$this->isLoggingIn) $this->isLoggingIn = in_array(DOUtil::Get(),$loginOptions);

        $this->isRetrievingAccount = DOUtil::Contains('forgotpassword');
        $session = SimpleSession::Get();
        $user = $session->GetUserInfo();
        if ($this->isAuth) {

            if ($user) {

                $session->EndAllSessions($user['id']);
            }
            $user = null;
        }
        $isValidSession = !is_null($user);

        $sessionExists = (!$isValidSession && $this->sessMethod == SimpleSession::METHOD_COOKIE);
        if (($session->HasSession() === false) && ($this->isLoggingIn === false)) {
            $start = $this->GetStart();
            print redirect($start);
            return;
            // self::Redirect($start);
            // return;
        }
        if (!$creds->IsSysOwner() && !$creds->IsNull()) {
            if ($this->authState >= Auth::STATE_OK) {
                $override = $this->isAuth;
                $session = $session->CreateSession(\System::Get()->getPersonByUsername($creds->username), array(
                    'application' => 'dmi'
                        ), true, $override);
            }
        }
        if ($this->isLoggingIn) {
            if (($this->authState >= Auth::STATE_OK) && !$creds->IsNull()) {
                $sys = System::Get();
                $username = !is_null($creds->username) ? $creds->username : 'public';
                $user = $sys->getPersonByUsername($username);
                System::Get()->logUserLogin($user->username, $_SERVER['REMOTE_ADDR']);
                $session = $session->CreateSession($user, array(
                    'application' => 'dmi'
                        ), true, true);
            }
        }

        $this->sessState = $session->sessionState;
        $this->sessMethod = $session->sessionMethod;

        if (($this->authState == Auth::STATE_ANON) && ($this->sessState == SimpleSession::STATE_SESS_OK)) {
            if ($session->application == 'dmi') {
                $userInfo = $user;
                $this->authState = Auth::STATE_OK;
            } else {
                $this->sessState = SimpleSession::STATE_SESS_NONE;
            }
        }

        if ($this->authState == Auth::STATE_OK && $this->sessState == SimpleSession::STATE_SESS_NONE) {
            $sys = System::Get();
            $username = !is_null($creds->username) ? $creds->username : $ini->visitor_account;
            $user = $sys->getPersonByUsername($username);
            $override = $this->fromLogin;
            $session = $session->CreateSession($user, array(
                'application' => 'dmi'
                    ), true, $override);
            $this->sessState = $session->sessionState;
            $this->sessMethod = $session->sessionMethod;
        } elseif (($this->authState == Auth::STATE_OK) && ($session->sessionState == SimpleSession::STATE_SESS_EXPIRED)) {
            $session->UpdateSession();
            $this->sessState = $session->sessionState;
        } elseif ($this->authState < Auth::STATE_OK) {

            // $session->EndSession();
        } elseif (($this->authState == Auth::STATE_ANON) && ($this->sessState == SimpleSession::STATE_SESS_NONE)) {
            if (!$this->isLoggingIn)
                PageUtil::RedirectTo('account/login');
        } else {

            if ($session->sessionMethod != SimpleSession::METHOD_TOKEN) {

                $session->UpdateSession(\SimpleSession::UPDATE_SET, true);
            } else {
                $session->UpdateSession(\SimpleSession::UPDATE_SET);
            }
        }

        $this->sessState = $session->sessionState;
        $this->sessMethod = $session->sessionMethod;

        if ($this->isLoggingIn) {

            /*
             * $start = $this->GetStart ();
             *
             * if(stripos($start,'/')) {
             *
             * $start = BASEURL.$start;
             * header ( 'Location: '.$start );
             * return;
             *
             * } else {
             *
             * $start = BASEURL . '?' . $start;
             * }
             *
             * if (substr ( $start, 0, 1 ) == '&') {
             * header ( 'Location: .?' . $start, true );
             * ;
             * } else {
             * if ($start == '.') {
             * header ( 'Location: .' );
             * die ();
             * } else {
             * #print redirect('Location: .?do=' . $this->GetStart () . '&state=' . \RequestUtil::Get ( 'state', 'normal' ));
             * #header ( 'Location: .?do=' . $this->GetStart () . '&state=' . \RequestUtil::Get ( 'state', 'normal' ), true );
             *
             * die ();
             * }
             * }
             */
        } else {
            if ($sessionExists) {
                // $this->sessState = SimpleSession::STATE_SESS_EXISTS;
            }
        }
    }

    public function Exec(array $args = null) {
        if (is_null($args))
            $args = $_REQUEST;

        $session = \SimpleSession::Get();

        $user = $session->GetUser();
        $userInfo = $session->GetUserInfo();
        $userOrg = is_null($userInfo) ? null : \Organization::GetOrgByUserId($userInfo['id']);

        $ini = System::GetIni();

        $context = ParamUtil::Get($args, 'context', null);
        if ($context == '')
            $context = null;
        $sandbox = \System::GetSandbox();

        $do = ParamUtil::Get($args, 'do');
        if ($do == 'start')
            $context = null;

        $api_cmd = ParamUtil::Get($args, 'do');

        $api_cmd = explode('/', $api_cmd);

        $api_cmd_segs = $api_cmd;
        $api_cmd = array();
        $pageArgs = array();

        $currentUser = $session->GetUser();

        foreach ($args as $arg => $val) {
            if ($arg == 'sl_path')
                continue;
            if ($arg == 'do')
                continue;
            $pageArgs[$arg] = $val;
        }

        foreach ($api_cmd_segs as $cmd) {
            if (!strpos($cmd, ':')) {
                $api_cmd[] = $cmd;
                continue;
            }
            $keyval = explode(':', $cmd);
            $key = array_shift($keyval);
            $val = implode(':', $keyval);
            $pageArgs[$key] = $val;
        }

        $pageActor = ($this->authState == Auth::STATE_OK) ? 'user' : 'nonuser';
        if ($this->IsSysAdmin()) {
            $pageActor = 'admin';
        }
        $pageArgs['pageActor'] = $pageActor;

        $pageArgs['userId'] = is_null($userInfo) ? null : $userInfo['id'];

        $pageArgs['userOrgName'] = '';
        $userOrgId = null;
        if (!is_null($userOrg)) {
            $orgId = $userOrg->id;
            $userOrgId = $userOrg->id;
            $pageArgs['userOrgName'] = $userOrg->name;
            $pageArgs['userOrgId'] = $userOrg->id;
        }

        if (!isset($pageArgs['orgId']))
            $pageArgs['orgId'] = $userOrgId;

        if (!is_null($pageArgs['orgId'])) {
            $org = \Organization::GetOrg($pageArgs['orgId']);
            $pageArgs['orgName'] = $org->name;
            $pageArgs['orgAccount'] = $org->short;
        }
        if ($userOrgId == $pageArgs['orgId']) {
            if (!is_null($pageArgs['orgId'])) {
                if ($userOrg->owner->id == $userInfo['id']) {
                    $pageArgs['orgActor'] = 'org_owner';
                } else {
                    $pageArgs['orgActor'] = 'org_member';
                }
            }
        } else {
            $pageArgs['orgActor'] = 'visitor';
        }
        if ($pageActor == 'admin')
            $pageArgs['orgActor'] = 'org_owner';

        $api_cmd = implode("/", $api_cmd);
        $seatAssignments = new SeatAssignments();

        $contextParam = ParamUtil::Get($args, 'context');

        $path = ParamUtil::Get($args, \RequestUtil::PATH_PARAM, '');


        $authContext = Context::Get();
        $loginInfo = array();
        LoginMessages::SetLoginMessages($authContext->authState, $authContext->sessState, $loginInfo);
        $pageArgs['loginInfo'] = $loginInfo;
        $action = ParamUtil::Get($args, 'do', $authContext->GetStart($pageArgs));

        if ($authContext->authState != \SimpleSession::STATE_SESS_OK) {
            $action = $this->GetStart($pageArgs);
        }
        if (is_null($context)) {
            if (stripos($action, '/')) {
                $info = explode('/', $action);
                $context = array_shift($info);
                $api_cmd = implode('/', $info);
                $action = "";
            }
        }

        // echo('action:'.$action);

        $dispatcher = WEBROOT . 'contexts/dmi/dispatchers/';

        $funcName = '_dispatch_';
        $isDispatcher = false;
        $isModule = false;
        $module = null;

        $template = \SLSmarty::GetTemplater(WEBROOT . '/contexts/dmi/templates/');
        if (isset($context)) {
            $rootContext = $context;

            $dispatcher .= $rootContext . '/';
            $actionItems = explode('/', $action);
            $actionItem = array_shift($actionItems);

            $args['action_items'] = $actionItems;
            $dispatcher .= $actionItem;
            $dispatcher .= '.php';
            $styles = array();

            $scripts = array();

            if (!file_exists($dispatcher . '.php')) {
                $api_root = explode('/', $api_cmd);
                $api_root = array_shift($api_root);

                // if($api_cmd) $context.='/'.$api_cmd;

                if (file_exists(WEBROOT . 'client_ui/pages/' . $context . '/' . $api_cmd . '/page.js')) {

                    $isModule = true;
                    $module = $context;
                    $cssIni = WEBROOT . "client_ui/pages/$context/$api_cmd/css.ini";
                    if (file_exists($cssIni)) {
                        $css = parse_ini_file($cssIni, FALSE);
                        foreach ($css['component_styles'] as $file) {
                            $styles[] = BASEURL . "client_ui/" . $file;
                        }
                    }
                }

                // throw new \Exception('No such page');
            } else {

                $isDispatcher = true;
            }
        } else {
            $actionParts = explode('.', $action);
            $actionItem = array_pop($actionParts);

            if (stripos($actionItem, '_wapiold') > -1) {
                $dispatcher .= str_replace('.', '/', $action) . '.php';
                require_once ($dispatcher);
                return call_user_func($funcName . $actionItem, $template, $args);
            }
            $dispatcher .= str_replace('.', '/', $action) . '.php';



            if (stripos($dispatcher, '/download/')) {
                $funcName .= $actionItem;
                require_once ($dispatcher);
                return call_user_func($funcName, $template, $args);
            }
        }

        $template->assign('isModule', $isModule);
        $funcName .= $actionItem;

        $user = \SimpleSession::Get()->GetUser();

        $template->assign('layerTypeEnum', \LayerTypes::GetEnum()->ToJSObj('layerTypeEnum'));
        $template->assign('geomTypeEnum', \GeomTypes::GetEnum()->ToJSObj('geomTypeEnum'));

        // instantiate a Templater
        $template->assign('world', \System::Get());
        $template->assign('user', $user);

        // Display the head if we should.
        $css_url = $ini->css_url;
        $print_css = $ini->print_css;

        $template->assign('print_css', $print_css);
        $template->assign('siteName', $ini->name);

        // var_dump($authContext->sessState, SimpleSession::STATE_SESS_OK,$authContext->authState , Auth::STATE_UNKNOWN);

        $template->assign("loggedIn", ($authContext->sessState >= SimpleSession::STATE_SESS_OK) && ($authContext->authState > Auth::STATE_UNKNOWN));

        $seats = new Seats();
        $seat = null;

        if ($session['seatId']) {
            $seat = $seats->GetSeat($session['seatId']);
        } else {
            $seat = $seats->GetSeat($seats->GetSeatIdByName(Seats::SEATNAME_UNASSIGNED));
        }

        $pageArgs['seatName'] = $seat['data']['seatName'];
        $template->assign('seatname', $seat['data']['seatName']);

        $userId = is_null($user) ? null : $user->id;
        $template->assign('userid', $userId);
        $fullname = is_null($user) ? null : $user->realname;
        $template->assign('fullname', $fullname);
        $template->assign("siteName", $ini->name);
        $template->assign('user', $user);

        $template->assign('baseURL', BASEURL);

        $importOptions = array();

        $formatOptions = array();
        $permissions = $session['permissions'];

        if ($permissions === false) {
            $permissions = array();
        }

        $orgId = ParamUtil::Get($pageArgs, 'orgId', $userOrgId);

        if ($orgId) {
            $org = \Organization::GetOrg($orgId);

            if ($org) {
                $orgGroupId = $org->group->id;
                $pageArgs['orgGroupId'] = $orgGroupId;
                $template->assign('orgId', $orgId);
                $template->assign('orgGroupId', $orgGroupId);
            }
        }

        foreach (\LayerFormats::GetFormatPermissionLookup() as $format => $permPath) {
            // var_dump($format.' '.$permPath. ' '.$permissions[$permPath].' '.($permissions[$permPath] & Permissions::CREATE));
            // echo "<br>";
            // continue;
            $formatOptions[$format] = array();
            $formatOptions[$format]['viewable'] = ($permissions[$permPath] & Permissions::VIEW) > 0;
            $formatOptions[$format]['cantMake'] = ($permissions[$permPath] & Permissions::CREATE) == 0;
            $formatOptions[$format]['canExport'] = ($permissions[$permPath] & Permissions::SAVE) > 0;
        }

        $adminOptions = array();

        $adminOptions['view'] = Permissions::HasPerm($permissions, ':SysAdmin:General:', Permissions::VIEW, Permissions::EDIT);
        $adminOptions['logs']['view'] = Permissions::HasPerm($permissions, array(
                    ':SysAdmin:Logs:AccountChanges:',
                    ':SysAdmin:Logs:AccountLogins:',
                    ':SysAdmin:Logs:LayerTransactions:',
                    ':SysAdmin:Logs:MapUsage:'
                        ), Permissions::VIEW);
        $adminOptions['logs']['maps'] = Permissions::HasPerm($permissions, ':SysAdmin:Logs:MapUsage:', Permissions::VIEW);
        $adminOptions['logs']['layers'] = Permissions::HasPerm($permissions, ':SysAdmin:Logs:LayerTransactions:', Permissions::VIEW);
        $adminOptions['logs']['logins'] = Permissions::HasPerm($permissions, ':SysAdmin:Logs:AccountLogins:', Permissions::VIEW);
        $adminOptions['logs']['accounts'] = Permissions::HasPerm($permissions, ':SysAdmin:Logs:AccountChanges:', Permissions::VIEW);
        $adminOptions['organizations']['list'] = Permissions::HasPerm($permissions, ':SysAdmin:Organizations:', Permissions::VIEW);
        $adminOptions['defaults']['view'] = Permissions::HasPerm($permissions, array(
                    ':SysAdmin:Defaults:Bookmarks:',
                    ':SysAdmin:Defaults:Contacts:',
                    ':SysAdmin:Defaults:Layers:',
                    ':SysAdmin:Defaults:Maps:',
                    ':SysAdmin:Defaults:Pricing:'
                        ), Permissions::VIEW);
        $adminOptions['configuration']['view'] = Permissions::PrefixedHasPerm($permissions, ':SysAdmin:Config', array(
                    'Perms:MasterList',
                    'Plans',
                    'Roles',
                    'Seats',
                    'Signups',
                    'SystemIdentification'
                        ), Permissions::VIEW) > 0;
        $adminOptions['configuration']['permissions'] = Permissions::HasPerm($permissions, ':SysAdmin:Config:Perms:MasterList:', Permissions::VIEW | Permissions::EDIT);
        $adminOptions['configuration']['plans'] = Permissions::HasPerm($permissions, ':SysAdmin:Config:Plans:', Permissions::VIEW | Permissions::EDIT);
        $adminOptions['configuration']['roles'] = Permissions::HasPerm($permissions, ':SysAdmin:Config:Roles:', Permissions::VIEW | Permissions::EDIT);
        $adminOptions['configuration']['seats'] = Permissions::HasPerm($permissions, ':SysAdmin:Config:Seats:', Permissions::VIEW | Permissions::EDIT);

        $inviteOptions = array();
        $inviteOptions['view'] = Permissions::HasPerm($permissions, ":Organization:Invites", Permissions::VIEW);

        $pageArgs['permissions'] = $permissions;

        $adminOptions['user_accounts']['view'] = Permissions::PrefixedHasPerm($permissions, ':SysAdmin:Config', array(
                    'UserAccounts',
                    'UserAccounts:Spoof'
                        ), Permissions::VIEW);

        $template->assign('adminOptions', $adminOptions);
        $template->assign('inviteOptions', $inviteOptions);

        $template->assign('formatOptions', $formatOptions);
        $template->assign('dojo', BASEURL . 'lib/js/' . $ini->dojo_version . '/');
        $template->assign('themeCSS', 'lib/js/' . $ini->dojo_version . '/dijit/themes/tundra/tundra.css');
        $template->assign('DialogCSS', 'lib/js/' . $ini->dojo_version . '/dijit/themes/tundra/Dialog.css');

        $dojoVersion = $ini->dojo_version;

        $scripts[] = BASEURL . "lib/js/$dojoVersion/dojo/dojo.js";
        // $styles[] = BASEURL . "lib/bootstrap-3.3.5-dist/css/bootstrap-theme.min.css";
        // $styles[] = BASEURL . "lib/bootstrap-3.3.5-dist/css/bootstrap.css";
        $styles[] = BASEURL . "lib/bootstrap5/css/bootstrap.css";
        header('X-UA-Compatible: IE=edge,chrome=1');
        if ($isModule) {
            PageUtil::SetPageArgs($pageArgs, $template);
            $pageArgs = PageUtil::MixinPlanArgs($template);

            $styles[] = BASEURL . 'client_ui/components/sl_nav/style.css';
            $template->assign('css_url', null);
            $template->assign('context', $rootContext);
            $template->assign('page', $api_cmd);

            $template->assign('clientUI', 'client_ui/components/');
            $template->assign('styles', $styles);
            $template->assign('userInfo', json_encode(SimpleSession::Get()->GetUserInfo(), JSON_FORCE_OBJECT));

            $template->assign('pageArgs', json_encode($pageArgs));
            $template->display("sl_page_header.tpl");

            $userPermission = $session['permissions'];

            $title = str_replace('\/', ':', $api_cmd);
            $title = array_pop(explode(':', $api_cmd));
            $title = explode(' ', str_replace('_', ' ', $api_cmd));
            for ($i = 0; $i < count($title); $i++) {
                $title[$i] = ucfirst($title[$i]);
            }
            $title = implode(' ', $title);
            $subnav = SubnavFactory::GetNav($rootContext);
            if ($subnav) {
                $subnav->makeDefault($user, $title, $org, $pageArgs);
                echo $subnav->fetch();
            } else {
                echo "<div class='mainContent'></div>";
            }

            // $template->assign('subnav',$subnav->fetch());
        } else {
            $template->assign('dojo', BASEURL . 'lib/js/' . $ini->dojo_version . '/');
            // $template->assign('page',$api_cmd);
            $template->assign('isLegacy', true);
            $styles[] = BASEURL . "client_ui/master.css";
            $styles[] = BASEURL . "styles/style.css";
            $styles[] = BASEURL . "styles/pivot.css";
            $styles[] = BASEURL . "styles/jquery.editable-select.css";
            $styles[] = BASEURL . "styles/jquery.tagsinput.css";
            $styles[] = BASEURL . "styles/jquery.tagsinput.css";

            $styles[] = BASEURL . "styles/toolbar.css";
            // $styles[]="icon";
            // $styles[]="shortcut icon";
            $styles[] = BASEURL . 'client_ui/components/sl_nav/style.css';

            $scripts[] = BASEURL . "lib/js/fsmenu.js";
            $scripts[] = BASEURL . "lib/js/json2.js";
            $scripts[] = BASEURL . "lib/js/jquery.js";
            $scripts[] = BASEURL . "lib/js/jquery.tooltip.min.js";
            $scripts[] = BASEURL . "lib/js/jquery.dataTables.min.js";
            $scripts[] = BASEURL . "lib/js/jquery.jsonQueue.js";
            $scripts[] = BASEURL . "lib/js/jquery.pivot.js";
            $scripts[] = BASEURL . "lib/js/jquery.editable-select.pack.js";
            $scripts[] = BASEURL . "lib/js/jquery.maskedinput-1.3.min.js";
            $scripts[] = BASEURL . "lib/js/jquery.tagsinput.js";
            $scripts[] = BASEURL . "lib/js/jquery.tools.min.js";

            /*
             * <script type="text/javascript" src="<!--{$baseURL}-->lib/js/fsmenu.js"></script>
             * <script type="text/javascript" src="<!--{$baseURL}-->lib/js/json2.js"></script>
             *
             *
             * <script type="text/javascript" src="<!--{$baseURL}-->lib/js/jquery.js"></script>
             * <script type="text/javascript" src="<!--{$baseURL}-->lib/js/jquery.tooltip.min.js"></script>
             * <script type="text/javascript" src="<!--{$baseURL}-->lib/js/jquery.dataTables.min.js"></script>
             * <script type="text/javascript" src="<!--{$baseURL}-->lib/js/jquery.jsonQueue.js"></script>
             * <script type="text/javascript" src="<!--{$baseURL}-->lib/js/jquery.pivot.js"></script>
             * <script type="text/javascript" src="<!--{$baseURL}-->lib/js/jquery.editable-select.pack.js"></script>
             * <script type="text/javascript" src="<!--{$baseURL}-->lib/js/jquery.maskedinput-1.3.min.js"></script>
             * <script type="text/javascript" src="<!--{$baseURL}-->lib/js/jquery.tagsinput.js"></script>
             * <script type="text/javascript" src="<!--{$baseURL}-->lib/js/jquery.tools.min.js"></script>
             */

            $template->assign('styles', $styles);
            $template->assign('scripts', $scripts);
            $template->assign('css_url', $css_url);
            $template->display("sl_page_header.tpl");
            // $template->display ( "header_loggedin.tpl" );
        }

        if ($isModule) {

            $dojoURL = BASEURL . '/lib/js/' . $dojoVersion . '/dojo/dojo.js';
            $jsonArgs = json_encode($pageArgs);
            $template->assign('jsonArgs', $jsonArgs);
            $template->assign('dojoURL', $dojoURL);
            $template->display("page.tpl");
        } else {


            $template->assign('styles', $styles);

            if (!file_exists($dispatcher)) {

                throw new \Exception('Requested resource not found:' . $dispatcher);
            }
            require_once ($dispatcher);
            $args['user'] = $user;
            $args['world'] = System::Get();

            if ($isModule) {
                
            } else {

                $pageArgs['permissions'] = $permissions;
                $template->assign('pageArgs', json_encode($pageArgs));

                call_user_func($funcName, $template, $args, $org, $pageArgs);
                $pageArgs = PageUtil::MixinMapArgs($template);
                $pageArgs = PageUtil::MixinLayerArgs($template);
                $pageArgs = PageUtil::MixinContactArgs($template);
                $pageArgs = PageUtil::MixinGroupArgs($template);
                $pageArgs = PageUtil::MixinPlanArgs($template);
            }
            $jsonArgs = json_encode($pageArgs);
            $template->assign('jsonArgs', $jsonArgs);
            $template->display("page.tpl");
        }

        $template->display('footer.tpl');
    }

    public function GetApp() {
        return "dmi";
    }

    public function GetStart(&$pageArgs = null) {
        if (is_null($pageArgs))
            $pageArgs = array();
        $ini = System::GetIni();
        $start = "";
        $session = \SimpleSession::Get();
        // echo "auth state:<br>";

        if ($this->authState < Auth::STATE_OK) {
            $start = 'account/login';
            switch ($this->authState) {
                case Auth::STATE_ERROR_INVALID_CREDS:
                    $pageArgs['state'] = 'invalid_creds';
                    break;
                case Auth::STATE_ERROR_NEEDPW:
                    $pageArgs['state'] = 'invalid_creds';
                    break;
                case Auth::STATE_ERROR_NEED_PW_RETRIEVAL:
                    $pageArgs['state'] = 'invalid_creds';
                    break;
                case Auth::STATE_ERROR_PW_SENT:
                    $pageArgs['state'] = 'invalid_creds';
                    break;
                case Auth::STATE_UNKNOWN:
                    $pageArgs['state'] = 'invalid_creds';
                    break;
            }
        }

        if ($start != "")
            return $start;
        // $start = \RequestUtil::Get ( 'return_to', $start );
        if ($this->sessState < SimpleSession::STATE_SESS_OK) {

            $target = \RequestUtil::Get('do');
            if ($target === 'login') {
                $target = 'account/login';
            }
            $targetIsLogin = $target == 'account/login';

            $sandbox = \System::GetSandbox();

            if ($sandbox) {
                // $sandbox = 'commnexis';
                $orgTest = \Organization::GetOrgByUserName($sandbox);
                if ($orgTest) {
                    $pageArgs['loginInfo']['org_disclaimer'] = 'true';
                    $pageArgs['loginInfo']['sandbox_org'] = $orgTest->id;
                }
            }
            switch ($this->sessState) {
                case SimpleSession::STATE_SESS_NONE:
                    if (!$targetIsLogin)
                        $pageArgs['return_to'] = $start = 'account/login';
                    break;
                case SimpleSession::STATE_SESS_EXPIRED:
                    $pageArgs['state'] = 'expired';
                    $pageArgs['return_to'] = \RequestUtil::GetURL();
                    $start = 'account/login';
                    break;
                case SimpleSession::STATE_SESS_EXISTS:
                    $pageArgs['state'] = 'exists';
                    if (is_null($session->GetUserInfo())) {
                        $pageArgs['return_to'] = \RequestUtil::GetURL();
                        $start = 'account/login';
                    } else {
                        $pageArgs['return_to'] = \RequestUtil::GetURL();
                        $start = 'account/login';
                    }
                    break;
            }
        }

        if ($start != "")
            return $start;

        /*
         * $return_to = $pageArgs['return_to'];
         * if ($return_to) {
         * return \RequestUtil::GetURL ();
         * }
         */

        return $ini->default_page;
    }

    public function SetLoginMessages($state, $template) {
        $states = array_keys($this->stateStrings);
        if (!in_array($state, $states))
            $state = 'normal';

        $info = $this->stateStrings[$state];

        foreach ($info as $key => $val) {
            $user = \SimpleSession::Get()->GetUserInfo();

            if ($user) {
                $name = $user['username'];
                $val = str_replace('%username%', $name, $val);
            }

            $template->assign($key, $val);
        }
        $template->assign('state', $state);
        return;
        switch ($state) {
            case 'expired':
                $template->assign('messageHeader', 'Session Expired');
                $template->assign('message', "No session activity detected in over an hour. Please log in again.");
                break;
            case 'exists':
                $template->assign('messageHeader', 'Simultaneous session attempted');
                $template->assign('message', "It appears that someone is currently logged into an active as user {$user['username']}. If you wish to continue logging in as {$user['username']} click 'Continue' to end other sessions or 'Cancel' to login with a different account. ");
                break;
            case 'invalid_creds':
                $template->assign('messageHeader', 'Incorrect Credentials');
                $template->assign('message', 'The username and password provided were not recognized.');
                break;
            case 'needpw':
                $template->assign('messageHeader', 'Password required');
                $template->assign('message', 'A password is needed to log in.');
                break;
            case 'forgotpw':
                $template->assign('messageHeader', 'Password Reset');
                $template->assign('message', 'Enter your username below and an email will be sent to you with further instructions.');
                break;
            case 'pwsent':
                $template->assign('messageHeader', 'Password email sent');
                $template->assign('message', 'After resetting your password, or if you remember your password, please enter a username and password to continue.');
                break;
            case 'none':
            default:
                $template->assign('messageHeader', 'Welcome to SimpleLayers');
                $template->assign('message', 'Enter a username and password to continue.');
                break;
        }
        $template->assign('state', $state);
    }

}

?>
