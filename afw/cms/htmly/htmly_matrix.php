<?php
class HtmlyMatrix extends HtmlyElement
{
    private $cols_matrix;
    private $total_count;

    private $cells = array();

    /**
     * @param int $total_count
     * @return int
     */
    public static function intelligentColNumber($total_count)
    {
        // Placeholder implementation - replace with actual logic
        return max(1, round(sqrt($total_count / 3)));
    }

    /**
     * @param int $total_count
     * @param int $cols_matrix
     */
    public function __construct(
        $total_count,
        $cols_matrix = 0,
        $id = "",
        $name = "",
        $text_direction = '',
        $spceial_class = ""
    ) {
        if (!$cols_matrix) {
            $cols_matrix = HtmlyProcessResultMatrix::intelligentColNumber($total_count);
        }
        $this->cols_matrix = $cols_matrix;
        $this->total_count = $total_count;

        parent::__construct("div", true, $id, $name, $text_direction);

        $this->addClass("htmly-matrix");
        if ($spceial_class) {
            $this->addClass($spceial_class);
        }
    }


    public function getProposedNextCellId()
    {
        $next_id = count($this->cells);
        return "cell-" . $next_id;
    }

    /**
     * Adds a cell to the matrix.
     *
     * @param string $id
     * @param string $title
     * @param string $hint
     * @param string $link
     * @param string $special_class
     */
    public function addCell($id, $title, $hint, $link = "", $special_class = "")
    {
        if (($this->total_count > 0) and (count($this->cells) >= $this->total_count)) {
            throw new AfwRuntimeException("HtmlyMatrix : cannot add more than total_count elements : " . $this->total_count);
        }

        if (!$id) {
            $id = $this->getProposedNextCellId();
        }
        $this->cells[$id] = ['id' => $id, 'title' => $title, 'hint' => $hint, 'link' => $link, 'special_class' => $special_class];
        $this_id = $this->id;
        $cell_id = "matrix-" . $this_id . "-" . $id;
        if ($link) {
            $content = "<a href='$link' title='$hint'>$title</a>";
        } else {
            $content = "<span title='$hint'>$title</span>";
        }

        $cell = new HtmlyDiv($content, $cell_id, $cell_id, $special_class);

        return parent::addElement($cell);
    }
}
