<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Erro</title>
</head>
<body>
{literal}
<style>

vazio {
	border:none !important;
	text-align:center !important;
}
.t {
background-color:#dddddd;
color:#000000;
font-weight:bold;
}
.e {
background-color:#efefef;
color:#000000;
font-weight:bold;
text-align:right;
vertical-align:top;
}
.v {
text-align:left;
}
.center {
	text-align:center;
}
</style>
{/literal}

	<div class="center">
	<center>
		<table width="800px"  cellpadding="3" border="0">
		<tbody>

			<tr>
				<td class="t" colspan="2">
					{$errtype}
				</td>
			</tr>


			<tr>
				<td class="e">
					Mensagem:
				</td>
				<td class="v">
					<strong>{$errstr}</strong>
				</td>
			</tr>

			<tr>
				<td class="e">
					Arquivo:
				</td>
				<td class="v">
					{$errfile} <br>
					{$errline}
				</td>
			</tr>

			<tr>
				<td class="e">
					Modulo:
				</td>
				<td class="v">
					{$app->getM()}
				</td>
			</tr>

			<tr>
				<td class="e">
					SubModulo:
				</td>
				<td class="v">
					{$app->getU()}
				</td>
			</tr>
			<tr>
				<td class="e">
					Action:
				</td>
				<td class="v">
					{$app->getA()}
				</td>
			</tr>
			<tr>
				<td class="e">
					Transacao:
				</td>
				<td class="v">
					{$app->getAcao()}
				</td>
			</tr>

			<tr>
				<td class="e">
					URL:
				</td>
				<td class="v">
					{$server.SCRIPT_NAME}
				</td>
			</tr>

			<tr>
				<td class="e">
					Parametros
				</td>
				<td class="v">
{foreach from=$lsParms item=valor key=chave}
					{$chave} = {$valor}<br />
{/foreach}
				</td>
			</tr>
			<tr>
				<td class="t" colspan="2">
					Configuracoes/versao
				</td>
			</tr>
			<tr>
				<td class="e">
					Gama versao:
				</td>
				<td class="v">
					{$ver.versao}
				</td>
			</tr>

{foreach from=$listaParmConfig item=valor key=chave}
			<tr>
				<td class="e">
					{$chave}
				</td>
				<td class="v">
					{$valor}
				</td>
			</tr>
		{/foreach}


		</tbody>
		</table>

		</center>
	</div>


