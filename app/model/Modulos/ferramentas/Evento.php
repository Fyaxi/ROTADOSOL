<?php
/**
 * Evento Active Record
 * @author  <your-name-here>
 */
class Evento extends TRecord
{
    const TABLENAME = 'evento';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('inicio');
        parent::addAttribute('fim');
        parent::addAttribute('titulo');
        parent::addAttribute('descricao');
        parent::addAttribute('cor');
        parent::addAttribute('system_user_id');
        parent::addAttribute('festa_id');
        parent::addAttribute('decoracao_id');
    }


}
