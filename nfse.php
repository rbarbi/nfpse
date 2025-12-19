<?php
//exit(phpinfo());
error_reporting(E_ALL & ~E_WARNING & ~E_DEPRECATED & ~E_STRICT & ~E_NOTICE);

ini_set('default_charset', 'UTF-8');

if (!function_exists("pre")) {

    /**
     * @param array $x
     * @param string $titulo
     * @param boolean $exit
     */
    function pre($x, $titulo = '', $exit = false)
    {
        echo "<fieldset style='min-width: 50%; word-wrap: break-word; background-color: #FAFAFA; border: 2px groove #ddd !important; padding: 1.4em 1.4em 1.4em 1.4em !important;'>";
            if (!empty($titulo)) {
                echo "<legend style='color:rgb(0, 0, 123); padding: 3px 10px 3px 10px; font-weight: bold; font-size: 14px; text-transform: uppercase; border: 1px groove #ddd !important;'> $titulo </legend>";
            }
            echo "<pre>";
                print_r($x);
            echo "</pre>";
        echo "</fieldset>";

        if ($exit) {
            exit;
        }
    }

    /**
     * @param array $x
     * @param string $titulo
     */
    function pred($x, $titulo = '')
    {
        pre($x, $titulo, true);
    }

    function pred2($dados) {
        echo "<pre>";
        print_r($dados);
        echo "</pre>";
        exit;
    }
}

try {
    require_once('./lib/gama/base/Main.php');

    // Seta somente o m(módulo), pois o resto default é definido no AutoExec.ini (u, a, acao)
    $_GET["m"]  = "nfse";
    $_POST["m"] = "nfse";

    $app = MainGama::getInstanceOf();

    echo $app->exec($_GET, $_POST);
} catch (Exception $e) {
    $dadosIni = array_merge($_POST, $_GET);

    $ret = array(
        'erros' => array(
            'codigo'   => $e->getCode(),
            'mensagem' => $e->getMessage()
        )
    );

    header('Content-Type: application/json; charset=UTF-8');
    $JSON = new JSONView($ret);
    echo $JSON->getDadosJSON();
}