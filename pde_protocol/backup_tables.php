<?php 
/* 
backup_tables.php v1.1a 20120131 - added DROP IF EXISTS

File taken from here : http://davidwalsh.name/backup-mysql-database-php
database backup function
Instructions :
Run it like this :
backup_tables("localhost","john","john","my_database",'*',"backup_dir/");
or
backup_tables('localhost','username','password','blog');

Change log:
 v1.1a 20120131 - added DROP IF EXISTS
1.0a : 
-Added UTF-8 support
-
*/


/* backup the db OR just a table */
function backup_tables($host,$user,$pass,$dbname,$tables = '*',$backup_subdir="")
{
  //$backup_subdir="backup_/"; // Must include trailing SLASH (eg "backup/". NOTE this MUST EXIST. Subdirectory where the backups will be saved
  
  $link = mysql_connect($host,$user,$pass);
  mysql_select_db($dbname,$link);
  @mysql_query("SET NAMES 'utf8'",$link);  //ADDED By JOHN	
  
  //get all of the tables
  if($tables == '*')
  {
    $tables = array();
    $result = mysql_query('SHOW TABLES');
    while($row = mysql_fetch_row($result))
    {
      $tables[] = $row[0];
    }
  }
  else
  {
    $tables = is_array($tables) ? $tables : explode(',',$tables);
  }
  
  //cycle through
  foreach($tables as $table)
  {
    $result = mysql_query('SELECT * FROM '.$table);
    $num_fields = mysql_num_fields($result);
    
    $return.= 'DROP TABLE IF EXISTS '.$table.';';
    $row2 = mysql_fetch_row(mysql_query('SHOW CREATE TABLE '.$table));
    $return.= "\n\n".$row2[1].";\n\n";
    
    for ($i = 0; $i < $num_fields; $i++) 
    {
      while($row = mysql_fetch_row($result))
      {
        $return.= 'INSERT INTO '.$table.' VALUES(';
        for($j=0; $j<$num_fields; $j++) 
        {
          $row[$j] = addslashes($row[$j]);
          $row[$j] = ereg_replace("\n","\\n",$row[$j]);
          if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
          if ($j<($num_fields-1)) { $return.= ','; }
        }
        $return.= ");\n";
      }
    }
    $return.="\n\n\n";
  }
  
  //save file
  $handle = fopen($backup_subdir.'db-backup-'.date("Ymd").'-'.time().'-'.(md5(implode(',',$tables))).'.sql','w+');
  fwrite($handle,$return);
  fclose($handle);
} //function backup_tables($host,$user,$pass,$dbname,$tables = '*')
//Run it like this :
//backup_tables("localhost","john","john","pde_protocol_production",'*',"backup_sql_/");

?>