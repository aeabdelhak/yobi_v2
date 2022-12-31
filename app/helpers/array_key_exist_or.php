<?php

function array_key_exist_or(string $key, array $array,$replacer){
   return array_key_exists($key,$array) ? $array[$key] : $replacer;
}