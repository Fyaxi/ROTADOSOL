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
class EstoqueList extends TPage
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
        $this->setActiveRecord('Produto');   // defines the active record
        $this->setDefaultOrder('id', 'asc');         // defines the default order
        $this->setLimit(999);
        // $this->setCriteria($criteria) // define a standard filter

        $this->addFilterField('id', '=', 'id'); // filterField, operator, formField
        $this->addFilterField('description', 'like', 'description'); // filterField, operator, formField
        $this->addFilterField('tipo_servico_id', '=', 'tipo_servico_id'); // filterField, operator, formField
        $this->addFilterField('ativo', 'like', 'ativo'); // filterField, operator, formField
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Estoque');
        $this->form->setFormTitle('Consulta Estoque');
        $this->form->enableCSRFProtection(); // ATIVA PROTEÇÃO CONTA EXECUÇÃO DE JAVA SCRIPT MALICIOSO
        

        // create the form fields
        $id = new TEntry('id');
        $description = new TEntry('description');
        //$tipo_servico_id = new TDBUniqueSearch('tipo_servico_id', 'DBUNIDADE', 'TipoProduto', 'id', 'nome');
        $ativo = new TRadioGroup('ativo');
        //$tipo_servico_id->setMinLength(0);
        
        $ativo->addItems( ['Y' => 'Sim', 'N' => 'Não', '' => 'Ambos'] );
        $ativo->setLayout('horizontal');
        
        // add the fields
        $this->form->addFields( [ new TLabel('N°') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Nome') ], [ $description ] );
        //$this->form->addFields( [ new TLabel('Tipo Produto') ], [ $tipo_servico_id ] );
        $this->form->addFields( [ new TLabel('Ativo') ], [ $ativo ] );


        // set sizes
        $id->setSize('100%');
        $description->setSize('100%');
        //$tipo_servico_id->setSize('100%');
        $ativo->setSize('100%');

        $description->forceUpperCase();

        $this->form->addExpandButton('Abrir Menu' , 'fa:search', true);

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';

        // creates the datagrid columns
        //$column_id = new TDataGridColumn('id', 'N°', 'center', '10%');
        $column_description = new TDataGridColumn('description', 'Nome', 'left');
        $column_stock = new TDataGridColumn('stock', 'Estoque', 'left');

        $column_description->setTransformer( function ($value, $object, $row) {
            if ($object->stock == 0)
            {
                $row->style= 'color: silver';
            }elseif($object->stock < 10){
                $row->style= 'color: #FA8072';
            }
            
            return $value;
        });

        // add the columns to the DataGrid
        //$this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_description);
        $this->datagrid->addColumn($column_stock);


        // creates the datagrid column actions
        //$column_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
        $column_description->setAction(new TAction([$this, 'onReload']), ['order' => 'description']);
        $column_stock->setAction(new TAction([$this, 'onReload']), ['order' => 'stock']);

        $action1 = new TDataGridAction(['EstoqueForm', 'onEdit'], ['id'=>'{id}', 'register_state' => 'false']);
        
        $this->datagrid->addAction($action1, _t('Edit'),   'far:edit blue');
        
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
