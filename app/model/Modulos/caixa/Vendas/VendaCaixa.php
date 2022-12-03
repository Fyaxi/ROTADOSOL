<?php
/**
 * Venda Active Record
 * @author  <your-name-here>
 */
class VendaCaixa extends TRecord
{
    const TABLENAME = 'venda_caixa';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('cliente_id');
        parent::addAttribute('dt_fatura');
        parent::addAttribute('mes');
        parent::addAttribute('ano');
        parent::addAttribute('total');
        parent::addAttribute('financeiro_gerado');
        parent::addAttribute('ativo');
    }

    public function delete($id = null)
    {
        $id = isset($id) ? $id : $this->id;
        
        VendaCaixaItem::where('fatura_id', '=', $this->id)->delete();
        parent::delete($id);
    }
    
    public function get_cliente()
    {
        return Pessoa::find($this->cliente_id);
    }
}
