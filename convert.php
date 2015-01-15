<?php

/**
 * Converts between array() and [] used by PHP 5.4
 *
 * @author     David Grudl (http://davidgrudl.com)
 */

echo '
Convertor for PHP 5.4 arrays
----------------------------
';

$args = $_SERVER['argv'];
$toOldSyntax = (isset($args[1]) && in_array($args[1], ['-r', '--reverse']));

if (!isset($args[1]) || ($toOldSyntax && !isset($args[2]))) {
	die("Usage: {$args[0]} [-r|--reverse] <directory>\n");
}

$dir = $toOldSyntax ? $args[2] : $args[1];
if (!is_dir($dir)) {
	echo "Directory $dir not found.\n";
	die(1);
}

foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir)) as $file) {
	if (!$file->isFile() || !in_array($file->getExtension(), ['php', 'phpt'], TRUE)) {
		continue;
	}
	echo $file;
	$orig = file_get_contents($file);
	if (!$toOldSyntax) {
		$res = convertArraysToSquareBrackets($orig);
	} else {
		$res = convertSquareBracketsToArrays($orig);
	}
	if ($orig !== $res) {
		echo " - converted";
		file_put_contents($file, $res);
	}
	echo "\n";
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
