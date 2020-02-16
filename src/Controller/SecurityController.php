<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Annotation\JsonParams;

class SecurityController extends AbstractController
{
    /**
     * @Route("/api/login", name="login")
     * 
     */
    public function login()
    {        
        // Gets resolved in guard authenticator
    }

    /**
     * @Route("/api/auth", name="security_auth")
     */
    public function auth() 
    {
        return $this->json([
            "authenticated" => $this->isGranted("ROLE_ADMIN")
        ]);
    }

    /**
     * @Route("/api/change-password", name="change_password", methods={"POST"})
     */
    public function changePassword(Request $request, UserPasswordEncoderInterface $encoder)
    {
        if ($this->isGranted("ROLE_USER")) {
            throw new UnauthorizedHttpException("");
        }
        
        $user = $this->getUser();

        $oldPassword = $request->request->get("oldPassword");
        $newPassword = $request->request->get("newPassword");
        
        
        if (!$encoder->isPasswordValid($user, $oldPassword)) {
            throw new \Exception("Invalid password.");
        }
        
        $user->setPassword($encoder->encodePassword($user, $newPassword));

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $this->json([]);
    }

     
    /**
     * @Route("/api/logout", name="logout")
     */
    public function logout()
    {

    }
}
