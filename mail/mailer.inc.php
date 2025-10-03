<?
function variable_get($name, $default = NULL) 
{
  return AfwSession::config($name, $default);
}

function trans($str)
{
  return $str;
}


/*
function variable_set($name, $value) {
  global $variables;

  $variables[$name] = $value;
}*/

?>