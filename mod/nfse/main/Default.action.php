<?php

class DefaultAction extends BaseAction
{
    /** @var NfpseService $service */
    protected $service;

    function __construct($app, $GET = array(), $POST = array(), $basePath = './mod')
    {
        // Chamo construtor da classe pai
        parent::__construct($app, $GET, $POST, $basePath);

        $this->service = $this->getService();

        $this->registraMetodos();
    }

    public function registraMetodos()
    {
        foreach (get_class_methods($this) as $metodo) {
            if (substr($metodo, -6) == "Action") {
                $this->registraAcao($metodo);
            }
        }
    }

    /**
     * Retorna o Service dinamicamente
     * @return BaseService
     */
    public function getService()
    {
        $svcName = $this->getServiceName(false);
        $svcFile = "./mod/".$this->getApp()->getM()."/".$this->getApp()->getU()."/service/".$svcName.".service.php";
        clearstatcache();
        if (is_file($svcFile)) {
            $svcName = $this->getServiceName(true);
            return new $svcName();
        } else {
            return null;
        }
    }

    /**
     * Retorna dinamicamente o nome do Service
     * @param boolean $sufixo
     * @return String
     */
    private function getServiceName($sufixo = true)
    {
        $svc = substr(get_class($this), 0, -6);
        if ($sufixo === true) {
            return $svc."Service";
        } else {
            return $svc;
        }
    }

    /**
     * sonbreescrita do método getParms para prever a questão do encode
     * @param type $chave
     * @param type $default
     * @param type $utf8_decode
     * @return type
     */
    public function getParms($chave = false, $default = null, $utf8_decode = false)
    {

        if ($chave === false) {
            $parms = array_merge(parent::getParms($chave, $default), $_FILES);
        } else {
            if (isset($_FILES[$chave])) {
                $parms = $_FILES[$chave];
            } else {
                $parms = parent::getParms($chave, $default);
            }
        }

        if (true === $utf8_decode) {
            $json  = new JSONView();
            $parms = $json->decodeUTF8Recursivo($parms);
        }
        return $parms;
    }
}