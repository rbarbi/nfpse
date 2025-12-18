<?php
class NfseAction extends DefaultAction {
    public function __construct($app, $GET, $POST) {
        $dados = (array) json_decode(file_get_contents('php://input'));
        
        if (isset($dados[0])) {
            $dados = (array) $dados[0];
		}

        if (!empty($dados)) {
			$_POST['dadosNota'] = $dados;
			$_POST['emp'] = $dados['emp'];
		}

        parent::__construct($app, $GET, $POST, "./mod/".MainGama::getApp()->getM());
    }

    public function indexAction() {
		$retorno = $this->service->preparaRetornoDados(
            array('erro' => 'Por favor defina a ação e parâmetros para executar no microserviço.')
        );

		$this->service->escreveRetorno($retorno);
	}

    /**
	 * Ação padrão para gerar uma NFS-e, seu XML e PDF
	 * @throws Exception
	 */
    public function gerarNotaAction() {
        try {
			$dadosPost = $this->service->getDadosPost();
			
			if (!isset($dadosPost['dadosNota']['emp'])) {
				throw new Exception('Parâmetro "emp" é obrigatório', 400);
			}

			if (!isset($dadosPost['dadosNota']['codigo_boleto'])) {
				throw new Exception('Parâmetro "codigo_boleto" é obrigatório', 400);
			}

            $dadosNota = $this->service->gerarNota($dadosPost);
            
            $arrRetorno = array(
                'chaveAcesso' => $dadosNota['chaveAcesso'],
				'conteudoXML' => $dadosNota['conteudoXML'],
				'conteudoPDF' => isset($dadosNota['conteudoPDF']) ? $dadosNota['conteudoPDF'] : null
			) + (isset($dadosNota['erroGeracaoPDF']) ? array('erroGeracaoPDF' => $dadosNota['erroGeracaoPDF']) : array())
			  + (isset($dadosNota['backupUtilizado']) ? array('backupUtilizado' => $dadosNota['backupUtilizado']) : array());

			$this->service->escreveRetorno(json_encode($arrRetorno));
        } catch (Exception $ex) {
			$this->service->reportaErro($ex);
			exit;
		}
    }

    /**
	 * Ação para cancelar uma NFS-e
	 * @throws Exception
	 */
	public function cancelarNotaAction() {
		try {
			$dadosPost = $this->service->getDadosPost();

            if (!isset($dadosPost['dadosNota']['chaveAcesso'])) {
				throw new Exception('Parâmetro "chaveAcesso" é obrigatório', 400);
			}

			$dadosNota = $this->service->cancelarNota($dadosPost);

			$arrRetorno = array(
				'conteudoXML' => $dadosNota['conteudoXML'],
				'conteudoPDF' => isset($dadosNota['conteudoPDF']) ? $dadosNota['conteudoPDF'] : null
			) + (isset($dadosNota['erroGeracaoPDF']) ? array('erroGeracaoPDF' => $dadosNota['erroGeracaoPDF']) : array())
			  + (isset($dadosNota['backupUtilizado']) ? array('backupUtilizado' => $dadosNota['backupUtilizado']) : array());;

			$this->service->escreveRetorno(json_encode($arrRetorno));
		} catch (Exception $ex) {
			$this->service->reportaErro($ex);
			exit;
		}
	}

    /**
	 * Ação para retornar o XML e PDF de uma nota fiscal com base na chave de acesso passada
	 * @throws Exception
	 */
	public function consultarNotasPorNumerosSerieAction() {
		try {
			$dadosPost = $this->service->getDadosPost()['dadosNota'];
            
			if (!isset($dadosPost['emp'])) {
				throw new Exception('Parâmetro "emp" é obrigatório', 400);
			}
			if (!isset($dadosPost['series'])) {
				throw new Exception('Parâmetro "series" é obrigatório', 400);
			}

			$arrRetorno = [];
			foreach ($dadosPost['series'] as $chaveAcesso) {
				$dadosNota = $this->service->consultarIdNota($chaveAcesso);

				$arrRetorno[] = array(
					'chaveAcesso' => $chaveAcesso,
					'conteudoXML' => $dadosNota['conteudoXML'],
					'conteudoPDF' => isset($dadosNota['conteudoPDF']) ? $dadosNota['conteudoPDF'] : null,
				) + (isset($dadosNota['erroGeracaoPDF']) ? array('erroGeracaoPDF' => $dadosNota['erroGeracaoPDF']) : array())
				  + (isset($dadosNota['backupUtilizado']) ? array('backupUtilizado' => $dadosNota['backupUtilizado']) : array());
			}

			$this->service->escreveRetorno(json_encode($arrRetorno));
		} catch (Exception $ex) {
			$this->service->reportaErro($ex);
			exit;
		}
	}

    /**
	 * Método que é utilizado na intranet da POL para consultar os valores de retenções de uma fatura
	 */
	public function consultarRetencoesFaturaAction() {
		try {
			$dadosFatura = $this->service->getDadosPost();
			$arrRetorno = $this->service->consultarRetencoesFatura($dadosFatura['dadosNota']);

			$this->service->escreveRetorno(json_encode($arrRetorno));
		} catch (Exception $ex) {
			$this->service->reportaErro($ex);
			exit;
		}
	}
}