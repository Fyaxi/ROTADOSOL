<?php
/**
 * RelatClientes TSession::getValue('unit_database')
 *
 * @version    2.0
 * @package    Sistema Integrado
 * @subpackage Módulos
 * @author     Jairo Barreto
 * @copyright  Copyright (c) 2021 Nativus Tecnologia. (http://www.nativustecnologia.com.br)
 * 
 */
class RelatClientes extends TPage
{
    private $form; // form
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Customer_report');
        $this->form->setFormTitle('Tela Relat. Clientes');
        $this->form->setClientValidation(true);
        
        // create the form fields
        //$city_id      = new TDBUniqueSearch('city_id', 'samples', 'City', 'id', 'name');
        $city_id = new TDBUniqueSearch('city_id', TSession::getValue('unit_database'), 'Pessoa', 'id', 'nome_fantasia');
        $output_type  = new TRadioGroup('output_type');
        
        $this->form->addFields( [new TLabel('Cliente')],     [$city_id] );
        $this->form->addFields( [new TLabel('Relatório')],   [$output_type] );
        
        // define field properties
        $city_id->setSize( '80%' );
        $city_id->setMinLength(0);
        $output_type->setUseButton();
        $options = ['html' =>'HTML', 'pdf' =>'PDF', 'rtf' =>'RTF', 'xls' =>'XLS'];
        $output_type->addItems($options);
        $output_type->setValue('pdf');
        $output_type->setLayout('horizontal');
        $city_id->addValidation( 'City', new TRequiredValidator);
        $this->form->addAction( 'Generate', new TAction(array($this, 'onGenerate')), 'fa:download blue');
        
        // wrap the page content using vertical box
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add($this->form);
        
        parent::add($vbox);
    }

    /**
     * method onGenerate()
     * Executed whenever the user clicks at the generate button
     */
    function onGenerate()
    {
        try
        {
            // get the form data into an active record Customer
            $data = $this->form->getData();
            $this->form->setData($data);
            
            $format = $data->output_type;
            
            // open a transaction with database 'samples'
            $source = TTransaction::open(TSession::getValue('unit_database'));
            
            // define the query
            $query = "SELECT p.id as 'id',
                             p.nome_fantasia as 'nome_fantasia',
                             p.email as 'pessoa_email',
                             p.fone as 'pessoa_fone',
                             c.nome as 'cidade_nome'
                       FROM  pessoa p, cidade c
                      WHERE  p.cidade_id = c.id and p.id = :city_id";
            
            $rows = TDatabase::getData($source, $query, null, [ 'city_id' => $data->city_id ]);
            
            if ($rows)
            {
                $widths = array(40, 200, 80, 120, 80);
                
                switch ($format)
                {
                    case 'html':
                        $table = new TTableWriterHTML($widths);
                        break;
                    case 'pdf':
                        $table = new TTableWriterPDF($widths);
                        break;
                    case 'rtf':
                        $table = new TTableWriterRTF($widths);
                        break;
                    case 'xls':
                        $table = new TTableWriterXLS($widths);
                        break;
                }
                
                if (!empty($table))
                {
                    // create the document styles
                    $table->addStyle('header', 'Helvetica', '16', 'B', '#ffffff', '#4B8E57');
                    $table->addStyle('title',  'Helvetica', '10', 'B', '#ffffff', '#6CC361');
                    $table->addStyle('datap',  'Helvetica', '10', '',  '#000000', '#E3E3E3', 'LR');
                    $table->addStyle('datai',  'Helvetica', '10', '',  '#000000', '#ffffff', 'LR');
                    $table->addStyle('footer', 'Helvetica', '10', '',  '#2B2B2B', '#B5FFB4');
                    
                    $table->setHeaderCallback( function($table) {
                        $table->addRow();
                        $table->addCell('Relatórios Listagem de Clientes', 'center', 'header', 5);
                        
                        $table->addRow();
                        $table->addCell('ID',      'center', 'title');
                        $table->addCell('Nome',      'left', 'title');
                        $table->addCell('Cidade',  'center', 'title');
                        $table->addCell('Email',     'left', 'title');
                        $table->addCell('Telefone', 'center', 'title');
                    });
                    
                    $table->setFooterCallback( function($table) {
                        $table->addRow();
                        $table->addCell(date('Y-m-d h:i:s'), 'center', 'footer', 5);
                    });
                    
                    // controls the background filling
                    $colour= FALSE;
                    
                    // data rows
                    foreach ($rows as $row)
                    {
                        $style = $colour ? 'datap' : 'datai';
                        $table->addRow();
                        $table->addCell($row['id'],                     'center', $style);
                        $table->addCell($row['nome_fantasia'],          'left',   $style);
                        $table->addCell($row['cidade_nome'],            'center', $style);
                        $table->addCell($row['pessoa_email'],           'left',   $style);
                        $table->addCell($row['pessoa_fone'],            'center', $style);
                        
                        $colour = !$colour;
                    }
                    
                    $output = "app/output/tabular.{$format}";
                    
                    // stores the file
                    if (!file_exists($output) OR is_writable($output))
                    {
                        $table->save($output);
                        parent::openFile($output);
                    }
                    else
                    {
                        throw new Exception(_t('Permission denied') . ': ' . $output);
                    }
                    
                    // shows the success message
                    new TMessage('info', "Relatório gerado. Por favor, habilite popups no navegador. <br> <a href='$output'>Clique aqui para baixar</a>");
                }
            }
            else
            {
                new TMessage('error', 'No records found');
            }
    
            // close the transaction
            TTransaction::close();
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}
