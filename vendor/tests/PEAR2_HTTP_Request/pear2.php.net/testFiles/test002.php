<?php
// echo back the posted body

$input = file_get_contents("php://input");
echo $input;
