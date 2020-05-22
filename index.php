<?php
include('simple_html_dom.php');
require 'rb.php';
require 'array.php';



R::setup( 'mysql:host=localhost;dbname=banco_imagens', 'root', '' );
if(!R::testConnection()){
    die ("Erro ao conectar");
    exit;
}

R::ext('xdispense', function( $type ){
	return R::getRedBean()->dispense( $type );
});




$PRODUTOS = [
	[4111, "LARANJA LIMA KG"],
	[4113, "AMEIXA NACIONAL KG"],
];


$aux1 = 1;
foreach ($n_produtos as $produto) {

    $nome 		  = $produto[1];
    $cod  		  = $produto[0];

	$nome_produto 	 = explode(" ", $nome);
	$new_nome        = implode("_",$nome_produto);
	$new_nome        = replaceChar($new_nome);
	$nome_produto	 = $nome_produto[0];
	$nome_descricao  = $nome;

    $produto = R::xdispense('produto');
    $produto->descricao = $nome_descricao;
    $produto_id = R::store($produto);

	$search_query = urlencode($nome_descricao);
	$url = "https://www.google.com/search?q=$search_query&tbm=isch";

	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec($curl);
	curl_close($curl);

	$domResult = new simple_html_dom();
	$domResult->load($result);
	$aux = 0;
	foreach($domResult->find('/<img[^>]+>/') as $link){
		if($link->src){
			if($aux == 1 ){ //or $aux == 2 or $aux == 3{

                $img = "{$new_nome}_{$new_nome}_{$aux}.png";
                file_put_contents("imagens/$img", file_get_contents( $link->src));
                echo "Imagen $aux1 Salva - $img\n";



                $produto_codbarras = R::xdispense('produto_codbarras');
                $produto_codbarras->idproduto = $produto_id;
                $produto_codbarras->codbarras = $cod;
                R::store($produto_codbarras);

                $produto_descricao = R::xdispense('produto_descricao');
                $produto_descricao->idproduto = $produto_id;
                $produto_descricao->descricao = $nome;
                R::store($produto_descricao);

                $produto_imagem = R::xdispense('produto_imagem');
                $produto_imagem->idproduto = $produto_id;
                $produto_imagem->imagem    = $img;
                R::store($produto_imagem);

			}
			$aux ++;
		}
	}

	$aux1 ++;

}

function replaceChar($str){
        $str = preg_replace('/[áàãâä]/ui', 'a', $str);
        $str = preg_replace('/[éèêë]/ui', 'e', $str);
        $str = preg_replace('/[íìîï]/ui', 'i', $str);
        $str = preg_replace('/[óòõôö]/ui', 'o', $str);
        $str = preg_replace('/[úùûü]/ui', 'u', $str);
        $str = preg_replace('/[ç]/ui', 'c', $str);
        $str = preg_replace('/[^a-z0-9]/i', '_', $str);
        $str = preg_replace('/_+/', '_', $str);
        return $str;
    }
?>
