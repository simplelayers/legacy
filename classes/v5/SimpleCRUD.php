<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace v5;

/**
 * Description of SimpleCRUD
 *
 * @author arthu
 */
class SimpleCRUD {

    protected $table = 'override_me';
    protected $fieldNames = ['id', 'override', 'me', 'too'];
    protected $idField = 'id';
    protected $id = null;

    function List() {
        $db = \System::GetDB();
        $results = $db->Execute('select * from ' . $this->table);
        return $results;
    }

    function RetrieveEmptyRecord() {
        return $this->Retrieve(-1);
    }

    function RetrieveRecordFromParams($params) {
        $id = \utils\ParamUtil::Get($id, $this->idField, null);
        if (is_null($id)) {
            return false;
        }
        return $this->RetrieveRecord($id);
    }

    function RetrieveRecord($id) {
        $db = \System::GetDB();
        $record = $db->GetRow("select * from {$this->table} where {$this->idField} = ?", $id);
        return $record;
    }

    function Retrieve($id) {
        $db = \System::GetDB();
        $record = $db->Execute("select * from {$this->table} where {$this->idField} = ?", $id);
        return $record;
    }

    function Upsert($args) {
        $data = \utils\ParamUtil::SubsetAssocArray($args, $this->fieldNames, true);
        $db = \System::GetDB();

        if (isset($data[$this->idField])) {
            $id = $data[$this->idField];
            $record = $this->Retrieve($id);

            if ($record) {
                $sql = $db->getUpdateSql($record, $data);
                $db->Execute($sql, $id);
                $record = $this->RetrieveRecord($id);
            }
        } else {
            $record = $this->RetrieveEmptyRecord();
            if ($record) {
                $sql = $db->getInsertSql($record, $data);
                $id = $db->GetOne($sql . 'Returning Id');
                $record = $this->RetrieveRecord($id);
            }
        }
        return $record;
    }

    function DeleteRecord($args) {
        $id = \utils\ParamUtil::Get($args, 'id', null);
        if (is_null($id)) {
            return false;
        }
        $db = \System::GetDB();
        $db->Execute(
                "delete from {$this->table} where {$this->idField}=?",
                array($id)
        );
    }

    function Get($record, $what, $default = null) {
        $whats = \explode('.', $what);
        $primaryWhat = \array_shift($whats);
        $whatHandler = "__get_{$primaryWhat}";
        if (method_exists($this, $whatHandler)) {
            return call_user_func_array([$this, $whatHandler], [$record, $default, $whats]);
        } else {
            return $default;
        }
    }

    function Set($record, $what, $value) {
        $whats = \explode('.', $what);
        $primaryWhat = \array_shift($whats);
        $whatHandler = "__set_{$primaryWhat}";
        if (method_exists($this, $whatHandler)) {
            return call_user_func_array([$this, $whatHandler], [$record, $value, $whats]);
        }
        throw new \Exception('Property not found:'.$primaryWhat.' is not a recognized property of '. get_class($this));
    }

}
