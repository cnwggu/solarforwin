<?php
/**
 * 
 * Table metadata for the Acme_Model_Tags model class.
 * 
 * This class is auto-generated by make-model; any changes you make will be
 * overwritten the next time you use make-model.  Modify the Acme_Model_Tags
 * class instead of this one.
 * 
 */
class Acme_Model_Tags_Metadata extends Acme_Sql_Model_Metadata
{
    public $table_name = 'tags';
    
    public $table_cols = array (
      'id' => array (
        'name' => 'id',
        'type' => 'mediumint',
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
        'require' => true,
        'primary' => false,
        'autoinc' => false,
      ),
    );
    
    public $index_info = array (
    );
}
