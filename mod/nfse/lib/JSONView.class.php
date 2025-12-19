<?php

class JSONView{

    private $dadosJSON;

    /**
     * 
     * @param mixed $variaveis
     * @return String
     */
    public function __construct($var = false, $escreve = false){
        //não codifica booleanos
        if (!is_bool($var)){
            $var = $this->encodeUTF8Recursivo($var);
        }

        $this->setDadosJSON($var);
        if ($escreve){
            echo $this->getDadosJSON();
        }
    }

    public function getDadosJSON(){
        return $this->dadosJSON;
    }

    public function setDadosJSON($dados){
        $this->dadosJSON = json_encode($dados);
    }

    /**
     * Codifica o UTF-8 de arrays e variáveis. 
     * 
     * @param mixed $var
     * @return mixed
     */
    function encodeUTF8Recursivo($var){
        $retorno = $var;

        if (true !== $var && false !== $var){
            if (is_array($var)){
                $retorno = array();
                foreach ($var as $i => $value){
                    $i           = Encoding::toUTF8($i);
                    $retorno[$i] = $this->encodeUTF8Recursivo($value);
                }
            }
            else if (!is_object($var)){
                $retorno = Encoding::toUTF8($var);
            }
        }

        return $retorno;
    }

    /**
     * Decodifica o UTF-8 de arrays e variáveis. Antes de decodificar, 
     * verifica se a codificação era UTF-8
     * 
     * @param mixed $var
     * @return mixed
     */
    function decodeUTF8Recursivo($var, $retornaSoArray = false){

        $retorno = $var;

        //não decodifica booleanos
        if (true !== $var && false !== $var){
            if (is_array($var) || (is_object($var) && $retornaSoArray === true)){
                if (is_object($var)){
                    $var = (array) $var;
                }
                $retorno = array();
                foreach ($var as $i => $value){
                    if (strtoupper(mb_detect_encoding($i)) == "UTF-8"){
                        $i = utf8_decode($i);
                    }
                    $retorno[$i] = $this->decodeUTF8Recursivo($value, $retornaSoArray);
                }
            }
            //não decodifica objetos
            else if (!is_object($var)){
                if (strtoupper(mb_detect_encoding($var)) == "UTF-8"){
                    $retorno = utf8_decode($var);
                }
            }
        }
        return $retorno;
    }

}