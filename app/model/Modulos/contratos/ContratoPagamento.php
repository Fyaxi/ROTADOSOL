<?php
/**
 * PessoaPapel Active Record
 * @author  <your-name-here>
 */
class ContratoPagamento extends TRecord
{
    const TABLENAME = 'contrato_pagamento';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}

    const CREATEDAT = 'created_at';
    const UPDATEDAT = 'updated_at';
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('pessoa_id');
        parent::addAttribute('contrato_id');
        parent::addAttribute('valor');
        parent::addAttribute('status');
        parent::addAttribute('ContaPagamento');
        parent::addAttribute('created_at');
        parent::addAttribute('updated_at');
    }

    public function get_pessoa()
    {
        return Pessoa::find($this->pessoa_id);
    }

}
