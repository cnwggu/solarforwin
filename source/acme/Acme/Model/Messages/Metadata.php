<?php
/**
 * 
 * Table metadata for the Acme_Model_Messages model class.
 * 
 * This class is auto-generated by make-model; any changes you make will be
 * overwritten the next time you use make-model.  Modify the Acme_Model_Messages
 * class instead of this one.
 * 
 */
class Acme_Model_Messages_Metadata extends Acme_Sql_Model_Metadata
{
    public $table_name = 'messages';
    
    public $table_cols = array (
      'id' => array (
        'name' => 'id',
        'type' => 'int',
        'size' => NULL,
        'scope' => NULL,
        'default' => NULL,
        'require' => true,
        'primary' => true,
        'autoinc' => true,
      ),
      'name' => array (
        'name' => 'name',
        'type' => 'varchar',
        'size' => 20,
        'scope' => NULL,
        'default' => NULL,
        'require' => false,
        'primary' => false,
        'autoinc' => false,
      ),
      'pwd' => array (
        'name' => 'pwd',
        'type' => 'varchar',
        'size' => 20,
        'scope' => NULL,
        'default' => NULL,
        'require' => false,
        'primary' => false,
        'autoinc' => false,
      ),
      'msg' => array (
        'name' => 'msg',
        'type' => 'text',
        'size' => NULL,
        'scope' => NULL,
        'default' => NULL,
        'require' => false,
        'primary' => false,
        'autoinc' => false,
      ),
    );
    
    public $index_info = array (
    );
}
