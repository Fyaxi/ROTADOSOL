<?php
/**
 * ContratoList 'DBUNIDADE'
 *
 * @version    2.0
 * @package    Sistema Integrado
 * @subpackage Módulos
 * @author     Jairo Barreto
 * @copyright  Copyright (c) 2021 Nativus Tecnologia. (http://www.nativustecnologia.com.br)
 * 
 */
class ContratoList extends TPage
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
        $this->setActiveRecord('Contrato');   // defines the active record
        $this->setDefaultOrder('id', 'desc');         // defines the default order
        $this->setLimit(50);
        // $this->setCriteria($criteria) // define a standard filter

        $this->addFilterField('id', '=', 'id'); // filterField, operator, formField
        $this->addFilterField('cliente_id', '=', 'cliente_id'); // filterField, operator, formField
        $this->addFilterField('tipo_contrato_id', '=', 'tipo_contrato_id'); // filterField, operator, formField
        $this->addFilterField('ativo', 'like', 'ativo'); // filterField, operator, formField
        $this->addFilterField('agendado', 'like', 'agendado'); // filterField, operator, formField
        $this->addFilterField('mes', '=', 'mes'); // filterField, operator, formField
        $this->addFilterField('ano', '=', 'ano'); // filterField, operator, formField
        $this->addFilterField('dt_inicio', '=', 'dt_inicio'); // filterField, operator, formField
        $this->setOrderCommand('cliente->nome_fantasia', '(SELECT nome_fantasia FROM pessoa WHERE pessoa.id=contrato.cliente_id)');
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Contrato');
        $this->form->setFormTitle('Contratos');
        $this->form->enableCSRFProtection(); // ATIVA PROTEÇÃO CONTA EXECUÇÃO DE JAVA SCRIPT MALICIOSO
        

        // create the form fields
        $id = new TEntry('id');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('grupo_id', '=', 1), TExpression::OR_OPERATOR); 
        $cliente_id = new TDBUniqueSearch('cliente_id', 'DBUNIDADE', 'Pessoa', 'id', 'nome_fantasia', 'grupo_id ', $criteria);
        $tipo_contrato_id = new TDBUniqueSearch('tipo_contrato_id', 'DBUNIDADE', 'TipoContrato', 'id', 'nome');
        $ativo = new TRadioGroup('ativo');
        $aniversariante = new TEntry('aniversariante');
        $dt_inicio = new TDate('dt_inicio');

        $mes = new TRadioGroup('mes');
        $ano = new TRadioGroup('ano');
        $current = (int) date('Y');
        $mes->addItems( ['01' => 'Jan', '02' => 'Fev', '03' => 'Mar', '04' => 'Abr', '05' => 'Mai', '06' => 'Jun', '07' => 'Jul', '08' => 'Ago', '09' => 'Set', '10' => 'Out', '11' => 'Nov', '12' => 'Dez', '' => 'Todos'] );
        $ano->addItems( [ ($current -5) => ($current -5), ($current -4) => ($current -4), ($current -3) => ($current -3), ($current -2) => ($current -2), ($current -1) => ($current -1), $current => $current, '' => 'Todos' ] );
        
        $mes->setLayout('horizontal');
        $ano->setLayout('horizontal');

        $cliente_id->setMinLength(0);
        $tipo_contrato_id->setMinLength(0);
        $ativo->addItems( ['Y' => 'Sim', 'N' => 'Não', '' => 'Ambos'] );
        $ativo->setLayout('horizontal');
        
        // add the fields
        $this->form->addFields( [ new TLabel('N°') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Cliente') ], [ $cliente_id ], [ new TLabel('Aniversariante') ], [ $aniversariante ] );
        $this->form->addFields( [ new TLabel('Tipo Contrato') ], [ $tipo_contrato_id ], [ new TLabel('Data') ], [ $dt_inicio ]  );
        $this->form->addFields( [ new TLabel('Mes') ], [ $mes ] );
        $this->form->addFields( [ new TLabel('Ano') ], [ $ano ] );
        $this->form->addFields( [ new TLabel('Cancelados?') ], [ $ativo ] );


        // set sizes
        $id->setSize('15%');
        $cliente_id->setSize('100%');
        $aniversariante->setSize('100%');
        $tipo_contrato_id->setSize('100%');
        $ativo->setSize('100%');
        $dt_inicio->setSize('100%');

        //$this->form->addExpandButton('Expandir' , 'fa:search', true);

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink('<b>Novo Contrato</b>', new TAction(['ContratoForm', 'onEdit'], ['register_state' => 'false']), 'fa:plus green');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        //$this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'CONTRATO', 'center',  '10%');
        $column_cliente_id = new TDataGridColumn('cliente->nome_fantasia', 'CLIENTE', 'left');
        $column_aniversariante = new TDataGridColumn('aniversariante', 'ANIVERSARIANTE', 'CENTER');
        $column_tipo_contrato_id = new TDataGridColumn('tipo_contrato->nome', 'TIPO CONTRATO', 'CENTER');
        $column_dt_inicio = new TDataGridColumn('dt_inicio', 'DATA', 'CENTER');
        //$column_dt_fim = new TDataGridColumn('dt_fim', 'Dt Fim', 'left');
        $column_ativo = new TDataGridColumn('agendado', 'RESERVADO', 'CENTER');

        $column_id->setTransformer( function ($value, $object, $row) {
            if ($object->ativo == 'N')
            {
                $row->style= 'color: silver';
            }
            
            return $value;
        });

        $column_dt_inicio->setTransformer( function ($value, $object, $row) {
            if ($object->ativo == 'N')
            {
                return '<u>Cancelado</u>';
            }
            else
            {
                return TDate::convertToMask($value, 'yyyy-mm-dd', 'dd/mm/yyyy');
            }
            
        });

        $column_ativo->setTransformer( function ($value) {
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
        
        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_cliente_id);
        $this->datagrid->addColumn($column_aniversariante);
        $this->datagrid->addColumn($column_tipo_contrato_id);
        $this->datagrid->addColumn($column_dt_inicio);
        //$this->datagrid->addColumn($column_dt_fim);
        $this->datagrid->addColumn($column_ativo);

        // creates the datagrid column actions
        $column_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
        $column_cliente_id->setAction(new TAction([$this, 'onReload']), ['order' => 'cliente->nome_fantasia']);
        $column_dt_inicio->setAction(new TAction([$this, 'onReload']), ['order' => 'dt_inicio']);
        
        $column_tipo_contrato_id->enableAutoHide(500);
        $column_dt_inicio->enableAutoHide(500);
        //$column_dt_fim->enableAutoHide(500);
        $column_ativo->enableAutoHide(500);
        
        $action1 = new TDataGridAction(['ContratoView', 'onEdit'], ['id'=>'{id}']);
        $action2 = new TDataGridAction([$this, 'onAgendar'], ['id'=>'{id}']);
        $action3 = new TDataGridAction([$this, 'onGerarRecibo'], ['id'=>'{id}']);
        $action4 = new TDataGridAction([$this, 'onDeletarEvento'], ['id'=>'{id}']);
        $action5 = new TDataGridAction(['ContratoForm', 'onEdit'], ['id'=>'{id}']);
        $action6 = new TDataGridAction([$this, 'onContratoPagamento'], ['id'=>'{id}']);

        //$action1->setDisplayCondition( function ($object) {
        //    return $object->ativo !== 'N';
        //});
        
        $action2->setDisplayCondition( function ($object) {
            return $object->ativo !== 'N';
        });

        $action3->setDisplayCondition( function ($object) {
            return $object->ativo !== 'N';
        });

        $action5->setDisplayCondition( function ($object) {
            return $object->ativo !== 'N';
        });

        $action6->setDisplayCondition( function ($object) {
            return $object->ativo !== 'N';
        });
        
        //$this->datagrid->addAction($action1, 'Editar Evento',   'far:edit blue');
        //$this->datagrid->addAction($action2, 'Marcar/Desmarcar Evento', 'fa:calendar orange');
        //$this->datagrid->addAction($action3, 'Gerar Recibo',   'fa:file-invoice-dollar green');
        //$this->datagrid->addAction($action4, 'Cancelar Evento', 'far:trash-alt red');

        $action_group = new TDataGridActionGroup('Ações', 'fa:bars');

        $action1->setLabel('Visualizar Contrato');
        $action1->setImage('fa:eye #7C93CF');
        
        $action2->setLabel('Marcar/Desmarcar Evento');
        $action2->setImage('fa:calendar orange');

        $action3->setLabel('Gerar Recibo');
        $action3->setImage('fa:file-invoice-dollar green');

        $action4->setLabel('Cancelar Contrato/Evento');
        $action4->setImage('fa:ban red');

        $action5->setLabel('Editar Contrato');
        $action5->setImage('far:edit #7C93CF');

        $action6->setLabel('Colaboradores');
        $action6->setImage('far:edit #7C93CF');
        
        $action_group->addHeader('Rotina');
        $action_group->addAction($action1);
        $action_group->addAction($action5);
        $action_group->addAction($action2);
        $action_group->addSeparator();
        $action_group->addHeader('Relatórios');
        $action_group->addAction($action3);
        $action_group->addHeader('Financeiro');
        $action_group->addAction($action6);
        $action_group->addSeparator();
        $action_group->addHeader('Cancelamento');
        $action_group->addAction($action4);
        
        // add the actions to the datagrid
        $this->datagrid->addActionGroup($action_group);
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        
        $panel = new TPanelGroup('<small>relação de contratos</small>', 'white');
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

    public function onAbrirTela($param)
    {

    }
    
    /**
     * Turn on/off an user
     */
    public function onDeletarEvento($param)
    {
        try
        {
            TTransaction::open('DBUNIDADE');
            $contrato = Contrato::find($param['id']);
            
            if ($contrato instanceof Contrato)
            {
                $contrato->ativo = $contrato->ativo == 'Y' ? 'N' : 'Y';
                $contrato->agendado = $contrato->agendado == 'N';
                $contrato->store();
                Evento::where('festa_id', '=', $contrato->id)->delete();
                $pos_action = new TAction(['ContratoList', 'onReload']);
                new TMessage('info', 'Evento Alterado Com Sucesso!', $pos_action); 
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

    /**
     * Agendar Decoração
     */
    public function onAgendar($param)
    {
        try
        {
            TTransaction::open('DBUNIDADE');
            $contrato = Contrato::find($param['id']);

            if($contrato->ativo == 'Y')
            {
                if ($contrato instanceof Contrato)
                {
                    $contrato->agendado = $contrato->agendado == 'Y' ? 'N' : 'Y';
                    $contrato->store();
                }

                if($contrato->agendado == 'Y')
                {
                    $evento_criado  = Evento::where('festa_id', '=', $contrato->id)->load();
                    //print_r($evento_criado);

                    if(!$evento_criado)
                    {
                        $tipo_contrato = new TipoContrato($contrato->tipo_contrato_id);

                        $evento = new Evento;
                        $evento->cor       = $tipo_contrato->cor;
                        $evento->titulo    = 'Festa '.$contrato->aniversariante.' | '.$contrato->convidados.' Conv.';
                        $evento->descricao = $contrato->obs;
                        $evento->inicio    = $contrato->dt_inicio;
                        $evento->fim       = $contrato->dt_fim;
                        $evento->festa_id  = $contrato->id;
                        $evento->system_user_id = TSession::getValue('userid');
                        $evento->store();
                        //TToast::show('success', 'Contrato inserido na agenda de eventos!', 'top center', 'fas:window-close' );
                        $pos_action = new TAction(['ContratoList', 'onReload']);
                        new TMessage('info', 'Contrato inserido na agenda de eventos!', $pos_action); 
                    }
                }
                else
                {
                    Evento::where('festa_id', '=', $contrato->id)->delete();
                    //TToast::show('info', 'Evento removido da agenda de eventos!', 'top center', 'fas:window-close' );
                    $pos_action = new TAction(['ContratoList', 'onReload']);
                    new TMessage('info', 'Evento removido da agenda de eventos!', $pos_action); 
                }        
            }
            else
            {
                //TToast::show('error', 'Não é possível agendadar um evento cancelado!', 'top center', 'fas:window-close' );
                $pos_action = new TAction(['ContratoList', 'onReload']);
                new TMessage('warning', 'Não é possível agendadar um evento cancelado!', $pos_action); 
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
     * Open an input dialog
     */
    public static function onContratoPagamento($param)
    {
        TTransaction::open('DBUNIDADE');

        $form = new BootstrapFormBuilder('input_form_1');

        $Contrato = new Contrato($param['id']);
        $id = new TEntry('id');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('grupo_id', '=', 2), TExpression::OR_OPERATOR); 
        $colaboradores_id = new TDBMultiSearch('colaboradores_id', 'DBUNIDADE', 'Colaborador', 'id', 'nome_fantasia', 'grupo_id', $criteria);
        $id->setValue($param['id']);

        $ContratosPagamentos = ContratoPagamento::where('contrato_id', '=', $Contrato->id)->getIndexedArray('pessoa_id');
        $colaboradores_id->setValue($ContratosPagamentos);
        
        $id->setSize('30%');
        $colaboradores_id->setSize('100%');
        $colaboradores_id->setMinLength(0);
        
        $form->addFields( [new TLabel('Contrato')], [$id]);
        $form->addFields( [new TLabel('Vínculados:')], [$colaboradores_id]);
        
        $id->setEditable(FALSE);
        
        $form->addAction('Vincular', new TAction([__CLASS__, 'onQuestionario']), 'fa:arrow-circle-down green');
        $form->addAction('Cancelar Operação', new TAction([__CLASS__, 'onCancelarOperacao']), 'fa:times red');
        // show the input dialog
        new TInputDialog('Colaboradores Vinculados', $form); 
 

        TTransaction::close();
    }

    /**
     * Show the input dialog data
     */
    public static function onQuestionario($param)
    {
        $action1 = new TAction(array('ContratoList', 'onGravarVinculoColaborador'));
        $action2 = new TAction(array('ContratoList', 'onCancelarOperacao'));

        $action1->setParameter('param', $param);
        $action2->setParameter('parameter', 2);
        
        new TQuestion('Verifique se há pagamento já gerados para os colaboradores.<br><br>Deseja Continuar?', $action1, $action2);
    }

    public static function onGravarVinculoColaborador($param)
    {
        TTransaction::open('DBUNIDADE');

        ContratoPagamento::where('contrato_id', '=', $param['param']['id'])->where('status', '=', 'N')->delete();
        
        /*
        $PagamentoComVinculo = ContratoPagamento::where('contrato_id', '=', $param['param']['id'])->where('status', '=', 'G');
        echo '<pre>';
        print_r($PagamentoComVinculo);
        echo '</pre>';

        if( !empty($param['param']['colaboradores_id']) )
        {
            TToast::show('info', 'Esses colaboradores não foram alterados.', 'top center', 'fas:university' );
            foreach( $param['param']['colaboradores_id'] as $row => $ColaboradorID)
            {
                $Colaborador = new Colaborador($ColaboradorID);
                TToast::show('info', $Colaborador->nome_fantasia, 'top center', 'fas:university' );
            }
            TToast::show('info', 'Há colaboradores com vinculos de pagamentos já gerados para esse evento.', 'top center', 'fas:university' );
            TToast::show('success', 'Alteração realizada!', 'top center', 'fas:university' );
        }
        */
        if( !empty($param['param']['colaboradores_id']) AND is_array($param['param']['colaboradores_id']) )
        {
            foreach( $param['param']['colaboradores_id'] as $row => $ColaboradorID)
            {
                $pp = new ContratoPagamento;
                $pp->pessoa_id  = $ColaboradorID;
                $pp->contrato_id = $param['param']['id'];
                $Colaborador = new Colaborador($ColaboradorID);
                $ColaboradorGrupo = $Colaborador->GrupoPagamento_id;
                $GrupoPagamento = new GrupoPagamento($ColaboradorGrupo);
                $ColaboradorPagamento = $GrupoPagamento->ValorPagamento;
                
                $ColaboradorContaPagamento = $GrupoPagamento->ContaPagamento;
                $pp->valor = $ColaboradorPagamento;
                $pp->ContaPagamento = $ColaboradorContaPagamento;
                
                $pp->store();
                $pos_action = new TAction(['ContratoList', 'onReload'], ['register_state' => 'true']);
                new TMessage('info', 'Alteração realizada!', $pos_action);
            }
        }
        else
        {
            TToast::show('show', 'Todos os colaboradores foram removidos!', 'top center', 'fas:university' );
            $pos_action = new TAction(['ContratoList', 'onReload'], ['register_state' => 'true']);
            new TMessage('info', 'Alteração realizada!', $pos_action);
        }
        TTransaction::close();
    }
    
    /**
     * Show the input dialog data
     */
    public static function onCancelarOperacao( $param )
    {
        $pos_action = new TAction(['ContratoList', 'onReload']);
        new TMessage('error', 'Operação Cancelada!', $pos_action);
    }

    public function onGerarRecibo($param)
    {
        try
        {
            $this->html = new THtmlRenderer('app/resources/relat/ReciboEntradaFesta.html');
        
            TTransaction::open('DBUNIDADE');

            $relat_itens    = array();
            $contrato_item  = ViewContratoItem::where('contrato_id', '=', $param['id'])->load(); 

            // Informações sobre os itens do evento
            if ($contrato_item)
            { 
                foreach($contrato_item as $item)
                {
                    array_push($relat_itens, array( 
                        "id"        => $item->servico_id, 
                        "descricao" => $item->contrato_item_nome, 
                        "preco"     => $item->contrato_item_valor, 
                        "qtde"      => $item->contrato_item_qtd
                    ));
                }    

                //echo '<pre>';
                //print_r($contrato_item);
                //echo '</pre>';        
                
                if(!empty($relat_itens))
                {
                    $ValorTotalItens  = ViewContratoItem::where('contrato_id', '=', $param['id'])->sumBy('contrato_item_total');

                    if(!empty($ValorTotalItens))
                    {
                        // BUSCA O TIPO CONTRATO
                        $contrato = new Contrato($param['id']);
                        $ContratoValorEntrada = $contrato->ValorEntrada;

                        if($ValorTotalItens > $ContratoValorEntrada)
                        {
                            $contrato       = Contrato::find($param['id']);
                            $pessoa         = Pessoa::find($contrato->cliente_id);

                            // Configurações GLOBAIS
                            $relatorio = new stdClass;
                            $relatorio->dt_atual = date('Y-m-d');

                            // Informações sobre o evento
                            $evento                 = new stdClass;
                            $evento->id             = $contrato->id;
                            $evento->data           = $contrato->dt_inicio;
                            $evento->valor_entrada  = $contrato->ValorEntrada;
                            $evento->valor_base     = $contrato->ValorContrato;

                            // Informações sobre o cliente
                            $cliente                = new stdClass;
                            $cliente->nome          = $pessoa->nome_fantasia;
                            $cliente->cpf           = $pessoa->codigo_nacional;    

                            // Substituição das variáveis no html relatório
                            $replaces = []; 
                            $replaces['relatorio']  = $relatorio;
                            $replaces['evento']     = $evento;
                            $replaces['cliente']    = $cliente;
                            $replaces['items']      = $relat_itens;

                            // Execução do replace
                            // Replace pode ser utilizado para ativar sessões dentro do relatório
                            $this->html->enableSection('main', $replaces);

                            // string with HTML contents
                            $html = clone $this->html;
                            $contents = file_get_contents('app/resources/styles-print.html') . $html->getContents();

                            $options = new \Dompdf\Options();
                            $options->setChroot(getcwd());

                            // converts the HTML template into PDF
                            $dompdf = new \Dompdf\Dompdf($options);
                            $dompdf->loadHtml($contents);
                            $dompdf->setPaper('A4', 'portrait');
                            $dompdf->render();
                            
                            $file = 'app/output/ReciboEntradaFesta_'.$contrato->id.'.pdf';
                            
                            // write and open file
                            file_put_contents($file, $dompdf->output());
                            
                            $window = TWindow::create('Recibo', 0.8, 0.8);
                            $object = new TElement('object');
                            $object->data  = $file;
                            $object->type  = 'application/pdf';
                            $object->style = "width: 100%; height:calc(100% - 10px)";
                            $window->add($object);
                            $window->show();
                        }
                        else
                        {
                            TToast::show('error', 'Valores dos itens do contrato incompatível com o valor de entrada.', 'top right', 'far:check-circle' );
                        }
                    }
                    else
                    {
                        TToast::show('error', 'Não há itens lançados no contato.', 'top right', 'far:check-circle' );
                    }
                }
                else
                {
                    TToast::show('error', 'Itens não listados no contrato!', 'top right', 'far:check-circle' );
                }
            }
            else
            {
                TToast::show('error', 'Itens não encontrados no contrato!', 'top right', 'far:check-circle' );
            }        

            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
}
