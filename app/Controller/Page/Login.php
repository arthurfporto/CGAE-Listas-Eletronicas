<?php

namespace App\Controller\Page;

use \App\Utils\View;
use \App\Model\Entity;
use \App\Session;

/**
 * Controlador da página de login
 */
class Login
{
    /**
     * Retorna a View da página de login
     * @param Request $request Objeto referente a requisição
     * @param string|null $errorMessage Mensagem de erro
     * @return string View renderizada
     */
    public static function getLogin($errorMessage = null)
    {
        // CARREGA A MENSAGEM DE ERRO
        $status = !is_null($errorMessage) ? Alert::getError($errorMessage) : "";
        
        // RENDERIZA A VIEW
        $content = View::render("pages/login", [
            "title"   => "Entrar",
            "status" => $status,
        ]);
        
        return $content;
    }

    /**
     * Realiza o login
     * @param Request $request Objeto referente a requisição
     */
    public static function setLogin($request)
    {
        // OBTÉM AS VARIÁVEIS DE POST
        $postVars = $request->getPostVars();

        if (!isset($postVars['check']))
        {
            $postVars['check'] = "off";
        }

        $type = "";
        $ob = null;

        // VERIFICA SE AS CREDENCIAIS ESTÃO CORRETAS
        $obAux = Entity\Aluno::getAlunoByEmail($postVars['email']);

        if (!$obAux instanceof Entity\Aluno || !password_verify($postVars['senha'], $obAux->senha))
        {
            $obAux = Entity\Assistente::getAssistenteByEmail($postVars['email']);

            if (!$obAux instanceof Entity\Assistente || !password_verify($postVars['senha'], $obAux->senha))
            {
                $obAux = null;

                if ($postVars['email'] != Entity\Admin::EMAIL || password_verify($postVars['senha'], Entity\Admin::SENHA))
                {
                    // RETORNA A PÁGINA DE LOGIN COM A MENSAGEM DE ERRO
                    return self::getLogin("Usuário ou senha inválidos!");
                }
                
                else
                {
                    $type = "admin";
                }
            }

            else 
            {
                $type = "assistant";
            }
        }

        else
        {
            $type = "student";
        }

        $ob = $obAux;
        $postVars['id'] = $ob->id ?? -1;

        // REALIZA O LOGIN
        Session\Login::login($ob, $type);

        // CONFIGURA OS COOKIES DE LOGIN
        if ($postVars['check'] == "on")
        {
            self::setCookies($postVars, $type);
        }

        // REALIZA O REDIRECIONAMENTO
        switch ($type)
        {
            case "student":
                $request->getRouter()->redirect("/");
                break;

            case "assistant":
                $request->getRouter()->redirect("/assistant");
                break;

            default:
                $request->getRouter()->redirect("/admin");
                break;
        }
    }

    /**
     * Configura os cookies de login
     * @param array $postVars Variáveis do formulário de login
     * @param string $type Tipo de usuário a realizar o login
     */
    private static function setCookies($postVars, $type)
    {
        date_default_timezone_set("America/Sao_Paulo");
        
        setcookie("user", null, time() - 100);
        setcookie("id", null, time() - 100);
        setcookie("nome", null, time() - 100);
        setcookie("email", null, time() - 100);

        setcookie("user", $type, time() + getenv('LOGIN_TIME'));
        setcookie("id", $postVars['id'], time() + getenv('LOGIN_TIME'));
        setcookie("nome", $postVars['nome'], time() + getenv('LOGIN_TIME'));
        setcookie("email", $postVars['email'], time() + getenv('LOGIN_TIME'));
    }

    /**
     * Desconecta o usuário
     * @param Request $request Objeto referente a requisição
     */
    public static function setLogout($request)
    {
        // REALIZA O LOGOUT
        Session\Login::Logout();

        // REDIRECIONA O USUÁRIO
        $request->getRouter()->redirect("/login");
    }
}

?>