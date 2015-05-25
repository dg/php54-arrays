PHP 5.4 Short Arrays Converter []
=================================

Command-line script to convert between `array()` and PHP 5.4's short syntax `[]` (and vice versa).

It uses native PHP tokenizer, so conversion is safe.
The script was successfully tested against thousands of PHP files.

Usage
-----

To convert all `*.php` and `*.phpt` files in whole directory recursively or to convert a single file use:

```
php convert.php <directory | file>
```

To convert source code from STDIN and print the output to STDOUT use:

```
php convert.php < input.php > output.php
```

To convert short syntax `[]` to older long syntax `array()` use option `--reverse`:

```
php convert.php --reverse <directory | file>
```
