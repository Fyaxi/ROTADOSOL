<?php
/**
 * CaixaMovForm 'DBUNIDADE'
 *
 * @version    2.0
 * @package    Sistema Integrado
 * @subpackage Módulos
 * @author     Jairo Barreto
 * @copyright  Copyright (c) 2021 Nativus Tecnologia. (http://www.nativustecnologia.com.br)
 * 
 */
class CaixaMovForm extends TWindow
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
        $this->form = new BootstrapFormBuilder('form_CaixaFormPg');
        $this->form->setFormTitle('Detalhe Movimentação');
        $this->form->setClientValidation(true);
        $this->form->enableCSRFProtection(); // ATIVA PROTEÇÃO CONTA EXECUÇÃO DE JAVA SCRIPT MALICIOSO
        
        // master fields
        $id = new TEntry('id');

        $caixa_id = new TDBUniqueSearch('caixa_id', 'DBUNIDADE', 'Caixa', 'id', 'id');
        $venda_id = new TDBUniqueSearch('venda_id', 'DBUNIDADE', 'Venda', 'id', 'id');
        
        $caixa_id->setMinLength(0);
        $venda_id->setMinLength(0);
        
        $mov_valor = new TNumeric('mov_valor', 2, ',', '.', true);
        $mov_desconto = new TNumeric('mov_desconto', 2, ',', '.', true);
        $mov_data = new TDate('mov_data');
        $mov_baixa = new TDate('mov_baixa');
        $mov_total = new TNumeric('mov_total', 2, ',', '.', true);

        // set sizes
        $id->setSize('100%');
        $caixa_id->setSize('100%');
        $venda_id->setSize('100%');
        $mov_valor->setSize('100%');
        $mov_desconto->setSize('100%');
        $mov_data->setSize('100%');
        $mov_baixa->setSize('100%');
        $mov_total->setSize('100%');

        $mov_data->setMask('dd/mm/yyyy');
        $mov_data->setDatabaseMask('yyyy-mm-dd');

        $mov_baixa->setMask('dd/mm/yyyy');
        $mov_baixa->setDatabaseMask('yyyy-mm-dd');

        $id->setEditable(false);
        $caixa_id->setEditable(false);
        $venda_id->setEditable(false);
        $mov_valor->setEditable(false);
        $mov_desconto->setEditable(false);
        $mov_data->setEditable(false);
        $mov_baixa->setEditable(false);
        $mov_total->setEditable(false);

        $mov_data->setValue(date('Y-m-d'));
        
        // add the fields
        $this->form->addFields( [new TFormSeparator('<small>Informações da Movimentação</small>')] );
        $this->form->addFields( [ new TLabel('Lançamento') ], [ $mov_data ], [ new TLabel('Data Baixa') ], [ $mov_baixa ] );
        $this->form->addFields( [new TFormSeparator('')] );
        $this->form->addFields( [ new TLabel('Mov. n°:') ], [ $id ], [ new TLabel('Caixa nº') ], [ $caixa_id ], [ new TLabel('Venda nº') ], [ $venda_id ] );
        $this->form->addFields( [ new TLabel('Valor Total') ], [ $mov_total ], [ new TLabel('Desconto') ], [ $mov_desconto ], [ new TLabel('Valor Lançado') ], [ $mov_valor ] );
        
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        // detail fields
        $this->fieldlist = new TFieldList;
        $this->fieldlist-> width = '100%';
        $this->fieldlist->enableSorting();
        
        $criteria = new TCriteria;
        $criteria->add(new TFilter('ativo', '=', 'N'), TExpression::OR_OPERATOR);
        $criteria->add(new TFilter('id', '>', 1), TExpression::OR_OPERATOR); 
        
        $forma_recebimento = new TDBUniqueSearch('list_servico_id[]', 'DBUNIDADE', 'FormaRecebimento', 'id', 'nome', null, $criteria);
        $forma_recebimento->setSize('100%');
        $forma_recebimento->setMinLength(0);

        $valor = new TNumeric('list_valor[]', 2, ',', '.');
        $valor->setSize('100%');

        $this->fieldlist->addField( '<b>Tipo</b>', $forma_recebimento, ['width' => '50%']);
        $this->fieldlist->addField( '<b>Valor</b>', $valor, ['width' => '50%']);

        $this->form->addField($forma_recebimento);
        $this->form->addField($valor);
        
        $detail_wrapper = new TElement('div');
        $detail_wrapper->add($this->fieldlist);
        $detail_wrapper->style = 'overflow-x:auto';
        
        $this->form->addContent( [ TElement::tag('h5', 'Lançamentos', [ 'style'=>'background: whitesmoke; padding: 5px; border-radius: 5px; margin-top: 5px'] ) ] );
        $this->form->addContent( [ $detail_wrapper ] );

        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
        // Botão para ação na tela
        //$this->form->addAction( _t('Save'),  new TAction( [$this, 'onSave'] ),  'fa:save green' );
        //$this->form->addAction( _t('Clear'), new TAction( [$this, 'onClear'] ), 'fa:eraser red' );
        

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
            //print_r($param);
            if (isset($param['key']))
            {
                $key = $param['key'];
                //print_r($param);
                $object = new CaixaMov($key);
                
                $object->mov_data = TDateTime::convertToMask($object->mov_data, 'yyyy-mm-dd', 'dd/mm/yyyy');
                $object->mov_baixa = TDateTime::convertToMask($object->mov_baixa, 'yyyy-mm-dd', 'dd/mm/yyyy');
                $this->form->setData($object);
                //print_r($object);
                
                $items  = CaixaMovPg::where('mov_id', '=', $key)->load();

                if ($items)
                {
                    $this->fieldlist->addHeader();
                    foreach( $items  as $item )
                    {
                        $detail = new stdClass;
                        $detail->list_servico_id = $item->forma_recebimento;
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
    public static function onSave($param)
    {
        try
        {
            TTransaction::open('DBUNIDADE');
            
            $id = (int) $param['id'];
            $master = new CaixaMov;
            $master->fromArray($param);
            //print_r($param);
            $master->mov_data = TDateTime::convertToMask($param['mov_data'], 'dd/mm/yyyy', 'yyyy-mm-dd');
            $master->mov_baixa = TDateTime::convertToMask($param['mov_baixa'], 'dd/mm/yyyy', 'yyyy-mm-dd');

            $valor_desconto = 0;
            $valor_fatura = 0;
            $fatura_total = 0;

            if (empty($master->id))
            {
                
            }

            if( !empty($master->mov_desconto) )
            {
                $valor_desconto = (float) str_replace(['.',','], ['','.'], $master->mov_desconto);
                $fatura_total   = (float) str_replace(['.',','], ['','.'], $master->mov_total);
                $valor_fatura   = $fatura_total - $valor_desconto;
                $valor_fatura   = number_format($valor_fatura, 2, ',', '.');
                $master->mov_valor = $valor_fatura;
            }
            else
            {
                $fatura_total   = (float) str_replace(['.',','], ['','.'], $master->mov_total);
                $valor_fatura   = $fatura_total;
                $valor_fatura   = number_format($valor_fatura, 2, ',', '.');
                $master->mov_valor = $valor_fatura;
            }

            $master->store(); // Store Contas Receber

            CaixaMovPg::where('mov_id', '=', $master->id)->delete();
            
            if( !empty($param['list_servico_id']) AND is_array($param['list_servico_id']) )
            {
                $total_lancamentos = 0;
                $total_lancamentos_r = 0;
                
                foreach( $param['list_servico_id'] as $row => $forma_recebimento)
                {
                    if (!empty($forma_recebimento))
                    {
                        $detail = new CaixaMovPg;
                        //print_r($detail);
                        $detail->mov_id = $master->id;
                        $detail->forma_recebimento = $param['list_servico_id'][$row];
                        $detail->valor = (float) str_replace(['.',','], ['','.'], $param['list_valor'][$row]);
                        $detail->store(); // Store Contas Receber PG
                    }
                    else
                    {
                        throw new Exception('<span style="color:red;font-weight: bold;">ERRO NA DIVISÃO DO RECEBIMENTO</span><br><span style="color:black;font-weight: bold;">FORMA DE RECEBIMENTO INCORRETA<BR>OU<BR>NÃO INSERIDA</span>');
                    }
                }

                if(!empty($param['list_servico_id'][0]))
                {
                    $total_lancamentos =  (float) array_sum( str_replace(['.',','], ['','.'], $param['list_valor']) );
                    $total_lancamentos_r = number_format($total_lancamentos, 2, ',', '.');

                    if($master->mov_valor == $total_lancamentos_r)
                    {
                        $master->mov_desconto   = str_replace(['.',','], ['','.'], $master->mov_desconto);
                        $master->mov_total      = str_replace(['.',','], ['','.'], $master->mov_total);
                        $master->mov_valor      = str_replace(['.',','], ['','.'], $master->mov_valor);
                        $master->store(); // Store Contas Receber
                        //print_r($master);
                        $data = new stdClass;
                        $data->id = $master->id;
                        TForm::sendData('form_CaixaFormPg', $data);
                        $pos_action = new TAction(['CaixaMovList', 'onReload'], ['register_state' => 'true']);
                        new TMessage('info', AdiantiCoreTranslator::translate('Record saved'), $pos_action);
                        parent::closeWindow();
                    }
                    else
                    {
                        throw new Exception('<span style="color:black;font-weight: bold;">VALOR TOTAL DAS PACERLAS</span><BR>NÃO BATE COM O VALOR TOTAL DO RECEBIMENTO');
                    }
                }
                else
                {
                    throw new Exception('<span style="color:black;font-weight: bold;">SELECIONE PELO MENOS UM TIPO DE RECEBIMENTO</span>');
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
        parent::closeWindow();
    }   
}
