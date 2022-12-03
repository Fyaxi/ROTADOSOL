<?php
/**
 * Papel Active Record
 * @author  <your-name-here>
 */
class Papel extends TRecord
{
    const TABLENAME = 'papel';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
    }


}
