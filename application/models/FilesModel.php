<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class FilesModel extends CI_Model {

    function __construct() {
        parent::__construct();
    }

	function select_history($currentpage=1,$postdata='')
	{
			$content=array();
			$query = $this->db->query("select * from fileHistory");
			$numrows = $query->num_rows();
			$rowsperpage = rowsperpage; 
			$totalpages = ceil($numrows / $rowsperpage);
			$url = '';
			if (isset($currentpage) && is_numeric($currentpage)) {
				 $currentpage = (int) $currentpage;
			} else {
				 $currentpage = 1;
					}
			if ($currentpage > $totalpages) {
				$currentpage = $totalpages;
				} 
			if ($currentpage < 1) { 
				$currentpage = 1;
				} 
			$offset = ($currentpage - 1) * $rowsperpage;
			$query = $this->db->query("select * from fileHistory LIMIT $offset, $rowsperpage");
			foreach ($query->result_array() as $row)
						{
						$content[] = $row;
						}
			$contents['content'] = $content;
			$contents['offset'] = $offset;
			return $contents;
	}

	function update_history($file,$status) {
			$date = date('Y-m-d H:i:s ');
			$query = $this->db->query("insert into fileHistory set Name = ".$this->db->escape($file).", status = ".$this->db->escape($status).", Date_added = ".$this->db->escape($date)."");	
			if($query)
				return 1;
			else
				return 0;
}
	
}	
