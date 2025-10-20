<?php
class AfwSqlHelper extends AFWRoot
{
    public static final function sqlInsertOrUpdate($table, $my_row, $pkCol_arr=null)
    {
        $my_row_cols = array_keys($my_row);
        $set_insert_cols = "";
        $set_update_cols = "";
        $pk_cols_where = "1";
        foreach($my_row_cols as $row_col)
        {
            $row_val = $my_row[$row_col];
            $set_insert_cols .= " $row_col='$row_val',";
            if($pkCol_arr)
            {
                if(!$pkCol_arr[$row_col]) $set_update_cols .= " $row_col='$row_val',";
                else $pk_cols_where = " AND $row_col='$row_val'";
            }
            
        }

        $set_insert_cols=trim($set_insert_cols,",");
        $set_update_cols=trim($set_update_cols,",");
        
        if($pkCol_arr)
        {
            $sql = "INSERT IGNORE INTO $table $set_insert_cols ON DUPLICATE KEY UPDATE $table $set_update_cols WHERE $pk_cols_where;";
        }
        else
        {
            $sql = "INSERT INTO $table $set_insert_cols;";
        }

        return $sql;

    }
        

    

}
