<?php
/**
 * WelcomeView
 *
 * @version    1.0
 * @package    control
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class Teste extends TPage
{
    /**
     * Class constructor
     * Creates the page
     */
    function __construct()
    {
        parent::__construct();
        
        // create the form
        $this->form = new BootstrapFormBuilder('form_bmi');
        $this->form->setFormTitle('Client interactions');
        
        // create the form fields
        $mass   = new TEntry('mass');
        $height = new TEntry('height');
        $result = new TEntry('result');

        $mass->setNumericMask(2, ',', '.', true);
        $height->setNumericMask(2, ',', '.', true);
        $result->setNumericMask(2, ',', '.', true);
        
        $result->setEditable(FALSE);
        
        // add the fields inside the form
        $this->form->addFields( [new TLabel('Mass (Kg)')],  [$mass] );
        $this->form->addFields( [new TLabel('Height (m)')], [$height] );
        $this->form->addFields( [new TLabel('Result')],     [$result] );
        
        $mass->onBlur   = 'calculate_bmi()';
        $height->onBlur = 'calculate_bmi()';
        
        TScript::create('calculate_bmi = function() {
            if (parseFloat(document.form_bmi.mass.value) > 0 && parseFloat(document.form_bmi.height.value) > 0)
            {
                form_bmi.result.value = parseFloat(form_bmi.mass.value) - parseFloat(form_bmi.height.value);
            }
        };');
        
        // wrap the page content using vertical box
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        //$vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add($this->form);
        parent::add($vbox);
    }
}
