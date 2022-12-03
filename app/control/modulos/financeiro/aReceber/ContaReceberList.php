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
class ContaReceberList extends TPage
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
        $this->setActiveRecord('ContaReceber');   // defines the active record
        $this->setDefaultOrder('id', 'desc');         // defines the default order
        $this->setLimit(10);
        // $this->setCriteria($criteria) // define a standard filter

        $this->addFilterField('id', '=', 'id'); // filterField, operator, formField
        $this->addFilterField('pessoa_id', '=', 'pessoa_id'); // filterField, operator, formField
        $this->addFilterField('mes', '=', 'mes'); // filterField, operator, formField
        $this->addFilterField('ano', '=', 'ano'); // filterField, operator, formField

        $this->addFilterField('ativo', 'like', 'ativo'); // filterField, operator, formField
        
        $this->addFilterField('dt_emissao', '=', 'dt_emissao'); // filterField, operator, formField
        $this->addFilterField('dt_vencimento', '=', 'dt_vencimento'); // filterField, operator, formField
        $this->addFilterField('dt_pagamento', '=', 'dt_pagamento'); // filterField, operator, formField 

        $this->setOrderCommand('pessoa->nome_fantasia', '(SELECT nome_fantasia FROM pessoa WHERE pessoa.id=conta_receber.pessoa_id)');
        $this->setOrderCommand('conta_financeira->nome', '(SELECT nome FROM conta_financeira WHERE conta_financeira.id=forma_recebimeinto.id)');
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_ContaReceber');
        $this->form->setFormTitle('Contas a Receber');
        $this->form->enableCSRFProtection(); // ATIVA PROTEÇÃO CONTA EXECUÇÃO DE JAVA SCRIPT MALICIOSO
        

        // create the form fields
        $id = new TEntry('id');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('grupo_id', '=', 1), TExpression::OR_OPERATOR); 
        $pessoa_id = new TDBUniqueSearch('pessoa_id', 'DBUNIDADE', 'Pessoa', 'id', 'nome_fantasia', 'grupo_id ', $criteria);
        $mes = new TRadioGroup('mes');
        $ano = new TRadioGroup('ano');

        $ativo = new TRadioGroup('ativo');
        
        $dt_emissao = new TDate('dt_emissao');
        $dt_vencimento = new TDate('dt_vencimento');
        $dt_pagamento = new TDate('dt_pagamento');
        
        $current = (int) date('Y');
        $mes->addItems( ['01' => 'Jan', '02' => 'Fev', '03' => 'Mar', '04' => 'Abr', '05' => 'Mai', '06' => 'Jun', '07' => 'Jul', '08' => 'Ago', '09' => 'Set', '10' => 'Out', '11' => 'Nov', '12' => 'Dez', '' => 'Todos'] );
        $ano->addItems( [ ($current -5) => ($current -5), ($current -4) => ($current -4), ($current -3) => ($current -3), ($current -2) => ($current -2), ($current -1) => ($current -1), $current => $current, '' => 'Todos' ] );
        
        $mes->setLayout('horizontal');
        $ano->setLayout('horizontal');
        $pessoa_id->setMinLength(0);
        
        // add the fields
        $this->form->addFields( [ new TLabel('N°') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Dt Emissão') ], [ $dt_emissao ], [ new TLabel('Dt Vencimento') ], [ $dt_vencimento ], [ new TLabel('Dt Pagamento') ], [ $dt_pagamento ] );
        $this->form->addFields( [ new TLabel('Cliente') ], [ $pessoa_id ] );
        $this->form->addFields( [ new TLabel('Mes') ], [ $mes ] );
        $this->form->addFields( [ new TLabel('Ano') ], [ $ano ] );
        $this->form->addFields( [ new TLabel('Cancelados') ], [ $ativo ] );


        // set sizes
        $id->setSize('100%');
        $pessoa_id->setSize('100%');

        $this->form->addExpandButton('Menu Pesquisa' , 'fa:search-dollar', false);
        $id->setMask('999999');

        $ativo->addItems( ['Y' => 'Sim', 'N' => 'Não', '' => 'Ambos'] );
        $ativo->setLayout('horizontal');
        $ativo->setSize('100%');

        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink('Novo Recebimento', new TAction(['ReceberForm', 'onEdit'], ['register_state' => 'false']), 'fa:plus green');
        //$this->form->addActionLink('Recebimento Direto', new TAction(['ContaReceberForm', 'onEdit'], ['register_state' => 'false']), 'fa:money-bill-wave-alt green');
        
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        // $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'N°', 'center', '10%');
        $column_pessoa_id = new TDataGridColumn('pessoa->nome_fantasia', 'Pessoa', 'left');
        $column_dt_emissao = new TDataGridColumn('dt_emissao', 'Emissao', 'left');
        $column_dt_vencimento = new TDataGridColumn('dt_vencimento', 'Vencimento', 'left');
        $column_dt_pagamento = new TDataGridColumn('dt_pagamento', 'Pagamento', 'left');
        $column_valor = new TDataGridColumn('valor', 'Valor', 'right');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_pessoa_id);
        $this->datagrid->addColumn($column_dt_emissao);
        $this->datagrid->addColumn($column_dt_vencimento);
        $this->datagrid->addColumn($column_dt_pagamento);
        $this->datagrid->addColumn($column_valor);

        // creates the datagrid column actions
        $column_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
        $column_pessoa_id->setAction(new TAction([$this, 'onReload']), ['order' => 'pessoa->nome_fantasia']);
        $column_dt_emissao->setAction(new TAction([$this, 'onReload']), ['order' => 'dt_emissao']);
        $column_dt_vencimento->setAction(new TAction([$this, 'onReload']), ['order' => 'dt_vencimento']);
        $column_dt_pagamento->setAction(new TAction([$this, 'onReload']), ['order' => 'dt_pagamento']);
        
        $column_dt_emissao->enableAutoHide(500);
        $column_dt_vencimento->enableAutoHide(500);
        $column_dt_pagamento->enableAutoHide(500);
        $column_valor->enableAutoHide(500);
        
        $column_id->setTransformer( function ($value, $object, $row) {
            if ($object->ativo == 'N')
            {
                $row->style= 'color: silver';
            }
            
            return $value;
        });
        
        $column_dt_emissao->setTransformer( function($value) {
            return TDate::convertToMask($value, 'yyyy-mm-dd', 'dd/mm/yyyy');
        });
        
        $column_dt_pagamento->setTransformer( function($value, $object) {
            if ($object->ativo == 'N')
            {
                return 'cancelada';
            }


            if( empty($object->dt_pagamento) AND !empty($object->id_caixa) AND $object->origem == 'C' )
            {
                $value = 'Aguardando Caixa';
                $label = "warning";
            }

            if( !empty($object->dt_pagamento) AND !empty($object->id_caixa) AND $object->origem == 'C' )
            {
                $value = 'Liquid. (Caixa)';
                $label = "success";
            }


            if( !empty($object->dt_pagamento) AND empty($object->id_caixa) AND $object->origem == 'D' )
            {
                $value = 'Liquid. (Direta)';
                $label = "success";
            }

            if( empty($object->dt_pagamento) AND empty($object->id_caixa) AND $object->origem == 'D' )
            {
                $value = 'Aguardando';
                $label = "warning";
            }
            
            
            $pesq_fatura = FaturaContaReceber::where('conta_receber_id', '=', $object->id)->first();
            if ($pesq_fatura)
            {
                if ($value)
                {
                    $fatura = Fatura::find($pesq_fatura->fatura_id);
                    if( $object->valor !== $fatura->total){
                        $value = 'Fatura <b>nº ('.$fatura->id.')</b> Divergente';
                        $label = 'success';
                    }else{
                        $value = 'Liquidado';
                        //$value = TDate::convertToMask($value, 'yyyy-mm-dd', 'dd/mm/yyyy');
                        $label = 'success';
                    }
                }
                else
                {
                    if ($pesq_fatura)
                    {
                        $fatura = Fatura::find($pesq_fatura->fatura_id);
                        if ($fatura)
                        {  
                            if( $object->valor !== $fatura->total){
                                $value = 'Fatura <b>nº ('.$fatura->id.')</b> Divergente';
                                $label = 'warning';
                            }
                            else{
                                $value = 'Aguardando';
                                $label = 'warning';
                            }
                        }
                    }
                    
                }
            }
            
            $div = new TElement('span');

            if( empty($label) )
            {
                $value = 'Sem Situação';
                $label = "danger";
            }

            $div->class="label label-" . $label;

            $div->style="text-shadow:none; font-size:12px";
            $div->add($value);
            return $div;
        });
        
        $column_dt_vencimento->setTransformer( function($value, $object) {
            $today = new DateTime(date('Y-m-d'));
            $end   = new DateTime($value);
            $data = TDate::convertToMask($value, 'yyyy-mm-dd', 'dd/mm/yyyy');
            
            if ($object->ativo == 'Y' && empty($object->dt_pagamento) && !empty($value) && $today >= $end)
            {
                $div = new TElement('span');
                $div->class="label label-warning";
                $div->style="text-shadow:none; font-size:12px";
                $div->add($data);
                return $div;
            }
            
            return $data;
        });
        
        $column_valor->setTransformer( function($value) {
            if (is_numeric($value)) {
                return 'R$&nbsp;'.number_format($value, 2, ',', '.');
            }
            return $value;
        });

        $action1 = new TDataGridAction(['ReceberView', 'onView'], ['id'=>'{id}', 'register_state' => 'false']);
        $action2 = new TDataGridAction(['ReceberEdit', 'onEdit'], ['id'=>'{id}', 'register_state' => 'false']);
        $action3 = new TDataGridAction([$this, 'onReceber'], ['id'=>'{id}']);
        $action4 = new TDataGridAction([$this, 'onCancel'], ['id'=>'{id}']);
        $action5 = new TDataGridAction([$this, 'onEstornarPg'], ['id'=>'{id}']);
        $action6 = new TDataGridAction([$this, 'onEstornarCancelamento'], ['id'=>'{id}']);
        
        //$action1->setDisplayCondition( function ($object) {
        //    return $object->ativo !== 'N';
        //});
        
        $action2->setDisplayCondition( function ($object) {
            if ($object->ativo == 'Y' AND $object->dt_pagamento == NULL)
            {
                return TRUE;
            }
            return FALSE;
        });

        $action3->setDisplayCondition( function ($object) {
            if ($object->ativo == 'Y' AND $object->dt_pagamento == NULL)
            {
                return TRUE;
            }
            return FALSE;
        });

        $action4->setDisplayCondition( function ($object) {
            if ($object->ativo == 'Y' AND $object->dt_pagamento == NULL)
            {
                return TRUE;
            }
            return FALSE;
        });

        $action5->setDisplayCondition( function ($object) {
            if ($object->ativo == 'Y' AND $object->dt_pagamento !== NULL)
            {
                return TRUE;
            }
            return FALSE;
        });

        $action6->setDisplayCondition( function ($object) {
            return $object->ativo !== 'Y';
        });

        $action_group = new TDataGridActionGroup('Ações', 'fa:bars');

        $action1->setLabel('Visualizar');
        $action1->setImage('fa:eye #7C93CF');

        $action2->setLabel('Alterar Lanç.');
        $action2->setImage('far:edit #7C93CF');

        $action3->setLabel('Realizar Baixa');
        $action3->setImage('fa:credit-card green');

        $action4->setLabel('Cancelar');
        $action4->setImage('fa:ban red');

        $action5->setLabel('Estornar Lançamento');
        $action5->setImage('fa:ban red');

        $action6->setLabel('Estornar Cancelamento');
        $action6->setImage('fa:ban red');

        $action_group->addHeader('Rotina');
        $action_group->addAction($action1);
        $action_group->addAction($action2);
        $action_group->addSeparator();
        $action_group->addHeader('Financeiro');
        $action_group->addAction($action3);
        $action_group->addSeparator();
        $action_group->addHeader('Cancelamento');
        $action_group->addAction($action4);
        $action_group->addAction($action5);
        $action_group->addAction($action6);
        
        // add the actions to the datagrid
        $this->datagrid->addActionGroup($action_group);
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        
        $panel = new TPanelGroup('<small>recebimentos</small>', 'white');
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);
        
        // header actions
        //$dropdown = new TDropDown(_t('Export'), 'fa:list');
        //$dropdown->setPullSide('right');
        //$dropdown->setButtonClass('btn btn-default waves-effect dropdown-toggle');
        //$dropdown->addAction( _t('Save as CSV'), new TAction([$this, 'onExportCSV'], ['register_state' => 'false', 'static'=>'1']), 'fa:table blue' );
        //$dropdown->addAction( _t('Save as PDF'), new TAction([$this, 'onExportPDF'], ['register_state' => 'false', 'static'=>'1']), 'far:file-pdf red' );
        //$panel->addHeaderWidget( $dropdown );
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel);
        
        parent::add($container);
    }

    /**
     * Open an input dialog
     */
    public static function onReceber( $param )
    {
        TTransaction::open('DBUNIDADE');

        $form = new BootstrapFormBuilder('input_form_1');
        $form->setClientValidation(true);

        $items  = ContaReceberPg::where('recebimento_id', '=', $param['id'])->load();

        if ($items)
        {
            $ContaReceber = new ContaReceber($param['id']);
            if(empty($ContaReceber->dt_pagamento))
            {
                $id = new TEntry('id');
                $dt_pagamento = new TDate('dt_pagamento');
                $ValorRecebimento = new TNumeric('ValorRecebimento', 2, ',', '.');
                $valor = new TNumeric('valor', 2, ',', '.');
                $valor_total = new TNumeric('valor_total', 2, ',', '.');
                $desconto  = new TNumeric('desconto', 2, ',', '.');

                $id->setValue($param['id']);
                $dt_pagamento->setValue(date("d/m/Y"));
                $valor->setValue($ContaReceber->valor);
                $valor_total->setValue($ContaReceber->valor_total);
                $desconto->setValue($ContaReceber->desconto);

                $id->setSize('100%');
                $valor->setSize('100%');
                $desconto->setSize('100%');
                $dt_pagamento->setSize('100%');
                $valor_total->setSize('100%');
                $ValorRecebimento->setSize('100%');

                $form->appendPage('Informações');
        
                // add the fields inside the form
                $row = $form->addFields( [new TLabel('Nº'), $id], [new TLabel('Data'), $dt_pagamento] );
                $row->layout = ['col-sm-6', 'col-sm-6'];
                $form->addFields( [ new TFormSeparator('')] );
                $row = $form->addFields( [new TLabel('Total'), $valor_total] );
                $row->layout = ['col-sm-12 border-top-10'];
                $form->addFields( [ new TFormSeparator('')] );
                $row = $form->addFields( [new TLabel('Desconto'), $desconto], [new TLabel('A Receber'), $valor] );
                $row->layout = ['col-sm-6', 'col-sm-6'];
                $form->addFields( [ new TFormSeparator('')] );
                $row = $form->addFields( [new TLabel('Valor Pago'), $ValorRecebimento] );
                $row->layout = ['col-sm-12'];

                $id->setEditable(FALSE);
                $dt_pagamento->setEditable(FALSE);
                $valor_total->setEditable(FALSE);
                $valor->setEditable(FALSE);
                //$desconto->setEditable(FALSE);

                $dt_pagamento->setMask('dd/mm/yyyy');
                $dt_pagamento->setDatabaseMask('yyyy-mm-dd');

                $form->addAction('Liquidar', new TAction([__CLASS__, 'onLiquidarRecebimento']), 'fa:arrow-circle-down green');
                $form->addAction('Cancelar', new TAction([__CLASS__, 'onCancelarOperacao']), 'fa:times red');

                // show the input dialog
                new TInputDialog('Liquidar Recebimento', $form);

            }else
            {
                TToast::show('show', 'Não é possível liquidar um lançamento já baixada.', 'top right', 'far:check-circle' );
            }
        }else
        {
            TToast::show('warning', 'Não há recebimentos vinculados para o lançamento.', 'top center', 'far:check-circle' );
        }

        TTransaction::close();
    }
    
    /**
     * Show the input dialog data
     */
    public static function onLiquidarRecebimento( $param )
    {
        TTransaction::open('DBUNIDADE');

        if(!empty($param['ValorRecebimento']))
        {
            $ContaReceber = new ContaReceber($param['id']);
            $ValorRecebimento = str_replace(['.',','], ['','.'], $param['ValorRecebimento']);
            $ValorRecebimentoDesconto = str_replace(['.',','], ['','.'], $param['desconto']);

            $ValorConta = $ContaReceber->valor;
            $ValorContaTotal = $ContaReceber->valor_total;

            if(!empty($param['desconto']))
            {
                if($ValorRecebimentoDesconto == $ContaReceber->desconto)
                {
                    $NovoValorConta = $ValorContaTotal - $ValorRecebimentoDesconto;
                    if($ValorRecebimento == $NovoValorConta)
                    {
                        $ContaReceber->dt_pagamento = date("Y-m-d H:i:s");
                        $ContaReceber->store();
                        $pos_action = new TAction(['ContaReceberList', 'onReload'], ['register_state' => 'true']);
                        new TMessage('info', 'Liquidação <b>'.$param['id'].'</b> Ajustada!', $pos_action);
                    }
                    else
                    {
                        TToast::show('warning', 'PAGAMENTO: Não foi possível liquidar o lançamento <b>'.$param['id'].'</b>.', 'top right', 'fas:exclamation-circle' );
                    }
                }
                else
                {
                    $NovoValorConta = $ValorContaTotal - $ValorRecebimentoDesconto;
                    if($ValorRecebimento == $NovoValorConta)
                    {
                        $ContaReceber->dt_pagamento = date("Y-m-d H:i:s");
                        $ContaReceber->desconto = $ValorRecebimentoDesconto;
                        $ContaReceber->valor = $ValorRecebimento;
                        //$ContaReceber->obs = 'Desconto Alterado De: ('.$ContaReceber->desconto.') > ('.$ValorRecebimentoDesconto.')';
                        $ContaReceber->store();
                        $pos_action = new TAction(['ContaReceberList', 'onReload'], ['register_state' => 'true']);
                        new TMessage('info', 'Liquidação <b>'.$param['id'].'</b> Ajustada!', $pos_action);
                    }
                    else
                    {
                        TToast::show('warning', 'DESCONTO: Não foi possível liquidar o lançamento.', 'top right', 'fas:exclamation-circle' );
                    }
                }
            }
            else
            {
                if($ValorRecebimento == $ValorContaTotal)
                {
                    $ContaReceber->dt_pagamento = date("Y-m-d H:i:s");
                    $ContaReceber->store();
                    $pos_action = new TAction(['ContaReceberList', 'onReload'], ['register_state' => 'true']);
                    new TMessage('info', 'Liquidação <b>'.$param['id'].'</b> Ajustada!', $pos_action);
                }
                else
                {
                    TToast::show('warning', 'PAGAMENTO: Não foi possível liquidar o lançamento <b>'.$param['id'].'</b>.', 'top right', 'fas:exclamation-circle' );
                }
            }
        }
        else
        {
            TToast::show('warning', 'Valor para liquidação não pode ser vazio.', 'top right', 'fas:exclamation-circle' );
        }
        

        //$param->dt_pagamento = TDateTime::convertToMask($param['dt_pagamento'], 'yyyy-mm-dd', 'dd/mm/yyyy');
        //new TMessage('info', 'Confirm1 : ' . str_replace(',', '<br>', json_encode($param)));
        
        TTransaction::close();
    }
    
    /**
     * Show the input dialog data
     */
    public static function onCancelarOperacao( $param )
    {
        $pos_action = new TAction(['ContaReceberList', 'onReload']);
        new TMessage('error', 'Operação Cancelada!', $pos_action);
    }
    
    /**
     *
     */
    public static function onEstornarPg( $param )
    {
        TTransaction::open('DBUNIDADE');

        $Lancamentos  = ContaReceber::where('id', '=', $param['id'])->load();

        if ($Lancamentos)
        {
            $ContaReceber = new ContaReceber($param['id']);

            if($ContaReceber->ativo == 'Y' AND $ContaReceber->dt_pagamento !== NULL)
            {
                ContaReceberPg::where('recebimento_id', '=', $param['id'])->delete();
                $ContaReceber->dt_pagamento = NULL;
                $ContaReceber->store();
                $pos_action = new TAction(['ContaReceberList', 'onReload'], ['register_state' => 'true']);
                new TMessage('info', 'Lançamento Estornado!', $pos_action);
            }
            else
            {
                TToast::show('show', 'Não é possível estornar um lançamento cancelado.', 'top right', 'far:check-circle' );
            }

        }

        TTransaction::close();
    }

    /**
     *
     */
    public static function onEstornarCancelamento( $param )
    {
        TTransaction::open('DBUNIDADE');

        $Lancamentos  = ContaReceber::where('id', '=', $param['id'])->load();

        if ($Lancamentos)
        {
            $ContaReceber = new ContaReceber($param['id']);

            if($ContaReceber->ativo == 'N')
            {
                $ContaReceber->ativo = 'Y';
                $ContaReceber->store();
                $pos_action = new TAction(['ContaReceberList', 'onReload'], ['register_state' => 'true']);
                new TMessage('info', 'Cancelamento Estornado!', $pos_action);
            }
            else
            {
                TToast::show('show', 'Não é possível estornar um lançamento ativo.', 'top right', 'far:check-circle' );
            }
        }

        TTransaction::close();
    }
    
    /**
     *
     */
    public function onCancel($param)
    {
        try
        {
            TTransaction::open('DBUNIDADE');
            
            $ContaReceber = new ContaReceber($param['id']);

            if (!empty($ContaReceber->dt_pagamento))
            {
                throw new Exception('Conta a pagar já quitada');
            }
            else
            {
                $pega_caixa = new Caixa($ContaReceber->id_caixa);

                switch ($ContaReceber->ativo) {
                    case 'Y':
                        $ContaReceber->ativo = 'N';
                        if(!empty($ContaReceber->id_caixa))
                        {
                            if($pega_caixa->situacao == 'P')
                            {
                                $pega_caixa->situacao = 'A';
                                $pega_caixa->store();
                                $ContaReceber->id_caixa = NULL;
                            }
                            else
                            {
                                throw new Exception('Aviso: Não foi possível alterar a situação do Caixa.');
                            }
                        }
                        $ContaReceber->store();
                        new TMessage('info', 'Cancelamento Realizado.');
                        break;
                    default:
                    TToast::show('show', 'Não é possível estornar um lançamento inativo.', 'top right', 'far:check-circle' );
                }
            }
            
            TTransaction::close();
            
            $this->onReload($param);
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
    /**
     * method Delete()
     * Não é permitido exlucir registros importantes
     */
    public function Delete($param)
    {
        new TMessage('error', 'Operação não permitida');
    }

    /**
     * method onGenerateRecibo()
     * Executado a partir da chamada de um botão
     */
    function onGenerateRecibo()
    {
        try
        {

            TTransaction::open('DBUNIDADE');
            $contareceber = new ContaReceber($param['id']);

            $data = $this->form->getData();
            $this->form->validate();
            
            $designer = new TPDFDesigner;
            $designer->fromXml('app/reports/forms.pdf.xml');
            $designer->replace('{name}', $data->name );
            $designer->generate();
            
            $designer->gotoAnchorXY('anchor1');
            $designer->SetFontColorRGB('#FF0000');
            $designer->SetFont('Arial', 'B', 18);
            //$designer->Write(20, 'Dynamic text !');
            
            $file = 'app/output/pdf_shapes.pdf';            
            if (!file_exists($file) OR is_writable($file))
            {
                $designer->save($file);
                // parent::openFile($file);
                
                $window = TWindow::create(_t('Studio Pro - designed shapes'), 0.8, 0.8);
                $object = new TElement('object');
                $object->data  = $file;
                $object->type  = 'application/pdf';
                $object->style = "width: 100%; height:calc(100% - 10px)";
                $window->add($object);
                $window->show();
            }
            else
            {
                throw new Exception(_t('Permission denied') . ': ' . $file);
            }
            
            TTransaction::close();
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
        }
    }
}
