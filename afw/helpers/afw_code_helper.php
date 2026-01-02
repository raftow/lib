<?php
class AfwCodeHelper
{
    private static function cleanLineCode($line)
    {
        $line = str_replace("\n", '', $line);
        $line = strip_tags($line);

        return $line;
    }

    public static function showCodeLines($file_or_lines, $line = 0, $lines_before = 0, $lines_after = 0, $title = '', $language = 'php', $focus_class = 'line')
    {
        if (!is_array($file_or_lines)) {
            $file = $file_or_lines;
            if (file_exists($file)) {
                $lines = file($file);
                $title .= '<i>modified at ' . date('F d Y H:i:s.', filemtime($file)) . '</i>';
            }

            if ($line)
                $title .= "focus on line $line";
        } else {
            $file = '';
            if (!$title)
                $title = 'Some lines to show';
            $lines = $file_or_lines;
        }

        $html = '';
        $html .= "<code><p>$file $title</p></code>\n";
        $html .= "<ul class=\"code $language\">";

        if ($lines) {
            if ($lines_after)
                $lines_end = $line + $lines_after;
            else
                $lines_end = count(($lines)) - 1;

            if ($lines_before)
                $lines_start = $line - $lines_before;
            else
                $lines_start = 0;

            for ($i = $lines_start; $i <= $lines_end; $i++) {
                if ($i >= 0 && $i < count($lines)) {
                    $line_num = $i + 1;
                    if ($i == $line - 1)
                        $class_line_focus = $focus_class;
                    else
                        $class_line_focus = '';
                    $line_of_code = self::cleanLineCode($lines[$i]);
                    $html .= "<li class='$class_line_focus'><div class='linenum'>$line_num</div>$line_of_code</li>";
                }
            }
        } else {
            $html .= "<li class='empty'><div class='linenum'>00</div>Empty code</li>";
        }
        $html .= '</ul>';

        return $html;
    }
}
