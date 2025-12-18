<?php

class NfseDAO
{
    private $urlSefin;
    private $urlAdn;
    private $ambiente;
    private $idCNAE;
    private $inscricaoMunicipal;
    private $inscricaoFederal;
    private $codigoMunicipio;
    private $empresaAcesso;
    private $versao;
    private $dadosCertificado = array();


    public function __construct($empresaAcesso, $ambiente)
    {
        $this->ambiente = (isset($ambiente)) ? $ambiente : MainGama::getApp()->getConfig("ambiente");
        
        if ($this->ambiente == 1) {
            $this->urlSefin = MainGama::getApp()->getConfig("urlSefinProducao");
            $this->urlAdn = MainGama::getApp()->getConfig("urlAdnProducao");
        } else {
            $this->urlSefin = MainGama::getApp()->getConfig("urlSefinHomologacao");
            $this->urlAdn = MainGama::getApp()->getConfig("urlAdnHomologacao");
        }

        $this->idCNAE = MainGama::getApp()->getConfig("idCNAE-{$empresaAcesso}");
        $this->inscricaoFederal = MainGama::getApp()->getConfig("cnpj-{$empresaAcesso}");
        $this->inscricaoMunicipal = MainGama::getApp()->getConfig("im-{$empresaAcesso}");
        $this->codigoMunicipio = MainGama::getApp()->getConfig("codigo_municipio-{$empresaAcesso}");
        $this->versao = MainGama::getApp()->getConfig("versao");

        $this->empresaAcesso = $empresaAcesso;

        $this->dadosCertificado = array(
            'senha' => MainGama::getApp()->getConfig("senha_certificado-{$empresaAcesso}"),
            'path' => dirname(dirname(__DIR__)) . '/certificado-digital/' . MainGama::getApp()->getConfig("nome_certificado-{$empresaAcesso}")
        );
    }

    public function enviarDps($xmlAssinado)
    {
        $gzipB64 = UtilsNFSe::encodeGzipB64($xmlAssinado);

        $payload = json_encode(array(
            "dpsXmlGZipB64" => $gzipB64
        ), JSON_UNESCAPED_UNICODE);


        $dadosCertificado = array(
            'path' => $this->dadosCertificado['path'],
            'senha' => $this->dadosCertificado['senha']
        );

        $respostaDps = $this->executarRequisicaoCurl($this->urlSefin, $payload, $dadosCertificado, 'POST');
        
        if ($respostaDps['sucesso'] == false) {
            throw new Exception("Erro ao enviar DPS: " . $respostaDps['erro'], $respostaDps['http_code']);
        }

        if ($respostaDps['resposta']['chaveAcesso'] == null) {
            throw new Exception("Erro ao enviar DPS: " . UtilsNFSe::formatarErrosEmString($respostaDps['resposta']['erros']), 400);
        }

        $dadosDps = array(
            "xmlBase64" => UtilsNFSe::gzipB64ToB64($respostaDps['resposta']['nfseXmlGZipB64']),
            "chaveAcesso" => $respostaDps['resposta']['chaveAcesso']
        );

        return $dadosDps;
    }

    public function enviarDeventoDps($xmlAssinado, $chaveAcesso)
    {
        $gzipB64 = UtilsNFSe::encodeGzipB64($xmlAssinado);

        $payload = json_encode(array(
            "pedidoRegistroEventoXmlGZipB64" => $gzipB64
        ), JSON_UNESCAPED_UNICODE);


        $dadosCertificado = array(
            'path' => $this->dadosCertificado['path'],
            'senha' => $this->dadosCertificado['senha']
        );

        $respostaDps = $this->executarRequisicaoCurl(
            $this->urlSefin . "/{$chaveAcesso}/eventos",
            $payload,
            $dadosCertificado,
            'POST'
        );

        if ($respostaDps['sucesso'] == false) {
            throw new Exception("Erro ao registrar Evento: " . $respostaDps['erro'], $respostaDps['http_code']);
        }

        if ($respostaDps['resposta']['eventoXmlGZipB64'] == null) {
            throw new Exception("Erro ao registrar Evento: " . UtilsNFSe::formatarErrosEmString($respostaDps['resposta']['erro']), 400);
        }

        $dadosDps = array(
            "xmlBase64" => UtilsNFSe::gzipB64ToB64($respostaDps['resposta']['eventoXmlGZipB64'])
        );

        return $dadosDps;
    }

    public function retornarDps($chaveAcesso)
    {
        $dadosCertificado = array(
            'path' => $this->dadosCertificado['path'],
            'senha' => $this->dadosCertificado['senha']
        );

        $respostaDps = $this->executarRequisicaoCurl(
            $this->urlSefin . "/{$chaveAcesso}",
            '',
            $dadosCertificado,
            'GET'
        );

        if ($respostaDps['sucesso'] == false) {
            throw new Exception("Erro capturar DPS: " . $respostaDps['erro'], $respostaDps['http_code']);
        }

        if ($respostaDps['resposta']['chaveAcesso'] == null) {
            throw new Exception("Erro capturar DPS: " . UtilsNFSe::formatarErrosEmString($respostaDps['resposta']['erros']), 400);
        }

        $dadosDps = array(
            "xmlBase64" => UtilsNFSe::gzipB64ToB64($respostaDps['resposta']['nfseXmlGZipB64']),
            "chaveAcesso" => $respostaDps['resposta']['chaveAcesso']
        );

        return $dadosDps;
    }

    public function gerarDanfse($chaveAcesso)
    {
        $dadosCertificado = array(
            'path' => $this->dadosCertificado['path'],
            'senha' => $this->dadosCertificado['senha']
        );

        // Faz a requisição GET para obter o DANFSe (PDF)
        $respostaDanfse = $this->executarRequisicaoCurl(
            $this->urlAdn . "/{$chaveAcesso}",
            '',
            $dadosCertificado,
            'GET',
            true
        );

        // Se houver erro na requisição, retorna o erro sem lançar exception
        if ($respostaDanfse['sucesso'] == false) {
            return array(
                "pdfBase64" => null,
                "erro" => "Erro ao gerar DANFSe: " . $respostaDanfse['erro'],
                "http_code" => $respostaDanfse['http_code']
            );
        }

        if ($respostaDanfse['http_code'] != 200) {
            return array(
                "pdfBase64" => null,
                "erro" => "Erro ao gerar DANFSe: HTTP " . $respostaDanfse['http_code'],
                "http_code" => $respostaDanfse['http_code']
            );
        }

        $pdfBase64 = base64_encode($respostaDanfse['resposta']);

        return array(
            "pdfBase64" => $pdfBase64,
            "erro" => null,
            "http_code" => 200
        );
    }

    /**
     * Função backup para gerar DANFSe a partir da URL do Emissor Nacional
     * Utilizado quando o ADN não consegue gerar o PDF
     * 
     * @param string $chaveAcesso Chave de acesso da NFS-e (50 dígitos)
     * @return array Array com pdfBase64, erro e http_code
     */
    public function gerarDanfseBackup($chaveAcesso)
    {
        $urlDownload = "https://www.nfse.gov.br/EmissorNacional/Notas/Download/DANFSe/{$chaveAcesso}";

        $loginResult = $this->loginEmissorNacional();

        if ($loginResult['sucesso'] == false) {
            return array(
                "pdfBase64" => null,
                "erro" => "Erro ao fazer login no Emissor Nacional: " . $loginResult['erro'],
                "http_code" => 0
            );
        }

        $cookieFile = $loginResult['cookieFile'];

        $ch = curl_init($urlDownload);

        $opcoes = array(
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTPHEADER => array('Accept: application/pdf, application/json'),
            CURLOPT_SSLCERT => $this->dadosCertificado['path'],
            CURLOPT_SSLCERTPASSWD => $this->dadosCertificado['senha'],
            CURLOPT_SSLCERTTYPE => 'P12',
            CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_COOKIEJAR => $cookieFile,
            CURLOPT_COOKIEFILE => $cookieFile
        );

        curl_setopt_array($ch, $opcoes);

        $respostaRaw = curl_exec($ch);
        $erro = curl_error($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);
        
        if (file_exists($cookieFile)) {
            @unlink($cookieFile);
        }

        // Se houver erro na requisição, retorna o erro sem lançar exception
        if ($erro) {
            return array(
                "pdfBase64" => null,
                "erro" => "Erro ao gerar DANFSe Backup: " . $erro,
                "http_code" => $status
            );
        }

        // Verifica se recebeu a página de login (HTML) em vez do PDF
        if ($status == 200 && strpos($contentType, 'text/html') !== false) {
            return array(
                "pdfBase64" => null,
                "erro" => "Erro ao gerar DANFSe Backup: Acesso negado - autenticação com certificado digital falhou",
                "http_code" => 401
            );
        }

        if ($status != 200) {
            return array(
                "pdfBase64" => null,
                "erro" => "Erro ao gerar DANFSe Backup: HTTP " . $status,
                "http_code" => $status
            );
        }

        // Verifica se a resposta é realmente um PDF válido
        if (strpos($respostaRaw, '%PDF') === false) {
            return array(
                "pdfBase64" => null,
                "erro" => "Erro ao gerar DANFSe Backup: Resposta não é um PDF válido",
                "http_code" => 400
            );
        }

        $pdfBase64 = base64_encode($respostaRaw);
        return array(
            "pdfBase64" => $pdfBase64,
            "erro" => null,
            "http_code" => 200
        );
    }

    /**
     * Realiza login no Emissor Nacional usando certificado digital
     * Captura a sessão (cookies) para requisições subsequentes
     * 
     * @return array Array com cookies da sessão ou erro
     */
    private function loginEmissorNacional()
    {
        $urlLogin = "https://www.nfse.gov.br/EmissorNacional/Certificado";
        $cookieFile = sys_get_temp_dir() . '/nfse_cookies_' . md5($this->inscricaoFederal) . '.txt';

        $ch = curl_init($urlLogin);

        $opcoes = array(
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_SSLCERT => $this->dadosCertificado['path'],
            CURLOPT_SSLCERTPASSWD => $this->dadosCertificado['senha'],
            CURLOPT_SSLCERTTYPE => 'P12',
            CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_COOKIEJAR => $cookieFile,
            CURLOPT_COOKIEFILE => $cookieFile,
            CURLOPT_HEADER => true
        );

        curl_setopt_array($ch, $opcoes);

        $resposta = curl_exec($ch);
        $erro = curl_error($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($erro) {
            return array(
                "sucesso" => false,
                "erro" => $erro,
                "cookieFile" => null
            );
        }

        return array(
            "sucesso" => true,
            "cookieFile" => $cookieFile,
            "http_code" => $status
        );
    }

    /**
     * Método abstrato para executar requisições HTTP com autenticação mTLS
     * 
     * @param string $url URL do webservice
     * @param string $payload Dados a serem enviados (JSON)
     * @param array $dadosCertificado Array com 'path' e 'senha' do certificado
     * @param string $metodo Método HTTP (POST, GET, etc)
     * @param bool $respostaBinaria Se true, retorna conteúdo binário (PDF); se false, decodifica como JSON
     * @return array Resultado da requisição com sucesso, http_code e resposta/erro
     */
    private function executarRequisicaoCurl($url, $payload, $dadosCertificado, $metodo = 'POST', $respostaBinaria = false)
    {
        $ch = curl_init($url);

        $headers = array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload)
        );

        if (strtoupper($metodo) === 'GET' && empty($payload)) {
            $headers = array('Accept: application/pdf, application/json');
        }

        $opcoes = array(
            CURLOPT_CUSTOMREQUEST => strtoupper($metodo),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSLCERT => $dadosCertificado['path'],
            CURLOPT_SSLCERTPASSWD => $dadosCertificado['senha'],
            CURLOPT_SSLCERTTYPE => 'P12',
            CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 2
        );

        if (in_array(strtoupper($metodo), ['POST', 'PUT', 'PATCH'])) {
            $opcoes[CURLOPT_POSTFIELDS] = $payload;
        }

        curl_setopt_array($ch, $opcoes);

        $respostaRaw = curl_exec($ch);
        $erro = curl_error($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

        curl_close($ch);

        if ($erro) {
            return array(
                "sucesso" => false,
                "erro" => $erro,
                "http_code" => $status
            );
        }

        if ($respostaBinaria || strpos($contentType, 'application/pdf') !== false) {
            return array(
                "sucesso" => true,
                "http_code" => $status,
                "resposta" => $respostaRaw,
                "content_type" => $contentType
            );
        }

        $resposta = json_decode($respostaRaw, true);

        return array(
            "sucesso" => true,
            "http_code" => $status,
            "resposta" => $resposta
        );
    }

    /**
     * Valida todas as regras referente ?s reten??es de impostos, com base no que foi definido nos dados de nota fiscal do cliente
     *
     * @param array $dadosFatura
     * @return array
     */
    public function retornaDadosImpostosRetidos($dadosFatura)
    {
        $valorFatura = $dadosFatura['valor_unitario'];

        /*
         * Novas regras com base no valor do boleto
         *  - Boletos até R$ 215,04 - Não tem retenções
         *  - Boletos de R$ 215,05 até R$ 666,66 - Reter apenas PIS/COFINS/CSLL
         *  - Boletos acima de R$ 666,66 - Tem todas as retenções
         */
        if ($valorFatura <= 215.04) {
            $temRetencao = false;
            $retencaoCompleta = false;
        } elseif ($valorFatura >= 215.05 && $valorFatura <= 666.66) {
            $temRetencao = true;
            $retencaoCompleta = false;
        } elseif ($valorFatura >= 666.67) {
            $temRetencao = true;
            $retencaoCompleta = true;
        }

        $possuiRetencaoIR = $dadosFatura['ccn_retem_ir'] === 'S' && !empty($dadosFatura['ccn_percentual_ir']);
        $possuiRetencaoISS = $dadosFatura['ccn_retem_iss'] === 'S' && !empty($dadosFatura['ccn_percentual_iss']);

        // Se tiver alguma retenção, aplicar as regras existentes
        if ($temRetencao === true) {
            $percOutros = 4.65;
            $percIRRF = 1.5;
            $observacoes = '';
            $valorRetIRRF = $valorFatura * ($percIRRF / 100);
            $valorRetOutros = $valorFatura * ($percOutros / 100);
            $valorRetIRRFf = number_format($valorRetIRRF, 2, ",", ".");
            $valorRetOutrosf = number_format($valorRetOutros, 2, ",", ".");
            $valorRetISS = 0;
            $valorRetIR = 0;

            if ($dadosFatura['ccn_regime_tributario'] === 'SIN') {
                // Se for PJ simples nacional
                if ($retencaoCompleta === true) {
                    $valorRetOutros = 0;
                    $observacoes = !$possuiRetencaoIR ? " Retenção do IRRF 1,5% R$ {$valorRetIRRFf}." : '';
                } else {
                    $valorRetIRRF = 0;
                }
                $valorRetOutros = 0;
            } elseif ($dadosFatura['ccn_regime_tributario'] === 'LPR') {
                // Se for PJ lucro presumido
                if ($retencaoCompleta === true) {
                    $observacoes = " Retenção do PIS/COFINS/CSLL 4,65% R$ {$valorRetOutrosf};";
                    $observacoes .= !$possuiRetencaoIR ? " IRRF 1,5% R$ {$valorRetIRRFf}." : '';
                } else {
                    $valorRetIRRF = 0;
                    $observacoes = " Retenção do PIS/COFINS/CSLL 4,65% R$ {$valorRetOutrosf}.";
                }
            } elseif ($dadosFatura['ccn_regime_tributario'] === 'OPME') {
                // Se for órgão público estadual ou municipal - adicionar frase das retenções somente se tiver convênio com a União
                if ($dadosFatura['ccn_possui_convenio_uniao'] === 'S') {
                    if ($retencaoCompleta === true) {
                        $observacoes = " Retenção do PIS/COFINS/CSLL 4,65% R$ {$valorRetOutrosf};";
                        $observacoes .= !$possuiRetencaoIR ? " IRRF 1,5% R$ {$valorRetIRRFf}." : '';
                    } else {
                        $valorRetIRRF = 0;
                        $observacoes = " Retenção do PIS/COFINS/CSLL 4,65% R$ {$valorRetOutrosf}.";
                    }
                } else {
                    $valorRetOutros = $valorRetIRRF = 0;
                }
            } elseif ($dadosFatura['ccn_regime_tributario'] === 'OPF') {
                // Se for órgão público federal
                $percOutros = 9.45;
                $valorRetIRRF = 0;
                $valorRetOutros = $valorFatura * ($percOutros / 100);
                $valorRetOutrosf = number_format($valorRetOutros, 2, ",", ".");
                $observacoes = " Retenção do PIS/COFINS/CSLL/IRPJ 9,45% R$ {$valorRetOutrosf}.";
            } elseif ($dadosFatura['ccn_regime_tributario'] == 'CON') {
                // Se for condomínio
                $valorRetIRRF = 0;
                $observacoes = " Retenção do PIS/COFINS/CSLL 4,65% R$ {$valorRetOutrosf}.";
            } else {
                // Nenhuma retenção - se o campo ccn_regime_tributario estiver = "N" ou vazio
                $valorRetOutros = $valorRetIRRF = 0;
            }
        } else {
            // Nenhuma retenção - se o valor da fatura for inferior a 215,04
            $valorRetOutros = $valorRetIRRF = 0;
        }

        // Retenção de ISS - ainda falta definir
        if ($possuiRetencaoISS) {
            // Se retém ISS - Calcular com base no que foi digitado. Se nada for definido nesse campo, o default será DOIS
            $percISS = (!empty($dadosFatura['ccn_percentual_iss'])) ? $dadosFatura['ccn_percentual_iss'] : 2;
            $percISSf = number_format($percISS, 2, ',', '.');
            $valorRetISS = $valorFatura * ($percISS / 100);
            $valorRetISSf = number_format($valorRetISS, 2, ",", ".");

            // A observação do ISS deve ser concatenada com a observação dos demais impostos retidos
            $observacoes .= " Retenção do ISS {$percISSf}% R$ {$valorRetISSf}.";
        }

        // Retenção de IR - Ignorar faixa de valores se retem IR for "S"
        if ($possuiRetencaoIR) {
            // Se retém IR - Calcular com base no que foi digitado. Se nada for definido nesse campo, o default será 1.5
            $percIR = (!empty($dadosFatura['ccn_percentual_ir'])) ? $dadosFatura['ccn_percentual_ir'] : 1.5;
            $percIRf = number_format($percIR, 2, ',', '.');
            $valorRetIR = $valorFatura * ($percIR / 100);
            $valorRetIRf = number_format($valorRetIR, 2, ",", ".");

            // A observação do IR deve ser concatenada com a observação dos demais impostos retidos
            $observacoes .= " Retenção do IR {$percIRf}% R$ {$valorRetIRf}.";

            // Se houver retenção IR preenchida, remover IRRF padrão
            $valorRetIRRF = 0;
        }

        return array(
            'observacoes' => utf8_encode($observacoes), // Onde for implementado, deve-se usar o utf8_decode
            'valorRetIRRF' => $valorRetIRRF,
            'valorRetOutros' => $valorRetOutros,
            'valorRetISS' => $valorRetISS,
            'valorRetIR' => $valorRetIR
        );
    }

    public function getCon()
    {
        return MainGama::getApp()->getCon('-');
    }

    public function getUrlSefin()
    {
        return $this->urlSefin;
    }

    public function getAmbiente()
    {
        return $this->ambiente;
    }

    public function getVersao()
    {
        return $this->versao;
    }

    public function getIdCNAE()
    {
        return $this->idCNAE;
    }

    public function getInscricaoFederal()
    {
        return $this->inscricaoFederal;
    }

    public function getInscricaoMunicipal()
    {
        return $this->inscricaoMunicipal;
    }

    public function getCodigoMunicipio()
    {
        return $this->codigoMunicipio;
    }
}