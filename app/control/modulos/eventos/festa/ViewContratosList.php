<?php
/**
 * ContratoDashboard 'DBUNIDADE'
 *
 * @version    2.0
 * @package    Sistema Integrado
 * @subpackage Módulos
 * @author     Jairo Barreto
 * @copyright  Copyright (c) 2021 Nativus Tecnologia. (http://www.nativustecnologia.com.br)
 * 
 */
class ViewContratosList extends TPage
{
    protected $form;     // registration form
    protected $datagrid; // listing
    protected $pageNavigation;
    protected $formgrid;
    protected $deleteButton;
    
    use Adianti\base\AdiantiStandardListTrait;
    
    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->setDatabase('DBUNIDADE');            // defines the database
        $this->setActiveRecord('ViewContratoItem');   // defines the active record
        $this->setDefaultOrder('contrato_item_id', 'asc');         // defines the default order
        $this->setLimit(10);
        // $this->setCriteria($criteria) // define a standard filter

        $this->addFilterField('contrato_item_id', '=', 'contrato_item_id'); // filterField, operator, formField
        $this->addFilterField('contrato_item_nome', 'like', 'contrato_item_nome'); // filterField, operator, formField
        $this->addFilterField('contrato_item_fav_id', '=', 'contrato_item_fav_id'); // filterField, operator, formField
        $this->addFilterField('contrato_item_cli_id', '=', 'contrato_item_cli_id'); // filterField, operator, formField
        $this->addFilterField('servico_id', '=', 'servico_id'); // filterField, operator, formField
        $this->addFilterField('contrato_id', '=', 'contrato_id'); // filterField, operator, formField
        $this->setOrderCommand('favorecido->nome_fantasia', '(SELECT nome_fantasia FROM favorecido WHERE favorecido.id=view_contrato_item.contrato_id)');
        
        // creates the form
        $this->form = new BootstrapFormBuilder('Form_ViewContratos');
        $this->form->setFormTitle('Listagem de Itens por Evento e Fornecedor');
        $this->form->enableCSRFProtection(); // ATIVA PROTEÇÃO CONTA EXECUÇÃO DE JAVA SCRIPT MALICIOSO
        

        // create the form fields
        $contrato_item_id = new TEntry('contrato_item_id');
        $contrato_id = new TEntry('contrato_id');
        $contrato_item_fav_id = new TDBUniqueSearch('contrato_item_fav_id', 'DBUNIDADE', 'Favorecido', 'id', 'nome_fantasia');
        $contrato_item_cli_id = new TDBUniqueSearch('contrato_item_cli_id', 'DBUNIDADE', 'Pessoa', 'id', 'nome_fantasia');
        $servico_id = new TDBUniqueSearch('servico_id', 'DBUNIDADE', 'Servico', 'id', 'nome');


        // add the fields
        $this->form->addFields( [ new TLabel('Fornecedor') ], [ $contrato_item_fav_id ], [ new TLabel('Item') ], [ $servico_id ] );
        $this->form->addFields( [ new TLabel('Cliente') ], [ $contrato_item_cli_id ], [ new TLabel('Contrato nº') ], [ $contrato_id ] );

        // set sizes
        $contrato_id->setSize('100%');
        $contrato_item_id->setSize('100%');
        $servico_id->setSize('100%');
        $servico_id->setMinLength(0);
        $contrato_item_fav_id->setSize('100%');
        $contrato_item_fav_id->setMinLength(0);
        $contrato_item_cli_id->setSize('100%');
        $contrato_item_cli_id->setMinLength(0);
        
        //$this->form->addExpandButton('Expandir' , 'fa:search', true);

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        //$btn->class = 'btn btn-sm btn-primary';
        //$this->form->addActionLink('Inserir Novo Grupo', new TAction(['ContratoForm', 'onEdit'], ['register_state' => 'false']), 'fa:plus green');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        //$this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_contrato_item_id = new TDataGridColumn('contrato_item_id', 'ITEM N°', 'center', '10%');
        $column_contrato_item_nome = new TDataGridColumn('contrato_item_nome', 'ITEM', 'left');
        $column_contrato_item_fav_id = new TDataGridColumn('favorecido->nome_fantasia', 'FORNECEDOR', 'left');
        $column_contrato_item_valor = new TDataGridColumn('contrato_item_valor', 'VALOR', 'center');
        $column_contrato_item_qtd = new TDataGridColumn('contrato_item_qtd', 'QUANT.', 'center');
        $column_contrato_item_total = new TDataGridColumn('= {contrato_item_valor} * {contrato_item_qtd} ', 'SUBTOTAL', 'right');
        //$subtotal    = new TDataGridColumn('= {contrato_item_valor} * {contrato_item_qtd} ', 'Subtotal', 'right');


        // add the columns to the DataGrid
        //$this->datagrid->addColumn($column_contrato_item_id);
        $this->datagrid->addColumn($column_contrato_item_nome);
        $this->datagrid->addColumn($column_contrato_item_fav_id);
        $this->datagrid->addColumn($column_contrato_item_valor);
        $this->datagrid->addColumn($column_contrato_item_qtd);
        $this->datagrid->addColumn($column_contrato_item_total);

        $format_value = function($value) {
            if (is_numeric($value)) {
                return 'R$ '.number_format($value, 2, ',', '.');
            }
            return $value;
        };

        $column_contrato_item_total->setTransformer( $format_value );
        
        // define totals
        $column_contrato_item_total->setTotalFunction( function($values) {
            return array_sum((array) $values);
        });

        // creates the datagrid column actions
        $column_contrato_item_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
        $column_contrato_item_nome->setAction(new TAction([$this, 'onReload']), ['order' => 'contrato_item_nome']);

        
        $action1 = new TDataGridAction(['ContratoForm', 'onEdit'], ['id'=>'{contrato_item_id}', 'register_state' => 'false']);
        $action2 = new TDataGridAction([$this, 'onDelete'], ['id'=>'{contrato_item_id}']);
        
        //$this->datagrid->addAction($action1, _t('Edit'),   'far:edit blue');
        //$this->datagrid->addAction($action2 ,_t('Delete'), 'far:trash-alt red');
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        
        $panel = new TPanelGroup('', 'white');
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);
        
        // header actions
        $dropdown = new TDropDown(_t('Export'), 'fa:list');
        $dropdown->setPullSide('right');
        $dropdown->setButtonClass('btn btn-default waves-effect dropdown-toggle');
        $dropdown->addAction( _t('Save as CSV'), new TAction([$this, 'onExportCSV'], ['register_state' => 'false', 'static'=>'1']), 'fa:table blue' );
        $dropdown->addAction( _t('Save as PDF'), new TAction([$this, 'onExportPDF'], ['register_state' => 'false', 'static'=>'1']), 'far:file-pdf red' );
        $panel->addHeaderWidget( $dropdown );
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel);
        
        parent::add($container);
    }
}
