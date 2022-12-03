<?php
/**
 * TextoPersonalizado Active Record
 * @author  <your-name-here>
 */
class TextoPersonalizado extends TRecord
{
    const TABLENAME = 'texto_personalizado';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('Texto');
    }

}
