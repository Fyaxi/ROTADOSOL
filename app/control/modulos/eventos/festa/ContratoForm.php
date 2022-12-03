<?php
/*
 * @version    2.0
 * @package    Sistema Integrado
 * @subpackage Módulos
 * @author     Jairo Barreto
 * @copyright  Copyright (c) 2021 Nativus Tecnologia. (http://www.nativustecnologia.com.br)
 */
class ContratoForm extends TWindow
{
    protected $form; // form
    protected $fieldlist;
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct($param)
    {
        parent::__construct($param);
        parent::setSize(0.8, null);
        //parent::removePadding();
        parent::removeTitleBar();
        //parent::disableEscape();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Contrato');
        $this->form->setFormTitle('INFORMAÇÕES DO CONTRATO');
        $this->form->setClientValidation(true);
        
        // Remoção da funcionalidade devido ao novo modal da página.
        //$this->form->enableCSRFProtection(); // ATIVA PROTEÇÃO CONTA EXECUÇÃO DE JAVA SCRIPT MALICIOSO

        $this->form->appendPage('Informações Do Evento');

        // master fields
        $id = new TEntry('id');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('grupo_id', '=', 1), TExpression::OR_OPERATOR); 
        $cliente_id = new TDBUniqueSearch('cliente_id', 'DBUNIDADE', 'Pessoa', 'id', 'nome_fantasia', 'grupo_id ', $criteria);
        $cliente_id->setMinLength(0);
        $tipo_contrato_id = new TDBCombo('tipo_contrato_id', 'DBUNIDADE', 'TipoContrato', 'id', 'nome');

        //$criteria = new TCriteria;
        //$criteria->add(new TFilter('GrupoPagamento_id', '=', 1), TExpression::OR_OPERATOR); 
        //$colaboradores_id = new TDBMultiSearch('colaboradores_id', 'DBUNIDADE', 'Colaborador', 'id', 'nome_fantasia', 'GrupoPagamento_id', $criteria);

        //$agendado = new TRadioGroup('agendado');
        //$agendado->addItems( ['Y' => 'Sim', 'N' => 'Não'] );
        //$agendado->setLayout('horizontal');
        //$agendado->setUseButton();
        //$ativo = new TRadioGroup('ativo');
        ///$ativo->addItems( ['N' => 'Sim', 'Y' => 'Não'] );
        //$ativo->setLayout('horizontal');
        //$ativo->setUseButton();
        $dt_inicio = new TDateTime('dt_inicio');
        $dt_fim = new TDateTime('dt_fim');
        $obs = new TText('obs');

        $button = new TActionLink('', new TAction(['PessoaList', 'onClear']), 'green', null, null, 'fa:plus-circle');
        $button->class = 'btn btn-default inline-button';
        $button->title = 'Cadastrar Cliente';
        $cliente_id->after($button);

        $button = new TActionLink('', new TAction(['TipoContratoList', 'onClear']), 'green', null, null, 'fa:plus-circle');
        $button->class = 'btn btn-default inline-button';
        $button->title = 'Cadastrar Contrato';
        $tipo_contrato_id->after($button);

        $aniversariante = new TEntry('aniversariante');
        $convidados = new TEntry('convidados');
        $tema = new TEntry('tema');
        $valor = new TNumeric('valor', 2, ',', '.');

        $PagamentoEntrada = new TDBUniqueSearch('PagamentoEntrada', 'DBUNIDADE', 'FormaRecebimento', 'id', 'nome');
        $PagamentoEntrada->setMinLength(0);
        $ValorEntrada = new TNumeric('ValorEntrada', 2, ',', '.');
        $ValorDesconto = new TNumeric('ValorDesconto', 2, ',', '.');
        $ValorContrato = new TNumeric('ValorContrato', 2, ',', '.');
        $ValorTotal = new TNumeric('ValorTotal', 2, ',', '.');
        $IdVendedor = new TEntry('IdVendedor');
        $LoginVendedor = new TEntry('LoginVendedor');
        
        //$valor->setMask('99.999,99');

        $dt_inicio->setMask('dd/mm/yyyy hh:ii');
        $dt_fim->setMask('dd/mm/yyyy hh:ii');

        $dt_inicio->setDatabaseMask('yyyy-mm-dd hh:ii');
        $dt_fim->setDatabaseMask('yyyy-mm-dd hh:ii');

        $tipo_contrato_id->enableSearch();
        //$colaboradores_id->setMinLength(0);
        //$colaboradores_id->setSize('100%', 60);
        $convidados->setMask('9999');
        $dt_inicio->setSize('100%');
        $dt_fim->setSize('100%');
        $obs->setSize('100%', 70);
        //$agendado->setValue('N');
        //$ativo->setValue('Y');

        $PagamentoEntrada->setSize('100%');
        $ValorEntrada->setSize('100%');
        $ValorDesconto->setSize('100%');
        $IdVendedor->setSize('100%');
        $LoginVendedor->setSize('100%');
        $LoginVendedor->forceUpperCase();
        $LoginVendedor->setEditable(FALSE);
        $IdVendedor->setEditable(FALSE);
        $valor->setEditable(FALSE);
        //$ativo->setEditable(FALSE);

        $grupoUser = TSession::getValue('usergroupids');
        if( $grupoUser['0'] > 0 ) // Visualização Admin
        {
            $valor->setEditable(FALSE);
            $ValorContrato->setEditable(FALSE);
            $ValorTotal->setEditable(FALSE);
        }
        
        $cliente_id->addValidation('Cliente', new TRequiredValidator);
        $tipo_contrato_id->addValidation('Tipo Contrato', new TRequiredValidator);
        //$agendado->addValidation('Agendamento', new TRequiredValidator);
        $dt_inicio->addValidation('Data início', new TRequiredValidator);
        $dt_fim->addValidation('Data fim', new TRequiredValidator);

        // sizes
        $id->setSize('100%');
        $cliente_id->setSize('100%');
        $tipo_contrato_id->setSize('100%');
        //$agendado->setSize('100%');
        //$ativo->setSize('100%');
        $dt_inicio->setSize('100%');
        $dt_fim->setSize('100%');
        $id->setEditable(FALSE);
        $tema->forceUpperCase();
        $aniversariante->forceUpperCase();

        $aniversariante->setSize('100%');
        $convidados->setSize('100%');
        $tema->setSize('100%');
        $valor->setSize('100%');
        $convidados->setSize('100%');

        ///////////////////////////////
/*
        $hora_inicial->setChangeAction(new TAction(array($this, 'onChangeStartHour')));
        $hora_final->setChangeAction(new TAction(array($this, 'onChangeEndHour')));

        $dt_inicio->setExitAction(new TAction(array($this, 'onChangeStartDate')));
        $dt_fim->setExitAction(new TAction(array($this, 'onChangeEndDate')));
*/
        ///////////////////////////////
        
        
        // add form fields to the form
        //$this->form->addFields( [new TLabel('Id')], [$id], [new TLabel('Tipo Festa')], [$tipo_contrato_id], [new TLabel('Agendado')], [$agendado] );
        //$this->form->addFields( [new TLabel('Dt Inicio')], [$dt_inicio], [new TLabel('Dt Fim')], [$dt_fim] );
        //$this->form->addFields( [new TLabel('Cliente')], [$cliente_id], [new TLabel('Valor')], [$valor] );

        //$this->form->addFields( [new TLabel('Aniversariante')], [$aniversariante], [new TLabel('Tema')], [$tema], [new TLabel('Convidados')], [$convidados] );
        //$this->form->addFields( [new TLabel('Obs')], [$obs] );

        $row = $this->form->addFields( [ new TLabel('<br>Nº'),     $id ],
                                       [ new TLabel('<br>Cliente'),    $cliente_id ]);
        $row->layout = ['col-sm-1', 'col-sm-11'];
        
        $row = $this->form->addFields(  [ new TLabel('<br>Data Inicial'), $dt_inicio],
                                        [ new TLabel('<br>Data Final'), $dt_fim],
                                        [ new TLabel('<br>Tipo do Contrato'), $tipo_contrato_id] );
        $row->layout = ['col-sm-4', 'col-sm-4', 'col-sm-4'];

        $label2 = new TLabel('<br>Observação', '', 12, '');
        $label2->style='text-align:left;border-bottom:1px solid #c0c0c0;width:100%';
        $this->form->addContent( [$label2] );

        $row = $this->form->addFields( [ $obs ] );
        $row->layout = ['col-sm-12'];

        $subform = new BootstrapFormBuilder;
        $subform->setFieldSizes('100%');
        $subform->setProperty('style', 'border:none');

        $subform->appendPage( 'Detalhes do Evento' );
        $row = $subform->addFields(    [ new TLabel('<br>Aniversariante'),      $aniversariante ],
                                       [ new TLabel('<br>Tema'),       $tema ],
                                       [ new TLabel('<br>Convidados'), $convidados ] );
        $row->layout = ['col-sm-4', 'col-sm-4', 'col-sm-4'];

        $subform->appendPage( 'Financeiro' );
        $row = $subform->addFields( [ new TLabel('<br>Tipo da Entrada'), $PagamentoEntrada  ],
                                    [ new TLabel('<br>Valor Base'), $ValorContrato ] );
        $row->layout = ['col-sm-3', 'col-sm-2', 'col-sm-2'];
        $row = $subform->addFields( [ new TLabel('<br>Valor de Entrada'), $ValorEntrada  ],
                                    [ new TLabel('<br>Desconto'), $ValorDesconto  ],
                                    [ new TLabel('<br>Valor Total'), $ValorTotal ],
                                    [ new TLabel('<br>Valor a Receber <small>(com descontos)</small>'), $valor ] );
        $row->layout = ['col-sm-3', 'col-sm-3', 'col-sm-3', 'col-sm-3'];

        //$subform->appendPage( 'Colaboradores' );
        //$row = $subform->addFields( [ new TLabel('<br>Vínculados:'), $colaboradores_id  ] );
        //$row->layout = ['col-sm-12'];

        $this->form->addContent( [ $subform ] );
        
        
        // detail fields
        $this->fieldlist = new TFieldList;
        $this->fieldlist-> width = '100%';
        $this->fieldlist->enableSorting();

        $servico_id = new TDBUniqueSearch('list_servico_id[]', 'DBUNIDADE', 'Servico', 'id', 'nome', null, TCriteria::create( ['ativo' => 'Y'] ));
        $valor = new TNumeric('list_valor[]', 2, ',', '.');
        $quantidade = new TNumeric('list_quantidade[]', 2, ',', '.');
        
        $servico_id->setChangeAction(new TAction(array($this, 'onChangeServico')));
        
        $servico_id->setSize('100%');
        $servico_id->setMinLength(0);
        $valor->setSize('100%');
        $quantidade->setSize('100%');

        $this->fieldlist->addField( '<b>Itens</b>', $servico_id, ['width' => '40%']);
        $this->fieldlist->addField( '<b>Valor</b>', $valor, ['width' => '30%']);
        $this->fieldlist->addField( '<b>Quantidade</b>', $quantidade, ['width' => '30%']);

        $this->form->addField($servico_id);
        $this->form->addField($valor);
        $this->form->addField($quantidade);
        
        $detail_wrapper = new TElement('div');
        $detail_wrapper->add($this->fieldlist);
        $detail_wrapper->style = 'overflow-x:auto';
        
        $this->form->addContent( [ TElement::tag('h5', 'Planilha De Itens', [ 'style'=>'background: whitesmoke; padding: 5px; border-radius: 5px; margin-top: 5px'] ) ] );
        $this->form->addContent( [ $detail_wrapper ] );
        
        // create actions
        $this->form->addAction( _t('Save'),  new TAction( [$this, 'onSave'] ),  'fa:save green' );
        $this->form->addAction( _t('Clear'), new TAction( [$this, 'onClear'] ), 'fa:eraser red' );
        $this->form->addHeaderActionLink( _t('Close'), new TAction([$this, 'onClose']), 'fa:times red');
        
        // create the page container
        $container = new TVBox;
        $container->style = 'width: 100%';
        //$container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        parent::add($container);
    }

    /**
     * Executed whenever the user clicks at the edit button da datagrid
     */
    function onEdit($param)
    {
        try
        {
            TTransaction::open('DBUNIDADE');
            
            if (isset($param['key']))
            {
                $key = $param['key'];
                
                $object = new Contrato($key);
                $object->colaboradores_id = ContratoPagamento::where('contrato_id', '=', $object->id)->getIndexedArray('pessoa_id');
                $this->form->setData($object);

                $items  = ContratoItem::where('contrato_id', '=', $key)->load();
                
                if ($items)
                {
                    $this->fieldlist->addHeader();
                    
                    foreach($items  as $item )
                    {
                        $detail = new stdClass;
                        $detail->list_servico_id = $item->servico_id;
                        $detail->list_valor = $item->valor;
                        $detail->list_quantidade = $item->quantidade;
                        $this->fieldlist->addDetail($detail);
                    }
                    
                    $this->fieldlist->addCloneAction();
                }
                else
                {
                    $this->onClear($param);
                }

                $grupoUser = TSession::getValue('usergroupids');
                if( $grupoUser['0'] > 0 ) // Visualização Admin
                {
                    TScript::create(' $("select[name=\'tipo_contrato_id\'").prop("disabled", true); ');
                    TScript::create(' $("select[name=\'cliente_id\'").prop("disabled", true); ');
                    TScript::create(' $("select[name=\'PagamentoEntrada\'").prop("disabled", true); ');
                    TEntry::disableField('form_Contrato', 'ValorDesconto'); 
                    TEntry::disableField('form_Contrato', 'ValorEntrada'); 
                }
                /*
                $data = new stdClass;
                $data->id             = $object->id;
                $data->cor            = $object->cor;
                $data->titulo         = $object->titulo;
                $data->descricao      = $object->descricao;
                $data->dt_inicio      = substr($object->inicio,0,10);
                $data->hora_inicial   = substr($object->inicio,11,2);
                $data->minuto_inicial = substr($object->inicio,14,2);
                $data->dt_fim         = substr($object->fim,0,10);
                $data->hora_final     = substr($object->fim,11,2);
                $data->minuto_final   = substr($object->fim,14,2);
                $data->view = $param['view'];
                
                // fill the form with the active record data
                $this->form->setData($data);
                */
                
                TTransaction::close(); // close transaction
            }
            else
            {
                $this->onClear($param);
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    /**
     * Clear form
     */
    public function onClear($param)
    {
        $this->fieldlist->addHeader();
        $this->fieldlist->addDetail( new stdClass );
        $this->fieldlist->addCloneAction();
    }
    
    /**
     * Save the Contrato and the ContratoItem's
     */
    public static function onSave($param)
    {
        try
        {
            TTransaction::open('DBUNIDADE');

            $data = (object) $param;

            //echo '<pre>';
            //print_r($param);
            //echo '</pre>';

            $id = (int) $param['id'];
            $master = new Contrato;
            $master->fromArray($param);

            $master->dt_inicio      = TDateTime::convertToMask($param['dt_inicio'],  'dd/mm/yyyy hh:ii', 'yyyy-mm-dd hh:ii');
            $master->dt_fim         = TDateTime::convertToMask($param['dt_fim'],  'dd/mm/yyyy hh:ii', 'yyyy-mm-dd hh:ii');

            $master->mes            = TDateTime::convertToMask($param['dt_inicio'], 'dd/mm/yyyy', 'mm');
            $master->ano            = TDateTime::convertToMask($param['dt_inicio'], 'dd/mm/yyyy', 'yyyy');

            if (empty($param['id']))
            {
                $master->tipo_contrato_id   = $param['tipo_contrato_id'];
                $master->ValorEntrada       = $param['ValorEntrada'];
                $master->PagamentoEntrada   = $param['PagamentoEntrada'];
                $master->ValorDesconto      = $param['ValorDesconto'];
                $master->ValorContrato      = $param['ValorContrato'];

                $master->valor              = 0;
                $master->ValorTotal         = 0;

                $master->IdVendedor         = TSession::getValue('userid');
                $master->VendedorLogin      = TSession::getValue('login');
            }

            $master->store();

            // delete details
            ContratoItem::where('contrato_id', '=', $master->id)->delete();
            
            if( !empty($param['list_servico_id']) AND is_array($param['list_servico_id']) )
            {
                foreach( $param['list_servico_id'] as $row => $servico_id)
                {
                    if (!empty($servico_id))
                    {
                        $detail = new ContratoItem;
                        $detail->contrato_id = $master->id;
                        $detail->servico_id = $param['list_servico_id'][$row];
                        $detail->valor =      (float) str_replace(['.',','], ['','.'], $param['list_valor'][$row]);
                        $detail->quantidade = (float) str_replace(['.',','], ['','.'], $param['list_quantidade'][$row]);
                        $detail->total = round($detail->valor * $detail->quantidade,2);
                        $detail->store();
                    }
                }
            }

            // delete details
            /*ContratoPagamento::where('contrato_id', '=', $master->id)->where('status', '=', 'N')->delete();

            if( !empty($param['colaboradores_id']) AND is_array($param['colaboradores_id']) )
            {
                foreach( $param['colaboradores_id'] as $row => $ColaboradorID)
                {
                    $pp = new ContratoPagamento;
                    $pp->pessoa_id  = $ColaboradorID;
                    $pp->contrato_id = $master->id;

                    $Colaborador = new Colaborador($ColaboradorID);
                    $ColaboradorGrupo = $Colaborador->GrupoPagamento_id;

                    $GrupoPagamento = new GrupoPagamento($ColaboradorGrupo);
                    $ColaboradorPagamento = $GrupoPagamento->ValorPagamento;
                    
                    $ColaboradorContaPagamento = $GrupoPagamento->ContaPagamento;

                    $pp->valor = $ColaboradorPagamento;
                    $pp->ContaPagamento = $ColaboradorContaPagamento;
                    
                    $pp->store();
                    
                    
                }
            }
            else
            {
                TToast::show('show', 'Não existe colaboradores vinculados!', 'top center', 'fas:university' );
            }
            */
            ///////////////////////////////////////////////////////////////
            if(empty($param['ValorDesconto']) OR $param['ValorDesconto'] == 0)
            {
                if (empty($param['id']))
                {
                    $tipo_contrato = new TipoContrato($param['tipo_contrato_id']);
                    $valor_contrato = $tipo_contrato->ValorContrato;
                }
                else
                {
                    $valor_contrato = (float) str_replace(['.',','], ['','.'], $param['ValorContrato']);
                }

                $valor_entrada = (float) str_replace(['.',','], ['','.'], $param['ValorEntrada']);

                $ValorTotalItens = ContratoItem::where('contrato_id','=',$param['id'])->sumBy('total');
                $valor_itens =  $ValorTotalItens;

                if($valor_itens > $valor_contrato)
                {
                    $valor_total = $valor_itens;
                    TToast::show('show', 'Valor do contrato e seus itens foram alterados.', 'top center', 'fas:university' );
                }
                else
                {
                    $valor_total = $valor_contrato;
                    TToast::show('show', 'Valor do contrato inserido.', 'top center', 'fas:university' );
                }

                $master->valor = $valor_total; // valor final a receber
                $master->ValorEntrada = $valor_entrada; // valor pago como entrada
                $master->ValorContrato = $valor_contrato; // valor definido no tipo do contrato
                $master->ValorTotal = $valor_total; // valor total do contrato. (não alterado)

                if($master->ValorEntrada > $master->ValorTotal)
                {
                    TToast::show('show', 'Valor de entrada iniválido para a operação.', 'top center', 'fas:window-close' );
                }
                else
                {
                    $master->store();
                }
            }
            else
            {
                if (empty($param['id']))
                {
                    $tipo_contrato = new TipoContrato($param['tipo_contrato_id']);
                    $valor_contrato = $tipo_contrato->ValorContrato;
                }else
                {
                    $valor_contrato = (float) str_replace(['.',','], ['','.'], $param['ValorContrato']);
                }

                $valor_desconto = (float) str_replace(['.',','], ['','.'], $param['ValorDesconto']);
                $valor_entrada = (float) str_replace(['.',','], ['','.'], $param['ValorEntrada']);

                $ValorTotalItens = ContratoItem::where('contrato_id','=',$param['id'])->sumBy('total');
                $valor_itens =  $ValorTotalItens;

                if($valor_itens > $valor_contrato)
                {
                    $valor_total = $valor_itens;
                    $valor_festa = $valor_total - $valor_desconto;
                    TToast::show('show', 'EVENTO: Valor do contrato e seus itens foram inseridos.', 'top center', 'fas:window-close' );
                }
                else
                {
                    $valor_total = $valor_contrato;
                    $valor_festa = $valor_total - $valor_desconto;
                    TToast::show('show', 'EVENTO: Valor do contrato inserido.', 'top center' );
                }

                $master->valor = $valor_festa; // valor final a receber
                $master->ValorDesconto = $valor_desconto; // valor do desconto
                $master->ValorEntrada = $valor_entrada; // valor pago como entrada
                $master->ValorContrato = $valor_contrato; // valor definido no tipo do contrato
                $master->ValorTotal = $valor_total; // valor total do contrato. (não alterado)

                if($master->ValorEntrada > $master->ValorTotal)
                {
                    TToast::show('show', 'Valor de entrada iniválido para a operação.', 'top center', 'fas:window-close' );
                }
                else
                {
                    $master->store();
                }
            }
            ///////////////////////////////////////////////////////////////
            
            ///////////////////////////////////////////////////////////////
            ///////////////////////////////////////////////////////////////

            ///////////////////////////////////////////////////////////////
            

            $data = new stdClass;
            $data->id = $master->id;
            TForm::sendData('form_Contrato', $data);

            TScript::create(' $("select[name=\'tipo_contrato_id\'").prop("disabled", false); ');
            TScript::create(' $("select[name=\'cliente_id\'").prop("disabled", false); ');
            TScript::create(' $("select[name=\'PagamentoEntrada\'").prop("disabled", false); ');
            
            $pos_action = new TAction(['ContratoList', 'onReload']);
            new TMessage('info', 'Evento Salvo!', $pos_action);                
            ///////////////////////////////////////////////////////////////
            ///////////////////////////////////////////////////////////////

            TTransaction::close(); // close the transaction
            
            
        }
        catch (Exception $e) // in case of exception
        {
            //new TMessage('error', $master);
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    
    /**
     * Change servico
     */
    public static function onChangeServico($param)
    {
        $input_id = $param['_field_id'];
        $servico_id = $param['_field_value'];
        $input_pieces = explode('_', $input_id);
        $unique_id = end($input_pieces);
        
        if ($servico_id)
        {
            $response = new stdClass;
            
            try
            {
                TTransaction::open('DBUNIDADE');
                
                $servico = Servico::find($servico_id);
                $response->{'list_quantidade_'.$unique_id} = '1,00';
                $response->{'list_valor_'.$unique_id} = number_format($servico->valor,2,',', '.');
                
                TForm::sendData('form_Contrato', $response);
                TTransaction::close();
            }
            catch (Exception $e)
            {
                TTransaction::rollback();
            }
        }
    }
    
    /**
     * Close
     */
    public static function onClose($param)
    {
        TScript::create(' $("select[name=\'tipo_contrato_id\'").prop("disabled", false); ');
        TScript::create(' $("select[name=\'cliente_id\'").prop("disabled", false); ');
        TScript::create(' $("select[name=\'PagamentoEntrada\'").prop("disabled", false); ');
        parent::closeWindow();
    }
}
