<?php

class UtilsNFSe
{

    /**
     * Método que gera o XML da requisição de geração da NFPS-e para cada boleto selecionado
     *
     * @param array $dados Os dados necessários para preencher a NFPS-e
     * @return array
     */
    public static function gerarXMLRequisicao($dados, $empresa)
    {
        $inscricaoFederal = self::clean($dados['dadosAmbiente']['inscricaoFederal']);
        $numeroDPS = self::gerarIdDPS($inscricaoFederal, $dados['dadosAmbiente']['codigoMunicipio'], $dados['numeroSerie']);

        try {
            // Raiz - DPS
            $xml = new SimpleXMLElement('<DPS xmlns="http://www.sped.fazenda.gov.br/nfse"></DPS>');
            $xml->addAttribute("versao", $dados['dadosAmbiente']['versao']);

            // infDPS - seguindo a ordem exata do schema
            $infDPS = $xml->addChild("infDPS");
            $infDPS->addAttribute("Id", $numeroDPS);

            // 1. tpAmb - Tipo de Ambiente (1=Produção, 2=Homologação)
            $infDPS->addChild("tpAmb", $dados['dadosAmbiente']['ambiente']);

            // 2. dhEmi - Data e hora de emissão
            $infDPS->addChild("dhEmi", date("Y-m-d\TH:i:sP"));

            // 3. verAplic - Versão da aplicação
            $infDPS->addChild("verAplic", "SefinNacional_1.4.0");

            // 4. serie - Série do DPS
            $infDPS->addChild("serie", "900");

            // 5. nDPS - Número do DPS
            $infDPS->addChild("nDPS", $dados['numeroSerie']);

            // 6. dCompet - Data de competência
            $infDPS->addChild("dCompet", date("Y-m-d"));

            // 7. tpEmit - Tipo de emitente (1=Prestador, 2=Tomador, 3=Intermediário)
            $infDPS->addChild("tpEmit", "1");

            // 8. cLocEmi - Código do município emissor
            $infDPS->addChild("cLocEmi", $dados['dadosAmbiente']['codigoMunicipio']);

            // 9. prest - Prestador
            $prest = $infDPS->addChild("prest");
            $prest->addChild("CNPJ", $inscricaoFederal);

            if($empresa === 'api') {
                $prest->addChild("IM", $dados['dadosAmbiente']['inscricaoMunicipal']);
            }

            // regTrib - Regime de tributação
            $regTrib = $prest->addChild("regTrib");
            $regTrib->addChild("opSimpNac", "1"); // 1=Não optante, 2=MEI, 3=ME/EPP
            $regTrib->addChild("regEspTrib", "0"); // 0=Não informado

            // 10. toma - Tomador (opcional)
            $cpfcnpj = self::clean($dados["cpf_cnpj"]);
            if (!empty($cpfcnpj)) {
                $toma = $infDPS->addChild("toma");

                if (strlen($cpfcnpj) == 11) {
                    $toma->addChild("CPF", $cpfcnpj);
                } else {
                    $toma->addChild("CNPJ", $cpfcnpj);
                }
//                $toma->addChild("IM", $dados['dadosAmbiente']['inscricaoMunicipal']);
                $toma->addChild("xNome", self::sanitizarXML($dados['nome_nota']));
                
                // end - Endereço do tomador (opcional)
                if (!empty($dados["logradouro"])) {
                    $end = $toma->addChild("end");

                    // endNac - Endereço nacional
                    $endNac = $end->addChild("endNac");
                    $endNac->addChild("cMun", $dados["cod_ibge"]);
                    $endNac->addChild("CEP", self::clean($dados["cep"]));

                    $end->addChild("xLgr", $dados["logradouro"]);
                    $end->addChild("nro", $dados["numero"]);

                    if (!empty($dados["complemento"])) {
                        $end->addChild("xCpl", self::sanitizarXML($dados["complemento"]));
                    }

                    $end->addChild("xBairro", $dados["bairro"]);
                }

                if (!empty($dados["email"])) {
                    $email = explode(';', $dados["email"]);
                    $toma->addChild("email", $email[0]);
                }
            }

            // 11. serv - Serviço
            $serv = $infDPS->addChild("serv");

            // locPrest - Local da prestação
            $locPrest = $serv->addChild("locPrest");
            $locPrest->addChild("cLocPrestacao", $dados["cod_ibge"]);

            // cServ - Código do serviço
            $cServ = $serv->addChild("cServ");
            $cServ->addChild("cTribNac", $dados['dadosAmbiente']['idCNAE']);

            $cServ->addChild("xDescServ", $dados["descricao"]);

            $cServ->addChild("cNBS", "126030000");

            if (!empty($dados['dadosAdicionais'])) {
                $infCompl = $serv->addChild("infoCompl");
                $infCompl->addChild("xInfComp", self::sanitizarXML($dados['dadosAdicionais']));
            }

            // 12. valores - Valores
            $valores = $infDPS->addChild("valores");

            // vServPrest - Valor do serviço prestado
            $vServPrest = $valores->addChild("vServPrest");
            $valorServico = number_format((float) $dados["valor_unitario"], 2, ".", "");
            $vServPrest->addChild("vServ", $valorServico);

            // trib - Tributação
            $trib = $valores->addChild("trib");

            // tribMun - Tributação municipal
            $tribMun = $trib->addChild("tribMun");
            $tribMun->addChild("tribISSQN", "1"); // 1=Operação tributável

            $retencaoISS = ($dados["ccn_retem_iss"] === "S") ? "2" : "1"; // 1=Não retido, 2=Retido pelo tomador
            $tribMun->addChild("tpRetISSQN", $retencaoISS);

            // $percISS = (!empty($dados['ccn_percentual_iss'])) ? $dados['ccn_percentual_iss'] : 2;
            // $percISS = number_format($percISS, 2, ".", "");
            // $tribMun->addChild("pAliq", $percISS);

            // tribFed - Tributação Federal & Retenções
            $vTotTribFed = 0;
            $vTotTribMun = 0;

            if (isset($dados['retencoes'])) {
                $triFed = $trib->addChild("tribFed");

                $base = (float) $dados["valor_unitario"];
                $baseFmt = number_format($base, 2, ".", "");

                // Determina regime para aplicar alíquotas
                $regime = isset($dados['ccn_regime_tributario']) ? $dados['ccn_regime_tributario'] : '';

                // Flags de retenção calculadas na DAO
                $valorRetOutros = number_format($dados['retencoes']['valorRetOutros'], 2, ".", "");
                $valorRetIRRF = number_format($dados['retencoes']['valorRetIRRF'], 2, ".", "");
                $valorRetISS = number_format($dados['retencoes']['valorRetISS'], 2, ".", "");
                $valorRetIR = number_format($dados['retencoes']['valorRetIR'], 2, ".", "");

                // Alíquotas por regime
                $aliqPis = 0.00;
                $aliqCofins = 0.00;
                $aliqCsll = 0.00;

                if ($regime === 'SIN') {
                    // Simples Nacional: sem retenção de PIS/COFINS/CSLL
                    $aliqPis = 0.00;
                    $aliqCofins = 0.00;
                    $aliqCsll = 0.00;
                } elseif ($regime === 'OPF') {
                    // Órgão público federal: total 9,45% (PIS/COFINS/CSLL/IRPJ)
                    // No XML, discrimina PIS/COFINS/CSLL; IRPJ não tem campo específico aqui
                    $aliqPis = 0.65;
                    $aliqCofins = 3.00;
                    $aliqCsll = 1.00;
                } else {
                    // LPR, OPME (com convênio), CON: total 4,65% (PIS 0,65 + COFINS 3,00 + CSLL 1,00)
                    $aliqPis = 0.65;
                    $aliqCofins = 3.00;
                    $aliqCsll = 1.00;
                }

                // Monta bloco de PIS/COFINS se houver base (valorRetOutros > 0 indica retenção aplicada)
                if ($valorRetOutros > 0) {
                    $piscofins = $triFed->addChild("piscofins");
                    $piscofins->addChild("CST", "01");
                    $piscofins->addChild("vBCPisCofins", $baseFmt);

                    $vPis = round($base * ($aliqPis / 100), 2);
                    $vPis = number_format($vPis, 2, ".", "");
                    $vCofins = round($base * ($aliqCofins / 100), 2);
                    $vCofins = number_format($vCofins, 2, ".", "");

                    $piscofins->addChild("pAliqPis", number_format($aliqPis, 2, ".", ""));
                    $piscofins->addChild("pAliqCofins", number_format($aliqCofins, 2, ".", ""));
                    $piscofins->addChild("vPis", $vPis);
                    $piscofins->addChild("vCofins", $vCofins);

                    // 1 = Retido, 2 = Não retido
                    $piscofins->addChild("tpRetPisCofins", "2");
                }

                // IRRF (pode vir da regra de 1,5% ou do IR informado)
                if ($valorRetIRRF > 0 || $valorRetIR > 0) {
                    $valorRetIRTotal = $valorRetIR > 0 ? $valorRetIR : $valorRetIRRF;
                    $valorRetIRTotal = number_format($valorRetIRTotal, 2, ".", "");

                    $triFed->addChild("vRetIRRF", $valorRetIRTotal);
                }

                if ($valorRetOutros > 0) {
                    // CSLL como elemento separado
                    if ($aliqCsll > 0) {
                        $vCsll = round($base * ($aliqCsll / 100), 2);
                        $vCsll = number_format($vCsll, 2, ".", "");

                        $triFed->addChild("vRetCSLL", $vCsll);
                    }
                }

                $vTotTribFed = $valorRetOutros + $valorRetIRTotal;
                $vTotTribMun = $valorRetISS;

                $vTotTribFedf = number_format($vTotTribFed, 2, ".", "");
                $vTotTribMunf = number_format($vTotTribMun, 2, ".", "");
            }

            // totTrib - Total de tributos (campo obrigatório, mas pode ser vazio)
            $totTrib = $trib->addChild("totTrib");

            $vTotTrib = $totTrib->addChild("vTotTrib");
            $vTotTrib->addChild("vTotTribFed", $vTotTribFedf);
            $vTotTrib->addChild("vTotTribEst", 0);
            $vTotTrib->addChild("vTotTribMun", $vTotTribMunf);

            return array(
                "sucesso" => true,
                "conteudoXML" => $xml->asXML()
            );

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 400);
        }
    }


    /**
     * Gera o XML de pedido de registro de evento (pedRegEvento) para cancelamento
     * Formato simplificado sem o envelope <evento>
     *
     * @param array $dados
     * @return array { sucesso: bool, conteudoXML: string }
     * @throws Exception
     */
    public static function gerarXMLcancelamento($dados)
    {
        try {
            $tpAmb = isset($dados['dadosAmbiente']['ambiente']) ? (string) $dados['dadosAmbiente']['ambiente'] : '2';
            $verAplic = 'SefinNacional_1.4.0';
            $dhEvento = date('Y-m-d\TH:i:sP');

            // chNFSe deve ser numérico (limpo) - Chave de acesso com 50 dígitos
            $chNFSe = isset($dados['chaveAcesso']) ? preg_replace('/\D/', '', $dados['chaveAcesso']) : '';

            // Validação da chave
            if (strlen($chNFSe) !== 50) {
                throw new Exception("Chave de acesso inválida. Deve ter 50 dígitos.", 400);
            }

            // Autor do evento (CNPJ ou CPF do prestador)
            $inscFederal = isset($dados['dadosAmbiente']['inscricaoFederal']) ? preg_replace('/\D/', '', $dados['dadosAmbiente']['inscricaoFederal']) : '';
            $isCPF = strlen($inscFederal) === 11;

            // Número sequencial do cancelamento (cancel_id_val) - 3 dígitos
            $nSeqEvento = isset($dados['nSeqEvento']) ? (int) $dados['nSeqEvento'] : 1;
            $nPedRegEventoFmt = str_pad($nSeqEvento, 3, '0', STR_PAD_LEFT);

            // Tipo do evento (6 dígitos) - e101101 = 101101
            $tpEvento = '101101';

            // ID do pedRegEvento: PRE + chave(50) + tipoEvento(6) + nPedRegEvento(3) = 62 chars
            $idPedReg = 'PRE' . $chNFSe . $tpEvento . $nPedRegEventoFmt;

            // Mensagens
            $xDesc = 'Cancelamento de NFS-e';
            $cMotivo = isset($dados['codigoMotivo']) ? (string) $dados['codigoMotivo'] : '2';
            $xMotivo = isset($dados['motivoCancelamento']) && trim($dados['motivoCancelamento']) !== ''
                ? trim($dados['motivoCancelamento'])
                : 'Serviço não prestado';

            // Raiz: pedRegEvento
            $xml = new SimpleXMLElement('<pedRegEvento xmlns="http://www.sped.fazenda.gov.br/nfse"></pedRegEvento>');
            $xml->addAttribute('versao', $dados['dadosAmbiente']['versao']);

            // infPedReg
            $infPedReg = $xml->addChild('infPedReg');
            $infPedReg->addAttribute('Id', $idPedReg);

            $infPedReg->addChild('tpAmb', $tpAmb);
            $infPedReg->addChild('verAplic', $verAplic);
            $infPedReg->addChild('dhEvento', $dhEvento);

            // Autor (CPF ou CNPJ)
            if ($isCPF) {
                $infPedReg->addChild('CPFAutor', $inscFederal);
            } else {
                $infPedReg->addChild('CNPJAutor', $inscFederal);
            }

            $infPedReg->addChild('chNFSe', $chNFSe);
            $infPedReg->addChild('nPedRegEvento', $nPedRegEventoFmt);

            // Evento de cancelamento: e101101
            $e101101 = $infPedReg->addChild('e101101');
            $e101101->addChild('xDesc', $xDesc);
            $e101101->addChild('cMotivo', $cMotivo);
            $e101101->addChild('xMotivo', $xMotivo);

            return array(
                'sucesso' => true,
                'conteudoXML' => $xml->asXML()
            );
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 400);
        }
    }

    /**
     * Gera o ID da DPS conforme padrão do schema TSIdDPS
     * Formato (45 posições): "DPS" + Cód.Mun(7) + Tipo Insc.Federal(1) + Inscrição Federal(14) + Série DPS(5) + Núm. DPS(15)
     * 
     * Exemplo: DPS420540720874722700010700900000000000000001
     * - DPS = Prefixo literal
     * - 4205407 = Código IBGE do município
     * - 2 = Tipo de inscrição (1=CPF, 2=CNPJ)
     * - 08747227000107 = CNPJ com 14 dígitos
     * - 00900 = Série "900" formatada com 5 dígitos
     * - 000000000000001 = Número "1" formatado com 15 dígitos
     * Essa demorou para descobrir :)
     * 
     * @param string $inscricaoFederal CPF ou CNPJ do prestador (com ou sem formatação)
     * @param string $codMunicipio Código IBGE do município (7 dígitos)
     * @param string $serieDPS Série do DPS (até 5 dígitos)
     * @param int $numeroDPS Número sequencial do DPS (até 15 dígitos)
     * @return string ID da DPS com 45 posições
     */
    private static function gerarIdDPS($inscricaoFederal, $codMunicipio, $numeroDPS, $serieDPS = "900")
    {
        // Remove formatação
        $inscricaoLimpa = preg_replace('/\D/', '', $inscricaoFederal);
        $codMunicipioLimpo = preg_replace('/\D/', '', $codMunicipio);

        // Determina tipo de inscrição: 1=CPF, 2=CNPJ
        $tipoInscricao = (strlen($inscricaoLimpa) == 11) ? '1' : '2';

        // Completa inscrição federal para 14 dígitos (CPF com zeros à esquerda)
        $inscricaoCompleta = str_pad($inscricaoLimpa, 14, '0', STR_PAD_LEFT);

        // Completa código do município para 7 dígitos
        $codMunCompleto = str_pad($codMunicipioLimpo, 7, '0', STR_PAD_LEFT);

        // Completa série para 5 dígitos
        $serieCompleta = str_pad($serieDPS, 5, '0', STR_PAD_LEFT);

        // Completa número do DPS para 15 dígitos
        $numeroCompleto = str_pad($numeroDPS, 15, '0', STR_PAD_LEFT);

        // Monta o ID: DPS + 42 dígitos (7+1+14+5+15)
        return "DPS" . $codMunCompleto . $tipoInscricao . $inscricaoCompleta . $serieCompleta . $numeroCompleto;
    }

    /**
     * Transforma um array de erros em uma única string, respeitando o limite de 255 caracteres
     * - Array de arrays com 'Codigo' e 'Descricao'
     * - Array simples de strings
     * - Array com chaves customizadas
     * 
     * @param mixed $erros Array de erros (pode ser array ou string)
     * @param string $separador Separador entre erros (padrão: '; ')
     * @return string Erros concatenados truncados a 255 caracteres
     */
    public static function formatarErrosEmString($erros, $separador = '; ')
    {
        $mensagem = '';

        // Se não for array, converter para string simples
        if (!is_array($erros)) {
            $mensagem = (string) $erros;
        } else if (!empty($erros)) {
            $partes = array();

            foreach ($erros as $erro) {
                $linhaErro = '';

                if (is_array($erro)) {
                    $codigo = null;
                    $descricao = null;

                    foreach ($erro as $chave => $valor) {
                        $chaveUpper = strtoupper($chave);
                        
                        if (in_array($chaveUpper, ['CODIGO', 'CODE', 'ERROR_CODE', 'ERRORCODE', 'ERRO_CODIGO'])) {
                            $codigo = $valor;
                        } elseif (in_array($chaveUpper, ['DESCRICAO', 'DESCRIPTION', 'DESC', 'MESSAGE', 'ERROR', 'MENSAGEM', 'MOTIVO'])) {
                            $descricao = $valor;
                        }
                    }

                    if ($codigo === null && $descricao === null) {
                        $valores = array_values($erro);
                        if (count($valores) > 0) {
                            $descricao = (string) $valores[0];
                        }
                        if (count($valores) > 1) {
                            $codigo = (string) $valores[1];
                        }
                    }

                    if (!empty($codigo) && !empty($descricao)) {
                        $linhaErro = "[{$codigo}] {$descricao}";
                    } elseif (!empty($descricao)) {
                        $linhaErro = $descricao;
                    } elseif (!empty($codigo)) {
                        $linhaErro = $codigo;
                    }
                } else {
                    $linhaErro = (string) $erro;
                }

                if (!empty($linhaErro)) {
                    $partes[] = $linhaErro;
                }
            }

            $mensagem = implode($separador, $partes);
        }

        // Truncar a string a 255 caracteres se necessário
        if (strlen($mensagem) > 255) {
            $mensagem = substr($mensagem, 0, 252) . '...';
        }

        return $mensagem;
    }

    /**
     * Sanitiza texto para ser usado em tags XML, escapando caracteres especiais
     * Converte: & < > " ' em suas entidades XML correspondentes
     * 
     * @param string $texto
     * @return string
     */
    private static function sanitizarXML($texto)
    {
        $texto = trim($texto ?? '');
        return htmlspecialchars($texto, ENT_XML1);
    }

    /**
     * Remove caracteres especiais de strings (pontos, hífens, barras, etc)
     * 
     * @param string $value
     * @return string
     */
    private static function clean($value)
    {
        return str_replace(array('.', '-', '/', '(', ')', ' '), '', $value);
    }

    public static function encodeGzipB64($string)
    {
        $gz = gzencode($string, 9);

        return base64_encode($gz);
    }

    public static function decodeGzipB64($string)
    {
        $b64 = base64_decode($string);

        return gzdecode($b64);
    }

    public static function gzipB64ToB64($string)
    {
        $b64 = base64_decode($string);
        $b64Gzip = gzdecode($b64);

        return base64_encode($b64Gzip);
    }
}