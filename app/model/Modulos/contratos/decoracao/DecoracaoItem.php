<?php
/**
 * DecoracaoItem Active Record
 * @author  <your-name-here>
 */
class DecoracaoItem extends TRecord
{
    const TABLENAME = 'decoracao_item';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('servico_id');
        parent::addAttribute('contrato_id');
        parent::addAttribute('valor');
        parent::addAttribute('quantidade');
        parent::addAttribute('total');
    }


}
