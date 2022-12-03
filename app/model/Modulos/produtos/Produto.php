<?php
/**
 * Produto Active Record
 * @author  <your-name-here>
 */
class Produto extends TRecord
{
    const TABLENAME = 'produto';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}

    const CACHECONTROL = 'TAPCache';
    
    const CREATEDAT = 'created_at';
    const UPDATEDAT = 'updated_at';
    
    /*
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('valor');
        parent::addAttribute('custo');
        parent::addAttribute('estoque');

        parent::addAttribute('custo');
        parent::addAttribute('tipo_servico_id');
        parent::addAttribute('ativo');
    }
    */

    public function __construct($id = NULL)
    {
        parent::__construct($id);
        
        parent::addAttribute('description');
        parent::addAttribute('stock');
        parent::addAttribute('sale_price');
        parent::addAttribute('unity');

        parent::addAttribute('custo');
        parent::addAttribute('tipo_servico_id');
        parent::addAttribute('tipo_fornecedor_id');
        parent::addAttribute('ativo');
    }

    public function tipo_fornecedor_id()
    {
        return Favorecido::find($this->tipo_fornecedor_id);
    }

    public function get_tipo_servico()
    {
        return TipoProduto::find($this->tipo_servico_id);
    }

    public static function EstoqueAdicionar($ProdutoId, $Quantidade)
    {
        $produto = new Produto($ProdutoId);
        $produto->stock = ($produto->stock + $Quantidade);
        $produto->store();

    }

    public static function EstoqueRemover($ProdutoId, $Quantidade)
    {
        $produto = new Produto($ProdutoId);
        $produto->stock = ($produto->stock - $Quantidade);
        $produto->store();
    }

}
