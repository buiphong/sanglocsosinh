<?php

#### Roshan's very simple code to export data to excel   
#### Copyright reserved to Roshan Bhattarai - nepaliboy007@yahoo.com
#### if you find any problem contact me at http://roshanbh.com.np
#### fell free to visit my blog http://php-ajax-guru.blogspot.com

class ExportExcel
{
	//variable of the class
	var $titles=array();
	var $all_values=array();
	var $filename;
	var $colwidth = array();
	//functions of the class
	function ExportExcel($f_name) //constructor
	{
		$this->filename=$f_name;
	}
	function setHeadersAndValues($hdrs,$all_vals) //set headers and query
	{
		$this->titles=$hdrs;
		$this->all_values=$all_vals;
	}
    function setHeadersAndValuesAndColumnWidth($hdrs,$colwidth,$all_vals) //set headers and query
	{
	    $this->titles=$hdrs;
        $this->colwidth=$colwidth;
		$this->all_values=$all_vals;
	}
	function GenerateExcelFile1() //function to generate excel file
	{
		$header ="";
        $data="";
        $count =0;
		foreach ($this->titles as $title_val) 
 		{ 
 		     if($this->colwidth->size()>count)
             {
                	$header .= $title_val."\t"; 
             }
             else
 			    $header .=$title_val."\t"; 
            $count++;
 		} 
 		for($i=0;$i<sizeof($this->all_values);$i++) 
 		{ 
 			$line = ''; 
 			foreach($this->all_values[$i] as $value) 
			{ 
 				if ((!isset($value)) OR ($value == "")) 
				{ 
 					$value = "\t"; 
 				} //end of if
				else 
				{ 
 					$value = str_replace('"', '""', $value); 
 					$value = '"' . $value . '"' . "\t"; 
 				} //end of else
 				$line .= $value; 
 			} //end of foreach
 			$data .= trim($line)."\n"; 
 		}//end of the while 
 		$data = str_replace("\r", "", $data); 
		if ($data == "") 
 		{ 
 			$data = "\n(0) Records Found!\n"; 
 		} 
        
        header("Content-Description: File Transfer");
        header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
        header("Content-type: application/x-msexcel; charset=UTF-8");
        header("Content-Disposition: attachment; filename=$this->filename"); 
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        echo chr(255).chr(254).mb_convert_encoding("$header\n$data", 'UTF-16LE', 'UTF-8');
	
	}
    function GenerateExcelFile() //function to generate excel file
	{
	    $data = $this->GetTempPageExlsFile();
        $table ='<table width="100%" border="0" cellspacing="1" cellpadding="2" class="tbl_content">';
        $table.='<tr class="head" valign="bottom">';
        $count =0;
		foreach ($this->titles as $title_val) 
 		{ 
 		 if(sizeof($this->colwidth)>$count)
             {
 		         $table.='<td align="center" width="'.$this->colwidth[$count].'" height="22"><b>'.$title_val.'</b></td>';
             }
             else
             $table.='<td align="center" width="7%" height="22"><b>'.$title_val.'</b></td>';
             $count++;
		} 
        $table.="</tr>";
        
 		for($i=0;$i<sizeof($this->all_values);$i++) 
 		{ 
 			$table.="<tr>";
 			foreach($this->all_values[$i] as $value) 
			{ 
			     if($value!=null)
                    $value = str_replace('"', '""', $value); 
 				$table .= '<td>'.$value."</td>"; 
 			} //end of foreach
 		     $table.="</tr>";
 		}//end of the while 
        
 		$data = str_replace("\r", "", $data); 
		$data = str_replace("{data}", $table, $data);         
        header("Content-Description: File Transfer");
        header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
        header("Content-type: application/x-msexcel; charset=UTF-8");
        header("Content-Disposition: attachment; filename=$this->filename"); 
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        echo chr(255).chr(254).mb_convert_encoding("$data", 'UTF-16LE', 'UTF-8');
	
	}
	
	//phongbd
	function genExcelFile($title = '')
	{
		$data = $this->GetTempPageExlsFile();
			
		$table ='<table width="100%" border="0" cellspacing="1" cellpadding="2" class="tbl_content">';
		if ($title != '')
		{
			$table .= '<tr><td align="center" colspan="'.(sizeof($this->titles)).'"><b>'.$title.'</b></td></tr>';
		}
		$table.='<tr class="head" valign="bottom">';
		$count =0;
		foreach ($this->titles as $title_val)
		{
			if(sizeof($this->colwidth)>$count)
			{
				$table.='<td align="center" width="'.$this->colwidth[$count].'" height="22"><b>'.$title_val.'</b></td>';
			}
			else
				$table.='<td align="center" width="7%" height="22"><b>'.$title_val.'</b></td>';
			$count++;
		}
		$table.="</tr>";
		if (is_array($this->all_values[0]['content']))
		{
			foreach ($this->all_values as $key => $values)
			{
				$table .= '<tr>';
				//$table .= '<td align="center"><b>'.$key.'</b></td>';
				$table .= '<td colspan="'.(sizeof($this->titles)).'"><b>'.$values['title'].'</b></td>';
				$table .= '</tr>';
				foreach ($values['content'] as $v)
				{
					if (isset($v['title']) && isset($v['content']))
					{
						$table .= '<tr>';
						$table .= '<td colspan="'.(sizeof($this->titles)).'"><b>'.$v['title'].'</b></td>';
						$table .= '</tr>';
						foreach ($v['content'] as $v2)
						{
							$table.="<tr>";
							foreach ($v2 as $v3)
							{
								if($v3!=null)
									$v3 = str_replace('"', '""', $v3);
								$table .= '<td>'.$v3."</td>";
							}
							$table .= '</tr>';
						}
					}
					else 
					{
						$table.="<tr>";
						foreach ($v as $v2)
						{
							if($v2!=null)
								$v2 = str_replace('"', '""', $v2);
							$table .= '<td>'.$v2."</td>";
						}
						$table .= '</tr>';
					}
				}
			}
		}
		else
		{
			for($i=0;$i<sizeof($this->all_values);$i++)
			{
				$table.="<tr>";
				foreach($this->all_values[$i] as $value)
				{
					if($value!=null)
						$value = str_replace('"', '""', $value);
						$table .= '<td>'.$value."</td>";
				} //end of foreach
				$table.="</tr>";
			}//end of the while
		}
		//echo $table; die;
		$data = str_replace("\r", "", $data);
		$data = str_replace("{data}", $table, $data);
		header("Content-Description: File Transfer");
		header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
		header("Content-type: application/x-msexcel; charset=UTF-8");
		header("Content-Disposition: attachment; filename=$this->filename");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		echo chr(255).chr(254).mb_convert_encoding("$data", 'UTF-16LE', 'UTF-8');
	}
	
    function GetTempPageExlsFile()
    {
        $top = "<html xmlns:x=\"urn:schemas-microsoft-com:office:excel\">
            <style>
            
            @page
                {margin:1.0in .75in 1.0in .75in;
                mso-header-margin:.5in;
                mso-footer-margin:.5in;}
            tr
                {mso-height-source:auto;}
            col
                {mso-width-source:auto;}
            br
                {mso-data-placement:same-cell;
                }
            
            .style0
                {mso-number-format:General;
                text-align:general;
                vertical-align:bottom;
                white-space:nowrap;
                mso-rotate:0;
                mso-background-source:auto;
                mso-pattern:auto;
                color:windowtext;
                font-size:10.0pt;
                font-weight:400;
                font-style:normal;
                text-decoration:none;
                font-family:Arial;
                mso-generic-font-family:auto;
                mso-font-charset:0;
                border:none;
                mso-protection:locked visible;
                mso-style-name:Normal;
                mso-style-id:0;}
            td
                {mso-style-parent:style0;
                padding-top:1px;
                padding-right:1px;
                padding-left:1px;
                mso-ignore:padding;
                color:windowtext;
                font-size:10.0pt;
                font-weight:400;
                font-style:normal;
                text-decoration:none;
                font-family:Arial;
                mso-generic-font-family:auto;
                mso-font-charset:0;
                mso-number-format:General;
                text-align:general;
                vertical-align:bottom;
                border:none;
                mso-background-source:auto;
                mso-pattern:auto;
                mso-protection:locked visible;
                white-space:nowrap;
                mso-rotate:0;}
            .grids
                {mso-style-parent:style0;
                border:.5pt solid windowtext;}.head{
                font-weight:bold;
            }
            
            </style>
            <head>
            <!--[if gte mso 9]><xml>
            <x:ExcelWorkbook>
            <x:ExcelWorksheets>
            <x:ExcelWorksheet>
            <x:Name>Application List</x:Name>
            <x:WorksheetOptions>
            <x:Print>
            </x:Print>
            </x:WorksheetOptions>
            </x:ExcelWorksheet>
            </x:ExcelWorksheets>
            </x:ExcelWorkbook> 
            </xml>
            <![endif]--> 
            </head>
            <body>{data}</body></html>";
        return $top;
    } 

}
?>