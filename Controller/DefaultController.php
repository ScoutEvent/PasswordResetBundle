<?php

namespace ScoutEvent\PasswordResetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

use ScoutEvent\PasswordResetBundle\Form\Type\ResetType;

class DefaultController extends Controller
{
    public function resetAction(Request $request, $token)
    {
        $em = $this->getDoctrine()->getManager();
        $resetToken = $em
            ->getRepository('ScoutEventPasswordResetBundle:PasswordReset')
            ->findOneBy(array('token' => $token));
        
        if ($resetToken == null)
        {
            // Invalid token
            return $this->redirect($this->generateUrl('scout_base_app_list'));
        }
    
        $form = $this->createForm(new ResetType(), null, array(
            'action' => $this->generateUrl('scout_password_reset', array("token" => $token))
        ));
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $user = $resetToken->getUser();
            $user->setRawPassword($form->get('password')->getData(), $this->get('security.encoder_factory'));
            $em->remove($resetToken);
            $em->flush();
        
            $token = new UsernamePasswordToken($user, null, 'secured_area', $user->getRoles());
            $this->get('security.token_storage')->setToken($token);

            return $this->redirect($this->generateUrl('scout_base_app_list'));
        }
        
        return $this->render('ScoutEventPasswordResetBundle:Default:reset.html.twig', array(
            'form' => $form->createView()
        ));
    }
}
