<?php
/**
 * PessoaPapel Active Record
 * @author  <your-name-here>
 */
class PessoaPapel extends TRecord
{
    const TABLENAME = 'pessoa_papel';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('pessoa_id');
        parent::addAttribute('papel_id');
    }


}
