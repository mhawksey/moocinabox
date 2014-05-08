<?php

$title = (isset($this->build->_the_title)) ? $this->build->_the_title : $this->get_the_title();
$title = trim($title);
$box   = str_repeat( '~', strlen(utf8_decode($title)) );
echo "* $box *\n! $title !\n* $box *\n";

echo mysql2date(get_option( 'date_format' ), current_time('mysql'));

echo "\n\n";

if (isset($this->build->_the_content)) echo $this->build->_the_content; else $this->the_content();

echo "\n";

if (isset($this->build->_the_actions)) echo $this->build->_the_actions;

