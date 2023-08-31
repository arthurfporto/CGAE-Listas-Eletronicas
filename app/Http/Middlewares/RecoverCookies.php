<?php

namespace App\Http\Middlewares;

class RecoverCookies
{
    /**
     * Executa o middleware
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle($request, $next)
    {
        if ($this->verifyCookies()) 
        {
            $this->recoverCookies();
        }

        return $next($request);
    }

    /**
     * Verifica se existem cookies de login
     */
    private function verifyCookies()
    {
        // VERIFICA SE EXISTE ALGUM COOKIE DE LOGIN ATIVO
        return isset($_COOKIE['user']);
    }

    /**
     * 
     */
    private function recoverCookies()
    {
        // INICIALIZA A SESSÃO
        \App\Session\Login::init();

        // CONFIGURA A SESSÃO REALIZANDO O LOGIN
        if ($_COOKIE['user'] == "student")
        {
            $ob = \App\Model\Entity\Student::getStudentById($_COOKIE['id']);

        }

        else if ($_COOKIE['user'] == "assistant")
        {
            $ob = \App\Model\Entity\Assistant::getAssistantById($_COOKIE['id']);
        }

        else
        {
            $ob['nome'] = \App\Model\Entity\Admin::NOME;
            $ob['email'] = \App\Model\Entity\Admin::EMAIL;
            $ob['senha'] = \App\Model\Entity\Admin::SENHA;
        }
            
        \App\Session\Login::login($ob);
    }
}

?>