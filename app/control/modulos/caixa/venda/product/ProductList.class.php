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
class ProductList extends TPage
{
    protected $form;     // registration form
    protected $datagrid; // listing
    protected $pageNavigation;
    
    // trait with onReload, onSearch, onDelete...
    use Adianti\Base\AdiantiStandardListTrait;
    
    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->setDatabase('DBUNIDADE');                // defines the database
        $this->setActiveRecord('Produto');            // defines the active record
        $this->setDefaultOrder('id', 'asc');          // defines the default order
        $this->addFilterField('description', 'like'); // add a filter field
        $this->addFilterField('unity', '=');          // add a filter field
        $this->addFilterField('tipo_servico_id', '=', 'tipo_servico_id'); // filterField, operator, formField
        $this->addFilterField('tipo_fornecedor_id', '=', 'tipo_fornecedor_id'); // filterField, operator, formField
        $this->setLimit(25);
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Product');
        $this->form->setFormTitle('Listagem de Produtos');
        
        // create the form fields
        $description = new TEntry('description');
        $unit        = new TCombo('unity');
        $unit->addItems( ['UN' => 'Unidade', 'CX' => 'Caixa'] );
        
        $tipo_servico_id = new TDBUniqueSearch('tipo_servico_id', 'DBUNIDADE', 'TipoProduto', 'id', 'nome');
        $tipo_servico_id->setMinLength(0);
        $tipo_servico_id->setSize('100%');

        $criteria = new TCriteria;
        $criteria->add(new TFilter('grupo_id', '=', 3), TExpression::OR_OPERATOR); 
        $tipo_fornecedor_id = new TDBUniqueSearch('tipo_fornecedor_id', 'DBUNIDADE', 'Favorecido', 'id', 'nome_fantasia', 'grupo_id ', $criteria);
        $tipo_fornecedor_id->setMinLength(0);
        $tipo_fornecedor_id->setSize('100%');
        
        // add a row for the filter field
        $this->form->addFields( [new TLabel('Nome')], [$description], [ new TLabel('Fornecedor') ], [ $tipo_fornecedor_id ] );
        $this->form->addFields( [new TLabel('Tipo')], [$unit], [ new TLabel('Grupo') ], [ $tipo_servico_id ]  );
        
        $this->form->setData( TSession::getValue('ProductList_filter_data') );
        
        $this->form->addAction( 'Buscar', new TAction([$this, 'onSearch']), 'fa:search blue');
        $this->form->addActionLink( 'Novo Produto',  new TAction(['ProductForm', 'onEdit']), 'fa:plus green');
        
        // expand button
        $this->form->addExpandButton();
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        //$this->datagrid->enablePopover('Image', "<img style='max-height: 300px' src='{photo_path}'>");

        // creates the datagrid columns
        $col_id          = new TDataGridColumn('id', 'Nº', 'center', '10%');
        $col_description = new TDataGridColumn('description', 'Nome', 'left', '45%');
        $col_stock       = new TDataGridColumn('stock', 'Estoque', 'right', '15%');
        $col_sale_price  = new TDataGridColumn('sale_price', 'Preço', 'right', '15%');
        $col_unity       = new TDataGridColumn('unity', 'Tipo', 'right', '15%');

        $col_id->setTransformer( function ($value, $object, $row) {
            if ($object->ativo == 'N')
            {
                $row->style= 'color: silver';
            }
            
            return $value;
        });
        
        $this->datagrid->addColumn($col_id);
        $this->datagrid->addColumn($col_description);
        $this->datagrid->addColumn($col_stock);
        $this->datagrid->addColumn($col_sale_price);
        $this->datagrid->addColumn($col_unity);
        
        // creates two datagrid actions
        $action1 = new TDataGridAction(['ProductForm', 'onEdit'], ['id'=>'{id}']);
        $action2 = new TDataGridAction([$this, 'onDelete'], ['id'=>'{id}']);
        $action3 = new TDataGridAction([$this, 'onTurnOnOff'], ['id'=>'{id}']);
        
        // add the actions to the datagrid
        $this->datagrid->addAction($action1, 'Editar Produto', 'far:edit blue');
        $this->datagrid->addAction($action2 ,'Deletar', 'far:trash-alt red');
        $this->datagrid->addAction($action3 ,_t('Activate/Deactivate'), 'fa:power-off orange');
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->enableCounters();
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        
        // create the page container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', 'ProductList'));
        $container->add($this->form);
        $container->add($panel = TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));
        $panel->getBody()->style = 'overflow-x:auto';
        parent::add($container);
    }

    /**
     * Turn on/off an user
     */
    public function onTurnOnOff($param)
    {
        try
        {
            TTransaction::open('DBUNIDADE');
            $produto = Produto::find($param['id']);
            
            if ($produto instanceof Produto)
            {
                $produto->ativo = $produto->ativo == 'Y' ? 'N' : 'Y';
                $produto->store();
            }
            
            $pos_action = new TAction(['ProductList', 'onReload']);
            new TMessage('info', 'Produto Alterado!', $pos_action);
            
            TTransaction::close();
            
            $this->onReload($param);
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}
