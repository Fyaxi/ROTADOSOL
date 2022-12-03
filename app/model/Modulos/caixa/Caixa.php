<?php
/**
 * Caixa Active Record
 * @author  <your-name-here>
 */
class Caixa extends TRecord
{
    const TABLENAME = 'caixa';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('abertura');
        parent::addAttribute('fechamento');
        parent::addAttribute('aberto');
        parent::addAttribute('situacao');
        parent::addAttribute('valor');
        parent::addAttribute('valorAbertura');
        parent::addAttribute('usuario');
    }
}
