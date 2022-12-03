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
class PagarView extends TWindow
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
        $this->form = new BootstrapFormBuilder('form_ContaPagarPgViewView');
        $this->form->setFormTitle('Conta a Pagar');
        $this->form->setClientValidation(true);
        $this->form->enableCSRFProtection(); // ATIVA PROTEÇÃO CONTA EXECUÇÃO DE JAVA SCRIPT MALICIOSO
        
        // master fields
        $id = new TEntry('id');
        $pessoa_id = new TDBUniqueSearch('pessoa_id', 'DBUNIDADE', 'Pessoa', 'id', 'nome_fantasia');
        $dt_emissao = new TDate('dt_emissao');
        $dt_vencimento = new TDate('dt_vencimento');
        $dt_pagamento = new TDate('dt_pagamento');
        $valor = new TEntry('valor');
        $desconto = new TEntry('desconto');
        $valor_total = new TEntry('valor_total');
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
        $pessoa_id->addValidation('Pessoa Id', new TRequiredValidator);
        $valor_total->addValidation('Valor Total', new TRequiredValidator);

        $dt_emissao->setMask('dd/mm/yyyy');
        $dt_emissao->setDatabaseMask('yyyy-mm-dd');
        
        $dt_vencimento->setMask('dd/mm/yyyy');
        $dt_vencimento->setDatabaseMask('yyyy-mm-dd');
        
        $dt_pagamento->setMask('dd/mm/yyyy');
        $dt_pagamento->setDatabaseMask('yyyy-mm-dd');

        $dt_emissao->setValue(date('Y-m-d'));
        $pessoa_id->setMinLength(0);

        $id->setEditable(FALSE);
        $valor->setEditable(FALSE);
        $desconto->setEditable(FALSE);
        $dt_pagamento->setEditable(FALSE);
        $dt_emissao->setEditable(FALSE);
        $dt_vencimento->setEditable(FALSE);
        $obs->setEditable(FALSE);
        $valor->setEditable(FALSE);
        $valor_total->setEditable(FALSE);
        $pessoa_id->setEditable(FALSE);
        $desconto->setEditable(FALSE);
        
        // add form fields to the form
        $this->form->addFields( [new TFormSeparator('<small>Informações Do Cliente</small>')] );
        $row = $this->form->addFields( [ new TLabel('Recebimento N°') ], [ $id ] );
        $row->layout = ['col-sm-2 control-label', 'col-sm-2' ];
        
        $this->form->addFields( [ new TLabel('Pessoa') ], [ $pessoa_id ] );

        $this->form->addFields( [new TFormSeparator('<small>Informações Do Recebimento</small>')] );
        $this->form->addFields( [ new TLabel('Data Emissão') ], [ $dt_emissao ], [ new TLabel('Data Vencimento') ], [ $dt_vencimento ], [ new TLabel('Data Pagamento') ], [ $dt_pagamento ] );
        $this->form->addFields( [new TFormSeparator('')] );
        $this->form->addFields( [ new TLabel('Valor Total') ], [ $valor_total ], [ new TLabel('Desconto Concedido') ], [ $desconto ], [ new TLabel('Valor a Receber') ], [ $valor ]  );
        $this->form->addFields( ['<br>'] );
        $this->form->addFields( [ new TLabel('Observação') ], [ $obs ] );
        
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
        $this->form->addAction( 'Voltar',  new TAction( [$this, 'onClose'] ),  'fa:arrow-alt-circle-left green' );
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
    function onView($param)
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

                TEntry::disableField('form_ContaReceberPg', 'valor');
                TEntry::disableField('form_ContaReceberPg', 'desconto');
                TEntry::disableField('form_ContaReceberPg', 'valor_total');

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
     * Close
     */
    public static function onClose($param)
    {
        parent::closeWindow();
    }   
}
