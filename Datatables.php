<?php

require_once 'cnx.php';

Class DataTables{
    private $daw = null;
    private $start = null;
    private $length = null;
    private $search_key = null;
    private $order_column_index = null;
    private $order_column = null;
    
    private $list_columns = null;
    private $query = null;
    private $filters = null;
    
    private $data = null;
    private $dict = null;
    
    private $cnx;
    function init($post, $query, $list_columns){
        $this->daw = $post['draw'];
        $this->start = $post['start'];
        $this->length = $post['length'];
        $this->search_key = $post['search']['value'];
        $this->order_column_index = $post['order'][0]['column'];
        $this->order_column = $post['order'][0]['dir'];

        $this->list_columns = $list_columns;
        $this->query = $query;
        $this->filters = '';

        $this->data = array();
        $this->dict = array();

        $this->cnx = (new Cnx())->connection();
        
        $this->process($post);
    }
    function process($post){
        if($this->search_key){
            if(!strstr(strtoupper($this->query), 'WHERE')){
                $this->filters = ' WHERE ';
                foreach($this->list_columns as $cols){
                    $this->filters .= $cols . ' = ' . $this->search_key . ' OR ';
                } 
            }
            $res = $this->cnx->query($this->query . rtrim($this->filters, " OR ") . ' LIMIT ' . '10');
        }else{
            $column_to_oder = $this->list_columns[$this->order_column_index];
            $order = $this->order_column == 'asc' ? $column_to_oder . ' ASC' : $column_to_oder . ' DESC';
            $res = $this->cnx->query($this->query . ' ORDER BY ' . $order . ' LIMIT ' . $this->length . ' OFFSET ' . $this->start);
        }
        if(!empty($res) && $res->num_rows > 0){
            while($row = $res->fetch_assoc()){
                $this->data[] = $row;
            }
        }
        $this->returnData();
    }
    function returnData(){
        $result = $this->cnx->query($this->query);
        $count_records = $result->num_rows;
        $this->dict['recordsTotal'] = $count_records ? $count_records : 0; 
        $this->dict['recordsFiltered'] = $count_records ? $count_records : 0;
        $this->dict['draw'] = $this->daw;
        $this->dict['data'] = $this->data;
        echo json_encode($this->dict);
        $this->cnx->close();
    }
}