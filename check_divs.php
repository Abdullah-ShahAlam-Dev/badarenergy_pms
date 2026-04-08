<?php
$content = file_get_contents('d:/Ghufran PMS/pms/pms.badarexposolutions.com/resources/views/projects/ajax/overview.blade.php');
$open = substr_count($content, '<div');
$close = substr_count($content, '</div>');
echo "Open: $open, Close: $close\n";

// Count x-cards.data as well since they might contain divs
$open_card = substr_count($content, '<x-cards.data');
$close_card = substr_count($content, '</x-cards.data>');
echo "Open Card: $open_card, Close Card: $close_card\n";

// Count x-form
$open_form = substr_count($content, '<x-form');
$close_form = substr_count($content, '</x-form>');
echo "Open Form: $open_form, Close Form: $close_form\n";
