<?
function variable_get($name, $default = NULL) 
{
  return AfwSession::config($name, $default);
}

/*
function variable_set($name, $value) {
  global $variables;

  $variables[$name] = $value;
}*/

?>