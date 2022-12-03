<?php
/**
 * TipoProduto Active Record
 * @author  <your-name-here>
 */
class TipoProduto extends TRecord
{
    const TABLENAME = 'tipo_produto';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
    }


}
