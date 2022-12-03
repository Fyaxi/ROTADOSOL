<?php
/**
 * ViewContratos Active Record
 * @author  <your-name-here>
 */
class ViewContratos extends TRecord
{
    const TABLENAME = 'view_contrato';
    const PRIMARYKEY= 'contrato_id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('cliente_id');
        parent::addAttribute('tipo_contrato_id');
        parent::addAttribute('dt_inicio');
        parent::addAttribute('dt_fim');
        parent::addAttribute('tipo_contrato');
        parent::addAttribute('nome_cliente');
        parent::addAttribute('cidade');
        parent::addAttribute('uf');
        parent::addAttribute('nome_estado');
        parent::addAttribute('ativo');
        parent::addAttribute('total');
    }


}
