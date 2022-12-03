<?php
/**
 * ProdutoView
 *
 * @version    1.0
 * @package    Modulos
 * @subpackage Produtos
 * @author     Jairo Barreto
 * @copyright  Copyright (c) 2021 Jairo Barreto. (http://jairobarreto.com.br)
 * @license    http://www.sistemaintegrado.tech/
 */
class ProductForm extends TPage
{
    protected $form;
    
    // trait with saveFile, saveFiles, ...
    use Adianti\Base\AdiantiFileSaveTrait;
    
    function __construct()
    {
        parent::__construct();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Product');
        $this->form->setFormTitle('Cadastro de Produto');
        $this->form->setClientValidation(true);
        
        // create the form fields
        $id          = new TEntry('id');
        $description = new TEntry('description');
        $stock       = new TEntry('stock');
        $sale_price  = new TEntry('sale_price');
        $unity       = new TCombo('unity');
        $custo       = new TEntry('custo');
        $tipo_servico_id = new TDBUniqueSearch('tipo_servico_id', 'DBUNIDADE', 'TipoProduto', 'id', 'nome');
        
        $criteria = new TCriteria;
        $criteria->add(new TFilter('grupo_id', '=', 3), TExpression::OR_OPERATOR); 
        $tipo_fornecedor_id = new TDBUniqueSearch('tipo_fornecedor_id', 'DBUNIDADE', 'Favorecido', 'id', 'nome_fantasia', 'grupo_id ', $criteria);
        
        $id->setEditable( FALSE );
        $stock->setEditable( FALSE );
        $unity->addItems( ['UN' => 'Unidade', 'CX' => 'Caixa'] );
        $stock->setNumericMask(2, ',', '.', TRUE); // TRUE: process mask when editing and saving
        $sale_price->setNumericMask(2, ',', '.', TRUE); // TRUE: process mask when editing and saving
        $custo->setNumericMask(2, ',', '.', TRUE); // TRUE: process mask when editing and saving

        $tipo_servico_id->setSize('100%');
        $tipo_servico_id->setMinLength(0);
        
        $tipo_fornecedor_id->setSize('100%');
        $tipo_fornecedor_id->setMinLength(0);

        // add the form fields
        $this->form->addFields( [new TLabel('ID', 'red')],          [$id] );
        $this->form->addFields( [new TLabel('Nome', 'red')], [$description] );

        $this->form->addFields( [new TLabel('Estoque', 'red')],       [$stock],
                                [new TLabel('Tipo', 'red')],  [$unity] );

        $this->form->addFields( [new TLabel('Preço Custo', 'red')],       [$custo],
                                [new TLabel('Preço Venda', 'red')],  [$sale_price] );                   
        
        $this->form->addFields( [ new TLabel('Grupo', 'red') ], [ $tipo_servico_id ], [new TLabel('Fornecedor', 'red') ], [ $tipo_fornecedor_id ] );
        
        $id->setSize('50%');
        
        $description->addValidation('Nome', new TRequiredValidator);
        $stock->addValidation('Estoque', new TRequiredValidator);
        $sale_price->addValidation('Preço Venda', new TRequiredValidator);
        $unity->addValidation('Tipo Produto', new TRequiredValidator);
        $custo->addValidation('Preço Custo', new TRequiredValidator);
        $tipo_servico_id->addValidation('Grupo Produto', new TRequiredValidator);+
        $tipo_fornecedor_id->addValidation('Fornecedor Produto', new TRequiredValidator);
        
        // add the actions
        $this->form->addAction( 'Salvar', new TAction([$this, 'onSave']), 'fa:save green');
        $this->form->addActionLink( 'Limpar Formulário', new TAction([$this, 'onEdit']), 'fa:eraser red');
        $this->form->addActionLink( 'Lista de Produtos', new TAction(['ProductList', 'onReload']), 'fa:table blue');

        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', 'ProductList'));
        $vbox->add($this->form);

        parent::add($vbox);
    }
    
    /**
     * Overloaded method onSave()
     * Executed whenever the user clicks at the save button
     */
    public function onSave()
    {
        try
        {
            TTransaction::open('DBUNIDADE');
            
            // form validations
            $this->form->validate();
            
            // get form data
            $data   = $this->form->getData();
            
            // store product
            $object = new Produto;
            $object->fromArray( (array) $data);
            $object->store();
            
            // send id back to the form
            $data->id = $object->id;
            $this->form->setData($data);
            
            TTransaction::close();
            //new TMessage('info', AdiantiCoreTranslator::translate('Record saved'));
            
            $pos_action = new TAction(['ProductList', 'onReload']);
            new TMessage('info', 'Produto Salvo!', $pos_action);
        }
        catch (Exception $e)
        {
            $this->form->setData($this->form->getData());
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    public function onEdit($param)
    {
        try
        {
            if (isset($param['key']))
            {
                TTransaction::open('DBUNIDADE');
                $object = new Produto( $param['key'] );
                $this->form->setData($object);
                TTransaction::close();
                return $object;
            }
            else
            {
                $this->form->clear();
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}
