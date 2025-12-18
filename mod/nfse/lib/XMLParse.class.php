<?php

class XMLParse
{
    var $conteudoXML;
    var $pathXML;

    /**
     * @param type $conteudoXML
     * @param type $pathXML
     */
    public function __construct($conteudoXML = "", $pathXML = "")
    {
        $this->conteudoXML = $conteudoXML;
        $this->pathXML     = $pathXML;
    }

    /**
     * Converte um conteњdo em XML para um array
     * @return array do XML
     */
    public function string2array()
    {
        $simplexml = (array) simplexml_load_string($this->conteudoXML, null, LIBXML_NOCDATA | LIBXML_COMPACT);
        $simplexml = $this->object2Array($simplexml);
        return $simplexml;
    }

    /**
     * Converte um arquivo em XML para um array
     * @return array do XML
     */
    public function file2array()
    {
        clearstatcache();
        if (file_exists($this->pathXML)) {
            $this->conteudoXML = file_get_contents($this->pathXML);
            return $this->string2array();
        } else {
            return false;
        }
    }

    private function object2Array($array)
    {
        foreach ($array as $chave => $valor) {
            if (is_string($valor)) {
                $valor         = $this->decodeUTF8Recursivo($valor);
                $array[$chave] = trim($valor);
            } else if (is_array($valor)) {
                $array[$chave] = $this->object2Array($valor);
            } else if (is_object($valor)) {
                $valor         = (array) $valor;
                $array[$chave] = $this->object2Array($valor);
            } else {
                $valor         = $this->decodeUTF8Recursivo((string) $valor);
                $array[$chave] = trim($valor);
            }
        }
        return $array;
    }

    /**
     * Codifica o UTF-8 de arrays e variсveis. 
     * 
     * @param mixed $var
     * @return mixed
     */
    private function encodeUTF8Recursivo($var)
    {
        $retorno = $var;

        if (true !== $var && false !== $var) {
            if (is_array($var)) {
                $retorno = array();
                foreach ($var as $i => $value) {
                    $i           = utf8_encode($i);
                    $retorno[$i] = $this->encodeUTF8Recursivo($value);
                }
            } else if (!is_object($var)) {
                $retorno = utf8_encode($var);
            }
        }

        return $retorno;
    }

    /**
     * Decodifica o UTF-8 de arrays e variсveis. Antes de decodificar, 
     * verifica se a codificaчуo era UTF-8
     * 
     * @param mixed $var
     * @return mixed
     */
    private function decodeUTF8Recursivo($var)
    {

        $retorno = $var;

        //nуo decodifica booleanos
        if (true !== $var && false !== $var) {
            if (is_array($var)) {
                $retorno = array();
                foreach ($var as $i => $value) {
                    if (strtoupper(mb_detect_encoding($i)) == "UTF-8") {
                        $i = utf8_decode($i);
                    }
                    $retorno[$i] = $this->decodeUTF8Recursivo($value);
                }
            }
            //nуo decodifica objetos
            else if (!is_object($var)) {
                if (strtoupper(mb_detect_encoding($var)) == "UTF-8") {
                    $retorno = utf8_decode($var);
                }
            }
        }
        return $retorno;
    }
}
?>