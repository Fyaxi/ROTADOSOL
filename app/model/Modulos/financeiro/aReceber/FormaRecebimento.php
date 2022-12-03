<?php
/**
 * FormaRecebimento Active Record
 * @author  <your-name-here>
 */
class FormaRecebimento extends TRecord
{
    const TABLENAME = 'forma_recebimento';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('ContaFinanceiraId');
        parent::addAttribute('ativo');
    }

    public function get_conta_financeira()
    {
        return ContaFinanceira::find($this->ContaFinanceiraId);
    }
}
