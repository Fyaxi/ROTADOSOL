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
class KanbanFaseForm extends TWindow
{
    protected $form; // form
    
    // trait with onSave, onClear, onEdit
    use Adianti\Base\AdiantiStandardFormTrait;
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        parent::setSize(400, null);
        parent::removePadding();
        parent::setTitle('Fase');
        
        $this->setDatabase(TSession::getValue('unit_database'));    // defines the database
        $this->setActiveRecord('Fase');   // defines the active record
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Fase');
        $this->form->setColumnClasses(2, ['col-sm-3','col-sm-9']);
        $this->form->enableCSRFProtection(); // ATIVA PROTEÇÃO CONTA EXECUÇÃO DE JAVA SCRIPT MALICIOSO
        
        // create the form fields
        $id     = new THidden('id');
        $projeto_id = new THidden('projeto_id');
        $nome = new TEntry('nome');
        $ordem  = new THidden('ordem');
        $id->setEditable(FALSE);
        
        $ordem->setValue(999);
        $projeto_id->setValue( TSession::getValue('projeto_id') );
        
        // add the form fields
        $this->form->addFields( [$id, $projeto_id] );
        $this->form->addFields( [new TLabel('Nome', 'red')], [$nome] );
        $this->form->addFields( [$ordem] );
        
        // define the form action
        $this->form->addAction(_t('Save'), new TAction(array($this, 'onSave')), 'fa:save green');
        
        $this->setAfterSaveAction( new TAction( ['KanbanView', 'onLoad'] ) );
        $this->setUseMessages(FALSE);
        
        TScript::create('$("body").trigger("click")');
        TScript::create('$("[name=nome]").focus()');
        
        parent::add($this->form);
    }
}
