<?php
function site_url()
{
    return rtrim(getenv('SITE_URL'), '/') . '/';
}

function site_path()
{
    static $_path;

    if (!$_path) {
        $_path = rtrim(parse_url(getenv('SITE_URL'), PHP_URL_PATH), '/');
    }

    return $_path;
}

function normalizeChars()
{
    return array(
        'ï¿½' => 'S', 'ï¿½' => 's', 'ï¿½' => 'Dj', 'ï¿½' => 'Z', 'ï¿½' => 'z', 'ï¿½'  => 'A', 'ï¿½' => 'A', 'ï¿½' => 'A', 'ï¿½' => 'A', 'ï¿½' => 'A',
        'ï¿½' => 'A', 'ï¿½' => 'A', 'ï¿½' => 'C', 'ï¿½'  => 'E', 'ï¿½' => 'E', 'ï¿½'  => 'E', 'ï¿½' => 'E', 'ï¿½' => 'I', 'ï¿½' => 'I', 'ï¿½' => 'I',
        'ï¿½' => 'I', 'ï¿½' => 'N', 'ï¿½' => 'O', 'ï¿½'  => 'O', 'ï¿½' => 'O', 'ï¿½'  => 'O', 'ï¿½' => 'O', 'ï¿½' => 'O', 'ï¿½' => 'U', 'ï¿½' => 'U',
        'ï¿½' => 'U', 'ï¿½' => 'U', 'ï¿½' => 'Y', 'ï¿½'  => 'B', 'ï¿½' => 'Ss', 'ï¿½' => 'a', 'ï¿½' => 'a', 'ï¿½' => 'a', 'ï¿½' => 'a', 'ï¿½' => 'a',
        'ï¿½' => 'a', 'ï¿½' => 'a', 'ï¿½' => 'c', 'ï¿½'  => 'e', 'ï¿½' => 'e', 'ï¿½'  => 'e', 'ï¿½' => 'e', 'ï¿½' => 'i', 'ï¿½' => 'i', 'ï¿½' => 'i',
        'ï¿½' => 'i', 'ï¿½' => 'o', 'ï¿½' => 'n', 'ï¿½'  => 'o', 'ï¿½' => 'o', 'ï¿½'  => 'o', 'ï¿½' => 'o', 'ï¿½' => 'o', 'ï¿½' => 'o', 'ï¿½' => 'u',
        'ï¿½' => 'u', 'ï¿½' => 'u', 'ï¿½' => 'u', 'ï¿½'  => 'y', 'ï¿½' => 'y', 'ï¿½'  => 'b', 'ï¿½' => 'y', 'ï¿½' => 'f',
    );
}

function urlParsing($string)
{
    $arrDash = array("--", "---", "----", "-----");
    $string  = strtolower(trim($string));
    $string  = strtr($string, normalizeChars());
    $string  = preg_replace('/[^a-zA-Z0-9 -.]/', '', $string);
    $string  = str_replace(" ", "-", $string);
    $string  = str_replace("&", "", $string);
    $string  = str_replace(array("'", "\"", "&quot;"), "", $string);
    $string  = str_replace($arrDash, "-", $string);
    return str_replace($arrDash, "-", $string);
}

function dispatch()
{
    $path = $_SERVER['REQUEST_URI'];

    if (getenv('SITE_URL') !== null) {
        $path = preg_replace('@^' . preg_quote(site_path()) . '@', '', $path);
    }

    $parts = preg_split('/\?/', $path, -1, PREG_SPLIT_NO_EMPTY);

    $uri = trim($parts[0], '/');

    if ($uri == 'index.php' || $uri == '') {
        $uri = 'site';
    }

    return $uri;
}

function getUrlFile()
{
    $uri    = dispatch();
    $getUri = explode("/", $uri);

    if (isset($getUri[0])) {
        $url = $getUri[0];
    } else {
        $url = 'site';
    }

    $file = 'routes/' . $url . '.php';

    if (file_exists($file)) {
        return $file;
    }

    // return $url;
    return 'routes/site.php';
}

function partial($view, $locals = null)
{

    if (is_array($locals) && count($locals)) {
        extract($locals, EXTR_SKIP);
    }

    $path = basename($view);
    $view = preg_replace('/' . $path . '$/', "_{$path}", $view);
    $view = "views/{$view}.php";

    if (file_exists($view)) {
        ob_start();
        require $view;
        return ob_get_clean();
    } else {
        error(500, "partial [{$view}] not found");
    }

    return '';
}

function content($value = null)
{
    return stash('$content$', $value);
}

function render($view, $locals = null, $layout = null)
{
    if (is_array($locals) && count($locals)) {
        extract($locals, EXTR_SKIP);
    }

    ob_start();

    include "views/{$view}.php";
}

function successResponse($response, $message)
{
    return $response->withJson([
        'status_code' => 200,
        'data'        => $message,
    ], 200);
}

function unprocessResponse($response, $message)
{
    return $response->withJson([
        'status_code' => 422,
        'errors'      => $message,
    ], 422);
}

function unauthorizedResponse($response, $message)
{
    return $response->withJson([
        'status_code' => 401,
        'errors'      => $message,
    ], 401);
}

function is_url($uri)
{
    if (filter_var($uri, FILTER_VALIDATE_URL) === false) {
        return false;
    } else {
        return true;
    }
}

function is_base64($base64)
{
    $data = $base64;
    $data = str_replace(" ", "+", $data);
    $data = str_replace(":=", "==", $data);

    list($type, $data)  = explode(';', $data);
    list(, $data)       = explode(',', $data);
    list($header, $ext) = explode("/", $type);
    if (isset($data) and base64_decode($data)) {
        return true;
    } else {
        return false;
    }
}

function base64toImg($base64, $path, $name = null)
{
    $data = $base64;
    $data = str_replace(" ", "+", $data);
    $data = str_replace(":=", "==", $data);

    if (!empty($data) and is_base64($data)) {
        list($type, $data)  = explode(';', $data);
        list(, $data)       = explode(',', $data);
        list($header, $ext) = explode("/", $type);

        $data = base64_decode($data);
        if (empty($name)) {
            $name = date("ymdis");
        } else {
            $name = $name;
        }

        $allowExt = array('PNG', 'JPG', 'jpg', 'png', 'JPEG', 'jpeg');
        if (in_array($ext, $allowExt)) {
            $fileName = $name . '.' . $ext;
            file_put_contents($path . $fileName, $data);

            return [
                'status' => true,
                'data'   => $fileName,
            ];
        } else {
            return [
                'status' => false,
                'data'   => ['ekstensi gambar harus JPG atau PNG'],
            ];
        }

    } else {
        return [
            'status' => false,
            'data'   => $data,
        ];
    }
}

function rp($price = 0, $prefix = true, $decimal = 0)
{
    if ($price === '-' || empty($price)) {
        return '';
    } else {
        if ($prefix === "-") {
            return $price;
        } else {
            $rp = ($prefix) ? 'Rp. ' : '';

            if ($price < 0) {
                $price  = (float) $price * -1;
                $result = '(' . $rp . number_format($price, $decimal, ",", ".") . ')';
            } else {
                $price  = (float) $price;
                $result = $rp . number_format($price, $decimal, ",", ".");
            }
            return $result;
        }
    }
}
