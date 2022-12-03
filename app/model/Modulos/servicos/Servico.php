<?php
/**
 * Servico Active Record
 * @author  <your-name-here>
 */
class Servico extends TRecord
{
    const TABLENAME = 'servico';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('nome_relatorio');
        parent::addAttribute('valor');
        parent::addAttribute('valor_custo');
        parent::addAttribute('tipo_servico_id');
        parent::addAttribute('ativo');
        parent::addAttribute('tipo_favorecido_id');

        parent::addAttribute('quantidade');
    }

    public function get_tipo_servico()
    {
        return TipoServico::find($this->tipo_servico_id);
    }

    public function get_tipo_favorecido()
    {
        return Favorecido::find($this->tipo_favorecido_id);
    }

    public function get_pessoa()
    {
        return Pessoa::find($this->tipo_favorecido_id);
    }

}
