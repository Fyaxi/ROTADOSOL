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
class PagarEdit extends TWindow
{
    protected $form; // form
    protected $fieldlist;

    use Adianti\Base\AdiantiStandardFormTrait; // Standard form methods
    
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
        $this->form = new BootstrapFormBuilder('formedit_ContaPagarPg');
        $this->form->setFormTitle('Conta a Pagar');
        $this->form->setClientValidation(true);
        $this->form->enableCSRFProtection(); // ATIVA PROTEÇÃO CONTA EXECUÇÃO DE JAVA SCRIPT MALICIOSO
        
        // master fields
        $id = new TEntry('id');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('grupo_id', '=', 2), TExpression::OR_OPERATOR); 
        $criteria->add(new TFilter('grupo_id', '=', 3), TExpression::OR_OPERATOR); 
        $pessoa_id = new TDBUniqueSearch('pessoa_id', 'DBUNIDADE', 'Favorecido', 'id', 'nome_fantasia', 'grupo_id ', $criteria);
        $dt_emissao = new TDate('dt_emissao');
        $dt_vencimento = new TDate('dt_vencimento');
        $dt_pagamento = new TDate('dt_pagamento');
        $valor = new TEntry('valor');
        //$valor = new TNumeric('valor', 2, ',', '.');
        $desconto = new TNumeric('desconto', 2, ',', '.');
        $valor_total = new TNumeric('valor_total', 2, ',', '.');
        $obs = new TText('obs');

        // sizes
        $id->setSize('100%');
        $pessoa_id->setSize('100%');
        $dt_emissao->setSize('100%');
        $dt_vencimento->setSize('100%');
        $dt_pagamento->setSize('100%');
        $valor->setSize('100%');
        $desconto->setSize('100%');
        $valor_total->setSize('100%');
        $obs->setSize('100%');
        
        $dt_emissao->addValidation('Data Emissao', new TRequiredValidator);
        $dt_vencimento->addValidation('Data Vencimento', new TRequiredValidator);
        $pessoa_id->addValidation('Favorecido', new TRequiredValidator);
        $valor_total->addValidation('Valor Total', new TRequiredValidator);

        $dt_emissao->setMask('dd/mm/yyyy');
        $dt_emissao->setDatabaseMask('yyyy-mm-dd');
        
        $dt_vencimento->setMask('dd/mm/yyyy');
        $dt_vencimento->setDatabaseMask('yyyy-mm-dd');
        
        $dt_pagamento->setMask('dd/mm/yyyy');
        $dt_pagamento->setDatabaseMask('yyyy-mm-dd');

        $dt_emissao->setValue(date('Y-m-d'));
        $id->setEditable(FALSE);
        $valor_total->setEditable(FALSE);
        //$desconto->setEditable(FALSE);
        $pessoa_id->setMinLength(0);
        //$forma_recebimento->setMinLength(0);

        $desconto->setEditable(FALSE);
        $valor->setEditable(FALSE);
        $dt_pagamento->setEditable(FALSE);
        
        
        // add form fields to the form
        $this->form->addFields( [new TFormSeparator('<small>Informações Do Cliente</small>')] );
        $row = $this->form->addFields( [ new TLabel('Recebimento N°') ], [ $id ] );
        $row->layout = ['col-sm-2 control-label', 'col-sm-2' ];
        
        $this->form->addFields( [ new TLabel('Pessoa') ], [ $pessoa_id ] );

        $this->form->addFields( [ new TFormSeparator('<small>Informações Do Recebimento</small>')] );
        $this->form->addFields( [ new TLabel('Data Emissão') ], [ $dt_emissao ], [ new TLabel('Data Vencimento') ], [ $dt_vencimento ], [ new TLabel('Data Pagamento') ], [ $dt_pagamento ] );
        $this->form->addFields( [ new TFormSeparator('')] );
        $this->form->addFields( [ new TLabel('Valor Total') ], [ $valor_total ], [ new TLabel('Desconto Concedido') ], [ $desconto ], [ new TLabel('Valor a Receber') ], [ $valor ]  );
        $this->form->addFields( ['<br>'] );
        $this->form->addFields( [ new TLabel('Observação') ], [ $obs ] );   

        $valor_total->onBlur   = 'calculate_bmi1()';
        $desconto->onBlur = 'calculate_bmi1()';
        
        TScript::create('calculate_bmi1 = function() {
            if (parseFloat(document.form_ContaReceberPg.valor_total.value) > 0 || parseFloat(document.form_ContaReceberPg.desconto.value) > 0)
            {
                form_ContaReceberPg.valor.value = parseFloat(form_ContaReceberPg.valor_total.value) - parseFloat(form_ContaReceberPg.desconto.value);
            }
            
            if (parseFloat(document.form_ContaReceberPg.valor_total.value) > 0 || parseFloat(document.form_ContaReceberPg.desconto.value) = 0)
            {
                form_ContaReceberPg.valor.value = parseFloat(form_ContaReceberPg.valor_total.value);
            }
        };');
        
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        // detail fields
        $this->fieldlist = new TFieldList;
        $this->fieldlist-> width = '100%';
        $this->fieldlist->enableSorting();
        
        $criteria = new TCriteria;
        $criteria->add(new TFilter('ativo', '=', 'Y'), TExpression::OR_OPERATOR);
        $recebimentos_id = new TDBUniqueSearch('list_servico_id[]', 'DBUNIDADE', 'FormaPagamento', 'id', 'nome', null, $criteria);

        $recebimentos_valor = new TNumeric('list_valor[]', 2, ',', '.');
        
        $recebimentos_id->setSize('100%');
        $recebimentos_valor->setSize('100%');
        $recebimentos_id->setMinLength(0);

        $this->fieldlist->addField( '<b>Tipo</b>', $recebimentos_id, ['width' => '50%']);
        $this->fieldlist->addField( '<b>Valor</b>', $recebimentos_valor, ['width' => '50%']);

        $this->form->addField($recebimentos_id);
        $this->form->addField($recebimentos_valor);
        
        $detail_wrapper = new TElement('div');
        $detail_wrapper->add($this->fieldlist);
        $detail_wrapper->style = 'overflow-x:auto';
        
        $this->form->addContent( [ TElement::tag('h5', 'Lançamento Rateio', [ 'style'=>'background: whitesmoke; padding: 5px; border-radius: 5px; margin-top: 5px'] ) ] );
        $this->form->addContent( [ $detail_wrapper ] );

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
            TTransaction::open('DBUNIDADE');
            
            if (isset($param['key']))
            {
                $key = $param['key'];
                
                $object = new ContaPagar($key);
                $object->dt_emissao = TDateTime::convertToMask($object->dt_emissao, 'yyyy-mm-dd', 'dd/mm/yyyy');
                $object->dt_vencimento = TDateTime::convertToMask($object->dt_vencimento, 'yyyy-mm-dd', 'dd/mm/yyyy');
                $object->dt_pagamento = TDateTime::convertToMask($object->dt_pagamento, 'yyyy-mm-dd', 'dd/mm/yyyy');
                $this->form->setData($object);
                
                $items  = ContaPagarPg::where('recebimento_id', '=', $key)->load();

                TScript::create(' $("select[name=\'pessoa_id\'").prop("disabled", true); ');
                TEntry::disableField('formedit_ContaReceberPg', 'valor');
                //TCombo::disableField('formedit_ContaReceberPg', 'pessoa_id');

                if ($items)
                {
                    $this->fieldlist->addHeader();
                    foreach($items  as $item )
                    {
                        $detail = new stdClass;
                        $detail->list_servico_id = $item->formarecebimento_id;
                        $detail->list_valor = $item->valor;
                        $this->fieldlist->addDetail($detail);
                    }

                    $this->fieldlist->addCloneAction();
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
     * Save the Venda and the VendaItem's
     */
    public static function onSaveBack($param)
    {
        try
        {
            TTransaction::open('DBUNIDADE');
            
            $id = (int) $param['id'];
            $master = new ContaPagar;
            $master->fromArray($param);

            $master->dt_emissao = TDateTime::convertToMask($param['dt_emissao'], 'dd/mm/yyyy', 'yyyy-mm-dd');
            $master->dt_vencimento = TDateTime::convertToMask($param['dt_vencimento'], 'dd/mm/yyyy', 'yyyy-mm-dd');
            $master->dt_pagamento = TDateTime::convertToMask($param['dt_pagamento'], 'dd/mm/yyyy', 'yyyy-mm-dd');

            $master->mes = TDateTime::convertToMask($param['dt_vencimento'], 'dd/mm/yyyy', 'mm');
            $master->ano = TDateTime::convertToMask($param['dt_vencimento'], 'dd/mm/yyyy', 'yyyy');

            $valor_desconto = 0;
            $valor_fatura = 0;
            $fatura_total = 0;

            if (empty($master->id))
            {
                $master->ativo  = 'Y';
                $master->origem = 'D';

                $valor_desconto = 0;
                $valor_fatura   = 0;
                $fatura_total   = 0;

                if( !empty($master->desconto) )
                {
                    $valor_desconto = (float) str_replace(['.',','], ['','.'], $param['desconto']);
                    $fatura_total   = (float) str_replace(['.',','], ['','.'], $param['valor_total']);
                    $valor_fatura   = $fatura_total - $valor_desconto;

                    // Formatação Visualização
                    //$valor_fatura   = number_format($valor_fatura, 2, ',', '.');
                    
                    // Formatação Banco
                    $master->valor  = $valor_fatura;

                    // Formatação Visualização
                    $master->valor_total = $fatura_total;
                }
                else
                {
                    $fatura_total   = (float) str_replace(['.',','], ['','.'], $param['valor_total']);
                    $valor_fatura   = $fatura_total;

                    // Formatação Visualização
                    //$valor_fatura   = number_format($valor_fatura, 2, ',', '.');

                    // Formatação Banco
                    $master->valor = $fatura_total;
                }
            }

            if($master->valor >= 1 AND $master->valor_total >= 1)
            {
                $master->store(); // Store Contas Pagar
            }
            else
            {
                throw new Exception('<span style="color:black;font-weight: bold;">Valor mínimo para o lançamento é de $1 Real.</span>');
            }
            
            if( !empty($param['list_servico_id']) AND is_array($param['list_servico_id']) )
            {
                if(!empty($param['list_servico_id'][0]))
                {
                    $valor_desconto             = 0;
                    $valor_fatura               = 0;
                    $fatura_total               = 0;
                    $total_lancamentos          = 0;
                    $total_lancamentos_r        = 0;

                    $total_lancamentos =  (float) array_sum( str_replace(['.',','], ['','.'], $param['list_valor']) );
                    $total_lancamentos_r = number_format($total_lancamentos, 2, ',', '.');

                    if( !empty($master->desconto) )
                    {
                        $valor_desconto = (float) str_replace(['.',','], ['','.'], $param['desconto']);
                        $fatura_total   = (float) str_replace(['.',','], ['','.'], $param['valor_total']);

                        $valor_fatura   = $fatura_total - $valor_desconto;

                        // Formatação Visualização
                        $valor_fatura   = number_format($valor_fatura, 2, ',', '.');
                        
                        // Formatação Banco
                        $master->valor  = (float) str_replace(['.',','], ['','.'], $valor_fatura);
                    }
                    else
                    {
                        $fatura_total   = (float) str_replace(['.',','], ['','.'], $param['valor_total']);
                        $valor_fatura   = $fatura_total;

                        // Formatação Visualização
                        $valor_fatura   = number_format($valor_fatura, 2, ',', '.');
                    }

                    if($valor_fatura == $total_lancamentos_r)
                    {
                        if( $valor_fatura >= 1 AND $total_lancamentos_r >= 1)
                        {
                            $master->store(); // Store Contas Pagar
                            $data = new stdClass;
                            $data->id = $master->id;
                            TForm::sendData('formedit_ContaPagarPg', $data);

                            ContaPagarPg::where('recebimento_id', '=', $master->id)->delete();
                            
                            foreach( $param['list_servico_id'] as $row => $formarecebimento)
                            {
                                $detail = new ContaPagarPg;
                                $detail->recebimento_id = $master->id;
                                $detail->formarecebimento_id = $param['list_servico_id'][$row];
                                $detail->valor = (float) str_replace(['.',','], ['','.'], $param['list_valor'][$row]);
                                $detail->store(); // Store Contas Receber PG
                            }

                            $pos_action = new TAction(['ContaPagarList', 'onReload'], ['register_state' => 'true']);
                            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'), $pos_action);
                            TScript::create(' $("select[name=\'pessoa_id\'").prop("disabled", false); ');
                            parent::closeWindow();
                        }
                        else
                        {
                            throw new Exception('<span style="color:black;font-weight: bold;">Valor mínimo para um lançamento é de $1.</span>');
                        }
                    }
                    else
                    {
                        throw new Exception('<span style="color:black;font-weight: bold;">VALOR TOTAL DAS PACERLAS</span><BR>NÃO BATE COM O VALOR TOTAL DO PAGAMENTO');
                    }
                }
                else
                {
                    throw new Exception('<span style="color:black;font-weight: bold;">SELECIONE PELO MENOS UM TIPO DE PAGAMENTO</span>');
                }
            }

            TTransaction::close(); // close the transaction
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    /**
     * Save the Venda and the VendaItem's
     */
    public static function onSave($param)
    {
        try
        {
            TTransaction::open('DBUNIDADE');
            
            $id = (int) $param['id'];
            $master = new ContaPagar;
            $master->fromArray($param);

            if (empty($master->id))
            {
                throw new Exception('<span style="color:black;font-weight: bold;">Editar o lançamento não é permitido.</span>');
            }

            $master->dt_emissao = TDateTime::convertToMask($param['dt_emissao'], 'dd/mm/yyyy', 'yyyy-mm-dd');
            $master->dt_vencimento = TDateTime::convertToMask($param['dt_vencimento'], 'dd/mm/yyyy', 'yyyy-mm-dd');
            $master->dt_pagamento = TDateTime::convertToMask($param['dt_pagamento'], 'dd/mm/yyyy', 'yyyy-mm-dd');

            $master->mes = TDateTime::convertToMask($param['dt_vencimento'], 'dd/mm/yyyy', 'mm');
            $master->ano = TDateTime::convertToMask($param['dt_vencimento'], 'dd/mm/yyyy', 'yyyy');
            
            if( !empty($param['list_servico_id']) AND is_array($param['list_servico_id']) )
            {
                if(!empty($param['list_servico_id'][0]))
                {
                    $valor_desconto             = 0;
                    $valor_fatura               = 0;
                    $fatura_total               = 0;
                    $total_lancamentos          = 0;
                    $total_lancamentos_r        = 0;

                    $total_lancamentos =  (float) array_sum( str_replace(['.',','], ['','.'], $param['list_valor']) );
                    $total_lancamentos_r = number_format($total_lancamentos, 2, ',', '.');

                    if( !empty($master->desconto) )
                    {
                        // Formatação Form
                        $valor_desconto = (float) str_replace(['.',','], ['','.'], $param['desconto']);
                        $fatura_total   = (float) str_replace(['.',','], ['','.'], $param['valor_total']);

                        $valor_fatura   = $fatura_total - $valor_desconto;

                        // Formatação Visualização
                        $valor_fatura   = number_format($valor_fatura, 2, ',', '.');
                        $valor_fatura_total   = number_format($fatura_total, 2, ',', '.');
                        $valor_fatura_desconto   = number_format($valor_desconto, 2, ',', '.');
                        
                        // Formatação Banco
                        $master->valor  = (float) str_replace(['.',','], ['','.'], $valor_fatura);
                        $master->valor_total  = (float) str_replace(['.',','], ['','.'], $valor_fatura_total);
                        $master->desconto  = (float) str_replace(['.',','], ['','.'], $valor_fatura_desconto);
                    }
                    else
                    {
                        // Formatação Form
                        $fatura_total   = (float) str_replace(['.',','], ['','.'], $param['valor_total']);

                        // Formatação Visualização
                        $valor_fatura   = number_format($fatura_total, 2, ',', '.');

                        // Formatação Banco
                        $master->valor  = (float) str_replace(['.',','], ['','.'], $valor_fatura);
                        $master->valor_total  = (float) str_replace(['.',','], ['','.'], $valor_fatura);
                    }

                    if($valor_fatura == $total_lancamentos_r)
                    {
                        if( $valor_fatura >= 1 AND $total_lancamentos_r >= 1)
                        {
                            $master->store(); // Store Contas Pagar
                            $data = new stdClass;
                            $data->id = $master->id;

                            ContaPagarPg::where('recebimento_id', '=', $master->id)->delete();
                            
                            foreach( $param['list_servico_id'] as $row => $formarecebimento)
                            {
                                $detail = new ContaPagarPg;
                                $detail->recebimento_id = $master->id;
                                $detail->formarecebimento_id = $param['list_servico_id'][$row];
                                $detail->valor = (float) str_replace(['.',','], ['','.'], $param['list_valor'][$row]);
                                $detail->store(); // Store Contas Receber PG
                            }

                            TForm::sendData('formedit_ContaPagarPg', $data);

                            $pos_action = new TAction(['ContaPagarList', 'onReload'], ['register_state' => 'true']);
                            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'), $pos_action);
                            TScript::create(' $("select[name=\'pessoa_id\'").prop("disabled", false); ');
                            parent::closeWindow();
                        }
                        else
                        {
                            throw new Exception('<span style="color:black;font-weight: bold;">Valor mínimo para um lançamento é de $1.</span>');
                        }
                    }
                    else
                    {
                        throw new Exception('<span style="color:black;font-weight: bold;">VALOR TOTAL DAS PACERLAS</span><BR>NÃO BATE COM O VALOR TOTAL DO PAGAMENTO');
                    }
                }
                else
                {
                    throw new Exception('<span style="color:black;font-weight: bold;">SELECIONE PELO MENOS UM TIPO DE PAGAMENTO</span>');
                }
            }

            TTransaction::close(); // close the transaction
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
     * Close
     */
    public static function onClose($param)
    {
        TScript::create(' $("select[name=\'pessoa_id\'").prop("disabled", false); ');
        parent::closeWindow();
    }   
}
