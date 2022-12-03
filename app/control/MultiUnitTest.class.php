<?php
class MultiUnitTest extends TPage
{
    public function __construct()
    {
        parent::__construct();
        
        $conn = TTransaction::open('unit_database');
        
        echo '<pre>';
        var_dump( TDatabase::getData($conn, 'SELECT * FROM people') ); 
        echo '</pre>';
        
        TTransaction::close();
    }
}
