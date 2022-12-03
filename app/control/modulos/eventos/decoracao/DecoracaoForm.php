<?php
/**
 * DecoracaoDashboard TSession::getValue('unit_database')
 *
 * @version    2.0
 * @package    Sistema Integrado
 * @subpackage Módulos
 * @author     Jairo Barreto
 * @copyright  Copyright (c) 2021 Nativus Tecnologia. (http://www.nativustecnologia.com.br)
 * 
 */
class DecoracaoForm extends TWindow
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
        parent::removePadding();
        parent::removeTitleBar();
        parent::disableEscape();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Decoracao');
        $this->form->setFormTitle('Agendamento Decoracao');
        $this->form->setClientValidation(true);
        
        // Remoção da funcionalidade devido ao novo modal da página.
        //$this->form->enableCSRFProtection(); // ATIVA PROTEÇÃO CONTA EXECUÇÃO DE JAVA SCRIPT MALICIOSO

        $this->form->appendPage('Informações Do Evento');
        
        // master fields
        $id = new TEntry('id');
        $cliente_id = new TDBUniqueSearch('cliente_id', TSession::getValue('unit_database'), 'Pessoa', 'id', 'nome_fantasia');
        $cliente_id->setMinLength(0);
        $tipo_contrato_id = new TDBCombo('tipo_contrato_id', TSession::getValue('unit_database'), 'TipoContrato', 'id', 'nome');
        $agendado = new TRadioGroup('agendado');
        $agendado->addItems( ['Y' => 'Sim', 'N' => 'Não'] );
        $agendado->setLayout('horizontal');
        $agendado->setUseButton();
        $ativo = new TRadioGroup('ativo');
        $ativo->addItems( ['N' => 'Sim', 'Y' => 'Não'] );
        $ativo->setLayout('horizontal');
        //$ativo->setUseButton();
        $dt_inicio = new TDate('dt_inicio');
        $dt_fim = new TDate('dt_fim');
        $obs = new TText('obs');

        $button = new TActionLink('', new TAction(['PessoaList', 'onClear']), 'green', null, null, 'fa:plus-circle');
        $button->class = 'btn btn-default inline-button';
        $button->title = 'Cadastrar Cliente';
        $cliente_id->after($button);

        $button = new TActionLink('', new TAction(['TipoDecoracaoList', 'onClear']), 'green', null, null, 'fa:plus-circle');
        $button->class = 'btn btn-default inline-button';
        $button->title = 'Cadastrar Contrato';
        $tipo_contrato_id->after($button);

        $aniversariante = new TEntry('aniversariante');
        $convidados = new TEntry('convidados');
        $tema = new TEntry('tema');
        $valor = new TNumeric('valor', 2, ',', '.');

        $PagamentoEntrada = new TDBUniqueSearch('PagamentoEntrada', TSession::getValue('unit_database'), 'FormaRecebimento', 'id', 'nome');
        $PagamentoEntrada->setMinLength(0);
        $ValorEntrada = new TNumeric('ValorEntrada', 2, ',', '.');
        $ValorDesconto = new TNumeric('ValorDesconto', 2, ',', '.');
        $ValorContrato = new TNumeric('ValorContrato', 2, ',', '.');
        $ValorTotal = new TNumeric('ValorTotal', 2, ',', '.');
        $IdVendedor = new TEntry('IdVendedor');
        $LoginVendedor = new TEntry('LoginVendedor');

        $local_festa        = new TText('local_festa');
        $dt_remocao         = new TDate('dt_remocao');
        $hora_remocao       = new TEntry('hora_remocao');
        $moveis             = new TText('moveis');
        $itens_decorativos  = new TText('itens_decorativos');
        $pecas_doces        = new TText('pecas_doces');
        $obs_checklist      = new TText('obs_checklist');

        $cliente_id->setMinLength(0);
        $tipo_contrato_id->enableSearch();
        
        //$valor->setMask('99.999,99');

        //$dt_inicio->setMask('dd/mm/yyyy hh:ii');
        //$dt_inicio->setDatabaseMask('yyyy-mm-dd hh:ii');
        //$dt_fim->setMask('dd/mm/yyyy');
        //$dt_fim->setDatabaseMask('yyyy-mm-dd');

        $dt_inicio->setMask('dd/mm/yyyy');
        $dt_fim->setMask('dd/mm/yyyy');
        $dt_inicio->setDatabaseMask('yyyy-mm-dd');
        $dt_fim->setDatabaseMask('yyyy-mm-dd');
        
        $convidados->setMask('9999');
        $dt_inicio->setSize('100%');
        $dt_fim->setSize('100%');
        $obs->setSize('100%', 70);
        $ativo->setValue('Y');
        $agendado->setValue('Y');

        $PagamentoEntrada->setSize('100%');
        $ValorEntrada->setSize('100%');
        $ValorDesconto->setSize('100%');
        $ValorContrato->setSize('100%');
        $ValorTotal->setSize('100%');
        $IdVendedor->setSize('100%');
        $LoginVendedor->setSize('100%');
        $LoginVendedor->forceUpperCase();
        $LoginVendedor->setEditable(FALSE);
        $IdVendedor->setEditable(FALSE);
        $valor->setEditable(FALSE);
        $ativo->setEditable(FALSE);

        $grupoUser = TSession::getValue('usergroupids');
        if( $grupoUser['0'] > 0 ) // Visualização Admin
        {
            $valor->setEditable(FALSE);
            $ValorContrato->setEditable(FALSE);
            $ValorTotal->setEditable(FALSE);
        }
        
        $cliente_id->addValidation('Cliente', new TRequiredValidator);
        $tipo_contrato_id->addValidation('Tipo Festa', new TRequiredValidator);
        $agendado->addValidation('Agendamento', new TRequiredValidator);
        $dt_inicio->addValidation('Data início', new TRequiredValidator);
        $dt_fim->addValidation('Data fim', new TRequiredValidator);

        // sizes
        $id->setSize('100%');
        $cliente_id->setSize('100%');
        $tipo_contrato_id->setSize('100%');
        $agendado->setSize('100%');
        $ativo->setSize('100%');
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

        $local_festa->setSize('100%', 70);
        $moveis->setSize('100%', 70);
        $itens_decorativos->setSize('100%', 70);
        $pecas_doces->setSize('100%', 70);
        $obs_checklist->setSize('100%', 70);

        $dt_remocao->setSize('100%');
        $dt_remocao->setMask('dd/mm/yyyy');
        $dt_remocao->setDatabaseMask('yyyy-mm-dd');
        $hora_remocao->setSize('100%');

        $hora_remocao->setMask('99:99');
        
        // add form fields to the form
        //$this->form->addFields( [new TLabel('Id')], [$id], [new TLabel('Tipo Contrato')], [$tipo_contrato_id], [new TLabel('Ativo')], [$ativo] );
        //$this->form->addFields( [new TLabel('Dt Inicio')], [$dt_inicio], [new TLabel('Dt Fim')], [$dt_fim] );
        //$this->form->addFields( [new TLabel('Cliente')], [$cliente_id], [new TLabel('Valor')], [$valor] );

        //$this->form->addFields( [new TLabel('Aniversariante')], [$aniversariante], [new TLabel('Tema')], [$tema], [new TLabel('Convidados')], [$convidados] );

        //$this->form->addFields( [new TLabel('Obs')], [$obs] );

        //$this->form->addFields( [new TLabel('Local Festa')], [$local_festa] );
        //$this->form->addFields( [new TLabel('Moveis')], [$moveis] );
        //$this->form->addFields( [new TLabel('Itens Decorativos')], [$itens_decorativos] );
        //$this->form->addFields( [new TLabel('Peças Doces')], [$pecas_doces] );
        //$this->form->addFields( [new TLabel('Obs CheckList')], [$obs_checklist] );

        //$this->form->addFields( [new TLabel('Dt Remoação')], [$dt_remocao], [new TLabel('Hora Remoção')], [$hora_remocao] );

        $row = $this->form->addFields( [ new TLabel('<br>Nº'),     $id ],
                                       [ new TLabel('<br>Cliente'),    $cliente_id ],
                                       [ new TLabel('<br>Gerar reserva?'), $agendado],
                                       [ new TLabel('<br>Cancelado?'), $ativo] );
        $row->layout = ['col-sm-1', 'col-sm-5', 'col-sm-3', 'col-sm-3'];
        
        $row = $this->form->addFields(  [ new TLabel('<br>Dt Inicio'), $dt_inicio],
                                        [ new TLabel('<br>Dt Fim'), $dt_fim],
                                        [ new TLabel('<br>Tipo Contrato'), $tipo_contrato_id] );
        $row->layout = ['col-sm-4', 'col-sm-4', 'col-sm-4'];

        $label2 = new TLabel('<br>Observação', '', 12, '');
        $label2->style='text-align:left;border-bottom:1px solid #c0c0c0;width:100%';
        $this->form->addContent( [$label2] );
        
        $row = $this->form->addFields( [ $obs ] );
        $row->layout = ['col-sm-12'];

        $subform = new BootstrapFormBuilder;
        $subform->setFieldSizes('100%');
        $subform->setProperty('style', 'border:none');

        $subform->appendPage( 'Detalhes Festa' );
        $row = $subform->addFields(    [ new TLabel('<br>Aniversariante'),      $aniversariante ],
                                       [ new TLabel('<br>Tema'),       $tema ],
                                       [ new TLabel('<br>Convidados'), $convidados ] );
        $row->layout = ['col-sm-4', 'col-sm-4', 'col-sm-4'];

        $subform->appendPage( 'Detalhes Decoração' );
        $row = $subform->addFields( [ new TLabel('<br>Local Festa'), $local_festa ] );
        $row->layout = ['col-sm-12'];

        $row = $subform->addFields( [ new TLabel('<br>Moveis'), $moveis ] );
        $row->layout = ['col-sm-12'];

        $row = $subform->addFields( [ new TLabel('<br>Itens Decorativos'), $itens_decorativos ] );
        $row->layout = ['col-sm-12'];

        $row = $subform->addFields( [ new TLabel('<br>Peças Doces'), $pecas_doces ] );
        $row->layout = ['col-sm-12'];

        $row = $subform->addFields( [ new TLabel('<br>Observações do CheckList'), $obs_checklist ] );
        $row->layout = ['col-sm-12'];

        $subform->appendPage( 'Financeiro' );
        $row = $subform->addFields( [ new TLabel('<br>Tipo da Entrada'), $PagamentoEntrada  ],
                                    [ new TLabel('<br>Valor Base'), $ValorContrato ] );
        $row->layout = ['col-sm-3', 'col-sm-2', 'col-sm-2'];
        $row = $subform->addFields( [ new TLabel('<br>Valor de Entrada'), $ValorEntrada  ],
                                    [ new TLabel('<br>Desconto'), $ValorDesconto  ],
                                    [ new TLabel('<br>Valor Total'), $ValorTotal ],
                                    [ new TLabel('<br>Valor a Receber <small>(com descontos)</small>'), $valor ] );
        $row->layout = ['col-sm-3', 'col-sm-3', 'col-sm-3', 'col-sm-3'];

        $subform->appendPage( 'Remoção' );
        $row = $subform->addFields( [ new TLabel('<br>Data Remoção'), $dt_remocao ],
                                    [ new TLabel('<br>Hora Remoção'), $hora_remocao ] );
        $row->layout = ['col-sm-6','col-sm-6'];

        $this->form->addContent( [ $subform ] );

        // detail fields
        $this->fieldlist = new TFieldList;
        $this->fieldlist-> width = '100%';
        $this->fieldlist->enableSorting();

        //$servico_id = new TDBUniqueSearch('list_servico_id[]', TSession::getValue('unit_database'), 'Servico', 'id', 'nome', 'nome', TCriteria::create( ['ativo' => 'Y', 'tipo_servico_id' => 1] ));
        $servico_id = new TEntry('list_servico_id[]');
        $valor = new TNumeric('list_valor[]', 2, ',', '.');
        $quantidade = new TNumeric('list_quantidade[]', 2, ',', '.');
        
        //$servico_id->setChangeAction(new TAction(array($this, 'onChangeServico')));
        
        $servico_id->setSize('100%');
        //$servico_id->setMinLength(0);
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
        
        $this->form->addContent( [ TElement::tag('h5', 'Planilha De Festa', [ 'style'=>'background: whitesmoke; padding: 5px; border-radius: 5px; margin-top: 5px'] ) ] );
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
            TTransaction::open(TSession::getValue('unit_database'));
            
            if (isset($param['key']))
            {
                $key = $param['key'];
                
                $object = new Decoracao($key);
                $this->form->setData($object);
                
                $items  = DecoracaoItem::where('contrato_id', '=', $key)->load();
                
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
                    TEntry::disableField('form_Decoracao', 'ValorDesconto'); 
                    TEntry::disableField('form_Decoracao', 'ValorEntrada'); 
                }
                
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
            TTransaction::open(TSession::getValue('unit_database'));

            $data = (object) $param;

            $id = (int) $param['id'];
            $master = new Decoracao;
            $master->fromArray($param);

            //print_r($param);

            $master->dt_inicio      = TDateTime::convertToMask($param['dt_inicio'],  'dd/mm/yyyy', 'yyyy-mm-dd');
            $master->dt_fim         = TDateTime::convertToMask($param['dt_fim'],  'dd/mm/yyyy', 'yyyy-mm-dd');
            $master->dt_remocao     = TDateTime::convertToMask($param['dt_remocao'], 'dd/mm/yyyy', 'yyyy-mm-dd');
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
            DecoracaoItem::where('contrato_id', '=', $master->id)->delete();
            
            if( !empty($param['list_servico_id']) AND is_array($param['list_servico_id']) )
            {
                foreach( $param['list_servico_id'] as $row => $servico_id)
                {
                    if (!empty($servico_id))
                    {
                        $detail = new DecoracaoItem;
                        $detail->contrato_id = $master->id;
                        $detail->servico_id = $param['list_servico_id'][$row];
                        $detail->valor =      (float) str_replace(['.',','], ['','.'], $param['list_valor'][$row]);
                        $detail->quantidade = (float) str_replace(['.',','], ['','.'], $param['list_quantidade'][$row]);
                        $detail->total = round($detail->valor * $detail->quantidade,2);
                        $detail->store();
                    }
                }
            }
            
            ///////////////////////////////////////////////////////////////
            if(empty($param['ValorDesconto']) OR $param['ValorDesconto'] == 0)
            {
                if (empty($param['id']))
                {
                    $tipo_contrato = new TipoDecoracao($param['tipo_contrato_id']);
                    $valor_contrato = $tipo_contrato->ValorContrato;
                }
                else
                {
                    $valor_contrato = (float) str_replace(['.',','], ['','.'], $param['ValorContrato']);
                }

                $valor_entrada = (float) str_replace(['.',','], ['','.'], $param['ValorEntrada']);

                $ValorTotalItens = DecoracaoItem::where('contrato_id','=',$param['id'])->sumBy('total');
                $valor_itens =  $ValorTotalItens;

                $valor_total = $valor_itens + $valor_contrato;

                $master->valor = $valor_total;
                $master->ValorEntrada = $valor_entrada;
                $master->ValorContrato = $valor_contrato;
                $master->ValorTotal = $valor_total;
            }
            else
            {
                if (empty($param['id']))
                {
                    $tipo_contrato = new TipoDecoracao($param['tipo_contrato_id']);
                    $valor_contrato = $tipo_contrato->ValorContrato;
                }else
                {
                    $valor_contrato = (float) str_replace(['.',','], ['','.'], $param['ValorContrato']);
                }

                $valor_desconto = (float) str_replace(['.',','], ['','.'], $param['ValorDesconto']);
                $valor_entrada = (float) str_replace(['.',','], ['','.'], $param['ValorEntrada']);

                $ValorTotalItens = DecoracaoItem::where('contrato_id','=',$param['id'])->sumBy('total');
                $valor_itens =  $ValorTotalItens;

                $valor_total = $valor_itens + $valor_contrato;
                $valor_decoracao = $valor_total - $valor_desconto;

                $master->valor = $valor_decoracao;
                $master->ValorDesconto = $valor_desconto;
                $master->ValorEntrada = $valor_entrada;
                $master->ValorContrato = $valor_contrato;
                $master->ValorTotal = $valor_total;
            }

            ///////////////////////////////////////////////////////////////
            $master->store();
            ///////////////////////////////////////////////////////////////
            ///////////////////////////////////////////////////////////////

            ///////////////////////////////////////////////////////////////
            if($param['agendado'] == 'Y')
            {
                if($master->ativo == 'Y')
                {
                    $evento_criado  = Evento::where('decoracao_id', '=', $master->id)->load();
                    //print_r($evento_criado);
                    if(!$evento_criado)
                    {
                        $tipo_contrato = new TipoDecoracao($master->tipo_contrato_id);

                        $evento = new Evento;
                        $evento->cor            = $tipo_contrato->cor;
                        $evento->titulo         = 'Decoração '.$master->aniversariante.'';
                        $evento->descricao      = $master->obs;
                        $evento->inicio         = $master->dt_inicio;
                        $evento->fim            = $master->dt_fim;
                        $evento->decoracao_id   = $master->id;
                        $evento->system_user_id = TSession::getValue('userid'); // id usuário = login
                        $evento->store();

                        $data = new stdClass;
                        $data->id = $master->id;
                        TForm::sendData('form_Decoracao', $data);

                        TScript::create(' $("select[name=\'tipo_contrato_id\'").prop("disabled", false); ');
                        TScript::create(' $("select[name=\'cliente_id\'").prop("disabled", false); ');
                        TScript::create(' $("select[name=\'PagamentoEntrada\'").prop("disabled", false); ');
                        
                        $pos_action = new TAction(['DecoracaoList', 'onReload']);
                        new TMessage('info', 'Decoração Salva!', $pos_action);
                    }
                }
                else
                {
                    new TMessage('danger', 'Não é possível agendar uma decoração cancelada!');
                }
            }
            else
            {
                Evento::where('decoracao_id', '=', $master->id)->delete();
            }
            ///////////////////////////////////////////////////////////////
            ///////////////////////////////////////////////////////////////

            TTransaction::close(); // close the transaction
            
            
        }
        catch (Exception $e) // in case of exception
        {
            //new TMessage('error', $param);
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
                TTransaction::open(TSession::getValue('unit_database'));
                
                $servico = Servico::find($servico_id);
                $response->{'list_quantidade_'.$unique_id} = '1,00';
                $response->{'list_valor_'.$unique_id} = number_format($servico->valor,2,',', '.');
                
                TForm::sendData('form_Decoracao', $response);
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
