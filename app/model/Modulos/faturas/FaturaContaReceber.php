<?php
/**
 * FaturaContaReceber Active Record
 * @author  <your-name-here>
 */
class FaturaContaReceber extends TRecord
{
    const TABLENAME = 'fatura_conta_receber';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('fatura_id');
        parent::addAttribute('conta_receber_id');
    }


}
