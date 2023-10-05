<?php
	class AfwBatchColors 
	{
		private static $foreground_colors = array();
		private static $background_colors = array();

		private static $singleton = null;

		public function __construct() {
			// Set up shell colors
			self::$foreground_colors['black'] = '0;30';
			self::$foreground_colors['dark_gray'] = '1;30';
			self::$foreground_colors['blue'] = '0;34';
			self::$foreground_colors['light_blue'] = '1;34';
			self::$foreground_colors['green'] = '0;32';
			self::$foreground_colors['light_green'] = '1;32';
			self::$foreground_colors['cyan'] = '0;36';
			self::$foreground_colors['light_cyan'] = '1;36';
			self::$foreground_colors['red'] = '0;31';
			self::$foreground_colors['light_red'] = '1;31';
			self::$foreground_colors['purple'] = '0;35';
			self::$foreground_colors['light_purple'] = '1;35';
			self::$foreground_colors['brown'] = '0;33';
			self::$foreground_colors['yellow'] = '1;33';
			self::$foreground_colors['light_gray'] = '0;37';
			self::$foreground_colors['white'] = '1;37';

			self::$background_colors['black'] = '40';
			self::$background_colors['red'] = '41';
			self::$background_colors['green'] = '42';
			self::$background_colors['yellow'] = '43';
			self::$background_colors['blue'] = '44';
			self::$background_colors['magenta'] = '45';
			self::$background_colors['cyan'] = '46';
			self::$background_colors['light_gray'] = '47';
		}

		public static function singleton()
		{
			if(!self::$singleton)
			{
				self::$singleton = new AfwBatchColors();
			}

			return self::$singleton;
		}
                
                public static function rgba_color($color)
                {
                       if($color=="black") return "#000";
                       elseif($color=="yellow") return "#ffff80";
                       elseif($color=="white") return "#fff";
                       elseif($color=="dark_gray") return "#555";
                       elseif($color=="light_blue") return "#88f";
                       elseif($color=="light_green") return "#8f8";
                       elseif($color=="light_cyan") return "#8ff";
                       elseif($color=="light_red") return "#f88";
                       elseif($color=="light_purple") return "#ff8";
                       elseif($color=="light_gray") return "#ddd";
                       
                       return $color;
                }

		// Returns colored string
		public function getColoredString($string, $foreground_color = null, $background_color = null) {
			$colored_string = "";

			// Check if given foreground color found
			if ($foreground_color and isset(self::$foreground_colors[$foreground_color])) {
				$colored_string .= "\033[".self::$foreground_colors[$foreground_color] . "m";
			}
			// Check if given background color found
			if ($background_color and isset(self::$background_colors[$background_color])) {
				$colored_string .= "\033[".self::$background_colors[$background_color] . "m";
			}

			// Add string and end coloring
			$colored_string .=  " '> " . $string . "\033[0m"; /* "[**** $foreground_color] " .*/ 

			return $colored_string;
		}
                
                public static function coloredStringToHtml($colored_string)
                {
                        $html_str = $colored_string;
                        $html_str = str_replace("\033[0m", "</div>", $html_str);
                        $html_str = str_replace("\033", "<div style='", $html_str);
                        /*
                        foreach(self::$background_colors as $color => $scolor)
                        {
                             $html_str = str_replace("[" . $scolor . "m", "background-color:".self::rgba_color($color), $html_str);
                        }*/
                        
                        foreach(self::$foreground_colors as $color => $scolor)
                        {
                             $html_str = str_replace("[" . $scolor . "m", "color:".self::rgba_color($color), $html_str);
                        }
                        
                        return $html_str;
                }
                
                public function getColoredHtml($html, $foreground_color = null, $background_color = null) {
                   if($foreground_color) $foreground_color_css = self::rgba_color($foreground_color);
                   else $foreground_color_css = "#000";
                   if($background_color) $background_color_css = self::rgba_color($background_color);
                   else $background_color_css = "#fff";
                   $html = str_replace('[color]', $foreground_color_css, $html);
                   $html = str_replace('[bgcolor]', $background_color_css, $html);
                   
                   return $html; 
                }

		// Returns all foreground color names
		public function getForegroundColors() {
			return array_keys(self::$foreground_colors);
		}

		// Returns all background color names
		public function getBackgroundColors() {
			return array_keys(self::$background_colors);
		}
	}

?>