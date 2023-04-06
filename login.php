<?php
session_start();

include $_SERVER['DOCUMENT_ROOT']."/php/basic_arrays.php"; // Charge infos de bases
include $_SERVER['DOCUMENT_ROOT']."/php/basic_fcts.php"; // Charge fonctions de bases
include $_SERVER['DOCUMENT_ROOT']."/php/bdd_connect.php"; 
include $_SERVER['DOCUMENT_ROOT'].'/php/vendor/autoload.php'; // charge les librairies composer

use PHPAuth\Config; 
use PHPAuth\Auth;
$config = new PHPAuth\Config($bdd, $CONFIG_ARRAY_phpauth, 'array',$CONFIG_ARRAY_phpauth["site_language"]);
$auth   = new PHPAuth\Auth($bdd, $config);


// si appel de login.php?logout=1
if ($_GET["logout"]=="1")
{
  $auth->logout($auth->getCurrentSessionHash(),true);
  session_destroy();
  unset($_SESSION['utilisateur']);
  header("Location: /login.php");
}

// Si deja loggué > index.php
if ($auth->isLogged()) {
    header('Location: /index.php'); exit; 
}



// Etat php auth ?
$blocked=$auth->isBlocked();
$captcha="";
if($blocked=='verify')
{
  $captcha="recaptcha";
}

?>

<html>
<head>
<? include $_SERVER['DOCUMENT_ROOT']."/composants/basic_head.php"; //  head?>    
<script src='https://www.google.com/recaptcha/api.js'></script>
<style>
.loginform.login:not(.recaptcha) .g-recaptcha {
    display: none;
}
.loginform.login:not(.recaptcha) .recaptcha.message {
    display: none;
}
.ui.login.message {
    display: none;
}
.ui.login.active.message {
    display: block;
}


body {
    background-color: #F5F5F5;
}

.ui.container.loginform.login{
  margin-top: 40px;
}

.ui.container.loginform.forgot{
  margin-top: 40px;
}


.ui.erreur.message {
    background-color: rgba(217, 45, 33, 0.5);
    color: #fff;
    -webkit-box-shadow: 0 0 0 1px #db2828 inset, 0 0 0 0 transparent;
    box-shadow: 0 0 0 1px #db2828 inset, 0 0 0 0 transparent;
}

.ui.segment {
      background-color: #FFFFFF;
      border-radius: 5px;
      box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1)
    }

.ui.warn.message {
    background-color: rgba(217, 111, 33, 0.5);
    color: #fff;
    -webkit-box-shadow: 0 0 0 1px #db2828 inset, 0 0 0 0 transparent;
    box-shadow: 0 0 0 1px #db6b28 inset, 0 0 0 0 transparent;
    font-size: 15px;
    text-align: center;
}

.ui.login.message .header {
    color: white;
}

.ui.ok.message {
    background-color: rgba(94, 242, 28, 0.5);
    color: #fff;
    -webkit-box-shadow: 0 0 0 1px #6ef21c inset, 0 0 0 0 transparent;
    box-shadow: 0 0 0 1px #6ef21c inset, 0 0 0 0 transparent;
}

.overflow {
    overflow: hidden;
}

.field .red.pointing.label {
    display: table !important;
    margin: 6px auto -6px;
    background-color: #661c1c !important;
    border-color: #db2828 !important;
    color: #fff !important;
}
.field.error .red.pointing.label {
    position: absolute;
    right: 30px;
    margin-top: -15px;
}
</style>
</head>
<body>

<div class="ui container loginform login <? echo $captcha;?>">
    <div class="ui centered grid">
      <div class="six wide computer ten wide tablet twelve wide mobile column">
        <div class="ui segment">
          <h2 class="ui header">Connexion</h2>
          <div class="ui login red message"></div>
          <div class="ui form">
            <div class="field">
              <label>Email</label>
              <div class="ui left icon input">
                <i class="envelope icon"></i>
                <input type="email" name="email" id="id_user" placeholder="Entrez votre email">
              </div>
            </div>
            <div class="field">
              <label>Mot de passe</label>
              <div class="ui left icon input">
                <i class="lock icon"></i>
                <input type="password" name="motdepasse" id="mdp_user" placeholder="Entrez votre mot de passe">
              </div>
            </div>
            <div class="g-recaptcha" data-sitekey="<? echo $CONFIG_ARRAY_phpauth['recaptcha_site_key']?>"></div>
            <div class="field">
              <div class="ui checkbox">
                <input type="checkbox" tabindex="0" class="hidden">
                <label>Rester connecté</label>
              </div>
            </div>
            <button  onclick="login()" class="ui primary login button" type="submit">Se connecter</button>
            <a class="ui button" href="/signup.php">Inscription</a>
            </div>
            <h5 id="forgot_link" class="ui red inverted header" onclick="forgot()" style="cursor: pointer; margin: 1em auto 0em; text-align:center;">Oups j'ai oublié mon mot de passe...</h5>
        </div>
      </div>
    </div>
  </div>

  <div class="ui container loginform forgot" style="display:none;">
    <div class="ui centered grid">
      <div class="six wide computer ten wide tablet twelve wide mobile column">
        <div class="ui segment">
          <h2 class="ui header" style="line-height: 1.5;">Vous avez oublié vos identifiants ?<br>
          <div class="sub header" style="line-height: 1.5;">Vous pouvez réinitialiser votre mot de passe à l'aide de votre email.</div></h2>
          <div class="ui login red message"></div>
          <div class="ui form">
          <div class="field">
              <label>Email</label>
              <div class="ui left icon input">
                <i class="envelope icon"></i>
                <input type="email" name="email" id="mail_user" placeholder="Entrez votre email">
              </div>
            </div>
            <button  onclick="rst_password()" style="margin-top: 17px;" class="ui primary forgot button" type="submit">Réinitialiser</button>
        <h5 class="ui blue header" onclick="forgotcancel()" style="cursor: pointer; margin: 1em auto 0em; text-align:center;">Laissez tomber, je viens de m'en rappeler....</h5>
          </div>
        </div>
      </div>
    </div>
</div>
    
<!-- <div class="loginform forgot form" style="display:none;">

<div class="ui inverted segment">
    <h3 class="ui green header" style="text-align: center;">Vous avez oublié vos identifiants ? 
     <div class="sub header" style="color: #dddada;">Vous pouvez réinitialiser votre mot de passe à l'aide de votre email.</div></h3>
    <div class="ui login message"></div>
    <div class="field">
    <div class="ui labeled left icon input">
      <input placeholder="email" id="mail_user" type="text" type="text" autocorrect="off" autocapitalize="none" autocomplete="off">
      <i class="envelope open outline large icon"></i>
    </div>
    </div>
    <button  onclick="rst_password()" style="margin-top: 17px;" class="ui inverted large fluid green forgot button" type="submit">Réinitialiser</button>
    <h5 class="ui green header" onclick="forgotcancel()" style="cursor: pointer; margin: 1em auto 0em; text-align:center;">Laissez tomber, je viens de m'en rappeler....</h5>
</div>
</div> -->

</body>
<script>
      
 function forgot()
    {
        if ($('.login.button').hasClass('loading')){return false;}
        $('.login.message').removeClass("active");
        $('.login.message').html("");
        $(".loginform.login").transition('slide right');
        setTimeout(function(){$(".loginform.forgot").transition('slide left');  }, 500); 
        
    }
    
function forgotcancel()
    {
        if ($('.forgot.button').hasClass('loading')){return false;}
        $('.login.message').removeClass("active");
        $('.login.message').html("");
        $('.conmsg').hide();
        $(".loginform.forgot").transition('slide left');
        setTimeout(function(){$(".loginform.login").transition('slide right');  }, 500); 
    }

function login()
{
  
if ( $('.login.button').hasClass('disabled')) {return false;}  // embeche envoi xhr deux fois
  
  var recaptcha_valid=true;
  var recaptcha_reponse="";
  if((typeof(grecaptcha)!="undefined")&&(typeof(grecaptcha.getResponse)=="function"))
 { recaptcha_reponse=grecaptcha.getResponse();
  if(( $('.loginform').hasClass('recaptcha'))&&(recaptcha_reponse=="")){recaptcha_valid=false;}
 }
    if ($('.loginform').form("is valid")&&recaptcha_valid)
    {
    $('.login.button').addClass('disabled active loading');
    var login=$("#id_user").val();
    var mdp=$("#mdp_user").val();

    // Prepare la requete ajax
    var posting = $.post("/api/auth/loginapi.php", {login : login, pass: mdp, recaptcha_reponse:recaptcha_reponse});
   
    // Lorsque la requere a repondu
    posting.done(function (data) { 
        console.log(data);
        try { var res= JSON.parse(data); }
        catch(e)
        {
        $('.loginform .login.message').html("<i class='frown outline huge icon' style='margin: 0px auto 20px;width: 100%;'></i>");
        $('.loginform .login.message').append(data);
        $('.loginform .login.message').removeClass("ok");   
        $('.loginform .login.message').addClass("erreur active");
        if((typeof(grecaptcha)!="undefined")&&(typeof(grecaptcha.reset)=="function"))
        { 
        grecaptcha.reset();
        }
        return false;
        }       

                     
        if(res["message"]=="recaptcha")
        {
            $('.loginform').addClass("recaptcha");
            $('.login.button').removeClass('disabled active loading');

            $(window).resize();
            
            return false;
        }

            if (res["error"])
            {
                $('.loginform .login.message').html(res["message"]);
                $('.loginform .login.message').addClass("erreur active");
                $('.loginform .login.message').removeClass("ok");   
                if((typeof(grecaptcha)!="undefined")&&(typeof(grecaptcha.reset)=="function"))
                { 
                grecaptcha.reset();
                }
                $('.login.button').removeClass('disabled active loading');
            }
            else
            {
                if (res["redirect"])
                {              
                    setTimeout(function(){
                    window.location.href =res["redirect"];
                    },400);
                }
                
            }
                

        });
    }
    
}

    function rst_password()
{
    if ( $('.forgot.button').hasClass('disabled')) {return false;}  // embeche envoi xhr deux fois

    //$('.loginform').form("submit");
    if ($('.loginform').form("is valid"))
    {
    $('.forgot.button').addClass('disabled active loading');
    //$('.login.button').blur();
    var email=$("#mail_user").val();
    var posting = $.post("/api/auth/password_reset.php", {email : email});

    posting.done(function (data) { 
        try { var res= JSON.parse(data); }
            catch(e)
            {
            $('.loginform .login.message').html("<i class='frown outline huge icon' style='margin: 0px auto 20px;width: 100%;'></i>");
            $('.loginform .login.message').append(data);
            $('.loginform .login.message').removeClass("erreur ok");   
            $('.loginform .login.message').addClass("erreur active");
            return false;
            }                    
        $('.loginform .login.message').removeClass("erreur ok");   

        if (res["error"])
        {
            $('.loginform .login.message').addClass("erreur active");   
        }
        else
        {
            $('.loginform .login.message').addClass("ok active");   
        }   
        
            $('.loginform .login.message').html(res["message"]);
        setTimeout(function(){
            $('.forgot.button').removeClass('disabled active loading');
        },5000);
                     

                });
    }
    
}
$(function(){
    // Verification des formulaires
    $('.loginform').form({
    fields: {
     id_user: {
        identifier: 'id_user',
        rules: [
          {
            type   : 'empty',
            prompt : 'Entrez votre login'
          }
        ]
      },
     mdp_user: {
        identifier: 'mdp_user',
        rules: [
          {
            type   : 'empty',
            prompt : 'Entrez votre mot de passe'
          }
        ]
      }
    },
      on: 'blur',
      inline: 'false'
    }  
  )
;
$('.loginform')
  .form({
         fields: { mail_user: {
      identifier : 'mail_user',
      rules: [
        {
          type   : 'empty',
          prompt : 'Entrez votre email'
        }
      ]
    }

  },
      on: 'blur',
      inline: 'true'

    }  
  )
;
// Valide les formulaires par la touche entree
$(".loginform input").keypress(function(e) {
      if(e.which == 13) {
          login();
      }
  });
  $(".loginform input").keypress(function(e) {
      if(e.which == 13) {
          rst_password();
      }
  });
});
</script>