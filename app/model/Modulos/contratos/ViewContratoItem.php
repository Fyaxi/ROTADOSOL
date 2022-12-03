<?php
/**
 * ViewContratos Active Record
 * @author  <your-name-here>
 */
class ViewContratoItem extends TRecord
{
    const TABLENAME = 'view_contrato_item';
    const PRIMARYKEY= 'contrato_item_id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('contrato_item_id');
        parent::addAttribute('servico_id');
        parent::addAttribute('contrato_id');
        parent::addAttribute('contrato_item_valor');
        parent::addAttribute('contrato_item_qtd');
        parent::addAttribute('contrato_item_total');
        parent::addAttribute('contrato_item_nome');
        parent::addAttribute('contrato_item_fav_id');
        parent::addAttribute('contrato_item_cli_id');
    }

    public function get_favorecido()
    {
        return Favorecido::find($this->contrato_item_fav_id);
    }
}
