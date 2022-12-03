<?php
/**
 * Fase Active Record
 * @author  <your-name-here>
 */
class Fase extends TRecord
{
    const TABLENAME = 'fase';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('ordem');
        parent::addAttribute('projeto_id');
    }


}
