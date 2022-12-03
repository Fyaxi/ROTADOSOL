<?php
/**
 * Decoracao Active Record
 * @author  <your-name-here>
 */
class Decoracao extends TRecord
{
    const TABLENAME = 'decoracao';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('cliente_id');
        parent::addAttribute('tipo_contrato_id');
        parent::addAttribute('ativo');
        parent::addAttribute('agendado');
        parent::addAttribute('dt_inicio');
        parent::addAttribute('dt_fim');
        parent::addAttribute('obs');

        parent::addAttribute('aniversariante');
        parent::addAttribute('convidados');
        parent::addAttribute('tema');
        parent::addAttribute('valor');
        parent::addAttribute('ultima_fatura');

        parent::addAttribute('mes');
        parent::addAttribute('ano');

        parent::addAttribute('ValorEntrada');
        parent::addAttribute('PagamentoEntrada');
        parent::addAttribute('ValorDesconto');
        parent::addAttribute('IdVendedor');
        parent::addAttribute('VendedorLogin');

        parent::addAttribute('FaturaID');
        parent::addAttribute('ValorContrato');
        parent::addAttribute('ValorTotal');

        ////////////////////////////////////
        parent::addAttribute('local_festa');
        parent::addAttribute('moveis');
        parent::addAttribute('itens_decorativos');
        parent::addAttribute('pecas_doces');
        parent::addAttribute('obs_checklist');
        parent::addAttribute('dt_remocao');
        parent::addAttribute('hora_remocao');
        ////////////////////////////////////
    }

    public function get_tipo_contrato()
    {
        return TipoDecoracao::find($this->tipo_contrato_id);
    }

    public function get_cliente()
    {
        return Pessoa::find($this->cliente_id);
    }
    
    public function get_total()
    {
        return DecoracaoItem::where('contrato_id', '=', $this->id)->sumBy('total');
    }
    
    public function get_ultima_fatura()
    {
        return Fatura::where('cliente_id','=',$this->cliente_id)->where('total','=', $this->get_total())->orderBy('dt_fatura', 'desc')->first()->dt_fatura;
    }
}
