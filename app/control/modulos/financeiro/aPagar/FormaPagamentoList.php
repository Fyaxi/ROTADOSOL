<?php
/**
 * ContratoDashboard TSession::getValue('unit_database')
 *
 * @version    2.0
 * @package    Sistema Integrado
 * @subpackage Módulos
 * @author     Jairo Barreto
 * @copyright  Copyright (c) 2021 Nativus Tecnologia. (http://www.nativustecnologia.com.br)
 * 
 */
class FormaPagamentoList extends TPage
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
        
        $this->setDatabase(TSession::getValue('unit_database'));            // defines the database
        $this->setActiveRecord('FormaPagamento');   // defines the active record
        $this->setDefaultOrder('id', 'asc');         // defines the default order
        $this->setLimit(10);
        // $this->setCriteria($criteria) // define a standard filter

        $this->addFilterField('id', '=', 'id'); // filterField, operator, formField
        $this->addFilterField('nome', 'like', 'nome'); // filterField, operator, formField
        $this->addFilterField('ContaFinanceiraId', '=', 'ContaFinanceiraId'); // filterField, operator, formField

        $this->setOrderCommand('conta_financeira->nome', '(SELECT nome FROM conta_financeira WHERE conta_financeira.id=forma_pagamento.id)');
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_FormaPagamento');
        $this->form->setFormTitle('Forma de Pagamento');
        $this->form->enableCSRFProtection(); // ATIVA PROTEÇÃO CONTA EXECUÇÃO DE JAVA SCRIPT MALICIOSO
        

        // create the form fields
        $id = new TEntry('id');
        $nome = new TEntry('nome');
        $ContaFinanceiraId = new TDBUniqueSearch('ContaFinanceiraId', 'DBUNIDADE', 'ContaFinanceira', 'id', 'nome');


        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ], [ new TLabel('Conta') ], [ $ContaFinanceiraId ] );


        // set sizes
        $id->setSize('100%');
        $nome->setSize('100%');
        $ContaFinanceiraId->setSize('100%');
        $ContaFinanceiraId->setMinLength(0);

        $nome->forceUpperCase();

        $this->form->addExpandButton('Abrir Pesquisa' , 'fa:search', true);
        $id->setMask('9999');

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'), new TAction(['FormaPagamentoForm', 'onEdit'], ['register_state' => 'false']), 'fa:plus green');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        //$this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'N°', 'center', '10%');
        $column_nome = new TDataGridColumn('nome', 'Nome', 'left');
        $column_ContaFinanceiraId = new TDataGridColumn('conta_financeira->nome', 'Conta Vinculada', 'left');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_nome);
        $this->datagrid->addColumn($column_ContaFinanceiraId);


        // creates the datagrid column actions
        $column_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
        $column_nome->setAction(new TAction([$this, 'onReload']), ['order' => 'nome']);
        $column_ContaFinanceiraId->setAction(new TAction([$this, 'onReload']), ['order' => 'ContaFinanceiraId']);

        
        $action1 = new TDataGridAction(['FormaPagamentoForm', 'onEdit'], ['id'=>'{id}', 'register_state' => 'false']);
        $action2 = new TDataGridAction([$this, 'onDeletee'], ['id'=>'{id}']);
        
        $this->datagrid->addAction($action1, _t('Edit'),   'far:edit blue');
        $this->datagrid->addAction($action2 ,_t('Delete'), 'far:trash-alt red');
        
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

        /**
     * Ask before deletion
     */
    public function onDeletee($param)
    {
        $action = new TAction(array($this, 'Delete'));
        $action->setParameters($param);
        new TQuestion('DESEJA APAGAR A FORMA DE PAGAMENTO ?', $action);
    }
    
    /**
     * Delete a record
     */
    public function Delete($param)
    {
        try
        {
            $key=$param['key'];
            TTransaction::open($this->database);
            
            $class = $this->activeRecord;
            
            $object = new $class($key, FALSE);

            if($object->id == 1)
            {
                throw new Exception('<span style="color:black;">NÃO É POSSÍVEL APAGAR A FORMA DE PAGAMENTO</span><BR><span style="color:red;font-weight: bold;">MOVITO:</span> <span style="color:black;font-weight: bold;">FORMA DE PAGAMENTO PADRÃO DO SISTEMA<span>');
            }
            else
            {
                $object->delete();
                $this->onReload( $param );
                new TMessage('info', 'FORMA DE PAGAMENTO APAGADA COM SUCESSO!');
            }

            TTransaction::close();
        }
        catch (Exception $e)
        {
            //new TMessage('error','<span style="font-weight: bold;">ERRO AO APAGAR FORMA DE PAGAMENTO</span>');
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}
