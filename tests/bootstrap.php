<?php
require_once __DIR__ . '/../bootstrap.php';

# Testing Db
Model::$db = $mongo->selectDB('blog_test');
