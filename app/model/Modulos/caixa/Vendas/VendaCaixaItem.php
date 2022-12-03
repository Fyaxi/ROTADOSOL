<?php
/**
 * VendaItem Active Record
 * @author  <your-name-here>
 */
class VendaCaixaItem extends TRecord
{
    const TABLENAME = 'venda_caixa_item';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('servico_id');
        parent::addAttribute('fatura_id');
        parent::addAttribute('valor');
        parent::addAttribute('quantidade');
        parent::addAttribute('total');
    }

    public function get_servico()
    {
        return Produto::find($this->servico_id);
    }

}
