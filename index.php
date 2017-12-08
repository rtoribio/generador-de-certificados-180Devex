<style>
	
b,p{font-size: 12px; }

</style>
<?php 

include 'certificados.php';

$Obj = new Certificados(); 


if (isset($_GET['controller']))
{
	
	if (!$_GET['controller'])
	{
		
		echo "¡ERROR!";
		exit(); 

	}

	if (!isset($_FILES['cer']['name']) OR empty($_FILES['cer']['name']))
	{

		echo "No existe el archivo .cer";
		exit(); 
	
	}

	if (!isset($_FILES['key']['name']) OR empty($_FILES['key']['name']))
	{

		echo "No existe el archivo .key";
		exit(); 
	
	}


	if (!isset($_POST['password']) OR empty($_POST['password']))
	{

		echo "Ingresa la contraseña";
		exit(); 
	
	}


	if ( strtoupper(explode('.', $_FILES['cer']['name'])[1]) != 'CER' OR strtoupper(explode('.', $_FILES['key']['name'])[1]) != 'KEY')
	{
		echo "Carga los archivos correctos"; 
		exit(); 
	}


	$directorio = 'certificados/' . explode('.', $_FILES['cer']['name'])[0] . '/'; 

	if (!file_exists($directorio))
	{
	
		mkdir($directorio);
		
	
	}


	$path_cer = 'certificados/' . explode('.', $_FILES['cer']['name'])[0] . '/'.$_FILES['cer']['name']; 

	if (!file_exists($path_cer))
	{
	
		if(!move_uploaded_file($_FILES['cer']['tmp_name'], $path_cer))
		{

			echo "No se pudo cargar el archivo .cer";
			exit();

		}

	}

	$path_key = 'certificados/' . explode('.', $_FILES['key']['name'])[0] . '/'.$_FILES['key']['name'];

	if (!file_exists($path_key))
	{
	
		if(!move_uploaded_file($_FILES['key']['tmp_name'], $path_key))
		{

			echo "No se pudo cargar el archivo .key";
			exit();

		}

	}


	$cer_pem = $Obj -> generaCerPem($path_cer);
	
	if (isset($cer_pem['error']))
	{

		echo "No se pudo crear el archivo cer.pem - " . $cer_pem['error'];

		unlink($path_cer); 
		unlink($path_key);

		exit();

	}
	

	$key_pem = $Obj -> generaKeyPem($path_key,$_POST['password']);

	if (isset($key_pem['error']))
	{

		echo "No se pudo crear el archivo key.pem - " . $key_pem['error'];

		if(!unlink($path_cer.'.pem'))
		{
			
			echo "<br> No se pudo eliminar el archivo cer.pem, eliminalo manualmente para eviatar errores"; 
			exit(); 

		} 
		
		exit(); 
	}

	$sello = $Obj -> getSelloCer($path_cer,$path_key .'.pem'); 
	$certificado = $Obj -> getCertificado($path_cer);
	$no_certificado = $Obj -> getSerialCert($path_cer .'.pem');
 

	file_put_contents($directorio . 'SELLO.txt', $sello);

	file_put_contents($directorio . 'CERTIFICADO.txt', $certificado);

	file_put_contents($directorio . 'NO_CERTIFICADO.txt', $no_certificado['serial']);

}



?>


<?php echo(isset($sello) ? '<b>SELLO: </b> <p>'.$sello.'</p><br><br>' : '' );  ?>

<?php echo(isset($certificado) ? '<b>CERTIFICADO: </b> <p>'.$certificado.'</p><br><br>' : '' );  ?>

<?php echo(isset($no_certificado['serial']) ? '<b>NO. CERTIFICADO: </b> <p>'.$no_certificado['serial'].'</p><br><br>' : '' );  ?>




<form action="?controller=true" enctype="multipart/form-data" method="POST">
	
	<input type="file" name="cer" required>
	
	<input type="file" name="key" required>

	<input type="text" name="password" required>

	<input type="submit" value="Convertir">
	
</form>
