<?php

function rs2json($rs, $moveFirst = false) {
  try {
  	if (!$rs) {
      return("[{\"error\":\"Bad result set.\"}]");
  	}
  
  	$output = '';
  	$rowOutput = '';
  	
  	$totalRows = $rs->numrows();
  	
  	if($totalRows > 0)
  	{
  		$output .= '[';
  		
  		$rowCounter = 1;
  		while ($row = $rs->FetchRow())
  		{
  			$rowOutput .= '{'; // . " id:" . $rowCounter . ", ";
  			
  			$cols = count($row);
  			$colCounter = 1;
  			// while (list($key,$val) = each($row)) {
  			foreach ($row as $key => $val)
  			{
  				$rowOutput .= '"' . $key . '":';
  				$rowOutput .= '"' . $val . '"';
  				
  				if ($colCounter != $cols)
  				{
  					$rowOutput .= ',';
  				}
  				$colCounter++;
  			}
  			
  			$rowOutput .= '}' . "\n";
  			
  			if ($rowCounter != $totalRows)
  			{
  				$rowOutput .= ',';
  			}
  			$rowCounter++;
  		}
  		$output .= $rowOutput . ']';
  	}	
  
  	if ($moveFirst)
  	{
  		$rs->MoveFirst();
  	}
  	return $output;
  } catch (exception $e) {
      $e = str_replace('"', "'", $e);
      return("[{\"error\":\"Exception in rs2json: $e.\"}]");
  } // try catch
} // rs2json


?>
