<?php
require_once('./includes/ClassesLoader.inc.php');
session_start();
(new Routeur)->router();