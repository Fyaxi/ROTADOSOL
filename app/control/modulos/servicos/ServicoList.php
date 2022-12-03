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
class ServicoList extends TPage
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
        $this->setActiveRecord('Servico');   // defines the active record
        $this->setDefaultOrder('id', 'asc');         // defines the default order
        $this->setLimit(10);
        // $this->setCriteria($criteria) // define a standard filter

        $this->addFilterField('id', '=', 'id'); // filterField, operator, formField
        $this->addFilterField('nome', 'like', 'nome'); // filterField, operator, formField
        $this->addFilterField('tipo_servico_id', '=', 'tipo_servico_id'); // filterField, operator, formField
        $this->addFilterField('tipo_favorecido_id', '=', 'tipo_favorecido_id'); // filterField, operator, formField
        $this->addFilterField('ativo', 'like', 'ativo'); // filterField, operator, formField
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Servico');
        $this->form->setFormTitle('Listagem de itens para festa');
        $this->form->enableCSRFProtection(); // ATIVA PROTEÇÃO CONTA EXECUÇÃO DE JAVA SCRIPT MALICIOSO
        

        // create the form fields
        $id = new TEntry('id');
        $nome = new TEntry('nome');
        $tipo_servico_id = new TDBUniqueSearch('tipo_servico_id', 'DBUNIDADE', 'TipoServico', 'id', 'nome');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('grupo_id', '=', 3), TExpression::OR_OPERATOR); 
        $tipo_favorecido_id = new TDBUniqueSearch('tipo_favorecido_id', 'DBUNIDADE', 'Favorecido', 'id', 'nome_fantasia', 'grupo_id ', $criteria);
        $ativo = new TRadioGroup('ativo');
        $tipo_servico_id->setMinLength(0);
        $tipo_favorecido_id->setMinLength(0);
        
        $ativo->addItems( ['Y' => 'Sim', 'N' => 'Não', '' => 'Ambos'] );
        $ativo->setLayout('horizontal');
        
        // add the fields
        $this->form->addFields( [ new TLabel('N°') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] );
        $this->form->addFields( [ new TLabel('Fornecedor') ], [ $tipo_favorecido_id ] );
        $this->form->addFields( [ new TLabel('Grupo') ], [ $tipo_servico_id ] );
        $this->form->addFields( [ new TLabel('Ativo') ], [ $ativo ] );


        // set sizes
        $id->setSize('100%');
        $nome->setSize('100%');
        $tipo_servico_id->setSize('100%');
        $tipo_favorecido_id->setSize('100%');
        $ativo->setSize('100%');

        $nome->forceUpperCase();

        //$this->form->addExpandButton('Expandir Menu' , 'fa:search', true);

        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink('Cadastrar Item', new TAction(['ServicoForm', 'onEdit'], ['register_state' => 'false']), 'fa:plus green');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        //$this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        //$column_id = new TDataGridColumn('id', 'N°', 'center', '10%');
        $column_nome = new TDataGridColumn('nome', 'Nome', 'left');
        $column_tipo_favorecido_id = new TDataGridColumn('pessoa->nome_fantasia', 'Fornecedor', 'left');
        $column_tipo_servico_id = new TDataGridColumn('tipo_servico->nome', 'Grupo', 'center');
        $column_valor = new TDataGridColumn('valor', 'Valor', 'center');
        $column_ativo = new TDataGridColumn('ativo', 'Ativo', 'left');

        //$column_id->setTransformer( function ($value, $object, $row) {
        //    if ($object->ativo == 'N')
        //    {
        //        $row->style= 'color: silver';
        //    }
        //    
        //    return $value;
        //});
        
        $column_ativo->setTransformer( function ($value, $object, $row) {
            if ($object->ativo == 'N')
            {
                $row->style= 'color: silver';
            }

            if ($value == 'Y')
            {
                $div = new TElement('span');
                $div->class="label label-success";
                $div->style="text-shadow:none; font-size:12px";
                $div->add('Sim');
                return $div;
            }
            else
            {
                $div = new TElement('span');
                $div->class="label label-danger";
                $div->style="text-shadow:none; font-size:12px";
                $div->add('Não');
                return $div;
            }
        });
        
        
        $column_valor->setTransformer( function($value) {
            if (is_numeric($value)) {
                return 'R$ '.number_format($value, 2, ',', '.');
            }
            return $value;
        });

        // add the columns to the DataGrid
        //$this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_nome);
        $this->datagrid->addColumn($column_tipo_favorecido_id);
        //$this->datagrid->addColumn($column_tipo_servico_id);
        $this->datagrid->addColumn($column_valor);
        //$this->datagrid->addColumn($column_ativo);
        
        $column_valor->enableAutoHide(500);
        //$column_tipo_servico_id->enableAutoHide(500);
        $column_tipo_favorecido_id->enableAutoHide(500);
        //$column_ativo->enableAutoHide(500);


        // creates the datagrid column actions
        //$column_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
        $column_nome->setAction(new TAction([$this, 'onReload']), ['order' => 'nome']);
        $column_valor->setAction(new TAction([$this, 'onReload']), ['order' => 'valor']);
        //$column_ativo->setAction(new TAction([$this, 'onReload']), ['order' => 'ativo']);

        
        $action1 = new TDataGridAction(['ServicoForm', 'onEdit'], ['id'=>'{id}', 'register_state' => 'false']);
        $action2 = new TDataGridAction([$this, 'onTurnOnOff'], ['id'=>'{id}']);
        $action3 = new TDataGridAction([$this, 'onDelete'], ['id'=>'{id}']);
        
        $this->datagrid->addAction($action1, _t('Edit'),   'far:edit blue');
        $this->datagrid->addAction($action2 ,_t('Activate/Deactivate'), 'fa:power-off orange');
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
    
    /**
     * Turn on/off an user
     */
    public function onTurnOnOff($param)
    {
        try
        {
            TTransaction::open(TSession::getValue('unit_database'));
            $servico = Servico::find($param['id']);
            
            if ($servico instanceof Servico)
            {
                $servico->ativo = $servico->ativo == 'Y' ? 'N' : 'Y';
                $servico->store();
            }
            
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
