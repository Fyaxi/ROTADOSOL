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
class EstoqueForm extends TPage
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
        $this->setAfterSaveAction( new TAction(['EstoqueList', 'onReload'], ['register_state' => 'true']) );

        $this->setDatabase(TSession::getValue('unit_database'));              // defines the database
        $this->setActiveRecord('Produto');     // defines the active record
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Estoque');
        $this->form->setFormTitle('Produto');
        $this->form->setClientValidation(true);
        $this->form->setColumnClasses( 2, ['col-sm-5 col-lg-4', 'col-sm-7 col-lg-8'] );
        $this->form->enableCSRFProtection(); // ATIVA PROTEÇÃO CONTA EXECUÇÃO DE JAVA SCRIPT MALICIOSO
        

        // create the form fields
        $id = new TEntry('id');
        $description = new TEntry('description');
        $stock = new TEntry('stock');


        // add the fields
        $this->form->addFields( [ new TLabel('Nº') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Nome') ], [ $description ] );
        $this->form->addFields( [ new TLabel('Estoque') ], [ $stock ] );

        $description->addValidation('Nome', new TRequiredValidator);
        $stock->addValidation('Valor', new TRequiredValidator);


        // set sizes
        $id->setSize('100%');
        $description->setSize('100%');
        $stock->setSize('100%');

        $description->setEditable(false);
        
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
}
