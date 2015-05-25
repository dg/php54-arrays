PHP 5.4 Short Arrays Converter []
=================================

Command-line script to convert between `array()` and PHP 5.4's short syntax `[]`.

Usage:

	php convert.php <directory>               # converts to [] syntax in a whole directory recursively
	php convert.php --reverse <directory>     # converts to array() syntax
	php convert.php < input.php > output.php  # converts single file from STDIN
