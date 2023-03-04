<?php
require  '../config/Database.php';

   class CustomerCare {

    //connessione al database
    
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }
    
    public function SelectCalls() {
        $sql = 'select * from customerCare';
        
        //Preparo istruzione
        $stmt = $this->conn->prepare($sql);

        //Eseguo la query
        $stmt->execute();

        //formatto la data da formato database in italiano e assegno i risultati
        $control_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        //inizializzo una variabile per conservare i risultati
        $result = '';
        foreach($control_results as $control_results) {
            $date = $control_results['dataRicevuta'];
            $format_date = date('d/m/Y', $date);
        }
    }

    public function importCsv($input) 
    {
        print_r($input);
    }

   }