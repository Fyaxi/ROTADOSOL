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
class VendaCaixaForm extends TWindow
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
        //parent::disableEscape();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Venda');
        $this->form->setFormTitle('Vender pelo Caixa');
        $this->form->setClientValidation(true);
        $this->form->enableCSRFProtection(); // ATIVA PROTEÇÃO CONTA EXECUÇÃO DE JAVA SCRIPT MALICIOSO
        
        // master fields
        $id = new TEntry('id');
        $cliente_id = new TDBUniqueSearch('cliente_id', TSession::getValue('unit_database'), 'Pessoa', 'id', 'nome_fantasia');
        $dt_fatura = new TDate('dt_fatura');

        // sizes
        $id->setSize('100%');
        $cliente_id->setSize('100%');
        $dt_fatura->setSize('100%');
        
        $cliente_id->addValidation('Cliente', new TRequiredValidator);
        $dt_fatura->addValidation('Dt Venda', new TRequiredValidator);

        $id->setEditable(FALSE);
        $cliente_id->setMinLength(0);
        
        $dt_fatura->setMask('dd/mm/yyyy');
        $dt_fatura->setDatabaseMask('yyyy-mm-dd');
        
        // add form fields to the form
        $this->form->addFields( [new TLabel('Id')], [$id] );
        $this->form->addFields( [new TLabel('Cliente')], [$cliente_id] );
        $this->form->addFields( [new TLabel('Dt Venda')], [$dt_fatura] );
        
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        // detail fields
        $this->fieldlist = new TFieldList;
        $this->fieldlist-> width = '100%';
        $this->fieldlist->enableSorting();
        
        $servico_id = new TDBUniqueSearch('list_servico_id[]', TSession::getValue('unit_database'), 'Produto', 'id', 'nome', null, TCriteria::create( ['ativo' => 'Y'] ));
        $servico_id->setChangeAction(new TAction(array($this, 'onChangeServico')));

        $valor = new TNumeric('list_valor[]', 2, ',', '.');
        //$valor->setEditable(FALSE);

        $quantidade = new TNumeric('list_quantidade[]', 2, ',', '.');
        
        $servico_id->setSize('100%');
        $valor->setSize('100%');
        $quantidade->setSize('100%');
        $servico_id->setMinLength(0);

        $this->fieldlist->addField( '<b>Produto</b>', $servico_id, ['width' => '40%']);
        $this->fieldlist->addField( '<b>Valor</b>', $valor, ['width' => '30%']);
        $this->fieldlist->addField( '<b>Quantidade</b>', $quantidade, ['width' => '30%']);

        $this->form->addField($servico_id);
        $this->form->addField($valor);
        $this->form->addField($quantidade);
        
        $detail_wrapper = new TElement('div');
        $detail_wrapper->add($this->fieldlist);
        $detail_wrapper->style = 'overflow-x:auto';
        
        $this->form->addContent( [ TElement::tag('h5', 'Itens da faturaa', [ 'style'=>'background: whitesmoke; padding: 5px; border-radius: 5px; margin-top: 5px'] ) ] );
        $this->form->addContent( [ $detail_wrapper ] );

        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        // detail fields
        $this->fieldlist2 = new TFieldList;
        $this->fieldlist2-> width = '100%';
        $this->fieldlist2->enableSorting();
        
        $criteria = new TCriteria;
        $criteria->add(new TFilter('ativo', '=', 'N'), TExpression::OR_OPERATOR);
        $criteria->add(new TFilter('id', '>', 1), TExpression::OR_OPERATOR); 
        
        $forma_recebimento = new TDBUniqueSearch('list_servico_id2[]', TSession::getValue('unit_database'), 'FormaRecebimento', 'id', 'nome', null, $criteria);
        $forma_recebimento->setSize('100%');
        $forma_recebimento->setMinLength(0);

        $valor = new TNumeric('list_valor2[]', 2, ',', '.');
        $valor->setSize('100%');

        $this->fieldlist2->addField( '<b>Tipo</b>', $forma_recebimento, ['width' => '50%']);
        $this->fieldlist2->addField( '<b>Valor</b>', $valor, ['width' => '50%']);

        $this->form->addField($forma_recebimento);
        $this->form->addField($valor);
        
        $detail_wrapper2 = new TElement('div');
        $detail_wrapper2->add($this->fieldlist2);
        $detail_wrapper2->style = 'overflow-x:auto';
        
        $this->form->addContent( [ TElement::tag('h5', 'Lançamentos', [ 'style'=>'background: whitesmoke; padding: 5px; border-radius: 5px; margin-top: 5px'] ) ] );
        $this->form->addContent( [ $detail_wrapper2 ] );

        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
        // create actions
        $this->form->addAction( _t('Save'),  new TAction( [$this, 'onSave'] ),  'fa:save green' );
        $this->form->addAction( _t('Clear'), new TAction( [$this, 'onClear'] ), 'fa:eraser red' );

        $this->form->addHeaderActionLink( _t('Close'), new TAction([$this, 'onClose']), 'fa:window-close red');
        
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
                
                $object = new VendaCaixa($key);
                $this->form->setData($object);
                
                $items  = VendaCaixaItem::where('fatura_id', '=', $key)->load();
                
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

                /////////////////////////////////////////////////////////////////////

                $items2  = CaixaMovPg::where('mov_id', '=', $key)->load();

                if ($items2)
                {
                    $this->fieldlist2->addHeader();
                    foreach( $items2  as $item2 )
                    {
                        $detail2 = new stdClass;
                        $detail2->list_servico_id2 = $item2->forma_recebimento;
                        $detail2->list_valor2 = $item2->valor;
                        $this->fieldlist2->addDetail($detail2);
                    }

                    $this->fieldlist2->addCloneAction();
                }
                else
                {
                    $this->onClear($param);
                }
                
                TTransaction::close(); // close transaction  

                TDate::disableField('form_Venda', 'dt_fatura');
                TScript::create(' $("select[name=\'cliente_id\'").prop("disabled", true); ');
                //TButton::disableField('form_Venda', 'btn_salvar'); 
                //TButton::disableField('form_Venda', 'btn_limpar');
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
                
                $servico = Produto::find($servico_id);
                $response->{'list_quantidade_'.$unique_id} = '1,00';
                $response->{'list_valor_'.$unique_id} = number_format($servico->valor,2,',', '.');
                
                TForm::sendData('form_Venda', $response);
                TTransaction::close();
            }
            catch (Exception $e)
            {
                TTransaction::rollback();
            }
        }
    }

    /**
     * Save the Venda and the VendaItem's
     */
    public static function onSave($param)
    {
        try
        {
            TTransaction::open(TSession::getValue('unit_database'));
            
            $id = (int) $param['id'];
            $master = new VendaCaixa;
            $master->fromArray( $param);
            $master->dt_fatura = TDateTime::convertToMask($param['dt_fatura'], 'dd/mm/yyyy', 'yyyy-mm-dd');
            $master->mes = TDateTime::convertToMask($param['dt_fatura'], 'dd/mm/yyyy', 'mm');
            $master->ano = TDateTime::convertToMask($param['dt_fatura'], 'dd/mm/yyyy', 'yyyy');
            
            if (empty($master->id))
            {
                $master->ativo = 'Y';
                $master->financeiro_gerado = 'N';
            }
            
            $master->store(); // save master object
            
            // delete details
            VendaCaixaItem::where('fatura_id', '=', $master->id)->delete();
            
            if( !empty($param['list_servico_id']) AND is_array($param['list_servico_id']) )
            {
                $total = 0;
                foreach( $param['list_servico_id'] as $row => $servico_id)
                {
                    if(!empty($param['list_servico_id'][0]))
                    {
                        $detail = new VendaCaixaItem;
                        $detail->fatura_id = $master->id;
                        $detail->servico_id = $param['list_servico_id'][$row];
                        $detail->valor =      (float) str_replace(['.',','], ['','.'], $param['list_valor'][$row]);
                        $detail->quantidade = (float) str_replace(['.',','], ['','.'], $param['list_quantidade'][$row]);
                        $detail->total = round($detail->valor * $detail->quantidade, 2);

                        $qnt_produto = explode(".", $detail->quantidade);
                        if(!empty($qnt_produto[1]))
                        {
                            if($qnt_produto[1] > 0)
                            {
                                $qnt_produto[0] += 1;
                            }
                                
                        }

                        $produto = new Produto($param['list_servico_id'][$row]);
                            
                        $estoque_atual = $produto->estoque;
                        //echo 'Estoque Atual: '.$estoque_atual.'<br>';

                        if(!$qnt_produto[0] == 0)
                        {
                            //echo 'Saida Produto: '.$qnt_produto[0].'<br>';
                            $estoque_novo = $estoque_atual - $qnt_produto[0];
                            //echo 'Novo Estoque (>0): '.$estoque_novo;
                        }
                        else{
                            //echo 'Saida Produto: '.$qnt_produto[0].'<br>';
                            $estoque_novo = $estoque_atual - 1;
                            //echo 'Novo Estoque (=0): '.$estoque_novo;
                        }
                        
                        $produto->estoque = $estoque_novo;
                        $produto->store();

                        $detail->store();
                        
                        $total += $detail->total;

                        $master->total = $total;
                        $master->store(); // save master object
                    }
                    else
                    {
                        new TMessage('info', 'erro');
                    }
                }

                

                $data = new stdClass;
                $data->id = $master->id;
                TForm::sendData('form_Venda', $data);
                TTransaction::close(); // close the transaction
                
                $pos_action = new TAction(['VendaCaixaList', 'onReload']);
                new TMessage('info', 'Venda Registrada!', $pos_action);
                parent::closeWindow();
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

        $this->fieldlist2->addHeader();
        $this->fieldlist2->addDetail( new stdClass );
        $this->fieldlist2->addCloneAction();
    }
    
    /**
     * Close
     */
    public static function onClose($param)
    {
        parent::closeWindow();
        TScript::create(' $("select[name=\'cliente_id\'").prop("disabled", false); ');
    }   
}
