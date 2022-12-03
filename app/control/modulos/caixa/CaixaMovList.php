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
class CaixaMovList extends TPage
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
        $this->setActiveRecord('CaixaMov');   // defines the active record
        $this->setDefaultOrder('id', 'desc');         // defines the default order
        $this->setLimit(50);
        // $this->setCriteria($criteria) // define a standard filter

        $this->addFilterField('id', '=', 'id'); // filterField, operator, formField
        $this->addFilterField('caixa_id', '=', 'caixa_id'); // filterField, operator, formField
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_CaixaMov');
        $this->form->setFormTitle('PESQUISAR MOVIMENTAÇÕES NO CAIXA');
        $this->form->enableCSRFProtection(); // ATIVA PROTEÇÃO CONTA EXECUÇÃO DE JAVA SCRIPT MALICIOSO
        
        // create the form fields
        $id = new TEntry('id');
        $caixa_id = new TEntry('caixa_id');

        // add the fields
        $this->form->addFields( [ new TLabel('Caixa ID') ], [ $caixa_id ], [ new TLabel('Movimentação ID') ], [ $id ] );

        // set sizes
        $id->setSize('100%');
        $caixa_id->setSize('100%');
        
        $this->form->addExpandButton('Expandir' , 'fa:search', true);

        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';

        $SuporteLogin = substr(TSession::getValue('login'), 0, 4);
        if( $SuporteLogin == 'sic.' )
        {
            $this->form->addActionLink('Adicionar Movimentação <b>(admin)</b>', new TAction(['CaixaMovForm', 'onEdit'], ['register_state' => 'false']), 'fa:plus green');
        }
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        //$this->datagrid->datatable = 'true';
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'N°', 'center', '10%');
        $column_mov_total = new TDataGridColumn('mov_total', 'Valor', 'center');
        $column_mov_desconto = new TDataGridColumn('mov_desconto', 'Desconto', 'center');
        $column_mov_data = new TDataGridColumn('mov_data', 'Data Lançamento', 'center');
        $column_mov_baixa = new TDataGridColumn('mov_baixa', 'Data Baixa', 'center');
        $column_mov_valor = new TDataGridColumn('mov_valor', 'Valor Lançado', 'center');

        $column_id->setTransformer( function ($value, $object, $row) {
            if ($object->mov_ativo == 'N')
            {
                $row->style= 'color: silver';
            }
            
            return $value;
        });

        $column_mov_total->setTransformer( function($value, $object) {
            if ($value)
            {
                $value = number_format($value, 2, ',', '.');
                $label = 'info';
            }

            $div = new TElement('span');
            $div->class="label label-" . $label;
            $div->style="text-shadow:none; font-size:12px";
            $div->add( $value );
            return $div;
        });

        $column_mov_valor->setTransformer( function($value, $object) {
            if ($value)
            {
                $value = number_format($value, 2, ',', '.');
                $label = 'success';
            }

            $div = new TElement('span');
            $div->class="label label-" . $label;
            $div->style="text-shadow:none; font-size:12px";
            $div->add( $value );
            return $div;
        });

        $column_mov_desconto->setTransformer( function($value, $object) {
            if ($value)
            {
                $value = number_format($value, 2, ',', '.');
                $label = 'warning';
            }

            $div = new TElement('span');
            $div->class="label label-" . $label;
            $div->style="text-shadow:none; font-size:12px";
            $div->add( $value );
            return $div;
        });

        $column_mov_data->setTransformer( function($value, $object) {
            return TDate::convertToMask($value, 'yyyy-mm-dd', 'dd/mm/yyyy');
        });
        
        $column_mov_baixa->setTransformer( function($value, $object) {
            if ($object->mov_ativo == 'N' && !empty($object->mov_baixa) )
            {
                $value = $object->mov_baixa;
                $label = 'success';
            }
            elseif( $object->mov_ativo == 'N' && empty($object->mov_baixa) )
            {
                return 'CANCELADA';
            }

            if ($value)
            {
                $value = TDate::convertToMask($value, 'yyyy-mm-dd', 'dd/mm/yyyy');
                $label = 'success';
            }
            elseif ( empty($object->mov_baixa) )
            {
                $value = 'NÃO BAIXADO';
                $label = 'danger';
            }
            else
            {
                $value = 'BAIXA REALIZADA';
                $label = 'success';
            }

            $div = new TElement('span');
            $div->class="label label-" . $label;
            $div->style="text-shadow:none; font-size:12px";
            $div->add( $value );
            return $div;
        });


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_mov_total);
        $this->datagrid->addColumn($column_mov_data);
        $this->datagrid->addColumn($column_mov_baixa);
        $this->datagrid->addColumn($column_mov_desconto);
        $this->datagrid->addColumn($column_mov_valor);


        // creates the datagrid column actions
        $column_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
        $column_mov_valor->setAction(new TAction([$this, 'onReload']), ['order' => 'mov_valor']);
        $column_mov_desconto->setAction(new TAction([$this, 'onReload']), ['order' => 'mov_desconto']);
        $column_mov_data->setAction(new TAction([$this, 'onReload']), ['order' => 'mov_data']);
        $column_mov_baixa->setAction(new TAction([$this, 'onReload']), ['order' => 'mov_baixa']);
        $column_mov_total->setAction(new TAction([$this, 'onReload']), ['order' => 'mov_total']);
        

        $action1 = new TDataGridAction(['CaixaMovForm', 'onEdit'], ['id'=>'{id}', 'register_state' => 'false']);
        $action2 = new TDataGridAction([$this, 'onCancel'], ['id'=>'{id}']);

        $this->datagrid->addAction($action1, 'Informações da Movimentação',   'fas:book blue');
        $this->datagrid->addAction($action2, 'Cancelar Movimentação', 'fa:ban red');

        //$action3 = new TDataGridAction([$this, 'onDelete'], ['id'=>'{id}']);
        //$this->datagrid->addAction($action3, 'Excluir Movimentação <b>(admin)</b>', 'fa:window-close red');
        

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
    public function onFechar($param)
    {
        $action = new TAction(array($this, 'FecharCaixa'));
        $action->setParameters($param);
        new TQuestion('<span style="font-weight: bold;">DESEJA REALIZAR O FECHAMENDO DO CAIXA ?</span>', $action);
    }

    /**
     * Cancelar Movimentação
     */
    public function onCancel($param)
    {
        try
        {
            TTransaction::open('DBUNIDADE');
            $CaixaMovimentacao = new CaixaMov($param['id']);
            
            if (empty($CaixaMovimentacao->mov_baixa))
            {
                if ($CaixaMovimentacao->mov_ativo == 'N')
                {
                    throw new Exception('<span style="font-weight: bold;">MOVIMENTAÇÃO JÁ ESTÁ CANCELADA!</span>');
                }
                else
                {
                    $CaixaMovimentacao->mov_ativo = 'N';
                    $CaixaMovimentacao->store();

                    $pos_action = new TAction( ['CaixaMovList', 'onReload'] );
                    new TMessage('info', '<span style="font-weight: bold;">Conta cancelada!</span>', $pos_action);
                }
            }
            else
            {
                throw new Exception('A MOVIMENTAÇÃO NÃO PODE SER CANCELADA!');
            }

            TTransaction::close();
            
            $this->onReload($param);
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
}
