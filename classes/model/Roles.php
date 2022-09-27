<?php

namespace model;

use model\MongoCRUD;
use utils\ParamUtil;

class Roles extends MongoCRUD {

    const ROLE_CONTEXT_SYSADMIN = 'sysadmin';
    const ROLE_CONTEXT_SYSTEM = 'system';
    const ROLE_CONTEXT_ORG = 'org';
    const ROLE_CONTEXT_GROUP = 'group';
    const ROLE_CONTEXT_APP = 'app';
    const ROLE_DEFAULT_OWNER = 'Power User';

    protected $generalContexts = array(self::ROLE_CONTEXT_SYSADMIN, self::ROLE_CONTEXT_SYSTEM);
    protected $allContexts = array(self::ROLE_CONTEXT_SYSADMIN, self::ROLE_CONTEXT_SYSTEM, self::ROLE_CONTEXT_ORG, self::ROLE_CONTEXT_GROUP, self::ROLE_CONTEXT_APP);
    protected $collectionName = 'roles';

    /* (non-PHPdoc)
     * @see \model\MongoCRUD::__construct()
     */

    public function __construct() {
        // TODO: Auto-generated method stub
        parent::__construct();
        $doc = array();
        foreach ($this->generalContexts as $context) {
            #$this->MakeDocument(array('context'=>$context));
        }
        $this->collection->createIndex(array('data.context' => 1), array('unique' => 1));
    }

    public function AddRoleContext($context) {
        return $this->MakeDocument(array('context' => $context, 'roles' => array()));
    }

    public function UpdateRoleContext($changeSetDoc) {
        if (isset($changeSetDoc['isDeleted'])) {
            if ($changeSetDoc['isDeleted'])
                return $this->RemoveRoleContext($changeSetDoc['id']);
        }

        $doc = $this->FindOneByCriteria(\Comparisons::ToMongoCriteria('id', \Comparisons::COMPARE_EQUALS, $changeSetDoc['id']));
        $doc['data']['context'] = $changeSetDoc['context'];
        $this->Update($doc);
        return($doc);
    }

    public function UpdateRole($context, $changeSetDoc) {
        $roles = array();
        foreach ($context['data']['roles'] as $role) {
            if ($role['id'] == $changeSetDoc['id']) {
                if (isset($changeSetDoc['isDeleted'])) {
                    if ($changeSetDoc['isDeleted'])
                        continue;
                }
                $role['name'] = $changeSetDoc['name'];
                $roles[] = $role;
            } else {
                $roles[] = $role;
            }
        }
        $context['data']['roles'] = $roles;
        $this->Update($context);
    }

    public function RemoveRoleContext($contextId = null) {
        $this->DeleteItem($contextId);
    }

    public function GetRoleContext($contextId) {
        $result = $this->FindOneByCriteria(\Comparisons::ToMongoCriteria('id', \Comparisons::COMPARE_EQUALS, $contextId));
        return $result;
    }

    public function GetRoleContextByName($contextName) {
        $results = $this->FindByCriteria(\Comparisons::ToMongoCriteria('data.context', \Comparisons::COMPARE_EQUALS, $contextName));
        $result = iterator_to_array($results);
        return $result;
    }

    public function GetRoleContextByRole($roleId) {
        $context = $this->FindOneByCriteria(\Comparisons::ToMongoCriteria('data.roles.id', \Comparisons::COMPARE_EQUALS, $roleId));
        return $context;
    }

    public function GetRoleContexts() {
        $contexts = $this->collection->find(array(), array('data.context', '_id' => false, 'id'));
        $results = array();
        foreach ($contexts as $context) {
            $results[] = array('id' => $context['id'], 'context' => $context['data']['context']);
        }

        return $results;
    }

    public static function GetDefaultContextId() {
        $me = new Roles();
        $context = $me->FindOneByCriteria(\Comparisons::ToMongoCriteria('data.context', \Comparisons::COMPARE_EQUALS, 'System'));
        return $context['id'];
    }

    public function GetDefaultContext() {
        $context = $this->FindOneByCriteria(\Comparisons::ToMongoCriteria('data.context', \Comparisons::COMPARE_EQUALS, 'System'));
        return $context;
    }

    public function GetRolesByContext($contextId = null) {
        if (is_null($contextId)) {
            $document = $this->GetDefaultContext();
        } else {
            $criteria = \Comparisons::ToMongoCriteria('id', \Comparisons::COMPARE_EQUALS, $contextId);
            $document = $this->FindOneByCriteria($criteria);
        }
        if (!$document) {
            return null;
        }
        if (!isset($document['data'])) {
            $document['data']['roles'] = array();
        }
        return $document;
    }

    public function AddRole($roleName, $contextId = null) {

        $criteria = \Comparisons::ToMongoCriteria('id', \Comparisons::COMPARE_EQUALS, $contextId);
        $cursor = $this->FindByCriteria($criteria);
        if (!$cursor)
            throw new \Exception("Context not recognized: could not find a record for role context " . $contextId);
        // $doc = $doc->getNext();
        foreach ($cursor as $context) {
            $doc = $context;
            break;
        }
        if (!$doc)
            throw new \Exception("Context not recognized: could not find a record for role context " . $contextId);

        foreach ($doc['data']['roles'] as $role) {
            if ($role['name'] == $roleName)
                throw new \Exception('duplicate key error');
        }
        $role = $this->MakeSubDoc(array('name' => $roleName));
        $rolePermissions = new RolePermissions();
        $ref = $rolePermissions->CreatePermissionsDoc($contextId, $role['id']);

        $role['permissions'] = $ref;

        $doc['data']['roles'][] = $role;
        $this->Update($doc);
        $rolePermissions = new RolePermissions();

        return $doc;
        #$this->MakeDocument(array('permission'=>trim($permissionPath)),true);	
    }

    public static function GetRoleById($roleId) {
        $me = new Roles();

        return $me->GetRole($roleId);
    }

    public function GetRole($roleId) {

        $criteria = \Comparisons::ToMongoCriteria('data.roles.id', \Comparisons::COMPARE_EQUALS, $roleId);
        $criteria = array();
        $roleDoc = $this->FindOneByCriteria($criteria);
        foreach ($roleDoc['data']['roles'] as $role) {
            if ($role['id'] == $roleId)
                return $role;
        }
        return null;
    }

    public function UpdatePermission($docOrDocs) {
        if (ParamUtil::IsAssoc($docOrDocs))
            return $this->Update($docOrDocs);
        foreach ($docOrDocs as $doc) {
            $this->Update($doc);
        }
    }

    public function GetRolePermissions($roleId) {
        $RP = new RolePermissions();
        $permissions = null;
        $rolePerms = $RP->GetPermissionsByIds(null, $roleId);
        $rolePerms = $RP->ListPermissions($permissions, true, null, true);
        return $rolePerms;
    }

    public static function GetPermissions($roleId) {
        $roles = new Roles();
        return $roles->GetRolePermissions($roleId);
    }

    public function GetPermissionSet($permPath) {
        if (substr($permPath, 0, 1) != ':')
            $permPath = ":" . $permPath;
        if (substr($permPath, -1) != ':')
            $permPath .= ':';
        $regex = new \MongoRegex('/^' . $permPath . '/i');
        return $this->FindByCriteria(array('permission' => $regex));
    }

    public function Reset() {
        $this->collection->remove();
    }

}
