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
class VendaListCaixa extends TPage
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
        $this->setActiveRecord('Venda');   // defines the active record
        $this->setDefaultOrder('id', 'desc');         // defines the default order
        $this->setLimit(20);

        $criteria = new TCriteria;
        $criteria->add(new TFilter('origem', '=', 'C'), TExpression::OR_OPERATOR);
        $this->setCriteria($criteria); // define a standard filter

        $this->addFilterField('id', '=', 'id'); // filterField, operator, formField
        $this->addFilterField('cliente_id', 'like', 'cliente_idp'); // filterField, operator, formField
        $this->addFilterField('financeiro_gerado', '=', 'financeiro_gerado'); // filterField, operator, formField
        $this->addFilterField('mes', '=', 'mes'); // filterField, operator, formField
        $this->addFilterField('ano', '=', 'ano'); // filterField, operator, formField
        $this->setOrderCommand('cliente->nome_fantasia', '(SELECT nome_fantasia FROM pessoa WHERE pessoa.id=fatura.cliente_id)');
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Venda');
        $this->form->setFormTitle('Venda');
        $this->form->enableCSRFProtection(); // ATIVA PROTEÇÃO CONTA EXECUÇÃO DE JAVA SCRIPT MALICIOSO

        // create the form fields
        $id = new TEntry('id');
        $cliente_id = new TDBUniqueSearch('cliente_id', TSession::getValue('unit_database'), 'Pessoa', 'id', 'nome_fantasia');
        $financeiro_gerado = new TRadioGroup('financeiro_gerado');
        $mes = new TRadioGroup('mes');
        $ano = new TRadioGroup('ano');
        
        $current = (int) date('Y');
        $mes->addItems( ['01' => 'Jan', '02' => 'Fev', '03' => 'Mar', '04' => 'Abr', '05' => 'Mai', '06' => 'Jun', '07' => 'Jul', '08' => 'Ago', '09' => 'Set', '10' => 'Out', '11' => 'Nov', '12' => 'Dez'] );
        $ano->addItems( [ ($current -5) => ($current -5), ($current -4) => ($current -4), ($current -3) => ($current -3), ($current -2) => ($current -2), ($current -1) => ($current -1), $current => $current ] );
        
        $mes->setLayout('horizontal');
        $ano->setLayout('horizontal');
        $cliente_id->setMinLength(0);
        
        $financeiro_gerado->addItems( ['Y' => 'Sim', 'N' => 'Não', '' => 'Ambos'] );
        $financeiro_gerado->setLayout('horizontal');
        
        // add the fields
        $this->form->addFields( [ new TLabel('N°') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Cliente') ], [ $cliente_id ] );
        $this->form->addFields( [ new TLabel('Financeiro Gerado') ], [ $financeiro_gerado ] );
        $this->form->addFields( [ new TLabel('Mes') ], [ $mes ] );
        $this->form->addFields( [ new TLabel('Ano') ], [ $ano ] );

        // set sizes
        $id->setSize('100%');
        $cliente_id->setSize('100%');
        $financeiro_gerado->setSize('100%');

        $this->form->addExpandButton('Menu Pesquisa' , 'fa:search-dollar', false);
        $id->setMask('999999');

        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink('Inserir Nova Venda', new TAction(['VendaForm', 'onEdit']), 'fa:plus green');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'N°', 'center');
        $column_cliente_id = new TDataGridColumn('cliente->nome_fantasia', 'Cliente', 'left');
        $column_dt_fatura = new TDataGridColumn('dt_fatura', 'Dt Venda', 'center');
        $column_total = new TDataGridColumn('total', 'Total', 'right');
        $column_financeiro_gerado = new TDataGridColumn('financeiro_gerado', 'Financeiro', 'center');
        
        
        $column_dt_fatura->enableAutoHide(500);
        $column_total->enableAutoHide(500);
        $column_financeiro_gerado->enableAutoHide(500);
        
        $column_id->setTransformer( function ($value, $object, $row) {
            if ($object->ativo == 'N')
            {
                $row->style= 'color: silver';
            }
            
            return $value;
        });
        
        $column_dt_fatura->setTransformer( function($value) {
            return TDate::convertToMask($value, 'yyyy-mm-dd', 'dd/mm/yyyy');
        });
        
        $column_total->setTransformer( function($value) {
            if (is_numeric($value)) {
                return 'R$&nbsp;'.number_format($value, 2, ',', '.');
            }
            return $value;
        });
        
        $column_financeiro_gerado->setTransformer( function($value, $object) {
            if ($object->ativo == 'N')
            {
                return 'Cancelada';
            }
            
            if ($object->financeiro_gerado == 'N')
            {
                $value = 'Não gerado';
                $label = 'danger';
            }
            else{
                $value = 'Aguardando Recebimento';
                $label = 'warning';
            }
            
            TTransaction::open(TSession::getValue('unit_database'));
            $fcc = VendaContaReceber::where('fatura_id', '=', $object->id)->first();

            if ($object->financeiro_gerado == 'Y')
            {
                if ($fcc)
                {
                    $conta_receber = ContaReceber::find($fcc->conta_receber_id);
                    if ($conta_receber)
                    {
                        if (!empty($conta_receber->dt_pagamento))
                        {
                            if( $object->total < $conta_receber->valor)
                            {
                                $value = 'Recebimento <b>nº ('.$conta_receber->id.')</b> a maior';
                                $label = 'success';
                            }elseif( $object->total > $conta_receber->valor)
                            {
                                $value = 'Recebimento <b>nº ('.$conta_receber->id.')</b> a menor';
                                $label = 'success';
                            }
                            else{
                                $value = 'Pago';
                                $label = 'success';
                            }
                            
                        }elseif( $object->total !== $conta_receber->valor){
                            $value = 'Recebimento <b>nº ('.$conta_receber->id.')</b> Divergente';
                            $label = 'warning';
                        }
                        
                        if($conta_receber->ativo == 'N'){
                            $value = 'Recebimento Cancelado';
                            $label = 'danger';
                        }
                    }
                }
            }
            TTransaction::close();
            
            $div = new TElement('span');
            $div->class="label label-" . $label;
            $div->style="text-shadow:none; font-size:12px";
            $div->add( $value );
            return $div;
        });
        
        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_cliente_id);
        $this->datagrid->addColumn($column_dt_fatura);
        $this->datagrid->addColumn($column_financeiro_gerado);
        $this->datagrid->addColumn($column_total);


        // creates the datagrid column actions
        $column_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
        $column_cliente_id->setAction(new TAction([$this, 'onReload']), ['order' => 'cliente->nome_fantasia']);
        $column_dt_fatura->setAction(new TAction([$this, 'onReload']), ['order' => 'dt_fatura']);

        
        $action1 = new TDataGridAction(['VendaForm', 'onEdit'], ['id'=>'{id}']);
        $action2 = new TDataGridAction([$this, 'onCancel'], ['id'=>'{id}']);
        
        $this->datagrid->addAction($action1, _t('Edit'),   'far:edit blue');
        $this->datagrid->addAction($action2,_t('Cancel'), 'fa:power-off red');
        
        $action1->setDisplayCondition( function ($object) {
            return $object->ativo !== 'N';
        });
        
        $action2->setDisplayCondition( function ($object) {
            return $object->ativo !== 'N';
        });
        
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
     * Cancela Venda
     */
    public function onCancel($param)
    {
        try
        {
            TTransaction::open(TSession::getValue('unit_database'));
            $venda = new Venda($param['id']);
            
            if ($venda->ativo == 'Y')
            {
                $fcr = VendaContaReceber::where('fatura_id', '=', $venda->id)->first();
                
                if ($fcr)
                {
                    $contareceber = ContaReceber::find($fcr->conta_receber_id);
                    
                    if (!empty($contareceber->dt_pagamento))
                    {
                        throw new Exception('Conta a receber já quitada');
                    }
                    else
                    {
                        $contareceber->ativo = 'N';
                        $contareceber->store();
                    }
                }

                $venda->ativo = 'N';
                $venda->store();

                /////////////////////////////////////////////////////////////////////////////////

                $pesq_venda = VendaItem::where('fatura_id', '=', $venda->id)->load();
                foreach ($pesq_venda as $venda_item)
                {
                    //print 'Produto id:'.$venda_item->servico_id.'<br>';
                    //print 'Quantidade:'.$venda_item->quantidade.'<br>';

                    $qnt_produto = explode(".", $venda_item->quantidade);
                    if(!empty($qnt_produto[1]))
                    {
                        if($qnt_produto[1] > 0)
                        {
                            $qnt_produto[0] += 1;
                        }
                                    
                    }

                    $produto = new Produto($venda_item->servico_id);

                    $estoque_atual = $produto->estoque;
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

                    $produto->estoque = $estoque_novo;
                    $produto->store();

                }
                /////////////////////////////////////////////////////////////////////////////////
                
                
                new TMessage('info', 'Venda cancelada');
            }
            
            TTransaction::close();
            
            $this->onReload($param);
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
    public function Delete($param)
    {
        new TMessage('error', 'Operação não permitida');
    }
}
