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
class FormaPagamentoForm extends TPage
{
    protected $form; // form
    
    use Adianti\Base\AdiantiStandardFormTrait; // Standard form methods
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        
        parent::setTargetContainer('adianti_right_panel');
        $this->setAfterSaveAction( new TAction(['FormaPagamentoList', 'onReload'], ['register_state' => 'true']) );
        
        $this->setDatabase(TSession::getValue('unit_database'));              // defines the database
        $this->setActiveRecord('FormaPagamento');     // defines the active record
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_FormaPagamento');
        $this->form->setFormTitle('Forma de Pagamento');
        $this->form->setClientValidation(true);
        $this->form->setColumnClasses( 2, ['col-sm-5 col-lg-4', 'col-sm-7 col-lg-8'] );
        $this->form->enableCSRFProtection(); // ATIVA PROTEÇÃO CONTA EXECUÇÃO DE JAVA SCRIPT MALICIOSO

        // create the form fields
        $id = new TEntry('id');
        $nome = new TEntry('nome');
        $ContaFinanceiraId = new TDBUniqueSearch('ContaFinanceiraId', 'DBUNIDADE', 'ContaFinanceira', 'id', 'nome');
        $ativo = new TRadioGroup('ativo');

        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] );
        $this->form->addFields( [ new TLabel('Conta') ], [ $ContaFinanceiraId ] );
        $this->form->addFields( [ new TLabel('Ativo') ], [ $ativo ] );

        $nome->addValidation('Nome', new TRequiredValidator);
        $ContaFinanceiraId->addValidation('Conta Financeira', new TRequiredValidator);
        $ativo->addValidation('Ativo', new TRequiredValidator);


        // set sizes
        $id->setSize('100%');
        $nome->setSize('100%');
        $ativo->setSize('100%');
        $ativo->addItems( ['Y' => 'Sim', 'N' => 'Não'] );
        $ativo->setLayout('horizontal');

        $ContaFinanceiraId->setSize('100%');
        $ContaFinanceiraId->setMinLength(0);

        $nome->forceUpperCase();

        $id->setEditable(FALSE);
        
        // create the form actions
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'),  new TAction([$this, 'onEdit']), 'fa:eraser red');
        
        $this->form->addHeaderActionLink( _t('Close'), new TAction([$this, 'onClose']), 'fa:times red');
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        
        parent::add($container);
    }
    
    /**
     * Close side panel
     */
    public static function onClose($param)
    {
        TScript::create("Template.closeRightPanel()");
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

                $object = new FormaPagamento($key);

                if($object->id == 1)
                {
                    TEntry::disableField('form_FormaPagamento', 'nome');
                    TRadioGroup::disableField('form_FormaPagamento', 'ativo');
                }

                $this->form->setData($object);
            }

            TTransaction::close(); // close transaction 
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        } 
    }
}
