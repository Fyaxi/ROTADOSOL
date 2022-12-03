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
class FavorecidoList extends TPage
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
        
        $this->setDatabase( 'DBUNIDADE' );            // defines the database
        $this->setActiveRecord('Pessoa');   // defines the active record
        $this->setDefaultOrder('id', 'asc');         // defines the default order
        $this->setLimit(10);

        $criteria = new TCriteria;
        $criteria->add(new TFilter('grupo_id', '=', 3), TExpression::OR_OPERATOR);
        $criteria->add(new TFilter('grupo_id', '=', 4), TExpression::OR_OPERATOR);
        $this->setCriteria($criteria); // define a standard filter

        $this->addFilterField('id', '=', 'id'); // filterField, operator, formField
        $this->addFilterField('nome_fantasia', 'like', 'nome_fantasia'); // filterField, operator, formField
        $this->addFilterField('fone', 'like', 'fone'); // filterField, operator, formField
        $this->addFilterField('email', 'like', 'email'); // filterField, operator, formField
        $this->addFilterField('grupo_id', '=', 'grupo_id'); // filterField, operator, formField
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Favorecido');
        $this->form->setFormTitle('Favorecidos');
        $this->form->enableCSRFProtection(); // ATIVA PROTEÇÃO CONTA EXECUÇÃO DE JAVA SCRIPT MALICIOSO
        

        // create the form fields
        $id = new TEntry('id');
        $nome_fantasia = new TEntry('nome_fantasia');
        $fone = new TEntry('fone');
        $email = new TEntry('email');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('id', '=', 3), TExpression::OR_OPERATOR); 
        $criteria->add(new TFilter('id', '=', 4), TExpression::OR_OPERATOR); 
        $grupo_id = new TDBUniqueSearch('grupo_id', 'DBUNIDADE', 'Grupo', 'id', 'nome', 'id', $criteria);
        $grupo_id->setMinLength(0);

        

        // add the fields
        $this->form->addFields( [ new TLabel('N°') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Nome Fantasia') ], [ $nome_fantasia ] );
        $this->form->addFields( [ new TLabel('Fone') ], [ $fone ] );
        $this->form->addFields( [ new TLabel('Email') ], [ $email ] );
        $this->form->addFields( [ new TLabel('Grupo') ], [ $grupo_id ] );


        // set sizes
        $id->setSize('100%');
        $nome_fantasia->setSize('100%');
        $fone->setSize('100%');
        $email->setSize('100%');
        $grupo_id->setSize('100%');

        $id->setMask('99999');
        $fone->setMask('(99) 9 9999-9999');

        $nome_fantasia->forceUppercase();
        $fone->forceUpperCase();
        $fone->forceUppercase();
        $email->forceUpperCase();

        $this->form->addExpandButton('Expandir' , 'fa:search', true);

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink('Inserir Novo Favorecido', new TAction(['FavorecidoForm', 'onEdit']), 'fa:plus green');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        //$this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        //$column_id = new TDataGridColumn('id', 'N°', 'left');
        $column_nome_fantasia = new TDataGridColumn('nome_fantasia', 'Nome', 'left');
        $column_fone = new TDataGridColumn('fone', 'Fone', 'left');
        //$column_email = new TDataGridColumn('email', 'Email', 'left');
        $column_grupo_id = new TDataGridColumn('grupo->nome', 'Grupo', 'left');
        
        $column_fone->enableAutoHide(500);
        //$column_email->enableAutoHide(500);
        $column_grupo_id->enableAutoHide(500);
        
        // add the columns to the DataGrid
        //$this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_nome_fantasia);
        $this->datagrid->addColumn($column_fone);
        //$this->datagrid->addColumn($column_email);
        $this->datagrid->addColumn($column_grupo_id);
        
        //$column_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
        $column_nome_fantasia->setAction(new TAction([$this, 'onReload']), ['order' => 'nome_fantasia']);

        
        $action1 = new TDataGridAction(['FavorecidoFormView', 'onEdit'], ['id'=>'{id}', 'register_state' => 'false']);
        $action2 = new TDataGridAction(['FavorecidoForm', 'onEdit'], ['id'=>'{id}']);
        $action3 = new TDataGridAction([$this, 'onDelete'], ['id'=>'{id}', 'register_state' => 'false']);
        
        $this->datagrid->addAction($action1, _t('View'),   'fa:search gray');
        $this->datagrid->addAction($action2, _t('Edit'),   'far:edit blue');
        $this->datagrid->addAction($action3 ,_t('Delete'), 'far:trash-alt red');
        
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
