<?php


function sanitizeString($var){
    
    if(get_magic_quotes_gpc()) //get rid of unwanted slashes using magic_quotes_gpc
        $var= stripslashes($var);
    
    $var=  htmlentities($var,ENT_COMPAT, 'UTF-8'); //get rid of html entities e.g. &lt;b&gt;hi&lt;/b&gt; = <b>hi</b>
    $var= strip_tags($var); //get rid of html tags e.g. <b>
    return $var;
}

function sanitizeMYSQL($connection,$var){
    $var = mysqli_real_escape_string($connection,$var); //Escapes special characters in a string for use in an SQL statement
    $var=  sanitizeString($var);
    return $var;
}

function replace_html($html, $values) {
    foreach ($values as $key => $value)
        $html = str_replace("{{".$key."}}", $value, $html);
    return $html;
}

function make_row(&$row,&$result){
    $new_row=array();
    $num_fields=  mysqli_num_fields($result);
        for($i=0;$i<$num_fields;++$i){
        $finfo = mysqli_fetch_field_direct($result, $i);
        if($finfo->type>=249 && $finfo->type<=252) //is blob
            $new_row[$finfo->name]=base64_encode($row[$finfo->name]);
        else 
          $new_row[$finfo->name]=$row[$finfo->name];
    }
    return $new_row;
}

function convert_json($result){
    $rows=array();
  while($row = mysqli_fetch_array($result)) {
      $new_row=make_row($row,$result);
      $rows[] = $new_row;
  }
  return json_encode($rows);
}

?>
