<?php
class Danfse
{
    private $infNFSe;
    private $infDPS;
    private $chave;
    private $cancelada;

    private static $tpEmitDesc = [
        '1' => 'Prestador',
        '2' => 'Tomador',
        '3' => 'Intermedi&aacute;rio'
    ];
    private static $finNFSeDesc = [
        '1' => 'NFS-e regular',
        '2' => 'NFS-e complementar',
        '3' => 'NFS-e de ajuste',
        '4' => 'NFS-e de substitui&ccedil;&atilde;o',
    ];
    private static $tribISSQNDesc = [
        '1' => 'Opera&ccedil;&atilde;o tribut&aacute;vel',
        '2' => 'Imunidade',
        '3' => 'Exporta&ccedil;&atilde;o de servi&ccedil;o',
        '4' => 'N&atilde;o Incid&ecirc;ncia'
    ];
    private static $tpRetISSQNDesc = [
        '1' => 'N&atilde;o Retido',
        '2' => 'Retido pelo Tomador',
        '3' => 'Retido pelo Intermediario'
    ];
    private static $regEspTribDesc = [
        '0' => 'Nenhum',
        '1' => 'Ato Cooperado (Cooperativa)',
        '2' => 'Estimativa',
        '3' => 'Microempresa Municipal',
        '4' => 'Not&aacute;rio ou Registrador',
        '5' => 'Profissional Aut&ocirc;nomo',
        '6' => 'Sociedade de Profissionais',
        '9' => 'Outros'
    ];
    private static $opSimpNacDesc = [
        '1' => 'N&atilde;o Optante',
        '2' => 'Optante - Microempreendedor Individual (MEI)',
        '3' => 'Optante - Microempresa ou Empresa de Pequeno Porte (ME/EPP)'
    ];
    private static $regApTribSNDesc = [
        '1' => '1 - Regime de apura&ccedil;&atilde;o dos tributos federais e municipal pelo SN',
        '2' => '2 - Regime de apura&ccedil;&atilde;o dos tributos federais pelo SN e ISSQN  por fora do SN conforme respectiva legisla&ccedil;&atilde;o municipal do tributo',
        '3' => '3 - Regime de apura&ccedil;&atilde;o dos tributos federais e municipal por fora do SN conforme respectivas legisla&ccedil;&otilde;es federal e municipal de cada tributo'
    ];
    private static $tpImunidadeDesc = [
        '0' => '0 - Imunidade (tipo n&atilde;o informado na nota de origem)',
        '1' => '1 - Patrim&ocirc;nio, renda ou servi&ccedil;os, uns dos outros (CF88, Art 150, VI, a)',
        '2' => '2 - Templos de qualquer culto (CF88, Art 150, VI, b)',
        '3' => '3 - Patrim&ocirc;nio, renda ou servi&ccedil;os dos partidos pol&iacute;ticos, inclusive suas funda&ccedil;&otilde;es, das entidades sindicais dos trabalhadores, das institui&ccedil;&otilde;es de educa&ccedil;&atilde;o e de assist&ecirc;ncia social, sem fins lucrativos, atendidos os requisitos da lei (CF88, Art 150, VI, c)',
        '4' => '4 - Livros, jornais, peri&oacute;dicos e o papel destinado a sua impress&atilde;o (CF88, Art 150, VI, d)',
        '5' => '5 - Fonogramas e videofonogramas musicais produzidos no Brasil contendo obras musicais ou literomusicais de autores brasileiros e/ou obras em geral interpretadas por artistas brasileiros bem como os suportes materiais ou arquivos digitais que os contenham, salvo na etapa de replica&ccedil;&atilde;o industrial de m&iacute;dias &oacute;pticas de leitura a laser.   (CF88, Art 150, VI, e)'
    ];
    private static $tpBMDesc = [
        "1" => "1) Isen&ccedil;&atilde;o",
        "2" => "2) Redu&ccedil;&atilde;o da BC em 'ppBM' %",
        "3" => "3) Redu&ccedil;&atilde;o da BC em R$ 'vInfoBM'",
        "4" => "4) l&iacute;quota Diferenciada de 'aliqDifBM'"
    ];
    private static $tpRetPisCofinsDesc = [
        '0' => '0 - PIS/COFINS/CSLL N&atilde;o Retidos',
        '1' => '1 - PIS/COFINS Retidos',
        '2' => '2 - PIS/COFINS N&atilde;o Retidos',
        '3' => '3 - PIS/COFINS/CSLL Retidos',
        '4' => '4 - PIS/COFINS Retidos, CSLL N&atilde;o Retido',
        '5' => '5 - PIS Retido, COFINS/CSLL N&atilde;o Retido',
        '6' => '6 - COFINS Retido, PIS/CSLL N&atilde;o Retido',
        '7' => '7 - PIS N&atilde;o Retido, COFINS/CSLL Retidos',
        '8' => '8 - PIS/COFINS N&atilde;o Retidos, CSLL Retido',
        '9' => '9 - COFINS N&atilde;o Retido, PIS/CSLL Retidos'
    ];
    private static $ufMap = [
        '11' => 'RO',
        '12' => 'AC',
        '13' => 'AM',
        '14' => 'RR',
        '15' => 'PA',
        '16' => 'AP',
        '17' => 'TO',
        '21' => 'MA',
        '22' => 'PI',
        '23' => 'CE',
        '24' => 'RN',
        '25' => 'PB',
        '26' => 'PE',
        '27' => 'AL',
        '28' => 'SE',
        '29' => 'BA',
        '31' => 'MG',
        '32' => 'ES',
        '33' => 'RJ',
        '35' => 'SP',
        '41' => 'PR',
        '42' => 'SC',
        '43' => 'RS',
        '50' => 'MS',
        '51' => 'MT',
        '52' => 'GO',
        '53' => 'DF',
    ];

    public function __construct($xmlString, $chaveAcesso, $cancelada = false)
    {
        $xml = preg_replace('/xmlns[^=]*="[^"]*"/i', '', $xmlString);
        $obj = simplexml_load_string($xml);
        if (!$obj)
            throw new Exception('XML invalido');
        $this->chave = $chaveAcesso;
        $this->cancelada = (bool) $cancelada;
        $this->infNFSe = $obj->infNFSe;
        $this->infDPS = $obj->infNFSe->DPS->infDPS;
    }

    public function generate()
    {
        $autoload = dirname(dirname(__DIR__)) . '/lib/vendor/autoload.php';
        if (!file_exists($autoload))
            throw new Exception('Autoload nao encontrado');
        require_once $autoload;

        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'helvetica');
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($this->buildHtml(), 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        return $dompdf->output();
    }

    /* ===== HTML ===== */
    private function buildHtml()
    {
        $homologWarn = ($this->v($this->infDPS->tpAmb) === '2')
            ? '<div style="font-family:Arial,Helvetica,sans-serif;font-size:8pt;font-weight:bold;color:#FF0000;text-align:center;margin-top:1mm;">NFS-e SEM VALIDADE JUR&Iacute;DICA</div>'
            : '';

        return '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>'
            . $this->css()
            . '</style></head><body>'
            . '<div class="danfse">'
            . $this->buildWatermark()
            . $this->secCabecalho($homologWarn)
            . $this->secEmitente()
            . $this->secTomador()
            . $this->secIntermediario()
            . $this->secServico()
            . $this->secTribMunicipal()
            . $this->secTribFederal()
            . $this->secValorTotal()
            . $this->secTotaisAproximados()
            . $this->secInfoCompl()
            . '</div>'
            . '</body></html>';
    }

    /* ===== CSS ===== */
    private function css()
    {
        return '
@page { margin:0; size:A4 portrait; }
*     { margin:0; padding:0; box-sizing:border-box; }
body  { font-family:Helvetica,Arial,sans-serif; font-size:7pt; color:#000; margin:1.6mm; }

.danfse { border:1pt solid #000; padding:1mm; position:relative; min-height:288mm; z-index: 1}

.watermark {
    position:fixed; top:42%; left:50%; width:220mm; margin-left:-110mm;
    text-align:center; font-family:Arial,Helvetica,sans-serif; font-size:75pt;
    color:#595959; opacity:1; transform:rotate(-45deg); z-index: -2;
}
.watermark-cancel { color:#e8e8e8; opacity:1; }

table { width:100%; border-collapse:collapse; table-layout:fixed; }
td    { vertical-align:top; padding:0.8mm 1.2mm; font-size:7pt; overflow:hidden; }

/* Labels */
.lbl    { display:block; font-family:Arial,Helvetica,sans-serif; font-size:6pt; font-weight:bold; color:#000; line-height:1.2; }
.lbl-id { display:block; font-family:Arial,Helvetica,sans-serif; font-size:7pt; font-weight:bold; color:#000; line-height:1.2; }
.val    { display:block; font-size:7pt; color:#000; margin-top:0.3mm; line-height:1.3; }

/* Separador de bloco: borda superior na primeira linha de cada secao */
.blk-row td { border-top:0.5pt solid #000; }

/* Celula titulo inline (Emitente, Tomador) */
.sec-title {
    font-family:Arial,Helvetica,sans-serif;
    font-size:7pt; font-weight:bold;
    vertical-align:middle;
}

/* Cabecalho de bloco full-width (Servico, Tribut., etc.) */
.blk-full td {
    font-family:Arial,Helvetica,sans-serif;
    font-size:7pt; font-weight:bold;
    text-transform:uppercase;
    border-top:0.5pt solid #000;
    padding:0.8mm 1.2mm;
}

/* Larguras */
.w20  { width:20%; }
.w25  { width:25%; }
.w33  { width:33.33%; }
.w40  { width:40%; }
.w50  { width:50%; }
.w75  { width:75%; }
.w80  { width:80%; }
.w100 { width:100%; }

/* QR auth text */
.qr-txt { font-family:Helvetica,Arial,sans-serif; font-size:5.5pt; line-height:1.3; text-align:center; color:#000; margin-top:0.8mm; }

/* Totais aproximados */
.tot-blk { text-align:center; vertical-align:middle; }
.tot-lbl { font-family:Arial,Helvetica,sans-serif; font-size:6pt; font-weight:bold; text-align:center; display:block; }
.tot-val { font-family:Helvetica,Arial,sans-serif; font-size:7pt; text-align:center; display:block; margin-top:0.3mm; }
';
    }

    /* ===== CABECALHO ===== */
    private function secCabecalho($homologWarn)
    {
        $city = $this->v($this->infNFSe->xLocEmi);
        $email = "nfse@pmf.sc.gov.br";

        $nNFSe = $this->v($this->infNFSe->nNFSe);
        $dComp = $this->fmtDate($this->infDPS->dCompet);
        $dhProc = $this->fmtDateTime($this->infNFSe->dhProc);
        $nDPS = $this->v($this->infDPS->nDPS);
        $serie = $this->v($this->infDPS->serie);
        $dhEmi = $this->fmtDateTime($this->infDPS->dhEmi);

        $chaveUrl = preg_replace('/^[A-Za-z]+/', '', $this->chave);
        $qrUrl = 'https://www.nfse.gov.br/ConsultaPublica/?tpc=1&chave=' . $chaveUrl;
        $qrImg = $this->qrImg($qrUrl);
        $logo = $this->getLogo();
        $escudo = $this->getEscudoImg();

        $prefNome = $city ? 'PREFEITURA MUNICIPAL DE ' . mb_strtoupper($city, 'UTF-8') : '';
        $emailLine = $email
            ? '<div style="font-family:Helvetica,Arial,sans-serif;font-size:5.5pt;margin-top:0.5mm;color:#333;">' . $this->h($email) . '</div>'
            : '';

        $pad = '0.5mm 1.2mm';

        $tblHeader = '
<table style="border-bottom:0.5pt solid #000;">
<colgroup>
  <col style="width:20%">
  <col style="width:40%">
  <col style="width:40%">
</colgroup>
<tr>
  <td style="text-align:left; vertical-align:middle; padding:1.5mm;">' . $logo . '</td>
  <td style="text-align:center; vertical-align:middle; padding:1.5mm;">
    <div style="font-family:Arial,Helvetica,sans-serif;font-size:10pt;font-weight:bold;line-height:1.2;">DANFSe v1.0</div>
    <div style="font-family:Arial,Helvetica,sans-serif;font-size:8pt;font-weight:bold;line-height:1.2;">Documento Auxiliar da NFS-e</div>
    ' . $homologWarn . '
  </td>
  <td style="vertical-align:middle; padding:1mm 1.5mm;">
    <table style="width:100%; border-collapse:collapse;">
    <tr>
      <td style="width:22%; text-align:center; vertical-align:middle; padding:0;">' . $escudo . '</td>
      <td style="vertical-align:middle; padding:0 0 0 0;">
        <div style="font-family:Arial,Helvetica,sans-serif;font-size:7pt;font-weight:bold;line-height:1.3;">' . $this->h($prefNome) . '</div>
        ' . $emailLine . '
      </td>
    </tr>
    </table>
  </td>
</tr>
</table>';

        $tblIdent = '
<table style="">
<colgroup>
  <col style="width:26.67%">
  <col style="width:26.67%">
  <col style="width:26.66%">
  <col style="width:20%">
</colgroup>
<tr>
  <td colspan="3" style="padding:' . $pad . ';">
    <span class="lbl-id">Chave de Acesso da NFS-e</span>
    <span class="val" style="word-break:break-all;letter-spacing:0.3pt;">' . $this->h($this->chave) . '</span>
  </td>
  <td rowspan="3" style="text-align:center; vertical-align:middle; padding:1mm;">
    ' . $qrImg . '
    <div class="qr-txt" style="margin-top:1mm;text-align:left;">A autenticidade desta NFS-e pode ser verificada pela leitura deste c&oacute;digo QR ou pela consulta da chave de acesso no portal nacional da NFS-e</div>
  </td>
</tr>
<tr>
  <td style="padding:' . $pad . ';">
    <span class="lbl-id">N&uacute;mero da NFS-e</span>
    <span class="val">' . $this->h($this->dash($nNFSe)) . '</span>
  </td>
  <td style="padding:' . $pad . ';">
    <span class="lbl-id">Compet&ecirc;ncia da NFS-e</span>
    <span class="val">' . $this->h($this->dash($dComp)) . '</span>
  </td>
  <td style="padding:' . $pad . ';">
    <span class="lbl-id">Data e Hora da Emiss&atilde;o da NFS-e</span>
    <span class="val">' . $this->h($this->dash($dhProc)) . '</span>
  </td>
</tr>
<tr>
  <td style="padding:' . $pad . ';">
    <span class="lbl-id">N&uacute;mero da DPS</span>
    <span class="val">' . $this->h($this->dash($nDPS)) . '</span>
  </td>
  <td style="padding:' . $pad . ';">
    <span class="lbl-id">S&eacute;rie da DPS</span>
    <span class="val">' . $this->h($this->dash($serie)) . '</span>
  </td>
  <td style="padding:' . $pad . ';">
    <span class="lbl-id">Data e Hora da Emiss&atilde;o da DPS</span>
    <span class="val">' . $this->h($this->dash($dhEmi)) . '</span>
  </td>
</tr>
</table>';

        return $tblHeader . $tblIdent;
    }

    /* ===== EMITENTE ===== */
    private function secEmitente()
    {
        $prest = $this->infDPS->prest;
        $emit = $this->infNFSe->emit;

        $tpEmit = $this->desc(self::$tpEmitDesc, $this->v($this->infDPS->tpEmit));
        $cnpj = $this->fmtDoc(
            $this->v($prest->CNPJ) ?: $this->v($prest->CPF) ?: $this->v($prest->NIF)
            ?: $this->v($emit->CNPJ) ?: $this->v($emit->CPF)
        );
        $im = $this->v($prest->IM) ?: $this->v($emit->IM);
        $fone = $this->fmtPhone($this->v($prest->fone) ?: $this->v($emit->fone));
        $xNome = $this->v($prest->xNome) ?: $this->v($emit->xNome);
        $email = $this->v($prest->email) ?: $this->v($emit->email);

        $endNac = $emit->enderNac ?? null;
        $endStr = $this->getEndStrEmit($endNac);
        $city = $this->getMunCity($endNac, $this->v($this->infNFSe->xLocEmi ?? null));
        $cep = $this->fmtCEP($this->v($endNac->CEP ?? null));

        $opSN = $this->desc(self::$opSimpNacDesc, $this->v($prest->regTrib->opSimpNac ?? null));
        $regSN = $this->desc(self::$regApTribSNDesc, $this->v($prest->regTrib->regApTribSN ?? null));

        return '
<table>
<tr class="blk-row">
  <td class="w25 sec-title">EMITENTE DA NFS-e<br><span style="font-size:6pt;font-weight:normal;text-transform:none;">' . $tpEmit . '</span></td>
  <td class="w25">' . $this->field('CNPJ / CPF / NIF', $this->h($this->dash($cnpj))) . '</td>
  <td class="w25">' . $this->field('Inscri&ccedil;&atilde;o Municipal', $this->h($this->dash($im))) . '</td>
  <td class="w25">' . $this->field('Telefone', $this->h($this->dash($fone))) . '</td>
</tr>
<tr class="dr">
  <td class="w50" colspan="2">' . $this->field('Nome / Nome Empresarial', $this->h($this->dash($xNome))) . '</td>
  <td class="w50" colspan="2">' . $this->field('E-mail', $this->h($this->dash($email))) . '</td>
</tr>
<tr class="dr">
  <td class="w50" colspan="2">' . $this->field('Endere&ccedil;o', $this->h($this->dash($endStr))) . '</td>
  <td class="w25">' . $this->field('Munic&iacute;pio', $this->h($this->dash($city))) . '</td>
  <td class="w25">' . $this->field('CEP', $this->h($this->dash($cep))) . '</td>
</tr>
<tr class="dr-last">
  <td class="w25">' . $this->field('Simples Nacional Na Data De Compet&ecirc;ncia', $opSN) . '</td>
  <td class="w75" colspan="3">' . $this->field('Regime De Apura&ccedil;&atilde;o Tribut&aacute;ria Pelo SN', $regSN) . '</td>
</tr>
</table>';
    }

    /* ===== TOMADOR ===== */
    private function secTomador()
    {
        $tomaNome = $this->v($this->infDPS->toma->xNome ?? null);
        $tomaCNPJ = $this->v($this->infDPS->toma->CNPJ ?? null)
            ?: $this->v($this->infDPS->toma->CPF ?? null)
            ?: $this->v($this->infDPS->toma->NIF ?? null);

        if (!$tomaNome && !$tomaCNPJ) {
            return '
<table>
<tr class="blk-row">
  <td class="w25 sec-title">TOMADOR DO SERVI&Ccedil;O</td>
  <td colspan="3"><span class="val">TOMADOR/ADQUIRENTE DA OPERA&Ccedil;&Atilde;O N&Atilde;O IDENTIFICADO NA NFS-e</span></td>
</tr>
</table>';
        }

        $toma = $this->infDPS->toma;
        $end = $toma->end ?? null;
        $cnpj = $this->fmtDoc($tomaCNPJ);
        $im = $this->v($toma->IM ?? null);
        $fone = $this->fmtPhone($this->v($toma->fone ?? null));
        $email = $this->v($toma->email ?? null);
        $endStr = $this->getEndStrToma($end);
        $city = $this->getMunCityToma($end);
        $cep = $this->fmtCEP($this->v($end->endNac->CEP ?? null));

        return '
<table>
<tr class="blk-row">
  <td class="w25 sec-title">TOMADOR DO SERVI&Ccedil;O</td>
  <td class="w25">' . $this->field('CNPJ / CPF / NIF', $this->h($this->dash($cnpj))) . '</td>
  <td class="w25">' . $this->field('Inscri&ccedil;&atilde;o Municipal', $this->h($this->dash($im))) . '</td>
  <td class="w25">' . $this->field('Telefone', $this->h($this->dash($fone))) . '</td>
</tr>
<tr class="dr">
  <td class="w50" colspan="2">' . $this->field('Nome / Nome Empresarial', $this->h($this->dash($tomaNome))) . '</td>
  <td class="w50" colspan="2">' . $this->field('E-mail', $this->h($this->dash($email))) . '</td>
</tr>
<tr class="dr-last">
  <td class="w50" colspan="2">' . $this->field('Endere&ccedil;o', $this->h($this->dash($endStr))) . '</td>
  <td class="w25">' . $this->field('Munic&iacute;pio', $this->h($this->dash($city))) . '</td>
  <td class="w25">' . $this->field('CEP', $this->h($this->dash($cep))) . '</td>
</tr>
</table>';
    }

    /* ===== INTERMEDIARIO ===== */
    private function secIntermediario()
    {
        $intermNome = $this->v($this->infDPS->interm->xNome ?? null);
        $intermCNPJ = $this->v($this->infDPS->interm->CNPJ ?? null)
            ?: $this->v($this->infDPS->interm->CPF ?? null)
            ?: $this->v($this->infDPS->interm->NIF ?? null);

        if (!$intermNome && !$intermCNPJ) {
            return '
<table>
<tr class="blk-full"><td colspan="4" style="text-align:center;">INTERMEDI&Aacute;RIO DO SERVI&Ccedil;O N&Atilde;O IDENTIFICADO NA NFS-e</td></tr>
</table>';
        }

        $interm = $this->infDPS->interm;
        $end = $interm->end ?? null;
        $cnpj = $this->fmtDoc($intermCNPJ);
        $im = $this->v($interm->IM ?? null);
        $fone = $this->fmtPhone($this->v($interm->fone ?? null));
        $email = $this->v($interm->email ?? null);
        $endStr = $this->getEndStrToma($end);
        $city = $this->getMunCityToma($end);
        $cep = $this->fmtCEP($this->v($end->endNac->CEP ?? null));

        return '
<table>
<tr class="blk-row">
  <td class="w25 sec-title">INTERMEDI&Aacute;RIO DO SERVI&Ccedil;O</td>
  <td class="w25">' . $this->field('CNPJ / CPF / NIF', $this->h($this->dash($cnpj))) . '</td>
  <td class="w25">' . $this->field('Inscri&ccedil;&atilde;o Municipal', $this->h($this->dash($im))) . '</td>
  <td class="w25">' . $this->field('Telefone', $this->h($this->dash($fone))) . '</td>
</tr>
<tr class="dr">
  <td class="w50" colspan="2">' . $this->field('Nome / Nome Empresarial', $this->h($this->dash($intermNome))) . '</td>
  <td class="w50" colspan="2">' . $this->field('E-mail', $this->h($this->dash($email))) . '</td>
</tr>
<tr class="dr-last">
  <td class="w50" colspan="2">' . $this->field('Endere&ccedil;o', $this->h($this->dash($endStr))) . '</td>
  <td class="w25">' . $this->field('Munic&iacute;pio', $this->h($this->dash($city))) . '</td>
  <td class="w25">' . $this->field('CEP', $this->h($this->dash($cep))) . '</td>
</tr>
</table>';
    }

    /* ===== SERVICO ===== */
    private function secServico()
    {
        $serv = $this->infDPS->serv;
        $cServ = $serv->cServ;

        $cTribNac = $this->v($cServ->cTribNac ?? null) ?: $this->v($this->infNFSe->cTribNac ?? null);
        $cTribMun = $this->v($cServ->cTribMun ?? null) ?: $this->v($this->infNFSe->cTribMun ?? null);

        if (preg_match('/^(\d{2})(\d{2})(\d{2})$/', preg_replace('/\D/', '', $cTribNac), $pm))
            $cTribNacFmt = $pm[1] . '.' . $pm[2] . '.' . $pm[3];
        else
            $cTribNacFmt = $cTribNac;

        $xTribNac = $this->v($this->infNFSe->xTribNac ?? null);
        $xTribMun = $this->v($this->infNFSe->xTribMun ?? null);
        $descTrib = $xTribNac ?: $xTribMun;
        if ($descTrib && $cTribNacFmt)
            $cTribNacFmt = $cTribNacFmt . ' - ' . $this->trunc($descTrib, 60);

        $locVal = $this->v($this->infNFSe->xLocPrestacao ?? null)
            ?: $this->v($serv->locPrest->cLocPrestacao ?? null);
        $ufL = $locVal ? $this->ufFromCode($this->v($serv->locPrest->cLocPrestacao ?? null)) : '';
        $localStr = trim($locVal . ($ufL ? ' - ' . $ufL : ''), ' -');
        $cPais = $this->v($serv->locPrest->cPaisPrestacao ?? null);

        $descServ = $this->trunc(
            $this->v($cServ->xDescServ ?? null) ?: $this->v($serv->xDescServ ?? null),
            1300
        );

        return '
<table>
' . $this->sectionHeader('Servi&ccedil;o Prestado') . '
<tr class="dr">
  <td class="w25">' . $this->field('C&oacute;digo De Tributa&ccedil;&atilde;o Nacional', $this->h($this->dash($cTribNacFmt))) . '</td>
  <td class="w25">' . $this->field('C&oacute;digo De Tributa&ccedil;&atilde;o Municipal', $this->h($this->dash($cTribMun))) . '</td>
  <td class="w25">' . $this->field('Local Da Presta&ccedil;&atilde;o', $this->h($this->dash($localStr))) . '</td>
  <td class="w25">' . $this->field('Pa&iacute;s Da Presta&ccedil;&atilde;o', $this->h($this->dash($cPais))) . '</td>
</tr>
<tr class="dr-last">
  <td colspan="4"><span class="lbl">Descri&ccedil;&atilde;o Do Servi&ccedil;o</span><span class="val" style="min-height:8mm;display:block;">' . $this->h($this->dash($descServ)) . '</span></td>
</tr>
</table>';
    }

    /* ===== TRIBUTACAO MUNICIPAL ===== */
    private function secTribMunicipal()
    {
        $tribMun = $this->infDPS->valores->trib->tribMun ?? null;
        $tribISSQNDesc = $this->desc(self::$tribISSQNDesc, $this->v($tribMun->tribISSQN ?? null));

        $cPaisRes = $this->v($tribMun->cPaisResult ?? null);
        $xLocIncid = $this->v($this->infNFSe->xLocIncid ?? null) ?: $this->v($this->infNFSe->xLocEmi ?? null);
        $cMunIncid = $this->v($tribMun->cMunIncid ?? null);
        $ufI = $cMunIncid ? $this->ufFromCode($cMunIncid) : '';
        $incidStr = trim($xLocIncid . ($ufI ? ' - ' . $ufI : ''), ' -');

        $regEsp = $this->desc(self::$regEspTribDesc, $this->v($this->infDPS->prest->regTrib->regEspTrib ?? null));
        $tpImun = $this->desc(self::$tpImunidadeDesc, $this->v($tribMun->tpImunidade ?? null));

        $tpSuspCode = $this->v($tribMun->exigSusp->tpSusp ?? null);
        $tpSusp = ($tpSuspCode !== '') ? $this->desc(self::tpSuspDescV1(), $tpSuspCode) : 'N&atilde;o';
        $nProcesso = $this->v($tribMun->exigSusp->nProcesso ?? null);

        $tpBM = $this->desc(self::$tpBMDesc, $this->v($this->infNFSe->valores->tpBM ?? null));
        $vCalcBM = $this->fmtM($this->v($this->infNFSe->valores->vCalcBM ?? null)
            ?: $this->v($tribMun->BM->vRedBCBM ?? null));
        $vServ = $this->fmtM($this->v($this->infDPS->valores->vServPrest->vServ ?? null));
        $vDescIncond = $this->fmtM($this->v($this->infDPS->valores->vDescCondIncond->vDescIncond ?? null));
        $vDR = $this->fmtM($this->v($this->infDPS->valores->vDedRed->vDR ?? null));
        $vBC = $this->fmtM($this->v($this->infNFSe->valores->vBC ?? null));
        $pAliq = $this->fmtAliq($this->v($this->infNFSe->valores->pAliqAplic ?? null));
        $tpRet = $this->desc(self::$tpRetISSQNDesc, $this->v($tribMun->tpRetISSQN ?? null));
        $vISSQN = $this->fmtM($this->v($this->infNFSe->valores->vISSQN ?? null));

        return '
<table>
' . $this->sectionHeader('Tributa&ccedil;&atilde;o Municipal') . '
<tr class="dr">
  <td class="w25">' . $this->field('Tributa&ccedil;&atilde;o Do ISSQN', $tribISSQNDesc) . '</td>
  <td class="w25">' . $this->field('Pa&iacute;s Resultado Da Presta&ccedil;&atilde;o Do Servi&ccedil;o', $this->h($this->dash($cPaisRes))) . '</td>
  <td class="w25">' . $this->field('Munic&iacute;pio De Incid&ecirc;ncia Do ISSQN', $this->h($this->dash($incidStr))) . '</td>
  <td class="w25">' . $this->field('Regime Especial De Tributa&ccedil;&atilde;o', $regEsp) . '</td>
</tr>
<tr class="dr">
  <td class="w25">' . $this->field('Tipo De Imunidade', $tpImun) . '</td>
  <td class="w25">' . $this->field('Suspens&atilde;o Da Exigibilidade Do ISSQN', $tpSusp) . '</td>
  <td class="w25">' . $this->field('N&uacute;mero Processo Suspens&atilde;o', $this->h($this->dash($nProcesso))) . '</td>
  <td class="w25">' . $this->field('Benef&iacute;cio Municipal', $tpBM) . '</td>
</tr>
<tr class="dr">
  <td class="w25">' . $this->field('Valor Do Servi&ccedil;o', $this->h($vServ)) . '</td>
  <td class="w25">' . $this->field('Desconto Incondicionado', $this->h($vDescIncond)) . '</td>
  <td class="w25">' . $this->field('Total Dedu&ccedil;&otilde;es/Redu&ccedil;&otilde;es', $this->h($vDR)) . '</td>
  <td class="w25">' . $this->field('C&aacute;lculo Do BM', $this->h($vCalcBM)) . '</td>
</tr>
<tr class="dr-last">
  <td class="w25">' . $this->field('BC ISSQN', $this->h($vBC)) . '</td>
  <td class="w25">' . $this->field('Al&iacute;quota Aplicada', $this->h($pAliq)) . '</td>
  <td class="w25">' . $this->field('Reten&ccedil;&atilde;o Do ISSQN', $tpRet) . '</td>
  <td class="w25">' . $this->field('ISSQN Apurado', $this->h($vISSQN)) . '</td>
</tr>
</table>';
    }

    private static function tpSuspDescV1()
    {
        return [
            '1' => 'Exig. Suspensa - Decis&atilde;o Judicial',
            '2' => 'Exig. Suspensa - Proc. Administrativo',
        ];
    }

    /* ===== TRIBUTACAO FEDERAL ===== */
    private function secTribFederal()
    {
        $tribFed = $this->infDPS->valores->trib->tribFed ?? null;
        $pisconf = $tribFed->piscofins ?? null;
        $showPC = $this->isPisCofinsVisible();

        $vIRRF = $this->fmtM($this->v($tribFed->vRetIRRF ?? null));
        $vCP = $this->fmtM($this->v($tribFed->vRetCP ?? null));
        $vCSLL = $this->fmtM($this->v($tribFed->vRetCSLL ?? null));
        $vPis = $this->fmtM($this->v($pisconf->vPis ?? null));
        $vCofins = $this->fmtM($this->v($pisconf->vCofins ?? null));
        $tpRetPC = $this->desc(self::$tpRetPisCofinsDesc, $this->v($pisconf->tpRetPisCofins ?? null));

        $rowPC = '';
        if ($showPC) {
            $rowPC = '
<tr class="dr-last">
  <td class="w25">' . $this->field('PIS - D&eacute;bito Apura&ccedil;&atilde;o Pr&oacute;pria', $this->h($vPis)) . '</td>
  <td class="w25">' . $this->field('COFINS - D&eacute;bito Apura&ccedil;&atilde;o Pr&oacute;pria', $this->h($vCofins)) . '</td>
  <td class="w50" colspan="2"></td>
</tr>';
        }

        return '
<table>
' . $this->sectionHeader('Tributa&ccedil;&atilde;o Federal') . '
<tr class="' . ($showPC ? 'dr' : 'dr-last') . '">
  <td class="w25">' . $this->field('IRRF', $this->h($vIRRF)) . '</td>
  <td class="w25">' . $this->field('Contribui&ccedil;&atilde;o Previdenci&aacute;ria - Retida', $this->h($vCP)) . '</td>
  <td class="w25">' . $this->field('Contribui&ccedil;&otilde;es Sociais - Retidas', $this->h($vCSLL)) . '</td>
  <td class="w25">' . $this->field('Descri&ccedil;&atilde;o Contrib. Sociais - Retidas', $tpRetPC) . '</td>
</tr>' . $rowPC . '
</table>';
    }

    /* ===== VALOR TOTAL DA NFS-E ===== */
    private function secValorTotal()
    {
        $valDPS = $this->infDPS->valores ?? null;
        $valNFSe = $this->infNFSe->valores ?? null;
        $pisconf = $this->infDPS->valores->trib->tribFed->piscofins ?? null;
        $showPC = $this->isPisCofinsVisible();

        $vServ = $this->fmtM($this->v($valDPS->vServPrest->vServ ?? null));
        $vDescCond = $this->fmtM($this->v($valDPS->vDescCondIncond->vDescCond ?? null));
        $vDescIncond = $this->fmtM($this->v($valDPS->vDescCondIncond->vDescIncond ?? null));
        $vTotalRet = $this->fmtM($this->v($valNFSe->vTotalRet ?? null));
        $vLiq = $this->fmtM($this->v($valNFSe->vLiq ?? null));

        $tpRet = $this->v($valDPS->trib->tribMun->tpRetISSQN ?? null);
        $vISSQN = $this->v($valNFSe->vISSQN ?? null);
        $vRetISS = ($tpRet === '2' || $tpRet === '3') ? $this->fmtM($vISSQN) : '-';

        $fPis = (float) str_replace(',', '.', $this->v($pisconf->vPis ?? null));
        $fCofins = (float) str_replace(',', '.', $this->v($pisconf->vCofins ?? null));
        $pisCof = ($fPis + $fCofins > 0) ? 'R$ ' . number_format($fPis + $fCofins, 2, ',', '.') : '-';

        return '
<table>
' . $this->sectionHeader('Valor Total Da NFS-E') . '
<tr class="dr">
  <td class="w25">' . $this->field('Valor Do Servi&ccedil;o', $this->h($vServ)) . '</td>
  <td class="w25">' . $this->field('Desconto Condicionado', $this->h($vDescCond)) . '</td>
  <td class="w25">' . $this->field('Desconto Incondicionado', $this->h($vDescIncond)) . '</td>
  <td class="w25">' . $this->field('ISSQN Retido', $this->h($vRetISS)) . '</td>
</tr>
<tr class="dr-last">
  <td class="w25">' . $this->field('Total Das Reten&ccedil;&otilde;es Federais', $this->h($vTotalRet)) . '</td>
  <td class="w25">' . $this->field('PIS/COFINS - D&eacute;bito Apur. Pr&oacute;pria', ($showPC ? $this->h($pisCof) : '-')) . '</td>
  <td class="w25"></td>
  <td class="w25 gray" style="vertical-align:middle;">
    <span class="lbl">Valor L&iacute;quido Da NFS-e</span>
    <span class="val" style="font-weight:bold;">' . $this->h($vLiq) . '</span>
  </td>
</tr>
</table>';
    }

    /* ===== TOTAIS APROXIMADOS ===== */
    private function secTotaisAproximados()
    {
        $vTotTrib = $this->infDPS->valores->trib->totTrib->vTotTrib ?? null;

        $fedStr = $this->fmtMZero($this->v($vTotTrib->vTotTribFed ?? null));
        $estStr = $this->fmtMZero($this->v($vTotTrib->vTotTribEst ?? null));
        $munStr = $this->fmtMZero($this->v($vTotTrib->vTotTribMun ?? null));

        return '
<table>
<tr class="blk-full" style="text-align:left;"><td colspan="3">Totais Aproximados Dos Tributos</td></tr>
<tr class="dr-last">
  <td class="w33 tot-blk">
    <span class="tot-lbl">Federais</span>
    <span class="tot-val">' . $this->h($fedStr) . '</span>
  </td>
  <td class="w33 tot-blk">
    <span class="tot-lbl">Estaduais</span>
    <span class="tot-val">' . $this->h($estStr) . '</span>
  </td>
  <td class="w33 tot-blk">
    <span class="tot-lbl">Municipais</span>
    <span class="tot-val">' . $this->h($munStr) . '</span>
  </td>
</tr>
</table>';
    }

    /* ===== INFORMACOES COMPLEMENTARES ===== */
    private function secInfoCompl()
    {
        $serv = $this->infDPS->serv ?? null;
        $subst = $this->infDPS->subst ?? null;

        $parts = [];
        $xInfComp = $this->v($serv->infoCompl->xInfComp ?? null);
        if ($xInfComp)
            $parts[] = 'Inf Cont: ' . $xInfComp;
        $chSubst = $this->v($subst->chSubstda ?? null);
        if ($chSubst)
            $parts[] = 'NFS-e Subst.: ' . $chSubst;
        $cObra = $this->v($serv->obra->cObra ?? null);
        if ($cObra)
            $parts[] = 'Cod. Obra: ' . $cObra;
        $cNBS = $this->v($serv->cServ->cNBS ?? null);
        if ($cNBS)
            $parts[] = 'NBS: ' . $cNBS;
        $xOutInf = $this->v($this->infNFSe->xOutInf ?? null);
        if ($xOutInf)
            $parts[] = $xOutInf;

        $texto = implode(' | ', $parts);
        if (strlen($texto) > 1997)
            $texto = substr($texto, 0, 1994) . '...';

        if (!$texto)
            return '';

        return '
<table>
<tr class="blk-full"><td>Informa&ccedil;&otilde;es Complementares</td></tr>
<tr class="dr-last"><td style="min-height:8mm;"><span class="val">' . $this->h($texto) . '</span></td></tr>
</table>';
    }

    /* ===== AUXILIARES ===== */

    private function buildWatermark()
    {
        if ($this->cancelada)
            return '<div class="watermark watermark-cancel">CANCELADA</div>';

        $cStat = $this->v($this->infNFSe->cStat);
        $lower = strtolower($cStat);
        $cancelCodes = ['101', '102', '155', '301', '302'];
        $isCancel = in_array($cStat, $cancelCodes) || strpos($lower, 'cancel') !== false;

        if ($isCancel)
            return '<div class="watermark watermark-cancel">CANCELADA</div>';
        if (strpos($lower, 'substitu') !== false)
            return '<div class="watermark">SUBSTITU&Iacute;DA</div>';
        return '';
    }

    private function getLogo()
    {
        $file = dirname(dirname(__DIR__)) . '/assets/nfse-logo.png';
        if (file_exists($file)) {
            $b64 = base64_encode(file_get_contents($file));
            return '<img src="data:image/png;base64,' . $b64 . '" '
                . 'style="width:160px;height:auto;display:block;margin:0 auto;" />';
        }
        return '<div style="text-align:center;padding:1mm 0;">'
            . '<div style="font-family:Arial,Helvetica,sans-serif;font-weight:bold;font-size:16pt;line-height:1;">'
            . '<span style="color:#2D7D32;">NFS</span><span style="color:#1565C0;">e</span></div>'
            . '<div style="font-family:Helvetica,Arial,sans-serif;font-size:5pt;color:#757575;line-height:1.3;margin-top:1mm;">'
            . 'Nota Fiscal de<br>Servi&ccedil;o Eletr&ocirc;nica</div></div>';
    }

    private function getEscudoImg()
    {
        $file = dirname(dirname(__DIR__)) . '/assets/escudo_floripa.png';
        if (file_exists($file)) {
            $b64 = base64_encode(file_get_contents($file));
            return '<img src="data:image/png;base64,' . $b64 . '" '
                . 'style="max-height:11mm;max-width:100%;display:block;margin:0 auto;" />';
        }
        return '';
    }

    private function qrImg($url)
    {
        try {
            $b = new \Com\Tecnick\Barcode\Barcode();
            $o = $b->getBarcodeObj('QRCODE', $url, -1, -1, 'black', [0, 0, 0, 0]);
            $svg = $o->getSvgCode();
            return '<img src="data:image/svg+xml;base64,' . base64_encode($svg)
                . '" style="width:60px;height:60px;display:block;margin:0 auto;" />';
        } catch (\Exception $e) {
            return '<div style="width:60px;height:60px;border:1pt solid #000;text-align:center;'
                . 'font-size:5pt;padding-top:20px;margin:0 auto;">QR</div>';
        }
    }

    private function getEndStrEmit($endNac)
    {
        if (!$endNac)
            return '-';
        $parts = array_filter([
            $this->v($endNac->xLgr ?? null),
            $this->v($endNac->nro ?? null),
            $this->v($endNac->xCpl ?? null),
            $this->v($endNac->xBairro ?? null),
        ]);
        return $this->trunc(implode(', ', $parts), 80);
    }

    /* Endereco do toma/interm: xLgr etc sao filhos de end (nao de endNac) */
    private function getEndStrToma($end)
    {
        if (!$end)
            return '-';
        $parts = array_filter([
            $this->v($end->xLgr ?? null),
            $this->v($end->nro ?? null),
            $this->v($end->xCpl ?? null),
            $this->v($end->xBairro ?? null),
        ]);
        return $this->trunc(implode(', ', $parts), 80);
    }

    /* Municipio - UF a partir de enderNac do emit */
    private function getMunCity($endNac, $cityFallback = '')
    {
        if (!$endNac)
            return $cityFallback ? $this->dash($cityFallback) : '-';
        $cMun = $this->v($endNac->cMun ?? null);
        $xMun = $this->v($endNac->xMun ?? null) ?: ($cityFallback ?: $cMun);
        $uf = $cMun ? $this->ufFromCode($cMun) : '';
        return $this->dash(trim($xMun . ($uf ? ' - ' . $uf : ''), ' -'));
    }

    /* Municipio - UF a partir do no end do toma/interm */
    private function getMunCityToma($end)
    {
        if (!$end)
            return '-';
        $endNac = $end->endNac ?? null;
        if (!$endNac)
            return '-';
        $cMun = $this->v($endNac->cMun ?? null);
        $xMun = $this->v($endNac->xMun ?? null) ?: $cMun;
        $uf = $cMun ? $this->ufFromCode($cMun) : '';
        return $this->dash(trim($xMun . ($uf ? ' - ' . $uf : ''), ' -'));
    }

    /* ===== HELPERS DE RENDERIZACAO ===== */

    /* Renderiza par lbl/val dentro de um <td> */
    private function field($label, $value)
    {
        return '<span class="lbl">' . $label . '</span><span class="val">' . $value . '</span>';
    }

    /* Cabecalho de bloco full-width com colspan=4 */
    private function sectionHeader($title)
    {
        return '<tr class="blk-full"><td colspan="4">' . $title . '</td></tr>';
    }

    /* PIS/COFINS so aparecem para competencias ate 2026 */
    private function isPisCofinsVisible()
    {
        return (int) substr($this->v($this->infDPS->dCompet), 0, 4) <= 2026;
    }

    /* Formata moeda; retorna '-' para zero ou vazio */
    private function fmtMZero($v)
    {
        $n = (float) str_replace(',', '.', $v ?: '0');
        return $n > 0 ? 'R$ ' . number_format($n, 2, ',', '.') : '-';
    }

    /* ===== HELPERS DE VALOR ===== */

    private function v($node)
    {
        return $node !== null ? trim((string) $node) : '';
    }

    private function h($s)
    {
        return htmlspecialchars((string) $s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function dash($s)
    {
        $s = (string) $s;
        return ($s !== '') ? $s : '-';
    }

    private function trunc($s, $max)
    {
        $s = (string) $s;
        if ($s === '' || $s === '-')
            return '-';
        return (strlen($s) > $max) ? substr($s, 0, $max - 3) . '...' : $s;
    }

    private function desc(array $map, $code)
    {
        $code = (string) $code;
        return isset($map[$code]) ? $map[$code] : $this->dash($code);
    }

    private function fmtDate($val)
    {
        $s = $this->v($val);
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $s, $m))
            return $m[3] . '/' . $m[2] . '/' . $m[1];
        return $this->dash($s);
    }

    private function fmtDateTime($val)
    {
        $s = $this->v($val);
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})T(\d{2}:\d{2}:\d{2})/', $s, $m))
            return $m[3] . '/' . $m[2] . '/' . $m[1] . ' ' . $m[4];
        return $this->dash($s);
    }

    private function fmtDoc($v)
    {
        $v = preg_replace('/\D/', '', (string) $v);
        if (strlen($v) === 14)
            return preg_replace('/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/', '$1.$2.$3/$4-$5', $v);
        if (strlen($v) === 11)
            return preg_replace('/^(\d{3})(\d{3})(\d{3})(\d{2})$/', '$1.$2.$3-$4', $v);
        return $this->dash($v);
    }

    private function fmtCEP($v)
    {
        $v = preg_replace('/\D/', '', (string) $v);
        if (strlen($v) === 8)
            return preg_replace('/^(\d{5})(\d{3})$/', '$1-$2', $v);
        return $this->dash($v);
    }

    private function fmtPhone($v)
    {
        $v = preg_replace('/\D/', '', (string) $v);
        if (strlen($v) === 10)
            return preg_replace('/^(\d{2})(\d{4})(\d{4})$/', '($1) $2-$3', $v);
        if (strlen($v) === 11)
            return preg_replace('/^(\d{2})(\d{5})(\d{4})$/', '($1) $2-$3', $v);
        return $this->dash($v);
    }

    private function fmtM($v)
    {
        $v = (string) $v;
        if ($v === '' || $v === '-')
            return '-';
        return 'R$ ' . number_format((float) str_replace(',', '.', $v), 2, ',', '.');
    }

    private function fmtAliq($v)
    {
        $v = (string) $v;
        if ($v === '')
            return '-';
        $f = (float) str_replace(',', '.', $v);
        if ($f === 0.0)
            return '-';
        if ($f > 0 && $f < 1)
            $f *= 100;
        return number_format($f, 2, ',', '.') . '%';
    }

    private function ufFromCode($code)
    {
        $prefix = substr(preg_replace('/\D/', '', (string) $code), 0, 2);
        return isset(self::$ufMap[$prefix]) ? self::$ufMap[$prefix] : '';
    }
}
