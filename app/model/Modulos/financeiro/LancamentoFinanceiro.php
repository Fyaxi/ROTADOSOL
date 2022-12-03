<?php
/**
 * Contrato Active Record
 * @author  <your-name-here>
 */
class LancamentoFinanceiro extends TRecord
{
    const TABLENAME = 'tbLancamentoFinanceiro';
    const PRIMARYKEY = 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('DataMovimentacao');
        parent::addAttribute('IdContaFinanceira');
        parent::addAttribute('IdPlanoContas');
        parent::addAttribute('IdRecebimento');
        parent::addAttribute('IdPagamento');
        parent::addAttribute('TipoMovimentacao');
        parent::addAttribute('ValorMovimentacao');
        parent::addAttribute('EstornoMovimentacao');
        parent::addAttribute('UsuarioMovimentacao');
    }
}
