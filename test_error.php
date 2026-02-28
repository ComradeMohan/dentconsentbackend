<?php
// test_error.php
require_once 'db_connect.php';

echo "Trying to include a missing file to test the error handler...\n";

// This will trigger a fatal error
require_once 'non_existent_file_to_test_error_handler.php';

echo "This will not be shown.";
