<?php

/**
 * Converts array() to [] used by PHP 5.4
 *
 * @author     David Grudl (http://davidgrudl.com)
 */

echo '
Convertor for PHP 5.4 arrays
----------------------------
';

if (!isset($_SERVER['argv'][1])) {
	die("Usage: {$_SERVER['argv'][0]} <directory>");
}

$dir = $_SERVER['argv'][1];
if (!is_dir($dir)) {
	echo "Directory $dir not found.\n";
	die(1);
}

foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir)) as $file) {
	if (!$file->isFile() || $file->getExtension() !== 'php') {
		continue;
	}
	echo $file;
	$orig = file_get_contents($file);
	$res = convertCode($orig);
	if ($orig !== $res) {
		echo " - converted";
		file_put_contents($file, $res);
	}
	echo "\n";
}


/**
 * Converts PHP code
 * @param  string
 * @return string
 */
function convertCode($code)
{
	$out = '';
	$brackets = array();
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
