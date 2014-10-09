<?php
include "php/config.php";
require "lib/Moove.php";

$moove = new Moove(PDO_DATA_SOURCE_NAME);

$moove->upgradeDatabase();