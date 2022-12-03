<?php
/**
 * TipoContrato Active Record
 * @author  <your-name-here>
 */
class TipoContrato extends TRecord
{
    const TABLENAME = 'tipo_contrato';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('cor');

        parent::addAttribute('TextoPersonalizado');
        parent::addAttribute('ValorContrato');
        parent::addAttribute('QtdConvidados');
    }


}
