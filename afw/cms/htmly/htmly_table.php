<?php

class HtmlyTable extends HtmlyElement
{
    private $nb_rows_repeat_header = -99;
    private $add_footer = false;
    private $showAsDataTable = false;
    private $order_key = "";
    private $row_class_key = "";
    private $text_direction = 'rtl';
    private $header_trad = [];
    private $data_arr = [];

    public function __construct(
        $id = "",
        $name = "",
        $add_footer = false,
        $showAsDataTable = false,
        $nb_rows_repeat_header = -99,
        $header_trad = [],
        $data_arr = [],
        $row_class_key = "",
        $order_key = "",
        $text_direction = 'rtl'
    ) {
        $this->nb_rows_repeat_header = $nb_rows_repeat_header;
        $this->add_footer = $add_footer;
        $this->header_trad = $header_trad;
        $this->data_arr = $data_arr;
        $this->row_class_key = $row_class_key;
        $this->order_key = $order_key;
        $this->text_direction = $text_direction;
        $this->showAsDataTable = $showAsDataTable;
        parent::__construct("table", false, $id, $name);
    }

    protected function renderSpecialHtml()
    {
        $html = "";
        $count_header = count($this->header_trad);
        $the_header = '';
        if ($count_header > 0) {
            $the_header .= "   <thead>\n";
            $the_header .= "   <tr>\n";
            foreach ($this->header_trad as $nom_col => $trad_col) {
                $width_th = '';
                $the_header .= "      <th class='th-$nom_col' $width_th align='center'>$trad_col</th>\n";
            }
            $the_header .= "   </tr>\n";
            $the_header .= "   </thead>\n";
        }

        $html .= $the_header;
        $ids = '';

        $sum_cols_total = [];
        $my_class_name = '';
        $cl_tr = '';
        $old_cl = '';
        $rows_count_table = 0;
        $previous_tuple = null;
        foreach ($this->data_arr as $id => $tuple) {
            $row_class_css = "";
            if ($this->row_class_key) {
                $valcss = $tuple['ca-' . $this->row_class_key] ?? $tuple[$this->row_class_key];
                $row_class_key_val = '' . $valcss;
                $row_class_key_val = str_replace('-', '_', $row_class_key_val);
                $row_class_css .= ' csr_' . $this->row_class_key . ' hzm_row_' . $row_class_key_val;
            } else {
                $row_class_css .= ' hzm_row_std';
            }
            if ($ids) {
                $ids .= ',';
            }
            $ids .= $id;
            $old_cl = $cl_tr;
            if ($cl_tr == "odd") {
                $cl_tr = "even";
            } else {
                $cl_tr = "odd";
            }
            
            if ($this->order_key)
                $order = $tuple[$this->order_key];
            elseif ($tuple['id'])
                $order = $tuple['id'];
            else
                $order = $id;
            $myTr = "";
            $colspan = 0;
            $myTr .= "   <tr id='tr-object-$order' class='ky$this->order_key $cl_tr $row_class_css' alt='old_cl=$old_cl'>\n";

            foreach ($this->header_trad as $nom_col => $label) {
                $val_col = $tuple[$nom_col];
                
                $myTr .= "         <td id='row-$order' class='text_$this->text_direction $nom_col'>$val_col</td>\n";
                $colspan++;                
            }
            $myTr .= "   </tr>\n";
            $html .= $myTr;
            $rows_count_table++;
            if ((!$this->showAsDataTable) and ($rows_count_table == $this->nb_rows_repeat_header)) {
                $html .= "\n</tbody>\n";

                $html .= $the_header;
                $html .= '<tbody>';
                $rows_count_table = 0;
            }
            // $previous_tuple = $tuple;
        }

        

        $html .= '</tbody>';
        
        
    }

    /**
     * @return string
     */

    protected function renderSpecialHtmlSuffix() {
        $html = "";

        if ($this->showAsDataTable and !$this->id) {
            throw new AfwRuntimeException("To be able to show this html table as DataTable, please define the id attribute");
        }

        if ($this->showAsDataTable and $this->id) 
        {
            $html .= "<script type=\"text/javascript\">
\$(document).ready(function() {
    \$('#$this->id').DataTable({
        pagingType: \"full_numbers\",
        pageLength: 25,
        lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, \"All\"]
        ]
    });
});
</script>";
        } else {
            $html .= '<!-- show As Data Table off -->';
        }
    }
}
