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
class ContaReceberQuitacaoList extends TPage
{
    protected $form;     // registration form
    protected $datagrid; // listing
    protected $formgrid;
    protected $saveButton;
    
    use Adianti\base\AdiantiStandardListTrait;
    
    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->setDatabase(TSession::getValue('unit_database'));            // defines the database
        $this->setActiveRecord('ContaReceber');   // defines the active record
        $this->setDefaultOrder('id', 'asc');         // defines the default order
        $this->setLimit(0);
        
        $criteria = new TCriteria;
        $criteria->add(new TFilter('ativo', '=', 'Y'));
        $this->setCriteria($criteria); // define a standard filter

        $this->addFilterField('id', '=', 'id'); // filterField, operator, formField
        $this->addFilterField('pessoa_id', '=', 'pessoa_id'); // filterField, operator, formField
        $this->addFilterField('mes', '=', 'mes'); // filterField, operator, formField
        $this->addFilterField('ano', '=', 'ano'); // filterField, operator, formField
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_update_ContaReceber');
        $this->form->setFormTitle('Quitação de Contas a Receber');
        $this->form->enableCSRFProtection(); // ATIVA PROTEÇÃO CONTA EXECUÇÃO DE JAVA SCRIPT MALICIOSO
        

        // create the form fields
        $id = new TEntry('id');
        $pessoa_id = new TDBUniqueSearch('pessoa_id', TSession::getValue('unit_database'), 'Pessoa', 'id', 'nome');
        $mes = new TRadioGroup('mes');
        $ano = new TRadioGroup('ano');

        
        $current = (int) date('Y');
        $mes->addItems( ['01' => 'Jan', '02' => 'Fev', '03' => 'Mar', '04' => 'Abr', '05' => 'Mai', '06' => 'Jun', '07' => 'Jul', '08' => 'Ago', '09' => 'Set', '10' => 'Out', '11' => 'Nov', '12' => 'Dez'] );
        $ano->addItems( [ ($current -5) => ($current -5), ($current -4) => ($current -4), ($current -3) => ($current -3), ($current -2) => ($current -2), ($current -1) => ($current -1), $current => $current ] );
        
        $mes->setLayout('horizontal');
        $ano->setLayout('horizontal');
        $pessoa_id->setMinLength(0);

        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Pessoa') ], [ $pessoa_id ] );
        $this->form->addFields( [ new TLabel('Mes') ], [ $mes ] );
        $this->form->addFields( [ new TLabel('Ano') ], [ $ano ] );


        // set sizes
        $id->setSize('100%');
        $pessoa_id->setSize('100%');

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__ . '_filter_data') );
        
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        // $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'Id', 'left');
        $column_pessoa_id = new TDataGridColumn('pessoa->nome_fantasia', 'Pessoa', 'left');
        $column_dt_emissao = new TDataGridColumn('dt_emissao', 'Emissao', 'left');
        $column_dt_pagamento = new TDataGridColumn('dt_pagamento', 'Pagamento', 'left');
        $column_valor = new TDataGridColumn('valor', 'Valor', 'right');
        $column_obs = new TDataGridColumn('obs', 'Obs', 'left');

        $column_valor->setTransformer( function($value) {
            if (is_numeric($value)) {
                return 'R$&nbsp;'.number_format($value, 2, ',', '.');
            }
            return $value;
        });
        
        $column_dt_emissao->setTransformer( function($value) {
            return TDate::convertToMask($value, 'yyyy-mm-dd', 'dd/mm/yyyy');
        });
        
        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_pessoa_id);
        $this->datagrid->addColumn($column_dt_emissao);
        $this->datagrid->addColumn($column_dt_pagamento);
        $this->datagrid->addColumn($column_valor);
        $this->datagrid->addColumn($column_obs);


        // creates the datagrid column actions
        $column_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
        $column_pessoa_id->setAction(new TAction([$this, 'onReload']), ['order' => 'pessoa_id']);
        $column_dt_emissao->setAction(new TAction([$this, 'onReload']), ['order' => 'dt_emissao']);

        
        $column_dt_pagamento->setTransformer( function($value, $object, $row) {
            $widget = new TDate('dt_pagamento' . '_' . $object->id);
            $widget->setValue( TDate::convertToMask($object->dt_pagamento, 'yyyy-mm-dd', 'dd/mm/yyyy' ) );
            $widget->setSize(100);
            $widget->setMask('dd/mm/yyyy');
            $widget->setDatabaseMask('yyyy-mm-dd');
            $widget->setFormName('form_update_ContaReceber');
            
            $action = new TAction( [$this, 'onSaveInline'], ['column' => 'dt_pagamento' ] );
            $widget->setExitAction( $action );
            return $widget;
        });
        
        $column_obs->setTransformer( function($value, $object, $row) {
            $widget = new TEntry('obs' . '_' . $object->id);
            $widget->setValue( $object->obs );
            $widget->setSize(200);
            $widget->setFormName('form_update_ContaReceber');
            
            $action = new TAction( [$this, 'onSaveInline'], ['column' => 'obs' ] );
            $widget->setExitAction( $action );
            return $widget;
        });
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        $panel = new TPanelGroup;
        $panel->add($this->datagrid)->style = 'overflow-x:auto';
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel);
        
        parent::add($container);
    }
    
    /**
     * Save the datagrid objects
     */
    public static function onSaveInline($param)
    {
        $name   = $param['_field_name'];
        $value  = $param['_field_value'];
        $column = $param['column'];
        
        $parts  = explode('_', $name);
        $id     = end($parts);
        
        try
        {
            // open transaction
            TTransaction::open(TSession::getValue('unit_database'));
            
            $object = ContaReceber::find($id);
            
            $new_value = $value;
            
            if ($column == 'dt_pagamento')
            {
                $new_value = TDate::convertToMask($value, 'dd/mm/yyyy', 'yyyy-mm-dd');
            }
            
            if ($object && (string) $object->$column !== (string) $new_value)
            {
                $object->$column = $new_value;
                $object->store();
                TToast::show('success', 'Registro atualizado', 'top right', 'far:check-circle');
            }
            
            TTransaction::close();
        }
        catch (Exception $e)
        {
            // show the exception message
            TToast::show('error', $e->getMessage(), 'bottom center', 'fa:exclamation-triangle');
        }
    }
    
    public function onLoad($param)
    {
        TSession::setValue($this->activeRecord.'_filter_id',  null);
        TSession::setValue($this->activeRecord.'_filter_pessoa_id',  null);
        TSession::setValue($this->activeRecord.'_filter_mes', new TFilter('mes', '=', date('m')));
        TSession::setValue($this->activeRecord.'_filter_ano', new TFilter('ano', '=', date('Y')));
        
        $data = new stdClass;
        $data->ano = date('Y');
        $data->mes = date('m');
        $data->id  = '';
        $data->pessoa_id  = '';
        TSession::setValue(__CLASS__ . '_filter_data', $data);
        $this->form->setData($data);
        
        $this->onReload($param);
    }
}
