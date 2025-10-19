#!/usr/bin/env php
<?php
/**
 * Strip comments from PHP (in app/) and Blade/HTML (in resources/views/).
 * - PHP: uses token_get_all to safely remove T_COMMENT and T_DOC_COMMENT.
 * - Blade/HTML: remove {{-- --}} and <!-- --> blocks (not inside <script> tags).
 *
 * Usage (from project root):
 *   php scripts/strip_comments.php
 */

function stripPhpComments(string $code): string {
    $tokens = token_get_all($code);
    $out = '';
    foreach ($tokens as $token) {
        if (is_array($token)) {
            [$type, $text] = $token;
            if ($type === T_COMMENT || $type === T_DOC_COMMENT) {
                continue; // drop
            }
            $out .= $text;
        } else {
            $out .= $token;
        }
    }
    return $out;
}

function stripBladeHtmlComments(string $code): string {
    // Remove Blade comments {{-- ... --}}
    $code = preg_replace('/\{\{\-\-[\s\S]*?\-\-\}\}/', '', $code);
    // Remove HTML comments <!-- ... --> (non-greedy, across lines)
    $code = preg_replace('/<!--([\s\S]*?)-->/', '', $code);
    return $code;
}

function processDir(string $path, callable $processor, array $extensions): void {
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
    foreach ($rii as $file) {
        if ($file->isDir()) continue;
        $ext = strtolower(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
        if (!in_array($ext, $extensions, true)) continue;
        $full = $file->getPathname();
        $orig = file_get_contents($full);
        $processed = $processor($orig);
        if ($processed !== null && $processed !== $orig) {
            file_put_contents($full, $processed);
            echo "Updated: {$full}\n";
        }
    }
}

$root = getcwd();
$appPath = $root . DIRECTORY_SEPARATOR . 'app';
$viewsPath = $root . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views';

if (!is_dir($appPath) || !is_dir($viewsPath)) {
    fwrite(STDERR, "Run this from the project root.\n");
    exit(1);
}

processDir($appPath, 'stripPhpComments', ['php']);
processDir($viewsPath, 'stripBladeHtmlComments', ['php','blade.php','html','htm']);

echo "Done.\n";
