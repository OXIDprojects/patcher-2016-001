<?php

  define('OX_IS_ADMIN', true);
  include_once('bootstrap.php');

  $oAdminUser = oxNew('oxuser');
  $blHasDefaultAdmin = $oAdminUser->load('oxdefaultadmin');

  if(file_exists(getShopBasePath().'admin/.htaccess')){
    $sHtAcesssContent = file_get_contents(getShopBasePath().'admin/.htaccess');

    if( preg_match("/#\s*AuthType Basic/", $sHtAcesssContent ) === 0 && preg_match("/AuthType Basic/", $sHtAcesssContent ) ){
      $blHasHtaccess = true;
    }
  }
  

  if(isset($_POST['run'])){

    $aMessages = array();

    if(isset($_POST['htAccessCreate'])){
        
      if(isset($_POST['htUser']) && isset($_POST['htPassword']) ){
          
          $sHtaccess = "AuthType Basic\n";
          $sHtaccess .= "AuthName \"Password Protected Area\"\n";
          $sHtaccess .= "AuthUserFile ".getShopBasePath()."admin/.htpasswd\n";
          $sHtaccess .= "Require valid-user";

          $sHtpassword = $_POST['htUser'] . ':' . crypt($_POST['htPassword'], base64_encode($_POST['htPassword']));

          if(!file_exists(getShopBasePath().'admin/.htaccess')){
            file_put_contents(getShopBasePath().'admin/.htaccess', $sHtaccess);
            file_put_contents(getShopBasePath().'admin/.htpasswd', $sHtpassword);
            $aMessages[] = array('type' => 'success', 'message' => 'Der Verzeichnisschutz wurde angelegt, bitte prüfen Sie ob die Abfrage beim Aufruf des Admin Bereichs korrekt funktioniert. <br/> <strong>Benutzername: </strong>' . $_POST['htUser'] . ' <br/><strong>Passwort: </strong>' . $_POST['htPassword']);
          }else{
            $sMessage  = 'Die .htaccess Datei hat bereits existiert, legen Sie folgende Dateien an:<br/><br/>';
            $sMessage .= '<strong>Dateiname: </strong><br/><i>admin/.htaccess</i><br/><strong>Inhalt:</strong><br><i>';
            $sMessage .= nl2br($sHtaccess);
            $sMessage .= '</i><br/><br/><strong>Dateiname: </strong><br><i>admin/.htpasswd</i><br/><strong>Inhalt: </strong><br/><i>';
            $sMessage .= $sHtpassword . '</i>';
            $aMessages[] = array('type' => 'warning', 'message' => $sMessage);
          }

      }else{
        $aMessages[] = array('type' => 'danger', 'message' => 'Der Benutzer und das Passwort für den Verzeichnisschutz müssen angegeben werden!');
      }


    }

    if(isset($_POST['removeDefaultAdmin'])){

      $oUser = oxNew('oxuser');
      if($oUser->load('oxdefaultadmin')){

        //Create new admin
        $oNewUser = oxNew('oxuser');
        $oNewUser->oxuser__oxusername = new oxField($_POST['email']);
        $oNewUser->oxuser__oxrights = new oxField('malladmin');
        $oNewUser->setPassword($_POST['password']);

        //delete old user
        if( $oUser->delete() ){
          //save new one
          $oNewUser->createUser();
        }
        
      }else{
        $aMessages[] = array('type' => 'info', 'message' => 'Der Standard Benutzer ist nicht mehr vorhanden, es wurde kein neuer Benutzer angelegt.');
      }

    }
  }
  

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>Oxid Security patcher </title>

    <!-- Bootstrap -->
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">



    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
    
    <div class="container">

      <?php foreach($aMessages as $aMessage): ?>
        <div class="alert alert-<?php echo $aMessage['type'] ?>" role="alert"><?php echo $aMessage['message'] ?></div>
      <?php endforeach; ?>

      <form class="form-signin" action="/patcher.php" method="POST">
        <input type="hidden" name="run" value="1" />
        <h2 class="form-signin-heading">Oxid Security Patcher</h2>
        <p>
          Dieses Script führt die Schritte aus, um einen direkten Angriff auf die Sicherheitslücke 2016-001 zu verhindern. Nur wenn beide Funktionen ausgeführt werden, kann ein direkter Angriff vermieden werden. Bitte führen Sie unbedingt das angebotene Shopupdate durch, um die Sicherheitslücke zu schliessen!
        </p>
        <p>Release notes:</p>
        <ul>
          <li>http://oxidforge.org/en/oxid-eshop-version-4-8-12-ce-pe-5-1-12-ee.html</li>
          <li>http://oxidforge.org/en/oxid-eshop-version-4-9-9-ce-pe-5-2-9-ee.html</li>
        </ul>
        <p>Security Bulletin:</p>
        <p>http://oxidforge.org/en/security-bulletin-2016-001.html</p>

        <p>FAQ:</p>
        <p>http://oxidforge.org/en/faq-security-bulletin-2016-001.html</p>

        <?php if($blHasDefaultAdmin): ?>
        <div class="checkbox">
          <label>
            <input name="removeDefaultAdmin" type="checkbox" value="1" class="checkbox"/> Standard Admin Benutzer löschen
            <p class="help-block">Löscht den Standard Benutzer und legt einen neuen Benutzer mit den Daten unten an</p>
            <p class="help-block" style="color:red;">Diese Funktion löscht den Benutzer mit der E-Mail Adresse <strong><?php echo $oAdminUser->oxuser__oxusername->value ?></strong> inklusive aller zugehörigen Daten!</p>
          </label>
        </div>
        <div class="form-group">
          <label for="inputAdminUser">Oxid Admin E-Mail</label>
          <input name="email" type="email" id="inputEmail" class="form-control" placeholder="Email addresse" autofocus>
        </div>
        <div class="form-group">
          <label for="inputAdminPassword">Oxid Admin Passwort</label>
          <input name="password" type="password" id="inputPassword" class="form-control" placeholder="Passwort">
        </div>
        <?php else: ?>
        <div class="alert alert-info" role="alert">
          Der Standard Administrator existiert nicht mehr, die Funktion um einen neuen Administrator zu erstellen steht nicht zur Verfügung!
        </div>
        <?php endif; ?>
        <hr/>
        <?php if(!$blHasHtaccess): ?>
        <div class="checkbox">
          <label>
            <input name="htAccessCreate" type="checkbox" value="1" id="inputHtAccessCreate" class="checkbox"/> Verzeichnisschutz anlegen
            <p class="help-block">Funktioniert nur wenn noch keine .htaccess Datei im admin/ Ordner existiert. Falls doch achten Sie bitte auf die folgenden Anweisungen.</p>
          </label>
        </div>
        <div class="form-group">
          <label for="inputHtUser">Verzeichnisschutz Benutzer</label>
          <input name="htUser" type="text" id="inputHtUser" class="form-control" placeholder="Benutzername">
        </div>
        <div class="form-group">
          <label for="inputHtPassword">Verzeichnisschutz Passwort</label>
          <input name="htPassword" type="htPassword" id="inputHtPassword" class="form-control" placeholder="Passwort">
        </div>
        <?php else: ?>
        <div class="alert alert-info" role="alert">
          Es scheint bereits ein Verzeichnisschutz für den Admin Bereich zu existieren, die Funktion um eine .htaccess Datei zu erstellen steht nicht zur Verfügung!
        </div>
        <?php endif; ?>

        <button class="btn btn-lg btn-primary btn-block" type="submit">Patchen</button>
      </form>

    </div> <!-- /container -->

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
  </body>
</html>
