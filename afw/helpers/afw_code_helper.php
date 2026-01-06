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

    public static function generatePhpFile($module_code, $fileName, $php, $subFolder)
    {
        $php_generation_folder = AfwSession::config("php_generation_folder", "C:/gen/php");
        $dir_sep = AfwSession::config("dir_sep", "/");
        $root_www_path = AfwSession::config("root_www_path", AfwSession::config("parent_project_path", "C:/dev-folder"));
        $merge_tool = AfwSession::config("merge_tool", "ex winmerge");
        $mv_command = AfwSession::config("mv_command", "mv ");
        $command_lines_arr = [];
        if ($php_generation_folder != "no-gen") {
            $generated_fileName = $php_generation_folder . $dir_sep . $fileName;
            try {
                AfwFileSystem::write($generated_fileName, $php);
            } catch (Exception $e) {
                $command_lines_arr[] = AfwUtils::hzm_format_command_line("error", "failed to write php file $generated_fileName\n");
            } finally {
                $root_module_path = $root_www_path . $dir_sep . $module_code;
                if ($subFolder) $root_module_path .= $dir_sep . $subFolder;
                $destination_fileName = $root_module_path . $dir_sep . $fileName;
                $command_lines_arr[] = AfwUtils::hzm_format_command_line("info", "php file $fileName has been generated under $php_generation_folder \n");
                $command_lines_arr[] = AfwUtils::hzm_format_command_line("info", "  to install the file :");
                $command_lines_arr[] = AfwUtils::hzm_format_command_line("info", "  if the file is not new use your merge tool $merge_tool and do the following command to merge manually : ");
                $command_lines_arr[] = AfwUtils::hzm_format_command_line("help", "  $merge_tool $generated_fileName $destination_fileName <br>\n");
                $command_lines_arr[] = AfwUtils::hzm_format_command_line("info", "  if the file not do the following command line manually : ");
                $command_lines_arr[] = AfwUtils::hzm_format_command_line("help-mv", "  $mv_command$generated_fileName $root_module_path <br>\n");
                $mv_command_line = "$mv_command$generated_fileName $root_module_path";
            }
        } else {
            $command_lines_arr[] = AfwUtils::hzm_format_command_line("warning", "  file generation disable");
        }

        return [$command_lines_arr, $mv_command_line];
    }
}
