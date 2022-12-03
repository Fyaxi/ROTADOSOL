<?php
/**
 * SaleList
 *
 * @version    1.0
 * @package    DBUNIDADE
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SaleList extends TPage
{
    protected $form;     // registration form
    protected $datagrid; // listing
    protected $pageNavigation;
    
    use Adianti\Base\AdiantiStandardListTrait;
    
    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->setDatabase('DBUNIDADE');          // defines the database
        $this->setActiveRecord('Venda');         // defines the active record
        $this->setDefaultOrder('id', 'desc');    // defines the default order
        $this->addFilterField('id', '=', 'id'); // filterField, operator, formField
        $this->addFilterField('caixa', '=', 'caixa'); // filterField, operator, formField
        $this->addFilterField('customer_id', '=', 'customer_id'); // filterField, operator, formField
        $this->setLimit(50);
        
        $this->addFilterField('date', '>=', 'date_from', function($value) {
            return TDate::convertToMask($value, 'dd/mm/yyyy', 'yyyy-mm-dd');
        }); // filterField, operator, formField, transformFunction
        
        $this->addFilterField('date', '<=', 'date_to', function($value) {
            return TDate::convertToMask($value, 'dd/mm/yyyy', 'yyyy-mm-dd');
        }); // filterField, operator, formField, transformFunction
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Sale');
        $this->form->setFormTitle('Vendas');
        
        // create the form fields
        $id        = new TEntry('id');
        $date_from = new TDate('date_from');
        $date_to   = new TDate('date_to');

        $caixa        = new TEntry('caixa');
        $caixa->setMask('999999');
        
        $criteria = new TCriteria;
        $criteria->add(new TFilter('grupo_id', '=', 1), TExpression::OR_OPERATOR); 
        $customer_id = new TDBUniqueSearch('customer_id', 'DBUNIDADE', 'Pessoa', 'id', 'nome_fantasia', 'grupo_id ', $criteria);
        $customer_id->setMinLength(0);
        //$customer_id->setMask('{name} ({id})');
        
        // add the fields
        $this->form->addFields( [new TLabel('Nº')],          [$id], 
                                [new TLabel('Caixa Nº')],          [$caixa]); 
        $this->form->addFields( [new TLabel('Data (Início)')], [$date_from],
                                [new TLabel('Data (Fim)')],   [$date_to] );
        $this->form->addFields( [new TLabel('Cliente')],    [$customer_id] );
        
        $id->setSize('100%');
        $date_from->setSize('100%');
        $date_to->setSize('100%');
        $date_from->setMask( 'dd/mm/yyyy' );
        $date_to->setMask( 'dd/mm/yyyy' );
        $caixa->setSize('100%');
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('SaleList_filter_data') );
        
        // add the search form actions
        $this->form->addAction('Buscar', new TAction([$this, 'onSearch']), 'fa:search');
        $this->form->addActionLink('Nova Venda',  new TAction(['SaleForm', 'onEdit']), 'fa:plus green');
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = '100%';
        
        // creates the datagrid columns
        $column_id       = new TDataGridColumn('id', 'Nº', 'center', '10%');
        $column_date     = new TDataGridColumn('date', 'Data', 'center', '20%');
        $column_customer = new TDataGridColumn('customer->nome_fantasia', 'Cliente', 'left', '50%');
        $column_total    = new TDataGridColumn('total', 'Total', 'right', '20%');
        
        // define format function
        $format_value = function($value) {
            if (is_numeric($value)) {
                return 'R$ '.number_format($value, 2, ',', '.');
            }
            return $value;
        };
        
        $column_total->setTransformer( $format_value );
        
        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_date);
        $this->datagrid->addColumn($column_customer);
        $this->datagrid->addColumn($column_total);
        
        // creates the datagrid column actions
        $column_id->setAction(new TAction([$this, 'onReload']),   ['order' => 'id']);
        $column_date->setAction(new TAction([$this, 'onReload']), ['order' => 'date']);
        
        // define the transformer method over date
        $column_date->setTransformer( function($value, $object, $row) {
            $date = new DateTime($value);
            return $date->format('d/m/Y');
        });

        $action_view   = new TDataGridAction(['SaleSidePanelView', 'onView'],   ['key' => '{id}', 'register_state' => 'false'] );
        $action_edit   = new TDataGridAction(['SaleForm', 'onEdit'],   ['key' => '{id}'] );
        $action_delete = new TDataGridAction([$this, 'onQuestionario'],   ['key' => '{id}'] );

        $column_id->setTransformer( function ($value, $object, $row) {
            if ($object->ativo == 'N')
            {
                $row->style= 'color: silver';
            }
            
            return $value;
        });

        $action_edit->setDisplayCondition( function ($object) {
            return $object->ativo !== 'N';
        });

        $action_delete->setDisplayCondition( function ($object) {
            return $object->ativo !== 'N';
        });
        
        $this->datagrid->addAction($action_view, 'Visualizar Venda', 'fa:search green fa-fw');
        $this->datagrid->addAction($action_edit, 'Editar',   'far:edit blue fa-fw');
        $this->datagrid->addAction($action_delete, 'Deletar', 'far:trash-alt red fa-fw');
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel = TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));
        $panel->getBody()->style = 'overflow-x:auto';
        parent::add($container);
    }

    /**
     * Show the input dialog data
     */
    public static function onQuestionario($param)
    {
        $action1 = new TAction(array('SaleList', 'onCancelarVenda'));
        $action2 = new TAction(array('SaleList', 'onCancelarOperacao'));

        $action1->setParameter('venda', $param);
        $action2->setParameter('cancelar', 2);
        
        new TQuestion('Deseja cancelar a enda e suas movimentações no caixa ?', $action1, $action2);
    }

    public static function onCancelarVenda($param)
    {
        TTransaction::open('DBUNIDADE');
        try
        {
            TTransaction::open('DBUNIDADE');  

            #VendaItem::where('sale_id', '=', $param['venda']['id'])->delete();
            #Venda::where('id', '=', $param['venda']['id'])->delete();
            
            $CaixaMov = CaixaMov::where('venda_id', '=', $param['venda']['id'])
                                ->where('mov_ativo', '=', 'Y')
                                ->load();

            if(!isset($CaixaMov[0]->id))
            {
                /////////////////////////////////////////////////////////////////////////////////
                $pesq_venda = VendaItem::where('sale_id', '=', $param['venda']['id'])->load();
                if($pesq_venda)
                {
                    foreach ($pesq_venda as $venda_item)
                    {
                        $qnt_produto = explode(".", $venda_item->amount);
                        if(!empty($qnt_produto[1]))
                        {
                            if($qnt_produto[1] > 0)
                            {
                                $qnt_produto[0] += 1;
                            }
                        }

                        $produto = new Produto($venda_item->product_id);

                        $estoque_atual = $produto->stock;
                        //echo 'Estoque Atual: '.$estoque_atual.'<br>';

                        if(!$qnt_produto[0] == 0)
                        {
                            //echo 'Saida Produto: '.$qnt_produto[0].'<br>';
                            $estoque_novo = $estoque_atual + $qnt_produto[0];
                            //echo 'Novo Estoque (>0): '.$estoque_novo;
                        }
                        else{
                            //echo 'Saida Produto: '.$qnt_produto[0].'<br>';
                            $estoque_novo = $estoque_atual + 1;
                            //echo 'Novo Estoque (=0): '.$estoque_novo;
                        }
                        $produto->stock = $estoque_novo;
                        $produto->store();
                    }
                }
                else
                {
                    throw new Exception('<span style="font-weight: bold;">Aviso: Vínculo das Vendas com os Itens inexistente.<A></span>');
                }
                /////////////////////////////////////////////////////////////////////////////////

                $Venda = new Venda($param['venda']['id']);
                $Venda->ativo = 'N';
                $Venda->store();

                $pos_action = new TAction(['SaleList', 'onReload']);
                new TMessage('info', 'Venda Cancelada!', $pos_action);
            }
            else
            {
                throw new Exception('<span style="font-weight: bold;">Realize o cancelamento da venda nas movimentações no caixa.<A></span>');
            }
            TTransaction::close();

            
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
        
    }
    
    /**
     * Show the input dialog data
     */
    public static function onCancelarOperacao( $param )
    {
        $pos_action = new TAction(['SaleList', 'onReload']);
        new TMessage('error', 'Operação Cancelada!', $pos_action);
    }
}
