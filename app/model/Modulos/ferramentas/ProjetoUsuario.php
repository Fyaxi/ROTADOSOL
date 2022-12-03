<?php
/**
 * ProjetoUsuario Active Record
 * @author  <your-name-here>
 */
class ProjetoUsuario extends TRecord
{
    const TABLENAME = 'projeto_usuario';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('projeto_id');
        parent::addAttribute('system_user_id');
    }


}
