<?php
/*
 * @version    2.0
 * @package    Sistema Integrado
 * @subpackage Módulos
 * @author     Jairo Barreto
 * @copyright  Copyright (c) 2021 Nativus Tecnologia. (http://www.nativustecnologia.com.br)
 */
class ContratoView extends TWindow
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
        $cliente_id = new TDBUniqueSearch('cliente_id', 'DBUNIDADE', 'Pessoa', 'id', 'nome_fantasia');
        $cliente_id->setMinLength(0);
        $tipo_contrato_id = new TDBCombo('tipo_contrato_id', 'DBUNIDADE', 'TipoContrato', 'id', 'nome');
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

        $dt_inicio->setEditable(FALSE);
        $dt_fim->setEditable(FALSE);
        $obs->setEditable(FALSE);
        $tipo_contrato_id->setEditable(FALSE);
        $cliente_id->setEditable(FALSE);
        $aniversariante->setEditable(FALSE);
        $convidados->setEditable(FALSE);
        $tema->setEditable(FALSE);
        $valor->setEditable(FALSE);
        $PagamentoEntrada->setEditable(FALSE);
        $ValorEntrada->setEditable(FALSE);
        $ValorDesconto->setEditable(FALSE);
        $ValorContrato->setEditable(FALSE);
        $ValorTotal->setEditable(FALSE);

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
        //$this->form->addAction( _t('Save'),  new TAction( [$this, 'onSave'] ),  'fa:save green' );
        //$this->form->addAction( _t('Clear'), new TAction( [$this, 'onClear'] ), 'fa:eraser red' );
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
                    
                    //$this->fieldlist->addCloneAction();
                }
                else
                {
                    $this->onClear($param);
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
        parent::closeWindow();
    }
}
