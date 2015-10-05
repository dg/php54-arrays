<?php

/**
 * Converts between array() and [] used by PHP 5.4
 *
 * @author     David Grudl (https://davidgrudl.com)
 */

$args = $_SERVER['argv'];
$toOldSyntax = (isset($args[1]) && in_array($args[1], ['-r', '--reverse']));
$convert = $toOldSyntax ? 'convertSquareBracketsToArrays' : 'convertArraysToSquareBrackets';

if (isset($args[$tmp = $toOldSyntax ? 2 : 1])) {
	$path = $args[$tmp];

	if (is_file($path)) {
		$iterator = array($path);
	} elseif (is_dir($path)) {
		$iterator = new CallbackFilterIterator(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)), function($file) {
			return $file->isFile() && in_array($file->getExtension(), ['php', 'phpt', 'phtml'], TRUE);
		});
	} else {
		echo "Path $path not found.\n";
		die(1);
	}

	foreach ($iterator as $file) {
		echo $file;
		$orig = file_get_contents($file);
		$res = $convert($orig);
		if ($orig !== $res) {
			echo " (changed)";
			file_put_contents($file, $res);
		}
		echo "\n";
	}

} elseif (defined('STDIN') && (fstat(STDIN)['size'])) {
	$orig = file_get_contents('php://stdin');
	echo $convert($orig);

} else {
	echo "
Convertor for PHP 5.4 arrays
----------------------------
Usage: {$args[0]} [-r|--reverse] [<directory> | <file>] (or STDIN is used)
";
	die(1);
}


/**
 * Converts array() to []
 * @param  string
 * @return string
 */
function convertArraysToSquareBrackets($code)
{
	$out = '';
	$brackets = [];
	$tokens = token_get_all($code);

	for ($i = 0; $i < count($tokens); $i++) {
		$token = $tokens[$i];
		if ($token === '(') {
			$brackets[] = FALSE;

		} elseif ($token === ')') {
			$token = array_pop($brackets) ? ']' : ')';

		} elseif (is_array($token) && $token[0] === T_ARRAY) {
			$a = $i + 1;
			if (isset($tokens[$a]) && $tokens[$a][0] === T_WHITESPACE) {
				$a++;
			}
			if (isset($tokens[$a]) && $tokens[$a] === '(') {
				$i = $a;
				$brackets[] = TRUE;
				$token = '[';
			}
		}
		$out .= is_array($token) ? $token[1] : $token;
	}
	return $out;
}

/**
 * Converts [] to array()
 * @param  string
 * @return string
 * @author Honza Novák (http://honzanovak.com)
 */
function convertSquareBracketsToArrays($code)
{
	$out = '';
	$brackets = [];
	$ignoreBracket = FALSE;
	foreach (token_get_all($code) as $token) {
		if ($token === '[') {
			$brackets[] = !$ignoreBracket;
			$token = $ignoreBracket ? '[' : 'array(';
		} elseif ($token == ']'){
			$token = array_pop($brackets) ? ')' : ']';
		}
		if (!is_array($token) || $token[0] !== T_WHITESPACE) {
			$ignoreBracket = (in_array($token, [')', ']', '}'])
				|| (is_array($token) && in_array($token[0], [T_VARIABLE, T_STRING, T_STRING_VARNAME])));
		}
		$out .= is_array($token) ? $token[1] : $token;
	}
	return $out;
}
