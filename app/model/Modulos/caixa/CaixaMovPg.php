<?php
/**
 * Caixa Active Record
 * @author  <your-name-here>
 */
class CaixaMovPg extends TRecord
{
    const TABLENAME = 'caixa_mov_pg';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('mov_id');
        parent::addAttribute('forma_recebimento');
        parent::addAttribute('valor');
    }

}
