<?php
/**
 * ViewDecoracao Active Record
 * @author  <your-name-here>
 */
class ViewReceber extends TRecord
{
    const TABLENAME = 'view_receber';
    const PRIMARYKEY= 'ReceberId';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('dt_emissao');
        parent::addAttribute('dt_vencimento');
        parent::addAttribute('dt_pagamento');
        parent::addAttribute('pessoa_id');
        parent::addAttribute('VendedorID');
        parent::addAttribute('valor');
        parent::addAttribute('desconto');
        parent::addAttribute('valor_total');
        parent::addAttribute('obs');
        parent::addAttribute('mes');
        parent::addAttribute('ano');
        parent::addAttribute('ativo');
        parent::addAttribute('origem');
        parent::addAttribute('id_caixa');
        parent::addAttribute('nome_cliente');
        parent::addAttribute('FormaRecebimentoEntrada');
    }


}
