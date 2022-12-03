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
class CaixaList extends TPage
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
        $this->setActiveRecord('Caixa');   // defines the active record
        $this->setDefaultOrder('id', 'desc');         // defines the default order
        $this->setLimit(10);
        // $this->setCriteria($criteria) // define a standard filter

        $this->addFilterField('id', '=', 'id'); // filterField, operator, formField
        $this->addFilterField('aberto', '=', 'aberto'); // filterField, operator, formField
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Caixa');
        $this->form->setFormTitle('REGISTROS DE CAIXA');
        $this->form->enableCSRFProtection(); // ATIVA PROTEÇÃO CONTA EXECUÇÃO DE JAVA SCRIPT MALICIOSO
        
        // create the form fields
        $id = new TEntry('id');

        $aberto = new TRadioGroup('aberto');
        $aberto->addItems( ['Y' => 'Sim', 'N' => 'Não', '' => 'Ambos'] );
        $aberto->setLayout('horizontal');

        // add the fields
        $this->form->addFields( [ new TLabel('N°') ], [ $id ], [ new TLabel('Caixa Aberto?') ], [ $aberto ] );

        // set sizes
        $id->setSize('100%');
        $aberto->setSize('100%');
        
        $this->form->addExpandButton('Expandir' , 'fa:search', true);

        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink('Abrir Novo Caixa', new TAction(['CaixaForm', 'onEdit'], ['register_state' => 'false']), 'fa:plus green');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        //$this->datagrid->datatable = 'true';
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'N°', 'center', '10%');
        $column_abertura = new TDataGridColumn('abertura', 'Data Abertura', 'center');
        $column_fechamento = new TDataGridColumn('fechamento', 'Data Fechamento', 'center');
        $column_valor = new TDataGridColumn('valor', 'Informações', 'center');
        //$column_usuario = new TDataGridColumn('usuario', 'Usuário', 'center');
        $column_aberto = new TDataGridColumn('aberto', 'Caixa Aberto', 'center');

        //$column_usuario->setTransformer( function($value, $object) {
        //    if ($value)
        //    {
        //        $value = strtoupper($value);
        //        $label = 'info';
        //    }

        //    $div = new TElement('span');
        //    $div->class="label label-" . $label;
        //    $div->style="text-shadow:none; font-size:12px";
        //    $div->add( $value );
        //    return $div;
        //});

        $column_abertura->setTransformer( function($value) {
            return TDate::convertToMask($value, 'yyyy-mm-dd', 'dd/mm/yyyy');
        });

        $column_fechamento->setTransformer( function($value) {
            return TDate::convertToMask($value, 'yyyy-mm-dd', 'dd/mm/yyyy');
        });
        
        $column_aberto->setTransformer( function($value, $object) {
            if ($object->aberto == 'N')
            {
                $value = 'Fechado';
                $label = 'danger';
            }
            elseif( $object->aberto == 'Y' AND $object->situacao == 'C')
            {
                $value = 'Em Conferência';
                $label = 'warning';
            }
            elseif( $object->aberto == 'Y' AND $object->situacao == 'P')
            {
                $value = 'Aguardando Pgto.';
                $label = 'warning';
            }
            else
            {
                $value = 'Aberto';
                $label = 'success';
            }

            $div = new TElement('span');
            $div->class="label label-" . $label;
            $div->style="text-shadow:none; font-size:12px";
            $div->add( $value );
            return $div;
        });

        $column_valor->setTransformer( function($value, $object) {
            if ($object->valor > 0)
            {
                $pega_caixa = new Caixa($object->id);
                $caixaValor = $pega_caixa->valorAbertura + $value;


                $value = 'Vendas: R$ '.$value.'  <br>  <small>Saldo Inicial: R$ '.$pega_caixa->valorAbertura.'</small>  <br>  <small><b>Total: R$ '.$caixaValor.'</b></small>';
                $label = 'success';
            }
            else
            {
                $value = $value;
                $label = 'info';
            }

            $div = new TElement('span');
            $div->class="label label-" . $label;
            $div->style="text-shadow:none; font-size:12px";
            $div->add( $value );
            return $div;
        });

        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_abertura);
        $this->datagrid->addColumn($column_fechamento);
        $this->datagrid->addColumn($column_valor);
        //$this->datagrid->addColumn($column_usuario);
        $this->datagrid->addColumn($column_aberto);


        // creates the datagrid column actions
        $column_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
        $column_abertura->setAction(new TAction([$this, 'onReload']), ['order' => 'abertura']);
        $column_fechamento->setAction(new TAction([$this, 'onReload']), ['order' => 'fechamento']);
        $column_valor->setAction(new TAction([$this, 'onReload']), ['order' => 'valor']);
        //$column_usuario->setAction(new TAction([$this, 'onReload']), ['order' => 'usuario']);
        $column_aberto->setAction(new TAction([$this, 'onReload']), ['order' => 'aberto']);

        
        //$action1 = new TDataGridAction(['CaixaForm', 'onEdit'], ['id'=>'{id}', 'register_state' => 'false']);
        //$action2 = new TDataGridAction([$this, 'onFechar'], ['id'=>'{id}']);
        //$action3 = new TDataGridAction([$this, 'onBater'], ['id'=>'{id}']);
        
        //$this->datagrid->addAction($action3, 'Bater Caixa', 'fas:book orange');
        //$this->datagrid->addAction($action1, 'Informações Do Caixa',   'fas:info-circle blue');
        //$this->datagrid->addAction($action2, 'Receber Valores Do Caixa', 'far:money-bill-alt green');

        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        $action1 = new TDataGridAction([$this, 'onFechar'], ['id' => '{id}' ] );
        $action2 = new TDataGridAction([$this, 'onBater'], ['id' => '{id}' ] );
        $action3 = new TDataGridAction([$this, 'onGerarFinanceiro'], ['id' => '{id}' ] );
        $action4 = new TDataGridAction(['CaixaForm', 'onEdit'], ['id'=>'{id}', 'register_state' => 'false']);
        $action5 = new TDataGridAction([$this, 'onReabrirCaixa'], ['id' => '{id}' ] );

        $action1->setDisplayCondition( function ($object) {
            if ($object->aberto == 'Y' AND $object->situacao == 'P')
            {
                return TRUE;
            }
            return FALSE;
        });

        $action2->setDisplayCondition( function ($object) {
            if ($object->aberto == 'Y' AND $object->situacao == 'A' || $object->situacao == 'P')
            {
                return TRUE;
            }
            return FALSE;
        });

        $action3->setDisplayCondition( function ($object) {
            if ($object->aberto == 'Y' AND $object->situacao == 'C')
            {
                return TRUE;
            }
            return FALSE;
        });

        $action5->setDisplayCondition( function ($object) {
            if ($object->aberto == 'Y' AND $object->situacao == 'C')
            {
                return TRUE;
            }
            return FALSE;
        });
        
        $action1->setLabel('Fechar Caixa');
        $action1->setImage('far:money-bill-alt green');
        
        $action2->setLabel('Bater Caixa');
        $action2->setImage('fas:book orange');
        
        $action3->setLabel('Gerar Financeiro');
        $action3->setImage('far:clipboard green');

        $action4->setLabel('Informações Do Caixa');
        $action4->setImage('fas:info-circle blue');

        $action5->setLabel('Reabrir Caixa');
        $action5->setImage('fas:shopping-cart green');
        
        $action_group = new TDataGridActionGroup('', 'fa:bars');
        
        
        $action_group->addHeader('Conferência');
        $action_group->addAction($action4);
        $action_group->addAction($action2);
        $action_group->addAction($action3);
        $action_group->addSeparator();
        $action_group->addHeader('Recebimento');
        $action_group->addAction($action1);
        $action_group->addHeader('Caixa');
        $action_group->addAction($action5);
        
        $this->datagrid->addActionGroup($action_group);

        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
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

    // define o log
    //TTransaction::setLogger(new TLoggerTXT('/tmp/log.txt'));
    //TTransaction::log("** inserindo cidade");
    //http://localhost/sic/erp/index.php?class=CaixaList&method=onFechar&id=9&key=9

    public function onBater($param)
    {
        $action = new TAction(array($this, 'BaterCaixa'));
        $action->setParameters($param);
        new TQuestion('<span style="font-weight: bold;">DESEJA BATER O CAIXA ?</span>', $action);
    }

    public function BaterCaixa($param)
    {
        try
        {
            // PEGA ID CAIXA LINHA
            $key = $param['key'];
            TTransaction::open('DBUNIDADE');

            //$pega_caixa = Caixa::last();
            $pega_caixa = new Caixa($key);

            if($pega_caixa->id == $key AND $pega_caixa->aberto == 'Y'){

                $PuxaRelatorioGeralCaixa = CaixaMov::where('caixa_id ','=',$pega_caixa->id)->sumBy('mov_valor');

                //$PuxaRelatorioGeralCaixa = CaixaMov::BaterMov( $pega_caixa->id, $pega_caixa->abertura, date('Y-m-d') );

                if( empty($PuxaRelatorioGeralCaixa) )
                {
                    $PuxaRelatorioGeralCaixa = 0.00;
                }

                if( $PuxaRelatorioGeralCaixa > 0.00)
                {
                    if( $pega_caixa->situacao == 'C' )
                    {
                        $caixaAberto = Caixa::find($key);
                        $caixaAberto->valor = $PuxaRelatorioGeralCaixa;
                        $caixaAberto->store();
                        $pos_action = new TAction(['CaixaList', 'onReload']);
                        new TMessage('warning', '
                    <table style="width:100%">
                    <tr>
                        <td><span style="font-weight: bold;">Informações</span></td>
                        <td><span style="font-weight: bold;">Data</span></td>
                    </tr>
                    <tr>
                        <td aling="left">Data Abertura</td>
                        <td><u>'.TDate::convertToMask($pega_caixa->abertura, 'yyyy-mm-dd', 'dd/mm/yyyy').'</u></td>
                    </tr>
                    <tr>
                        <td>Operador</td>
                        <td><u>'.strtoupper($pega_caixa->usuario).'</u></td>
                    </tr>
                    <tr>
                        <td> </td>
                        <td> </td>
                    </tr>
                    <tr>
                        <td colspan="2"><CENTER>TOTAL A RECEBER: $<u>'.$PuxaRelatorioGeralCaixa.'</u></CENTER></td>
                    </tr>
                    </table>', $pos_action);
                    
                    return;
                    }

                    if( $pega_caixa->situacao == 'P' )
                    {
                        throw new Exception('<span style="font-weight: bold;">Realize a abertura do caixa novamente para conferir.</span>');
                    }

                    if( $pega_caixa->situacao == 'F' )
                    {
                        throw new Exception('<span style="font-weight: bold;">CAiXA FECHADO PARA MOVIMENTAÇÕES</span>');
                    }

                    if( $pega_caixa->situacao == 'A' )
                    {
                        $caixaAberto = Caixa::find($key);
                        $caixaAberto->valor = $PuxaRelatorioGeralCaixa;
                        $caixaAberto->situacao = 'C';
                        $caixaAberto->store();
                    }
                    else
                    {
                        throw new Exception('<span style="font-weight: bold;">ERROR AO TENTAR BATER O CAIXA<BR>ENTRE EM CONTATO COM O SUPORTE TÉCNICO</span>');
                    }

                    $pos_action = new TAction(['CaixaList', 'onReload']);
                    new TMessage('warning', '
                    <table style="width:100%">
                    <tr>
                        <td><span style="font-weight: bold;">Informações</span></td>
                        <td><span style="font-weight: bold;">Data</span></td>
                    </tr>
                    <tr>
                        <td aling="left">Data Abertura</td>
                        <td><u>'.TDate::convertToMask($pega_caixa->abertura, 'yyyy-mm-dd', 'dd/mm/yyyy').'</u></td>
                    </tr>
                    <tr>
                        <td>Operador</td>
                        <td><u>'.strtoupper($pega_caixa->usuario).'</u></td>
                    </tr>
                    <tr>
                        <td> </td>
                        <td> </td>
                    </tr>
                    <tr>
                        <td colspan="2"><CENTER>TOTAL A RECEBER: $<u>'.$PuxaRelatorioGeralCaixa.'</u></CENTER></td>
                    </tr>
                    </table>', $pos_action);
                }
                else
                {
                    new TMessage('info', 'NÃO HOUVE MOVIMENTAÇÕES NO CAIXA');
                }
            }
            else
            {
                throw new Exception('<span style="font-weight: bold;">CAIXA FECHADO PARA MOVIMENTAÇÕES</span>');
            }

            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    public function onReabrirCaixa($param)
    {
        $action = new TAction(array($this, 'ReabrirCaixa'));
        $action->setParameters($param);
        new TQuestion('<span style="font-weight: bold;">DESEJA REABRIR O CAIXA ?</span>', $action);
    }

    public function ReabrirCaixa($param)
    {
        try
        {
            $key = $param['key'];

            TTransaction::open('DBUNIDADE');
            $pega_caixa = new Caixa($key);

            if( $pega_caixa->aberto == 'Y' )
            {
                if($pega_caixa->situacao == 'C')
                {
                    $caixaAberto = Caixa::find($key);
                    $caixaAberto->situacao = 'A';
                    $caixaAberto->store();

                    $pos_action = new TAction(['CaixaList', 'onReload']);
                    new TMessage('info', '<span style="font-weight: bold;">CAIXA '.$key.' ABERTO</span>', $pos_action);
                }
                else
                {
                    new TMessage('error', 'O CAIXA NÃO ESTÁ EM MODO CONFERÊNCIA');
                }
            }
            else
            {
                throw new Exception('<span style="font-weight: bold;">CAiXA FECHADO PARA MOVIMENTAÇÕES</span>');
            }
            TTransaction::close();
        }
        catch (Exception $e)
        {
            //new TMessage('error','<span style="font-weight: bold;">ERRO</span>');
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    public function onGerarFinanceiro($param)
    {
        $action = new TAction(array($this, 'GerarFinanceiro'));
        $action->setParameters($param);
        new TQuestion('<span style="font-weight: bold;">DESEJA GERAR O LANÇAMENTO DO CAIXA ?</span>', $action);
    }

    public function GerarFinanceiro($param)
    {
        try
        {
            // PEGA ID CAIXA LINHA
            $key = $param['key'];
            TTransaction::open('DBUNIDADE');

            $pega_caixa = Caixa::find($key);

            $recebimento = ContaReceber::where('id_caixa ','=',$param['id'])->load();

            if( $pega_caixa->usuario == TSession::getValue('login') )
            {
                if($pega_caixa->aberto == 'Y' && $pega_caixa->situacao == 'C')
                {
                    $conta_receber = new ContaReceber;
                    $conta_receber->dt_emissao = date('Y-m-d');
                    $conta_receber->dt_vencimento = date("Y-m-d", strtotime("+1 day") );
                    $conta_receber->pessoa_id = 1; // OPERADOR CAIXA
                    $conta_receber->valor = $pega_caixa->valor;
                    $conta_receber->valor_total = $pega_caixa->valor;
                    $conta_receber->desconto = 00.00;
                    $conta_receber->obs = 'Recebimento Vinculado ao Caixa '.strtoupper($pega_caixa->usuario).' DATA DE ABERTURA:'.TDate::convertToMask($pega_caixa->abertura, 'yyyy-mm-dd', 'dd/mm/yyyy').'';
                    $conta_receber->ano = date('Y', strtotime("+1 day") );
                    $conta_receber->mes = date('m', strtotime("+1 day") );
                    $conta_receber->ativo = 'Y';
                    $conta_receber->origem = 'C';
                    $conta_receber->id_caixa = $pega_caixa->id;
                    $conta_receber->store();

                    $caixaPgto = Caixa::find($key);
                    $caixaPgto->situacao = 'P';
                    $caixaPgto->store();

                    $pos_action = new TAction(['ContaReceberList', 'onReload']);
                    new TMessage('info', '<span style="font-weight: bold;">FATURAMENTO DO CAIXA '.$pega_caixa->id.'<BR> GERADO COM SUCESSO</span>', $pos_action);
                }
                elseif($pega_caixa->aberto == 'Y' AND empty($recebimento[0]->id))
                {
                    $conta_receber = new ContaReceber;
                    $conta_receber->dt_emissao = date('Y-m-d');
                    $conta_receber->dt_vencimento = date("Y-m-d", strtotime("+1 day") );
                    $conta_receber->pessoa_id = 1; // OPERADOR CAIXA
                    $conta_receber->valor = $pega_caixa->valor;
                    $conta_receber->valor_total = $pega_caixa->valor;
                    $conta_receber->desconto = 00.00;
                    $conta_receber->obs = 'Lançamento Vinculado ao Caixa '.strtoupper($pega_caixa->usuario).' |  DATA DE ABERTURA: '.TDate::convertToMask($pega_caixa->abertura, 'yyyy-mm-dd', 'dd/mm/yyyy').' | ' .' DATA DE FECHAMENTO: '.TDate::convertToMask($pega_caixa->fechamento, 'yyyy-mm-dd', 'dd/mm/yyyy').' |';
                    $conta_receber->ano = date('Y', strtotime("+1 day") );
                    $conta_receber->mes = date('m', strtotime("+1 day") );
                    $conta_receber->ativo = 'Y';
                    $conta_receber->origem = 'C';
                    $conta_receber->id_caixa = $pega_caixa->id;
                    $conta_receber->store();

                    $caixaPgto = Caixa::find($key);
                    $caixaPgto->situacao = 'P';
                    $caixaPgto->store();

                    $pos_action = new TAction(['ContaReceberList', 'onReload']);
                    new TMessage('info', '<span style="font-weight: bold;">FATURAMENTO DO CAIXA '.$pega_caixa->id.'<BR> ALTERADO COM SUCESSO</span>', $pos_action);
                }
                else
                {
                    throw new Exception('<span style="font-weight: bold;">FATURAMENTO VINCULADO AO CAIXA JÁ FOI LANÇADO<BR><BR><SMALL>Verifique na tela de lançamentos a Receber.</SMALL></span>');
                }
            }
            else
            {
                new TMessage('danger', '<span style="font-weight: bold;">SOMENTE O USUÁRIO QUE ABRIU O CAIXA<BR>PODE REALIZAR MOVIMENTAÇÕES<span>');
            }    

            TTransaction::close();
        }
        catch (Exception $e)
        {
            //new TMessage('error','<span style="font-weight: bold;">ERRO AO APAGAR FORMA DE RECEBIMENTO</span>');
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    public function onFechar($param)
    {
        $action = new TAction(array($this, 'FecharCaixa'));
        $action->setParameters($param);
        new TQuestion('<span style="font-weight: bold;">DESEJA GERAR O RECEBIMENTO DO CAIXA ?</span>', $action);
    }

    public function FuncaoBase($param)
    {
        try
        {
            TTransaction::open('DBUNIDADE');
            TTransaction::close();

        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }

    }

    public function FecharCaixa($param)
    {
        try
        {
            $key = $param['key'];
            TTransaction::open('DBUNIDADE');

            $pega_caixa = Caixa::find($key);

            $recebimento = ContaReceber::where('id_caixa ','=',$param['id'])->load();

            $PuxaRelatorioGeralCaixa = CaixaMov::where('caixa_id ','=',$pega_caixa->id)->sumBy('mov_total');

            if($pega_caixa->situacao == 'P')
            {
                if(isset($recebimento[0]->origem))
                {
                    if( $recebimento[0]->origem == 'C')
                    {
                        if( !empty($recebimento[0]->dt_pagamento) && $recebimento[0]->ativo == 'Y')
                        {
                            //$pega_recebimentoPG = CaixaMov::PegarRecebimentoValor( $pega_recebimento );
                            
                            $pega_recebimentoPG = ContaReceber::where('id_caixa ','=',$pega_caixa->id)->sumBy('valor');

                            if ($pega_recebimentoPG == $pega_caixa->valor)
                            {
                                $pega_caixa->fechamento = date('Y-m-d h:m:s');
                                $pega_caixa->aberto = 'N';
                                $pega_caixa->store();
                                CaixaMov::ZerarCaixa( $pega_caixa->id, date('Y-m-d') );
                            }
                            else{
                                throw new Exception('<span style="font-weight: bold;">VALOR ENTRE RECEBIMENTO x CAIXA É INCONSISTENTE<A><BR><BR><SMALL>Verifique o lançamento relacionado ao caixa.</SMALL></span>');
                            }

                            $pos_action = new TAction(['CaixaList', 'onReload']);
                            new TMessage('info', '<span style="font-weight: bold;"CAIXA '.$pega_caixa->id.'<BR> FECHADO COM SUCESSO</span>', $pos_action);
                        }
                        else{
                        
                            throw new Exception('<span style="font-weight: bold;"><A HREF="?class=ReceberForm&method=onEdit&id='.$pega_recebimento.'&register_state=false&key='.$pega_recebimento.'">O CAIXA NÃO PODE SER FECHADO!<BR>FAÇA A BAIXA DO RECEBIMENTO PARA FECHAR O CAIXA.<A></span>');
                        }
                    }
                    else{
                        throw new Exception('<span style="font-weight: bold;">O RECEBIMENTO VINCULADO NÃO É ORIGEM CAIXA<A></span>');
                    }
                }
                else{
                    throw new Exception('<span style="font-weight: bold;">RECEBIMENTO NÃO ESTÁ SENDO VINCULADO<A></span>');
                }
            }
            elseif($pega_caixa->situacao == 'A' && !isset($PuxaRelatorioGeralCaixa))
            {
                $pega_caixa->fechamento = date('Y-m-d h:m:s');
                $pega_caixa->situacao = 'F';
                $pega_caixa->aberto = 'N';
                $pega_caixa->store();
                CaixaMov::ZerarCaixa( $pega_caixa->id, date('Y-m-d') );
                $pos_action = new TAction(['CaixaList', 'onReload']);
                new TMessage('info', '<span style="font-weight: bold;"CAIXA '.$pega_caixa->id.'<BR> FECHADO COM SUCESSO!<BR><SMALL>(SEM MOVIMENTAÇÕES)</SMALL></span>', $pos_action);
            }
            else
            {
                throw new Exception('<span style="font-weight: bold;">O CAIXA NÃO ESTÁ MODO DE PAGAMENTO PARA FECHAR<A></span>');
            }
            TTransaction::close();
        }
        catch (Exception $e)
        {
            //new TMessage('error','<span style="font-weight: bold;">ERRO</span>');
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

}
