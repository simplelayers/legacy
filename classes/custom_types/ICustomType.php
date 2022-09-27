<?php
namespace custom_types;

interface ICustomType
{

	public function __construct($db);

	public function AddCustomType();

	public function RemoveCustomType();
}
