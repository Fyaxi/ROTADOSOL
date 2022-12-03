<?php
/**
 * ContaReceberPg Active Record
 * @author  <your-name-here>
 */
class ContaPagarPg extends TRecord
{
    const TABLENAME = 'conta_pagar_pg';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('recebimento_id');
        parent::addAttribute('formarecebimento_id');
        parent::addAttribute('valor');
    }

}
