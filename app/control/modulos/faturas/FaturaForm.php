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
class FaturaForm extends TWindow
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
        $this->form = new BootstrapFormBuilder('form_Fatura');
        $this->form->setFormTitle('Tela Faturamento');
        $this->form->setClientValidation(true);
        $this->form->enableCSRFProtection(); // ATIVA PROTEÇÃO CONTA EXECUÇÃO DE JAVA SCRIPT MALICIOSO
        
        // master fields
        $id                     = new TEntry('id');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('grupo_id', '=', 1), TExpression::OR_OPERATOR); 
        $cliente_id = new TDBUniqueSearch('cliente_id', 'DBUNIDADE', 'Pessoa', 'id', 'nome_fantasia', 'grupo_id ', $criteria);
        $dt_fatura              = new TDate('dt_fatura');
        //$DataPrevistoPagamento  = new TDate('DataPrevistoPagamento');
        $ValorDesconto          = new TNumeric('ValorDesconto', 2, ',', '.');
        $ValorTotal             = new TNumeric('ValorTotal', 2, ',', '.');
        //$total                  = new TNumeric('total', 2, ',', '.');

        // sizes
        $id->setSize('100%');
        $cliente_id->setSize('100%');
        $dt_fatura->setSize('100%');
        //$DataPrevistoPagamento->setSize('100%');
        $ValorDesconto->setSize('100%');
        $ValorTotal->setSize('100%');
        //$total->setSize('100%');

        $grupoUser = TSession::getValue('usergroupids');
        if( $grupoUser['0'] > 1 ) // Visualização Admin
        {
            $cliente_id->setEditable(FALSE);
            $dt_fatura->setEditable(FALSE);
            $ValorDesconto->setEditable(FALSE);
            $ValorTotal->setEditable(FALSE);
            //$total->setEditable(FALSE);
        }
        
        $cliente_id->addValidation('Cliente', new TRequiredValidator);
        $dt_fatura->addValidation('Dt Fatura', new TRequiredValidator);
        //$DataPrevistoPagamento->addValidation('Dt Previsão Pagamento', new TRequiredValidator);

        $id->setEditable(FALSE);
        $cliente_id->setMinLength(0);
        
        $dt_fatura->setMask('dd/mm/yyyy');
        $dt_fatura->setDatabaseMask('yyyy-mm-dd');

        //$DataPrevistoPagamento->setMask('dd/mm/yyyy');
        //$DataPrevistoPagamento->setDatabaseMask('yyyy-mm-dd');
        
        // add form fields to the form
        $this->form->addFields( [new TLabel('Nº')], [$id] );
        $this->form->addFields( [new TLabel('Cliente')], [$cliente_id] );
        $this->form->addFields( [new TLabel('Dt Fatura')], [$dt_fatura] );
        $this->form->addFields( [new TLabel('Valor Total')], [$ValorTotal], [new TLabel('Valor Desconto')], [$ValorDesconto] );
        
        
        // detail fields
        $this->fieldlist = new TFieldList;
        $this->fieldlist-> width = '100%';
        $this->fieldlist->enableSorting();

        $servico_id = new TDBUniqueSearch('list_servico_id[]', 'DBUNIDADE', 'Servico', 'id', 'nome', null, TCriteria::create( ['ativo' => 'Y'] ));
        $servico_id->setChangeAction(new TAction(array($this, 'onChangeServico')));
        $valor = new TNumeric('list_valor[]', 2, ',', '.');
        $valor->setEditable(FALSE);
        $quantidade = new TNumeric('list_quantidade[]', 2, ',', '.');

        $servico_id->setSize('100%');
        $valor->setSize('100%');
        $quantidade->setSize('100%');
        $servico_id->setMinLength(0);
        $this->fieldlist->addField( '<b>Servico</b>', $servico_id, ['width' => '40%']);
        $this->fieldlist->addField( '<b>Valor</b>', $valor, ['width' => '30%']);
        $this->fieldlist->addField( '<b>Quantidade</b>', $quantidade, ['width' => '30%']);
        $this->form->addField($servico_id);
        $this->form->addField($valor);
        $this->form->addField($quantidade);

        $detail_wrapper = new TElement('div');
        $detail_wrapper->add($this->fieldlist);
        $detail_wrapper->style = 'overflow-x:auto';

        $this->form->addContent( [ TElement::tag('h5', 'Lançamentos', [ 'style'=>'background: whitesmoke; padding: 5px; border-radius: 5px; margin-top: 5px'] ) ] );
        $this->form->addContent( [ $detail_wrapper ] );
        

        if( $grupoUser['0'] == 1 ) // Visualização Admin
        {
            $this->form->addAction( 'Salvar',  new TAction( [$this, 'onSave'] ),  'fa:save green' );
            $this->form->addAction( 'Limpar', new TAction( [$this, 'onClear'] ), 'fa:eraser red' );
        }

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
                
                $object = new Fatura($key);
                $this->form->setData($object);
                
                $items  = FaturaItem::where('fatura_id', '=', $key)->load();
                
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
                
                TForm::sendData('form_Fatura', $response);
                TTransaction::close();
            }
            catch (Exception $e)
            {
                TTransaction::rollback();
            }
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
     * Save the Fatura and the FaturaItem's
     */
    public static function onSave($param)
    {
        try
        {
            TTransaction::open('DBUNIDADE');
            
            $id = (int) $param['id'];
            $master = new Fatura;
            $master->fromArray($param);

            $master->dt_fatura      = TDateTime::convertToMask($param['dt_fatura'], 'dd/mm/yyyy', 'yyyy-mm-dd');
            $master->mes            = TDateTime::convertToMask($param['dt_fatura'], 'dd/mm/yyyy', 'mm');
            $master->ano            = TDateTime::convertToMask($param['dt_fatura'], 'dd/mm/yyyy', 'yyyy');

            if (empty($param['id']))
            {
                $master->ativo = 'Y';
                $master->financeiro_gerado = 'N';

                $valor_desconto = 0;
                $valor_fatura   = 0;
                $fatura_total   = 0;

                if( !empty($master->ValorDesconto) )
                {
                    $valor_desconto = (float) str_replace(['.',','], ['','.'], $master->ValorDesconto);
                    $fatura_total   = (float) str_replace(['.',','], ['','.'], $master->ValorTotal);
                    $valor_fatura   = $fatura_total - $valor_desconto;
                    $valor_fatura   = number_format($valor_fatura, 2, ',', '.');
                    $master->valor  = $valor_fatura;
                }
                else
                {
                    $fatura_total   = (float) str_replace(['.',','], ['','.'], $master->ValorTotal);
                    $valor_fatura   = $fatura_total;
                    $valor_fatura   = number_format($valor_fatura, 2, ',', '.');
                    $master->valor  = $valor_fatura;
                }
            }
            
            $master->store(); // save master object
    
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

                    if( !empty($master->ValorDesconto) || $master->ValorDesconto == 0 )
                    {
                        $valor_desconto = (float) str_replace(['.',','], ['','.'], $master->ValorDesconto);
                        $fatura_total   = (float) str_replace(['.',','], ['','.'], $master->ValorTotal);
                        $valor_fatura   = $fatura_total - $valor_desconto;
                        $valor_fatura   = number_format($valor_fatura, 2, ',', '.');
                        //$master->total  = $valor_fatura;

                    }
                    else
                    {
                        $fatura_total   = (float) str_replace(['.',','], ['','.'], $master->ValorTotal);
                        $valor_fatura   = $fatura_total;
                        $valor_fatura   = number_format($valor_fatura, 2, ',', '.');
                        //$master->total  = $valor_fatura;
                    }

                    if($valor_fatura == $total_lancamentos_r)
                    {
                        FaturaItem::where('fatura_id', '=', $master->id)->delete();
                        
                        foreach( $param['list_servico_id'] as $row => $servico_id)
                        {
                            $detail = new FaturaItem;
                            $detail->fatura_id  = $master->id;
                            $detail->servico_id = $param['list_servico_id'][$row];
                            $detail->valor      = (float) str_replace(['.',','], ['','.'], $param['list_valor'][$row]);
                            $detail->quantidade = (float) str_replace(['.',','], ['','.'], $param['list_quantidade'][$row]);
                            $detail->total      = round($detail->valor * $detail->quantidade, 2);
                            $detail->store(); // Store Contas Receber PG
                        }

                        $master->store();
                    }
                    else
                    {
                        throw new Exception('<span style="color:black;font-weight: bold;">VALOR TOTAL DAS PACERLAS</span><BR>NÃO BATE COM O VALOR TOTAL DO RECEBIMENTO');
                    }
                }
                else
                {
                    throw new Exception('<span style="color:black;font-weight: bold;">INSIRA UM SERVIÇO PARA SALVAR O FATURAMENTO</span>');
                }
            }
            else
            {
                throw new Exception('<span style="color:black;font-weight: bold;">ARRAY() INVÁLIDO</span>');
            }
            

            //echo '<pre>';
            //print_r($param);
            //echo '</pre>';
            
            $data = new stdClass;
            $data->id = $master->id;
            TForm::sendData('form_Fatura',  $data);
            TTransaction::close(); // close the transaction
            
            $pos_action = new TAction(['FaturaList', 'onReload']);
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'), $pos_action);
            parent::closeWindow();
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    /**
     * Close
     */
    public static function onClose($param)
    {
        TScript::create(' $("select[name=\'cliente_id\'").prop("disabled", false); ');
        parent::closeWindow();
    }   
}
