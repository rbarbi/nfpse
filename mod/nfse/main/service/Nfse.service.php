<?php

use NFePHP\Common\Signer;
use NFePHP\Common\Certificate;

class NfseService extends NfseBaseService {
	public function __construct() {
		parent::__construct();
	}

    /**
	 * Chama a geração de uma nota fiscal na DAO, consumindo o webservice
	 * Retorna um base64 com o XML completo de retorno da nota gerada
	 * 
	 * @return array
	 * @throws Exception
	 */
	public function gerarNota($dadosPost) {
        try {
            $dadosPost['dadosNota']['numeroSerie'] = time();
            $dadosPost['dadosNota']['dadosAmbiente'] = $this->getDadosAmbiente();
            $dadosPost['dadosNota']['retencoes'] = $this->getImpostosRetidos($dadosPost);
            
            $xml = UtilsNFSe::gerarXMLRequisicao($dadosPost['dadosNota'], $dadosPost['emp']); //pred($xml);

            $objCertificado = $this->getCertificadoDigital($dadosPost['emp']);
            $xmlAssinado = Signer::sign($objCertificado, $xml['conteudoXML'], 'DPS', '', OPENSSL_ALGO_SHA256, [true, false, null, null], '');

            //pred($this->validarXmlComSchema($xmlAssinado)); //<- O schema está com problemas...

            $dadosNotaGerada = $this->gerarNotaApi($xmlAssinado);


            return $dadosNotaGerada;
		} catch (Exception $e) {
			throw new Exception($e->getMessage(), $e->getCode());
		}
    }

    public function cancelarNota($dadosPost) {
        try {
            $chaveAcesso = $dadosPost['dadosNota']['chaveAcesso'];
            
            $dadosPost['dadosNota']['dadosAmbiente'] = $this->getDadosAmbiente();
            
            $xml = UtilsNFSe::gerarXMLcancelamento($dadosPost['dadosNota']); //pred($xml);
            
            $objCertificado = $this->getCertificadoDigital($dadosPost['emp']);            
            $xmlAssinado = Signer::sign($objCertificado, $xml['conteudoXML'], 'infPedReg', 'Id', OPENSSL_ALGO_SHA256, [true, false, null, null], 'pedRegEvento');
            
            $dadosNotaGerada = $this->cancelarNotaApi($xmlAssinado, $chaveAcesso);

            return $dadosNotaGerada;
		} catch (Exception $e) {
			throw new Exception($e->getMessage(), $e->getCode());
		}
    }

    public function consultarIdNota($chaveAcesso)
    {
        try {
            $dadosNota = $this->consultarIdNotaApi($chaveAcesso);

            return $dadosNota;
		} catch (Exception $e) {
			throw new Exception($e->getMessage(), $e->getCode());
		}
    }

    public function getImpostosRetidos($dadosPost)
    {
        $listaRetencoes = $this->getListaRetencoes($dadosPost);

        $dadosRetencoes = $this->dao->retornaDadosImpostosRetidos($listaRetencoes);

        return $dadosRetencoes;
    }

    /**
     * Extrai e monta a lista de retenções conforme marcadores encontrados nos dados adicionais
     * 
     * @param string $strDadosAdicionais String com possíveis marcadores de retenção
     * @param array $dadosPost Dados completos da nota
     * @return array|null Lista de retenções ou null se nenhum marcador for encontrado
     */
    private function getListaRetencoes($dadosPost)
    {
        $dadosNota = $dadosPost['dadosNota'];
        
        // Todas as retenções (ISS, IR, INSS, PIS, COFINS, CSLL)
        return array(
            'valor_unitario' => $dadosNota['valor_unitario'],
            'ccn_regime_tributario' => 'LPR',
            'ccn_possui_convenio_uniao' => "N",
            'ccn_retem_iss' => "N",
            'ccn_retem_ir' => "N"
        );
    }

    /**
	 * Retorna os dados de retenções para uma determinada fatura
	 *
	 * @param array $dadosFatura
	 * @return array
	 * @throws Exception
	 */
	public function consultarRetencoesFatura($dadosFatura) {
		try {
			return $this->dao->retornaDadosImpostosRetidos($dadosFatura);
		} catch (Exception $e) {
			throw new Exception($e->getMessage(), 400);
		}
	}
    
    private function gerarNotaApi($xmlAssinado)
    {
        $dadosNotas = array();
        $dadosDps = $this->dao->enviarDps($xmlAssinado);
        
        if(isset($dadosDps['chaveAcesso']) && isset($dadosDps['xmlBase64'])) {
            $dadosNotas['chaveAcesso'] = $dadosDps['chaveAcesso'];
            $dadosNotas['conteudoXML'] = $dadosDps['xmlBase64'];
            
            $dadosDanfse = $this->gerarDanfse($dadosDps['chaveAcesso'], $dadosDps['xmlBase64']);
            
            // Se houver erro na geração do PDF, adiciona o erro mas retorna os dados da DPS
            if ($dadosDanfse['erro'] != null) {
                $dadosNotas['conteudoPDF'] = '';
                $dadosNotas['erroGeracaoPDF'] = $dadosDanfse['erro'];
            } else {
                $dadosNotas['conteudoPDF'] = $dadosDanfse['pdfBase64'];
            }

            return $dadosNotas;
        }

        throw new Exception("Erro ao gerar a nota via API: ".$dadosDps['mensagemErro'], 400);
    }

    /**
	 * Retorna o núme de série da NF-e 4.00 a com base na empresa passada e no boleto, para checar se tem numeração em cache
	 * @param string $empresa
	 * @param integer $idBoleto
	 * @return integer
	 */
	public function getNumeroSerieSequencial($empresa, $idBoleto) {
		return $this->dao->getNumeroSerieSequencial($empresa, $idBoleto);
	}

    private function cancelarNotaApi($xmlAssinado, $chaveAcesso)
    {
        $dadosNotas = array();
        $dadosDps = $this->dao->enviarDeventoDps($xmlAssinado, $chaveAcesso);
        
        if(isset($dadosDps['xmlBase64'])) {
            $dadosNotas['conteudoXML'] = $dadosDps['xmlBase64'];
            
            $xmlNfse = null;
            
            try {
                $dadosNfse = $this->dao->retornarDps($chaveAcesso);
                if (isset($dadosNfse['xmlBase64'])) {
                    $xmlNfse = $dadosNfse['xmlBase64'];
                }
            } catch (Exception $e) { /* ignora: gerarDanfse buscara o XML sozinho */ }
            $dadosDanfse = $this->gerarDanfse($chaveAcesso, $xmlNfse, true);
            if ($dadosDanfse['erro'] != null) {
                $dadosNotas['conteudoPDF'] = '';
                $dadosNotas['erroGeracaoPDF'] = $dadosDanfse['erro'];
            } else {
                $dadosNotas['conteudoPDF'] = $dadosDanfse['pdfBase64'];
            }

            return $dadosNotas;
        }

        throw new Exception("Erro ao cancelar a nota via API: ".$dadosDps['mensagemErro'], 400);
    }

    private function consultarIdNotaApi($chaveAcesso)
    {
        $dadosNotas = array();
        $dadosDps = $this->dao->retornarDps($chaveAcesso);

        if(isset($dadosDps['chaveAcesso']) && isset($dadosDps['xmlBase64'])) {
            $dadosNotas['chaveAcesso'] = $dadosDps['chaveAcesso'];
            $dadosNotas['conteudoXML'] = $dadosDps['xmlBase64'];
            
            $dadosDanfse = $this->gerarDanfse($dadosDps['chaveAcesso'], $dadosDps['xmlBase64']);
            
            // Se houver erro na geração do PDF, adiciona o erro mas retorna os dados da DPS
            if ($dadosDanfse['erro'] != null) {
                $dadosNotas['conteudoPDF'] = '';
                $dadosNotas['erroGeracaoPDF'] = $dadosDanfse['erro'];
            } else {
                $dadosNotas['conteudoPDF'] = $dadosDanfse['pdfBase64'];

                if(isset($dadosDanfse['backupUtilizado']) && $dadosDanfse['backupUtilizado'] === true){
                    $dadosNotas['backupUtilizado'] = 'O DANFSe foi gerado utilizando o Emissor Nacional como backup, pois a geração via ADN falhou.';
                }
            }

            return $dadosNotas;
        }

        throw new Exception("Erro ao consultar nota via API: ".$dadosDps['mensagemErro'], 400);
    }

    /**
     * Retorna o objeto do certificado digital
     * 
     * @param integer $emp
     * @return Certificate
     * @throws Exception
     */
    private function getCertificadoDigital($emp) {
        try {
            $nomeCertificado = MainGama::getApp()->getConfig("nome_certificado-{$emp}");
            $senhaCertificado = MainGama::getApp()->getConfig("senha_certificado-{$emp}");
            $pathCertificado = dirname(dirname(__DIR__)).'/certificado-digital/'.$nomeCertificado;
            $strCertificado = file_get_contents($pathCertificado);
            $objCertificado = Certificate::readPfx($strCertificado, $senhaCertificado);

            return $objCertificado;
        } catch (Exception $e) {
            throw new Exception("Erro ao carregar o certificado digital: ".$e->getMessage(), $e->getCode());
        }   
    }

    private function validarXmlComSchema($xmlString, $tipoXml="DPS")
    {
        $versao = 'v' . $this->dao->getVersao();

        switch ($tipoXml) {
            case "DPS":
                $schemaPath=dirname(dirname(__DIR__))."/schemas/{$versao}/DPS_{$versao}.xsd";
                break;
            case "evento":
                $schemaPath=dirname(dirname(__DIR__))."/schemas/{$versao}/evento_{$versao}.xsd";
                break;
            default:
                 throw new Exception("Tipo de XML para validação desconhecido: ".$tipoXml, 400);
        }

        libxml_use_internal_errors(true);

        $xml = new DOMDocument();
        $xml->preserveWhiteSpace = false;
        $xml->formatOutput = false;

        if (!$xml->loadXML($xmlString)) {
            throw new Exception("Falha ao carregar o XML a partir da string. ". UtilsNFSe::formatarErrosEmString($this->obterErrosLibxml()), 400);
        }

        if (!$xml->schemaValidate($schemaPath)) {
            throw new Exception("XML inválido conforme schema." . UtilsNFSe::formatarErrosEmString($this->obterErrosLibxml()), 400);
        }

        return [
            'sucesso' => true,
            'mensagem' => 'XML válido conforme schema.',
            'erros' => array()
        ];
    }

    private function getDadosAmbiente()
    {
        return array(
            'idCNAE' => $this->dao->getidCNAE(),
            'ambiente' => $this->dao->getAmbiente(),
            'codigoMunicipio' => $this->dao->getcodigoMunicipio(),
            'inscricaoFederal' => $this->dao->getInscricaoFederal(),
            'inscricaoMunicipal' => $this->dao->getInscricaoMunicipal(),
            'versao' => $this->dao->getVersao()
        );
    }

    private function obterErrosLibxml()
    {
        $errors = array();
        foreach (libxml_get_errors() as $error) {
            $errors[] = trim($error->message) . " (Linha {$error->line})";
        }
        libxml_clear_errors();
        return $errors;
    }

    /**
     * Obtém o DANFSe (PDF) tentando primeiro o ADN
     * Se falhar, tenta o Emissor Nacional como fallback
     * 
     * @param string $chaveAcesso Chave de acesso da nota
     * @return array Array com pdfBase64, erro e http_code
     */
    private function gerarDanfse($chaveAcesso, $xml = null, $cancelada = false)
    {
        $dadosLocal = $this->dao->gerarDanfseLocal($chaveAcesso, $xml, $cancelada);
        $dadosLocal['localGerado'] = true;
        return $dadosLocal;
    }
}